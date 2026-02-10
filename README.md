# Sistema de Gestión Hotelera

Sistema completo de gestión hotelera desarrollado en PHP, MySQL, Bootstrap 5, HTML, CSS y JavaScript.

## Requisitos

- XAMPP (Apache + MySQL + PHP 7.4 o superior)
- Navegador web moderno (Chrome, Firefox, Edge)

## Instalación

### 1. Clonar o descargar el proyecto

Coloca la carpeta del proyecto en: `C:/xampp/htdocs/hotel-management/`

### 2. Crear la base de datos

1. Abre phpMyAdmin: `http://localhost/phpmyadmin/`
2. Crea una nueva base de datos llamada `hotel_management`
3. Importa el archivo `scripts/hotel_management_system.sql`

### 3. Configurar la conexión

Edita el archivo `config/database.php` con tus credenciales:

\`\`\`php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hotel_management');
\`\`\`

### 4. Iniciar XAMPP

1. Abre el panel de control de XAMPP
2. Inicia Apache
3. Inicia MySQL

### 5. Acceder al sistema

Abre tu navegador y ve a: `http://localhost/hotel-management/`

## Estructura del Proyecto

\`\`\`
hotel-management/
├── api/
│   └── endpoints/          # APIs REST para CRUD
│       ├── usuarios.php
│       ├── clientes.php
│       ├── habitaciones.php
│       ├── reservas.php
│       └── stats.php
├── assets/
│   ├── css/
│   │   └── style.css       # Estilos personalizados
│   └── js/
│       └── main.js         # Funciones JavaScript
├── config/
│   └── database.php        # Configuración de BD
├── includes/
│   ├── header.php          # Header HTML
│   ├── sidebar.php         # Menú lateral
│   └── footer.php          # Footer HTML
├── scripts/
│   └── hotel_management_system.sql  # Script de BD
├── index.php               # Dashboard principal
├── habitaciones.php        # Gestión de habitaciones
├── usuarios.php            # Gestión de usuarios
├── clientes.php            # Gestión de clientes
├── reservas.php            # Gestión de reservas
└── README.md
\`\`\`

## Características

### Módulos Principales

1. **Dashboard**
   - Estadísticas en tiempo real
   - Total de habitaciones y disponibles
   - Reservas activas
   - Total de clientes
   - Ingresos del mes
   - Actividad reciente

2. **Gestión de Habitaciones**
   - CRUD completo
   - Tipos: Simple, Doble, Suite, Presidencial
   - Estados: Disponible, Ocupada, Mantenimiento
   - Precio por noche
   - Capacidad de personas

3. **Gestión de Usuarios**
   - Crear, editar y eliminar usuarios
   - Roles: Admin, Recepcionista, Limpieza, Gerente
   - Estados: Activo, Inactivo
   - Autenticación con contraseña encriptada

4. **Gestión de Clientes**
   - Registro completo de clientes
   - Tipos de documento
   - Datos de contacto
   - Dirección completa

5. **Gestión de Reservas**
   - Crear nuevas reservas
   - Cálculo automático de precio
   - Estados: Pendiente, Confirmada, Cancelada, Completada
   - Validación de disponibilidad
   - Observaciones

### Características Técnicas

- **Responsive Design**: Compatible con móviles, tablets y desktop
- **Ajax**: Operaciones sin recargar la página
- **Bootstrap 5**: Interfaz moderna y profesional
- **Font Awesome**: Iconografía completa
- **jQuery**: Manejo simplificado del DOM
- **Notificaciones**: Alertas visuales de éxito/error
- **Validación**: Formularios validados en cliente y servidor
- **Soft Delete**: Los registros no se eliminan físicamente
- **Seguridad**: Prepared statements para prevenir SQL injection

## Usuarios de Prueba

Los siguientes usuarios están incluidos en el script SQL:

| Email | Contraseña | Rol |
|-------|------------|-----|
| admin@hotel.com | admin123 | Administrador |
| recepcion@hotel.com | recep123 | Recepcionista |
| gerente@hotel.com | gerente123 | Gerente |

## Soporte

Para problemas o consultas, revisa:
- La consola del navegador (F12) para errores JavaScript
- Los logs de Apache en `C:/xampp/apache/logs/error.log`
- Los logs de MySQL en `C:/xampp/mysql/data/mysql_error.log`

## Tecnologías Utilizadas

- PHP 7.4+
- MySQL 5.7+
- Bootstrap 5.3
- jQuery 3.6
- Font Awesome 6.4
- HTML5
- CSS3
- JavaScript ES6

## Licencia

Sistema desarrollado para fines educativos y comerciales.
