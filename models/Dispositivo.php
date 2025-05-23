<?php
class Dispositivo extends Model {
    protected $table = 'dispositivos';

    public function __construct() {
        parent::__construct();
    }

    public function getDispositivosWithMascotas($propietario_id) {
        try {
            $sql = "SELECT d.*, m.nombre as mascota_nombre, m.especie
                    FROM {$this->table} d
                    LEFT JOIN mascotas m ON d.mascota_id = m.id
                    WHERE d.propietario_id = :propietario_id
                    ORDER BY d.ultima_conexion DESC";
            $result = $this->query($sql, [':propietario_id' => $propietario_id]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getDispositivosWithMascotas: " . $e->getMessage());
            return [];
        }
    }

    public function createDispositivo($data) {
        try {
            // Validar campos requeridos
            $requiredFields = ['nombre', 'mac', 'estado', 'propietario_id'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    error_log("Campo requerido faltante: {$field}");
                    $this->lastError = "El campo {$field} es requerido";
                    return false;
                }
            }

            // Asegurar que los campos opcionales tengan valores por defecto
            $data['bateria'] = $data['bateria'] ?? 100;
            $data['ultima_conexion'] = $data['ultima_conexion'] ?? date('Y-m-d H:i:s');
            $data['identificador'] = $data['identificador'] ?? 'DEV-' . strtoupper(uniqid());

            // Validar formato de MAC
            if (!preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $data['mac'])) {
                error_log("Formato de MAC inválido: {$data['mac']}");
                $this->lastError = "Formato de MAC inválido";
                return false;
            }

            // Validar unicidad de MAC
            if ($this->existeMac($data['mac'])) {
                error_log("MAC duplicada: {$data['mac']}");
                $this->lastError = "La MAC ya está registrada";
                return false;
            }

            error_log("Intentando crear dispositivo con datos: " . print_r($data, true));
            $result = $this->create($data);
            
            if (!$result) {
                error_log("Error al crear dispositivo: " . $this->lastError);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error en createDispositivo: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->lastError = $e->getMessage();
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

    public function getDispositivosActivos($propietario_id) {
        try {
            $sql = "SELECT d.*, m.nombre as mascota_nombre
                    FROM {$this->table} d
                    LEFT JOIN mascotas m ON d.mascota_id = m.id
                    WHERE d.propietario_id = :propietario_id 
                    AND d.estado = 'activo'
                    ORDER BY d.ultima_conexion DESC";
            $result = $this->query($sql, [':propietario_id' => $propietario_id]);
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

    public function getEstadisticas($propietario_id) {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activos,
                        COUNT(DISTINCT tipo) as tipos,
                        COUNT(DISTINCT mascota_id) as mascotas_asignadas
                    FROM {$this->table}
                    WHERE propietario_id = :propietario_id";
            $result = $this->query($sql, [':propietario_id' => $propietario_id]);
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

    public function getDispositivosPorTipo($propietario_id, $tipo) {
        try {
            $sql = "SELECT d.*, m.nombre as mascota_nombre
                    FROM {$this->table} d
                    LEFT JOIN mascotas m ON d.mascota_id = m.id
                    WHERE d.propietario_id = :propietario_id 
                    AND d.tipo = :tipo
                    ORDER BY d.ultima_conexion DESC";
            $result = $this->query($sql, [
                ':propietario_id' => $propietario_id,
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

    public function getDispositivosSinMascota($propietario_id) {
        try {
            $sql = "SELECT d.*
                    FROM {$this->table} d
                    WHERE d.propietario_id = :propietario_id 
                    AND d.mascota_id IS NULL
                    ORDER BY d.ultima_conexion DESC";
            $result = $this->query($sql, [':propietario_id' => $propietario_id]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getDispositivosSinMascota: " . $e->getMessage());
            return [];
        }
    }

    public function getDispositivosPorEstado($propietario_id) {
        try {
            $sql = "SELECT 
                        estado,
                        COUNT(*) as total
                    FROM {$this->table}
                    WHERE propietario_id = :propietario_id
                    GROUP BY estado";
            $result = $this->query($sql, [':propietario_id' => $propietario_id]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getDispositivosPorEstado: " . $e->getMessage());
            return [];
        }
    }

    public function getDispositivosPorTipoConEstadisticas($propietario_id) {
        try {
            $sql = "SELECT 
                        tipo,
                        COUNT(*) as total,
                        SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activos,
                        COUNT(DISTINCT mascota_id) as mascotas_asignadas
                    FROM {$this->table}
                    WHERE propietario_id = :propietario_id
                    GROUP BY tipo";
            $result = $this->query($sql, [':propietario_id' => $propietario_id]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getDispositivosPorTipoConEstadisticas: " . $e->getMessage());
            return [];
        }
    }

    public function getDispositivosConAlertas($propietario_id) {
        try {
            $sql = "SELECT DISTINCT d.*, m.nombre as mascota_nombre
                    FROM {$this->table} d
                    LEFT JOIN mascotas m ON d.mascota_id = m.id
                    JOIN alertas a ON d.id = a.dispositivo_id
                    WHERE d.propietario_id = :propietario_id 
                    AND a.leida = 0
                    ORDER BY d.ultima_conexion DESC";
            $result = $this->query($sql, [':propietario_id' => $propietario_id]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getDispositivosConAlertas: " . $e->getMessage());
            return [];
        }
    }

    public function getDispositivosPorUltimaConexion($propietario_id, $dias = 7) {
        try {
            $sql = "SELECT d.*, m.nombre as mascota_nombre
                    FROM {$this->table} d
                    LEFT JOIN mascotas m ON d.mascota_id = m.id
                    WHERE d.propietario_id = :propietario_id 
                    AND d.ultima_conexion >= DATE_SUB(NOW(), INTERVAL :dias DAY)
                    ORDER BY d.ultima_conexion DESC";
            $result = $this->query($sql, [
                ':propietario_id' => $propietario_id,
                ':dias' => $dias
            ]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getDispositivosPorUltimaConexion: " . $e->getMessage());
            return [];
        }
    }

    public function getTodosDispositivosConMascotas() {
        try {
            $sql = "SELECT d.*, m.nombre as mascota_nombre, u.nombre as propietario_nombre
                    FROM {$this->table} d
                    LEFT JOIN mascotas m ON d.mascota_id = m.id
                    LEFT JOIN usuarios u ON d.propietario_id = u.id
                    ORDER BY d.ultima_conexion DESC";
            $result = $this->query($sql);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getTodosDispositivosConMascotas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica si una MAC ya existe en la base de datos.
     * Si se pasa $ignoreId, ignora ese ID (útil para edición).
     */
    public function existeMac($mac, $ignoreId = null) {
        $sql = "SELECT id FROM {$this->table} WHERE mac = :mac";
        $params = [':mac' => $mac];
        if ($ignoreId) {
            $sql .= " AND id != :ignoreId";
            $params[':ignoreId'] = $ignoreId;
        }
        $result = $this->query($sql, $params);
        return !empty($result);
    }

    /**
     * Obtiene todos los dispositivos sin mascota asignada (disponibles para asignar).
     */
    public function getDispositivosDisponibles() {
        $sql = "SELECT * FROM {$this->table} WHERE mascota_id IS NULL";
        $result = $this->query($sql);
        return $result ?: [];
    }

    /**
     * Filtra dispositivos según los parámetros recibidos.
     * $filtros: ['busqueda', 'estado', 'propietario_id', 'mascota_id', 'bateria']
     * $propietario_id: si se pasa, filtra solo por ese propietario
     */
    public function filtrarDispositivos($filtros, $propietario_id = null) {
        $sql = "SELECT d.*, m.nombre as mascota_nombre, u.nombre as propietario_nombre
                FROM {$this->table} d
                LEFT JOIN mascotas m ON d.mascota_id = m.id
                LEFT JOIN usuarios u ON d.propietario_id = u.id
                WHERE 1=1";
        $params = [];
        if ($propietario_id) {
            $sql .= " AND d.propietario_id = :propietario_id";
            $params[':propietario_id'] = $propietario_id;
        } elseif (!empty($filtros['propietario_id'])) {
            $sql .= " AND d.propietario_id = :propietario_id";
            $params[':propietario_id'] = $filtros['propietario_id'];
        }
        if (!empty($filtros['estado'])) {
            $sql .= " AND d.estado = :estado";
            $params[':estado'] = $filtros['estado'];
        }
        if (!empty($filtros['mascota_id'])) {
            $sql .= " AND d.mascota_id = :mascota_id";
            $params[':mascota_id'] = $filtros['mascota_id'];
        }
        if (!empty($filtros['bateria'])) {
            if ($filtros['bateria'] === 'baja') {
                $sql .= " AND d.bateria < 30";
            } elseif ($filtros['bateria'] === 'media') {
                $sql .= " AND d.bateria >= 30 AND d.bateria <= 70";
            } elseif ($filtros['bateria'] === 'alta') {
                $sql .= " AND d.bateria > 70";
            }
        }
        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (d.nombre LIKE :busqueda OR d.mac LIKE :busqueda OR d.identificador LIKE :busqueda)";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }
        $sql .= " ORDER BY d.ultima_conexion DESC";
        $result = $this->query($sql, $params);
        return $result ?: [];
    }

    /**
     * Obtiene la última fecha de lectura para un dispositivo
     */
    public function getUltimaLectura($dispositivo_id) {
        $sql = "SELECT MAX(creado_en) as ultima_lectura FROM datos_sensores WHERE dispositivo_id = :dispositivo_id";
        $result = $this->query($sql, [':dispositivo_id' => $dispositivo_id]);
        return $result && $result[0]['ultima_lectura'] ? $result[0]['ultima_lectura'] : null;
    }

    public function getEstadisticasSensores($dispositivo_id) {
        $sql = "SELECT
                    AVG(temperatura) as temp_promedio,
                    MAX(temperatura) as temp_maxima,
                    MIN(temperatura) as temp_minima,
                    AVG(bpm) as bpm_promedio,
                    MAX(bpm) as bpm_maxima,
                    MIN(bpm) as bpm_minima,
                    AVG(bateria) as bat_promedio,
                    MAX(bateria) as bat_maxima,
                    MIN(bateria) as bat_minima
                FROM datos_sensores
                WHERE dispositivo_id = :dispositivo_id
                  AND fecha >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
        $result = $this->query($sql, [':dispositivo_id' => $dispositivo_id]);
        return $result ? $result[0] : [
            'temp_promedio' => null, 'temp_maxima' => null, 'temp_minima' => null,
            'bpm_promedio' => null, 'bpm_maxima' => null, 'bpm_minima' => null,
            'bat_promedio' => null, 'bat_maxima' => null, 'bat_minima' => null
        ];
    }

    // Obtener todo el historial de datos de un dispositivo (sin límite)
    public function getHistorialCompleto($dispositivoId) {
        try {
            $sql = "SELECT * FROM datos_sensores WHERE dispositivo_id = ? ORDER BY fecha DESC";
            $result = $this->query($sql, [$dispositivoId]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getHistorialCompleto: " . $e->getMessage());
            return [];
        }
    }
}
?> 