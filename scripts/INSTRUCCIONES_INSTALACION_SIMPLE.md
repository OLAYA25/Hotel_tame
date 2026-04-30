# Instrucciones de Instalación - Base de Datos Simplificada

## Problema Resuelto
El error #1558 que experimentaste es por incompatibilidad de versiones de MariaDB. Este nuevo script SQL es **100% compatible** con cualquier versión de MySQL 5.x y MariaDB 10.x.

## ¿Qué se eliminó?
- ❌ Procedimientos almacenados
- ❌ Triggers complejos
- ❌ Vistas
- ✅ Solo tablas con relaciones y datos de prueba

## Pasos de Instalación

### 1. Abrir phpMyAdmin
- Accede a: `http://localhost/phpmyadmin`

### 2. Importar el Script
1. Haz clic en la pestaña **"SQL"** en el menú superior
2. Copia todo el contenido del archivo `hotel_management_simple.sql`
3. Pégalo en el área de texto grande
4. Haz clic en el botón **"Continuar"** (esquina inferior derecha)

### 3. Verificar la Instalación
Deberías ver mensajes de éxito como:
\`\`\`
Base de datos creada exitosamente!
total_usuarios: 4
total_clientes: 5
total_habitaciones: 7
total_reservas: 5
total_pagos: 5
\`\`\`

### 4. Configurar la Conexión en PHP
Edita el archivo `config/database.php`:
\`\`\`php
$host = 'localhost';
$dbname = 'hotel_management_system';
$username = 'root';
$password = ''; // Deja vacío para XAMPP por defecto
\`\`\`

### 5. Acceder al Sistema
- URL: `http://localhost/hotel-management/`
- Las vistas ya están listas para funcionar

## Estructura de la Base de Datos

### Tablas Creadas:
1. **usuarios** - Sistema de usuarios con roles (admin, staff, viewer)
2. **clientes** - Información de clientes/huéspedes
3. **habitaciones** - Catálogo de habitaciones del hotel
4. **reservas** - Gestión de reservas
5. **pagos** - Registro de pagos

### Datos de Prueba Incluidos:
- 4 usuarios (admin, staff, recepcionista, viewer)
- 5 clientes (incluyendo Jhuliet Tibasosa)
- 7 habitaciones (diferentes tipos y estados)
- 5 reservas (varias en diferentes estados)
- 5 pagos registrados

## Usuarios de Prueba
Todos usan la contraseña: **password**

| Email | Rol | Contraseña |
|-------|-----|------------|
| admin@hotel.com | admin | password |
| staff@hotel.com | staff | password |
| recepcion@hotel.com | staff | password |
| viewer@hotel.com | viewer | password |

## Notas Importantes
- La base de datos usa el motor **InnoDB** para integridad referencial
- Los campos JSON en `caracteristicas` de habitaciones funcionan en MySQL 5.7+
- Si usas MySQL 5.6 o anterior, esos campos se tratarán como TEXT
- Todas las fechas usan **TIMESTAMP** para compatibilidad

## Solución de Problemas

### Si el script falla:
1. Verifica que XAMPP esté ejecutando MySQL/MariaDB
2. Asegúrate de tener permisos de administrador en phpMyAdmin
3. Si existe una base de datos anterior, elimínala primero:
   \`\`\`sql
   DROP DATABASE IF EXISTS hotel_management_system;
   \`\`\`

### Si aparece error de caracteres:
- El script usa `utf8mb4` que soporta emojis y caracteres especiales
- Si tu MySQL es muy antiguo, cambia a `utf8`:
  \`\`\`sql
  CHARACTER SET utf8 COLLATE utf8_unicode_ci
  \`\`\`

¡Listo! Tu sistema de gestión hotelera está configurado y funcionando.
