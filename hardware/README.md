# Hardware

I keep the PCB design, enclosure models and bill of materials here. Use the Excel BOM to plan component purchases, the KiCad project to inspect the circuit, and the manufacturing files and enclosure meshes to prepare fabrication.

| Directory | Contents |
| --- | --- |
| [bom](bom/README.md) | Excel component, quantity and cost tables |
| [cad](cad/README.md) | STL files for the enclosure base and lid |
| [pcb/kicad](pcb/kicad/README.md) | Editable KiCad project, schematic and board layout |
| [pcb/gerber](pcb/gerber/README.md) | Gerber layers and drill files in a manufacturing archive |
| [pcb/3d](pcb/3d/README.md) | STEP export of the PCB assembly |

My Gerber job describes a two-layer, 1.6 mm board. I use [EMON_BOM.xlsx](bom/EMON_BOM.xlsx) as the bill of materials for this repository. Check component footprints and the intended board revision before assembly.
