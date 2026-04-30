#!/bin/bash
# scripts/verificar-reestructuracion.sh

echo "=== VERIFICACIÓN DE REESTRUCTURACIÓN - HOTEL TAME ==="
echo "🚨 IMPORTANTE: Esta verificación debe hacerse en el navegador 🚨"
echo ""

echo "=== 1. VERIFICANDO ESTRUCTURA DE ARCHIVOS ==="
echo "✅ Router principal:"
if [ -f "index.php" ]; then
    echo "   ✓ index.php existe"
else
    echo "   ❌ index.php NO existe"
fi

echo "✅ Vistas públicas:"
for file in home.php portal_cliente.php login.php habitaciones.php; do
    if [ -f "frontend/views/public/$file" ]; then
        echo "   ✓ frontend/views/public/$file"
    else
        echo "   ❌ frontend/views/public/$file NO existe"
    fi
done

echo "✅ Vistas privadas:"
for file in index.php reservas.php clientes.php usuarios.php contabilidad.php reportes.php settings.php; do
    if [ -f "frontend/views/private/$file" ]; then
        echo "   ✓ frontend/views/private/$file"
    else
        echo "   ❌ frontend/views/private/$file NO existe"
    fi
done

echo "✅ Backend includes:"
for file in header.php footer.php sidebar.php auth_middleware.php simple_permissions.php; do
    if [ -f "backend/includes/$file" ]; then
        echo "   ✓ backend/includes/$file"
    else
        echo "   ❌ backend/includes/$file NO existe"
    fi
done

echo "✅ Backend config:"
if [ -f "backend/config/database.php" ]; then
    echo "   ✓ backend/config/database.php"
else
    echo "   ❌ backend/config/database.php NO existe"
fi

echo ""
echo "=== 2. URLs PARA VERIFICAR MANUALMENTE EN NAVEGADOR ==="
echo "📋 Abre estas URLs en tu navegador y verifica que se vean EXACTAMENTE IGUAL:"
echo ""
echo "🏠 Página principal:"
echo "   http://localhost/Hotel_tame/"
echo "   → Debe mostrar el home del hotel"
echo ""
echo "👤 Portal cliente:"
echo "   http://localhost/Hotel_tame/portal-cliente"
echo "   → Debe mostrar el portal de clientes"
echo ""
echo "🔐 Login:"
echo "   http://localhost/Hotel_tame/login"
echo "   → Debe mostrar el formulario de login"
echo ""
echo "🏢 Habitaciones:"
echo "   http://localhost/Hotel_tame/habitaciones"
echo "   → Debe mostrar el catálogo de habitaciones"
echo ""
echo "📊 Dashboard (requiere login):"
echo "   http://localhost/Hotel_tame/dashboard"
echo "   → Debe mostrar el dashboard admin"
echo ""
echo "📋 Reservas (requiere login):"
echo "   http://localhost/Hotel_tame/reservas"
echo "   → Debe mostrar la gestión de reservas"
echo ""
echo "👥 Clientes (requiere login):"
echo "   http://localhost/Hotel_tame/clientes"
echo "   → Debe mostrar la gestión de clientes"
echo ""
echo "⚙️  Configuración (requiere login admin):"
echo "   http://localhost/Hotel_tame/settings"
echo "   → Debe mostrar la configuración del sistema"
echo ""

echo "=== 3. CHECKLIST DE VERIFICACIÓN VISUAL CRÍTICA ==="
echo "🔍 Para cada página, verificar:"
echo "   ✓ Los estilos CSS cargan correctamente"
echo "   ✓ Las imágenes se muestran"
echo "   ✓ El layout es idéntico al original"
echo "   ✓ Los colores son los mismos"
echo "   ✓ La tipografía es la misma"
echo "   ✓ Bootstrap funciona"
echo "   ✓ Select2 funciona (en formularios)"
echo "   ✓ Los menús funcionan"
echo "   ✓ Las redirecciones funcionan"
echo "   ✓ La base de datos conecta"
echo "   ✓ No hay errores en consola JavaScript"
echo ""

echo "=== 4. PRUEBA DE API ENDPOINTS ==="
echo "🔗 Probar estos endpoints (deben devolver JSON):"
echo "   http://localhost/Hotel_tame/api/clientes"
echo "   http://localhost/Hotel_tame/api/reservas"
echo "   http://localhost/Hotel_tame/api/habitaciones"
echo "   http://localhost/Hotel_tame/api/notifications"
echo "   http://localhost/Hotel_tame/api/widgets"
echo ""

echo "=== 5. SEÑALES DE PELIGRO - DETENER SI: ==="
echo "🚨 ALGO ANDA MAL SI:"
echo "   ❌ Los estilos son diferentes"
echo "   ❌ Los colores cambian"
echo "   ❌ El layout se rompe"
echo "   ❌ Las imágenes no cargan"
echo "   ❌ El CSS no se aplica"
echo "   ❌ JavaScript da errores"
echo "   ❌ La base de datos no conecta"
echo "   ❌ El usuario nota cualquier diferencia"
echo ""

echo "=== 6. COMANDO DE ROLLBACK (si algo falla) ==="
echo "🔄 Para restaurar el estado original:"
echo "   cp backups/antes-reestructuracion/*.php ./"
echo "   # O restaurar desde git si está disponible"
echo ""

echo "=== 7. ÉXITO ESPERADO ==="
echo "✅ Si todo funciona bien:"
echo "   • Estructura organizada"
echo "   • Misma apariencia visual"
echo "   • Misma funcionalidad"
echo "   • URLs limpias"
echo "   • Código mantenible"
echo "   • El usuario NO nota la diferencia"
echo ""

echo "🎯 REGLA FINAL: El éxito es que el usuario NO sepa que algo cambió."
echo ""
