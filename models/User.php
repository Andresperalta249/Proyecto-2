<?php
class User extends Model {
    protected $table = 'usuarios';
    
    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email";
        $result = $this->query($sql, [':email' => $email]);
        return $result ? $result[0] : false;
    }
    
    public function getUserPermissions($userId) {
        $sql = "SELECT p.codigo 
                FROM permisos p 
                INNER JOIN roles_permisos rp ON p.id = rp.permiso_id 
                INNER JOIN usuarios u ON u.rol_id = rp.rol_id 
                WHERE u.id = :user_id";
        
        $result = $this->query($sql, [':user_id' => $userId]);
        return array_column($result, 'codigo');
    }
    
    public function createPasswordReset($userId, $token, $expires) {
        $data = [
            'user_id' => $userId,
            'token' => $token,
            'expires' => $expires
        ];
        return $this->create($data);
    }
    
    public function getPasswordReset($token) {
        $sql = "SELECT * FROM password_resets WHERE token = :token";
        $result = $this->query($sql, [':token' => $token]);
        return $result ? $result[0] : false;
    }
    
    public function deletePasswordReset($token) {
        $sql = "DELETE FROM password_resets WHERE token = :token";
        return $this->query($sql, [':token' => $token]);
    }
    
    public function updateLastLogin($userId) {
        return $this->update($userId, ['ultimo_acceso' => date('Y-m-d H:i:s')]);
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
        
        return $this->query($sql, [':query' => "%{$query}%"]);
    }
    
    public function getUsersWithRole() {
        $sql = "SELECT u.*, r.nombre as rol_nombre 
                FROM {$this->table} u 
                INNER JOIN roles r ON u.rol_id = r.id 
                ORDER BY u.nombre";
        
        return $this->query($sql);
    }
    
    public function getUserStats() {
        $sql = "SELECT 
                    COUNT(*) as total_usuarios,
                    SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as usuarios_activos,
                    SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) as usuarios_inactivos,
                    COUNT(DISTINCT rol_id) as total_roles
                FROM {$this->table}";
        
        $result = $this->query($sql);
        return $result ? $result[0] : false;
    }
}
?> 