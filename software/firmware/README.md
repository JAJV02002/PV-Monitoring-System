# ESP32 firmware

I use this ESP32 firmware to acquire voltage and current signals, calculate electrical measurements and send them to the web application. The program is in `src/main.cpp`, and I keep network credentials and the server URL in the local `include/secrets.h` file.

## Configuration

Copy the configuration template:

```sh
cp include/secrets.example.h include/secrets.h
```

Set `SERVER_URL` to your HTTPS endpoint ending in `/app/app.php`. Set `DEVICE_API_KEY` to the same value as the web application's `DEVICE_API_KEY`. Use a random hexadecimal key: the current firmware concatenates form fields without URL encoding, so characters such as `&`, `+` and `=` must not be used in that key.

Set `WIFI_SSID` and `WIFI_PASSWORD` for your local network. The network-selection routine starts with one configured entry. To add networks, define their credentials in the ignored `secrets.h` file and add the corresponding entries to `knownNetworks`.

Never commit `secrets.h`.

## Build and upload

The project targets an ESP32 development board (`esp32dev`) using the Arduino framework. Upload and serial-monitor speeds are both 115200 baud.

```sh
pio run
pio run --target upload
pio device monitor
```

PlatformIO installs the Espressif platform on the first build. I send measurements directly to the PHP application using the ESP32 Wi-Fi and HTTP libraries. The platform version is not pinned in `platformio.ini`; record the version used for your build so you can reproduce it later.

## Measurement procedure

1. Read voltage on GPIO 34 and current on GPIO 35 with 12-bit ADC resolution.
2. Collect 1,000 samples per block and update the dynamic offset estimates.
3. Accumulate squared offset-corrected samples and calculate RMS values.
4. Apply the existing calibration coefficients and piecewise corrections.
5. Compute apparent power for the current block as `Vrms × Irms`.
6. Integrate that power over elapsed time into an energy estimate in Wh, assuming unity power factor.
7. Average voltage and current over five blocks and transmit the resulting values.

The transmitted power is the value from the final block, not the product of the five-block average RMS values. Energy is accumulated each block and is held only in RAM. It resets at restart. The routine includes a five-second delay after an upload attempt; acquisition and networking add to that interval, so transmission is not on an exact fixed-period schedule.

The ADC offset filter removes the DC component. I keep the calibration coefficients in `src/main.cpp`. Check and adjust them against your measurement hardware and a suitable reference instrument before using the readings.

## Telemetry

The firmware sends `_np`, `api_key`, `chipid`, `mac`, `voltaje`, `corriente`, `potencia` and `energia` as form fields. See [the protocol description](../../docs/architecture.md).

My current HTTPS client calls `setInsecure()`, so it does not verify the server certificate. Configure certificate verification before using it on an untrusted network. An HTTP success response alone does not prove that a reading was stored: the server also uses response bodies for device registration and measurement-point status.

## Checking your build

After uploading, open the serial monitor and check the Wi-Fi connection, device identifier and server response. Associate the device with a measurement point in the web application, then confirm that new readings appear in the database and dashboard. Compare voltage and current readings against your reference instrument before relying on the calibration.
