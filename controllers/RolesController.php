<?php
class RolesController {
    private $rolModel;
    
    public function __construct() {
        // Verificar si el usuario tiene permiso para gestionar roles
        if (!(
            verificarPermiso('ver_roles') ||
            verificarPermiso('crear_roles') ||
            verificarPermiso('editar_roles') ||
            verificarPermiso('eliminar_roles')
        )) {
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
        
        extract(['roles' => $roles, 'permisos' => $permisos]);
        ob_start();
        require 'views/roles/index.php';
        $content = ob_get_clean();
        
        $title = 'Gestión de Roles';
        $menuActivo = 'roles';
        require_once 'views/layouts/main.php';
    }
    
    /**
     * Crea un nuevo rol
     */
    public function createAction() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $nombre = trim($_POST['nombre'] ?? '');
                $descripcion = trim($_POST['descripcion'] ?? '');
                $estado = $_POST['estado'] ?? 'activo';
                $permisos = json_decode($_POST['permisos'] ?? '[]', true);
                
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
                
                if ($this->rolModel->createRol($nombre, $permisos, $descripcion, $estado)) {
                    echo json_encode(['success' => true, 'message' => 'Rol creado correctamente']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Error al crear el rol']);
                }
            } catch (Exception $e) {
                error_log("Error en createAction: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => 'Error al procesar la solicitud: ' . $e->getMessage()]);
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
            $descripcion = trim($_POST['descripcion'] ?? '');
            $estado = $_POST['estado'] ?? 'activo';
            $permisos = json_decode($_POST['permisos'] ?? '[]', true);
            
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
                if ($this->rolModel->updateRol($id, $nombre, $permisos, $descripcion, $estado)) {
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
     * Devuelve la cantidad de usuarios asociados a un rol
     */
    public function usuariosAsociadosAction() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id'];
            $cantidad = $this->rolModel->countUsuariosAsociados($id);
            echo json_encode(['success' => true, 'cantidad' => $cantidad]);
        }
    }
    
    /**
     * Elimina un rol
     */
    public function deleteAction() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id = (int)$_POST['id'];
                if ($id <= 3) {
                    echo json_encode(['success' => false, 'error' => 'No se pueden eliminar los roles predeterminados']);
                    return;
                }
                $cantidad = $this->rolModel->countUsuariosAsociados($id);
                if ($cantidad > 0 && empty($_POST['forzar'])) {
                    echo json_encode(['success' => false, 'usuarios_asociados' => $cantidad, 'error' => 'Hay usuarios asociados a este rol']);
                    return;
                }
                // Si se fuerza la eliminación, primero dejar a los usuarios sin rol
                if ($cantidad > 0 && !empty($_POST['forzar'])) {
                    $this->rolModel->quitarRolAUsuarios($id);
                }
                if ($this->rolModel->delete($id)) {
                    echo json_encode(['success' => true, 'message' => 'Rol eliminado correctamente']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Error al eliminar el rol']);
                }
            } catch (Exception $e) {
                error_log("Error en deleteAction: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => 'Error al eliminar el rol: ' . $e->getMessage()]);
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
            try {
                $roles = $this->rolModel->getAll();
                $data = array_map(function($rol) {
                    return [
                        'id' => $rol['id'],
                        'nombre' => $rol['nombre'],
                        'descripcion' => $rol['descripcion'] ?? '',
                        'estado' => $rol['estado'] ?? 'activo',
                        'permisos' => $rol['permisos'] ?? []
                    ];
                }, $roles);
                
                // Filtrar solo roles activos
                $data = array_filter($data, function($rol) {
                    return $rol['estado'] === 'activo';
                });
                
                echo json_encode(['success' => true, 'data' => array_values($data)]);
            } catch (Exception $e) {
                error_log("Error en listAction: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => 'Error al obtener los roles']);
            }
        }
    }
    
    /**
     * Cambia el estado de un rol (activo/inactivo) vía AJAX
     */
    public function cambiarEstadoAction() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verificarPermiso('editar_roles')) {
                echo json_encode(['success' => false, 'error' => 'No tiene permiso para cambiar el estado de roles']);
                return;
            }
            $id = (int)($_POST['id'] ?? 0);
            $estado = $_POST['estado'] ?? '';
            if ($id <= 3) {
                echo json_encode(['success' => false, 'error' => 'No se puede cambiar el estado de roles predeterminados']);
                return;
            }
            if (!in_array($estado, ['activo', 'inactivo'])) {
                echo json_encode(['success' => false, 'error' => 'Estado inválido']);
                return;
            }
            $rolModel = $this->rolModel;
            $ok = $rolModel->update($id, ['estado' => $estado]);
            if ($ok) {
                echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error al actualizar el estado']);
            }
        }
    }

    /**
     * Obtiene los detalles de un rol específico
     */
    public function getByIdAction() {
        if (!isset($_POST['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }

        $id = (int)$_POST['id'];
        $rol = $this->rolModel->getById($id);

        if ($rol) {
            echo json_encode(['success' => true, 'data' => $rol]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Rol no encontrado']);
        }
    }
} 