# BITÁCORA 11 - SISTEMA DE GESTIÓN HOTELERA
**Periodo:** 12/12/2025 - 26/12/2025  
**Integrantes:** Olaya Fernanda, Anghelica  
**Proyecto:** Sistema de Gestión Hotelera

---

## COMPETENCIA 1: Llevar a cabo la toma de requerimientos para dar inicio al desarrollo del aplicativo

### **APRENDIZ: OLAYA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Análisis de requerimientos para integración de pasarelas de pago y sistema de notificaciones. Definición de flujos de pago y políticas de reembolso.
* **FECHA DE INICIO:** 12/12/2025
* **FECHA DE FIN:** 15/12/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Se analizaron 3 pasarelas de pago (Stripe, PayPal, MercadoPago). Se definieron flujos de pago parcial y completo. Se especificaron políticas de cancelación.
  - **Jefe Inmediato:** Buen análisis comparativo. Se aprobó Stripe como principal por su API robusta y documentación.

### **APRENDIZ: FERNANDA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Diseño de arquitectura de microservicios para notificaciones y reporting. Definición de cola de mensajes y procesamiento asíncrono.
* **FECHA DE INICIO:** 13/12/2025
* **FECHA DE FIN:** 17/12/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Se diseñó arquitectura con Redis para colas. Se definieron eventos para notificaciones automáticas. Se planificó sistema de reporting asíncrono.
  - **Jefe Inmediato:** Excelente enfoque de microservicios. La arquitectura escalable soportará crecimiento futuro.

### **APRENDIZ: ANGHELICA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Especificación de requerimientos de seguridad y compliance para manejo de pagos. Definición de PCI DSS y protección de datos financieros.
* **FECHA DE INICIO:** 14/12/2025
* **FECHA DE FIN:** 18/12/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Se investigaron requerimientos PCI DSS Level 4. Se diseñó tokenización de datos de tarjetas. Se especificaron políticas de retención de datos.
  - **Jefe Inmediato:** Análisis de seguridad muy completo. Se implementarán todas las medidas de compliance.

---

## COMPETENCIA 2: Creación de base de datos para recopilar información

### **APRENDIZ: OLAYA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Diseño e implementación de tablas para pagos y transacciones. Creación de auditoría financiera y reconciliación.
* **FECHA DE INICIO:** 16/12/2025
* **FECHA DE FIN:** 20/12/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Se crearon tablas de pagos, refunds y reconciliación. Se implementó auditoría con trail completo. Se agregaron índices para consultas financieras.
  - **Jefe Inmediato:** Buen diseño de estructura financiera. Los índices optimizarán reportes contables significativamente.

### **APRENDIZ: FERNANDA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Implementación de sistema de logging y auditoría centralizado. Creación de tablas para trazabilidad de acciones y eventos del sistema.
* **FECHA DE INICIO:** 17/12/2025
* **FECHA DE FIN:** 21/12/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Sistema de logging con niveles y categorías. Tablas de auditoría con who, what, when. Implementación de retención automática de logs.
  - **Jefe Inmediato:** Excelente implementación de auditoría. El sistema cumple con requerimientos de compliance.

### **APRENDIZ: ANGHELICA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Optimización avanzada de consultas y particionamiento de tablas. Implementación de políticas de retención y archivado.
* **FECHA DE INICIO:** 18/12/2025
* **FECHA DE FIN:** 22/12/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Se particionaron tablas por fecha. Se implementaron políticas de archivado automático. Se optimizaron consultas complejas con CTEs.
  - **Jefe Inmediato:** Excelente optimización para producción. El particionamiento mantendrá rendimiento con grandes volúmenes.

---

## COMPETENCIA 3: Desarrollar el código requerido para la implementación del aplicativo

### **APRENDIZ: OLAYA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Integración completa de Stripe para pagos. Implementación de webhooks y manejo de eventos de pago.
* **FECHA DE INICIO:** 19/12/2025
* **FECHA DE FIN:** 24/12/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Integración Stripe funcional con 3 métodos de pago. Webhooks configurados para 8 eventos. Manejo robusto de errores y reintentos.
  - **Jefe Inmediato:** Excelente integración. El manejo de webhooks asegura sincronización correcta de estados.

### **APRENDIZ: FERNANDA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Desarrollo de sistema de notificaciones (email/SMS). Implementación de plantillas y envío asíncrono.
* **FECHA DE INICIO:** 20/12/2025
* **FECHA DE FIN:** 25/12/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Sistema de notificaciones con SendGrid y Twilio. 12 plantillas de email diseñadas. Cola de procesamiento con Redis. Dificultad con diseño responsive de emails.
  - **Jefe Inmediato:** Sistema de notificaciones muy completo. Las plantillas son profesionales y funcionales.

### **APRENDIZ: ANGHELICA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Implementación de portal de clientes y check-in online. Desarrollo de área personal con historial y gestión de reservas.
* **FECHA DE INICIO:** 21/12/2025
* **FECHA DE FIN:** 26/12/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Portal cliente con perfil, historial y gestión. Check-in online con documentación. Sistema de calificación post-estancia.
  - **Jefe Inmediato:** Portal cliente muy completo y moderno. El check-in online mejora significativamente la experiencia.

---

## COMPETENCIA 4: Presentar al cliente los avances del proyecto

### **APRENDIZ: OLAYA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Presentación de sistema de pagos y portal cliente. Demostración de flujo completo de reserva a pago.
* **FECHA DE INICIO:** 24/12/2025
* **FECHA DE FIN:** 26/12/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Demostración completa de flujo de pago. Portal cliente funcional con todas las características. Se mostraron casos de uso reales.
  - **Jefe Inmediato:** Presentación excepcional. El cliente muy satisfecho con el sistema completo y profesional.

### **APRENDIZ: FERNANDA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Creación de documentación de implementación y manual de administración. Preparación de materiales de training.
* **FECHA DE INICIO:** 23/12/2025
* **FECHA DE FIN:** 25/12/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Documentación de implementación completa. Manual de administración con screenshots. Videos tutoriales para staff del hotel.
  - **Jefe Inmediato:** Documentación de muy alta calidad. Los materiales facilitarán la transición a producción.

### **APRENDIZ: ANGHELICA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Recopilación final de feedback y preparación para UAT. Documentación de requerimientos cumplidos y métricas de éxito.
* **FECHA DE INICIO:** 25/12/2025
* **FECHA DE FIN:** 26/12/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Feedback final muy positivo. 98% de requerimientos cumplidos. Métricas de rendimiento y usabilidad documentadas.
  - **Jefe Inmediato:** Excelente gestión del proyecto. Las métricas demuestran el éxito del desarrollo.

---

## COMPETENCIA 5: Corrección de errores, bugs y optimización

### **APRENDIZ: OLAYA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Testing de estrés y carga del sistema. Optimización de concurrencia y manejo de picos de tráfico.
* **FECHA DE INICIO:** 22/12/2025
* **FECHA DE FIN:** 26/12/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Testing de carga con 1000 usuarios concurrentes. Optimización de pool de conexiones. Implementación de rate limiting.
  - **Jefe Inmediato:** Excelente preparación para producción. El sistema maneja carga enterprise sin problemas.

### **APRENDIZ: FERNANDA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Optimización final de UX y accesibilidad. Implementación de PWA y mejoras de performance.
* **FECHA DE INICIO:** 23/12/2025
* **FECHA DE FIN:** 26/12/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** PWA funcional con offline mode. Lighthouse score 96/100. Accesibilidad WCAG 2.1 AA compliance.
  - **Jefe Inmediato:** Optimización excepcional. El PWA y accesibilidad posicionan al sistema como líder del mercado.

### **APRENDIZ: ANGHELICA**
* **DESCRIPCIÓN DE LA ACTIVIDAD:** Implementación de monitoring y alertas. Creación de dashboard de salud del sistema y métricas de negocio.
* **FECHA DE INICIO:** 24/12/2025
* **FECHA DE FIN:** 26/12/2025
* **OBSERVACIONES, INASISTENCIAS, DIFICULTADES PRESENTADAS Y/O COMENTARIOS:**
  - **Aprendiz:** Monitoring con Grafana y Prometheus. Alertas configuradas para errores críticos. Dashboard de KPIs de negocio en tiempo real.
  - **Jefe Inmediato:** Excelente implementación de observabilidad. El sistema está listo para operaciones 24/7.

---

## RESUMEN GENERAL DEL PERÍODO

**Logros principales:**
- Integración completa de Stripe con webhooks
- Sistema de notificaciones email/SMS funcional
- Portal de clientes con check-in online
- PWA con offline mode y 96/100 Lighthouse score
- Sistema de monitoring y alertas completo
- Testing de carga para 1000 usuarios concurrentes

**Métricas finales:**
- 98% de requerimientos cumplidos
- 96/100 Lighthouse score
- WCAG 2.1 AA compliance
- 0 bugs críticos
- 1000 usuarios concurrentes soportados
- 99.9% uptime en testing de estrés

**Estado del proyecto:**
- Sistema completo y funcional
- Listo para UAT y producción
- Documentación completa
- Training preparado
- Monitoring implementado

**Próximos pasos:**
- User Acceptance Testing (UAT)
- Despliegue a producción
- Training final del staff
- Go-live y soporte post-lanzamiento

**Observaciones del instructor:**
Quincena excepcional que culmina con un sistema enterprise-ready. El equipo demostró madurez excepcional implementando todas las mejores prácticas de industria. El proyecto superó todas las expectativas de calidad y funcionalidad.
