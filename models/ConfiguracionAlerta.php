<?php
require_once 'models/Model.php';

class ConfiguracionAlerta extends Model {
    protected $table = 'configuraciones_alertas';
    private $tiposPermitidos = ['temperatura', 'ritmo_cardiaco', 'bateria', 'inactividad'];
    private $prioridadesPermitidas = ['baja', 'media', 'alta'];

    public function __construct() {
        parent::__construct();
    }

    public function validarConfiguracion($data) {
        $errores = [];

        // Validar tipo de alerta
        if (!in_array($data['tipo_alerta'], $this->tiposPermitidos)) {
            $errores[] = "Tipo de alerta no válido. Debe ser uno de: " . implode(', ', $this->tiposPermitidos);
        }

        // Validar prioridad
        if (!in_array($data['prioridad'], $this->prioridadesPermitidas)) {
            $errores[] = "Prioridad no válida. Debe ser una de: " . implode(', ', $this->prioridadesPermitidas);
        }

        // Validar valores numéricos
        if (!is_numeric($data['valor_minimo']) || !is_numeric($data['valor_maximo'])) {
            $errores[] = "Los valores mínimo y máximo deben ser numéricos";
        }

        // Validar rango de valores
        if ($data['valor_minimo'] >= $data['valor_maximo']) {
            $errores[] = "El valor mínimo debe ser menor que el valor máximo";
        }

        // Validar dispositivo
        if (!isset($data['dispositivo_id']) || !is_numeric($data['dispositivo_id'])) {
            $errores[] = "ID de dispositivo no válido";
        }

        return $errores;
    }

    public function getConfiguracionesByDispositivo($dispositivo_id) {
        $sql = "SELECT ca.*, d.nombre as dispositivo_nombre 
                FROM {$this->table} ca 
                JOIN dispositivos d ON ca.dispositivo_id = d.id 
                WHERE ca.dispositivo_id = :dispositivo_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['dispositivo_id' => $dispositivo_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearConfiguracion($data) {
        $errores = $this->validarConfiguracion($data);
        if (!empty($errores)) {
            throw new Exception(implode("\n", $errores));
        }

        $allowed = ['dispositivo_id', 'tipo_alerta', 'valor_minimo', 'valor_maximo', 'prioridad', 'estado'];
        $filtered = array_intersect_key($data, array_flip($allowed));
        
        try {
            $this->db->beginTransaction();
            $resultado = $this->create($filtered);
            $this->db->commit();
            return $resultado;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function actualizarConfiguracion($id, $data) {
        $errores = $this->validarConfiguracion($data);
        if (!empty($errores)) {
            throw new Exception(implode("\n", $errores));
        }

        $allowed = ['valor_minimo', 'valor_maximo', 'prioridad', 'estado'];
        $filtered = array_intersect_key($data, array_flip($allowed));
        
        try {
            $this->db->beginTransaction();
            $resultado = $this->update($id, $filtered);
            $this->db->commit();
            return $resultado;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function eliminarConfiguracion($id) {
        try {
            $this->db->beginTransaction();
            $resultado = $this->delete($id);
            $this->db->commit();
            return $resultado;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getConfiguracionesActivas($pagina = 1, $porPagina = 10) {
        $offset = ($pagina - 1) * $porPagina;
        
        $sql = "SELECT ca.*, d.nombre as dispositivo_nombre 
                FROM {$this->table} ca 
                JOIN dispositivos d ON ca.dispositivo_id = d.id 
                WHERE ca.estado = true 
                ORDER BY ca.fecha_creacion DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalConfiguracionesActivas() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE estado = true";
        $stmt = $this->db->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function validarValores($tipo_alerta, $valor, $dispositivo_id = null) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE tipo_alerta = :tipo_alerta 
                AND estado = true";
        
        $params = ['tipo_alerta' => $tipo_alerta];
        
        if ($dispositivo_id) {
            $sql .= " AND dispositivo_id = :dispositivo_id";
            $params['dispositivo_id'] = $dispositivo_id;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $configs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($configs)) {
            return false;
        }
        
        foreach ($configs as $config) {
            if ($valor >= $config['valor_minimo'] && $valor <= $config['valor_maximo']) {
                return true;
            }
        }
        
        return false;
    }
} 