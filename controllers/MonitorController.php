<?php
class MonitorController extends Controller {
    private $dispositivoModel;
    private $mascotaModel;

    public function __construct() {
        parent::__construct();
        $this->dispositivoModel = $this->loadModel('Dispositivo');
        $this->mascotaModel = $this->loadModel('Mascota');
    }

    public function indexAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        // Obtener todos los dispositivos activos del usuario
        $dispositivos = $this->dispositivoModel->getDispositivosWithMascotas($_SESSION['user_id']);

        $title = 'Monitor en Vivo';
        $content = $this->render('monitor/index', ['dispositivos' => $dispositivos]);
        require_once 'views/layouts/main.php';
    }

    public function deviceAction($id = null) {
        if (!isset($_SESSION['user_id']) || !$id) {
            redirect('auth/login');
        }

        $dispositivo = $this->dispositivoModel->getDispositivoById($id);
        $puedeVerTodos = function_exists('verificarPermiso') ? verificarPermiso('ver_todos_dispositivo') : false;
        if (!$dispositivo || (!$puedeVerTodos && $dispositivo['propietario_id'] != $_SESSION['user_id'])) {
            $_SESSION['error'] = 'Dispositivo no encontrado o no autorizado.';
            redirect('monitor');
        }

        // Obtener datos del dispositivo
        $ultimosDatos = $this->dispositivoModel->getHistorialCompleto($id);
        $estadisticas = $this->dispositivoModel->getEstadisticasSensores($id);
        $mascota = $this->mascotaModel->findById($dispositivo['mascota_id']);

        if (!$mascota) {
            $_SESSION['error'] = 'La mascota asociada a este dispositivo no existe.';
            redirect('monitor');
        }
        if (!$ultimosDatos || !is_array($ultimosDatos)) {
            $_SESSION['error'] = 'No hay datos recientes para este dispositivo.';
            redirect('monitor');
        }
        if (!$estadisticas || !is_array($estadisticas)) {
            $_SESSION['error'] = 'No hay estadísticas disponibles para este dispositivo.';
            redirect('monitor');
        }

        $title = 'Monitor - ' . $dispositivo['nombre'];
        $menuActivo = 'dispositivos';
        $content = $this->render('monitor/device', [
            'dispositivo' => $dispositivo,
            'mascota' => $mascota,
            'ultimosDatos' => $ultimosDatos,
            'estadisticas' => $estadisticas
        ]);
        require_once 'views/layouts/main.php';
    }

    public function getDataAction($id = null) {
        if (!isset($_SESSION['user_id']) || !$id) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Acceso denegado'
            ], 403);
        }

        $dispositivo = $this->dispositivoModel->getDispositivoById($id);
        $puedeVerTodos = function_exists('verificarPermiso') ? verificarPermiso('ver_todos_dispositivo') : false;
        if (!$dispositivo || (!$puedeVerTodos && $dispositivo['usuario_id'] != $_SESSION['user_id'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Dispositivo no encontrado'
            ], 404);
        }

        // Obtener últimos datos
        $ultimosDatos = $this->dispositivoModel->getUltimosDatos($id);
        $estadisticas = $this->dispositivoModel->getEstadisticasSensores($id);

        $this->jsonResponse([
            'success' => true,
            'data' => [
                'ultimosDatos' => $ultimosDatos,
                'estadisticas' => $estadisticas
            ]
        ]);
    }
}
?> 