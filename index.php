<?php
ob_start();
session_start();
// Configuración de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurar el log de errores
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Verificar que podemos escribir en el archivo de log
$logFile = __DIR__ . '/logs/error.log';
if (!file_exists($logFile)) {
    file_put_contents($logFile, "");
}
if (!is_writable($logFile)) {
    die("No se puede escribir en el archivo de log: " . $logFile);
}

// Registrar que la aplicación está iniciando
error_log("Aplicación iniciando - " . date('Y-m-d H:i:s'));

// Cargar la configuración primero
require_once dirname(__FILE__) . '/config/config.php';
require_once ROOT_PATH . '/core/Autoload.php';
require_once ROOT_PATH . '/includes/functions.php';

try {
    // Obtener la URL solicitada
    $request_uri = $_SERVER['REQUEST_URI'];
    $base_path = parse_url(APP_URL, PHP_URL_PATH);
    $path = substr($request_uri, strlen($base_path));

    // Eliminar parámetros de la URL
    $path = parse_url($path, PHP_URL_PATH);

    error_log("Ruta solicitada: " . $path);

    // Si no hay ruta, redirigir al dashboard o login
    if ($path == '/' || $path == '') {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/dashboard');
        } else {
            header('Location: ' . APP_URL . '/auth/login');
        }
        exit;
    }

    // Dividir la ruta en segmentos
    $segments = explode('/', trim($path, '/'));

    // Determinar el controlador y la acción
    $controller = !empty($segments[0]) ? $segments[0] : 'dashboard';
    $action = !empty($segments[1]) ? $segments[1] : 'index';
    $params = array_slice($segments, 2);

    error_log("Controlador: $controller, Acción: $action");

    // Formatear nombres de controlador y acción
    $controller_name = ucfirst($controller) . 'Controller';
    $action_name = $action . 'Action';

    // Verificar si el controlador existe
    $controller_file = ROOT_PATH . '/controllers/' . $controller_name . '.php';
    if (!file_exists($controller_file)) {
        error_log("Controlador no encontrado: " . $controller_file);
        header('HTTP/1.0 404 Not Found');
        include ROOT_PATH . '/views/errors/404.php';
        exit;
    }

    // Cargar el controlador
    require_once $controller_file;
    
    if (!class_exists($controller_name)) {
        error_log("Clase del controlador no encontrada: " . $controller_name);
        throw new Exception("Clase del controlador no encontrada");
    }

    $controller_instance = new $controller_name();

    // Verificar si la acción existe
    if (!method_exists($controller_instance, $action_name)) {
        error_log("Acción no encontrada: " . $action_name);
        header('HTTP/1.0 404 Not Found');
        include ROOT_PATH . '/views/errors/404.php';
        exit;
    }

    // Ejecutar la acción
    call_user_func_array([$controller_instance, $action_name], $params);

} catch (Exception $e) {
    error_log("Error crítico: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    header('HTTP/1.0 500 Internal Server Error');
    include ROOT_PATH . '/views/errors/500.php';
    exit;
}
?> 