<?php
class Dispositivo extends Model {
    protected $table = 'dispositivos';

    public function __construct() {
        parent::__construct();
    }

    public function getDispositivosWithMascotas($usuario_id) {
        try {
            $sql = "SELECT d.*, m.nombre as mascota_nombre, m.especie, m.raza
                    FROM {$this->table} d
                    LEFT JOIN mascotas m ON d.mascota_id = m.id
                    WHERE d.usuario_id = :usuario_id
                    ORDER BY d.ultima_conexion DESC";
            $result = $this->query($sql, [':usuario_id' => $usuario_id]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getDispositivosWithMascotas: " . $e->getMessage());
            return [];
        }
    }

    public function createDispositivo($data) {
        try {
            return $this->create($data);
        } catch (Exception $e) {
            error_log("Error en createDispositivo: " . $e->getMessage());
            return false;
        }
    }

    public function updateDispositivo($id, $data) {
        try {
            return $this->update($id, $data);
        } catch (Exception $e) {
            error_log("Error en updateDispositivo: " . $e->getMessage());
            return false;
        }
    }

    public function deleteDispositivo($id) {
        try {
            return $this->delete($id);
        } catch (Exception $e) {
            error_log("Error en deleteDispositivo: " . $e->getMessage());
            return false;
        }
    }

    public function getDispositivoById($id) {
        try {
            return $this->find($id);
        } catch (Exception $e) {
            error_log("Error en getDispositivoById: " . $e->getMessage());
            return null;
        }
    }

    public function getDispositivosByMascota($mascotaId) {
        try {
            return $this->findAll(['mascota_id' => $mascotaId]) ?: [];
        } catch (Exception $e) {
            error_log("Error en getDispositivosByMascota: " . $e->getMessage());
            return [];
        }
    }

    public function getDispositivosActivos($usuario_id) {
        try {
            $sql = "SELECT d.*, m.nombre as mascota_nombre
                    FROM {$this->table} d
                    LEFT JOIN mascotas m ON d.mascota_id = m.id
                    WHERE d.usuario_id = :usuario_id 
                    AND d.estado = 'activo'
                    ORDER BY d.ultima_conexion DESC";
            $result = $this->query($sql, [':usuario_id' => $usuario_id]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getDispositivosActivos: " . $e->getMessage());
            return [];
        }
    }

    public function actualizarEstado($id, $estado) {
        try {
            $sql = "UPDATE {$this->table} 
                    SET estado = :estado,
                        ultima_conexion = NOW()
                    WHERE id = :id";
            return $this->query($sql, [
                ':id' => $id,
                ':estado' => $estado
            ]);
        } catch (Exception $e) {
            error_log("Error en actualizarEstado: " . $e->getMessage());
            return false;
        }
    }

    public function getUltimosDatos($dispositivoId, $limite = 10) {
        try {
            $sql = "SELECT * FROM datos_sensores 
                    WHERE dispositivo_id = ? 
                    ORDER BY fecha DESC 
                    LIMIT ?";
            $result = $this->query($sql, [$dispositivoId, $limite]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getUltimosDatos: " . $e->getMessage());
            return [];
        }
    }

    public function getEstadisticas($usuario_id) {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activos,
                        COUNT(DISTINCT tipo) as tipos,
                        COUNT(DISTINCT mascota_id) as mascotas_asignadas
                    FROM {$this->table}
                    WHERE usuario_id = :usuario_id";
            $result = $this->query($sql, [':usuario_id' => $usuario_id]);
            return $result ? $result[0] : [
                'total' => 0,
                'activos' => 0,
                'tipos' => 0,
                'mascotas_asignadas' => 0
            ];
        } catch (Exception $e) {
            error_log("Error en getEstadisticas: " . $e->getMessage());
            return [
                'total' => 0,
                'activos' => 0,
                'tipos' => 0,
                'mascotas_asignadas' => 0
            ];
        }
    }

    public function getDispositivosPorTipo($usuario_id, $tipo) {
        try {
            $sql = "SELECT d.*, m.nombre as mascota_nombre
                    FROM {$this->table} d
                    LEFT JOIN mascotas m ON d.mascota_id = m.id
                    WHERE d.usuario_id = :usuario_id 
                    AND d.tipo = :tipo
                    ORDER BY d.ultima_conexion DESC";
            $result = $this->query($sql, [
                ':usuario_id' => $usuario_id,
                ':tipo' => $tipo
            ]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getDispositivosPorTipo: " . $e->getMessage());
            return [];
        }
    }

    public function getDispositivosPorMascota($mascota_id) {
        try {
            $sql = "SELECT d.*, m.nombre as mascota_nombre
                    FROM {$this->table} d
                    JOIN mascotas m ON d.mascota_id = m.id
                    WHERE m.id = :mascota_id
                    ORDER BY d.ultima_conexion DESC";
            $result = $this->query($sql, [':mascota_id' => $mascota_id]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getDispositivosPorMascota: " . $e->getMessage());
            return [];
        }
    }

    public function getDispositivosSinMascota($usuario_id) {
        try {
            $sql = "SELECT d.*
                    FROM {$this->table} d
                    WHERE d.usuario_id = :usuario_id 
                    AND d.mascota_id IS NULL
                    ORDER BY d.ultima_conexion DESC";
            $result = $this->query($sql, [':usuario_id' => $usuario_id]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getDispositivosSinMascota: " . $e->getMessage());
            return [];
        }
    }

    public function getDispositivosPorEstado($usuario_id) {
        try {
            $sql = "SELECT 
                        estado,
                        COUNT(*) as total
                    FROM {$this->table}
                    WHERE usuario_id = :usuario_id
                    GROUP BY estado";
            $result = $this->query($sql, [':usuario_id' => $usuario_id]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getDispositivosPorEstado: " . $e->getMessage());
            return [];
        }
    }

    public function getDispositivosPorTipoConEstadisticas($usuario_id) {
        try {
            $sql = "SELECT 
                        tipo,
                        COUNT(*) as total,
                        SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activos,
                        COUNT(DISTINCT mascota_id) as mascotas_asignadas
                    FROM {$this->table}
                    WHERE usuario_id = :usuario_id
                    GROUP BY tipo";
            $result = $this->query($sql, [':usuario_id' => $usuario_id]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getDispositivosPorTipoConEstadisticas: " . $e->getMessage());
            return [];
        }
    }

    public function getDispositivosConAlertas($usuario_id) {
        try {
            $sql = "SELECT DISTINCT d.*, m.nombre as mascota_nombre
                    FROM {$this->table} d
                    LEFT JOIN mascotas m ON d.mascota_id = m.id
                    JOIN alertas a ON d.id = a.dispositivo_id
                    WHERE d.usuario_id = :usuario_id 
                    AND a.leida = 0
                    ORDER BY d.ultima_conexion DESC";
            $result = $this->query($sql, [':usuario_id' => $usuario_id]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getDispositivosConAlertas: " . $e->getMessage());
            return [];
        }
    }

    public function getDispositivosPorUltimaConexion($usuario_id, $dias = 7) {
        try {
            $sql = "SELECT d.*, m.nombre as mascota_nombre
                    FROM {$this->table} d
                    LEFT JOIN mascotas m ON d.mascota_id = m.id
                    WHERE d.usuario_id = :usuario_id 
                    AND d.ultima_conexion >= DATE_SUB(NOW(), INTERVAL :dias DAY)
                    ORDER BY d.ultima_conexion DESC";
            $result = $this->query($sql, [
                ':usuario_id' => $usuario_id,
                ':dias' => $dias
            ]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getDispositivosPorUltimaConexion: " . $e->getMessage());
            return [];
        }
    }
}
?> 