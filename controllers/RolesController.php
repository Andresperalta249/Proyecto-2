<?php
class RolesController {
    private $rolModel;
    
    public function __construct() {
        // Verificar si el usuario tiene permiso para gestionar roles
        if (!verificarPermiso('gestionar_roles')) {
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }
        
        $this->rolModel = new Rol();
    }
    
    /**
     * Muestra la lista de roles
     */
    public function indexAction() {
        $roles = $this->rolModel->getAll();
        $permisos = $this->rolModel->getPermisos();
        
        $title = 'Gestión de Roles';
        $content = requireToVar('views/roles/index.php', [
            'roles' => $roles,
            'permisos' => $permisos
        ]);
        
        require_once 'views/layouts/main.php';
    }
    
    /**
     * Crea un nuevo rol
     */
    public function createAction() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre'] ?? '');
            $permisos = $_POST['permisos'] ?? [];
            
            // Validaciones
            if (empty($nombre)) {
                echo json_encode(['success' => false, 'error' => 'El nombre del rol es obligatorio']);
                return;
            }
            
            if ($this->rolModel->nombreExiste($nombre)) {
                echo json_encode(['success' => false, 'error' => 'Ya existe un rol con ese nombre']);
                return;
            }
            
            if (empty($permisos)) {
                echo json_encode(['success' => false, 'error' => 'Debe seleccionar al menos un permiso']);
                return;
            }
            
            if ($this->rolModel->create($nombre, $permisos)) {
                echo json_encode(['success' => true, 'message' => 'Rol creado correctamente']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error al crear el rol']);
            }
        }
    }
    
    /**
     * Actualiza un rol existente
     */
    public function updateAction() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id'];
            $nombre = trim($_POST['nombre'] ?? '');
            $permisos = $_POST['permisos'] ?? [];
            
            // Validaciones
            if (empty($nombre)) {
                echo json_encode(['success' => false, 'error' => 'El nombre del rol es obligatorio']);
                return;
            }
            
            if ($this->rolModel->nombreExiste($nombre, $id)) {
                echo json_encode(['success' => false, 'error' => 'Ya existe un rol con ese nombre']);
                return;
            }
            
            if (empty($permisos)) {
                echo json_encode(['success' => false, 'error' => 'Debe seleccionar al menos un permiso']);
                return;
            }
            
            try {
                if ($this->rolModel->update($id, $nombre, $permisos)) {
                    echo json_encode(['success' => true, 'message' => 'Rol actualizado correctamente']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Error al actualizar el rol']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }
    
    /**
     * Elimina un rol
     */
    public function deleteAction() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id'];
            
            try {
                if ($this->rolModel->delete($id)) {
                    echo json_encode(['success' => true, 'message' => 'Rol eliminado correctamente']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Error al eliminar el rol']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }
    
    /**
     * Obtiene los datos de un rol
     */
    public function getAction() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $id = (int)$_GET['id'];
            $rol = $this->rolModel->getById($id);
            
            if ($rol) {
                echo json_encode(['success' => true, 'data' => $rol]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Rol no encontrado']);
            }
        }
    }
    
    public function listAction() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $roles = $this->rolModel->getAll();
            $data = array_map(function($rol) {
                return [
                    'id' => $rol['id'],
                    'nombre' => $rol['nombre']
                ];
            }, $roles);
            echo json_encode(['success' => true, 'data' => $data]);
        }
    }
} 