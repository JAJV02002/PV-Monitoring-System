# 🛠️ KiCad PCB Design

Editable circuit schematic and board layout for the monitoring unit.

## 📁 Project Files

- [monitoring-board.kicad_pro](monitoring-board.kicad_pro): project settings.
- [monitoring-board.kicad_sch](monitoring-board.kicad_sch): circuit schematic.
- [monitoring-board.kicad_pcb](monitoring-board.kicad_pcb): board layout.

## 🚀 Getting Started

1. Open `monitoring-board.kicad_pro` with KiCad 8.0 or a compatible newer version.
2. Inspect the schematic and board layout.
3. Use the [Excel BOM](../../bom/bill-of-materials.xlsx) for component planning.
4. Review the board revision and library assignments before editing or manufacturing.

## 🧩 Libraries and 3D Models

The schematic embeds symbol definitions, and the board contains its placed footprints. Separate custom libraries and individual component 3D models are not included.

Project-local model references use `${KIPRJMOD}/3dmodels/<filename>`. Place matching models there and install the standard KiCad 8 libraries for `${KICAD8_3DMODEL_DIR}` references. Check other relative model paths in the board settings.

## 🔗 Related Resources

- [Manufacturing files](../gerber/README.md)
- [PCB assembly STEP model](../3d/README.md)
- [Enclosure meshes](../../cad/README.md)
