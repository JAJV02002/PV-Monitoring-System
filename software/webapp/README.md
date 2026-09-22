# Monitoring web application

The application from `EMON_WebApp-main.zip` is preserved here as a single PHP/MySQL project. Controllers, models, views and static assets remain in their original directories. The application includes Spanish and English views, authentication, devices, matrices, measurement points, history, statistics and dashboard code.

## Requirements

- PHP with `mysqli` and session support. The source uses PHP 7.3-or-later language/API features; a supported PHP release should be validated for deployment.
- MySQL and a compatible database schema.
- Apache with `mod_rewrite`, `.htaccess` overrides enabled, and permission to apply the included access restrictions. `mod_headers` enables the supplied HTTP headers.
- An HTTPS endpoint reachable by the ESP32.

The archive does not include a database schema or migrations. Read [database requirements](database/README.md) before attempting deployment. No database dump or private readings are included.

## Local configuration

```sh
cp .env.example .env
```

Fill in these values for your own server:

| Variable | Purpose |
| --- | --- |
| `DB_HOST` | MySQL host; defaults to `localhost` |
| `DB_NAME` | Database name |
| `DB_USER` | Database user |
| `DB_PASSWORD` | Database password |
| `DEVICE_API_KEY` | Shared key used by the ESP32 ingestion endpoint |

Use a new random hexadecimal device key and copy the same value into the firmware's local `secrets.h`. Empty or missing device keys are rejected. Database credentials have no embedded deployment defaults.

`app/config.php` loads the optional `.env` file. Existing `$_ENV`, `$_SERVER` or process environment values take precedence. The file supports one `KEY=value` assignment per line, optional matching quotes and full-line `#` comments. It does not expand variables, process escape sequences, or support inline comments. The legacy `DB_PASS` variable is replaced by `DB_PASSWORD`.

Keep `.env` private. The included `.htaccess` denies HTTP access to dotfiles, local configuration and database exports. If another web server is used, equivalent restrictions are required; it will not read Apache rules.

## Apache setup

Point the virtual host document root to this `software/webapp` directory, not to the repository root. The application uses root-relative routes and expects to be hosted at `/`; serving it from a nested URL path requires routing changes.

Enable the rewrite rules and the included access restrictions, then configure HTTPS. Import the original schema into your database once it is available, and enter that database's credentials in the local configuration. The application timezone is currently `America/Mexico_City`, as in the supplied code.

Before use, resolve the source inconsistencies listed in [project status](../../docs/project-status.md), including view paths, inline scripts under the existing content-security policy, and database naming.

## Devices and measurements

The ESP32 posts to `/app/app.php` with `_np=1` and the shared device key. A previously unseen chip ID creates a device record. Assign the device to a measurement point before expecting readings to be recorded. Once a point is associated with the device, the ingestion path maps the submitted voltage, current, power and energy to the readings model.

See [architecture and field mapping](../../docs/architecture.md) for the protocol.

## Publication changes

- Replaced embedded database credentials and device keys with local configuration.
- Centralized device-key checks and reject empty keys.
- Require a valid key in the telemetry-identification helper.
- Disabled browser display of PHP errors in the request handler.
- Added Apache access restrictions for configuration and private artifacts.
- Removed `.DS_Store` and the temporary debug PHP script.

The broader authentication, SQL-query and dashboard implementation is preserved. This cleanup does not constitute a complete application security audit or deployment test. Known source issues are documented in [project status](../../docs/project-status.md).

## License

The original [GNU General Public License version 3](LICENSE) is retained unchanged. Third-party library copyright and license notices remain in the bundled assets.
