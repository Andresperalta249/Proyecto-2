<?php
class Alerta extends Model {
    protected $table = 'alertas';

    public function getAlertasByUser($userId) {
        $sql = "SELECT a.*, d.nombre as dispositivo_nombre, m.nombre as mascota_nombre 
                FROM {$this->table} a 
                LEFT JOIN dispositivos d ON a.dispositivo_id = d.id 
                LEFT JOIN mascotas m ON d.mascota_id = m.id 
                WHERE a.usuario_id = ? 
                ORDER BY a.fecha DESC";
        return $this->query($sql, [$userId]);
    }

    public function getAlertasNoLeidas($userId) {
        $sql = "SELECT a.*, d.nombre as dispositivo_nombre, m.nombre as mascota_nombre 
                FROM {$this->table} a 
                LEFT JOIN dispositivos d ON a.dispositivo_id = d.id 
                LEFT JOIN mascotas m ON d.mascota_id = m.id 
                WHERE a.usuario_id = ? AND a.leida = 0 
                ORDER BY a.fecha DESC";
        return $this->query($sql, [$userId]);
    }

    public function marcarComoLeida($id) {
        return $this->update($id, ['leida' => 1]);
    }

    public function marcarTodasComoLeidas($userId) {
        $sql = "UPDATE {$this->table} SET leida = 1 WHERE usuario_id = ? AND leida = 0";
        return $this->query($sql, [$userId]);
    }

    public function crearAlerta($data) {
        return $this->create($data);
    }

    public function getAlertasByDispositivo($dispositivoId) {
        return $this->findAll(['dispositivo_id' => $dispositivoId]);
    }

    public function getAlertasByMascota($mascotaId) {
        $sql = "SELECT a.* FROM {$this->table} a 
                JOIN dispositivos d ON a.dispositivo_id = d.id 
                WHERE d.mascota_id = ? 
                ORDER BY a.fecha DESC";
        return $this->query($sql, [$mascotaId]);
    }

    public function getEstadisticas($userId) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN leida = 0 THEN 1 ELSE 0 END) as no_leidas,
                    SUM(CASE WHEN tipo = 'error' THEN 1 ELSE 0 END) as errores,
                    SUM(CASE WHEN tipo = 'advertencia' THEN 1 ELSE 0 END) as advertencias,
                    SUM(CASE WHEN tipo = 'info' THEN 1 ELSE 0 END) as informativas
                FROM {$this->table} 
                WHERE usuario_id = ?";
        return $this->query($sql, [$userId])[0];
    }
}
?> 