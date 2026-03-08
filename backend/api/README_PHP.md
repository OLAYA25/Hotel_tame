# API REST PHP - Sistema de Gestión Hotelera

## Configuración de la Base de Datos

1. Edita el archivo `config/database.php` con tus credenciales:

\`\`\`php
private $host = "localhost";        // Tu servidor MySQL
private $db_name = "hotel_management_system";  // Nombre de tu BD
private $username = "root";         // Tu usuario MySQL
private $password = "";             // Tu contraseña MySQL
\`\`\`

2. Asegúrate de haber importado el script SQL en phpMyAdmin (`scripts/hotel_management_system.sql`)

## Estructura de Archivos

\`\`\`
api/
├── config/
│   └── database.php          # Configuración de conexión a MySQL
├── models/
│   ├── Usuario.php           # Modelo de Usuarios
│   ├── Cliente.php           # Modelo de Clientes
│   ├── Habitacion.php        # Modelo de Habitaciones
│   └── Reserva.php           # Modelo de Reservas
├── endpoints/
│   ├── usuarios.php          # API de Usuarios
│   ├── clientes.php          # API de Clientes
│   ├── habitaciones.php      # API de Habitaciones
│   ├── reservas.php          # API de Reservas
│   └── stats.php             # API de Estadísticas
└── README_PHP.md
\`\`\`

## Endpoints Disponibles

### Usuarios
- **GET** `/api/endpoints/usuarios.php` - Obtener todos los usuarios
- **GET** `/api/endpoints/usuarios.php?id=1` - Obtener usuario por ID
- **POST** `/api/endpoints/usuarios.php` - Crear usuario
- **PUT** `/api/endpoints/usuarios.php` - Actualizar usuario
- **DELETE** `/api/endpoints/usuarios.php` - Eliminar usuario

### Clientes
- **GET** `/api/endpoints/clientes.php` - Obtener todos los clientes
- **GET** `/api/endpoints/clientes.php?id=1` - Obtener cliente por ID
- **POST** `/api/endpoints/clientes.php` - Crear cliente
- **PUT** `/api/endpoints/clientes.php` - Actualizar cliente
- **DELETE** `/api/endpoints/clientes.php` - Eliminar cliente

### Habitaciones
- **GET** `/api/endpoints/habitaciones.php` - Obtener todas las habitaciones
- **GET** `/api/endpoints/habitaciones.php?id=1` - Obtener habitación por ID
- **GET** `/api/endpoints/habitaciones.php?disponibles=true` - Obtener habitaciones disponibles
- **POST** `/api/endpoints/habitaciones.php` - Crear habitación
- **PUT** `/api/endpoints/habitaciones.php` - Actualizar habitación
- **DELETE** `/api/endpoints/habitaciones.php` - Eliminar habitación

### Reservas
- **GET** `/api/endpoints/reservas.php` - Obtener todas las reservas
- **GET** `/api/endpoints/reservas.php?id=1` - Obtener reserva por ID
- **GET** `/api/endpoints/reservas.php?recent=true&limit=10` - Obtener reservas recientes
- **POST** `/api/endpoints/reservas.php` - Crear reserva (verifica disponibilidad)
- **PUT** `/api/endpoints/reservas.php` - Actualizar reserva
- **DELETE** `/api/endpoints/reservas.php` - Eliminar reserva

### Estadísticas
- **GET** `/api/endpoints/stats.php` - Obtener estadísticas del dashboard

## Ejemplos de Uso

### Crear un Cliente (POST)
\`\`\`bash
curl -X POST http://localhost/api/endpoints/clientes.php \
-H "Content-Type: application/json" \
-d '{
  "nombre": "Juan Pérez",
  "email": "juan@email.com",
  "telefono": "+123456789",
  "documento": "12345678",
  "direccion": "Calle Principal 123"
}'
\`\`\`

### Crear una Reserva (POST)
\`\`\`bash
curl -X POST http://localhost/api/endpoints/reservas.php \
-H "Content-Type: application/json" \
-d '{
  "cliente_id": 1,
  "habitacion_id": 2,
  "fecha_entrada": "2024-12-20",
  "fecha_salida": "2024-12-25",
  "estado": "confirmada",
  "total": 2500,
  "metodo_pago": "Tarjeta de crédito",
  "noches": 5
}'
\`\`\`

### Obtener Estadísticas (GET)
\`\`\`bash
curl http://localhost/api/endpoints/stats.php
\`\`\`

## Características de Seguridad

- **Validación de datos**: Todos los inputs son sanitizados
- **Prepared statements**: Prevención de SQL Injection
- **Soft Delete**: Los registros no se eliminan físicamente
- **Password hashing**: Contraseñas encriptadas con bcrypt
- **CORS habilitado**: Para integración con frontend

## Notas Importantes

1. Los endpoints usan **soft delete** - los registros se marcan como eliminados pero permanecen en la BD
2. Las contraseñas se hashean automáticamente con `password_hash()`
3. Todas las respuestas son en formato JSON
4. Los códigos HTTP siguen estándares REST (200, 201, 400, 404, 503)
5. La verificación de disponibilidad de habitaciones es automática al crear reservas
