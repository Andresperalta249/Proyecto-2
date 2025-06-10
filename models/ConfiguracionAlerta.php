<?php
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Database.php';

class ConfiguracionAlerta extends Model {
    protected $table = 'configuraciones_alertas';
    private $tiposPermitidos = ['temperatura', 'ritmo_cardiaco', 'bateria', 'inactividad'];
    private $prioridadesPermitidas = ['baja', 'media', 'alta'];
    protected $db;

    public function __construct() {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    public function validarConfiguracion($data) {
        $errores = [];

        // Validar tipo de sensor
        if (!in_array($data['tipo_sensor'], $this->tiposPermitidos)) {
            $errores[] = "Tipo de sensor no válido. Debe ser uno de: " . implode(', ', $this->tiposPermitidos);
        }

        // Validar valores numéricos
        if (!is_numeric($data['valor_minimo']) || !is_numeric($data['valor_maximo']) || 
            !is_numeric($data['valor_critico_minimo']) || !is_numeric($data['valor_critico_maximo'])) {
            $errores[] = "Los valores deben ser numéricos";
        }

        // Validar rango de valores
        if ($data['valor_minimo'] >= $data['valor_maximo']) {
            $errores[] = "El valor mínimo debe ser menor que el valor máximo";
        }

        if ($data['valor_critico_minimo'] >= $data['valor_critico_maximo']) {
            $errores[] = "El valor crítico mínimo debe ser menor que el valor crítico máximo";
        }

        return $errores;
    }

    public function getConfiguracionesByDispositivo($dispositivo_id) {
        $sql = "SELECT ca.*, d.nombre as dispositivo_nombre 
                FROM {$this->table} ca 
                JOIN dispositivos d ON ca.dispositivo_id = d.id 
                WHERE ca.dispositivo_id = :dispositivo_id";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute(['dispositivo_id' => $dispositivo_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearConfiguracion($data) {
        $errores = $this->validarConfiguracion($data);
        if (!empty($errores)) {
            throw new Exception(implode("\n", $errores));
        }

        $allowed = ['tipo_sensor', 'nombre', 'valor_minimo', 'valor_maximo', 
                   'valor_critico_minimo', 'valor_critico_maximo', 'unidad_medida', 
                   'mensaje_alerta', 'activo'];
        $filtered = array_intersect_key($data, array_flip($allowed));

        try {
            $this->db->getConnection()->beginTransaction();
            $resultado = $this->create($filtered);
            $this->db->getConnection()->commit();
            return $resultado;
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            throw $e;
        }
    }

    public function actualizarConfiguracion($id, $data) {
        $errores = $this->validarConfiguracion($data);
        if (!empty($errores)) {
            throw new Exception(implode("\n", $errores));
        }

        $campos = [];
        $valores = [':id' => $id];

        foreach ($data as $campo => $valor) {
            if ($campo !== 'id' && $campo !== 'fecha_creacion' && $campo !== 'fecha_actualizacion') {
                $campos[] = "`$campo` = :$campo";
                $valores[":$campo"] = $valor;
            }
        }

        try {
            $this->db->getConnection()->beginTransaction();
            $sql = "UPDATE {$this->table} SET " . implode(', ', $campos) . " WHERE id = :id";
            $resultado = $this->query($sql, $valores);
            $this->db->getConnection()->commit();
            return $resultado;
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            throw $e;
        }
    }

    public function eliminarConfiguracion($id) {
        try {
            $this->db->getConnection()->beginTransaction();
            $resultado = $this->delete($id);
            $this->db->getConnection()->commit();
            return $resultado;
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            throw $e;
        }
    }

    public function getConfiguracionesActivas($pagina = 1, $porPagina = 10) {
        $offset = ($pagina - 1) * $porPagina;
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE activo = 1 
                ORDER BY fecha_creacion DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalConfiguracionesActivas() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE activo = 1";
        $stmt = $this->db->getConnection()->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function validarValores($tipo_sensor, $valor) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE tipo_sensor = :tipo_sensor 
                AND activo = 1";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute(['tipo_sensor' => $tipo_sensor]);
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$config) {
            return false;
        }
        
        return $valor >= $config['valor_minimo'] && $valor <= $config['valor_maximo'];
    }

    public static function getMensajePorDefecto($tipo_alerta, $valor, $min, $max) {
        if ($tipo_alerta == 'temperatura') {
            if ($valor > $max) return 'Temperatura elevada detectada';
            if ($valor < $min) return 'Temperatura baja detectada';
        } elseif ($tipo_alerta == 'ritmo_cardiaco') {
            if ($valor > $max) return 'Frecuencia cardíaca elevada detectada';
            if ($valor < $min) return 'Frecuencia cardíaca baja detectada';
        } elseif ($tipo_alerta == 'bateria') {
            if ($valor <= $min) return 'Batería baja detectada';
        }
        return 'Alerta generada';
    }

    /**
     * Obtiene la configuración de alerta para un tipo de sensor específico
     */
    public function getConfiguracionPorTipo($tipoSensor) {
        $sql = "SELECT * FROM {$this->table} WHERE tipo_sensor = :tipo AND activo = 1";
        return $this->query($sql, [':tipo' => $tipoSensor])->fetch();
    }

    /**
     * Obtiene todas las configuraciones activas
     */
    public function getAllConfiguraciones() {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE activo = 1";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!$result) {
                error_log("No se encontraron configuraciones de alertas activas");
                return [];
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error en getAllConfiguraciones: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica si un valor está dentro del rango normal
     * Retorna: 'normal', 'advertencia' o 'critico'
     */
    public function verificarEstado($tipoSensor, $valor) {
        $config = $this->getConfiguracionPorTipo($tipoSensor);
        
        if (!$config) {
            return 'normal';
        }

        if ($valor < $config['valor_critico_minimo'] || $valor > $config['valor_critico_maximo']) {
            return 'critico';
        }

        if ($valor < $config['valor_minimo'] || $valor > $config['valor_maximo']) {
            return 'advertencia';
        }

        return 'normal';
    }

    /**
     * Obtiene el mensaje de alerta para un valor específico
     */
    public function getMensajeAlerta($tipoSensor, $valor) {
        $config = $this->getConfiguracionPorTipo($tipoSensor);
        
        if (!$config) {
            return null;
        }

        $estado = $this->verificarEstado($tipoSensor, $valor);
        
        if ($estado === 'normal') {
            return null;
        }

        return $config['mensaje_alerta'];
    }

    public function obtenerConfiguracionGeneral() {
        $query = "SELECT * FROM configuraciones_alertas WHERE tipo_sensor IN ('temperatura', 'ritmo_cardiaco', 'bateria')";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $config = [];
        foreach ($resultados as $row) {
            $key = $row['especie'] . ($row['tamanio'] ? '_' . $row['tamanio'] : '');
            if (!isset($config[$key])) $config[$key] = [];
            $config[$key][$row['tipo_sensor']] = [
                'nombre' => $row['nombre'],
                'valor_minimo' => $row['valor_minimo'],
                'mensaje_min_normal' => $row['mensaje_min_normal'],
                'valor_maximo' => $row['valor_maximo'],
                'mensaje_max_normal' => $row['mensaje_max_normal'],
                'valor_critico_minimo' => $row['valor_critico_minimo'],
                'mensaje_critico_min' => $row['mensaje_critico_min'],
                'valor_critico_maximo' => $row['valor_critico_maximo'],
                'mensaje_critico_max' => $row['mensaje_critico_max'],
                'unidad_medida' => $row['unidad_medida']
            ];
        }
        return $config;
    }

    public function guardarConfiguracionGeneral($datos) {
        $this->db->getConnection()->beginTransaction();
        try {
            foreach ($datos as $key => $sensores) {
                list($especie, $tamanio) = strpos($key, '_') !== false ? explode('_', $key, 2) : [$key, null];
                foreach ($sensores as $tipo_sensor => $config) {
                    $query = "UPDATE configuraciones_alertas SET nombre = :nombre, valor_minimo = :valor_minimo, mensaje_min_normal = :mensaje_min_normal, valor_maximo = :valor_maximo, mensaje_max_normal = :mensaje_max_normal, valor_critico_minimo = :valor_critico_minimo, mensaje_critico_min = :mensaje_critico_min, valor_critico_maximo = :valor_critico_maximo, mensaje_critico_max = :mensaje_critico_max WHERE especie = :especie AND tipo_sensor = :tipo_sensor" . ($tamanio ? " AND tamanio = :tamanio" : " AND tamanio IS NULL");
                    $stmt = $this->db->getConnection()->prepare($query);
                    $stmt->bindParam(':nombre', $config['nombre']);
                    $stmt->bindParam(':valor_minimo', $config['valor_minimo']);
                    $stmt->bindParam(':mensaje_min_normal', $config['mensaje_min_normal']);
                    $stmt->bindParam(':valor_maximo', $config['valor_maximo']);
                    $stmt->bindParam(':mensaje_max_normal', $config['mensaje_max_normal']);
                    $stmt->bindParam(':valor_critico_minimo', $config['valor_critico_minimo']);
                    $stmt->bindParam(':mensaje_critico_min', $config['mensaje_critico_min']);
                    $stmt->bindParam(':valor_critico_maximo', $config['valor_critico_maximo']);
                    $stmt->bindParam(':mensaje_critico_max', $config['mensaje_critico_max']);
                    $stmt->bindParam(':especie', $especie);
                    $stmt->bindParam(':tipo_sensor', $tipo_sensor);
                    if ($tamanio) {
                        $stmt->bindParam(':tamanio', $tamanio);
                    }
                    $stmt->execute();
                }
            }
            $this->db->getConnection()->commit();
            return true;
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            return false;
        }
    }
} 