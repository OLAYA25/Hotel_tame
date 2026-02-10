# Instrucciones de Instalación de Base de Datos

## Guía para Importar en phpMyAdmin

### Paso 1: Acceder a phpMyAdmin
1. Abre tu navegador web
2. Accede a phpMyAdmin (generalmente en `http://localhost/phpmyadmin`)
3. Inicia sesión con tus credenciales de MySQL

### Paso 2: Método de Importación

#### Opción A: Importar el archivo SQL completo
1. En phpMyAdmin, haz clic en la pestaña **"Importar"** en el menú superior
2. Haz clic en el botón **"Seleccionar archivo"**
3. Selecciona el archivo `hotel_management_system.sql`
4. Asegúrate de que el formato esté configurado como **SQL**
5. Haz clic en el botón **"Continuar"** al final de la página
6. Espera a que la importación se complete

#### Opción B: Ejecutar el script manualmente
1. Abre el archivo `hotel_management_system.sql` en un editor de texto
2. Copia todo el contenido del archivo
3. En phpMyAdmin, haz clic en la pestaña **"SQL"**
4. Pega el contenido en el área de texto
5. Haz clic en el botón **"Continuar"**

### Paso 3: Verificar la Instalación

1. En el panel izquierdo de phpMyAdmin, deberías ver la base de datos **"hotel_management_system"**
2. Haz clic en ella para expandir y ver todas las tablas:
   - `usuarios` (4 registros)
   - `clientes` (5 registros)
   - `habitaciones` (8 registros)
   - `reservas` (5 registros)
   - `pagos` (4 registros)
   - `servicios_adicionales` (6 registros)
   - `reserva_servicios` (5 registros)
   - `mantenimiento` (2 registros)
   - `auditoria` (vacía inicialmente)

### Paso 4: Verificar Datos de Prueba

Ejecuta estas consultas SQL en phpMyAdmin para verificar:

\`\`\`sql
-- Ver todas las habitaciones
SELECT * FROM habitaciones;

-- Ver reservas con información completa
SELECT * FROM vista_reservas_completa;

-- Ver estadísticas del dashboard
SELECT * FROM vista_estadisticas_dashboard;

-- Ver usuarios del sistema
SELECT id, nombre, email, rol, activo FROM usuarios;
\`\`\`

## Estructura de la Base de Datos

### Tablas Principales

1. **usuarios**: Gestiona el personal del hotel
   - Roles: admin, staff, viewer
   - Contraseñas hasheadas con bcrypt

2. **clientes**: Información de huéspedes
   - Datos personales y de contacto
   - Documento único

3. **habitaciones**: Inventario de habitaciones
   - Estados: disponible, ocupada, mantenimiento, reservada
   - Tipos: simple, doble, suite, presidencial

4. **reservas**: Gestión de reservas
   - Estados: confirmada, pendiente, cancelada, completada
   - Relaciones con clientes y habitaciones

5. **pagos**: Registro de transacciones
   - Métodos de pago diversos
   - Estados de pago

6. **servicios_adicionales**: Catálogo de servicios
   - Desayunos, spa, tours, etc.

7. **reserva_servicios**: Servicios contratados por reserva

8. **mantenimiento**: Control de mantenimiento

9. **auditoria**: Registro de cambios (automático con triggers)

### Procedimientos Almacenados

- `sp_crear_reserva()`: Crea reserva y actualiza habitación
- `sp_cancelar_reserva()`: Cancela y libera habitación
- `sp_check_in()`: Procesa entrada del cliente
- `sp_check_out()`: Procesa salida del cliente

### Vistas

- `vista_reservas_completa`: Reservas con toda la información
- `vista_estadisticas_dashboard`: Métricas del sistema
- `vista_habitaciones_disponibles`: Habitaciones libres

## Configuración en el Proyecto

Para conectar la aplicación con esta base de datos, necesitarás configurar:

### Variables de Entorno

Crea un archivo `.env.local` en la raíz del proyecto:

\`\`\`env
# Base de datos MySQL
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=hotel_management_system
DB_USERNAME=root
DB_PASSWORD=tu_password
\`\`\`

## Solución de Problemas

### Error: "MySQL server has gone away"
- Aumenta el valor de `max_allowed_packet` en tu configuración de MySQL
- En phpMyAdmin: SQL > `SET GLOBAL max_allowed_packet=67108864;`

### Error: "Access denied"
- Verifica que tu usuario tenga permisos en la base de datos
- Ejecuta: `GRANT ALL PRIVILEGES ON hotel_management_system.* TO 'tu_usuario'@'localhost';`

### Tablas no se crean
- Asegúrate de que el charset sea `utf8mb4`
- Verifica que MySQL sea versión 5.7+ o MariaDB 10.3+

## Datos de Prueba

### Usuarios del Sistema
- **Admin**: admin@hotel.com
- **Staff**: maria@hotel.com, carlos@hotel.com
- **Viewer**: ana@hotel.com
- Password por defecto: `hotel123` (cambiar en producción)

### Habitaciones de Ejemplo
- 101: Suite ($450,000/noche) - Ocupada
- 102: Simple ($150,000/noche) - Ocupada
- 202: Simple ($130,000/noche) - Disponible
- 301: Presidencial ($800,000/noche) - Disponible

## Mantenimiento

### Respaldo Regular
\`\`\`sql
-- Exportar base de datos
mysqldump -u root -p hotel_management_system > backup_$(date +%Y%m%d).sql

-- Restaurar desde backup
mysql -u root -p hotel_management_system < backup_20241213.sql
\`\`\`

### Limpiar Datos de Prueba
\`\`\`sql
-- Eliminar solo datos de prueba (mantiene estructura)
TRUNCATE TABLE reserva_servicios;
TRUNCATE TABLE pagos;
TRUNCATE TABLE reservas;
TRUNCATE TABLE mantenimiento;
TRUNCATE TABLE clientes;
-- Las habitaciones y usuarios se pueden mantener
\`\`\`

## Seguridad

### Recomendaciones Importantes

1. **Cambiar contraseñas**: Las contraseñas de ejemplo están sin hashear
2. **Crear usuario específico**: No usar root en producción
3. **Limitar permisos**: Solo los necesarios para la aplicación
4. **Habilitar SSL**: Para conexiones a la base de datos
5. **Actualizar regularmente**: Mantener MySQL actualizado

## Soporte

Si encuentras problemas:
1. Verifica los logs de MySQL
2. Revisa la configuración de phpMyAdmin
3. Consulta la documentación técnica del proyecto
