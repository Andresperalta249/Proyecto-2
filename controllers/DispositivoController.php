<?php
class DispositivoController extends Controller {
    private $dispositivoModel;
    private $mascotaModel;
    private $logModel;

    public function __construct() {
        parent::__construct();
        $this->dispositivoModel = $this->loadModel('Dispositivo');
        $this->mascotaModel = $this->loadModel('Mascota');
        $this->logModel = $this->loadModel('Log');
    }

    public function indexAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        // Obtener dispositivos del usuario con información de mascotas
        $dispositivos = $this->dispositivoModel->getDispositivosWithMascotas($_SESSION['user_id']);

        $title = 'Mis Dispositivos';
        $content = $this->render('dispositivos/index', ['dispositivos' => $dispositivos]);
        require_once 'views/layouts/main.php';
    }

    public function createAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        // Obtener solo mascotas del usuario que no tengan dispositivo asignado
        $mascotas = $this->mascotaModel->getMascotasSinDispositivos($_SESSION['user_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->validateRequest(['nombre', 'tipo', 'mascota_id', 'descripcion']);
            
            // Generar identificador único
            $data['identificador'] = $this->generarIdentificador();
            $data['usuario_id'] = $_SESSION['user_id'];
            $data['estado'] = 'activo';

            if ($this->dispositivoModel->createDispositivo($data)) {
                $this->logModel->crearLog($_SESSION['user_id'], 'Creación de dispositivo: ' . $data['nombre']);
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Dispositivo registrado correctamente',
                    'redirect' => BASE_URL . 'dispositivos'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Error al registrar el dispositivo'
                ], 500);
            }
        }

        $title = 'Nuevo Dispositivo';
        $content = $this->render('dispositivos/create', ['mascotas' => $mascotas]);
        require_once 'views/layouts/main.php';
    }

    public function editAction($id = null) {
        if (!isset($_SESSION['user_id']) || !$id) {
            redirect('auth/login');
        }

        $dispositivo = $this->dispositivoModel->findById($id);
        if (!$dispositivo || $dispositivo['usuario_id'] != $_SESSION['user_id']) {
            redirect('dispositivos');
        }

        // Obtener mascotas sin dispositivo + la mascota actualmente asignada
        $mascotas = $this->mascotaModel->getMascotasSinDispositivos($_SESSION['user_id']);
        if ($dispositivo['mascota_id']) {
            $mascotaActual = $this->mascotaModel->findById($dispositivo['mascota_id']);
            if ($mascotaActual) {
                $mascotas[] = $mascotaActual;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->validateRequest(['nombre', 'tipo', 'mascota_id', 'descripcion', 'estado']);

            if ($this->dispositivoModel->updateDispositivo($id, $data)) {
                $this->logModel->crearLog($_SESSION['user_id'], 'Actualización de dispositivo: ' . $data['nombre']);
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Dispositivo actualizado correctamente',
                    'redirect' => BASE_URL . 'dispositivos'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Error al actualizar el dispositivo'
                ], 500);
            }
        }

        $title = 'Editar Dispositivo';
        $content = $this->render('dispositivos/edit', [
            'dispositivo' => $dispositivo,
            'mascotas' => $mascotas
        ]);
        require_once 'views/layouts/main.php';
    }

    public function deleteAction($id = null) {
        if (!isset($_SESSION['user_id']) || !$id) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Acceso denegado'
            ], 403);
        }

        // Verificar que el dispositivo pertenezca al usuario
        $dispositivo = $this->dispositivoModel->findById($id);
        if (!$dispositivo || $dispositivo['usuario_id'] != $_SESSION['user_id']) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Dispositivo no encontrado'
            ], 404);
        }

        if ($this->dispositivoModel->deleteDispositivo($id)) {
            $this->logModel->crearLog($_SESSION['user_id'], 'Eliminación de dispositivo: ' . $dispositivo['nombre']);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Dispositivo eliminado correctamente'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Error al eliminar el dispositivo'
            ], 500);
        }
    }

    private function generarIdentificador() {
        return 'DEV-' . strtoupper(uniqid());
    }
}
?> 