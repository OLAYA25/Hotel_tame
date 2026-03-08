<?php
// Script de prueba para endpoints de notificaciones
session_start();
if (!isset($_SESSION['usuario'])) {
    echo "Por favor inicia sesión primero";
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Notificaciones API</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Test de Endpoints de Notificaciones</h1>
        
        <div class="row">
            <div class="col-md-6">
                <h3>Prueba de Widgets</h3>
                <div id="widget-results"></div>
            </div>
            <div class="col-md-6">
                <h3>Prueba de Notificaciones</h3>
                <div id="notification-results"></div>
            </div>
        </div>
        
        <div class="mt-4">
            <button class="btn btn-primary" onclick="testAll()">Probar Todos</button>
            <button class="btn btn-secondary" onclick="testWidgets()">Probar Widgets</button>
            <button class="btn btn-info" onclick="testNotifications()">Probar Notificaciones</button>
        </div>
    </div>

    <script>
        async function testAll() {
            await testWidgets();
            await testNotifications();
        }
        
        async function testWidgets() {
            const widgetTypes = [
                'system_status',
                'user_activity', 
                'revenue_overview',
                'occupancy_rate',
                'recent_reservations'
            ];
            
            const resultsDiv = document.getElementById('widget-results');
            resultsDiv.innerHTML = '<h4>Resultados de Widgets:</h4>';
            
            for (const widgetId of widgetTypes) {
                try {
                    const response = await fetch(`api/endpoints/widgets.php?id=${widgetId}&type=status`);
                    const data = await response.json();
                    
                    const statusClass = response.ok ? 'success' : 'danger';
                    const resultHtml = `
                        <div class="alert alert-${statusClass} mb-2">
                            <strong>${widgetId}:</strong> ${response.status} - ${response.ok ? 'OK' : 'Error'}
                            <br><small>${JSON.stringify(data).substring(0, 100)}...</small>
                        </div>
                    `;
                    resultsDiv.innerHTML += resultHtml;
                } catch (error) {
                    resultsDiv.innerHTML += `
                        <div class="alert alert-danger mb-2">
                            <strong>${widgetId}:</strong> Error de red - ${error.message}
                        </div>
                    `;
                }
            }
        }
        
        async function testNotifications() {
            const notificationTypes = [
                'system_alerts',
                'security_issues',
                'backup_status',
                'user_activity'
            ];
            
            const resultsDiv = document.getElementById('notification-results');
            resultsDiv.innerHTML = '<h4>Resultados de Notificaciones:</h4>';
            
            for (const type of notificationTypes) {
                try {
                    const response = await fetch(`api/endpoints/notifications.php?type=${type}&user_id=1`);
                    const data = await response.json();
                    
                    const statusClass = response.ok ? 'success' : 'danger';
                    const resultHtml = `
                        <div class="alert alert-${statusClass} mb-2">
                            <strong>${type}:</strong> ${response.status} - ${response.ok ? 'OK' : 'Error'}
                            <br><small>${JSON.stringify(data).substring(0, 100)}...</small>
                        </div>
                    `;
                    resultsDiv.innerHTML += resultHtml;
                } catch (error) {
                    resultsDiv.innerHTML += `
                        <div class="alert alert-danger mb-2">
                            <strong>${type}:</strong> Error de red - ${error.message}
                        </div>
                    `;
                }
            }
        }
        
        // Probar automáticamente al cargar
        window.addEventListener('load', function() {
            setTimeout(testAll, 1000);
        });
    </script>
</body>
</html>
