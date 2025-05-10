<?php
class Dispositivo extends Model {
    protected $table = 'dispositivos';

    public function __construct() {
        parent::__construct();
    }

    public function getDispositivosWithMascotas($usuario_id) {
        $sql = "SELECT d.*, m.nombre as mascota_nombre, m.especie, m.raza
                FROM {$this->table} d
                LEFT JOIN mascotas m ON d.mascota_id = m.id
                WHERE d.usuario_id = :usuario_id
                ORDER BY d.ultima_conexion DESC";
        
        return $this->query($sql, [':usuario_id' => $usuario_id]);
    }

    public function createDispositivo($data) {
        return $this->create($data);
    }

    public function updateDispositivo($id, $data) {
        return $this->update($id, $data);
    }

    public function deleteDispositivo($id) {
        return $this->delete($id);
    }

    public function getDispositivoById($id) {
        return $this->find($id);
    }

    public function getDispositivosByMascota($mascotaId) {
        return $this->findAll(['mascota_id' => $mascotaId]);
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
        $sql = "UPDATE {$this->table} 
                SET estado = :estado,
                    ultima_conexion = NOW()
                WHERE id = :id";
        
        return $this->query($sql, [
            ':id' => $id,
            ':estado' => $estado
        ]);
    }

    public function getUltimosDatos($dispositivoId, $limite = 10) {
        $sql = "SELECT * FROM datos_sensores 
                WHERE dispositivo_id = ? 
                ORDER BY fecha DESC 
                LIMIT ?";
        return $this->query($sql, [$dispositivoId, $limite]);
    }

    public function getEstadisticas($usuario_id) {
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
    }

    public function getDispositivosPorTipo($usuario_id, $tipo) {
        $sql = "SELECT d.*, m.nombre as mascota_nombre
                FROM {$this->table} d
                LEFT JOIN mascotas m ON d.mascota_id = m.id
                WHERE d.usuario_id = :usuario_id 
                AND d.tipo = :tipo
                ORDER BY d.ultima_conexion DESC";
        
        return $this->query($sql, [
            ':usuario_id' => $usuario_id,
            ':tipo' => $tipo
        ]);
    }

    public function getDispositivosPorMascota($mascota_id) {
        $sql = "SELECT d.*, m.nombre as mascota_nombre
                FROM {$this->table} d
                JOIN mascotas m ON d.mascota_id = m.id
                WHERE m.id = :mascota_id
                ORDER BY d.ultima_conexion DESC";
        
        return $this->query($sql, [':mascota_id' => $mascota_id]);
    }

    public function getDispositivosSinMascota($usuario_id) {
        $sql = "SELECT d.*
                FROM {$this->table} d
                WHERE d.usuario_id = :usuario_id 
                AND d.mascota_id IS NULL
                ORDER BY d.ultima_conexion DESC";
        
        return $this->query($sql, [':usuario_id' => $usuario_id]);
    }

    public function getDispositivosPorEstado($usuario_id) {
        $sql = "SELECT 
                    estado,
                    COUNT(*) as total
                FROM {$this->table}
                WHERE usuario_id = :usuario_id
                GROUP BY estado";
        
        return $this->query($sql, [':usuario_id' => $usuario_id]);
    }

    public function getDispositivosPorTipoConEstadisticas($usuario_id) {
        $sql = "SELECT 
                    tipo,
                    COUNT(*) as total,
                    SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activos,
                    COUNT(DISTINCT mascota_id) as mascotas_asignadas
                FROM {$this->table}
                WHERE usuario_id = :usuario_id
                GROUP BY tipo";
        
        return $this->query($sql, [':usuario_id' => $usuario_id]);
    }

    public function getDispositivosConAlertas($usuario_id) {
        $sql = "SELECT DISTINCT d.*, m.nombre as mascota_nombre
                FROM {$this->table} d
                LEFT JOIN mascotas m ON d.mascota_id = m.id
                JOIN alertas a ON d.id = a.dispositivo_id
                WHERE d.usuario_id = :usuario_id 
                AND a.leida = 0
                ORDER BY d.ultima_conexion DESC";
        
        return $this->query($sql, [':usuario_id' => $usuario_id]);
    }

    public function getDispositivosPorUltimaConexion($usuario_id, $dias = 7) {
        $sql = "SELECT d.*, m.nombre as mascota_nombre
                FROM {$this->table} d
                LEFT JOIN mascotas m ON d.mascota_id = m.id
                WHERE d.usuario_id = :usuario_id 
                AND d.ultima_conexion >= DATE_SUB(NOW(), INTERVAL :dias DAY)
                ORDER BY d.ultima_conexion DESC";
        
        return $this->query($sql, [
            ':usuario_id' => $usuario_id,
            ':dias' => $dias
        ]);
    }
}
?> 