# PV Monitoring System

I developed this photovoltaic monitoring system for my bachelor’s thesis. I use an ESP32 for electrical measurements and a PHP/MySQL application for wireless telemetry, data storage and a bilingual web dashboard. I share the hardware, firmware and setup instructions here so you can recreate the system and adapt it to your installation.

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
├── bom/                 Excel bill of materials
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

I developed the system as a research prototype. The application includes authentication, device registration, measurement-point management, historical queries and dashboard views. When recreating it, verify the calibration, electrical ranges and installation requirements against your hardware before collecting measurements.

## License

I distribute the web application under its existing [GNU GPL version 3 license](software/webapp/LICENSE). Third-party libraries retain their own notices. Separate licensing terms for the firmware, hardware and documentation are not yet specified.
