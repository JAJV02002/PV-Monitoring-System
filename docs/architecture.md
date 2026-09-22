# Architecture and telemetry

## Embedded acquisition

The ESP32 acquires two analog channels, applies offset removal and calibration, and reports RMS voltage and current. Voltage and current are averaged over five acquisition blocks. Power is the last block's apparent power; the energy accumulator integrates block-level apparent power with a unity-power-factor assumption.

## Transport

The firmware sends an HTTPS POST to `/app/app.php` with content type `application/x-www-form-urlencoded`.

| Field | Meaning | Server use |
| --- | --- | --- |
| `_np` | Device request marker, value `1` | Selects device ingestion |
| `api_key` | Shared deployment key | Compared with `DEVICE_API_KEY` |
| `chipid` | ESP32 eFuse MAC-derived numeric identifier | `dispositivos.id_chip` |
| `mac` | Wi-Fi MAC address | `dispositivos.mac_address` |
| `voltaje` | Averaged RMS voltage, V | `lecturas.voltaje_RMS` |
| `corriente` | Averaged RMS current, A | `lecturas.corriente_RMS` |
| `potencia` | Final-block apparent power, VA | `lecturas.potencia_aparente` |
| `energia` | Accumulated estimate, Wh | `lecturas.consumo_electrico` |

The body is form data, not JSON. Device identifiers describe the board; the shared key provides the endpoint's device authentication. The firmware currently disables certificate verification.

## Server flow

`app/app.php` validates the device key, then uses `MatrixController` to look up the chip ID. A new chip ID is inserted into `dispositivos`. An existing device must be linked to a record in `puntos`; only then does `ReadingController::captureReading()` store the measurement through the `lecturas` model.

The same request handler serves browser operations. Browser POST requests use a CSRF token. Session handling and remember-me support are defined in the authentication controller and autoloader.

## Storage and presentation

Models extend the `DB` class, which connects through `mysqli`. The web application keeps its PHP views, CSS and JavaScript under `resources/`. The front controller in `index.php` chooses Spanish or English views according to the URL prefix.

The archive contains query and model code, but no authoritative database definition. The tables and columns documented in [database requirements](../software/webapp/database/README.md) are observations from the source, not a replacement schema.
