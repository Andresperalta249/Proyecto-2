<?php
error_log('SESSION: ' . print_r($_SESSION, true));

class MascotasController extends Controller {
    private $mascotaModel;
    private $dispositivoModel;
    private $logModel;
    private $notificacionModel;

    public function __construct() {
        parent::__construct();
        $this->mascotaModel = $this->loadModel('Mascota');
        $this->dispositivoModel = $this->loadModel('Dispositivo');
        $this->logModel = $this->loadModel('Log');
        $this->notificacionModel = $this->loadModel('Notificacion');
    }

    public function indexAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        // Obtener mascotas del usuario
        $mascotas = $this->mascotaModel->getMascotasByUser($_SESSION['user_id']);
        $usuariosModel = $this->loadModel('User');
        $usuarios = $usuariosModel->getActiveUsers();
        $title = 'Mis Mascotas';
        $content = $this->render('mascotas/index', ['mascotas' => $mascotas, 'usuarios' => $usuarios]);
        require_once 'views/layouts/main.php';
    }

    public function createAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->validateRequest(['nombre', 'especie', 'tamano', 'fecha_nacimiento']);
            $data['usuario_id'] = $_SESSION['user_id'];

            // Validar que todos los campos estén completos
            foreach (['nombre', 'especie', 'tamano', 'fecha_nacimiento'] as $campo) {
                if (empty($data[$campo])) {
                    $this->jsonResponse([
                        'success' => false,
                        'error' => 'Todos los campos son obligatorios.'
                    ], 400);
                }
            }

            // Validar nombre único por usuario
            $existe = $this->mascotaModel->findAll([
                'usuario_id' => $_SESSION['user_id'],
                'nombre' => $data['nombre']
            ]);
            if ($existe) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Ya existe una mascota con ese nombre.'
                ], 400);
            }

            // Permisos para propietario y estado
            $puedeAsignarPropietario = in_array('gestionar_mascotas', $_SESSION['permissions'] ?? []);
            if ($puedeAsignarPropietario && isset($_POST['propietario_id'])) {
                $data['propietario_id'] = $_POST['propietario_id'];
            }
            if (isset($_POST['estado'])) {
                $data['estado'] = $_POST['estado'];
            }

            if ($this->mascotaModel->createMascota($data)) {
                $this->logModel->crearLog($_SESSION['user_id'], 'Creación de mascota: ' . $data['nombre']);
                $this->notificacionModel->crearNotificacion(
                    $_SESSION['user_id'],
                    'Nueva Mascota Registrada',
                    'Has registrado exitosamente a ' . $data['nombre'],
                    'exito',
                    BASE_URL . 'mascotas/view/' . $this->mascotaModel->getLastInsertId()
                );
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Mascota registrada correctamente',
                    'redirect' => BASE_URL . 'mascotas'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Error al registrar la mascota'
                ], 500);
            }
        }

        // Obtener usuarios activos para el select de propietario
        $usuariosModel = $this->loadModel('User');
        $usuarios = $usuariosModel->getActiveUsers();
        $title = 'Nueva Mascota';
        $content = $this->render('mascotas/edit_modal', ['usuarios' => $usuarios]);
        require_once 'views/layouts/main.php';
    }

    public function editAction($id = null) {
        if (!isset($_SESSION['user_id']) || !$id) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Acceso denegado'
            ], 403);
        }

        $mascota = $this->mascotaModel->findById($id);
        if (!$mascota) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Mascota no encontrada'
            ], 404);
        }

        // Verificar permisos
        $esAdmin = in_array($_SESSION['user_role'] ?? 0, [1,2]);
        $puedeEditarCualquiera = in_array('editar_cualquier_mascota', $_SESSION['permissions'] ?? []);
        $puedeEditarPropias = in_array('editar_mascotas', $_SESSION['permissions'] ?? []);

        if (!$esAdmin && !$puedeEditarCualquiera && !($puedeEditarPropias && $mascota['usuario_id'] == $_SESSION['user_id'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'No tienes permisos para editar esta mascota'
            ], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre' => $_POST['nombre'] ?? '',
                'especie' => $_POST['especie'] ?? '',
                'tamano' => $_POST['tamano'] ?? '',
                'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? '',
                'genero' => $_POST['genero'] ?? ''
            ];

            // Solo permitir cambiar propietario y estado si tiene permisos
            if ($esAdmin || $puedeEditarCualquiera) {
                if (isset($_POST['propietario_id'])) {
                    $data['propietario_id'] = $_POST['propietario_id'];
                }
                if (isset($_POST['estado'])) {
                    $data['estado'] = $_POST['estado'];
                }
            }

            if ($this->mascotaModel->updateMascota($id, $data)) {
                $this->logModel->crearLog($_SESSION['user_id'], 'Actualización de mascota: ' . $data['nombre']);
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Mascota actualizada correctamente'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Error al actualizar la mascota'
                ], 500);
            }
        }
    }

    public function deleteAction($id = null) {
        if (!isset($_SESSION['user_id']) || !$id) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Acceso denegado'
            ], 403);
        }

        // Verificar que la mascota pertenezca al usuario
        $mascota = $this->mascotaModel->findById($id);
        if (!$mascota || $mascota['usuario_id'] != $_SESSION['user_id']) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Mascota no encontrada'
            ], 404);
        }

        // Eliminar imagen si existe (eliminado porque ya no se maneja imagen)
        // if ($mascota['imagen']) {
        //     $this->deleteImage($mascota['imagen']);
        // }

        if ($this->mascotaModel->deleteMascota($id)) {
            $this->logModel->crearLog($_SESSION['user_id'], 'Eliminación de mascota: ' . $mascota['nombre']);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Mascota "' . $mascota['nombre'] . '" eliminada correctamente'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Error al eliminar la mascota'
            ], 500);
        }
    }

    public function viewAction($id = null) {
        if (!isset($_SESSION['user_id']) || !$id) {
            redirect('auth/login');
        }

        // Verificar que la mascota pertenezca al usuario
        $mascota = $this->mascotaModel->findById($id);
        if (!$mascota || $mascota['usuario_id'] != $_SESSION['user_id']) {
            redirect('mascotas');
        }

        // Obtener dispositivos asignados
        $dispositivos = $this->dispositivoModel->getDispositivosByMascota($id);
        
        $title = 'Detalles de Mascota';
        $content = $this->render('mascotas/view', [
            'mascota' => $mascota,
            'dispositivos' => $dispositivos
        ]);
        require_once 'views/layouts/main.php';
    }

    public function guardarAction() {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Acceso denegado'
            ], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre' => $_POST['nombre'] ?? '',
                'especie' => $_POST['especie'] ?? '',
                'tamano' => $_POST['tamano'] ?? '',
                'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? '',
                'usuario_id' => $_SESSION['user_id']
            ];
            if (isset($_POST['genero'])) {
                $data['genero'] = $_POST['genero'];
            }
            if (isset($_POST['propietario_id'])) {
                $data['propietario_id'] = $_POST['propietario_id'];
            }
            if (isset($_POST['estado'])) {
                $data['estado'] = $_POST['estado'];
            }

            if ($this->mascotaModel->createMascota($data)) {
                $this->logModel->crearLog($_SESSION['user_id'], 'Creación de mascota: ' . $data['nombre']);
                $this->jsonResponse([
                    'status' => 'success',
                    'message' => 'Mascota guardada exitosamente'
                ]);
            } else {
                $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'Error al guardar la mascota'
                ]);
            }
        }
    }

    public function crearAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }
        $usuariosModel = $this->loadModel('User');
        $usuarios = $usuariosModel->getActiveUsers();
        // Renderizar solo el formulario sin el layout
        echo $this->render('mascotas/edit_modal', ['usuarios' => $usuarios], true);
        exit;
    }

    private function handleImageUpload($file) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }

        if ($file['size'] > $maxSize) {
            return false;
        }

        $uploadDir = 'uploads/mascotas/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = uniqid() . '_' . basename($file['name']);
        $uploadFile = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
            return $filename;
        }

        return false;
    }

    private function handleDocumentUpload($file) {
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        $maxSize = 10 * 1024 * 1024; // 10MB

        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }

        if ($file['size'] > $maxSize) {
            return false;
        }

        $uploadDir = 'uploads/documentos/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = uniqid() . '_' . basename($file['name']);
        $uploadFile = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
            return $filename;
        }

        return false;
    }

    private function deleteImage($filename) {
        $filepath = 'uploads/mascotas/' . $filename;
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    public function tablaAction() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            exit;
        }

        // Obtener filtros desde GET
        $filtros = [
            'usuario_id' => $_SESSION['user_id'],
            'nombre' => $_GET['nombre'] ?? '',
            'especie' => $_GET['especie'] ?? '',
            'estado' => $_GET['estado'] ?? ''
        ];

        $mascotas = $this->mascotaModel->getMascotasFiltradas($filtros);
        
        // Obtener usuarios activos para mostrar información del propietario
        $usuariosModel = $this->loadModel('User');
        $usuarios = $usuariosModel->getActiveUsers();
        
        echo $this->render('mascotas/tabla', [
            'mascotas' => $mascotas,
            'usuarios' => $usuarios
        ], true);
        exit;
    }

    public function cambiarEstadoAction($id = null) {
        if (!isset($_SESSION['user_id']) || !$id) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Acceso denegado'
            ], 403);
        }
        $mascota = $this->mascotaModel->findById($id);
        if (!$mascota || $mascota['usuario_id'] != $_SESSION['user_id']) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Mascota no encontrada'
            ], 404);
        }
        $nuevoEstado = $_POST['estado'] ?? '';
        if (!in_array($nuevoEstado, ['activo', 'inactivo'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Estado no válido'
            ], 400);
        }
        if ($this->mascotaModel->updateMascota($id, ['estado' => $nuevoEstado])) {
            $this->jsonResponse([
                'success' => true,
                'message' => 'Estado actualizado correctamente'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'error' => 'No se pudo actualizar el estado'
            ], 500);
        }
    }

    public function editarModalAction($id = null) {
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => 'Debes iniciar sesión para realizar esta acción.'
            ]);
            exit;
        }

        // Obtener el ID de la mascota de la URL o del POST
        $id = $id ?? $_GET['id'] ?? null;
        
        if (!$id) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'ID de mascota no proporcionado.'
            ]);
            exit;
        }

        $mascota = $this->mascotaModel->findById($id);
        
        if (!$mascota) {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Mascota no encontrada.'
            ]);
            exit;
        }

        // Verificar permisos
        $esAdmin = in_array($_SESSION['user_role'] ?? 0, [1,2]);
        $puedeEditarCualquiera = in_array('editar_cualquier_mascota', $_SESSION['permissions'] ?? []);
        $puedeEditarPropias = in_array('editar_mascotas', $_SESSION['permissions'] ?? []);

        if (!$esAdmin && !$puedeEditarCualquiera && !($puedeEditarPropias && $mascota['usuario_id'] == $_SESSION['user_id'])) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => 'No tienes permisos para editar esta mascota.'
            ]);
            exit;
        }

        $dispositivos = $this->dispositivoModel->getDispositivosByMascota($id);
        $usuariosModel = $this->loadModel('User');
        $usuarios = $usuariosModel->getActiveUsers();

        // Renderizar el formulario de edición
        echo $this->render('mascotas/edit_modal', [
            'mascota' => $mascota,
            'dispositivos' => $dispositivos,
            'usuarios' => $usuarios
        ], true);
        exit;
    }
}
?> 