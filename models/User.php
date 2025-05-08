<?php
class User extends Model {
    protected $table = 'usuarios';
    
    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getUserPermissions($userId) {
        $sql = "SELECT p.codigo 
                FROM permisos p 
                INNER JOIN roles_permisos rp ON p.id = rp.permiso_id 
                INNER JOIN usuarios u ON u.rol_id = rp.rol_id 
                WHERE u.id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'codigo');
    }
    
    public function createPasswordReset($userId, $token, $expires) {
        $sql = "INSERT INTO password_resets (user_id, token, expires) 
                VALUES (:user_id, :token, :expires)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':token', $token);
        $stmt->bindValue(':expires', $expires);
        
        return $stmt->execute();
    }
    
    public function getPasswordReset($token) {
        $sql = "SELECT * FROM password_resets WHERE token = :token";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':token', $token);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function deletePasswordReset($token) {
        $sql = "DELETE FROM password_resets WHERE token = :token";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':token', $token);
        return $stmt->execute();
    }
    
    public function updateLastLogin($userId) {
        $sql = "UPDATE {$this->table} SET ultimo_acceso = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $userId);
        return $stmt->execute();
    }
    
    public function getUsersByRole($roleId) {
        return $this->findAll(['rol_id' => $roleId]);
    }
    
    public function getActiveUsers() {
        return $this->findAll(['estado' => 'activo']);
    }
    
    public function getInactiveUsers() {
        return $this->findAll(['estado' => 'inactivo']);
    }
    
    public function activateUser($userId) {
        return $this->update($userId, ['estado' => 'activo']);
    }
    
    public function deactivateUser($userId) {
        return $this->update($userId, ['estado' => 'inactivo']);
    }
    
    public function changeRole($userId, $roleId) {
        return $this->update($userId, ['rol_id' => $roleId]);
    }
    
    public function updateProfile($userId, $data) {
        $allowedFields = ['nombre', 'email', 'telefono', 'direccion'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));
        
        if (!empty($updateData)) {
            return $this->update($userId, $updateData);
        }
        
        return false;
    }
    
    public function changePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
        return $this->update($userId, ['password' => $hashedPassword]);
    }
    
    public function searchUsers($query) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE nombre LIKE :query 
                OR email LIKE :query 
                OR telefono LIKE :query";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':query', "%{$query}%");
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUsersWithRole() {
        $sql = "SELECT u.*, r.nombre as rol_nombre 
                FROM {$this->table} u 
                INNER JOIN roles r ON u.rol_id = r.id 
                ORDER BY u.nombre";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUserStats() {
        $sql = "SELECT 
                    COUNT(*) as total_usuarios,
                    SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as usuarios_activos,
                    SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) as usuarios_inactivos,
                    COUNT(DISTINCT rol_id) as total_roles
                FROM {$this->table}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?> 