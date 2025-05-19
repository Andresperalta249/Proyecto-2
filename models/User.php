<?php
class User extends Model {
    protected $table = 'usuarios';

    public function getUsuarios() {
        $sql = "SELECT u.*, r.nombre as rol_nombre 
                FROM usuarios u 
                LEFT JOIN roles r ON u.rol_id = r.id 
                ORDER BY u.id DESC";
        return $this->query($sql);
    }

    public function buscarUsuarios($nombre, $rol, $estado) {
        $sql = "SELECT u.*, r.nombre as rol_nombre 
                FROM usuarios u 
                LEFT JOIN roles r ON u.rol_id = r.id 
                WHERE 1=1";
        $params = [];

        if (!empty($nombre)) {
            $sql .= " AND (u.nombre LIKE :nombre OR u.email LIKE :nombre)";
            $params[':nombre'] = "%$nombre%";
        }

        if (!empty($rol)) {
            $sql .= " AND r.nombre = :rol";
            $params[':rol'] = $rol;
        }

        if (!empty($estado)) {
            $sql .= " AND u.estado = :estado";
            $params[':estado'] = $estado;
        }

        $sql .= " ORDER BY u.id DESC";
        return $this->query($sql, $params);
    }

    public function insertUsuario($data) {
        // Validar email único
        if ($this->findByEmail($data['email'])) {
            throw new Exception('El email ya está registrado');
        }

        // Encriptar contraseña
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        return $this->create($data);
    }

    public function updateUsuario($id, $data) {
        // Validar email único excluyendo el usuario actual
        $usuario = $this->find($id);
        if (!$usuario) {
            throw new Exception('Usuario no encontrado');
        }

        if ($usuario['email'] !== $data['email']) {
            if ($this->findByEmail($data['email'])) {
                throw new Exception('El email ya está registrado');
            }
        }

        // Si se proporciona una nueva contraseña, encriptarla
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }

        return $this->update($id, $data);
    }

    public function deleteUsuario($id) {
        // Verificar si el usuario tiene mascotas asociadas
        $sql = "SELECT COUNT(*) as total FROM mascotas WHERE usuario_id = :id";
        $result = $this->query($sql, [':id' => $id]);
        
        if ($result[0]['total'] > 0) {
            throw new Exception('El usuario tiene mascotas asociadas');
        }

        return $this->delete($id);
    }

    public function cambiarEstadoUsuario($id, $estado) {
        if (!in_array($estado, ['activo', 'inactivo'])) {
            throw new Exception('Estado inválido');
        }

        return $this->update($id, ['estado' => $estado]);
    }

    public function findByEmail($email) {
        $sql = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
        $result = $this->query($sql, [':email' => $email]);
        return $result ? $result[0] : null;
    }

    public function getRolesDisponibles() {
        $sql = "SELECT * FROM roles ORDER BY nombre";
        return $this->query($sql);
    }

    public function getActiveUsers() {
        $sql = "SELECT * FROM usuarios WHERE estado = 'activo' AND rol_id = 3 ORDER BY nombre";
        return $this->query($sql);
    }

    public function getMascotasPorUsuario($usuarioId) {
        $sql = "SELECT id, nombre FROM mascotas WHERE propietario_id = :usuario_id";
        return $this->query($sql, [':usuario_id' => $usuarioId]);
    }

    public function getDispositivosPorUsuario($usuarioId) {
        $sql = "SELECT d.id, d.nombre FROM dispositivos d
                INNER JOIN mascotas m ON d.mascota_id = m.id
                WHERE m.propietario_id = :usuario_id";
        return $this->query($sql, [':usuario_id' => $usuarioId]);
    }

    public function getUsuarioById($id) {
        $sql = "SELECT u.*, r.nombre as rol_nombre 
                FROM usuarios u 
                LEFT JOIN roles r ON u.rol_id = r.id 
                WHERE u.id = :id 
                LIMIT 1";
        $result = $this->query($sql, [':id' => $id]);
        return $result ? $result[0] : null;
    }
} 