<?php
class DispositivosController extends Controller {
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
        if (!isset($_SESSION['usuario_id'])) {
            redirect('auth/login');
        }
        $usuario_id = $_SESSION['usuario_id'];
        $puedeVerTodos = verificarPermiso('ver_todos_dispositivo');
        if ($puedeVerTodos) {
            $dispositivos = $this->dispositivoModel->getTodosDispositivosConMascotas();
        } else {
            $dispositivos = $this->dispositivoModel->getDispositivosWithMascotas($usuario_id);
        }
        $title = $puedeVerTodos ? 'Todos los Dispositivos' : 'Mis Dispositivos';
        $content = $this->render('dispositivos/index', ['dispositivos' => $dispositivos]);
        require_once 'views/layouts/main.php';
    }

    public function createAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        $userModel = $this->loadModel('User');
        $usuarios = $userModel->getUsuarios();
        $mascotaModel = $this->mascotaModel;
        $mascotas = $mascotaModel->findAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->validateRequest(['nombre', 'tipo', 'mascota_id', 'descripcion', 'usuario_id']);
            $data['identificador'] = $this->generarIdentificador();
            $data['estado'] = 'activo';
            if (empty($data['usuario_id'])) {
                $data['usuario_id'] = $_SESSION['user_id'];
            }
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
        $content = $this->render('dispositivos/create', [
            'usuarios' => $usuarios,
            'mascotas' => $mascotas
        ]);
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

    public function cambiarEstadoAction() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verificarPermiso('cambiar_estado_dispositivos')) {
                echo json_encode(['success' => false, 'error' => 'No tiene permiso para cambiar el estado de dispositivos']);
                return;
            }
            $id = (int)($_POST['id'] ?? 0);
            $estado = $_POST['estado'] ?? '';
            if (!in_array($estado, ['activo', 'inactivo'])) {
                echo json_encode(['success' => false, 'error' => 'Estado inválido']);
                return;
            }
            $dispositivo = $this->dispositivoModel->findById($id);
            if (!$dispositivo) {
                echo json_encode(['success' => false, 'error' => 'Dispositivo no encontrado']);
                return;
            }
            // Solo superadmin/admin o dueño pueden cambiar el estado
            $usuarioLogueadoId = $_SESSION['user_id'] ?? 0;
            $esSuperAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1;
            if (!$esSuperAdmin && $dispositivo['usuario_id'] != $usuarioLogueadoId) {
                echo json_encode(['success' => false, 'error' => 'No puede cambiar el estado de este dispositivo']);
                return;
            }
            $ok = $this->dispositivoModel->updateDispositivo($id, ['estado' => $estado]);
            if ($ok) {
                echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error al actualizar el estado']);
            }
        }
    }

    private function generarIdentificador() {
        return 'DEV-' . strtoupper(uniqid());
    }
}
?> 