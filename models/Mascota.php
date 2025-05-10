<?php
class Mascota extends Model {
    protected $table = 'mascotas';

    public function __construct() {
        parent::__construct();
    }

    public function getMascotasByUser($usuario_id) {
        return $this->findAll(['usuario_id' => $usuario_id]);
    }

    public function createMascota($data) {
        // Solo permitir los campos válidos
        $allowed = ['nombre', 'especie', 'tamano', 'fecha_nacimiento', 'usuario_id', 'propietario_id', 'estado'];
        $filtered = array_intersect_key($data, array_flip($allowed));
        return $this->create($filtered);
    }

    public function updateMascota($id, $data) {
        $allowed = ['nombre', 'especie', 'tamano', 'fecha_nacimiento', 'propietario_id', 'estado'];
        $filtered = array_intersect_key($data, array_flip($allowed));
        return $this->update($id, $filtered);
    }

    public function deleteMascota($id) {
        return $this->delete($id);
    }

    public function getEstadisticas($usuario_id) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(DISTINCT especie) as especies,
                    COUNT(DISTINCT raza) as razas,
                    AVG(TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE())) as edad_promedio
                FROM {$this->table} 
                WHERE usuario_id = :usuario_id";
        $result = $this->query($sql, [':usuario_id' => $usuario_id]);
        return $result ? $result[0] : [
            'total' => 0,
            'especies' => 0,
            'razas' => 0,
            'edad_promedio' => 0
        ];
    }

    public function getMascotasByEspecie($usuario_id, $especie) {
        return $this->findAll([
            'usuario_id' => $usuario_id,
            'especie' => $especie
        ]);
    }

    public function getMascotasByRaza($usuario_id, $raza) {
        return $this->findAll([
            'usuario_id' => $usuario_id,
            'raza' => $raza
        ]);
    }

    public function getMascotasByEdad($usuario_id, $edad_minima, $edad_maxima) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE usuario_id = :usuario_id 
                AND TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) 
                BETWEEN :edad_minima AND :edad_maxima";
        return $this->query($sql, [
            ':usuario_id' => $usuario_id,
            ':edad_minima' => $edad_minima,
            ':edad_maxima' => $edad_maxima
        ]);
    }

    public function getMascotasConDispositivos($usuario_id) {
        $sql = "SELECT m.*, COUNT(d.id) as total_dispositivos 
                FROM {$this->table} m 
                LEFT JOIN dispositivos d ON m.id = d.mascota_id 
                WHERE m.usuario_id = :usuario_id 
                GROUP BY m.id";
        return $this->query($sql, [':usuario_id' => $usuario_id]);
    }

    public function getMascotasSinDispositivos($usuario_id) {
        $sql = "SELECT m.* 
                FROM {$this->table} m 
                LEFT JOIN dispositivos d ON m.id = d.mascota_id 
                WHERE m.usuario_id = :usuario_id AND d.id IS NULL";
        return $this->query($sql, [':usuario_id' => $usuario_id]);
    }

    public function getMascotasConAlertas($usuario_id) {
        $sql = "SELECT DISTINCT m.* 
                FROM {$this->table} m 
                JOIN dispositivos d ON m.id = d.mascota_id 
                JOIN alertas a ON d.id = a.dispositivo_id 
                WHERE m.usuario_id = :usuario_id AND a.leida = 0";
        return $this->query($sql, [':usuario_id' => $usuario_id]);
    }

    public function getMascotasPorVeterinario($veterinario_id) {
        $sql = "SELECT m.*, u.nombre as dueno_nombre 
                FROM {$this->table} m 
                JOIN usuarios u ON m.usuario_id = u.id 
                GROUP BY m.id";
        return $this->query($sql, [':veterinario_id' => $veterinario_id]);
    }

    public function getEstadisticasAvanzadas($usuario_id) {
        $sql = "SELECT 
                    COUNT(*) as total_mascotas,
                    COUNT(DISTINCT especie) as total_especies,
                    COUNT(DISTINCT raza) as total_razas,
                    AVG(TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE())) as edad_promedio,
                    MAX(TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE())) as edad_maxima,
                    MIN(TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE())) as edad_minima,
                    (SELECT COUNT(*) FROM dispositivos d WHERE d.mascota_id IN 
                        (SELECT id FROM {$this->table} WHERE usuario_id = :usuario_id)) as total_dispositivos,
                    (SELECT COUNT(*) FROM historial_medico hm WHERE hm.mascota_id IN 
                        (SELECT id FROM {$this->table} WHERE usuario_id = :usuario_id)) as total_registros_medicos
                FROM {$this->table} 
                WHERE usuario_id = :usuario_id";
        
        $estadisticas = $this->query($sql, [
            ':usuario_id' => $usuario_id,
            ':usuario_id' => $usuario_id,
            ':usuario_id' => $usuario_id
        ])[0];
        
        // Agregar distribución por edad
        $estadisticas['edad_0_1'] = $this->getMascotasPorRangoEdad($usuario_id, 0, 1);
        $estadisticas['edad_1_3'] = $this->getMascotasPorRangoEdad($usuario_id, 1, 3);
        $estadisticas['edad_3_5'] = $this->getMascotasPorRangoEdad($usuario_id, 3, 5);
        $estadisticas['edad_5_10'] = $this->getMascotasPorRangoEdad($usuario_id, 5, 10);
        $estadisticas['edad_10_plus'] = $this->getMascotasPorRangoEdad($usuario_id, 10, 999);
        
        // Agregar distribución por especie
        $estadisticas['distribucion_especies'] = $this->getDistribucionPorEspecie($usuario_id);
        
        return $estadisticas;
    }

    private function getMascotasPorRangoEdad($usuario_id, $edad_min, $edad_max) {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table} 
                WHERE usuario_id = :usuario_id 
                AND TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) 
                BETWEEN :edad_min AND :edad_max";
        $result = $this->query($sql, [
            ':usuario_id' => $usuario_id,
            ':edad_min' => $edad_min,
            ':edad_max' => $edad_max
        ]);
        return $result[0]['total'];
    }

    private function getDistribucionPorEspecie($usuario_id) {
        $sql = "SELECT especie, COUNT(*) as total 
                FROM {$this->table} 
                WHERE usuario_id = :usuario_id 
                GROUP BY especie 
                ORDER BY total DESC";
        return $this->query($sql, [':usuario_id' => $usuario_id]);
    }

    public function getMascotasPorEstado($usuario_id) {
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
                WHERE m.usuario_id = :usuario_id
                GROUP BY m.id";
        return $this->query($sql, [':usuario_id' => $usuario_id]);
    }

    public function getMascotasPorTipoAlerta($usuario_id) {
        $sql = "SELECT 
                    m.*,
                    a.tipo as tipo_alerta,
                    COUNT(a.id) as total_alertas
                FROM {$this->table} m 
                JOIN dispositivos d ON m.id = d.mascota_id 
                JOIN alertas a ON d.id = a.dispositivo_id 
                WHERE m.usuario_id = :usuario_id AND a.leida = 0
                GROUP BY m.id, a.tipo";
        return $this->query($sql, [':usuario_id' => $usuario_id]);
    }

    public function findById($id) {
        return $this->find($id);
    }

    public function getMascotasFiltradas($filtros) {
        $sql = "SELECT * FROM mascotas WHERE usuario_id = :usuario_id";
        $params = [':usuario_id' => $filtros['usuario_id']];

        if (!empty($filtros['nombre'])) {
            $sql .= " AND nombre LIKE :nombre";
            $params[':nombre'] = '%' . $filtros['nombre'] . '%';
        }
        if (!empty($filtros['especie'])) {
            $sql .= " AND especie = :especie";
            $params[':especie'] = $filtros['especie'];
        }
        if (!empty($filtros['estado'])) {
            $sql .= " AND estado = :estado";
            $params[':estado'] = $filtros['estado'];
        }

        return $this->query($sql, $params);
    }
}
?> 