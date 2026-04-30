Phase 2 - Migración de datos (Coherencia BD <-> Código)

- Objetivo: alinear datos entre BD y código existente para soportar soft delete, apellidos y URLs de imágenes.
- Archivos involucrados:
  - scripts/hotel_management_system.sql: ya contiene ALTERs de fase 1 para compatibilidad (deleted_at, apellido, imagen_url, precio_noche, etc.).
  - scripts/migrate_phase2.sql: script de migración de datos para Phase 2 (llenar apellidos, imagen_url).
  - frontend/views/public/home.php: usa imagen_url para imágenes de habitaciones destacadas.
  - frontend/views/public/portal_cliente.php: usa imagen_url para imágenes de habitación en reservas del cliente.
  - frontend/views/private/index.php: usa precio_total y fecha_creacion para cálculos/visualización (generated columns).
- Pasos de ejecución sugeridos:
  1) Realizar backup de la BD (ver comandos en la guía de ejecución).
  2) Ejecutar migrate_phase2.sql para rellenar apellidos e imágenes.
  3) Verificar los tres queries clave (usuarios, clientes, habitaciones) tras la migración.
  4) Probar las rutas públicas y la sección de administración para confirmar coherencia.

- Notas:
  - El script migrate_phase2.sql está pensado para su ejecución en una BD existente sin perder datos actuales; si ya tienes datos, revisa las sentencias de UPDATE para no sobrescribir valores deseados.
  - Si usas una base distinta a hotel_management_system, adapta el nombre en los comandos.
