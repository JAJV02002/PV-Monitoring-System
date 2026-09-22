# ☀️ PV Monitoring System

A photovoltaic monitoring project combining an ESP32 measurement unit, a PHP/MySQL web application, PCB designs and a printable enclosure. Developed for a bachelor’s thesis, the repository brings together the hardware and software resources for studying and recreating the system.

![Project Type](https://img.shields.io/badge/project-research%20prototype-blue)
![Firmware](https://img.shields.io/badge/firmware-ESP32-orange)
![Web Application License](https://img.shields.io/badge/web%20app%20license-GPL--3.0-lightblue)

## 🌟 Features

- **Electrical measurements:** RMS voltage, RMS current, apparent power and an accumulated energy estimate.
- **Wi-Fi telemetry:** ESP32 measurements sent to the web application using HTTPS form requests.
- **Web dashboard:** Spanish and English interfaces with charts and historical readings.
- **Device management:** Devices grouped into matrices and measurement points.
- **Hardware resources:** KiCad schematic and board layout, manufacturing outputs and a PCB assembly model.
- **Printable enclosure:** Separate STL files for the base and lid.

## 📁 Repository Structure

```text
PV-Monitoring-System/
├── hardware/
│   ├── bom/
│   │   └── bill-of-materials.xlsx
│   ├── cad/
│   │   ├── enclosure-base.stl
│   │   └── enclosure-lid.stl
│   └── pcb/
│       ├── kicad/
│       │   ├── monitoring-board.kicad_pro
│       │   ├── monitoring-board.kicad_sch
│       │   └── monitoring-board.kicad_pcb
│       ├── gerber/pcb-manufacturing.zip
│       └── 3d/pcb-assembly.zip
├── software/
│   ├── firmware/
│   │   ├── src/main.cpp
│   │   ├── include/secrets.example.h
│   │   └── platformio.ini
│   └── webapp/
│       ├── app/
│       ├── resources/
│       ├── index.php
│       └── .env.example
└── docs/
    └── architecture.md
```

Each component directory includes its own README with configuration and usage instructions.

## 🚀 Quick Start

### 1. Prepare the Hardware

1. Review the [Excel bill of materials](hardware/bom/bill-of-materials.xlsx).
2. Open the [KiCad project](hardware/pcb/kicad/README.md) and inspect the circuit and board layout.
3. Review the [manufacturing files](hardware/pcb/gerber/README.md) before ordering a PCB.
4. Inspect and print the [enclosure base and lid](hardware/cad/README.md), checking scale and fit against the board.

### 2. Configure the Web Application

Set up Apache, PHP with `mysqli`, and MySQL. Follow the [web application guide](software/webapp/README.md) to configure the document root, HTTPS and database connection.

```sh
cd software/webapp
cp .env.example .env
```

Enter the database credentials and a random hexadecimal device key in `.env`. A compatible database schema is required; the standalone schema is not yet published. See [database requirements](software/webapp/database/README.md).

### 3. Configure and Upload the Firmware

```sh
cd software/firmware
cp include/secrets.example.h include/secrets.h
```

Set the Wi-Fi credentials, HTTPS endpoint and matching device key in `secrets.h`, then build and upload:

```sh
pio run
pio run --target upload
pio device monitor
```

### 4. Connect the Measurement Point

Register the device with the web application, associate it with a measurement point, and confirm that readings appear in the database and dashboard. Check calibration against a suitable reference instrument.

## 📊 System Architecture

```text
Voltage and current signals
            │
            ▼
      ESP32 + sensors
  Sampling · RMS · Calibration
            │
            │ Wi-Fi / HTTPS form POST
            ▼
      PHP web application
    Controllers · Models · Views
            │
            ├── MySQL measurement storage
            │
            ▼
       Web dashboard
   Live views · History · Charts
```

## 🔧 Hardware

The Excel BOM contains core components and a high-precision sensing configuration, including the ESP32-DEVKITC-32E, HCPL-7800A-300E, OP177GSZ and HXS20-NP. Use the workbook as the component list and check compatibility with the intended PCB revision before assembly.

The manufacturing job describes a **two-layer, 1.6 mm board**, approximately **100.4 × 70.4 mm**. The enclosure is available as STL meshes, and the PCB assembly is available as a STEP model.

## 💻 Software Stack

| Component | Technology |
| --- | --- |
| Firmware | C++ / Arduino framework / PlatformIO |
| Microcontroller target | ESP32 development board (`esp32dev`) |
| Backend | PHP with an MVC-style structure |
| Database | MySQL through `mysqli` |
| Web server | Apache |
| Frontend | PHP views, HTML, CSS and JavaScript |
| Interface libraries | Bootstrap, Chart.js, jQuery and Popper |
| Telemetry | HTTPS with URL-encoded form fields |

## 📈 Measurement Details

- **ADC inputs:** GPIO 34 for voltage and GPIO 35 for current.
- **Sampling:** 12-bit resolution, 1,000 samples per block.
- **Averaging:** Voltage and current averaged over five blocks.
- **Power:** Apparent power calculated as `Vrms × Irms`.
- **Energy:** Time-integrated estimate assuming unity power factor; resets at restart.

The offset filter removes the DC component. Calibration and supported electrical ranges must be verified for the measurement hardware. See the [firmware guide](software/firmware/README.md) for timing and power-averaging details.

## 📖 Documentation

- [Hardware guide](hardware/README.md)
- [Bill of materials](hardware/bom/README.md)
- [Firmware setup](software/firmware/README.md)
- [Web application setup](software/webapp/README.md)
- [Database requirements](software/webapp/database/README.md)
- [Architecture and telemetry](docs/architecture.md)

## 📝 License

The web application is distributed under its existing [GNU GPL version 3 license](software/webapp/LICENSE). Third-party libraries retain their own notices. Separate licensing terms for the hardware, firmware and documentation are not yet specified.

## 🤝 Contributing

Improvements to documentation, reproducibility, hardware integration and software testing are welcome. Use repository issues to describe a problem or propose a change, and include the board revision and relevant configuration when reporting results.

## 🎓 Educational Use

The project supports study of embedded acquisition, electrical measurement, wireless telemetry and web-based monitoring in renewable-energy research.

## 🙏 Acknowledgments

- ESP32 and Arduino communities
- KiCad development community
- Bootstrap, Chart.js and other third-party library contributors

---

**Made with ❤️ for renewable energy monitoring**
