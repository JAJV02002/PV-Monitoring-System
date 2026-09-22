# Database requirements

No SQL schema or migration files were included with the web application. Obtain the original schema before attempting to deploy this version. Do not publish a production dump containing accounts, password hashes, session tokens or measurement records.

The source references the following tables:

| Table | Referenced columns or role |
| --- | --- |
| `users` | `id`, `username`, `name`, `email`, `password`, `rol` |
| `dispositivos` | `id`, `id_chip`, `mac_address` |
| `matrices` | `id`, `id_admin_central`, `name` |
| `puntos` | `id`, `id_device`, `id_matriz`, `id_user`, `name`, `type` |
| `lecturas` | `id`, `id_punto`, `consumo_electrico`, `corriente_RMS`, `voltaje_RMS`, `potencia_aparente`, `fecha_captura` |
| `remember_tokens` | `id`, `user_id`, `selector`, `validator_hash`, `expires_at` |
| `invitaciones` | Used in matrix queries through `id_user` and `id_punto` |

This inventory is not an authoritative schema. Column types, indexes, relationships, defaults and constraints must be checked against the original database. Some older query paths also use `usuarios` and `lectura`; reconcile those names before deployment.

When the original structure is available, add a reviewed, structure-only `schema.sql` here. Keep real data and credentials outside the repository.
