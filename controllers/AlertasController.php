<?php
class AlertasController extends Controller {
    private $alertaModel;

    public function __construct() {
        parent::__construct();
        $this->alertaModel = $this->loadModel('Alerta');
    }

    public function indexAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        $alertas = $this->alertaModel->findAllWithJoin();
        $mascotaModel = $this->loadModel('Mascota');
        $mascotas = $mascotaModel->findAll();

        // Obtener configuraciones de alertas activas para el formulario
        $configuracionAlertaModel = $this->loadModel('ConfiguracionAlerta');
        $configuraciones_alertas = $configuracionAlertaModel->getAllConfiguraciones();
        $config_alertas = [];
        foreach ($configuraciones_alertas as $conf) {
            $config_alertas[$conf['tipo_sensor']] = $conf;
        }

        $rolNombre = $_SESSION['rol_nombre'] ?? 'Usuario';
        switch (strtolower($rolNombre)) {
            case 'superadministrador': $badgeColor = 'primary'; break;
            case 'administrador': $badgeColor = 'success'; break;
            case 'usuario': $badgeColor = 'info'; break;
            default: $badgeColor = 'secondary'; break;
        }

        $title = 'Administrador de alertas';
        $menuActivo = 'alertas';
        $content = $this->render('alertas/index', [
            'alertas' => $alertas,
            'mascotas' => $mascotas,
            'config_alertas' => $config_alertas
        ]);
        $GLOBALS['content'] = $content;
        $GLOBALS['title'] = $title;
        $GLOBALS['menuActivo'] = 'alertas';
        require_once 'views/layouts/main.php';
    }
    // Puedes agregar aquí otros métodos como marcarLeida, eliminar, etc.
} 