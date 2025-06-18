<?php
require_once 'core/Model.php';

class Dispositivo extends Model {
    protected $table = 'dispositivos';

    public function __construct() {
        parent::__construct();
    }

    public function getDispositivosWithMascotas($usuario_id) {
        try {
            $sql = "SELECT d.*, m.nombre as mascota_nombre, m.especie
                    FROM {$this->table} d
                    LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota
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
            // Validar campos requeridos
            $requiredFields = ['nombre', 'mac', 'estado', 'usuario_id'];
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
            $sql = "SELECT d.*, m.nombre as mascota_nombre, m.especie
                    FROM {$this->table} d
                    LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota
                    WHERE d.id_dispositivo = :id";
            $result = $this->query($sql, [':id' => $id]);
            return $result ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Error en getDispositivoById: " . $e->getMessage());
            return null;
        }
    }

    public function getDispositivosByMascota($mascotaId) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE mascota_id = :mascota_id";
            $result = $this->query($sql, [':mascota_id' => $mascotaId]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getDispositivosByMascota: " . $e->getMessage());
            return [];
        }
    }

    public function getDispositivosActivos($usuario_id) {
        try {
            $sql = "SELECT d.*, m.nombre as mascota_nombre
                    FROM {$this->table} d
                    LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota
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
                    LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota
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
                    LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota
                    WHERE d.mascota_id = :mascota_id
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
                        SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activos
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

    public function getDispositivosPorUltimaConexion($usuario_id, $dias = 7) {
        try {
            $sql = "SELECT 
                        DATE(ultima_conexion) as fecha,
                        COUNT(*) as total
                    FROM {$this->table}
                    WHERE usuario_id = :usuario_id
                    AND ultima_conexion >= DATE_SUB(NOW(), INTERVAL :dias DAY)
                    GROUP BY DATE(ultima_conexion)
                    ORDER BY fecha DESC";
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

    public function getTodosDispositivosConMascotas() {
        try {
            $sql = "SELECT d.*, m.nombre as mascota_nombre, m.especie,
                        u.nombre as usuario_nombre
                    FROM {$this->table} d
                    LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota
                    LEFT JOIN usuarios u ON d.usuario_id = u.id_usuario
                    ORDER BY d.ultima_conexion DESC";
            $result = $this->query($sql);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getTodosDispositivosConMascotas: " . $e->getMessage());
            return [];
        }
    }

    public function existeMac($mac, $ignoreId = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE mac = :mac";
            $params = [':mac' => $mac];
            
            if ($ignoreId) {
                $sql .= " AND id != :id";
                $params[':id'] = $ignoreId;
            }
            
            $result = $this->query($sql, $params);
            return $result && $result[0]['total'] > 0;
        } catch (Exception $e) {
            error_log("Error en existeMac: " . $e->getMessage());
            return false;
        }
    }

    public function getDispositivosDisponibles() {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE mascota_id IS NULL";
            $result = $this->query($sql);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getDispositivosDisponibles: " . $e->getMessage());
            return [];
        }
    }

    public function filtrarDispositivos($filtros, $usuario_id = null) {
        try {
            $sql = "SELECT d.*, m.nombre as mascota_nombre
                    FROM {$this->table} d
                    LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota
                    WHERE 1=1";
            $params = [];

            if ($usuario_id) {
                $sql .= " AND d.usuario_id = :usuario_id";
                $params[':usuario_id'] = $usuario_id;
            }

            if (!empty($filtros['estado'])) {
                $sql .= " AND d.estado = :estado";
                $params[':estado'] = $filtros['estado'];
            }

            if (!empty($filtros['tipo'])) {
                $sql .= " AND d.tipo = :tipo";
                $params[':tipo'] = $filtros['tipo'];
            }

            if (!empty($filtros['mascota_id'])) {
                $sql .= " AND d.mascota_id = :mascota_id";
                $params[':mascota_id'] = $filtros['mascota_id'];
            }

            if (!empty($filtros['fecha_desde'])) {
                $sql .= " AND d.ultima_conexion >= :fecha_desde";
                $params[':fecha_desde'] = $filtros['fecha_desde'];
            }

            if (!empty($filtros['fecha_hasta'])) {
                $sql .= " AND d.ultima_conexion <= :fecha_hasta";
                $params[':fecha_hasta'] = $filtros['fecha_hasta'];
            }

            $sql .= " ORDER BY d.ultima_conexion DESC";
            
            $result = $this->query($sql, $params);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en filtrarDispositivos: " . $e->getMessage());
            return [];
        }
    }

    public function getUltimaLectura($dispositivo_id) {
        try {
            return $this->query("SELECT * FROM datos_sensores WHERE dispositivo_id = ? ORDER BY fecha DESC LIMIT 1", [$dispositivo_id])[0] ?? null;
        } catch (Exception $e) {
            error_log("Error en getUltimaLectura: " . $e->getMessage());
            return null;
        }
    }

    public function getEstadisticasSensores($dispositivo_id) {
        try {
            $sql = "SELECT 
                        AVG(temperatura) as temp_promedio,
                        MAX(temperatura) as temp_maxima,
                        MIN(temperatura) as temp_minima,
                        AVG(bpm) as bpm_promedio,
                        MAX(bpm) as bpm_maximo,
                        MIN(bpm) as bpm_minimo,
                        AVG(bateria) as bateria_promedio
                    FROM datos_sensores 
                    WHERE dispositivo_id = :dispositivo_id
                    AND fecha >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            
            $result = $this->query($sql, [':dispositivo_id' => $dispositivo_id]);
            return $result ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Error en getEstadisticasSensores: " . $e->getMessage());
            return null;
        }
    }

    public function getHistorialCompleto($dispositivoId) {
        try {
            $sql = "SELECT * FROM datos_sensores 
                    WHERE dispositivo_id = :dispositivo_id 
                    ORDER BY fecha DESC";
            $result = $this->query($sql, [':dispositivo_id' => $dispositivoId]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getHistorialCompleto: " . $e->getMessage());
            return [];
        }
    }

    public function getUltimasLecturasPorDispositivos($ids) {
        try {
            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
            $sql = "SELECT d.*, ds.* 
                    FROM {$this->table} d
                    LEFT JOIN datos_sensores ds ON d.id = ds.dispositivo_id
                    WHERE d.id IN ($placeholders)
                    AND ds.fecha = (
                        SELECT MAX(fecha) 
                        FROM datos_sensores 
                        WHERE dispositivo_id = d.id
                    )";
            $result = $this->query($sql, $ids);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error en getUltimasLecturasPorDispositivos: " . $e->getMessage());
            return [];
        }
    }

    public function create($data) {
        try {
            $sql = "INSERT INTO {$this->table} (
                        nombre, mac, estado, usuario_id, mascota_id, 
                        tipo, bateria, ultima_conexion, identificador
                    ) VALUES (
                        :nombre, :mac, :estado, :usuario_id, :mascota_id,
                        :tipo, :bateria, :ultima_conexion, :identificador
                    )";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([
                ':nombre' => $data['nombre'],
                ':mac' => $data['mac'],
                ':estado' => $data['estado'],
                ':usuario_id' => $data['usuario_id'],
                ':mascota_id' => $data['mascota_id'] ?? null,
                ':tipo' => $data['tipo'] ?? 'default',
                ':bateria' => $data['bateria'] ?? 100,
                ':ultima_conexion' => $data['ultima_conexion'] ?? date('Y-m-d H:i:s'),
                ':identificador' => $data['identificador'] ?? 'DEV-' . strtoupper(uniqid())
            ]);
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Error en create: " . $e->getMessage());
            return false;
        }
    }

    public function getDispositivosPaginados($offset, $limit) {
        try {
            $sql = "SELECT d.*, m.nombre as mascota_nombre
                    FROM {$this->table} d
                    LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota
                    ORDER BY d.ultima_conexion DESC
                    LIMIT :offset, :limit";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getDispositivosPaginados: " . $e->getMessage());
            return [];
        }
    }

    public function findByDeviceId($device_id) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE identificador = :device_id";
            $result = $this->query($sql, [':device_id' => $device_id]);
            return $result ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Error en findByDeviceId: " . $e->getMessage());
            return null;
        }
    }

    public function getConectadosPorDias($dias) {
        try {
            $sql = "SELECT COUNT(DISTINCT dispositivo_id) as total
                    FROM datos_sensores
                    WHERE fecha >= DATE_SUB(NOW(), INTERVAL :dias DAY)";
            $result = $this->query($sql, [':dias' => $dias]);
            return $result ? $result[0]['total'] : 0;
        } catch (Exception $e) {
            error_log("Error en getConectadosPorDias: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalConectados() {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE estado = 'activo'";
            $result = $this->query($sql);
            return $result ? $result[0]['total'] : 0;
        } catch (Exception $e) {
            error_log("Error en getTotalConectados: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalDesconectados() {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE estado = 'inactivo'";
            $result = $this->query($sql);
            return $result ? $result[0]['total'] : 0;
        } catch (Exception $e) {
            error_log("Error en getTotalDesconectados: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalDispositivos() {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table}";
            $result = $this->query($sql);
            return $result ? $result[0]['total'] : 0;
        } catch (Exception $e) {
            error_log("Error en getTotalDispositivos: " . $e->getMessage());
            return 0;
        }
    }

    public function find($id) {
        try {
            $sql = "SELECT d.*, m.nombre as mascota_nombre, m.especie
                    FROM {$this->table} d
                    LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota
                    WHERE d.id = :id";
            $result = $this->query($sql, [':id' => $id]);
            return $result ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Error en find: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca MACs de dispositivos por coincidencia parcial
     */
    public function buscarMacs($q = '') {
        $sql = "SELECT DISTINCT mac FROM {$this->table} WHERE mac LIKE :q ORDER BY mac LIMIT 20";
        $param = ['q' => '%' . $q . '%'];
        return $this->query($sql, $param);
    }
}
?> 