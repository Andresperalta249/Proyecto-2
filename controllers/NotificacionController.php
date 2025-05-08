<?php
require_once 'controllers/Controller.php';
require_once 'models/Notificacion.php';
require_once 'config/firebase.php';
require_once 'models/Usuario.php';
require_once 'models/Log.php';

class NotificacionController extends Controller {
    private $notificacionModel;
    private $usuarioModel;
    private $logModel;

    public function __construct() {
        parent::__construct();
        $this->notificacionModel = new Notificacion();
        $this->usuarioModel = new Usuario();
        $this->logModel = new Log();
    }

    public function indexAction() {
        if (!$this->isAuthenticated()) {
            $this->redirect('auth/login');
        }

        $notificaciones = $this->notificacionModel->getNotificacionesByUser($_SESSION['usuario_id']);
        $this->render('notificaciones/index', ['notificaciones' => $notificaciones]);
    }

    public function getNotificacionesAction() {
        if (!$this->isAuthenticated()) {
            $this->jsonResponse(['error' => 'No autorizado'], 401);
            return;
        }

        $notificaciones = $this->notificacionModel->getNotificacionesByUser($_SESSION['usuario_id']);
        $noLeidas = $this->notificacionModel->getNotificacionesNoLeidas($_SESSION['usuario_id']);

        $this->jsonResponse([
            'notificaciones' => $notificaciones,
            'no_leidas' => $noLeidas
        ]);
    }

    public function marcarLeidaAction() {
        if (!$this->isAuthenticated()) {
            $this->jsonResponse(['error' => 'No autorizado'], 401);
            return;
        }

        $notificacionId = $_POST['id'] ?? null;
        if (!$notificacionId) {
            $this->jsonResponse(['error' => 'ID de notificación requerido'], 400);
            return;
        }

        if ($this->notificacionModel->marcarComoLeida($notificacionId)) {
            $this->jsonResponse(['success' => true]);
        } else {
            $this->jsonResponse(['error' => 'Error al marcar la notificación como leída'], 500);
        }
    }

    public function marcarTodasLeidasAction() {
        if (!$this->isAuthenticated()) {
            $this->jsonResponse(['error' => 'No autorizado'], 401);
            return;
        }

        if ($this->notificacionModel->marcarTodasComoLeidas($_SESSION['usuario_id'])) {
            $this->jsonResponse(['success' => true]);
        } else {
            $this->jsonResponse(['error' => 'Error al marcar las notificaciones como leídas'], 500);
        }
    }

    public function eliminarAction() {
        if (!$this->isAuthenticated()) {
            $this->jsonResponse(['error' => 'No autorizado'], 401);
            return;
        }

        $notificacionId = $_POST['id'] ?? null;
        if (!$notificacionId) {
            $this->jsonResponse(['error' => 'ID de notificación requerido'], 400);
            return;
        }

        if ($this->notificacionModel->eliminarNotificacion($notificacionId)) {
            $this->jsonResponse(['success' => true]);
        } else {
            $this->jsonResponse(['error' => 'Error al eliminar la notificación'], 500);
        }
    }

    public function actualizarFCMTokenAction() {
        // Verificar que sea una petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'error' => 'Método no permitido']);
            return;
        }

        // Verificar que el usuario esté autenticado
        if (!isset($_SESSION['usuario_id'])) {
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado']);
            return;
        }

        // Obtener el token FCM
        $token = $_POST['token'] ?? '';
        if (empty($token)) {
            $this->jsonResponse(['success' => false, 'error' => 'Token no proporcionado']);
            return;
        }

        // Actualizar el token en la base de datos
        $usuarioId = $_SESSION['usuario_id'];
        $success = $this->usuarioModel->actualizarFCMToken($usuarioId, $token);

        if ($success) {
            // Registrar en el log
            $this->logModel->registrarActividad(
                $usuarioId,
                'actualizar_token_fcm',
                'Token FCM actualizado'
            );

            $this->jsonResponse(['success' => true]);
        } else {
            $this->jsonResponse(['success' => false, 'error' => 'Error al actualizar token']);
        }
    }

    public function enviarNotificacionAction() {
        // Verificar que sea una petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'error' => 'Método no permitido']);
            return;
        }

        // Verificar que el usuario esté autenticado
        if (!isset($_SESSION['usuario_id'])) {
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado']);
            return;
        }

        // Obtener datos de la notificación
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['titulo']) || !isset($data['mensaje'])) {
            $this->jsonResponse(['success' => false, 'error' => 'Datos incompletos']);
            return;
        }

        // Obtener el token FCM del usuario
        $usuarioId = $_SESSION['usuario_id'];
        $token = $this->usuarioModel->obtenerFCMToken($usuarioId);

        if (!$token) {
            $this->jsonResponse(['success' => false, 'error' => 'Usuario sin token FCM']);
            return;
        }

        // Preparar el mensaje
        $message = [
            'token' => $token,
            'notification' => [
                'title' => $data['titulo'],
                'body' => $data['mensaje'],
                'icon' => $data['icono'] ?? null,
                'click_action' => $data['url'] ?? null
            ],
            'data' => $data['datos'] ?? []
        ];

        // Enviar la notificación
        $response = enviarNotificacionFCM($message);

        if ($response['success']) {
            // Registrar en el log
            $this->logModel->registrarActividad(
                $usuarioId,
                'enviar_notificacion',
                'Notificación enviada: ' . $data['titulo']
            );

            $this->jsonResponse(['success' => true]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Error al enviar notificación: ' . $response['error']
            ]);
        }
    }

    public function enviarNotificacionMasivaAction() {
        // Verificar que sea una petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'error' => 'Método no permitido']);
            return;
        }

        // Verificar que el usuario esté autenticado y sea administrador
        if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado']);
            return;
        }

        // Obtener datos de la notificación
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['titulo']) || !isset($data['mensaje'])) {
            $this->jsonResponse(['success' => false, 'error' => 'Datos incompletos']);
            return;
        }

        // Obtener todos los tokens FCM válidos
        $tokens = $this->usuarioModel->obtenerTodosFCMTokens();

        if (empty($tokens)) {
            $this->jsonResponse(['success' => false, 'error' => 'No hay tokens FCM disponibles']);
            return;
        }

        // Preparar el mensaje
        $message = [
            'notification' => [
                'title' => $data['titulo'],
                'body' => $data['mensaje'],
                'icon' => $data['icono'] ?? null,
                'click_action' => $data['url'] ?? null
            ],
            'data' => $data['datos'] ?? []
        ];

        // Enviar la notificación a todos los tokens
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($tokens as $token) {
            $message['token'] = $token;
            $response = enviarNotificacionFCM($message);

            if ($response['success']) {
                $successCount++;
            } else {
                $errorCount++;
                $errors[] = $response['error'];
            }
        }

        // Registrar en el log
        $this->logModel->registrarActividad(
            $_SESSION['usuario_id'],
            'enviar_notificacion_masiva',
            "Notificación masiva enviada: {$successCount} exitosas, {$errorCount} fallidas"
        );

        $this->jsonResponse([
            'success' => true,
            'successCount' => $successCount,
            'errorCount' => $errorCount,
            'errors' => $errors
        ]);
    }

    protected function jsonResponse($data, $status = 200) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }
} 