# PV Monitoring System

An ESP32 measurement system and PHP/MySQL monitoring platform developed for a bachelor’s thesis on photovoltaic monitoring. The project brings together embedded acquisition, wireless telemetry, and a bilingual web interface for viewing electrical measurements.

## Overview

The ESP32 samples voltage and current signals on GPIO 34 and GPIO 35, removes their offsets, calculates calibrated RMS values, and sends measurements to the web application over Wi-Fi. The PHP application manages users, devices, matrices and measurement points, and stores readings in MySQL through `mysqli`.

```text
Voltage and current signals
           │
           ▼
 ESP32 · Arduino / C++
 Sampling · RMS · Calibration
           │
           │ HTTPS form POST
           ▼
 PHP application ── mysqli ── MySQL
 Controllers · Models · Views
           │
           ▼
 Web dashboard · Spanish / English
 Historical readings · Charts · Statistics
```

The included firmware processes offset-corrected waveforms. It should not be interpreted as a verified direct-DC photovoltaic measurement implementation.

## Repository layout

```text
hardware/
├── bom/                 Excel workbook and PCB BOM export
├── cad/                 Enclosure base and lid STL files
└── pcb/
    ├── kicad/          KiCad schematic and board layout
    ├── gerber/         Gerber and drill archive
    └── 3d/             PCB assembly STEP archive
software/
├── firmware/
│   ├── src/main.cpp     ESP32 firmware
│   ├── include/         Local configuration template
│   └── platformio.ini   PlatformIO project
└── webapp/
    ├── app/            PHP controllers, models and request handling
    ├── resources/      Views, styles and JavaScript
    ├── index.php       Web entry point
    └── .htaccess       Apache configuration
docs/                   Architecture and setup documentation
```

## Included software

| Component | Implementation |
| --- | --- |
| Firmware | C++ with the Arduino framework, targeting PlatformIO `esp32dev` |
| Acquisition | 12-bit ADC; 1,000 samples per block; five-block RMS averaging |
| Telemetry | HTTPS POST using `application/x-www-form-urlencoded` |
| Server | PHP with an MVC-style application structure |
| Database access | MySQL through `mysqli` |
| Interface | PHP/HTML, CSS, JavaScript, Bootstrap, Chart.js, jQuery and Popper |
| Languages | Spanish and English views |

Power is calculated as `Vrms × Irms` (apparent power). The accumulated energy estimate assumes unity power factor and resets when the ESP32 restarts. See the [firmware documentation](software/firmware/README.md) for the exact averaging and timing behavior.

## Getting started

1. Review the [hardware files](hardware/README.md): BOM tables, KiCad design, manufacturing outputs and enclosure meshes.
2. Configure the [PHP web application](software/webapp/README.md) with your own database and device key. A compatible database schema is required before the application can be used.
3. Copy `software/firmware/include/secrets.example.h` to `software/firmware/include/secrets.h`, then set your Wi-Fi credentials, server endpoint and matching device key.
4. From `software/firmware`, build and upload with PlatformIO:

   ```sh
   pio run
   pio run --target upload
   pio device monitor
   ```

Local credentials, database dumps and build output are excluded by `.gitignore`.

## Documentation

- [System architecture and telemetry](docs/architecture.md)
- [Firmware configuration and measurement procedure](software/firmware/README.md)
- [Web application configuration](software/webapp/README.md)
- [Database requirements](software/webapp/database/README.md)
- [Hardware files](hardware/README.md)

## Project scope

This repository preserves a research prototype. The supplied code includes authentication, device registration, measurement-point management, historical queries and dashboard views; these are source-level capabilities, not a claim of a fully tested deployment. Measurement accuracy, supported electrical ranges and hardware safety ratings are not established by the source files alone.

## License

The web application retains its existing [GNU GPL version 3 license](software/webapp/LICENSE). Bundled libraries retain their own notices. No additional license is assigned here to the firmware, hardware or documentation; those terms remain to be specified by their rights holders.
