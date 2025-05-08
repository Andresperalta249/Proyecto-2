<?php
class Mascota extends Model {
    protected $table = 'mascotas';

    public function getMascotasByUser($userId) {
        return $this->findAll(['usuario_id' => $userId]);
    }

    public function createMascota($data) {
        return $this->create($data);
    }

    public function updateMascota($id, $data) {
        return $this->update($id, $data);
    }

    public function deleteMascota($id) {
        // Primero eliminar el historial médico
        $this->deleteHistorialMedico($id);
        return $this->delete($id);
    }

    public function getHistorialMedico($mascotaId) {
        $sql = "SELECT * FROM historial_medico 
                WHERE mascota_id = ? 
                ORDER BY fecha DESC";
        return $this->query($sql, [$mascotaId]);
    }

    public function addHistorialMedico($data) {
        $sql = "INSERT INTO historial_medico 
                (mascota_id, fecha, tipo, descripcion, documento) 
                VALUES (?, ?, ?, ?, ?)";
        return $this->query($sql, [
            $data['mascota_id'],
            $data['fecha'],
            $data['tipo'],
            $data['descripcion'],
            $data['documento'] ?? null
        ]);
    }

    public function deleteHistorialMedico($mascotaId) {
        $sql = "DELETE FROM historial_medico WHERE mascota_id = ?";
        return $this->query($sql, [$mascotaId]);
    }

    public function getEstadisticas($userId) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(DISTINCT especie) as especies,
                    COUNT(DISTINCT raza) as razas,
                    AVG(TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE())) as edad_promedio
                FROM {$this->table} 
                WHERE usuario_id = ?";
        return $this->query($sql, [$userId])[0];
    }

    public function getMascotasByEspecie($userId, $especie) {
        return $this->findAll([
            'usuario_id' => $userId,
            'especie' => $especie
        ]);
    }

    public function getMascotasByRaza($userId, $raza) {
        return $this->findAll([
            'usuario_id' => $userId,
            'raza' => $raza
        ]);
    }

    public function getMascotasByEdad($userId, $edadMinima, $edadMaxima) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE usuario_id = ? 
                AND TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) 
                BETWEEN ? AND ?";
        return $this->query($sql, [$userId, $edadMinima, $edadMaxima]);
    }

    public function getMascotasConDispositivos($userId) {
        $sql = "SELECT m.*, COUNT(d.id) as total_dispositivos 
                FROM {$this->table} m 
                LEFT JOIN dispositivos d ON m.id = d.mascota_id 
                WHERE m.usuario_id = ? 
                GROUP BY m.id";
        return $this->query($sql, [$userId]);
    }

    public function getMascotasSinDispositivos($userId) {
        $sql = "SELECT m.* 
                FROM {$this->table} m 
                LEFT JOIN dispositivos d ON m.id = d.mascota_id 
                WHERE m.usuario_id = ? AND d.id IS NULL";
        return $this->query($sql, [$userId]);
    }

    public function getMascotasConAlertas($userId) {
        $sql = "SELECT DISTINCT m.* 
                FROM {$this->table} m 
                JOIN dispositivos d ON m.id = d.mascota_id 
                JOIN alertas a ON d.id = a.dispositivo_id 
                WHERE m.usuario_id = ? AND a.estado = 'pendiente'";
        return $this->query($sql, [$userId]);
    }

    public function getMascotasPorVeterinario($veterinarioId) {
        $sql = "SELECT m.*, u.nombre as dueno_nombre 
                FROM {$this->table} m 
                JOIN usuarios u ON m.usuario_id = u.id 
                JOIN historial_medico hm ON m.id = hm.mascota_id 
                WHERE hm.veterinario_id = ? 
                GROUP BY m.id";
        return $this->query($sql, [$veterinarioId]);
    }

    public function getProximasVacunas($mascotaId) {
        $sql = "SELECT * FROM historial_medico 
                WHERE mascota_id = ? 
                AND tipo = 'vacuna' 
                AND fecha > CURDATE() 
                ORDER BY fecha ASC";
        return $this->query($sql, [$mascotaId]);
    }

    public function getMascotasPorEdad($userId, $edadMinima, $edadMaxima) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE usuario_id = ? 
                AND TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) 
                BETWEEN ? AND ?";
        return $this->query($sql, [$userId, $edadMinima, $edadMaxima]);
    }

    public function getEstadisticasAvanzadas($userId) {
        $sql = "SELECT 
                    COUNT(*) as total_mascotas,
                    COUNT(DISTINCT especie) as total_especies,
                    COUNT(DISTINCT raza) as total_razas,
                    AVG(TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE())) as edad_promedio,
                    MAX(TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE())) as edad_maxima,
                    MIN(TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE())) as edad_minima,
                    (SELECT COUNT(*) FROM dispositivos d WHERE d.mascota_id IN 
                        (SELECT id FROM {$this->table} WHERE usuario_id = ?)) as total_dispositivos,
                    (SELECT COUNT(*) FROM historial_medico hm WHERE hm.mascota_id IN 
                        (SELECT id FROM {$this->table} WHERE usuario_id = ?)) as total_registros_medicos
                FROM {$this->table} 
                WHERE usuario_id = ?";
        
        $estadisticas = $this->query($sql, [$userId, $userId, $userId])[0];
        
        // Agregar distribución por edad
        $estadisticas['edad_0_1'] = $this->getMascotasPorRangoEdad($userId, 0, 1);
        $estadisticas['edad_1_3'] = $this->getMascotasPorRangoEdad($userId, 1, 3);
        $estadisticas['edad_3_5'] = $this->getMascotasPorRangoEdad($userId, 3, 5);
        $estadisticas['edad_5_10'] = $this->getMascotasPorRangoEdad($userId, 5, 10);
        $estadisticas['edad_10_plus'] = $this->getMascotasPorRangoEdad($userId, 10, 999);
        
        // Agregar distribución por especie
        $estadisticas['distribucion_especies'] = $this->getDistribucionPorEspecie($userId);
        
        return $estadisticas;
    }

    private function getMascotasPorRangoEdad($userId, $edadMin, $edadMax) {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table} 
                WHERE usuario_id = ? 
                AND TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) 
                BETWEEN ? AND ?";
        $result = $this->query($sql, [$userId, $edadMin, $edadMax]);
        return $result[0]['total'];
    }

    private function getDistribucionPorEspecie($userId) {
        $sql = "SELECT especie, COUNT(*) as total 
                FROM {$this->table} 
                WHERE usuario_id = ? 
                GROUP BY especie 
                ORDER BY total DESC";
        return $this->query($sql, [$userId]);
    }

    public function getMascotasPorEstado($userId) {
        $sql = "SELECT 
                    m.*,
                    CASE 
                        WHEN COUNT(d.id) = 0 THEN 'sin_dispositivo'
                        WHEN COUNT(a.id) > 0 THEN 'con_alerta'
                        ELSE 'normal'
                    END as estado
                FROM {$this->table} m 
                LEFT JOIN dispositivos d ON m.id = d.mascota_id 
                LEFT JOIN alertas a ON d.id = a.dispositivo_id AND a.estado = 'pendiente'
                WHERE m.usuario_id = ?
                GROUP BY m.id";
        return $this->query($sql, [$userId]);
    }

    public function getMascotasPorTipoAlerta($userId) {
        $sql = "SELECT 
                    m.*,
                    a.tipo as tipo_alerta,
                    COUNT(a.id) as total_alertas
                FROM {$this->table} m 
                JOIN dispositivos d ON m.id = d.mascota_id 
                JOIN alertas a ON d.id = a.dispositivo_id 
                WHERE m.usuario_id = ? AND a.estado = 'pendiente'
                GROUP BY m.id, a.tipo";
        return $this->query($sql, [$userId]);
    }
}
?> 