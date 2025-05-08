<?php
class Dispositivo extends Model {
    protected $table = 'dispositivos';

    public function getDispositivosWithMascotas($userId) {
        $sql = "SELECT d.*, m.nombre as mascota_nombre 
                FROM {$this->table} d 
                LEFT JOIN mascotas m ON d.mascota_id = m.id 
                WHERE d.usuario_id = ?";
        return $this->query($sql, [$userId]);
    }

    public function createDispositivo($data) {
        return $this->create($data);
    }

    public function updateDispositivo($id, $data) {
        return $this->update($id, $data);
    }

    public function deleteDispositivo($id) {
        return $this->delete($id);
    }

    public function getDispositivoById($id) {
        return $this->findById($id);
    }

    public function getDispositivosByMascota($mascotaId) {
        return $this->findAll(['mascota_id' => $mascotaId]);
    }

    public function getDispositivosActivos() {
        return $this->findAll(['estado' => 'activo']);
    }

    public function actualizarEstado($id, $estado) {
        return $this->update($id, ['estado' => $estado]);
    }

    public function getUltimosDatos($dispositivoId, $limite = 10) {
        $sql = "SELECT * FROM datos_sensores 
                WHERE dispositivo_id = ? 
                ORDER BY fecha DESC 
                LIMIT ?";
        return $this->query($sql, [$dispositivoId, $limite]);
    }

    public function getEstadisticas($dispositivoId) {
        $sql = "SELECT 
                    AVG(temperatura) as temp_promedio,
                    MAX(temperatura) as temp_maxima,
                    MIN(temperatura) as temp_minima,
                    AVG(humedad) as hum_promedio,
                    MAX(humedad) as hum_maxima,
                    MIN(humedad) as hum_minima,
                    AVG(actividad) as act_promedio,
                    MAX(actividad) as act_maxima,
                    MIN(actividad) as act_minima
                FROM datos_sensores 
                WHERE dispositivo_id = ? 
                AND fecha >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        return $this->query($sql, [$dispositivoId])[0];
    }
}
?> 