<?php
class ApiController extends Controller {
    private $dispositivoModel;
    private $mascotaModel;
    private $logModel;

    public function __construct() {
        parent::__construct();
        $this->dispositivoModel = $this->loadModel('Dispositivo');
        $this->mascotaModel = $this->loadModel('Mascota');
        $this->logModel = $this->loadModel('Log');
    }

    public function authenticateAction() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Método no permitido'], 405);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['device_id']) || empty($data['device_id'])) {
            $this->jsonResponse(['error' => 'ID de dispositivo requerido'], 400);
            return;
        }

        $dispositivo = $this->dispositivoModel->findByDeviceId($data['device_id']);
        
        if (!$dispositivo) {
            $this->jsonResponse(['error' => 'Dispositivo no encontrado'], 401);
            return;
        }

        // Generar token JWT
        $token = $this->generateJWT([
            'device_id' => $dispositivo['identificador'],
            'id' => $dispositivo['id']
        ]);
        
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