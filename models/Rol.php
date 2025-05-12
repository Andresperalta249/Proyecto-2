<?php
class Rol {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtiene todos los roles
     * @return array Array con los roles
     */
    public function getAll() {
        $sql = "SELECT r.*, GROUP_CONCAT(p.nombre) as permisos 
                FROM roles r 
                LEFT JOIN roles_permisos rp ON r.id = rp.rol_id 
                LEFT JOIN permisos p ON rp.permiso_id = p.id 
                GROUP BY r.id 
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
        $sql = "SELECT r.*, GROUP_CONCAT(p.id) as permiso_ids 
                FROM roles r 
                LEFT JOIN roles_permisos rp ON r.id = rp.rol_id 
                LEFT JOIN permisos p ON rp.permiso_id = p.id 
                WHERE r.id = ? 
                GROUP BY r.id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crea un nuevo rol
     * @param string $nombre Nombre del rol
     * @param array $permisos Array con los IDs de los permisos
     * @return bool True si se creó correctamente
     */
    public function create($nombre, $permisos) {
        try {
            $this->db->beginTransaction();
            
            // Insertar rol
            $sql = "INSERT INTO roles (nombre) VALUES (?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nombre]);
            $rol_id = $this->db->lastInsertId();
            
            // Asignar permisos
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
     * @return bool True si se actualizó correctamente
     */
    public function update($id, $nombre, $permisos) {
        try {
            $this->db->beginTransaction();
            
            // Verificar si es un rol predeterminado
            if ($id <= 3) {
                throw new Exception("No se pueden modificar los roles predeterminados");
            }
            
            // Actualizar rol
            $sql = "UPDATE roles SET nombre = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nombre, $id]);
            
            // Eliminar permisos actuales
            $sql = "DELETE FROM roles_permisos WHERE rol_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            
            // Asignar nuevos permisos
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
        $sql = "SELECT * FROM permisos ORDER BY nombre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 