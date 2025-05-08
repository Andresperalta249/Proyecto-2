<?php
require_once 'config/config.php';
require_once 'core/Autoload.php';

// Obtener la URL solicitada
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = parse_url(BASE_URL, PHP_URL_PATH);
$path = substr($request_uri, strlen($base_path));

// Eliminar parámetros de la URL
$path = parse_url($path, PHP_URL_PATH);

// Si no hay ruta, redirigir al dashboard o login
if ($path == '/' || $path == '') {
    if (isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . 'dashboard');
    } else {
        header('Location: ' . BASE_URL . 'auth/login');
    }
    exit;
}

// Dividir la ruta en segmentos
$segments = explode('/', trim($path, '/'));

// Determinar el controlador y la acción
$controller = !empty($segments[0]) ? $segments[0] : 'dashboard';
$action = !empty($segments[1]) ? $segments[1] : 'index';
$params = array_slice($segments, 2);

// Formatear nombres de controlador y acción
$controller_name = ucfirst($controller) . 'Controller';
$action_name = $action . 'Action';

// Verificar si el controlador existe
if (!file_exists('controllers/' . $controller_name . '.php')) {
    header('HTTP/1.0 404 Not Found');
    include 'views/errors/404.php';
    exit;
}

// Cargar el controlador
require_once 'controllers/' . $controller_name . '.php';
$controller_instance = new $controller_name();

// Verificar si la acción existe
if (!method_exists($controller_instance, $action_name)) {
    header('HTTP/1.0 404 Not Found');
    include 'views/errors/404.php';
    exit;
}

// Ejecutar la acción
call_user_func_array([$controller_instance, $action_name], $params);
?> 