# Instrucciones de Instalación - Sistema de Gestión Hotelera

## Requisitos Previos
- XAMPP instalado (Apache + MySQL)
- Navegador web moderno

## Pasos de Instalación

### 1. Preparar el Proyecto
- Copia la carpeta del proyecto a: `C:\xampp\htdocs\hotelsito\`

### 2. Iniciar XAMPP
- Abre el Panel de Control de XAMPP
- Inicia los servicios:
  - Apache ✓
  - MySQL ✓

### 3. Crear la Base de Datos

#### Opción A: Usando phpMyAdmin (Recomendado)
1. Abre tu navegador y ve a: `http://localhost/phpmyadmin`
2. Haz clic en la pestaña **SQL** en el menú superior
3. Copia TODO el contenido del archivo: `scripts/hotel_management_simple.sql`
4. Pégalo en el área de texto
5. Haz clic en el botón **Continuar** o **Go**
6. Espera a que termine (debería decir "X filas afectadas")

#### Opción B: Importar archivo SQL
1. En phpMyAdmin, haz clic en **Importar**
2. Haz clic en **Seleccionar archivo**
3. Busca y selecciona: `scripts/hotel_management_simple.sql`
4. Haz clic en **Continuar**

### 4. Verificar la Instalación
1. En phpMyAdmin, verifica que aparezca la base de datos: **hotel_management_system**
2. Haz clic en ella y verifica que contenga 5 tablas:
   - usuarios
   - clientes
   - habitaciones
   - reservas
   - pagos

### 5. Acceder al Sistema
- Abre tu navegador y ve a: `http://localhost/hotelsito/`
- Deberías ver el Dashboard con estadísticas

## Estructura del Proyecto

\`\`\`
hotelsito/
├── config/
│   └── database.php         (Configuración de BD)
├── includes/
│   ├── header.php          (Cabecera HTML)
│   ├── sidebar.php         (Menú lateral)
│   └── footer.php          (Pie de página)
├── api/
│   └── endpoints/          (APIs REST)
│       ├── usuarios.php
│       ├── clientes.php
│       ├── habitaciones.php
│       ├── reservas.php
│       └── stats.php
├── assets/
│   ├── css/
│   │   └── style.css       (Estilos personalizados)
│   └── js/
│       └── main.js         (JavaScript)
├── scripts/
│   └── hotel_management_simple.sql  ⭐ USAR ESTE ARCHIVO
├── index.php               (Dashboard)
├── habitaciones.php        (Gestión de habitaciones)
├── usuarios.php            (Gestión de usuarios)
├── clientes.php            (Gestión de clientes)
└── reservas.php            (Gestión de reservas)
\`\`\`

## Archivo SQL a Usar

**IMPORTANTE:** Usa únicamente el archivo:
### ✅ `scripts/hotel_management_simple.sql`

Este archivo contiene:
- Base de datos: `hotel_management_system`
- 5 tablas relacionales
- Datos de prueba pre-cargados
- Compatible con XAMPP

## Datos de Prueba Incluidos

### Usuarios (para futuras funcionalidades de login)
- Admin: admin@hotel.com (password: admin123)
- Recepcionista: recepcion@hotel.com (password: admin123)
- Gerente: gerente@hotel.com (password: admin123)

### Clientes
- 4 clientes con información completa

### Habitaciones
- 6 habitaciones de diferentes tipos
- Simple, Doble, Suite, Presidencial

### Reservas
- 4 reservas de ejemplo con diferentes estados

## Solución de Problemas

### Error 500 - Internal Server Error
**Causa:** Base de datos no creada o nombre incorrecto

**Solución:**
1. Ve a phpMyAdmin: `http://localhost/phpmyadmin`
2. Verifica que existe la base de datos: `hotel_management_system`
3. Si no existe, ejecuta el archivo SQL nuevamente

### Error de Conexión
**Causa:** MySQL no está iniciado

**Solución:**
1. Abre el Panel de Control de XAMPP
2. Haz clic en **Start** junto a MySQL
3. Espera a que el texto se ponga verde

### Página en blanco
**Causa:** Error de PHP no mostrado

**Solución:**
1. Abre el archivo: `config/database.php`
2. Verifica que tenga estas líneas al inicio:
\`\`\`php
error_reporting(E_ALL);
ini_set('display_errors', 1);
\`\`\`

### No aparecen los estilos
**Causa:** Ruta incorrecta de archivos CSS/JS

**Solución:**
- Verifica que la carpeta `assets` esté en la raíz del proyecto
- Revisa que los archivos existan en `assets/css/` y `assets/js/`

## URLs del Sistema

- **Dashboard:** `http://localhost/hotelsito/`
- **Habitaciones:** `http://localhost/hotelsito/habitaciones.php`
- **Usuarios:** `http://localhost/hotelsito/usuarios.php`
- **Clientes:** `http://localhost/hotelsito/clientes.php`
- **Reservas:** `http://localhost/hotelsito/reservas.php`
- **phpMyAdmin:** `http://localhost/phpmyadmin`

## Configuración de Base de Datos

Archivo: `config/database.php`

\`\`\`php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hotel_management_system');
\`\`\`

## Soporte

Si tienes problemas:
1. Verifica que XAMPP esté corriendo (Apache y MySQL en verde)
2. Asegúrate de usar el archivo SQL correcto: `hotel_management_simple.sql`
3. Revisa que el nombre de la base de datos sea: `hotel_management_system`
4. Verifica que la carpeta esté en: `C:\xampp\htdocs\hotelsito\`
