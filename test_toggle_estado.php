<?php
// Script de prueba para toggleEstado
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurar el log de errores
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

require_once 'config/config.php';
require_once 'core/Database.php';
require_once 'core/Model.php';
require_once 'models/UsuarioModel.php';

echo "=== PRUEBA DE TOGGLE ESTADO ===\n";

try {
    // Crear instancia del modelo
    $usuarioModel = new UsuarioModel();
    echo "Modelo creado correctamente\n";
    
    // Probar directamente con valores hardcodeados
    $id = 1;
    $estado = 'activo';
    
    echo "Datos de prueba:\n";
    echo "ID: " . $id . "\n";
    echo "Estado: " . $estado . "\n";
    
    // Ejecutar el método
    $resultado = $usuarioModel->cambiarEstado($id, $estado);
    
    echo "Resultado: " . ($resultado ? 'TRUE' : 'FALSE') . "\n";
    
    if ($resultado) {
        echo "SUCCESS: Estado actualizado correctamente\n";
    } else {
        echo "ERROR: No se pudo actualizar el estado\n";
    }
    
    // Probar con estado inactivo
    $estado2 = 'inactivo';
    echo "\nProbando con estado 'inactivo':\n";
    $resultado2 = $usuarioModel->cambiarEstado($id, $estado2);
    echo "Resultado: " . ($resultado2 ? 'TRUE' : 'FALSE') . "\n";
    
} catch (Exception $e) {
    echo "EXCEPCIÓN CAPTURADA:\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    
    // Registrar en el log
    error_log("Error en test_toggle_estado: " . $e->getMessage() . "\n" . $e->getTraceAsString());
}

echo "=== FIN DE PRUEBA ===\n";
?> 