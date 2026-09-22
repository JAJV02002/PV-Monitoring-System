# Project status and limitations

## Included material

- The supplied ESP32 C++ source, renamed to `software/firmware/src/main.cpp`.
- The supplied PlatformIO configuration, with its unused Firebase dependency removed.
- The PHP/MySQL web application, preserving its MVC-style structure and GPL license.
- Local configuration templates, repository ignore rules and setup documentation.

## Material still required

The source package does not contain a database schema, SQL migrations, a bill of materials, CAD files, KiCad designs, an assembly guide or measurement-validation results. Hardware directories are reserved for the actual project files; no example components or invented designs are included.

The repository therefore does not yet provide everything needed to reproduce a complete installation.

## Measurement limitations

The firmware removes the DC component before calculating RMS values. It calculates apparent power, assumes unity power factor for the energy estimate, and does not persist energy across restarts. The transmitted power comes from the last acquisition block, while voltage and current are averaged over five blocks. Calibration constants alone do not establish accuracy, supported ranges or electrical safety ratings.

## Web application findings

The following issues are visible in the supplied source and are not resolved by credential cleanup:

- Some front-controller view paths do not match the files in the archive. For example, the `/matrices` route points to `sections/matrices/matrices`, while the supplied view is `sections/matrices.php`; the matrix-detail filename is `mariz.php`.
- Query code refers to both `users` and legacy `usuarios` naming, and to both `lecturas` and `lectura`. The database schema is needed to reconcile these paths.
- User passwords use SHA-1, and parts of the query builder interpolate values into SQL. These require a deliberate authentication migration and parameterized queries before a public deployment.
- Authorization needs review for user/profile, reading and mutation endpoints; a valid session or CSRF token alone is not a complete ownership or role check.
- The existing content-security policy blocks inline scripts, while several views contain inline JavaScript. That mismatch needs validation and correction before the interface can be considered deployment-ready.

The firmware retains its original disabled TLS certificate verification. Publication of source code is separate from operating the application as an Internet-facing service.

## Release verification

Credential cleanup is checked against the original uploaded source values, and the original web application license is compared byte-for-byte. Repository layout, documentation links and excluded private artifacts are checked before publication.

All 72 PHP files passed a PHP 8.3 syntax-parser check. This is a static syntax check, not a PHP runtime or database integration test.

No complete database-backed deployment, ESP32 build, hardware test or measurement-accuracy test has been performed for this release. Those checks require the missing schema, a configured toolchain and the actual measurement hardware.
