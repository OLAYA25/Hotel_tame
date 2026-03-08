<!DOCTYPE html>
<html>
<head>
    <title>Hotel Management - Test Independiente</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .sidebar { width: 250px; background: #2c3e50; color: white; position: fixed; height: 100vh; left: 0; top: 0; padding: 20px; }
        .main-content { margin-left: 250px; padding: 20px; }
        .header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .nav-item { padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .nav-item a { color: white; text-decoration: none; display: block; padding: 8px 12px; border-radius: 4px; }
        .nav-item a:hover { background: rgba(255,255,255,0.1); }
        .nav-item.active a { background: #3498db; }
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 20px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-number { font-size: 2em; font-weight: bold; color: #2c3e50; }
        .stat-label { color: #7f8c8d; margin-top: 5px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .table th { background: #f8f9fa; font-weight: bold; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; color: white; }
        .badge-success { background: #27ae60; }
        .badge-warning { background: #f39c12; }
        .badge-info { background: #3498db; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background: #3498db; color: white; }
        .btn-primary:hover { background: #2980b9; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div style="text-align: center; margin-bottom: 30px;">
            <h2>🏨 Hotel Management</h2>
            <div style="margin-top: 15px;">
                <div style="font-size: 14px;">👤 Administrador</div>
                <div style="font-size: 12px; opacity: 0.8;">Sistema Activo</div>
            </div>
        </div>
        <nav>
            <div class="nav-item active">
                <a href="#" onclick="showPage('dashboard')">🏠 Dashboard</a>
            </div>
            <div class="nav-item">
                <a href="#" onclick="showPage('contabilidad')">🧮 Contabilidad</a>
            </div>
            <div class="nav-item">
                <a href="#" onclick="showPage('habitaciones')">🛏️ Habitaciones</a>
            </div>
            <div class="nav-item">
                <a href="#" onclick="showPage('clientes')">👥 Clientes</a>
            </div>
            <div class="nav-item">
                <a href="#" onclick="showPage('reservas')">📅 Reservas</a>
            </div>
            <div class="nav-item">
                <a href="#" onclick="showPage('productos')">📦 Productos</a>
            </div>
            <div class="nav-item">
                <a href="#" onclick="showPage('pedidos')">🛒 Pedidos</a>
            </div>
            <div class="nav-item">
                <a href="#" onclick="showPage('eventos')">🎉 Eventos</a>
            </div>
            <div class="nav-item">
                <a href="#" onclick="showPage('reportes')">📊 Reportes</a>
            </div>
            <div class="nav-item" style="margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.3);">
                <a href="#" onclick="showPage('configuracion')">⚙️ Configuración</a>
            </div>
            <div class="nav-item">
                <a href="logout.php">🚪 Cerrar Sesión</a>
            </div>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h1 id="pageTitle">Dashboard - Sistema de Gestión Hotelera</h1>
            <p id="pageSubtitle">Panel principal de control</p>
        </div>
        
        <div id="pageContent">
            <!-- Dashboard Content -->
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-number">25</div>
                    <div class="stat-label">Total Habitaciones</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">12</div>
                    <div class="stat-label">Reservas Activas</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">156</div>
                    <div class="stat-label">Total Clientes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">$15.2M</div>
                    <div class="stat-label">Ingresos del Mes</div>
                </div>
            </div>
            
            <div class="card">
                <h3>Actividad Reciente</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Habitación</th>
                            <th>Estado</th>
                            <th>Precio</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Juan Pérez</td>
                            <td>Hab. 101</td>
                            <td><span class="badge badge-success">Confirmada</span></td>
                            <td>$250,000</td>
                            <td>2024-12-15</td>
                        </tr>
                        <tr>
                            <td>María García</td>
                            <td>Hab. 205</td>
                            <td><span class="badge badge-warning">Pendiente</span></td>
                            <td>$180,000</td>
                            <td>2024-12-14</td>
                        </tr>
                        <tr>
                            <td>Carlos López</td>
                            <td>Hab. 302</td>
                            <td><span class="badge badge-success">Confirmada</span></td>
                            <td>$320,000</td>
                            <td>2024-12-13</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        function showPage(page) {
            // Remove active class from all nav items
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Add active class to clicked item
            event.target.closest('.nav-item').classList.add('active');
            
            // Update page content based on selection
            const pageTitle = document.getElementById('pageTitle');
            const pageSubtitle = document.getElementById('pageSubtitle');
            const pageContent = document.getElementById('pageContent');
            
            switch(page) {
                case 'dashboard':
                    pageTitle.textContent = 'Dashboard - Sistema de Gestión Hotelera';
                    pageSubtitle.textContent = 'Panel principal de control';
                    pageContent.innerHTML = getDashboardContent();
                    break;
                    
                case 'contabilidad':
                    pageTitle.textContent = 'Módulo Contable';
                    pageSubtitle.textContent = 'Gestión financiera y contable';
                    pageContent.innerHTML = getContabilidadContent();
                    break;
                    
                case 'habitaciones':
                    pageTitle.textContent = 'Gestión de Habitaciones';
                    pageSubtitle.textContent = 'Control de habitaciones y estados';
                    pageContent.innerHTML = getHabitacionesContent();
                    break;
                    
                case 'clientes':
                    pageTitle.textContent = 'Gestión de Clientes';
                    pageSubtitle.textContent = 'Base de datos de clientes';
                    pageContent.innerHTML = getClientesContent();
                    break;
                    
                case 'reservas':
                    pageTitle.textContent = 'Gestión de Reservas';
                    pageSubtitle.textContent = 'Sistema de reservas del hotel';
                    pageContent.innerHTML = getReservasContent();
                    break;
                    
                case 'productos':
                    pageTitle.textContent = 'Gestión de Productos';
                    pageSubtitle.textContent = 'Inventario y productos';
                    pageContent.innerHTML = getProductosContent();
                    break;
                    
                case 'pedidos':
                    pageTitle.textContent = 'Gestión de Pedidos';
                    pageSubtitle.textContent = 'Pedidos de productos y servicios';
                    pageContent.innerHTML = getPedidosContent();
                    break;
                    
                case 'eventos':
                    pageTitle.textContent = 'Gestión de Eventos';
                    pageSubtitle.textContent = 'Organización de eventos';
                    pageContent.innerHTML = getEventosContent();
                    break;
                    
                case 'reportes':
                    pageTitle.textContent = 'Reportes Financieros';
                    pageSubtitle.textContent = 'Análisis y reportes';
                    pageContent.innerHTML = getReportesContent();
                    break;
                    
                case 'configuracion':
                    pageTitle.textContent = 'Configuración';
                    pageSubtitle.textContent = 'Ajustes del sistema';
                    pageContent.innerHTML = getConfiguracionContent();
                    break;
            }
        }
        
        function getDashboardContent() {
            return `
                <div class="stats">
                    <div class="stat-card">
                        <div class="stat-number">25</div>
                        <div class="stat-label">Total Habitaciones</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">12</div>
                        <div class="stat-label">Reservas Activas</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">156</div>
                        <div class="stat-label">Total Clientes</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">$15.2M</div>
                        <div class="stat-label">Ingresos del Mes</div>
                    </div>
                </div>
                
                <div class="card">
                    <h3>Actividad Reciente</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Habitación</th>
                                <th>Estado</th>
                                <th>Precio</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Juan Pérez</td>
                                <td>Hab. 101</td>
                                <td><span class="badge badge-success">Confirmada</span></td>
                                <td>$250,000</td>
                                <td>2024-12-15</td>
                            </tr>
                            <tr>
                                <td>María García</td>
                                <td>Hab. 205</td>
                                <td><span class="badge badge-warning">Pendiente</span></td>
                                <td>$180,000</td>
                                <td>2024-12-14</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            `;
        }
        
        function getContabilidadContent() {
            return `
                <div class="stats">
                    <div class="stat-card">
                        <div class="stat-number">$15.2M</div>
                        <div class="stat-label">Ingresos del Mes</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">$8.7M</div>
                        <div class="stat-label">Egresos del Mes</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">$6.5M</div>
                        <div class="stat-label">Balance Neto</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">47</div>
                        <div class="stat-label">Transacciones</div>
                    </div>
                </div>
                
                <div class="card">
                    <h3>Transacciones Recientes</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Comprobante</th>
                                <th>Fecha</th>
                                <th>Descripción</th>
                                <th>Monto</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>2024-12-001</td>
                                <td>2024-12-15</td>
                                <td>Ingreso por reservas</td>
                                <td>$250,000</td>
                                <td><span class="badge badge-success">Confirmada</span></td>
                            </tr>
                            <tr>
                                <td>2024-12-002</td>
                                <td>2024-12-14</td>
                                <td>Compras suministros</td>
                                <td>$150,000</td>
                                <td><span class="badge badge-success">Confirmada</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            `;
        }
        
        function getHabitacionesContent() {
            return `
                <div class="card">
                    <h3>Estado de Habitaciones</h3>
                    <p>25 habitaciones totales</p>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                        <div style="text-align: center; padding: 20px; background: #d4edda; border-radius: 8px;">
                            <h4>15</h4>
                            <p>Disponibles</p>
                        </div>
                        <div style="text-align: center; padding: 20px; background: #f8d7da; border-radius: 8px;">
                            <h4>8</h4>
                            <p>Ocupadas</p>
                        </div>
                        <div style="text-align: center; padding: 20px; background: #fff3cd; border-radius: 8px;">
                            <h4>2</h4>
                            <p>Mantenimiento</p>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function getClientesContent() {
            return `
                <div class="card">
                    <h3>Clientes Registrados</h3>
                    <p>156 clientes activos</p>
                    <button class="btn btn-primary">+ Nuevo Cliente</button>
                </div>
            `;
        }
        
        function getReservasContent() {
            return `
                <div class="card">
                    <h3>Reservas Activas</h3>
                    <p>12 reservas confirmadas</p>
                    <button class="btn btn-primary">+ Nueva Reserva</button>
                </div>
            `;
        }
        
        function getProductosContent() {
            return `
                <div class="card">
                    <h3>Inventario de Productos</h3>
                    <p>45 productos disponibles</p>
                    <button class="btn btn-primary">+ Nuevo Producto</button>
                </div>
            `;
        }
        
        function getPedidosContent() {
            return `
                <div class="card">
                    <h3>Pedidos Activos</h3>
                    <p>8 pedidos en proceso</p>
                    <button class="btn btn-primary">+ Nuevo Pedido</button>
                </div>
            `;
        }
        
        function getEventosContent() {
            return `
                <div class="card">
                    <h3>Eventos Programados</h3>
                    <p>3 eventos próximos</p>
                    <button class="btn btn-primary">+ Nuevo Evento</button>
                </div>
            `;
        }
        
        function getReportesContent() {
            return `
                <div class="card">
                    <h3>Reportes Financieros</h3>
                    <p>Reportes mensuales y anuales disponibles</p>
                    <button class="btn btn-primary">Generar Reporte</button>
                </div>
            `;
        }
        
        function getConfiguracionContent() {
            return `
                <div class="card">
                    <h3>Configuración del Sistema</h3>
                    <p>Ajustes generales y preferencias</p>
                    <button class="btn btn-primary">Guardar Cambios</button>
                </div>
            `;
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ Sistema Hotel Management cargado correctamente');
        });
    </script>
</body>
</html>
