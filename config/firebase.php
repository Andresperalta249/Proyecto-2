<?php
// Configuración de Firebase
define('FIREBASE_API_KEY', 'tu-api-key');
define('FIREBASE_AUTH_DOMAIN', 'tu-proyecto.firebaseapp.com');
define('FIREBASE_PROJECT_ID', 'tu-proyecto');
define('FIREBASE_STORAGE_BUCKET', 'tu-proyecto.appspot.com');
define('FIREBASE_MESSAGING_SENDER_ID', 'tu-sender-id');
define('FIREBASE_APP_ID', 'tu-app-id');
define('FIREBASE_MEASUREMENT_ID', 'tu-measurement-id');

// Configuración de la base de datos
define('FIREBASE_DATABASE_URL', 'https://tu-proyecto.firebaseio.com');
define('FIREBASE_SERVICE_ACCOUNT', ROOT_PATH . '/config/firebase-service-account.json');

// Configuración de Firebase Cloud Messaging
define('FCM_SERVER_KEY', 'TU_SERVER_KEY_AQUI'); // Reemplazar con tu Server Key de Firebase

// Configuración del proyecto Firebase
define('FCM_PROJECT_ID', 'TU_PROJECT_ID_AQUI'); // Reemplazar con tu Project ID de Firebase
define('FCM_SENDER_ID', 'TU_SENDER_ID_AQUI'); // Reemplazar con tu Sender ID de Firebase

// Configuración de la aplicación web
define('FCM_WEB_PUSH_CERTIFICATES', [
    'publicKey' => 'TU_PUBLIC_KEY_AQUI', // Reemplazar con tu Public Key de Firebase
    'privateKey' => 'TU_PRIVATE_KEY_AQUI' // Reemplazar con tu Private Key de Firebase
]);

// Configuración de la API de Firebase
define('FCM_API_URL', 'https://fcm.googleapis.com/fcm/send');
define('FCM_API_VERSION', 'v1');

// Configuración de notificaciones push
define('FCM_NOTIFICATION_ICON', '/assets/img/logo.png');
define('FCM_NOTIFICATION_BADGE', '/assets/img/badge.png');
define('FCM_NOTIFICATION_SOUND', 'default');

// Configuración de tiempo de vida de las notificaciones
define('FCM_TTL', 3600); // 1 hora en segundos

// Configuración de reintentos
define('FCM_MAX_RETRIES', 3);
define('FCM_RETRY_DELAY', 1000); // 1 segundo en milisegundos

// Configuración de caché
define('FCM_CACHE_ENABLED', true);
define('FCM_CACHE_TTL', 3600); // 1 hora en segundos

// Configuración de logging
define('FCM_LOG_ENABLED', true);
define('FCM_LOG_FILE', __DIR__ . '/../logs/fcm.log');

// Función para registrar un nuevo token FCM
function registrarTokenFCM($usuarioId, $token) {
    global $db;
    
    $sql = "UPDATE usuarios SET fcm_token = :token WHERE id = :id";
    $stmt = $db->prepare($sql);
    return $stmt->execute(['token' => $token, 'id' => $usuarioId]);
}

// Función para eliminar un token FCM
function eliminarTokenFCM($usuarioId) {
    global $db;
    
    $sql = "UPDATE usuarios SET fcm_token = NULL WHERE id = :id";
    $stmt = $db->prepare($sql);
    return $stmt->execute(['id' => $usuarioId]);
}

// Función para verificar si un token FCM es válido
function verificarTokenFCM($token) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://iid.googleapis.com/iid/info/' . $token);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: key=' . FCM_SERVER_KEY
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode === 200;
}

// Función para registrar un error de FCM
function registrarErrorFCM($error, $context = []) {
    if (!FCM_LOG_ENABLED) {
        return;
    }
    
    $logMessage = date('Y-m-d H:i:s') . ' - ' . $error;
    if (!empty($context)) {
        $logMessage .= ' - Contexto: ' . json_encode($context);
    }
    
    file_put_contents(FCM_LOG_FILE, $logMessage . PHP_EOL, FILE_APPEND);
} 