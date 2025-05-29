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

        $alertas = $this->alertaModel->getAllAlertas();
        $mascotaModel = $this->loadModel('Mascota');
        $mascotas = $mascotaModel->findAll();

        $rolNombre = $_SESSION['rol_nombre'] ?? 'Usuario';
        switch (strtolower($rolNombre)) {
            case 'superadministrador': $badgeColor = 'primary'; break;
            case 'administrador': $badgeColor = 'success'; break;
            case 'usuario': $badgeColor = 'info'; break;
            default: $badgeColor = 'secondary'; break;
        }

        $title = 'Alertas';
        $menuActivo = 'alertas';
        $content = $this->render('alertas/index', [
            'alertas' => $alertas,
            'mascotas' => $mascotas
        ]);
        require_once 'views/layouts/main.php';
    }
    // Puedes agregar aquí otros métodos como marcarLeida, eliminar, etc.
} 