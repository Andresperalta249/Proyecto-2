<?php
class DispositivoModel {
    private $db;
    private $table = 'dispositivos';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getDispositivoById($id) {
        try {
            $query = "SELECT d.id_dispositivo, d.nombre, d.mac, d.estado, d.bateria,
                            d.usuario_id, d.mascota_id,
                            m.nombre as nombre_mascota, m.especie as especie_mascota 
                     FROM {$this->table} d 
                     LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota 
                     WHERE d.id_dispositivo = :id";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getDispositivoById: " . $e->getMessage());
            return false;
        }
    }

    public function getDispositivosByUsuario($usuarioId) {
        try {
            $query = "SELECT d.id_dispositivo, d.nombre, d.mac, d.estado, d.bateria,
                            d.usuario_id, d.mascota_id,
                            m.nombre as nombre_mascota, m.especie as especie_mascota 
                     FROM {$this->table} d 
                     LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota 
                     WHERE d.usuario_id = :usuario_id";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getDispositivosByUsuario: " . $e->getMessage());
            return [];
        }
    }

    public function getUltimaUbicacion($dispositivoId) {
        try {
            $query = "SELECT latitud, longitud, fecha 
                     FROM datos_sensores 
                     WHERE dispositivo_id = :dispositivo_id 
                     ORDER BY fecha DESC 
                     LIMIT 1";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->bindParam(':dispositivo_id', $dispositivoId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getUltimaUbicacion: " . $e->getMessage());
            return false;
        }
    }

    public function getUltimosDatos($dispositivoId, $horas = 24) {
        try {
            $query = "SELECT temperatura, ritmo_cardiaco, bateria, latitud, longitud, fecha 
                     FROM datos_sensores 
                     WHERE dispositivo_id = :dispositivo_id 
                     AND fecha >= DATE_SUB(NOW(), INTERVAL :horas HOUR) 
                     ORDER BY fecha DESC";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->bindParam(':dispositivo_id', $dispositivoId, PDO::PARAM_INT);
            $stmt->bindParam(':horas', $horas, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getUltimosDatos: " . $e->getMessage());
            return [];
        }
    }

    public function getRuta($dispositivoId, $horas = 24) {
        try {
            $query = "SELECT latitud, longitud, fecha 
                     FROM datos_sensores 
                     WHERE dispositivo_id = :dispositivo_id 
                     AND fecha >= DATE_SUB(NOW(), INTERVAL :horas HOUR) 
                     ORDER BY fecha ASC";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->bindParam(':dispositivo_id', $dispositivoId, PDO::PARAM_INT);
            $stmt->bindParam(':horas', $horas, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getRuta: " . $e->getMessage());
            return [];
        }
    }

    public function getDispositivosWithMascotas($usuarioId) {
        try {
            $query = "SELECT d.id_dispositivo, d.nombre, d.mac, d.estado, d.bateria,
                            d.usuario_id, d.mascota_id,
                            m.nombre as nombre_mascota, m.especie as especie_mascota 
                     FROM {$this->table} d 
                     LEFT JOIN mascotas m ON d.mascota_id = m.id_mascota 
                     WHERE d.usuario_id = :usuario_id";
            
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DispositivoModel::getDispositivosWithMascotas: " . $e->getMessage());
            return [];
        }
    }
} 