# Migración a Estructura Unificada de Personas

## Resumen de Cambios Implementados

### 1. Script de Migración
- **Archivo**: `/scripts/migracion_personas_unificadas.sql`
- **Funcionalidad**: Migración completa de clientes + acompañantes a estructura unificada
- **Características**:
  - Crea tabla `personas` unificada
  - Crea tabla `reserva_huespedes` para relaciones
  - Migra datos existentes manteniendo integridad
  - Crea vistas de compatibilidad
  - Incluye procedimientos almacenados optimizados

### 2. Nuevos Modelos PHP

#### Persona.php
- Gestión unificada de todas las personas
- Búsqueda por documento, nombre
- Historial completo de reservas
- Estadísticas personales
- Clasificación automática (frecuente/ocasional)

#### ReservaHuesped.php
- Gestión de relaciones reserva-persona
- Control de roles (principal/acompañante)
- Validaciones de negocio
- Estadísticas de ocupación real
- Soporte para operaciones masivas

### 3. Nuevos Endpoints

#### /api/endpoints/personas.php
- GET: Listar, buscar, obtener por ID/documento
- POST: Crear persona (con reserva opcional)
- PUT: Actualizar persona
- DELETE: Eliminar persona (con validaciones)

#### /api/endpoints/reserva_huespedes.php
- GET: Huéspedes por reserva, reservas por persona
- POST: Agregar huésped(es) a reserva
- PUT: Actualizar relación
- DELETE: Remover huésped de reserva

## Pasos para Implementación

### Paso 1: Ejecutar Migración
```bash
mysql -u usuario -p hotel_management_system < scripts/migracion_personas_unificadas.sql
```

### Paso 2: Verificar Migración
```sql
-- Verificar datos migrados
SELECT COUNT(*) as total_personas FROM personas;
SELECT COUNT(*) as total_relaciones FROM reserva_huespedes;

-- Verificar vistas de compatibilidad
SELECT * FROM v_clientes LIMIT 5;
SELECT * FROM v_acompanantes LIMIT 5;
```

### Paso 3: Probar Endpoints

#### Crear Persona
```bash
curl -X POST http://localhost/hotel/api/endpoints/personas.php \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Juan",
    "apellido": "Pérez",
    "tipo_documento": "DNI",
    "numero_documento": "12345678",
    "email": "juan@email.com",
    "telefono": "3001234567"
  }'
```

#### Buscar Persona
```bash
curl "http://localhost/hotel/api/endpoints/personas.php?documento=12345678"
```

#### Agregar Huésped a Reserva
```bash
curl -X POST http://localhost/hotel/api/endpoints/reserva_huespedes.php \
  -H "Content-Type: application/json" \
  -d '{
    "reserva_id": 1,
    "persona_id": 1,
    "rol_en_reserva": "acompanante",
    "parentesco": "Hijo"
  }'
```

## Ventajas de la Nueva Estructura

### 1. **Flexibilidad Total**
- Una persona puede ser principal en una reserva, acompañante en otra
- No hay duplicidad de datos
- Historial completo por persona

### 2. **Mejor Performance**
- Índices optimizados
- Consultas más simples
- Vistas para compatibilidad

### 3. **Negocio Mejorado**
- Sistema de lealtad unificado
- Estadísticas reales de ocupación
- Segmentación de clientes

### 4. **Escalabilidad**
- Fácil añadir nuevos roles
- Soporte para tipos de huéspedes futuros
- Extensible sin modificar estructura principal

## Compatibilidad

### Vistas Disponibles
- `v_clientes`: Compatibilidad con tabla clientes original
- `v_acompanantes`: Compatibilidad con tabla acompañantes
- `v_ocupacion_real_mejorada`: Vista mejorada de ocupación

### Procedimientos Almacenados
- `sp_get_huespedes_reserva(reserva_id)`: Obtener huéspedes de reserva
- `sp_buscar_persona(documento)`: Buscar persona con historial

## Pruebas Recomendadas

### 1. Datos de Prueba
```sql
-- Insertar persona de prueba
INSERT INTO personas (nombre, apellido, tipo_documento, numero_documento, email)
VALUES ('María', 'García', 'DNI', '87654321', 'maria@email.com');

-- Crear relación de prueba
INSERT INTO reserva_huespedes (reserva_id, persona_id, rol_en_reserva)
VALUES (1, LAST_INSERT_ID(), 'acompanante');
```

### 2. Validaciones
- Documentos únicos
- Un solo principal por reserva
- Cálculo automático de menores
- Actualización de tipo_persona

### 3. Rendimiento
- Consultas con grandes volúmenes
- Operaciones masivas
- Concurrencia

## Notas Importantes

1. **Las tablas originales no se eliminan automáticamente**
2. **Se recomienda probar extensivamente antes de eliminar tablas antiguas**
3. **Las vistas aseguran compatibilidad con código existente**
4. **La migración preserva todas las relaciones existentes**

## Siguientes Pasos

1. **Ejecutar script de migración**
2. **Probar funcionalidad básica**
3. **Actualizar frontend para usar nuevos endpoints**
4. **Monitorear performance**
5. **Una vez establecido, eliminar tablas antiguas**
