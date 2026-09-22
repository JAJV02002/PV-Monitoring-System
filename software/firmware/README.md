# ESP32 firmware

This is the supplied `main_Monitoreo_SolarVista.cpp`, organized as a PlatformIO project with the source at `src/main.cpp`. Measurement routines and calibration coefficients are preserved. Deployment credentials and the server URL are read from `include/secrets.h`.

## Configuration

Copy the configuration template:

```sh
cp include/secrets.example.h include/secrets.h
```

Set `SERVER_URL` to your HTTPS endpoint ending in `/app/app.php`. Set `DEVICE_API_KEY` to the same value as the web application's `DEVICE_API_KEY`. Use a random hexadecimal key: the current firmware concatenates form fields without URL encoding, so characters such as `&`, `+` and `=` must not be used in that key.

Set `WIFI_SSID` and `WIFI_PASSWORD` for your local network. The source retains the original network-iteration routine with one configured entry; additional local networks can be added using macros defined only in the ignored `secrets.h` file.

Never commit `secrets.h`.

## Build and upload

The project targets an ESP32 development board (`esp32dev`) using the Arduino framework. Upload and serial-monitor speeds are both 115200 baud.

```sh
pio run
pio run --target upload
pio device monitor
```

PlatformIO installs the Espressif platform on the first build. The supplied Firebase dependency was removed because the source communicates directly with PHP using the ESP32 Wi-Fi and HTTP libraries and contains no Firebase calls. The platform version remains unpinned, as in the original configuration; a verified toolchain version is not yet recorded.

## Measurement procedure

1. Read voltage on GPIO 34 and current on GPIO 35 with 12-bit ADC resolution.
2. Collect 1,000 samples per block and update the dynamic offset estimates.
3. Accumulate squared offset-corrected samples and calculate RMS values.
4. Apply the existing calibration coefficients and piecewise corrections.
5. Compute apparent power for the current block as `Vrms × Irms`.
6. Integrate that power over elapsed time into an energy estimate in Wh, assuming unity power factor.
7. Average voltage and current over five blocks and transmit the resulting values.

The transmitted power is the value from the final block, not the product of the five-block average RMS values. Energy is accumulated each block and is held only in RAM. It resets at restart. The routine includes a five-second delay after an upload attempt; acquisition and networking add to that interval, so transmission is not on an exact fixed-period schedule.

The ADC offset filter removes the DC component. Calibration constants describe the supplied prototype and must be checked against the actual measurement hardware; they do not establish a measurement range or accuracy specification.

## Telemetry

The firmware sends `_np`, `api_key`, `chipid`, `mac`, `voltaje`, `corriente`, `potencia` and `energia` as form fields. See [the protocol description](../../docs/architecture.md).

The original HTTPS client calls `setInsecure()`, so it does not verify the server certificate. This behavior is preserved and requires attention before deployment on an untrusted network. An HTTP success response alone does not prove that a reading was stored: the server also uses response bodies for device registration and measurement-point status.

## Validation status

No ESP32 hardware test or PlatformIO build has been performed for this repository preparation. The original acquisition logic is preserved; end-to-end verification requires the board, measurement circuit, database schema and configured web server.
