<?php
class Alerta extends Model {
    protected $table = 'alertas';

    public function __construct() {
        parent::__construct();
    }

    public function getAlertasByUser($userId) {
        try {
            $sql = "SELECT a.*, d.nombre as dispositivo_nombre, m.nombre as mascota_nombre 
                    FROM {$this->table} a 
                    LEFT JOIN dispositivos d ON a.dispositivo_id = d.id 
                    LEFT JOIN mascotas m ON d.mascota_id = m.id 
                    WHERE a.usuario_id = ? 
                    ORDER BY a.fecha DESC";
            $result = $this->query($sql, [$userId]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getAlertasByUser: " . $e->getMessage());
            return [];
        }
    }

    public function getAlertasNoLeidas($usuario_id, $limit = 5) {
        try {
            $sql = "SELECT a.*, d.nombre as dispositivo_nombre, m.nombre as mascota_nombre
                    FROM {$this->table} a
                    LEFT JOIN dispositivos d ON a.dispositivo_id = d.id
                    LEFT JOIN mascotas m ON d.mascota_id = m.id
                    WHERE d.propietario_id = :usuario_id 
                    AND a.leida = 0
                    ORDER BY a.fecha_creacion DESC
                    LIMIT :limit";
            $result = $this->query($sql, [
                ':usuario_id' => $usuario_id,
                ':limit' => $limit
            ]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getAlertasNoLeidas: " . $e->getMessage());
            return [];
        }
    }

    public function marcarComoLeida($id) {
        try {
            $sql = "UPDATE {$this->table} 
                    SET leida = 1, 
                        fecha_lectura = NOW() 
                    WHERE id = :id";
            return $this->query($sql, [':id' => $id]);
        } catch (Exception $e) {
            error_log("Error en marcarComoLeida: " . $e->getMessage());
            return false;
        }
    }

    public function marcarTodasComoLeidas($usuario_id) {
        try {
            $sql = "UPDATE {$this->table} a
                    JOIN dispositivos d ON a.dispositivo_id = d.id
                    SET a.leida = 1,
                        a.fecha_lectura = NOW()
                    WHERE d.propietario_id = :usuario_id 
                    AND a.leida = 0";
            return $this->query($sql, [':usuario_id' => $usuario_id]);
        } catch (Exception $e) {
            error_log("Error en marcarTodasComoLeidas: " . $e->getMessage());
            return false;
        }
    }

    public function crearAlerta($data) {
        try {
            return $this->create($data);
        } catch (Exception $e) {
            error_log("Error en crearAlerta: " . $e->getMessage());
            return false;
        }
    }

    public function getAlertasByDispositivo($dispositivoId) {
        try {
            return $this->findAll(['dispositivo_id' => $dispositivoId]) ?: [];
        } catch (Exception $e) {
            error_log("Error en getAlertasByDispositivo: " . $e->getMessage());
            return [];
        }
    }

    public function getAlertasByMascota($mascotaId) {
        try {
            $sql = "SELECT a.* FROM {$this->table} a 
                    JOIN dispositivos d ON a.dispositivo_id = d.id 
                    WHERE d.mascota_id = ? 
                    ORDER BY a.fecha DESC";
            $result = $this->query($sql, [$mascotaId]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getAlertasByMascota: " . $e->getMessage());
            return [];
        }
    }

    public function getEstadisticas($usuario_id) {
        try {
            $sql = "SELECT 
                        COUNT(DISTINCT a.id) as total,
                        SUM(CASE WHEN a.leida = 0 THEN 1 ELSE 0 END) as no_leidas,
                        SUM(CASE WHEN a.leida = 0 AND a.prioridad = 'alta' THEN 1 ELSE 0 END) as alertas_altas
                    FROM {$this->table} a
                    LEFT JOIN dispositivos d ON a.dispositivo_id = d.id
                    WHERE d.propietario_id = :usuario_id";
            $result = $this->query($sql, [':usuario_id' => $usuario_id]);
            if (!$result || !isset($result[0])) {
                return [
                    'total' => 0,
                    'no_leidas' => 0,
                    'alertas_altas' => 0
                ];
            }
            return [
                'total' => (int)($result[0]['total'] ?? 0),
                'no_leidas' => (int)($result[0]['no_leidas'] ?? 0),
                'alertas_altas' => (int)($result[0]['alertas_altas'] ?? 0)
            ];
        } catch (Exception $e) {
            error_log("Error en getEstadisticas: " . $e->getMessage());
            return [
                'total' => 0,
                'no_leidas' => 0,
                'alertas_altas' => 0
            ];
        }
    }

    public function getAlertasPorDispositivo($dispositivo_id, $limit = 10) {
        try {
            $sql = "SELECT a.*, d.nombre as dispositivo_nombre, m.nombre as mascota_nombre
                    FROM {$this->table} a
                    JOIN dispositivos d ON a.dispositivo_id = d.id
                    LEFT JOIN mascotas m ON d.mascota_id = m.id
                    WHERE a.dispositivo_id = :dispositivo_id
                    ORDER BY a.fecha_creacion DESC
                    LIMIT :limit";
            $result = $this->query($sql, [
                ':dispositivo_id' => $dispositivo_id,
                ':limit' => $limit
            ]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getAlertasPorDispositivo: " . $e->getMessage());
            return [];
        }
    }

    public function getAlertasPorMascota($mascota_id, $limit = 10) {
        try {
            $sql = "SELECT a.*, d.nombre as dispositivo_nombre, m.nombre as mascota_nombre
                    FROM {$this->table} a
                    JOIN dispositivos d ON a.dispositivo_id = d.id
                    JOIN mascotas m ON d.mascota_id = m.id
                    WHERE m.id = :mascota_id
                    ORDER BY a.fecha_creacion DESC
                    LIMIT :limit";
            $result = $this->query($sql, [
                ':mascota_id' => $mascota_id,
                ':limit' => $limit
            ]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getAlertasPorMascota: " . $e->getMessage());
            return [];
        }
    }

    public function getAlertasPorTipo($usuario_id, $tipo, $limit = 10) {
        try {
            $sql = "SELECT a.*, d.nombre as dispositivo_nombre, m.nombre as mascota_nombre
                    FROM {$this->table} a
                    JOIN dispositivos d ON a.dispositivo_id = d.id
                    LEFT JOIN mascotas m ON d.mascota_id = m.id
                    WHERE d.propietario_id = :usuario_id 
                    AND a.tipo = :tipo
                    ORDER BY a.fecha_creacion DESC
                    LIMIT :limit";
            $result = $this->query($sql, [
                ':usuario_id' => $usuario_id,
                ':tipo' => $tipo,
                ':limit' => $limit
            ]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getAlertasPorTipo: " . $e->getMessage());
            return [];
        }
    }

    public function getAlertasPorPrioridad($usuario_id, $prioridad, $limit = 10) {
        try {
            $sql = "SELECT a.*, d.nombre as dispositivo_nombre, m.nombre as mascota_nombre
                    FROM {$this->table} a
                    JOIN dispositivos d ON a.dispositivo_id = d.id
                    LEFT JOIN mascotas m ON d.mascota_id = m.id
                    WHERE d.propietario_id = :usuario_id 
                    AND a.prioridad = :prioridad
                    ORDER BY a.fecha_creacion DESC
                    LIMIT :limit";
            $result = $this->query($sql, [
                ':usuario_id' => $usuario_id,
                ':prioridad' => $prioridad,
                ':limit' => $limit
            ]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getAlertasPorPrioridad: " . $e->getMessage());
            return [];
        }
    }

    public function getAlertasPorFecha($usuario_id, $fecha_inicio, $fecha_fin) {
        try {
            $sql = "SELECT a.*, d.nombre as dispositivo_nombre, m.nombre as mascota_nombre
                    FROM {$this->table} a
                    JOIN dispositivos d ON a.dispositivo_id = d.id
                    LEFT JOIN mascotas m ON d.mascota_id = m.id
                    WHERE d.propietario_id = :usuario_id 
                    AND a.fecha_creacion BETWEEN :fecha_inicio AND :fecha_fin
                    ORDER BY a.fecha_creacion DESC";
            $result = $this->query($sql, [
                ':usuario_id' => $usuario_id,
                ':fecha_inicio' => $fecha_inicio,
                ':fecha_fin' => $fecha_fin
            ]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getAlertasPorFecha: " . $e->getMessage());
            return [];
        }
    }
}
?> 