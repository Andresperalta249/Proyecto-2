<?php
class UsuariosController extends Controller {
    private $userModel;
    private $logModel;

    public function __construct() {
        parent::__construct();
        if (!verificarPermiso('ver_usuarios')) {
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }
        $this->userModel = $this->loadModel('User');
        $this->logModel = $this->loadModel('Log');
    }

    public function indexAction() {
        try {
            $usuarios = $this->userModel->getUsuarios();
            $roles = $this->userModel->getRolesDisponibles();
            
            $title = 'Gestión de Usuarios';
            $content = $this->render('usuarios/index', [
                'usuarios' => $usuarios,
                'roles' => $roles
            ]);
            
            require_once 'views/layouts/main.php';
        } catch (Exception $e) {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Error al cargar la página de usuarios: ' . $e->getMessage()
            ];
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }
    }

    public function buscarAction() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $nombre = $_POST['nombre'] ?? '';
                $rol = $_POST['rol'] ?? '';
                $estado = $_POST['estado'] ?? '';

                $usuarios = $this->userModel->buscarUsuarios($nombre, $rol, $estado);
                echo $this->render('usuarios/tabla', ['usuarios' => $usuarios], true);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }

    public function createAction() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (!verificarPermiso('crear_usuarios')) {
                    throw new Exception('No tiene permiso para crear usuarios');
                }

                $data = $_POST;
                
                // Log temporal para depuración
                file_put_contents('logs/error.log', print_r($data, true), FILE_APPEND);
                
                // Validaciones básicas
                if (empty($data['nombre']) || empty($data['email']) || empty($data['password']) || empty($data['rol_id'])) {
                    throw new Exception('Todos los campos obligatorios deben estar completos');
                }

                if ($data['password'] !== $data['confirm_password']) {
                    throw new Exception('Las contraseñas no coinciden');
                }

                // Eliminar confirm_password antes de guardar en la base de datos
                unset($data['confirm_password']);

                if ($this->userModel->insertUsuario($data)) {
                    $this->logModel->crearLog($_SESSION['user_id'], 'Creó un usuario: ' . $data['email']);
                    echo json_encode(['success' => true, 'message' => 'Usuario creado correctamente']);
                } else {
                    throw new Exception('Error al crear el usuario');
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }

    public function updateAction() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (!verificarPermiso('editar_usuarios')) {
                    throw new Exception('No tiene permiso para editar usuarios');
                }

                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) {
                    throw new Exception('ID de usuario inválido');
                }

                $data = $_POST;
                
                // Validaciones básicas
                if (empty($data['nombre']) || empty($data['email'])) {
                    throw new Exception('Todos los campos obligatorios deben estar completos');
                }

                if (!empty($data['password'])) {
                    if ($data['password'] !== $data['confirm_password']) {
                        throw new Exception('Las contraseñas no coinciden');
                    }
                }

                if ($this->userModel->updateUsuario($id, $data)) {
                    $this->logModel->crearLog($_SESSION['user_id'], 'Actualizó el usuario ID: ' . $id);
                    echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente']);
                } else {
                    throw new Exception('Error al actualizar el usuario');
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }

    public function deleteAction() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (!verificarPermiso('eliminar_usuarios')) {
                    throw new Exception('No tiene permiso para eliminar usuarios');
                }

                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) {
                    throw new Exception('ID de usuario inválido');
                }

                if ($this->userModel->deleteUsuario($id)) {
                    $this->logModel->crearLog($_SESSION['user_id'], 'Eliminó el usuario ID: ' . $id);
                    echo json_encode(['success' => true, 'message' => 'Usuario eliminado correctamente']);
                } else {
                    throw new Exception('Error al eliminar el usuario');
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }

    public function estadoAction() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (!verificarPermiso('cambiar_estado_usuarios')) {
                    throw new Exception('No tiene permiso para cambiar el estado de usuarios');
                }

                $id = (int)($_POST['id'] ?? 0);
                $estado = $_POST['estado'] ?? '';
                
                if ($id <= 0) {
                    throw new Exception('ID de usuario inválido');
                }

                if ($this->userModel->cambiarEstadoUsuario($id, $estado)) {
                    $this->logModel->crearLog($_SESSION['user_id'], 'Cambió el estado del usuario ID: ' . $id . ' a ' . $estado);
                    echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
                } else {
                    throw new Exception('Error al actualizar el estado');
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }

    public function getAction() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            try {
                if (!verificarPermiso('editar_usuarios')) {
                    throw new Exception('No tiene permiso para ver detalles de usuarios');
                }

                $id = (int)($_GET['id'] ?? 0);
                if ($id <= 0) {
                    throw new Exception('ID de usuario inválido');
                }

                $usuario = $this->userModel->find($id);
                if (!$usuario) {
                    throw new Exception('Usuario no encontrado');
                }

                // Asegurarse de que los datos sensibles no se envían
                unset($usuario['password']);
                
                // Formatear los datos si es necesario
                $usuario['estado'] = $usuario['estado'] ?? 'activo';
                $usuario['rol_id'] = (int)$usuario['rol_id'];

                $this->jsonResponse([
                    'success' => true,
                    'data' => $usuario
                ]);
            } catch (Exception $e) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Método no permitido'
            ], 405);
        }
    }

    public function cambiarEstadoAction($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (!verificarPermiso('cambiar_estado_usuarios')) {
                    throw new Exception('No tiene permiso para cambiar el estado de usuarios');
                }
                $estado = $_POST['estado'] ?? '';
                if ($this->userModel->cambiarEstadoUsuario($id, $estado)) {
                    $this->logModel->crearLog($_SESSION['user_id'], 'Cambió el estado del usuario ID: ' . $id . ' a ' . $estado);
                    echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
                } else {
                    throw new Exception('Error al actualizar el estado');
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }

    public function tablaAction() {
        if (!verificarPermiso('ver_usuarios')) {
            http_response_code(403);
            exit;
        }
        $nombre = $_GET['nombre'] ?? '';
        $rol = $_GET['rol'] ?? '';
        $estado = $_GET['estado'] ?? '';
        $usuarios = $this->userModel->buscarUsuarios($nombre, $rol, $estado);
        echo $this->render('usuarios/tabla', ['usuarios' => $usuarios], true);
        exit;
    }

    public function verificarAsociacionesAction($id) {
        if (!verificarPermiso('eliminar_usuarios')) {
            http_response_code(403);
            exit;
        }
        $mascotas = $this->userModel->getMascotasPorUsuario($id);
        $dispositivos = $this->userModel->getDispositivosPorUsuario($id);
        echo json_encode([
            'success' => true,
            'mascotas' => $mascotas,
            'dispositivos' => $dispositivos
        ]);
        exit;
    }
} 