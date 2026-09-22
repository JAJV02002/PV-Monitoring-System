# 🔧 Hardware

PCB designs, enclosure models and component information for the monitoring unit.

## 📁 Files and Directories

| Directory | Contents |
| --- | --- |
| [bom](bom/README.md) | Excel component, quantity and cost tables |
| [cad](cad/README.md) | Enclosure base and lid STL meshes |
| [pcb/kicad](pcb/kicad/README.md) | Editable project, schematic and board layout |
| [pcb/gerber](pcb/gerber/README.md) | Manufacturing layers and drill files |
| [pcb/3d](pcb/3d/README.md) | PCB assembly STEP model |

## 🚀 Build Preparation

1. Use [bill-of-materials.xlsx](bom/bill-of-materials.xlsx) to plan component purchases.
2. Open the KiCad project to inspect the schematic, footprints and board revision.
3. Review the manufacturing outputs before placing a PCB order.
4. Inspect the enclosure meshes and check their fit against the PCB assembly model.

## 📐 Board Details

The Gerber job specifies two copper layers, a 1.6 mm thickness and an approximate size of 100.4 × 70.4 mm. Check the intended revision and component compatibility before assembly.
