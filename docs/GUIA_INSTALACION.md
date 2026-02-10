# Guía de Instalación - Sistema de Gestión Hotelera

## Requisitos del Sistema

### Hardware Mínimo

- **Procesador**: 2 GHz o superior
- **RAM**: 4 GB mínimo (8 GB recomendado)
- **Disco**: 20 GB de espacio disponible
- **Conexión**: Internet banda ancha

### Software Requerido

- **Node.js**: Versión 18.0 o superior
- **Base de Datos**: PostgreSQL 14+ o MySQL 8+
- **Navegador Web**: Chrome, Firefox, Safari o Edge (última versión)

## Instalación en Desarrollo

### Paso 1: Clonar el Repositorio

\`\`\`bash
git clone https://github.com/tu-organizacion/hotel-management.git
cd hotel-management
\`\`\`

### Paso 2: Instalar Dependencias

\`\`\`bash
npm install
\`\`\`

### Paso 3: Configurar Variables de Entorno

Cree un archivo `.env.local` en la raíz del proyecto:

\`\`\`env
# Base de Datos
DATABASE_URL="postgresql://usuario:password@localhost:5432/hotel_db"

# Aplicación
NEXT_PUBLIC_APP_URL="http://localhost:3000"

# Autenticación
NEXTAUTH_SECRET="tu-secreto-seguro-aqui"
NEXTAUTH_URL="http://localhost:3000"

# Email (opcional)
SMTP_HOST="smtp.gmail.com"
SMTP_PORT="587"
SMTP_USER="tu-email@gmail.com"
SMTP_PASSWORD="tu-password"
\`\`\`

### Paso 4: Inicializar Base de Datos

\`\`\`bash
# Crear las tablas
npm run db:migrate

# Cargar datos de ejemplo (opcional)
npm run db:seed
\`\`\`

### Paso 5: Iniciar Servidor de Desarrollo

\`\`\`bash
npm run dev
\`\`\`

Acceda a: `http://localhost:3000`

## Instalación en Producción

### Opción 1: Despliegue en Vercel (Recomendado)

1. Cree una cuenta en [Vercel](https://vercel.com)
2. Conecte su repositorio de GitHub
3. Configure las variables de entorno
4. Despliegue con un clic

### Opción 2: Servidor Propio

#### Paso 1: Construir la Aplicación

\`\`\`bash
npm run build
\`\`\`

#### Paso 2: Configurar Servidor Web

**Nginx Configuration:**

\`\`\`nginx
server {
    listen 80;
    server_name tu-dominio.com;

    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
}
\`\`\`

#### Paso 3: Configurar PM2 (Process Manager)

\`\`\`bash
# Instalar PM2
npm install -g pm2

# Iniciar aplicación
pm2 start npm --name "hotel-management" -- start

# Configurar inicio automático
pm2 startup
pm2 save
\`\`\`

#### Paso 4: Configurar HTTPS con Let's Encrypt

\`\`\`bash
# Instalar Certbot
sudo apt-get install certbot python3-certbot-nginx

# Obtener certificado
sudo certbot --nginx -d tu-dominio.com
\`\`\`

## Configuración de Base de Datos

### PostgreSQL

\`\`\`sql
-- Crear usuario
CREATE USER hotel_admin WITH PASSWORD 'password_seguro';

-- Crear base de datos
CREATE DATABASE hotel_db OWNER hotel_admin;

-- Otorgar permisos
GRANT ALL PRIVILEGES ON DATABASE hotel_db TO hotel_admin;
\`\`\`

### MySQL

\`\`\`sql
-- Crear usuario
CREATE USER 'hotel_admin'@'localhost' IDENTIFIED BY 'password_seguro';

-- Crear base de datos
CREATE DATABASE hotel_db;

-- Otorgar permisos
GRANT ALL PRIVILEGES ON hotel_db.* TO 'hotel_admin'@'localhost';
FLUSH PRIVILEGES;
\`\`\`

## Migración desde Versión Anterior

### Desde v1.x a v2.x

\`\`\`bash
# Hacer backup de la base de datos
npm run db:backup

# Ejecutar migración
npm run migrate:v1-to-v2

# Verificar migración
npm run db:verify
\`\`\`

### Desde v2.x a v3.x

\`\`\`bash
# Hacer backup
npm run db:backup

# Ejecutar migración
npm run migrate:v2-to-v3

# Verificar y reiniciar
npm run db:verify
pm2 restart hotel-management
\`\`\`

## Verificación de Instalación

### Checklist Post-Instalación

- [ ] Aplicación accesible en el navegador
- [ ] Inicio de sesión funcional
- [ ] Dashboard carga correctamente
- [ ] Base de datos conectada
- [ ] Creación de reservas funciona
- [ ] Sistema de ayuda accesible
- [ ] Emails de notificación funcionando (si configurado)

### Pruebas de Funcionalidad

\`\`\`bash
# Ejecutar suite de pruebas
npm run test

# Pruebas de integración
npm run test:integration

# Pruebas de extremo a extremo
npm run test:e2e
\`\`\`

## Solución de Problemas

### Error: No se puede conectar a la base de datos

**Solución:**
1. Verifique que PostgreSQL/MySQL esté en ejecución
2. Confirme las credenciales en `.env.local`
3. Verifique que el puerto de base de datos esté abierto

### Error: Puerto 3000 en uso

**Solución:**
\`\`\`bash
# Encontrar proceso usando el puerto
lsof -i :3000

# Matar el proceso
kill -9 <PID>

# O usar otro puerto
PORT=3001 npm run dev
\`\`\`

### Error de permisos en producción

**Solución:**
\`\`\`bash
# Ajustar permisos de archivos
chmod -R 755 .next
chown -R usuario:grupo .next
\`\`\`

## Mantenimiento

### Backups Automatizados

\`\`\`bash
# Configurar cron job para backups diarios
0 2 * * * /usr/local/bin/backup-hotel-db.sh
\`\`\`

### Actualizaciones

\`\`\`bash
# Actualizar dependencias
npm update

# Verificar vulnerabilidades
npm audit

# Aplicar parches de seguridad
npm audit fix
\`\`\`

## Soporte

Para asistencia con la instalación:

- **Documentación**: https://docs.hotelmanagement.com
- **Email**: soporte@hotelmanagement.com
- **Discord**: https://discord.gg/hotelmanagement

---

**Versión**: 3.0  
**Última Actualización**: Enero 2025
