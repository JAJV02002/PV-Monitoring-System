#include <Arduino.h>
#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <HTTPClient.h>
#include <math.h>
#include "secrets.h"

const char* serverURL = SERVER_URL;
const char* apiKeyValue = DEVICE_API_KEY;

uint64_t chipID = 0;
String macAddr;

const int pinTension = 34;
const int pinCorriente = 35;

const float vRef = 3300.0;
const int resolucionADC = 4095;
const float VCAL = 179.0 / vRef;
const float ICAL = 15 / vRef;
const unsigned int ADC_COUNTS = resolucionADC;

float offsetV = ADC_COUNTS / 3.1;
float offsetI = ADC_COUNTS / 2.9;

float avgV = 0.0;
float avgI = 0.0;
float sumSqV = 0.0;
float sumSqI = 0.0;
unsigned int numMuestras = 1000;

const int numPromedios = 5;
float acumuladorVrms = 0.0;
float acumuladorIrms = 0.0;
float acumuladorVavg = 0.0;
float acumuladorIavg = 0.0;
int contadorPromedios = 0;

float potenciaActiva = 0.0;
float potenciaAparente = 0.0;
float consumoEnergetico = 0.0;
unsigned long tiempoAnterior = 0;

// Diagnostic report counter
unsigned long measurementNumber = 0;

struct WiFiInfo {
    const char* ssid;
    const char* password;
};

const WiFiInfo knownNetworks[] PROGMEM = {
    {WIFI_SSID, WIFI_PASSWORD}
};

void printSeparator() {
    Serial.println(F("------------------------------------------------------------"));
}

void printStartupHeader() {
    Serial.println();
    printSeparator();
    Serial.println(F("eMon Monitoring System - Startup Diagnostics"));
    printSeparator();
}

void printADCInitialization() {
    Serial.println(F("[ADC] ADC subsystem initialized successfully"));
    Serial.print(F("[ADC] Resolution: 12 bits (0-"));
    Serial.print(resolucionADC);
    Serial.println(F(" counts)"));
    Serial.print(F("[ADC] Voltage channel assigned to GPIO "));
    Serial.println(pinTension);
    Serial.print(F("[ADC] Current channel assigned to GPIO "));
    Serial.println(pinCorriente);
    Serial.print(F("[ADC] Samples per calculation: "));
    Serial.println(numMuestras);
}

void printSensorInitialization() {
    Serial.println(F("[SENSORS] Voltage sensor routine initialized"));
    Serial.println(F("[SENSORS] Current sensor routine initialized"));
    Serial.println(F("[SENSORS] Filtering, RMS, calibration and averaging routines ready"));
    Serial.print(F("[SENSORS] Initial voltage offset: "));
    Serial.println(offsetV, 3);
    Serial.print(F("[SENSORS] Initial current offset: "));
    Serial.println(offsetI, 3);
}

void conectarWiFi() {
    WiFi.mode(WIFI_STA);
    Serial.println(F("[Wi-Fi] Searching for a known network..."));

    for (size_t i = 0; i < sizeof(knownNetworks) / sizeof(knownNetworks[0]); ++i) {
        WiFiInfo network;
        memcpy_P(&network, &knownNetworks[i], sizeof(WiFiInfo));

        Serial.print(F("[Wi-Fi] Attempting connection to: "));
        Serial.println(network.ssid);
        WiFi.begin(network.ssid, network.password);

        for (int j = 0; j < 5 && WiFi.status() != WL_CONNECTED; j++) {
            Serial.print('.');
            delay(1000);
        }
        Serial.println();

        if (WiFi.status() == WL_CONNECTED) {
            macAddr = WiFi.macAddress();
            Serial.println(F("[Wi-Fi] Connection successful"));
            Serial.print(F("[Wi-Fi] Connected SSID: "));
            Serial.println(WiFi.SSID());
            Serial.print(F("[Wi-Fi] Local IP address: "));
            Serial.println(WiFi.localIP());
            Serial.print(F("[Wi-Fi] Signal strength: "));
            Serial.print(WiFi.RSSI());
            Serial.println(F(" dBm"));
            return;
        }

        WiFi.disconnect();
        delay(250);
    }

    Serial.println(F("[Wi-Fi][WARNING] No known network could be reached"));
}

void printDeviceInformation() {
    Serial.println(F("[DEVICE] ESP32 identification initialized"));
    Serial.print(F("[DEVICE] Chip ID: "));
    Serial.println((unsigned long long)chipID);
    Serial.print(F("[DEVICE] MAC address: "));
    Serial.println(macAddr);
}

void printWebServiceInitialization() {
    Serial.println(F("[WEB SERVICE] HTTPS communication routine initialized"));
    Serial.print(F("[WEB SERVICE] Endpoint: "));
    Serial.println(serverURL);
    Serial.println(F("[WEB SERVICE] Server and database communication will be verified on the first upload"));
}


void printMeasurementDiagnostics(
    float voltaje,
    float corriente,
    float potencia,
    float energia,
    float Vavg,
    float Iavg,
    int minAdcV,
    int maxAdcV,
    int minAdcI,
    int maxAdcI
) {
    measurementNumber++;
    printSeparator();
    Serial.print(F("[MEASUREMENT] Averaged measurement No. "));
    Serial.println(measurementNumber);

    Serial.print(F("[VALUE] RMS voltage: "));
    Serial.print(voltaje, 3);
    Serial.println(F(" V"));
    Serial.print(F("[VALUE] RMS current: "));
    Serial.print(corriente, 4);
    Serial.println(F(" A"));
    Serial.print(F("[VALUE] Apparent power: "));
    Serial.print(potencia, 3);
    Serial.println(F(" VA"));
    Serial.print(F("[VALUE] Accumulated energy: "));
    Serial.print(energia, 6);
    Serial.println(F(" Wh"));

    Serial.print(F("[VALUE] Filtered average voltage component: "));
    Serial.println(Vavg, 6);
    Serial.print(F("[VALUE] Filtered average current component: "));
    Serial.println(Iavg, 6);

    Serial.print(F("[ADC] Voltage channel range: "));
    Serial.print(minAdcV);
    Serial.print(F(" to "));
    Serial.println(maxAdcV);
    Serial.print(F("[ADC] Current channel range: "));
    Serial.print(minAdcI);
    Serial.print(F(" to "));
    Serial.println(maxAdcI);

}

void sendToServer(
    uint64_t chipID,
    const String& macAddr,
    double voltaje,
    double corriente,
    double potencia,
    double energia
) {
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println(F("[Wi-Fi][WARNING] Connection lost. Attempting reconnection..."));
        conectarWiFi();
    }

    if (WiFi.status() != WL_CONNECTED) {
        Serial.println(F("[UPLOAD][ERROR] Data not sent because Wi-Fi is unavailable"));
        delay(5000);
        return;
    }

    WiFiClientSecure client;
    client.setInsecure();

    HTTPClient http;
    http.setTimeout(8000);

    if (!http.begin(client, serverURL)) {
        Serial.println(F("[UPLOAD][ERROR] HTTPS connection could not be initialized"));
        delay(5000);
        return;
    }

    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String postData;
    postData.reserve(220);
    postData = "_np=1";
    postData += "&api_key=";
    postData += apiKeyValue;
    postData += "&chipid=";
    postData += String((unsigned long long)chipID);
    postData += "&mac=";
    postData += macAddr;
    postData += "&voltaje=";
    postData += String(voltaje, 3);
    postData += "&corriente=";
    postData += String(corriente, 4);
    postData += "&potencia=";
    postData += String(potencia, 3);
    postData += "&energia=";
    postData += String(energia, 6);

    Serial.println(F("[UPLOAD] Sending measurement to the web application..."));
    int httpResponseCode = http.POST(postData);

    if (httpResponseCode > 0) {
        String response = http.getString();
        Serial.print(F("[UPLOAD] HTTP response code: "));
        Serial.println(httpResponseCode);
        Serial.print(F("[UPLOAD] Server response: "));
        Serial.println(response);

        if (httpResponseCode >= 200 && httpResponseCode < 300) {
            Serial.println(F("[UPLOAD] Web service and database upload verified successfully"));
        } else if (httpResponseCode == 401) {
            Serial.println(F("[UPLOAD][ERROR] API key rejected by the server"));
        } else if (httpResponseCode == 403) {
            Serial.println(F("[UPLOAD][ERROR] Request rejected by server security validation"));
        } else if (httpResponseCode >= 500) {
            Serial.println(F("[UPLOAD][ERROR] Internal server or database-processing error"));
        } else {
            Serial.println(F("[UPLOAD][WARNING] Server received the request but did not accept the reading"));
        }
    } else {
        Serial.print(F("[UPLOAD][ERROR] HTTPS request failed: "));
        Serial.println(http.errorToString(httpResponseCode));
    }

    http.end();
    delay(5000);
}

void setup() {
    Serial.begin(115200);
    delay(1000);

    printStartupHeader();
    chipID = ESP.getEfuseMac();

    analogReadResolution(12);
    printADCInitialization();
    printSensorInitialization();

    tiempoAnterior = millis();
    conectarWiFi();

    if (macAddr.length() == 0) {
        macAddr = WiFi.macAddress();
    }

    printDeviceInformation();
    printWebServiceInitialization();
    printSeparator();
    Serial.println(F("[SYSTEM] Startup initialization completed"));
    Serial.println(F("[SYSTEM] Beginning sensor acquisition and diagnostic reporting"));
    printSeparator();
}

void loop() {
    sumSqV = 0.0;
    sumSqI = 0.0;
    avgV = 0.0;
    avgI = 0.0;

    int minAdcV = resolucionADC;
    int maxAdcV = 0;
    int minAdcI = resolucionADC;
    int maxAdcI = 0;

    for (unsigned int n = 0; n < numMuestras; n++) {
        int lecturaADC_Tension = analogRead(pinTension);
        int lecturaADC_Corriente = analogRead(pinCorriente);

        if (lecturaADC_Tension < minAdcV) minAdcV = lecturaADC_Tension;
        if (lecturaADC_Tension > maxAdcV) maxAdcV = lecturaADC_Tension;
        if (lecturaADC_Corriente < minAdcI) minAdcI = lecturaADC_Corriente;
        if (lecturaADC_Corriente > maxAdcI) maxAdcI = lecturaADC_Corriente;

        offsetV += (lecturaADC_Tension - offsetV) / 512.0;
        float filteredV = lecturaADC_Tension - offsetV;
        offsetI += (lecturaADC_Corriente - offsetI) / 512.0;
        float filteredI = lecturaADC_Corriente - offsetI;

        float sqV = filteredV * filteredV;
        float sqI = filteredI * filteredI;
        sumSqV += sqV;
        sumSqI += sqI;
        avgV += filteredV;
        avgI += filteredI;
    }

    float rmsV = sqrt(sumSqV / numMuestras);
    float rmsI = sqrt(sumSqI / numMuestras);
    float Vavg = avgV / numMuestras;
    float Iavg = avgI / numMuestras;

    float Vrms = (rmsV * VCAL * (vRef / 1000.0));
    float Irms = rmsI * ICAL * (vRef / 1000.0);
    Vavg = Vavg * VCAL * (vRef / 1000.0);
    Iavg = Iavg * ICAL * (vRef / 1000.0);

    if (Vrms <= 5) Vrms *= 0.7185;
    else if (Vrms <= 10) Vrms *= 1.0391;
    else if (Vrms <= 19.79) Vrms *= 1.007;
    else if (Vrms <= 29.76) Vrms *= 1.0192;
    else if (Vrms <= 39.72) Vrms *= 1.0311;
    else if (Vrms <= 49.68) Vrms *= 1.0286;
    else if (Vrms <= 59.64) Vrms *= 1.0310;
    else if (Vrms <= 69.6) Vrms *= 1.0339;
    else if (Vrms <= 79.56) Vrms *= 1.0366;
    else if (Vrms <= 89.52) Vrms *= 1.0367;
    else if (Vrms <= 99.48) Vrms *= 1.0391;
    else if (Vrms <= 109.44) Vrms *= 1.0420;
    else if (Vrms <= 119.4) Vrms *= 1.0192;
    else if (Vrms <= 127) Vrms *= 1.0391;
    else if (Vrms > 127) Vrms *= 1.0414;

    if (Irms <= 0.245) Irms *= 0.6632;
    else if (Irms <= 0.493) Irms *= 0.8382;
    else if (Irms <= 0.737) Irms *= 0.8714;
    else if (Irms <= 0.981) Irms *= 0.8933;
    else if (Irms <= 1.472) Irms *= 0.9033;
    else if (Irms <= 1.964) Irms *= 0.9088;
    else if (Irms <= 2.471) Irms *= 0.9325;
    else if (Irms <= 2.96) Irms *= 0.9388;
    else if (Irms <= 3.463) Irms *= 0.9439;
    else if (Irms <= 3.961) Irms *= 0.9483;
    else if (Irms <= 4.463) Irms *= 0.9519;
    else if (Irms <= 4.961) Irms *= 0.9650;
    else if (Irms <= 5.463) Irms *= 0.9676;
    else if (Irms <= 5.961) Irms *= 0.9689;
    else if (Irms <= 6.463) Irms *= 0.9621;
    else if (Irms <= 6.8) Irms *= 0.9139;

    potenciaAparente = Vrms * Irms;
    potenciaActiva = potenciaAparente;

    unsigned long tiempoActual = millis();
    float tiempoTranscurridoHoras = (tiempoActual - tiempoAnterior) / 3600000.0;
    consumoEnergetico += potenciaActiva * tiempoTranscurridoHoras;
    tiempoAnterior = tiempoActual;

    acumuladorVrms += Vrms;
    acumuladorIrms += Irms;
    acumuladorVavg += Vavg;
    acumuladorIavg += Iavg;
    contadorPromedios++;

    if (contadorPromedios == numPromedios) {
        Vrms = acumuladorVrms / numPromedios;
        Irms = acumuladorIrms / numPromedios;
        Vavg = acumuladorVavg / numPromedios;
        Iavg = acumuladorIavg / numPromedios;

        acumuladorVrms = 0.0;
        acumuladorIrms = 0.0;
        acumuladorVavg = 0.0;
        acumuladorIavg = 0.0;
        contadorPromedios = 0;

        float voltaje = Vrms;
        float corriente = Irms;
        float potencia = potenciaAparente;
        float energia = consumoEnergetico;

        printMeasurementDiagnostics(
            voltaje, corriente, potencia, energia,
            Vavg, Iavg,
            minAdcV, maxAdcV, minAdcI, maxAdcI
        );

        sendToServer(chipID, macAddr, voltaje, corriente, potencia, energia);
    }
}
