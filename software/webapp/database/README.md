# 🗄️ Database Requirements

A standalone SQL schema and migrations are not yet published. The table inventory below describes the database structure referenced by the application and identifies the requirements for deployment. Keep accounts, password hashes, session tokens and measurement records out of any shared database export.

## 📊 Table Inventory

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

## 🛠️ Database Setup

This inventory is not an authoritative schema. Column types, indexes, relationships, defaults and constraints must be checked against the original database. Some older query paths also use `usuarios` and `lectura`; reconcile those names before deployment.

Use a compatible, structure-only schema when setting up your database. Keep real data and credentials outside the repository.
