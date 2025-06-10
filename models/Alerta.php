<?php
require_once 'core/Model.php';

class Alerta extends Model {
    protected $table = 'alertas_monitoreo';

    public function __construct() {
        parent::__construct();
    }

    // Devuelve el total de alertas (puede aceptar condiciones opcionales)
    public function count($conditions = []) {
        if (empty($conditions)) {
            $sql = "SELECT COUNT(*) as total FROM {$this->table}";
            $result = $this->query($sql);
            return $result ? (int)$result[0]['total'] : 0;
        } else {
            // Si se pasan condiciones, usar el método padre
            return parent::count($conditions);
        }
    }

    // Devuelve todas las alertas (puede aceptar condiciones y orden opcional)
    public function findAll($conditions = [], $orderBy = '') {
        if (empty($conditions) && empty($orderBy)) {
            $sql = "SELECT * FROM {$this->table} ORDER BY fecha_registro DESC LIMIT 10";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return parent::findAll($conditions, $orderBy);
        }
    }

    // Devuelve las alertas más recientes
    public function getAlertasRecientes($limit = 5) {
        $sql = "SELECT * FROM {$this->table} ORDER BY fecha_registro DESC LIMIT :limit";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAlertasPorDias($dias) {
        $placeholders = implode(',', array_fill(0, count($dias), '?'));
        $sql = "SELECT DATE(fecha_registro) as fecha, COUNT(*) as total FROM {$this->table} WHERE DATE(fecha_registro) IN ($placeholders) GROUP BY fecha";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute($dias);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalActivas() {
        $query = "SELECT COUNT(*) as total FROM alertas_monitoreo WHERE estado = 'activa'";
        $result = $this->query($query);
        return $result && isset($result[0]['total']) ? (int)$result[0]['total'] : 0;
    }

    public function getTotalResueltas() {
        $query = "SELECT COUNT(*) as total FROM alertas_monitoreo WHERE estado = 'resuelta'";
        $result = $this->query($query);
        return $result && isset($result[0]['total']) ? (int)$result[0]['total'] : 0;
    }

    public function getAlertasPorDia($dias = 7) {
        try {
            // Generar arreglo de fechas de los últimos N días
            $fechas = [];
            for ($i = $dias - 1; $i >= 0; $i--) {
                $fechas[date('Y-m-d', strtotime("-{$i} days"))] = 0;
            }

            $query = "SELECT 
                DATE(fecha_registro) as fecha,
                COUNT(*) as total
                FROM alertas_monitoreo
                WHERE fecha_registro >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY DATE(fecha_registro)
                ORDER BY fecha";
            
            $result = $this->query($query, [$dias]);
            
            if ($result === false) {
                throw new Exception('Error al ejecutar la consulta');
            }

            // Actualizar el conteo de alertas para cada fecha
            foreach ($result as $row) {
                $fecha = date('Y-m-d', strtotime($row['fecha']));
                if (isset($fechas[$fecha])) {
                    $fechas[$fecha] = (int)$row['total'];
                }
            }

            // Convertir el array asociativo a array indexado
            $alertas = [];
            foreach ($fechas as $fecha => $total) {
                $alertas[] = [
                    'fecha' => $fecha,
                    'total' => $total
                ];
            }

            return $alertas;
        } catch (Exception $e) {
            error_log("Error en Alerta::getAlertasPorDia: " . $e->getMessage());
            return false;
        }
    }

    public function getAlertasPorDiaAction($dias = 7) {
        $query = "SELECT 
            DATE(fecha_creacion) as fecha,
            COUNT(*) as total
            FROM alertas_monitoreo
            WHERE fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY DATE(fecha_creacion)
            ORDER BY fecha";
        $result = $this->query($query, [$dias]);
        $alertas = [];
        if (is_array($result)) {
            foreach ($result as $row) {
                $alertas[] = [
                    'fecha' => date('D', strtotime($row['fecha'])),
                    'total' => (int)$row['total']
                ];
            }
        }
        return $alertas;
    }

    public function getActividadReciente($limite = 10) {
        try {
            $query = "SELECT 
                a.tipo_alerta as tipo,
                a.mensaje,
                a.fecha_registro,
                TIMESTAMPDIFF(MINUTE, a.fecha_registro, NOW()) as minutos
                FROM alertas_monitoreo a
                ORDER BY a.fecha_registro DESC
                LIMIT ?";
            
            $result = $this->query($query, [$limite]);
            
            if ($result === false) {
                throw new Exception('Error al ejecutar la consulta');
            }

            $actividades = [];
            if (is_array($result)) {
                foreach ($result as $row) {
                    $tiempo = $row['minutos'] < 60 
                        ? $row['minutos'] . ' min'
                        : floor($row['minutos'] / 60) . ' h';
                    $actividades[] = [
                        'tipo' => $row['tipo'],
                        'mensaje' => $row['mensaje'],
                        'tiempo' => $tiempo
                    ];
                }
            }
            return $actividades;
        } catch (Exception $e) {
            error_log("Error en Alerta::getActividadReciente: " . $e->getMessage());
            return [];
        }
    }

    public function getActividadRecienteAction($limite = 10) {
        $query = "SELECT 
            a.tipo,
            a.mensaje,
            a.fecha_registro,
            TIMESTAMPDIFF(MINUTE, a.fecha_registro, NOW()) as minutos
            FROM alertas_monitoreo a
            ORDER BY a.fecha_registro DESC
            LIMIT ?";
        $result = $this->query($query, [$limite]);
        $actividades = [];
        if (is_array($result)) {
            foreach ($result as $row) {
                $tiempo = $row['minutos'] < 60 
                    ? $row['minutos'] . ' min'
                    : floor($row['minutos'] / 60) . ' h';
                $actividades[] = [
                    'tipo' => $row['tipo'],
                    'mensaje' => $row['mensaje'],
                    'tiempo' => $tiempo
                ];
            }
        }
        return $actividades;
    }

    // Devuelve todas las alertas con información de dispositivo, mascota y propietario
    public function findAllWithJoin($conditions = [], $orderBy = '') {
        $sql = "SELECT a.*, d.nombre AS dispositivo_nombre, m.nombre AS mascota_nombre, u.nombre AS propietario_nombre
                FROM alertas_monitoreo a
                LEFT JOIN dispositivos d ON a.dispositivo_id = d.id_dispositivo
                LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota
                LEFT JOIN usuarios u ON m.usuario_id = u.id_usuario
                ORDER BY a.fecha_registro DESC LIMIT 50";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getMensajePorDefecto($tipo_alerta, $valor, $min, $max, $esEspecifica = false) {
        $base = '';
        if ($tipo_alerta == 'temperatura') {
            if ($valor > $max) $base = 'Temperatura elevada detectada';
            if ($valor < $min) $base = 'Temperatura baja detectada';
        } elseif ($tipo_alerta == 'ritmo_cardiaco') {
            if ($valor > $max) $base = 'Frecuencia cardíaca elevada detectada';
            if ($valor < $min) $base = 'Frecuencia cardíaca baja detectada';
        } elseif ($tipo_alerta == 'bateria') {
            if ($valor <= $min) $base = 'Batería baja detectada';
        }
        if ($esEspecifica && $base) {
            return $base . ' (Alerta específica)';
        }
        return $base ?: 'Alerta generada';
    }
}
?> 