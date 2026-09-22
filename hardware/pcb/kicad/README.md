# KiCad PCB project

I maintain the circuit schematic and PCB layout in this KiCad project. Open [tarjeta_SolarVista.kicad_pro](tarjeta_SolarVista.kicad_pro) with KiCad 8.0 or a compatible newer version to inspect or modify the design.

- [tarjeta_SolarVista.kicad_sch](tarjeta_SolarVista.kicad_sch): schematic.
- [tarjeta_SolarVista.kicad_pcb](tarjeta_SolarVista.kicad_pcb): board layout.
- [Excel BOM](../../bom/EMON_BOM.xlsx): the project bill of materials.
- [Gerber and drill archive](../gerber/Gerber_PCB_EMON.zip): manufacturing outputs.
- [Assembly STEP model](../3d/tarjeta_SolarVista_STEP.zip): PCB assembly export.

## Libraries and models

The schematic contains embedded symbol definitions, and the board contains its placed footprints. Separate custom symbol and footprint libraries and individual component 3D models are not available in this directory. When editing the design, check the library assignments before replacing symbols or footprints.

I use `${KIPRJMOD}/3dmodels/<filename>` for project-local component models. Place matching models in that directory and install the standard KiCad 8 libraries for `${KICAD8_3DMODEL_DIR}` references. Check any other relative model paths in the board settings. You can view the assembly STEP export independently.

Use the [Excel BOM](../../bom/EMON_BOM.xlsx) when preparing the component list for your build.
