<?php
class ApiController extends Controller {
    private $dispositivoModel;
    private $mascotaModel;
    private $alertaModel;
    private $logModel;

    public function __construct() {
        parent::__construct();
        $this->dispositivoModel = $this->loadModel('Dispositivo');
        $this->mascotaModel = $this->loadModel('Mascota');
        $this->alertaModel = $this->loadModel('Alerta');
        $this->logModel = $this->loadModel('Log');
    }

    public function authenticateAction() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Método no permitido'], 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['device_id']) || !isset($data['api_key'])) {
            $this->jsonResponse(['error' => 'Credenciales incompletas'], 400);
        }

        $dispositivo = $this->dispositivoModel->findByDeviceId($data['device_id']);
        
        if (!$dispositivo || $dispositivo['api_key'] !== $data['api_key']) {
            $this->jsonResponse(['error' => 'Credenciales inválidas'], 401);
        }

        // Generar token JWT
        $token = $this->generateJWT($dispositivo);
        
        $this->jsonResponse([
            'token' => $token,
            'expires_in' => 3600 // 1 hora
        ]);
    }

    public function sendDataAction() {
        if (!$this->validateToken()) {
            $this->jsonResponse(['error' => 'Token inválido o expirado'], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Método no permitido'], 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['device_id']) || !isset($data['readings'])) {
            $this->jsonResponse(['error' => 'Datos incompletos'], 400);
        }

        $dispositivo = $this->dispositivoModel->findByDeviceId($data['device_id']);
        
        if (!$dispositivo) {
            $this->jsonResponse(['error' => 'Dispositivo no encontrado'], 404);
        }

        // Procesar lecturas
        foreach ($data['readings'] as $reading) {
            $this->processReading($dispositivo, $reading);
        }

        // Actualizar última lectura
        $this->dispositivoModel->updateLastReading($dispositivo['id']);

        $this->jsonResponse(['success' => true]);
    }

    public function getCommandsAction() {
        if (!$this->validateToken()) {
            $this->jsonResponse(['error' => 'Token inválido o expirado'], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(['error' => 'Método no permitido'], 405);
        }

        $deviceId = $_GET['device_id'] ?? null;
        
        if (!$deviceId) {
            $this->jsonResponse(['error' => 'ID de dispositivo requerido'], 400);
        }

        $dispositivo = $this->dispositivoModel->findByDeviceId($deviceId);
        
        if (!$dispositivo) {
            $this->jsonResponse(['error' => 'Dispositivo no encontrado'], 404);
        }

        $commands = $this->dispositivoModel->getPendingCommands($dispositivo['id']);
        
        $this->jsonResponse(['commands' => $commands]);
    }

    public function updateStatusAction() {
        if (!$this->validateToken()) {
            $this->jsonResponse(['error' => 'Token inválido o expirado'], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Método no permitido'], 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['device_id']) || !isset($data['status'])) {
            $this->jsonResponse(['error' => 'Datos incompletos'], 400);
        }

        $dispositivo = $this->dispositivoModel->findByDeviceId($data['device_id']);
        
        if (!$dispositivo) {
            $this->jsonResponse(['error' => 'Dispositivo no encontrado'], 404);
        }

        $this->dispositivoModel->updateStatus($dispositivo['id'], $data['status']);
        
        $this->jsonResponse(['success' => true]);
    }

    private function processReading($dispositivo, $reading) {
        // Guardar lectura en la base de datos
        $this->dispositivoModel->saveReading($dispositivo['id'], $reading);

        // Verificar alertas
        $this->checkAlerts($dispositivo, $reading);
    }

    private function checkAlerts($dispositivo, $reading) {
        $alertas = $this->alertaModel->getAlertasByDispositivo($dispositivo['id']);
        
        foreach ($alertas as $alerta) {
            if ($this->shouldTriggerAlert($alerta, $reading)) {
                $this->triggerAlert($dispositivo, $alerta, $reading);
            }
        }
    }

    private function shouldTriggerAlert($alerta, $reading) {
        switch ($alerta['tipo']) {
            case 'temperatura':
                return $reading['temperatura'] > $alerta['valor_max'] || 
                       $reading['temperatura'] < $alerta['valor_min'];
            
            case 'humedad':
                return $reading['humedad'] > $alerta['valor_max'] || 
                       $reading['humedad'] < $alerta['valor_min'];
            
            case 'movimiento':
                return $reading['movimiento'] > $alerta['valor_max'];
            
            default:
                return false;
        }
    }

    private function triggerAlert($dispositivo, $alerta, $reading) {
        $mensaje = $this->generateAlertMessage($alerta, $reading);
        
        $this->alertaModel->create([
            'dispositivo_id' => $dispositivo['id'],
            'tipo' => $alerta['tipo'],
            'mensaje' => $mensaje,
            'valor' => json_encode($reading),
            'estado' => 'pendiente'
        ]);

        // Notificar al usuario
        $this->notifyUser($dispositivo['usuario_id'], $mensaje);
    }

    private function generateAlertMessage($alerta, $reading) {
        $mascota = $this->mascotaModel->findById($alerta['mascota_id']);
        
        switch ($alerta['tipo']) {
            case 'temperatura':
                return sprintf(
                    "¡Alerta! La temperatura de %s está fuera del rango normal (%.1f°C)",
                    $mascota['nombre'],
                    $reading['temperatura']
                );
            
            case 'humedad':
                return sprintf(
                    "¡Alerta! La humedad del ambiente de %s está fuera del rango normal (%.1f%%)",
                    $mascota['nombre'],
                    $reading['humedad']
                );
            
            case 'movimiento':
                return sprintf(
                    "¡Alerta! Se detectó movimiento inusual en el área de %s",
                    $mascota['nombre']
                );
            
            default:
                return "¡Alerta! Se ha detectado una condición anormal";
        }
    }

    private function generateJWT($dispositivo) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'device_id' => $dispositivo['device_id'],
            'exp' => time() + 3600
        ]);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', 
            $base64UrlHeader . "." . $base64UrlPayload, 
            JWT_SECRET, 
            true
        );
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    private function validateToken() {
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;

        if (!$token) {
            return false;
        }

        $token = str_replace('Bearer ', '', $token);
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return false;
        }

        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);

        if (!$payload || !isset($payload['exp']) || $payload['exp'] < time()) {
            return false;
        }

        return true;
    }

    private function notifyUser($userId, $mensaje) {
        // Implementar notificación (email, push, etc.)
        // Por ahora solo guardamos en el log
        $this->logModel->crearLog($userId, $mensaje);
    }
} 