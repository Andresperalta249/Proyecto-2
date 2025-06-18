<?php
class RolesController {
    private $model;
    
    public function __construct() {
        $this->model = new Rol();
    }
    
    public function indexAction() {
        // Verificar permisos
        if (!verificarPermiso('roles_ver')) {
            header('Location: ' . APP_URL . '/error/403');
            exit;
        }
        
        $title = 'Administrador de roles';
        ob_start();
        require 'views/roles/index.php';
        $GLOBALS['content'] = ob_get_clean();
        $GLOBALS['title'] = $title;
        $GLOBALS['menuActivo'] = 'roles';
        require_once 'views/layouts/main.php';
    }
    
    public function getAction() {
        // Verificar permisos
        if (!verificarPermiso('roles_editar')) {
            echo json_encode(['success' => false, 'error' => 'No tienes permiso para editar roles']);
            exit;
        }
        
        $id = $_GET['id'] ?? null;
        if ($id) {
            $rol = $this->model->getById($id);
            if ($rol) {
                echo json_encode(['success' => true, 'data' => $rol]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Rol no encontrado']);
            }
        } else {
            // Cargar formulario para nuevo rol, pasando permisos
            $permisos = $this->model->getPermisos();
            $data = [
                'rol' => null,
                'permisos' => $permisos
            ];
            require_once 'views/roles/form.php';
        }
    }
    
    public function createAction() {
        // Verificar permisos
        if (!verificarPermiso('roles_crear')) {
            echo json_encode(['success' => false, 'error' => 'No tienes permiso para crear roles']);
            exit;
        }
        $data = [
            'nombre' => $_POST['nombre'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'estado' => $_POST['estado'] ?? 'activo',
            'permisos' => $_POST['permisos'] ?? []
        ];
        // Validar que al menos un permiso esté seleccionado
        if (empty($data['permisos'])) {
            echo json_encode(['success' => false, 'error' => 'Debes asignar al menos un permiso al rol.']);
            exit;
        }
        if ($this->model->create($data)) {
            echo json_encode(['success' => true, 'message' => 'Rol creado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al crear el rol']);
        }
    }
    
    public function updateAction() {
        // Verificar permisos
        if (!verificarPermiso('roles_editar')) {
            echo json_encode(['success' => false, 'error' => 'No tienes permiso para editar roles']);
            exit;
        }
        $id = $_POST['id_rol'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID de rol no proporcionado']);
            exit;
        }
        $data = [
            'nombre' => $_POST['nombre'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'estado' => $_POST['estado'] ?? 'activo',
        ];
        // Si se envían permisos, usarlos. Si no, mantener los actuales.
        if (isset($_POST['permisos'])) {
            $data['permisos'] = $_POST['permisos'];
            // Si explícitamente no hay ninguno seleccionado, mostrar error
            if (empty($data['permisos'])) {
                echo json_encode(['success' => false, 'error' => 'Debes asignar al menos un permiso al rol.']);
                exit;
            }
        } else {
            // Mantener los permisos actuales
            $rolActual = $this->model->getById($id);
            $data['permisos'] = $rolActual['permiso_ids'] ?? [];
        }
        if ($this->model->update($id, $data)) {
            echo json_encode(['success' => true, 'message' => 'Rol actualizado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al actualizar el rol']);
        }
    }
    
    public function deleteAction() {
        // Verificar permisos
        if (!verificarPermiso('roles_eliminar')) {
            echo json_encode(['success' => false, 'error' => 'No tienes permiso para eliminar roles']);
            exit;
        }
        
        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID de rol no proporcionado']);
            exit;
        }
        
        if ($this->model->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'Rol eliminado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al eliminar el rol']);
        }
    }
    
    public function cambiarEstadoAction() {
        // Verificar permisos
        if (!verificarPermiso('roles_editar')) {
            echo json_encode(['success' => false, 'error' => 'No tienes permiso para editar roles']);
            exit;
        }
        
        $id = $_POST['id'] ?? null;
        $estado = $_POST['estado'] ?? null;
        
        if (!$id || !$estado) {
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
            exit;
        }
        
        if ($this->model->cambiarEstado($id, $estado)) {
            echo json_encode(['success' => true, 'message' => 'Estado actualizado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al cambiar el estado']);
        }
    }
    
    public function getPermisosAction() {
        // Verificar permisos
        if (!verificarPermiso('roles_ver')) {
            echo json_encode(['success' => false, 'error' => 'No tienes permiso para ver roles']);
            exit;
        }
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID de rol no proporcionado']);
            exit;
        }
        $permisos = $this->model->getPermisosPorRol($id);
        if ($permisos !== false) {
            echo json_encode(['success' => true, 'permisos' => $permisos]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al obtener los permisos']);
        }
    }
    
    public function tablaAction() {
        // Verificar permisos
        if (!verificarPermiso('roles_ver')) {
            header('Location: ' . APP_URL . '/error/403');
            exit;
        }
        
        require_once 'views/roles/tabla.php';
    }
    
    public function formAction() {
        // Verificar permisos
        if (!verificarPermiso('roles_editar')) {
            header('Location: ' . APP_URL . '/error/403');
            exit;
        }
        
        $id = $_GET['id'] ?? null;
        $rol = null;
        if ($id) {
            $rol = $this->model->getById($id);
        }
        $permisos = $this->model->getPermisos();
        $data = [
            'rol' => $rol,
            'permisos' => $permisos
        ];
        require 'views/roles/form.php';
    }
    
    public function listAction() {
        // Verificar permisos
        if (!verificarPermiso('roles_ver')) {
            echo json_encode(['success' => false, 'error' => 'No tienes permiso para ver roles']);
            exit;
        }
        $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
        $roles = $this->model->getAll();
        // Mapear id_rol a id para DataTables
        foreach ($roles as &$rol) {
            $rol['id'] = $rol['id_rol'];
        }
        $total = count($roles);
        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $roles
        ]);
        exit;
    }
    
    public function obtenerRolesAction() {
        // Alias de listAction para compatibilidad con DataTables
        $this->listAction();
    }
} 