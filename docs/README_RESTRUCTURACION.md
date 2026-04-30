# 🏨 Hotel Tame - Sistema de Gestión Hotelera (Nueva Estructura)

## 📋 Resumen de la Reestructuración

El proyecto Hotel Tame ha sido completamente reestructurado manteniendo la compatibilidad con XAMPP y mejorando la organización del código.

## 🏗️ Nueva Estructura de Directorios

```
/opt/lampp/htdocs/Hotel_tame/
├── index.php                  # Router principal (nuevo)
├── .htaccess                  # Reescritura de URLs
├── backend/                    # Todo el código PHP
│   ├── api/                    # Endpoints de API
│   │   └── endpoints/          # Archivos PHP de endpoints
│   ├── config/                 # Configuraciones
│   ├── includes/               # Archivos de inclusión PHP
│   ├── lib/                    # Librerías PHP
│   ├── models/                 # Modelos de datos PHP
│   ├── utils/                  # Utilidades PHP
│   └── legacy-views/           # Vistas PHP antiguas (referencia)
│
├── frontend/                    # Código Next.js compilado
│   └── out/                    # Build estático de Next.js
│       ├── index.html
│       ├── login/
│       ├── dashboard/
│       └── 404.html
│
├── assets/                      # Recursos globales
├── uploads/                      # Archivos subidos
├── scripts/                      # Scripts de mantenimiento
├── docs/                         # Documentación
└── backups/                      # Backups
```

## 🚀 URLs del Nuevo Sistema

### Públicas
- **Inicio**: `http://localhost/Hotel_tame/`
- **Login**: `http://localhost/Hotel_tame/login`
- **Habitaciones**: `http://localhost/Hotel_tame/habitaciones`
- **Portal Cliente**: `http://localhost/Hotel_tame/portal-cliente`

### Dashboard (Protegidas)
- **Dashboard Principal**: `http://localhost/Hotel_tame/dashboard`
- **Reservas**: `http://localhost/Hotel_tame/dashboard/reservas`
- **Clientes**: `http://localhost/Hotel_tame/dashboard/clientes`
- **Usuarios**: `http://localhost/Hotel_tame/dashboard/usuarios`

### APIs
- **Autenticación**: `http://localhost/Hotel_tame/api/auth`
- **Clientes**: `http://localhost/Hotel_tame/api/clientes`
- **Reservas**: `http://localhost/Hotel_tame/api/reservas`
- **Usuarios**: `http://localhost/Hotel_tame/api/usuarios`

## 🔧 Características Principales

### ✅ Router PHP Central
- Un único punto de entrada (`index.php`)
- Manejo de rutas públicas y protegidas
- Redirección automática a endpoints de API
- Verificación de autenticación

### ✅ Separación Backend/Frontend
- **Backend PHP**: Lógica de negocio, APIs, base de datos
- **Frontend Next.js**: HTML estático, JavaScript, CSS
- **Comunicación**: Via APIs REST

### ✅ URLs Amigables
- Sin extensiones .php en las URLs
- Soporte para rutas anidadas
- Configuración con .htaccess

### ✅ Seguridad
- Autenticación por sesión PHP
- Protección de rutas del dashboard
- Validación de inputs en APIs

## 🛠️ Configuración de Next.js

```javascript
// next.config.mjs
const nextConfig = {
  output: 'export',           // Genera sitio estático
  distDir: 'out',             // Directorio de salida
  images: {
    unoptimized: true,         // Necesario para exportación estática
  },
  trailingSlash: true,         // Añade / al final
  basePath: '/Hotel_tame',    // Base path para subdirectorio
  assetPrefix: '/Hotel_tame/', // Para assets
}
```

## 📁 Migración Realizada

### Archivos Movidos
- `api/endpoints/*` → `backend/api/endpoints/`
- `config/*` → `backend/config/`
- `includes/*` → `backend/includes/`
- `lib/*.php` → `backend/lib/`
- `models/*` → `backend/models/`
- `utils/*` → `backend/utils/`

### Archivos Legacy
- Vistas PHP antiguas → `backend/legacy-views/`
- Archivos de backup → `backups/archivos-legacy/`

### Includes Actualizados
- Rutas relativas convertidas a absolutas
- Uso de `__DIR__` para paths correctos

## 🔄 Flujo de Navegación

1. **Usuario accede** a `http://localhost/Hotel_tame/`
2. **Router PHP** verifica autenticación
3. **Si no autenticado**: Redirige a `/login`
4. **Si autenticado**: Muestra dashboard
5. **API calls**: Se dirigen a `/api/*` endpoints

## 🧪 Pruebas del Sistema

### Verificar Funcionamiento
```bash
# Test de API
curl http://localhost/Hotel_tame/api/auth/check

# Test de página principal
curl http://localhost/Hotel_tame/

# Test de login
curl -X POST http://localhost/Hotel_tame/api/auth \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"password"}'
```

### Verificar Estructura
```bash
php scripts/migrar-estructura.php
```

## 📝 Scripts de Mantenimiento

### `scripts/migrar-estructura.php`
- Verifica estructura de directorios
- Valida archivos clave
- Reporta estado del sistema

### `scripts/actualizar-includes.php`
- Actualiza rutas de includes
- Convierte paths relativos a absolutos
- Procesa archivos PHP automáticamente

## 🔐 Autenticación

### Flujo de Login
1. Frontend envía credenciales a `/api/auth`
2. Backend verifica en base de datos
3. Si válido, crea sesión PHP
4. Redirige a dashboard

### Protección de Rutas
- Todas las rutas `/dashboard/*` requieren sesión
- Verificación automática en router PHP
- Redirección a login si no autenticado

## 🚨 Consideraciones Importantes

### XAMPP
- **No se modificó configuración** de Apache/PHP
- Funciona con estructura estándar de htdocs
- Compatible con VirtualHosts si se desea

### Base de Datos
- Sin cambios en estructura
- Mismas credenciales y tablas
- Conexión via `backend/config/database.php`

### Sesiones
- PHP sessions nativas
- Compatibilidad entre backend y frontend
- Cookies con path `/Hotel_tame/`

## 🎯 Próximos Pasos

### Desarrollo Frontend
1. Mover código fuente Next.js a `frontend/`
2. Desarrollar componentes con basePath `/Hotel_tame`
3. Construir con `npm run build`
4. Deploy automático a `frontend/out/`

### Mejoras Backend
1. Implementar REST API completa
2. Agregar validación de inputs
3. Implementar rate limiting
4. Agregar logging y monitoreo

### Testing
1. Tests unitarios para APIs
2. Tests de integración E2E
3. Tests de seguridad
4. Tests de carga

## 📞 Soporte

Si encuentras problemas:

1. **Verifica logs de Apache**: `/opt/lampp/logs/error_log`
2. **Verifica logs de PHP**: `error_log` en archivos PHP
3. **Ejecuta script de verificación**: `php scripts/migrar-estructura.php`
4. **Prueba URLs manualmente**: curl o navegador

## ✅ Estado Actual

- ✅ **Estructura reorganizada**
- ✅ **Router PHP funcional**
- ✅ **APIs migradas**
- ✅ **Frontend básico operativo**
- ✅ **Autenticación funcional**
- ✅ **URLs amigables**
- ✅ **Compatibilidad XAMPP**

**El sistema está listo para desarrollo y producción!** 🎉
