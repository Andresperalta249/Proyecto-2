<?php
class Log extends Model {
    protected $table = 'logs';

    public function crearLog($usuarioId, $accion) {
        $data = [
            'usuario_id' => $usuarioId,
            'accion' => $accion,
            'fecha' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR']
        ];
        return $this->create($data);
    }

    public function getActividadReciente($usuarioId, $limite = 10) {
        $sql = "SELECT l.*, u.nombre as usuario_nombre 
                FROM {$this->table} l 
                JOIN usuarios u ON l.usuario_id = u.id 
                WHERE l.usuario_id = ? 
                ORDER BY l.fecha DESC 
                LIMIT ?";
        return $this->query($sql, [$usuarioId, $limite]);
    }

    public function getActividadByFecha($usuarioId, $fechaInicio, $fechaFin) {
        $sql = "SELECT l.*, u.nombre as usuario_nombre 
                FROM {$this->table} l 
                JOIN usuarios u ON l.usuario_id = u.id 
                WHERE l.usuario_id = ? 
                AND l.fecha BETWEEN ? AND ? 
                ORDER BY l.fecha DESC";
        return $this->query($sql, [$usuarioId, $fechaInicio, $fechaFin]);
    }

    public function getActividadByTipo($usuarioId, $tipo) {
        $sql = "SELECT l.*, u.nombre as usuario_nombre 
                FROM {$this->table} l 
                JOIN usuarios u ON l.usuario_id = u.id 
                WHERE l.usuario_id = ? 
                AND l.accion LIKE ? 
                ORDER BY l.fecha DESC";
        return $this->query($sql, [$usuarioId, "%$tipo%"]);
    }

    public function limpiarLogsAntiguos($dias = 30) {
        $sql = "DELETE FROM {$this->table} 
                WHERE fecha < DATE_SUB(NOW(), INTERVAL ? DAY)";
        return $this->query($sql, [$dias]);
    }

    public function getEstadisticas($usuarioId) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(DISTINCT DATE(fecha)) as dias_activos,
                    COUNT(DISTINCT HOUR(fecha)) as horas_activas,
                    MAX(fecha) as ultima_actividad
                FROM {$this->table} 
                WHERE usuario_id = ?";
        return $this->query($sql, [$usuarioId])[0];
    }
}
?> 