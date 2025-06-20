<?php
require_once 'core/Controller.php';
require_once 'core/Model.php';
require_once 'models/UsuarioModel.php';
require_once 'models/Rol.php';

/**
 * Controlador para la gestión de usuarios.
 * Maneja todas las operaciones CRUD y relacionadas con los usuarios del sistema.
 */
class UsuariosController extends Controller {

    private $usuarioModel;
    private $Rol;

    public function __construct() {
        parent::__construct();
        $this->usuarioModel = new UsuarioModel();
    }

    /**
     * Muestra la página principal de gestión de usuarios.
     * Carga el layout y la vista principal del módulo de usuarios.
     */
    public function indexAction() {
        if (!verificarPermiso('ver_usuarios')) {
            $this->view->render('errors/403');
            return;
        }
        
        $this->view->setLayout('main');
        $this->view->setData('titulo', 'Gestión de Usuarios');
        $this->view->setData('subtitulo', 'Administración de usuarios y sus roles en el sistema.');
        $this->view->render('usuarios/index');
    }

    /**
     * Proporciona los datos de los usuarios para DataTables.
     * Maneja la paginación, búsqueda y ordenamiento del lado del servidor.
     */
    public function obtenerUsuariosAction() {
        if (!verificarPermiso('ver_usuarios')) {
            $this->jsonResponse(['error' => 'No tienes permiso'], 403);
            return;
        }

        $draw = $_POST['draw'] ?? 1;
        $start = $_POST['start'] ?? 0;
        $length = $_POST['length'] ?? 10;
        $search = $_POST['search']['value'] ?? '';

        // Total de registros sin filtrar
        $totalRecords = $this->usuarioModel->contarUsuarios();

        // Total de registros con filtro (si hay búsqueda)
        $recordsFiltered = $totalRecords;
        if (!empty($search)) {
            $recordsFiltered = $this->usuarioModel->contarUsuarios($search);
        }

        $usuarios = $this->usuarioModel->obtenerUsuariosPaginados($start, $length, $search);

        $data = [];
        foreach ($usuarios as $usuario) {
            $data[] = [
                'id' => $usuario['id_usuario'],
                'nombre' => htmlspecialchars($usuario['nombre']),
                'email' => htmlspecialchars($usuario['email']),
                'telefono' => htmlspecialchars($usuario['telefono'] ?? ''),
                'rol' => htmlspecialchars($usuario['rol_nombre']),
                'direccion' => htmlspecialchars($usuario['direccion'] ?? ''),
                'estado' => $usuario['estado']
            ];
        }

        $this->jsonResponse([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
    }

    /**
     * Crea un nuevo usuario en el sistema.
     * Valida los datos y los inserta en la base de datos.
     */
    public function crearAction() {
        if (!$this->isPostRequest() || !verificarPermiso('crear_usuarios')) {
            return $this->jsonResponse(['success' => false, 'error' => 'Acción no permitida.'], 403);
        }

        $datos = $this->_obtenerDatosDelRequest();
        
        // Verificar que Administrador no pueda crear Super Administrador
        if ($datos['rol_id'] == 3 && !verificarPermiso('crear_super_admin')) {
            return $this->jsonResponse(['success' => false, 'error' => 'No tienes permisos para crear Super Administradores.'], 403);
        }

        $errores = $this->_validarDatosUsuario($datos, true);

        if (!empty($errores)) {
            return $this->jsonResponse(['success' => false, 'errors' => $errores], 400);
        }

        $datos['password'] = password_hash($datos['password'], PASSWORD_BCRYPT);

        $resultado = $this->usuarioModel->crearUsuario($datos);

        if (is_numeric($resultado)) {
            $this->jsonResponse(['success' => true, 'message' => 'Usuario creado correctamente.']);
        } else {
            // El modelo devolvió un mensaje de error
            $this->jsonResponse([
                'success' => false, 
                'message' => 'No se pudo procesar la solicitud.',
                'error_code' => 'DB_INSERT_ERROR',
                'details' => $resultado // Aquí va el error de PDO
            ], 500);
        }
    }

    /**
     * Edita un usuario existente.
     * Valida los datos y los actualiza en la base de datos.
     */
    public function editarAction() {
        if (!$this->isPostRequest() || !verificarPermiso('editar_usuarios')) {
            return $this->jsonResponse(['success' => false, 'error' => 'Acción no permitida.'], 403);
        }

        $datos = $this->_obtenerDatosDelRequest();
        
        // Verificar que el usuario existe
        $usuario = $this->usuarioModel->obtenerUsuarioPorId($datos['id_usuario']);
        if (!$usuario) {
            return $this->jsonResponse(['success' => false, 'error' => 'Usuario no encontrado.'], 404);
        }

        // Proteger Super Administrador (rol_id = 3)
        if ($usuario['rol_id'] == 3) {
            return $this->jsonResponse(['success' => false, 'error' => 'No se puede editar un Super Administrador.'], 400);
        }

        // Si no es Super Administrador, no puede editar roles básicos
        if (!verificarPermiso('editar_roles_basicos') && in_array($usuario['rol_id'], [1, 2, 3])) {
            return $this->jsonResponse(['success' => false, 'error' => 'No tienes permisos para editar usuarios con roles básicos.'], 403);
        }

        // Verificar que Administrador no pueda cambiar rol a Super Administrador
        if ($datos['rol_id'] == 3 && !verificarPermiso('crear_super_admin')) {
            return $this->jsonResponse(['success' => false, 'error' => 'No tienes permisos para asignar rol de Super Administrador.'], 403);
        }

        $esCambioDePassword = !empty($datos['password']);
        $errores = $this->_validarDatosUsuario($datos, $esCambioDePassword, $datos['id_usuario']);

        if (!empty($errores)) {
            return $this->jsonResponse(['success' => false, 'errors' => $errores], 400);
        }

        if ($esCambioDePassword) {
            $datos['password'] = password_hash($datos['password'], PASSWORD_BCRYPT);
        }

        unset($datos['confirm_password']);

        error_log("EDITAR USUARIO - DATOS ENVIADOS AL MODELO: " . print_r($datos, true));

        if ($this->usuarioModel->actualizarUsuario($datos)) {
            $this->jsonResponse(['success' => true, 'message' => 'Usuario actualizado correctamente.']);
        } else {
            $this->jsonResponse(['success' => false, 'error' => 'Error al actualizar el usuario.'], 500);
        }
    }
    
    /**
     * Carga el HTML del formulario de creación/edición de usuario.
     * @param int|null $id El ID del usuario a editar. Si es null, es un formulario de creación.
     */
    public function cargarFormularioAction($id = null) {
        $this->Rol = $this->loadModel('Rol');
        $roles = $this->Rol->getAll();
        
        $data = ['roles' => $roles];
        
        if ($id) {
            $this->usuarioModel = $this->loadModel('UsuarioModel');
            $usuario = $this->usuarioModel->obtenerUsuarioPorId($id);
            if ($usuario) {
                $data['usuario'] = $usuario;
            }
        }
        
        echo $this->view->render('usuarios/form', $data, true);
    }

    /**
     * Cambia el estado (activo/inactivo) de un usuario.
     */
    public function toggleEstadoAction() {
        error_log("[DEBUG] toggleEstadoAction llamado");
        error_log("[DEBUG] POST recibido: " . print_r($_POST, true));

        if (!$this->isPostRequest() || !verificarPermiso('editar_usuarios')) {
            error_log("[ERROR] Acción no permitida o método no es POST");
            return $this->jsonResponse(['status' => 'error', 'message' => 'Acción no permitida.'], 403);
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        error_log("[DEBUG] ID filtrado: " . var_export($id, true));
        error_log("[DEBUG] Estado filtrado: " . var_export($estado, true));

        if (!$id || !$estado) {
            error_log("[ERROR] Datos incompletos: id o estado no válidos");
            return $this->jsonResponse(['status' => 'error', 'message' => 'Datos incompletos.'], 400);
        }

        $resultado = $this->usuarioModel->cambiarEstado($id, $estado);
        error_log("[DEBUG] Resultado cambiarEstado: " . var_export($resultado, true));

        if ($resultado) {
            error_log("[INFO] Estado actualizado correctamente para usuario $id");
            $this->jsonResponse(['status' => 'success', 'message' => 'Estado actualizado correctamente.']);
        } else {
            error_log("[ERROR] No se pudo actualizar el estado para usuario $id");
            $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo actualizar el estado.'], 500);
        }
    }

    /**
     * Elimina un usuario del sistema.
     * @param int|null $id El ID del usuario a eliminar.
     */
    public function eliminarUsuarioAction($id = null) {
        if (!$this->isPostRequest() || !verificarPermiso('eliminar_usuarios')) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Acción no permitida.'], 403);
        }

        if (!$id) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'ID de usuario no proporcionado.'], 400);
        }

        // Verificar que el usuario existe
        $usuario = $this->usuarioModel->obtenerUsuarioPorId($id);
        if (!$usuario) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Usuario no encontrado.'], 404);
        }

        // Verificar que no se elimine a sí mismo
        if ($id == $_SESSION['user_id']) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'No puedes eliminar tu propia cuenta.'], 400);
        }

        // Proteger Super Administrador (rol_id = 3) - nadie puede eliminarlo, ni siquiera él mismo
        if ($usuario['rol_id'] == 3) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'No se puede eliminar un Super Administrador.'], 400);
        }

        // Si no es Super Administrador, no puede eliminar roles básicos
        if (!verificarPermiso('eliminar_roles_basicos') && in_array($usuario['rol_id'], [1, 2, 3])) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'No tienes permisos para eliminar usuarios con roles básicos.'], 403);
        }

        if ($this->usuarioModel->delete($id)) {
            $this->jsonResponse(['status' => 'success', 'message' => 'Usuario eliminado correctamente.']);
        } else {
            $this->jsonResponse(['status' => 'error', 'message' => 'Error al eliminar el usuario.'], 500);
        }
    }

    /**
     * Verifica si la solicitud actual es de tipo POST.
     * @return bool
     */
    private function isPostRequest() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Recoge y sanitiza los datos del usuario desde la solicitud POST.
     * @return array
     */
    private function _obtenerDatosDelRequest() {
        return [
            'id_usuario' => filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT),
            'nombre' => filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'email' => filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL),
            'telefono' => filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'direccion' => filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'rol_id' => filter_input(INPUT_POST, 'rol_id', FILTER_VALIDATE_INT),
            'estado' => filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'password' => $_POST['password'] ?? null,
            'confirm_password' => $_POST['confirm_password'] ?? null
        ];
    }

    /**
     * Valida los datos de un usuario para creación o edición.
     * @param array $datos Los datos del usuario a validar.
     * @param bool $esCreacion Indica si la validación es para un nuevo usuario.
     * @param int|null $idUsuario El ID del usuario que se está editando (para ignorarlo en la validación de unicidad).
     * @return array Un array de errores. Vacío si no hay errores.
     */
    private function _validarDatosUsuario($datos, $esCreacion, $idUsuario = null) {
        $errores = [];

        if (empty($datos['nombre'])) {
            $errores['nombre'] = 'El nombre es obligatorio.';
        }
        if (empty($datos['email'])) {
            $errores['email'] = 'El email no es válido.';
        }
        if (empty($datos['rol_id'])) {
            $errores['rol_id'] = 'Debe seleccionar un rol.';
        }
        if ($this->usuarioModel->emailExiste($datos['email'], $idUsuario)) {
            $errores['email'] = 'Este email ya está en uso.';
        }

        if (empty($datos['estado'])) $errores['estado'] = 'El estado es obligatorio.';
        
        // --- Validación de Contraseña ---
        // La contraseña solo es obligatoria al crear un usuario.
        // Al editar, solo se valida si se proporciona una nueva.
        $esPasswordRequerido = $esCreacion;

        if (!empty($datos['password'])) {
            if (strlen($datos['password']) < 8 ||
                !preg_match('/[A-Z]/', $datos['password']) ||
                !preg_match('/[a-z]/', $datos['password']) ||
                !preg_match('/[0-9]/', $datos['password'])) {
                
                $errores['password'] = 'La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número.';
            }

            if ($datos['password'] !== $datos['confirm_password']) {
                $errores['confirm_password'] = 'Las contraseñas no coinciden.';
            }
        } elseif ($esPasswordRequerido) {
            $errores['password'] = 'La contraseña es obligatoria.';
        }

        // Validaciones de permisos
        $usuarioAfectado = $idUsuario ? $this->usuarioModel->obtenerUsuarioPorId($idUsuario) : null;

        // Proteger Super Administrador (rol_id = 3)
        if ($usuarioAfectado && $usuarioAfectado['rol_id'] == 3) {
            $errores['permisos'] = 'No se puede editar un Super Administrador.';
        }
        
        // No se pueden editar roles básicos sin permiso
        if ($usuarioAfectado && !verificarPermiso('editar_roles_basicos') && in_array($usuarioAfectado['rol_id'], [1, 2, 3])) {
            $errores['permisos'] = 'No tienes permisos para editar usuarios con roles básicos.';
        }
        
        // Asignar rol de Super Administrador solo con permiso
        if ($datos['rol_id'] == 3 && !verificarPermiso('crear_super_admin')) {
            $errores['permisos'] = 'No tienes permisos para asignar el rol de Super Administrador.';
        }

        return $errores;
    }
} 