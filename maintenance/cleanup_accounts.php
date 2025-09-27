<?php
/**
 * Script de mantenimiento para limpiar cuentas expiradas
 * Debe ejecutarse periódicamente (cada hora) para deshabilitar cuentas no verificadas
 */

require_once '../config/config.php';
require_once '../config/Database.php';
require_once '../config/AccountManager.php';

echo "=== LIMPIEZA DE CUENTAS EXPIRADAS ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $accountManager = new AccountManager();
    
    // Deshabilitar cuentas expiradas
    echo "1. Deshabilitando cuentas expiradas...\n";
    $disabledCount = $accountManager->disableExpiredAccounts();
    echo "   ✅ {$disabledCount} cuentas deshabilitadas\n";
    
    // Limpiar cuentas deshabilitadas antiguas
    echo "2. Limpiando cuentas deshabilitadas antiguas...\n";
    $cleanedCount = $accountManager->cleanDisabledAccounts();
    echo "   ✅ {$cleanedCount} cuentas eliminadas\n";
    
    // Mostrar estadísticas
    echo "3. Estadísticas actuales:\n";
    $stats = $accountManager->getAccountStats();
    foreach ($stats as $stat) {
        echo "   - {$stat['account_status']}: {$stat['count']} cuentas\n";
    }
    
    echo "\n✅ Limpieza completada exitosamente\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    error_log("Error in cleanup_accounts.php: " . $e->getMessage());
}
?>
