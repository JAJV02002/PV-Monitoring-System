# 🛠️ KiCad PCB Design

Editable schematic, board layout and project-local component libraries for the monitoring unit.

## 📁 Project Files

- [monitoring-board.kicad_pro](monitoring-board.kicad_pro): project settings.
- [monitoring-board.kicad_sch](monitoring-board.kicad_sch): circuit schematic.
- [monitoring-board.kicad_pcb](monitoring-board.kicad_pcb): board layout.
- [fp-lib-table](fp-lib-table): project footprint-library registration.
- [sym-lib-table](sym-lib-table): project symbol-library registration.

## 🧩 Component Libraries

| Directory | Contents |
| --- | --- |
| [footprints.pretty](footprints.pretty) | 19 custom footprints, including the two logos placed on the board |
| [symbols](symbols) | 7 custom symbol libraries referenced by the schematic |
| [3dmodels](3dmodels) | 16 component STEP models referenced by the board |

The component selection follows the PCB component references and its design BOM, with the two board-logo footprints included because they are part of the layout. Unused parts, plugins, caches and backups are excluded. The purchasing BOM is [EMON_BOM.xlsx](../../bom/EMON_BOM.xlsx).

## 🚀 Getting Started

1. Install KiCad 8.0 or a compatible newer version with its standard symbol, footprint and 3D-model libraries.
2. Open `monitoring-board.kicad_pro` from this directory.
3. The project library tables resolve custom components through `${KIPRJMOD}`; no personal library-folder path is required.
4. Inspect the schematic, board and 3D view before editing or manufacturing.

Standard KiCad components remain linked to the installed libraries. The custom models in `3dmodels/` use project-relative paths. Keep this directory structure intact when copying the project.

## 🔗 Related Resources

- [Bill of materials](../../bom/README.md)
- [Manufacturing files](../gerber/README.md)
- [PCB assembly STEP model](../3d/README.md)
- [Enclosure meshes](../../cad/README.md)
