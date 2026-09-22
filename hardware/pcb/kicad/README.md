# KiCad PCB project

Open [tarjeta_SolarVista.kicad_pro](tarjeta_SolarVista.kicad_pro) in KiCad. The supplied schematic and board identify KiCad 8.0 as their generator.

- [tarjeta_SolarVista.kicad_sch](tarjeta_SolarVista.kicad_sch): schematic.
- [tarjeta_SolarVista.kicad_pcb](tarjeta_SolarVista.kicad_pcb): board layout.
- [PCB BOM](../../bom/PCB_BOM.csv): component export from the project.
- [Gerber and drill archive](../gerber/Gerber_PCB_EMON.zip): supplied manufacturing outputs.
- [Assembly STEP model](../3d/tarjeta_SolarVista_STEP.zip): supplied 3D export.

## Libraries and models

The schematic contains embedded symbol definitions and the board contains its placed footprints. Custom library identifiers are retained. Separate custom symbol/footprint libraries and individual component 3D models were not included in the archive.

Computer-specific absolute 3D-model paths have been replaced with `${KIPRJMOD}/3dmodels/<filename>`. Place the corresponding models in that directory when available. Standard `${KICAD8_3DMODEL_DIR}` references and existing relative references are preserved. The assembly STEP export can be viewed independently.

Backup archives, footprint caches, `.kicad_prl` preferences and macOS metadata are excluded from the repository.
