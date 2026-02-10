# BITÁCORA 9 - SISTEMA DE GESTIÓN HOTELERA
**Periodo:** 12/11/2025 - 26/11/2025  
**Integrantes:** Olaya Fernanda, Anghelica  
**Proyecto:** Sistema de Gestión Hotelera

---

## COMPETENCIA 1: Llevar a cabo la toma de requerimientos para dar inicio al desarrollo del aplicativo

### **APRENDIZ: OLAYA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Recopilación y análisis de requerimientos funcionales y no funcionales del sistema hotelero. Entrevista con stakeholders para definir módulos principales: reservas, habitaciones, clientes, usuarios y pagos.
* **FECHA DE INICIO:** 12/11/2025
* **FECHA DE FIN:** 15/11/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Se identificaron requerimientos clave como gestión de reservas en tiempo real, sistema de autenticación por roles, y reporting de ocupación. Se documentaron 32 historias de usuario.
  - **Jefe Inmediato:** Buen trabajo en la recopilación. Se sugiere priorizar por valor de negocio y definir MVP inicial.

### **APRENDIZ: FERNANDA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Diseño de arquitectura del sistema basada en los requerimientos. Definición de stack tecnológico: PHP 7.4+, MySQL, Bootstrap 5, jQuery. Creación de diagramas de flujo y wireframes.
* **FECHA DE INICIO:** 13/11/2025
* **FECHA DE FIN:** 17/11/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Se definió arquitectura MVC con API REST. Se diseñaron mockups de todas las interfaces principales. Dificultad inicial con definición de relaciones entre tablas.
  - **Jefe Inmediato:** Excelente propuesta arquitectónica. Se aprobó el stack tecnológico por ser robusto y escalable.

### **APRENDIZ: ANGHELICA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Documentación técnica y especificación de casos de uso. Creación de matriz de trazabilidad entre requerimientos y componentes del sistema.
* **FECHA DE INICIO:** 14/11/2025
* **FECHA DE FIN:** 18/11/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Documentación completa con 15 casos de uso detallados. Se creó matriz de trazabilidad para asegurar cobertura de requerimientos.
  - **Jefe Inmediato:** Documentación muy completa y bien estructurada. Servirá como base para el desarrollo.

---

## COMPETENCIA 2: Creación de base de datos para recopilar información

### **APRENDIZ: OLAYA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Diseño y creación del esquema de base de datos relacional. Definición de 5 tablas principales: usuarios, clientes, habitaciones, reservas, pagos.
* **FECHA DE INICIO:** 16/11/2025
* **FECHA DE FIN:** 20/11/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Se diseñó esquema con relaciones apropiadas. Se implementaron índices para optimización. Se crearon constraints de integridad referencial.
  - **Jefe Inmediato:** Buen diseño normalizado. Se sugirió agregar campos de auditoría (created_at, updated_at).

### **APRENDIZ: FERNANDA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Implementación del script SQL de creación de base de datos. Creación de datos de prueba para testing inicial.
* **FECHA DE INICIO:** 18/11/2025
* **FECHA DE FIN:** 22/11/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Script SQL funcional con datos de prueba. Se implementó soft delete con campo deleted_at. Dificultad con sintaxis de fechas.
  - **Jefe Inmediato:** Script bien estructurado. Los datos de prueba cubren todos los escenarios básicos.

### **APRENDIZ: ANGHELICA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Configuración de conexión a base de datos y clase Database. Implementación de manejo de errores y logging.
* **FECHA DE INICIO:** 19/11/2025
* **FECHA DE FIN:** 23/11/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Se implementó conexión PDO con prepared statements. Se agregó manejo robusto de excepciones. Se configuró logging de errores.
  - **Jefe Inmediato:** Excelente implementación de seguridad. El uso de PDO previene SQL injection.

---

## COMPETENCIA 3: Desarrollar el código requerido para la implementación del aplicativo

### **APRENDIZ: OLAYA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Desarrollo del módulo de gestión de usuarios. Implementación de CRUD completo con autenticación y gestión de roles.
* **FECHA DE INICIO:** 21/11/2025
* **FECHA DE FIN:** 25/11/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Módulo funcional con validaciones. Se implementó hashing de contraseñas. Dificultad con sesiones y manejo de estados.
  - **Jefe Inmediato:** Buen progreso. Se recomienda agregar validación de contraseña fuerte y doble factor.

### **APRENDIZ: FERNANDA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Desarrollo del módulo de gestión de habitaciones. Implementación de CRUD con estados y tipos de habitación.
* **FECHA DE INICIO:** 20/11/2025
* **FECHA DE FIN:** 24/11/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** CRUD completo con validaciones. Se implementaron estados: disponible, ocupada, mantenimiento. Interfaz responsive con Bootstrap.
  - **Jefe Inmediato:** Módulo bien implementado. Se sugiere agregar fotos de habitaciones y calendario de disponibilidad.

### **APRENDIZ: ANGHELICA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Desarrollo del módulo de clientes. Implementación de CRUD completo con validación de documentos y datos de contacto.
* **FECHA DE INICIO:** 22/11/2025
* **FECHA DE FIN:** 26/11/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** CRUD funcional con validaciones. Se implementaron tipos de documento y validación de formatos. Se agregó búsqueda y filtrado.
  - **Jefe Inmediato:** Buen trabajo en validaciones. Se recomienda integrar API de validación de documentos.

---

## COMPETENCIA 4: Presentar al cliente los avances del proyecto

### **APRENDIZ: OLAYA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Preparación de presentación de avance quincenal. Creación de demostración funcional de módulos desarrollados.
* **FECHA DE INICIO:** 24/11/2025
* **FECHA DE FIN:** 26/11/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Presentación preparada con demo en vivo. Se mostraron 3 módulos funcionales. Se documentaron próximos pasos.
  - **Jefe Inmediato:** Excelente presentación. El cliente quedó satisfecho con el progreso y la calidad del código.

### **APRENDIZ: FERNANDA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Creación de documentación de usuario y manual técnico. Preparación de ambiente de demostración.
* **FECHA DE INICIO:** 23/11/2025
* **FECHA DE FIN:** 25/11/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Documentación completa y clara. Se configuró ambiente de demo estable. Se crearon videos tutoriales.
  - **Jefe Inmediato:** Documentación de alta calidad. Los tutoriales serán muy útiles para el cliente final.

### **APRENDIZ: ANGHELICA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Recopilación de feedback del cliente y documentación de requerimientos adicionales.
* **FECHA DE INICIO:** 25/11/2025
* **FECHA DE FIN:** 26/11/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Se recopiló feedback positivo. Se documentaron 5 mejoras sugeridas por el cliente. Se creó backlog de ajustes.
  - **Jefe Inmediato:** Buen trabajo en la recopilación de feedback. Se priorizarán las mejoras para la siguiente iteración.

---

## COMPETENCIA 5: Corrección de errores, bugs y optimización

### **APRENDIZ: OLAYA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Debugging y corrección de errores en módulo de usuarios. Optimización de consultas y manejo de sesiones.
* **FECHA DE INICIO:** 24/11/2025
* **FECHA DE FIN:** 26/11/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Se corrigieron 8 bugs críticos. Se optimizaron consultas reduciendo tiempo en 40%. Se mejoró manejo de errores.
  - **Jefe Inmediato:** Excelente trabajo de optimización. El sistema es mucho más estable ahora.

### **APRENDIZ: FERNANDA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Corrección de errores de UI/UX en módulo de habitaciones. Optimización de responsive design y accesibilidad.
* **FECHA DE INICIO:** 23/11/2025
* **FECHA DE FIN:** 25/11/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Se corrigieron 12 errores de UI. Se mejoró responsive design para móviles. Se agregaron accesibilidad improvements.
  - **Jefe Inmediato:** La interfaz es mucho más intuitiva ahora. Buen trabajo en accesibilidad.

### **APRENDIZ: ANGHELICA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Testing y corrección de errores en módulo de clientes. Implementación de validaciones adicionales y manejo de excepciones.
* **FECHA DE INICIO:** 25/11/2025
* **FECHA DE FIN:** 26/11/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Se corrigieron 6 bugs de validación. Se implementaron tests unitarios básicos. Se mejoró manejo de excepciones.
  - **Jefe Inmediato:** Buen trabajo en calidad. Los tests ayudarán a mantener la estabilidad futura.

---

## RESUMEN GENERAL DEL PERÍODO

**Logros principales:**
- 3 módulos core funcionales (usuarios, habitaciones, clientes)
- Base de datos relacional completa y optimizada
- Arquitectura MVC con API REST
- Documentación técnica completa
- Presentación exitosa al cliente

**Próximos pasos:**
- Desarrollar módulo de reservas
- Implementar dashboard con estadísticas
- Integrar sistema de pagos
- Testing de integración completo

**Observaciones del instructor:**
Excelente primer quincena. El equipo trabaja bien en conjunto, el código es limpio y bien documentado. Se mantiene buen ritmo de desarrollo.
