<?php
class Mascota extends Model {
    protected $table = 'mascotas';

    public function __construct() {
        parent::__construct();
    }

    public function getMascotasByUser($propietario_id) {
        return $this->findAll(['propietario_id' => $propietario_id]);
    }

    public function createMascota($data) {
        // Solo permitir los campos válidos
        $allowed = ['nombre', 'especie', 'tamano', 'fecha_nacimiento', 'propietario_id', 'estado', 'genero'];
        $filtered = array_intersect_key($data, array_flip($allowed));
        return $this->create($filtered);
    }

    public function updateMascota($id, $data) {
        $allowed = ['nombre', 'especie', 'tamano', 'fecha_nacimiento', 'propietario_id', 'estado', 'genero'];
        $filtered = array_intersect_key($data, array_flip($allowed));
        return $this->update($id, $filtered);
    }

    public function deleteMascota($id) {
        return $this->delete($id);
    }

    public function getEstadisticas($propietario_id) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(DISTINCT especie) as especies,
                    AVG(TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE())) as edad_promedio
                FROM {$this->table} 
                WHERE propietario_id = :propietario_id";
        $result = $this->query($sql, [':propietario_id' => $propietario_id]);
        return $result ? $result[0] : [
            'total' => 0,
            'especies' => 0,
            'edad_promedio' => 0
        ];
    }

    public function getMascotasByEspecie($propietario_id, $especie) {
        return $this->findAll([
            'propietario_id' => $propietario_id,
            'especie' => $especie
        ]);
    }

    public function getMascotasByRaza($propietario_id, $raza) {
        return $this->findAll([
            'propietario_id' => $propietario_id,
            'raza' => $raza
        ]);
    }

    public function getMascotasByEdad($propietario_id, $edad_minima, $edad_maxima) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE propietario_id = :propietario_id 
                AND TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) 
                BETWEEN :edad_minima AND :edad_maxima";
        return $this->query($sql, [
            ':propietario_id' => $propietario_id,
            ':edad_minima' => $edad_minima,
            ':edad_maxima' => $edad_maxima
        ]);
    }

    public function getMascotasConDispositivos($propietario_id) {
        $sql = "SELECT m.*, COUNT(d.id) as total_dispositivos 
                FROM {$this->table} m 
                LEFT JOIN dispositivos d ON m.id = d.mascota_id 
                WHERE m.propietario_id = :propietario_id 
                GROUP BY m.id";
        return $this->query($sql, [':propietario_id' => $propietario_id]);
    }

    public function getMascotasSinDispositivos($propietario_id) {
        $sql = "SELECT m.* 
                FROM {$this->table} m 
                WHERE m.propietario_id = :propietario_id
                AND NOT EXISTS (
                    SELECT 1 FROM dispositivos d WHERE d.mascota_id = m.id
                )";
        return $this->query($sql, [':propietario_id' => $propietario_id]);
    }

    public function getMascotasConAlertas($propietario_id) {
        $sql = "SELECT DISTINCT m.* 
                FROM {$this->table} m 
                JOIN dispositivos d ON m.id = d.mascota_id 
                JOIN alertas a ON d.id = a.dispositivo_id 
                WHERE m.propietario_id = :propietario_id AND a.leida = 0";
        return $this->query($sql, [':propietario_id' => $propietario_id]);
    }

    public function getMascotasPorVeterinario($veterinario_id) {
        $sql = "SELECT m.*, u.nombre as dueno_nombre 
                FROM {$this->table} m 
                JOIN usuarios u ON m.propietario_id = u.id 
                GROUP BY m.id";
        return $this->query($sql, [':veterinario_id' => $veterinario_id]);
    }

    public function getEstadisticasAvanzadas($propietario_id) {
        $sql = "SELECT 
                    COUNT(*) as total_mascotas,
                    COUNT(DISTINCT especie) as total_especies,
                    COUNT(DISTINCT raza) as total_razas,
                    AVG(TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE())) as edad_promedio,
                    MAX(TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE())) as edad_maxima,
                    MIN(TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE())) as edad_minima,
                    COUNT(DISTINCT d.id) as total_dispositivos,
                    COUNT(DISTINCT hm.id) as total_registros_medicos
                FROM {$this->table} m
                LEFT JOIN dispositivos d ON d.mascota_id = m.id
                LEFT JOIN historial_medico hm ON hm.mascota_id = m.id
                WHERE m.propietario_id = :propietario_id";
        
        $estadisticas = $this->query($sql, [':propietario_id' => $propietario_id])[0];
        
        // Agregar distribución por edad
        $estadisticas['edad_0_1'] = $this->getMascotasPorRangoEdad($propietario_id, 0, 1);
        $estadisticas['edad_1_3'] = $this->getMascotasPorRangoEdad($propietario_id, 1, 3);
        $estadisticas['edad_3_5'] = $this->getMascotasPorRangoEdad($propietario_id, 3, 5);
        
        return $estadisticas;
    }

    private function getMascotasPorRangoEdad($propietario_id, $edad_min, $edad_max) {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table} 
                WHERE propietario_id = :propietario_id 
                AND TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) 
                BETWEEN :edad_min AND :edad_max";
        $result = $this->query($sql, [
            ':propietario_id' => $propietario_id,
            ':edad_min' => $edad_min,
            ':edad_max' => $edad_max
        ]);
        return $result[0]['total'];
    }

    private function getDistribucionPorEspecie($propietario_id) {
        $sql = "SELECT especie, COUNT(*) as total 
                FROM {$this->table} 
                WHERE propietario_id = :propietario_id 
                GROUP BY especie 
                ORDER BY total DESC";
        return $this->query($sql, [':propietario_id' => $propietario_id]);
    }

    public function getMascotasPorEstado($propietario_id) {
        $sql = "SELECT 
                    m.*,
                    CASE 
                        WHEN COUNT(d.id) = 0 THEN 'sin_dispositivo'
                        WHEN COUNT(a.id) > 0 THEN 'con_alerta'
                        ELSE 'normal'
                    END as estado
                FROM {$this->table} m 
                LEFT JOIN dispositivos d ON m.id = d.mascota_id 
                LEFT JOIN alertas a ON d.id = a.dispositivo_id AND a.leida = 0
                WHERE m.propietario_id = :propietario_id
                GROUP BY m.id";
        return $this->query($sql, [':propietario_id' => $propietario_id]);
    }

    public function getMascotasPorTipoAlerta($propietario_id) {
        $sql = "SELECT 
                    m.*,
                    a.tipo as tipo_alerta,
                    COUNT(a.id) as total_alertas
                FROM {$this->table} m 
                JOIN dispositivos d ON m.id = d.mascota_id 
                JOIN alertas a ON d.id = a.dispositivo_id 
                WHERE m.propietario_id = :propietario_id AND a.leida = 0
                GROUP BY m.id, a.tipo";
        return $this->query($sql, [':propietario_id' => $propietario_id]);
    }

    public function findById($id) {
        return $this->find($id);
    }

    public function buscarMascotasPorTermino($termino, $userId, $soloPropias = false) {
        $sql = "SELECT m.*, u.nombre as propietario_nombre
                FROM {$this->table} m
                LEFT JOIN usuarios u ON m.propietario_id = u.id
                WHERE (
                    LOWER(m.nombre) LIKE :t1
                    OR LOWER(m.especie) LIKE :t2
                    OR LOWER(u.nombre) LIKE :t3
                    OR LOWER(m.estado) LIKE :t4
                )";
        $params = [
            ':t1' => '%' . strtolower($termino) . '%',
            ':t2' => '%' . strtolower($termino) . '%',
            ':t3' => '%' . strtolower($termino) . '%',
            ':t4' => '%' . strtolower($termino) . '%'
        ];
        if ($soloPropias) {
            $sql .= " AND m.propietario_id = :uid";
            $params[':uid'] = $userId;
        }
        $sql .= " ORDER BY m.nombre ASC";
        // Log temporal para depuración
        error_log('SQL Mascotas: ' . $sql);
        error_log('PARAMS Mascotas: ' . json_encode($params));
        return $this->query($sql, $params);
    }

    public function findAll($conditions = [], $orderBy = '') {
        $sql = "SELECT m.*, u.nombre as propietario_nombre
                FROM {$this->table} m
                LEFT JOIN usuarios u ON m.propietario_id = u.id";
        $params = [];
        if (!empty($conditions)) {
            $sql .= " WHERE ";
            $condiciones = [];
            foreach ($conditions as $campo => $valor) {
                $condiciones[] = "m.$campo = :$campo";
                $params[":$campo"] = $valor;
            }
            $sql .= implode(' AND ', $condiciones);
        }
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        } else {
            $sql .= " ORDER BY m.nombre ASC";
        }
        return $this->query($sql, $params);
    }
}
?> 