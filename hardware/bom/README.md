# 📋 Bill of Materials

[EMON_BOM.xlsx](EMON_BOM.xlsx) is the project's bill of materials. The tables below reproduce its component lists for convenient viewing on GitHub.

## 🧩 BOM Organization

The BOM is divided into **common components** and **configuration-specific components**:

- **Common components** form the core monitoring hardware and are shared across the sensing configurations.
- **Configuration-specific components** depend on the selected sensing arrangement. The workbook lists the additional parts for the high-precision configuration.

For a build, combine the common-component table with the parts for the chosen configuration. The workbook remains the editable reference for quantities, suppliers and costs.

## 🔧 Common Components

| Designator | Component / Part Number | Quantity | Unit Cost (USD) | Total Cost (USD) | Supplier |
| --- | --- | ---: | ---: | ---: | --- |
| Power_Ext_5V1 | 640456-2 (2-pin header) | 1 | 0.21 | 0.21 | Mouser |
| C1 | 1000 pF MLCC capacitor | 1 | 0.14 | 0.14 | Mouser |
| C2, C9 | 10 µF electrolytic capacitor | 2 | 0.57 | 1.14 | Mouser |
| C3, C4, C6, C8, C10, C11 | 0.1 µF tantalum capacitor | 6 | 1.16 | 6.96 | Mouser |
| C5, C7 | 200 pF MLCC capacitor | 2 | 0.62 | 1.24 | Mouser |
| C12, C14 | 33 nF MLCC capacitor | 2 | 0.16 | 0.32 | Mouser |
| C13 | 3.3 nF MLCC capacitor | 1 | 0.30 | 0.30 | Mouser |
| LED1, LED2 | Red SMD LED | 2 | 0.99 | 1.98 | Mouser |
| Terminal Block 1x3 | 3-pin screw terminal | 1 | 2.00 | 2.00 | Mouser |
| PS_HV1, PS_LV1 | IV0505S isolated DC/DC converter | 2 | 8.83 | 17.66 | Mouser |
| R1–R18, R_LED1, R_LED2 | Precision resistors (various values) | 20 | — | 8.52* | Mouser |
| TP_GND1–TP_Vy1 | Test points | 11 | 0.34 | 3.74 | Mouser |
| U2_Header | HDR100MET40F-G-V-TH female header | 2 | 3.50 | 7.00 | Mouser |
| U2 | ESP32-DEVKITC-32E | 1 | 13.26 | 13.26 | Mouser |

\* The resistor total is the combined cost of the different resistor values; no single unit price is specified.

## 🎯 Configuration-Specific Components

### High-Precision Sensing Configuration

| Designator | Component / Part Number | Quantity | Unit Cost (USD) | Total Cost (USD) | Supplier |
| --- | --- | ---: | ---: | ---: | --- |
| IC1 | HCPL-7800A-300E isolation amplifier | 1 | 14.64 | 14.64 | Mouser |
| IC2, IC3 | OP177GSZ precision operational amplifier | 2 | 4.08 | 8.16 | Mouser |
| U1 | HXS20-NP current transducer | 1 | 17.10 | 17.10 | Newark |

## 🚀 Purchasing and Assembly

1. Choose the sensing configuration for the build.
2. Use the common components together with the relevant configuration-specific parts.
3. Check footprints, electrical ratings and the intended PCB revision before ordering.
4. Confirm current supplier prices and availability; the listed USD costs are reference figures.

The KiCad project libraries follow the parts referenced by the board design. Selecting those library files does not substitute or modify entries in this Excel BOM.
