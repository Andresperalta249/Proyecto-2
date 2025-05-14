<?php
require_once __DIR__ . '/Model.php';
class Rol extends Model {
    protected $table = 'roles';
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Obtiene todos los roles
     * @return array Array con los roles
     */
    public function getAll() {
        $sql = "SELECT r.id, r.nombre, r.descripcion, r.estado, GROUP_CONCAT(p.nombre) as permisos 
                FROM roles r 
                LEFT JOIN roles_permisos rp ON r.id = rp.rol_id 
                LEFT JOIN permisos p ON rp.permiso_id = p.id 
                GROUP BY r.id, r.nombre, r.descripcion, r.estado 
                ORDER BY r.id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene un rol por su ID
     * @param int $id ID del rol
     * @return array Datos del rol
     */
    public function getById($id) {
        try {
            $sql = "SELECT r.id, r.nombre, r.descripcion, r.estado, 
                    GROUP_CONCAT(DISTINCT p.nombre) as permisos, 
                    GROUP_CONCAT(DISTINCT p.id) as permiso_ids 
                    FROM roles r 
                    LEFT JOIN roles_permisos rp ON r.id = rp.rol_id 
                    LEFT JOIN permisos p ON rp.permiso_id = p.id 
                    WHERE r.id = ? 
                    GROUP BY r.id, r.nombre, r.descripcion, r.estado";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $rol = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($rol) {
                // Asegurarnos de que los permisos sean arrays
                $rol['permisos'] = $rol['permisos'] ? explode(',', $rol['permisos']) : [];
                $rol['permiso_ids'] = $rol['permiso_ids'] ? explode(',', $rol['permiso_ids']) : [];
            }
            
            return $rol;
        } catch (Exception $e) {
            error_log("Error en getById: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crea un nuevo rol
     * @param string $nombre Nombre del rol
     * @param array $permisos Array con los IDs de los permisos
     * @param string $descripcion Descripción del rol
     * @param string $estado Estado del rol
     * @return bool True si se creó correctamente
     */
    public function createRol($nombre, $permisos, $descripcion = '', $estado = 'activo') {
        try {
            $this->db->beginTransaction();
            $sql = "INSERT INTO roles (nombre, descripcion, estado) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nombre, $descripcion, $estado]);
            $rol_id = $this->db->lastInsertId();
            
            if (!empty($permisos)) {
                $sql = "INSERT INTO roles_permisos (rol_id, permiso_id) VALUES (?, ?)";
                $stmt = $this->db->prepare($sql);
                foreach ($permisos as $permiso_id) {
                    $stmt->execute([$rol_id, $permiso_id]);
                }
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error al crear rol: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualiza un rol existente
     * @param int $id ID del rol
     * @param string $nombre Nuevo nombre del rol
     * @param array $permisos Array con los IDs de los permisos
     * @param string $descripcion Nueva descripción del rol
     * @param string $estado Nuevo estado del rol
     * @return bool True si se actualizó correctamente
     */
    public function updateRol($id, $nombre, $permisos, $descripcion = '', $estado = 'activo') {
        try {
            $this->db->beginTransaction();
            if ($id <= 3) {
                throw new Exception("No se pueden modificar los roles predeterminados");
            }
            
            $sql = "UPDATE roles SET nombre = ?, descripcion = ?, estado = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nombre, $descripcion, $estado, $id]);
            
            $sql = "DELETE FROM roles_permisos WHERE rol_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            
            if (!empty($permisos)) {
                $sql = "INSERT INTO roles_permisos (rol_id, permiso_id) VALUES (?, ?)";
                $stmt = $this->db->prepare($sql);
                foreach ($permisos as $permiso_id) {
                    $stmt->execute([$id, $permiso_id]);
                }
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error al actualizar rol: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Elimina un rol
     * @param int $id ID del rol
     * @return bool True si se eliminó correctamente
     */
    public function delete($id) {
        try {
            // Verificar si es un rol predeterminado
            if ($id <= 3) {
                throw new Exception("No se pueden eliminar los roles predeterminados");
            }
            
            $sql = "DELETE FROM roles WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Error al eliminar rol: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica si un nombre de rol ya existe
     * @param string $nombre Nombre del rol
     * @param int $exclude_id ID del rol a excluir (para actualizaciones)
     * @return bool True si el nombre ya existe
     */
    public function nombreExiste($nombre, $exclude_id = null) {
        $sql = "SELECT COUNT(*) FROM roles WHERE nombre = ?";
        $params = [$nombre];
        
        if ($exclude_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_id;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Obtiene todos los permisos disponibles
     * @return array Array con los permisos
     */
    public function getPermisos() {
        $sql = "SELECT id, nombre, codigo FROM permisos ORDER BY nombre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cuenta los usuarios asociados a un rol
     * @param int $rol_id
     * @return int
     */
    public function countUsuariosAsociados($rol_id) {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE rol_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$rol_id]);
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Quita el rol a todos los usuarios asociados (deja rol_id en NULL)
     * @param int $rol_id
     * @return void
     */
    public function quitarRolAUsuarios($rol_id) {
        $sql = "UPDATE usuarios SET rol_id = NULL WHERE rol_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$rol_id]);
    }
} 