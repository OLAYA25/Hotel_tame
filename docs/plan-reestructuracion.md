# PLAN DE REESTRUCTURACIÓN - HOTEL TAME
## 🚨 PRIORIDAD ABSOLUTA: CONSERVAR APARIENCIA VISUAL ORIGINAL 🚨

### REGLA DE ORO
El proyecto debe verse **EXACTAMENTE IGUAL** en el navegador. El usuario final NO DEBE NOTAR ningún cambio visual ni funcional.

### ESTRATEGIA: Router PHP + Mantener Vistas Originales
- NO vamos a modificar el contenido HTML/CSS de las vistas
- SOLO vamos a mover archivos y actualizar rutas de include
- Crearemos un router que sirva las vistas ORIGINALES desde nuevas ubicaciones

### FASE 1: CREAR ESTRUCTURA DE CARPETAS (sin mover archivos aún)
```bash
mkdir -p frontend/views/public
mkdir -p frontend/views/private  
mkdir -p backend/api/endpoints
mkdir -p backend/api/models
mkdir -p backend/includes
mkdir -p backend/utils
mkdir -p backend/config
mkdir -p docs/screenshots
```

### FASE 2: RESPALDO VISUAL CRÍTICO
ANTES de mover cualquier archivo:
1. Abrir en navegador y tomar screenshots:
   - http://localhost/Hotel_tame/home.php
   - http://localhost/Hotel_tame/portal_cliente.php  
   - http://localhost/Hotel_tame/index.php (dashboard)
   - http://localhost/Hotel_tame/login.php
   - http://localhost/Hotel_tame/reservas.php
2. Guardar en docs/screenshots/ con nombres descriptivos
3. **NO CONTINUAR si los screenshots no muestran el estado actual funcional**

### FASE 3: MOVER ARCHIVOS (MANTENIENDO CONTENIDO IDÉNTICO)

#### 3.1 Vistas Públicas
```bash
# MOVER (sin modificar contenido)
mv home.php frontend/views/public/home.php
mv portal_cliente.php frontend/views/public/portal_cliente.php
mv login.php frontend/views/public/login.php
mv habitaciones.php frontend/views/public/habitaciones.php
```

#### 3.2 Vistas Privadas  
```bash
# MOVER (sin modificar contenido)
mv index.php frontend/views/private/index.php
mv reservas.php frontend/views/private/reservas.php
mv clientes.php frontend/views/private/clientes.php
mv dashboard_advanced.php frontend/views/private/dashboard_advanced.php
mv mis_actividades_v2.php frontend/views/private/mis_actividades_v2.php
mv contabilidad.php frontend/views/private/contabilidad.php
mv reportes.php frontend/views/private/reportes.php
mv usuarios.php frontend/views/private/usuarios.php
mv settings.php frontend/views/private/settings.php
mv eventos.php frontend/views/private/eventos.php
mv productos.php frontend/views/private/productos.php
mv pedidos_productos.php frontend/views/private/pedidos_productos.php
```

#### 3.3 Backend
```bash
# APIs (ya están en api/, solo mover si es necesario)
# Includes (críticos para la apariencia)
mv includes/*.php backend/includes/

# Librerías y componentes
mv lib/*.php backend/utils/lib/
mv components/*.php backend/utils/components/

# Configuración
mv config/*.php backend/config/
```

### FASE 4: CREAR ROUTER PRINCIPAL
Crear nuevo `index.php` en la raíz:

```php
<?php
// index.php - Router principal (CONSERVA VISTAS ORIGINALES)
session_start();

// Mapeo de URLs a archivos PHP ORIGINALES
$routeMap = [
    '/' => 'frontend/views/public/home.php',
    '/home' => 'frontend/views/public/home.php',
    '/portal-cliente' => 'frontend/views/public/portal_cliente.php',
    '/login' => 'frontend/views/public/login.php',
    '/habitaciones' => 'frontend/views/public/habitaciones.php',
    '/dashboard' => 'frontend/views/private/index.php',
    '/reservas' => 'frontend/views/private/reservas.php',
    '/clientes' => 'frontend/views/private/clientes.php',
    '/usuarios' => 'frontend/views/private/usuarios.php',
    '/contabilidad' => 'frontend/views/private/contabilidad.php',
    '/reportes' => 'frontend/views/private/reportes.php',
    '/settings' => 'frontend/views/private/settings.php',
    '/eventos' => 'frontend/views/private/eventos.php',
    '/productos' => 'frontend/views/private/productos.php',
    '/pedidos-productos' => 'frontend/views/private/pedidos_productos.php',
    '/dashboard-advanced' => 'frontend/views/private/dashboard_advanced.php',
    '/mis-actividades' => 'frontend/views/private/mis_actividades_v2.php'
];

// Mapeo de APIs
$apiMap = [
    '/api/clientes' => 'api/endpoints/clientes.php',
    '/api/reservas' => 'api/endpoints/reservas.php',
    '/api/habitaciones' => 'api/endpoints/habitaciones.php',
    '/api/usuarios' => 'api/endpoints/usuarios.php',
    '/api/notifications' => 'api/endpoints/notifications.php',
    '/api/widgets' => 'api/endpoints/widgets.php',
    '/api/settings' => 'api/endpoints/settings.php'
];

// Obtener ruta solicitada
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = '/Hotel_tame';
$path = str_replace($basePath, '', $requestUri);
$path = parse_url($path, PHP_URL_PATH);
$path = rtrim($path, '/') ?: '/';

// Manejar APIs
if (strpos($path, '/api/') === 0) {
    if (isset($apiMap[$path]) && file_exists(__DIR__ . '/' . $apiMap[$path])) {
        require_once __DIR__ . '/' . $apiMap[$path];
        exit;
    }
    http_response_code(404);
    echo json_encode(['error' => 'API endpoint not found']);
    exit;
}

// Manejar vistas
if (isset($routeMap[$path]) && file_exists(__DIR__ . '/' . $routeMap[$path])) {
    // INCLUIR el archivo PHP original - CONSERVA TODO
    require_once __DIR__ . '/' . $routeMap[$path];
    exit;
}

// Si no encuentra, verificar si existe archivo directo
$directFile = __DIR__ . '/' . ltrim($path, '/');
if (file_exists($directFile) && is_file($directFile)) {
    require_once $directFile;
    exit;
}

// 404
http_response_code(404);
echo "<h1>Página no encontrada</h1><p>La ruta '$path' no existe.</p>";
```

### FASE 5: ACTUALIZAR RUTAS EN ARCHIVOS (CON EXTREMO CUIDADO)

#### 5.1 Actualizar includes en vistas PRIVADAS
En cada vista privada (frontend/views/private/*.php):

**ANTES:**
```php
<?php require_once 'config/database.php'; ?>
<?php require_once 'includes/auth_middleware.php'; ?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
```

**DESPUÉS:**
```php
<?php require_once __DIR__ . '/../../backend/config/database.php'; ?>
<?php require_once __DIR__ . '/../../backend/includes/auth_middleware.php'; ?>
<?php include __DIR__ . '/../../backend/includes/header.php'; ?>
<?php include __DIR__ . '/../../backend/includes/sidebar.php'; ?>
```

#### 5.2 Actualizar includes en vistas PÚBLICAS
En cada vista pública (frontend/views/public/*.php):

**ANTES:**
```php
<?php require_once 'config/database.php'; ?>
```

**DESPUÉS:**
```php
<?php require_once __DIR__ . '/../../backend/config/database.php'; ?>
```

#### 5.3 Actualizar rutas de assets en TODAS las vistas
**ANTES:**
```html
<link rel="stylesheet" href="assets/css/style.css">
<script src="assets/js/main.js"></script>
<img src="assets/images/logo.png">
```

**DESPUÉS:**
```html
<link rel="stylesheet" href="/Hotel_tame/assets/css/style.css">
<script src="/Hotel_tame/assets/js/main.js"></script>
<img src="/Hotel_tame/assets/images/logo.png">
```

#### 5.4 Actualizar rutas en APIs
En cada API (api/endpoints/*.php):

**ANTES:**
```php
require_once '../../config/database.php';
require_once '../models/Cliente.php';
```

**DESPUÉS:**
```php
require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../models/Cliente.php';
```

### FASE 6: VERIFICACIÓN VISUAL CRÍTICA (DESPUÉS DE CADA CAMBIO)

#### Checklist de verificación OBLIGATORIA:
- [ ] Home se ve **EXACTAMENTE IGUAL** (comparar con screenshot)
- [ ] Portal cliente se ve **EXACTAMENTE IGUAL**
- [ ] Dashboard se ve **EXACTAMENTE IGUAL** (sidebar, colores, tipografía)
- [ ] Login funciona y se ve **EXACTAMENTE IGUAL**
- [ ] Reservas mantiene la misma interfaz
- [ ] CSS carga completo (inspeccionar en navegador)
- [ ] JavaScript funciona (consola sin errores)
- [ ] Imágenes cargan todas
- [ ] Base de datos conecta igual
- [ ] Select2 funciona igual
- [ ] Bootstrap mantiene estilos

#### Comandos de verificación:
```bash
# Verificar en navegador
echo "VERIFICAR MANUALMENTE:"
echo "1. http://localhost/Hotel_tame/"
echo "2. http://localhost/Hotel_tame/portal-cliente"  
echo "3. http://localhost/Hotel_tame/dashboard"
echo "4. http://localhost/Hotel_tame/login"
echo "5. http://localhost/Hotel_tame/reservas"
```

### FASE 7: .htaccess PARA URLs AMIGABLES (OPCIONAL)
```apache
# .htaccess en la raíz
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /Hotel_tame/
  
  # Si el archivo o directorio existe, servirlo directamente
  RewriteCond %{REQUEST_FILENAME} -f [OR]
  RewriteCond %{REQUEST_FILENAME} -d
  RewriteRule ^ - [L]
  
  # Todas las demás peticiones van a index.php (router)
  RewriteRule ^ index.php [L]
</IfModule>
```

### SEÑALES DE PELIGRO - DETENER INMEDIATAMENTE SI:
- Los estilos cambian
- Los colores son diferentes
- El layout se rompe
- Las imágenes no cargan
- El CSS no se aplica
- JavaScript da errores
- La base de datos no conecta
- El usuario nota cualquier diferencia

### PLAN DE ROLLBACK INMEDIATO:
Si algo falla:
```bash
# Restaurar archivos originales desde backup
cp backups/archivos-originales/*.php ./
# O revertir git si se usó control de versiones
git checkout -- *.php
```

### REGLAS FINALES:
1. **NUNCA modificar el contenido HTML/CSS de las vistas**
2. **NUNCA cambiar los archivos CSS**
3. **NUNCA alterar la estructura HTML del header/footer/sidebar**
4. **SIEMPRE verificar cada cambio en el navegador**
5. **DETENER si algo se ve diferente**
6. **El éxito es que el usuario NO note la diferencia**

### RESULTADO ESPERADO:
- ✅ Estructura de carpetas organizada
- ✅ Código limpio y mantenible
- ✅ **EXACTAMENTE** la misma apariencia visual
- ✅ **EXACTAMENTE** la misma funcionalidad
- ✅ **EXACTAMENTE** la misma experiencia de usuario
- ✅ URLs más limpias (opcionales)

El usuario NO debe saber que algo cambió internamente.
