# 🌐 Monitoring Web Application

A PHP/MySQL application for receiving ESP32 measurements and displaying them in Spanish and English dashboards. Controllers, models and views support authentication, device and measurement-point management, historical readings and statistics.

## 📋 Requirements

- PHP with `mysqli` and session support. The source uses PHP 7.3-or-later language/API features; a supported PHP release should be validated for deployment.
- MySQL and a compatible database schema.
- Apache with `mod_rewrite`, `.htaccess` overrides enabled, and permission to apply the included access restrictions. `mod_headers` enables the configured HTTP headers.
- An HTTPS endpoint reachable by the ESP32.

The standalone database schema and migrations are not yet published. Read [database requirements](database/README.md) before deployment; a compatible database structure is required to run the application.

## ⚙️ Local Configuration

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

`app/config.php` loads the optional `.env` file. Existing `$_ENV`, `$_SERVER` or process environment values take precedence. The file supports one `KEY=value` assignment per line, optional matching quotes and full-line `#` comments. It does not expand variables, process escape sequences, or support inline comments. Use `DB_PASSWORD` for the database password.

Keep `.env` private. The included `.htaccess` denies HTTP access to dotfiles, local configuration and database exports. If another web server is used, equivalent restrictions are required; it will not read Apache rules.

## 🛠️ Apache Setup

Point the virtual host document root to this `software/webapp` directory, not to the repository root. The application uses root-relative routes and expects to be hosted at `/`; serving it from a nested URL path requires routing changes.

Enable the rewrite rules and the included access restrictions, then configure HTTPS. Create a compatible database using the schema when it is available, and enter its credentials in the local configuration. The application timezone is set to `America/Mexico_City`; adjust it in `app/api.php` if your installation uses a different timezone.

## 📡 Devices and Measurements

The ESP32 posts to `/app/api.php` with `_np=1` and the shared device key. A previously unseen chip ID creates a device record. Assign the device to a measurement point before expecting readings to be recorded. Once a point is associated with the device, the ingestion path maps the submitted voltage, current, power and energy to the readings model.

See [architecture and field mapping](../../docs/architecture.md) for the protocol.

## ✅ Checking Your Installation

Test the complete measurement path before using the dashboard: authenticate, register a device, associate it with a measurement point, send a reading and confirm that it appears in the history view. Keep database credentials and device keys in local configuration, and check server logs when a request fails. Review access control and database queries before exposing your installation to the Internet.

## 📝 License

This application is distributed under the [GNU General Public License version 3](LICENSE). Third-party libraries retain their own copyright and license notices.

## 📁 File Naming

Source filenames are in English. Model-class mappings in `app/autoloader.php` retain the existing database table names, and browser routes remain compatible with the Spanish and English interfaces.
