# Documentación Técnica - Sistema de Gestión Hotelera

## Arquitectura del Sistema

### Stack Tecnológico

- **Frontend**: Next.js 16, React 19.2, TypeScript
- **Styling**: Tailwind CSS v4, shadcn/ui
- **Backend**: Next.js API Routes, Server Actions
- **Base de Datos**: PostgreSQL 14+ / MySQL 8+
- **Autenticación**: NextAuth.js
- **Deployment**: Vercel / Docker

### Estructura del Proyecto

\`\`\`
hotel-management/
├── app/                          # Next.js App Router
│   ├── page.tsx                 # Dashboard principal
│   ├── layout.tsx               # Layout raíz
│   ├── documentacion/           # Página de documentación
│   ├── habitaciones/            # Módulo de habitaciones
│   ├── reservas/                # Módulo de reservas
│   ├── clientes/                # Módulo de clientes
│   └── api/                     # API Routes
├── components/                   # Componentes React
│   ├── dashboard-layout.tsx     # Layout principal
│   ├── sidebar.tsx              # Navegación lateral
│   ├── header.tsx               # Barra superior
│   ├── contextual-help.tsx      # Sistema de ayuda
│   └── ui/                      # Componentes base
├── lib/                         # Utilidades
│   ├── utils.ts                 # Funciones auxiliares
│   ├── db.ts                    # Cliente de base de datos
│   └── auth.ts                  # Configuración auth
├── scripts/                     # Scripts de migración
│   ├── migration-v1-to-v2.ts   # Migración v1 -> v2
│   └── migration-v2-to-v3.ts   # Migración v2 -> v3
└── docs/                        # Documentación
    ├── MANUAL_USUARIO.md
    ├── GUIA_INSTALACION.md
    └── DOCUMENTACION_TECNICA.md
\`\`\`

## Base de Datos

### Esquema Principal

#### Tabla: users

\`\`\`sql
CREATE TABLE users (
  id SERIAL PRIMARY KEY,
  email VARCHAR(255) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(200) NOT NULL,
  role VARCHAR(50) DEFAULT 'viewer',
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP DEFAULT NOW()
);
\`\`\`

#### Tabla: rooms

\`\`\`sql
CREATE TABLE rooms (
  id SERIAL PRIMARY KEY,
  room_number VARCHAR(20) UNIQUE NOT NULL,
  room_type VARCHAR(50) NOT NULL,
  price_per_night DECIMAL(10,2) NOT NULL,
  status VARCHAR(50) DEFAULT 'available',
  floor INTEGER,
  max_occupancy INTEGER DEFAULT 2,
  description TEXT,
  created_at TIMESTAMP DEFAULT NOW()
);
\`\`\`

#### Tabla: clients

\`\`\`sql
CREATE TABLE clients (
  id SERIAL PRIMARY KEY,
  full_name VARCHAR(200) NOT NULL,
  email VARCHAR(255),
  phone VARCHAR(50),
  document_type VARCHAR(50),
  document_number VARCHAR(100),
  address TEXT,
  notes TEXT,
  created_at TIMESTAMP DEFAULT NOW()
);
\`\`\`

#### Tabla: reservations

\`\`\`sql
CREATE TABLE reservations (
  id SERIAL PRIMARY KEY,
  client_id INTEGER REFERENCES clients(id),
  room_id INTEGER REFERENCES rooms(id),
  check_in_date DATE NOT NULL,
  check_out_date DATE NOT NULL,
  num_guests INTEGER NOT NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  status VARCHAR(50) DEFAULT 'pending',
  notes TEXT,
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP DEFAULT NOW()
);
\`\`\`

#### Tabla: help_content

\`\`\`sql
CREATE TABLE help_content (
  id SERIAL PRIMARY KEY,
  role VARCHAR(50) NOT NULL,
  category VARCHAR(100) NOT NULL,
  title VARCHAR(200) NOT NULL,
  content TEXT NOT NULL,
  media_url VARCHAR(500),
  order_index INTEGER DEFAULT 0,
  created_at TIMESTAMP DEFAULT NOW()
);
\`\`\`

### Índices para Optimización

\`\`\`sql
-- Índices de búsqueda frecuente
CREATE INDEX idx_reservations_dates ON reservations(check_in_date, check_out_date);
CREATE INDEX idx_reservations_status ON reservations(status);
CREATE INDEX idx_rooms_status ON rooms(status);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_help_content_role ON help_content(role, category);
\`\`\`

## API Endpoints

### Autenticación

\`\`\`typescript
POST /api/auth/login
Body: { email: string, password: string }
Response: { token: string, user: User }

POST /api/auth/logout
Response: { success: boolean }

GET /api/auth/session
Response: { user: User | null }
\`\`\`

### Reservas

\`\`\`typescript
GET /api/reservations
Query: { status?: string, date?: string }
Response: { reservations: Reservation[] }

POST /api/reservations
Body: ReservationCreateInput
Response: { reservation: Reservation }

PUT /api/reservations/:id
Body: ReservationUpdateInput
Response: { reservation: Reservation }

DELETE /api/reservations/:id
Response: { success: boolean }
\`\`\`

### Habitaciones

\`\`\`typescript
GET /api/rooms
Query: { status?: string, type?: string }
Response: { rooms: Room[] }

GET /api/rooms/availability
Query: { checkIn: string, checkOut: string }
Response: { availableRooms: Room[] }

PUT /api/rooms/:id
Body: RoomUpdateInput
Response: { room: Room }
\`\`\`

### Sistema de Ayuda

\`\`\`typescript
GET /api/help/content
Query: { role: string, category?: string }
Response: { content: HelpContent[] }

POST /api/help/track
Body: { contentId: number, action: string }
Response: { success: boolean }
\`\`\`

## Componentes Principales

### DashboardLayout

Componente de layout principal que envuelve todas las páginas del sistema.

**Props:**
\`\`\`typescript
interface DashboardLayoutProps {
  children: ReactNode
}
\`\`\`

**Uso:**
\`\`\`tsx
<DashboardLayout>
  <YourPageContent />
</DashboardLayout>
\`\`\`

### ContextualHelp

Sistema de ayuda sensible al rol del usuario.

**Features:**
- Tutoriales interactivos
- Guías rápidas
- Documentación completa
- Contenido filtrado por rol

**Uso:**
\`\`\`tsx
const { showHelp, getUserRole } = useHelp()

<Button onClick={() => showHelp('reservations')}>
  Ayuda
</Button>
\`\`\`

### HelpProvider

Proveedor de contexto para el sistema de ayuda.

\`\`\`tsx
<HelpProvider>
  <App />
</HelpProvider>
\`\`\`

## Sistema de Roles

### Roles Disponibles

1. **admin**
   - Acceso completo al sistema
   - Gestión de usuarios
   - Configuración avanzada
   - Todos los reportes

2. **staff**
   - Gestión de reservas
   - Check-in/Check-out
   - Gestión de clientes
   - Reportes básicos

3. **viewer**
   - Solo lectura
   - Dashboard básico
   - Consultar reservas

### Implementación de Permisos

\`\`\`typescript
// lib/permissions.ts
export const permissions = {
  admin: ['*'],
  staff: [
    'reservations:read',
    'reservations:write',
    'clients:read',
    'clients:write',
    'rooms:read'
  ],
  viewer: [
    'reservations:read',
    'rooms:read'
  ]
}

export function hasPermission(role: string, action: string): boolean {
  const userPermissions = permissions[role] || []
  return userPermissions.includes('*') || userPermissions.includes(action)
}
\`\`\`

## Scripts de Migración

### Estructura de Migración

Todos los scripts de migración siguen una estructura estándar:

\`\`\`typescript
interface MigrationResult {
  success: boolean
  message: string
  timestamp: Date
}

async function runMigration(): Promise<MigrationResult> {
  try {
    await createBackup()
    await updateDatabaseSchema()
    await migrateExistingData()
    await optimizeIndexes()
    
    return { success: true, message: 'OK', timestamp: new Date() }
  } catch (error) {
    await restoreBackup()
    return { success: false, message: error.message, timestamp: new Date() }
  }
}
\`\`\`

### Ejecutar Migraciones

\`\`\`bash
# Desarrollo
npm run migrate:dev

# Producción
npm run migrate:prod

# Rollback
npm run migrate:rollback
\`\`\`

## Seguridad

### Autenticación

- Passwords hasheados con bcrypt (10 rounds)
- Tokens JWT con expiración de 24 horas
- Refresh tokens para sesiones prolongadas

### Protección de Rutas

\`\`\`typescript
// middleware.ts
export async function middleware(request: NextRequest) {
  const token = request.cookies.get('auth-token')
  
  if (!token && !isPublicRoute(request.nextUrl.pathname)) {
    return NextResponse.redirect(new URL('/login', request.url))
  }
  
  return NextResponse.next()
}
\`\`\`

### SQL Injection Prevention

- Uso de queries parametrizadas
- Validación de entrada con Zod
- Sanitización de datos

\`\`\`typescript
// Ejemplo con parametrización
const result = await db.query(
  'SELECT * FROM reservations WHERE id = $1',
  [reservationId]
)
\`\`\`

## Performance

### Optimizaciones Implementadas

1. **Server-Side Rendering (SSR)**
   - Páginas estáticas pre-renderizadas
   - Incremental Static Regeneration (ISR)

2. **Caching**
   - Redis para cache de sesiones
   - Cache de queries frecuentes
   - CDN para assets estáticos

3. **Database**
   - Índices optimizados
   - Connection pooling
   - Query optimization

4. **Frontend**
   - Code splitting automático
   - Lazy loading de componentes
   - Image optimization

## Testing

### Tipos de Tests

\`\`\`bash
# Unit tests
npm run test:unit

# Integration tests
npm run test:integration

# E2E tests
npm run test:e2e

# Coverage
npm run test:coverage
\`\`\`

### Ejemplo de Test

\`\`\`typescript
// __tests__/reservations.test.ts
describe('Reservations API', () => {
  it('should create a new reservation', async () => {
    const reservation = {
      clientId: 1,
      roomId: 101,
      checkInDate: '2025-02-01',
      checkOutDate: '2025-02-05'
    }
    
    const response = await createReservation(reservation)
    
    expect(response.status).toBe(201)
    expect(response.data).toHaveProperty('id')
  })
})
\`\`\`

## Deployment

### Vercel (Recomendado)

1. Conectar repositorio de GitHub
2. Configurar variables de entorno
3. Deploy automático en cada push

### Docker

\`\`\`dockerfile
# Dockerfile
FROM node:18-alpine

WORKDIR /app

COPY package*.json ./
RUN npm ci --only=production

COPY . .
RUN npm run build

EXPOSE 3000

CMD ["npm", "start"]
\`\`\`

\`\`\`bash
# Build y run
docker build -t hotel-management .
docker run -p 3000:3000 hotel-management
\`\`\`

## Monitoreo

### Logging

\`\`\`typescript
// lib/logger.ts
export const logger = {
  info: (message: string, meta?: any) => {
    console.log(`[INFO] ${message}`, meta)
  },
  error: (message: string, error?: Error) => {
    console.error(`[ERROR] ${message}`, error)
  },
  warn: (message: string, meta?: any) => {
    console.warn(`[WARN] ${message}`, meta)
  }
}
\`\`\`

### Métricas

- Vercel Analytics para performance
- Error tracking con Sentry
- Custom metrics con Prometheus

## Roadmap

### v3.1 (Q2 2025)
- [ ] Integración con WhatsApp Business
- [ ] App móvil nativa
- [ ] Multi-idioma completo

### v3.2 (Q3 2025)
- [ ] IA para predicción de ocupación
- [ ] Sistema de recomendaciones
- [ ] Integración con OTAs (Booking, Airbnb)

### v4.0 (Q4 2025)
- [ ] Multi-propiedad
- [ ] Blockchain para pagos
- [ ] Metaverso virtual tours

---

**Versión**: 3.0  
**Última Actualización**: Enero 2025  
**Mantenedor**: Equipo de Desarrollo Hotel Management
