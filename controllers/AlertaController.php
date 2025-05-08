<?php
class AlertaController extends Controller {
    private $alertaModel;
    private $dispositivoModel;
    private $logModel;

    public function __construct() {
        parent::__construct();
        $this->alertaModel = $this->loadModel('Alerta');
        $this->dispositivoModel = $this->loadModel('Dispositivo');
        $this->logModel = $this->loadModel('Log');
    }

    public function indexAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        // Obtener alertas del usuario
        $alertas = $this->alertaModel->getAlertasByUser($_SESSION['user_id']);

        $title = 'Mis Alertas';
        $content = $this->render('alertas/index', ['alertas' => $alertas]);
        require_once 'views/layouts/main.php';
    }

    public function marcarLeidaAction($id = null) {
        if (!isset($_SESSION['user_id']) || !$id) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Acceso denegado'
            ], 403);
        }

        // Verificar que la alerta pertenezca al usuario
        $alerta = $this->alertaModel->findById($id);
        if (!$alerta || $alerta['usuario_id'] != $_SESSION['user_id']) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Alerta no encontrada'
            ], 404);
        }

        if ($this->alertaModel->marcarComoLeida($id)) {
            $this->logModel->crearLog($_SESSION['user_id'], 'Alerta marcada como leída: ' . $alerta['mensaje']);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Alerta marcada como leída'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Error al marcar la alerta como leída'
            ], 500);
        }
    }

    public function marcarTodasLeidasAction() {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Acceso denegado'
            ], 403);
        }

        if ($this->alertaModel->marcarTodasComoLeidas($_SESSION['user_id'])) {
            $this->logModel->crearLog($_SESSION['user_id'], 'Todas las alertas marcadas como leídas');
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Todas las alertas han sido marcadas como leídas'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Error al marcar las alertas como leídas'
            ], 500);
        }
    }

    public function eliminarAction($id = null) {
        if (!isset($_SESSION['user_id']) || !$id) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Acceso denegado'
            ], 403);
        }

        // Verificar que la alerta pertenezca al usuario
        $alerta = $this->alertaModel->findById($id);
        if (!$alerta || $alerta['usuario_id'] != $_SESSION['user_id']) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Alerta no encontrada'
            ], 404);
        }

        if ($this->alertaModel->delete($id)) {
            $this->logModel->crearLog($_SESSION['user_id'], 'Alerta eliminada: ' . $alerta['mensaje']);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Alerta eliminada correctamente'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Error al eliminar la alerta'
            ], 500);
        }
    }

    public function getNoLeidasAction() {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Acceso denegado'
            ], 403);
        }

        $alertas = $this->alertaModel->getAlertasNoLeidas($_SESSION['user_id']);
        
        $this->jsonResponse([
            'success' => true,
            'data' => [
                'alertas' => $alertas,
                'total' => count($alertas)
            ]
        ]);
    }
}
?> 