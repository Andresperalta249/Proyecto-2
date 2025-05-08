<?php
class DashboardController extends Controller {
    private $mascotaModel;
    private $dispositivoModel;
    private $alertaModel;
    private $logModel;

    public function __construct() {
        parent::__construct();
        $this->mascotaModel = $this->loadModel('Mascota');
        $this->dispositivoModel = $this->loadModel('Dispositivo');
        $this->alertaModel = $this->loadModel('Alerta');
        $this->logModel = $this->loadModel('Log');
    }

    public function indexAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        // Obtener estadísticas
        $stats = [
            'mascotas' => $this->mascotaModel->count(['usuario_id' => $_SESSION['user_id']]),
            'dispositivos' => $this->dispositivoModel->count(['usuario_id' => $_SESSION['user_id']]),
            'dispositivos_activos' => $this->dispositivoModel->count([
                'usuario_id' => $_SESSION['user_id'],
                'estado' => 'activo'
            ]),
            'alertas' => $this->alertaModel->getEstadisticas($_SESSION['user_id'])
        ];

        // Obtener últimas alertas
        $ultimasAlertas = $this->alertaModel->getAlertasNoLeidas($_SESSION['user_id']);

        // Obtener dispositivos activos
        $dispositivosActivos = $this->dispositivoModel->getDispositivosWithMascotas($_SESSION['user_id']);

        // Obtener actividad reciente
        $actividadReciente = $this->logModel->getActividadReciente($_SESSION['user_id']);

        $title = 'Dashboard';
        $content = $this->render('dashboard/index', [
            'stats' => $stats,
            'ultimasAlertas' => $ultimasAlertas,
            'dispositivosActivos' => $dispositivosActivos,
            'actividadReciente' => $actividadReciente
        ]);
        require_once 'views/layouts/main.php';
    }

    public function getStatsAction() {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Acceso denegado'
            ], 403);
        }

        $stats = [
            'mascotas' => $this->mascotaModel->count(['usuario_id' => $_SESSION['user_id']]),
            'dispositivos' => $this->dispositivoModel->count(['usuario_id' => $_SESSION['user_id']]),
            'dispositivos_activos' => $this->dispositivoModel->count([
                'usuario_id' => $_SESSION['user_id'],
                'estado' => 'activo'
            ]),
            'alertas' => $this->alertaModel->getEstadisticas($_SESSION['user_id'])
        ];

        $this->jsonResponse([
            'success' => true,
            'data' => $stats
        ]);
    }
}
?> 