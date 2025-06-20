<?php
class UsuariosController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        if (!verificarPermiso('ver_usuarios')) {
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }
        $this->userModel = $this->loadModel('User');
    }

    public function indexAction() {
        $roles = $this->userModel->getRoles();
        
        // Obtener parámetros de paginación y altura de pantalla
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $alturaPantalla = isset($_GET['altura']) ? (int)$_GET['altura'] : null;
        
        // Obtener usuarios con paginación dinámica
        $resultado = $this->userModel->getAll([], $pagina, $alturaPantalla);
        $usuarios = $resultado['usuarios'];
        $totalUsuarios = $resultado['total'];
        $totalPaginas = $resultado['paginas'];
        $porPagina = $resultado['por_pagina'];
        
        ob_start();
        require 'views/usuarios/index.php';
        $content = ob_get_clean();
        $title = 'Administración de usuarios';
        $GLOBALS['content'] = $content;
        $GLOBALS['title'] = $title;
        $GLOBALS['menuActivo'] = 'usuarios';
        require_once 'views/layouts/main.php';
    }

    public function buscarAction() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $termino = $_GET['q'] ?? '';
            $rol_id = $_GET['rol_id'] ?? '';
            $estado = $_GET['estado'] ?? '';
            $usuarios = $this->userModel->buscar($termino, [
                'rol_id' => $rol_id,
                'estado' => $estado
            ]);
            require_once 'views/usuarios/tabla.php';
        }
    }

    public function getAction() {
        if (!verificarPermiso('editar_usuarios')) {
            echo '<div class="alert alert-danger">No tienes permiso para esta acción</div>';
            return;
        }

        $id = $_GET['id_usuario'] ?? null;
        $roles = $this->userModel->getRoles();
        if (empty($roles)) {
            echo '<div class="alert alert-danger">No se encontraron roles disponibles. Por favor, revisa la configuración de roles en el sistema.</div>';
            return;
        }
        $usuario = null;
        if ($id) {
            $usuario = $this->userModel->findById($id);
            if (!$usuario) {
                echo '<div class="alert alert-danger">Usuario no encontrado</div>';
                return;
            }
        }
        require 'views/usuarios/form.php';
    }

    public function crearAction() {
        if (!verificarPermiso('crear_usuarios')) {
            echo json_encode(['success' => false, 'error' => 'No tienes permiso para esta acción']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'nombre' => $_POST['nombre'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'confirm_password' => $_POST['confirm_password'] ?? '',
                'telefono' => $_POST['telefono'] ?? '',
                'direccion' => $_POST['direccion'] ?? '',
                'rol_id' => $_POST['rol_id'] ?? '',
                'estado' => $_POST['estado'] ?? 'activo'
            ];

            // Validar datos
            if (empty($datos['nombre']) || empty($datos['email']) || empty($datos['password'])) {
                echo json_encode(['success' => false, 'error' => 'Todos los campos requeridos deben estar completos']);
                return;
            }

            if ($datos['password'] !== $datos['confirm_password']) {
                echo json_encode(['success' => false, 'error' => 'Las contraseñas no coinciden']);
                return;
            }

            // Validar que el email no exista
            if ($this->userModel->findByEmail($datos['email'])) {
                echo json_encode(['success' => false, 'error' => 'El email ya está registrado']);
                return;
            }

            // Validar rol (solo superadmin puede asignar roles de superadmin y admin)
            $rolUsuarioActual = $_SESSION['user_role'] ?? 0;
            if (($datos['rol_id'] == 1 || $datos['rol_id'] == 2) && $rolUsuarioActual != 1) {
                echo json_encode(['success' => false, 'error' => 'No tienes permiso para asignar este rol']);
                return;
            }

            // Crear usuario
            $resultado = $this->userModel->crear($datos);
            echo json_encode($resultado);
        }
    }

    public function editarAction() {
        if (!verificarPermiso('editar_usuarios')) {
            echo json_encode(['success' => false, 'error' => 'No tienes permiso para esta acción']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_usuario'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'error' => 'ID de usuario no proporcionado']);
                return;
            }

            $usuario = $this->userModel->findById($id);
            if (!$usuario) {
                echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
                return;
            }

            $datos = [
                'id_usuario' => $id,
                'nombre' => $_POST['nombre'] ?? '',
                'telefono' => $_POST['telefono'] ?? '',
                'direccion' => $_POST['direccion'] ?? '',
                'rol_id' => $_POST['rol_id'] ?? '',
                'estado' => $_POST['estado'] ?? 'activo'
            ];

            // Validar datos requeridos
            if (empty($datos['nombre'])) {
                echo json_encode(['success' => false, 'error' => 'El nombre es requerido']);
                return;
            }

            // Validar rol (solo superadmin puede asignar roles de superadmin y admin)
            $rolUsuarioActual = $_SESSION['user_role'] ?? 0;
            if (($datos['rol_id'] == 1 || $datos['rol_id'] == 2) && $rolUsuarioActual != 1) {
                echo json_encode(['success' => false, 'error' => 'No tienes permiso para asignar este rol']);
                return;
            }

            // Si se proporcionó una nueva contraseña
            if (!empty($_POST['password'])) {
                if ($_POST['password'] !== $_POST['confirm_password']) {
                    echo json_encode(['success' => false, 'error' => 'Las contraseñas no coinciden']);
                    return;
                }
                $datos['password'] = $_POST['password'];
            }

            // Actualizar usuario
            $resultado = $this->userModel->actualizar($datos);
            echo json_encode($resultado);
        }
    }

    public function cambiarEstadoAction() {
        if (!verificarPermiso('editar_usuarios')) {
            echo json_encode(['success' => false, 'error' => 'No tienes permiso para esta acción']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_usuario'] ?? null;
            $estado = $_POST['estado'] ?? '';

            if (!$id || !in_array($estado, ['activo', 'inactivo'])) {
                echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
                return;
            }

            // Verificar mascotas asociadas
            $mascotas = $this->userModel->getMascotasAsociadas($id);
            if (!empty($mascotas)) {
                $mensaje = "Este usuario tiene " . count($mascotas) . " mascota(s) asociada(s). ";
                $mensaje .= "Al cambiar el estado a " . ($estado === 'inactivo' ? 'inactivo' : 'activo') . ", ";
                $mensaje .= "las mascotas y sus dispositivos también cambiarán su estado. ¿Desea continuar?";
                
                echo json_encode([
                    'success' => false,
                    'needsConfirmation' => true,
                    'message' => $mensaje,
                    'data' => [
                        'id_usuario' => $id,
                        'estado' => $estado,
                        'mascotas' => $mascotas
                    ]
                ]);
                return;
            }

            $resultado = $this->userModel->cambiarEstado($id, $estado);
            echo json_encode($resultado);
        }
    }

    public function confirmarCambioEstadoAction() {
        if (!verificarPermiso('editar_usuarios')) {
            echo json_encode(['success' => false, 'error' => 'No tienes permiso para esta acción']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_usuario'] ?? null;
            $estado = $_POST['estado'] ?? '';

            if (!$id || !in_array($estado, ['activo', 'inactivo'])) {
                echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
                return;
            }

            // Cambiar estado del usuario y sus mascotas/dispositivos
            $resultado = $this->userModel->cambiarEstadoEnCascada($id, $estado);
            echo json_encode($resultado);
        }
    }

    public function eliminarAction() {
        if (!verificarPermiso('eliminar_usuarios')) {
            echo json_encode(['success' => false, 'error' => 'No tienes permiso para esta acción']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_usuario'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'error' => 'ID de usuario no proporcionado']);
                return;
            }

            // No permitir eliminar el propio usuario
            if ($id == $_SESSION['user_id']) {
                echo json_encode(['success' => false, 'error' => 'No puedes eliminar tu propia cuenta']);
                return;
            }

            // Verificar mascotas y dispositivos asociados
            $mascotas = $this->userModel->getMascotasAsociadas($id);
            $dispositivos = $this->userModel->getDispositivosAsociados($id);

            if (!empty($mascotas) || !empty($dispositivos)) {
                $mensaje = "Este usuario tiene ";
                if (!empty($mascotas)) {
                    $mensaje .= count($mascotas) . " mascota(s) ";
                }
                if (!empty($mascotas) && !empty($dispositivos)) {
                    $mensaje .= "y ";
                }
                if (!empty($dispositivos)) {
                    $mensaje .= count($dispositivos) . " dispositivo(s) ";
                }
                $mensaje .= "asociado(s). Al eliminar el usuario, también se eliminarán todas sus mascotas y dispositivos. ¿Desea continuar?";
                
                echo json_encode([
                    'success' => false,
                    'needsConfirmation' => true,
                    'message' => $mensaje,
                    'data' => [
                        'id_usuario' => $id,
                        'mascotas' => $mascotas,
                        'dispositivos' => $dispositivos
                    ]
                ]);
                return;
            }

            $resultado = $this->userModel->eliminar($id);
            echo json_encode($resultado);
        }
    }

    public function confirmarEliminacionAction() {
        if (!verificarPermiso('eliminar_usuarios')) {
            echo json_encode(['success' => false, 'error' => 'No tienes permiso para esta acción']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_usuario'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'error' => 'ID de usuario no proporcionado']);
                return;
            }

            // Eliminar usuario y sus mascotas/dispositivos
            $resultado = $this->userModel->eliminarEnCascada($id);
            echo json_encode($resultado);
        }
    }

    public function getAsociacionesAction() {
        $id = $_GET['id_usuario'] ?? null;
        if (!$id) {
            echo json_encode(['mascotas' => 0, 'dispositivos' => 0]);
            return;
        }
        // Mascotas asociadas
        $mascotas = $this->userModel->getMascotasAsociadas($id);
        $numMascotas = is_array($mascotas) ? count($mascotas) : 0;
        // Dispositivos asociados a las mascotas
        $numDispositivos = 0;
        if ($numMascotas > 0) {
            $mascotaIds = array_column($mascotas, 'id_mascota');
            if (!empty($mascotaIds)) {
                $numDispositivos = $this->userModel->contarDispositivosPorMascotas($mascotaIds);
            }
        }
        echo json_encode([
            'mascotas' => $numMascotas,
            'dispositivos' => $numDispositivos
        ]);
    }
} 