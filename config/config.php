<?php
// Configuración de zona horaria
date_default_timezone_set('America/Mexico_City');

// Configuración de sesión
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_lifetime', 3600);
session_start();

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Constantes del sistema
define('BASE_URL', 'http://localhost/proyecto-2/');
define('SITE_NAME', 'IoT Pets Monitor');

// Configuración de correo
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'tu_correo@gmail.com');
define('SMTP_PASS', 'tu_contraseña');

// Configuración de archivos
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// Configuración de seguridad
define('HASH_COST', 12); // Para password_hash()

// Configuración de sesión
define('SESSION_LIFETIME', 3600); // 1 hora

// Función para redireccionar
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

// Función para verificar sesión
function checkSession() {
    if (!isset($_SESSION['user_id'])) {
        redirect('auth/login.php');
    }
}

// Función para verificar permisos
function checkPermission($permission) {
    if (!isset($_SESSION['permissions']) || !in_array($permission, $_SESSION['permissions'])) {
        return false;
    }
    return true;
}
?> 