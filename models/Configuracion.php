<?php
require_once 'models/Model.php';

class Configuracion extends Model {
    public function __construct() {
        parent::__construct();
    }

    public function getConfiguracion() {
        $sql = "SELECT * FROM configuracion WHERE id_configuracion = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateConfiguracion($data) {
        $sql = "UPDATE configuracion SET 
                nombre_sistema = :nombre_sistema,
                email_contacto = :email_contacto,
                telefono_contacto = :telefono_contacto,
                direccion = :direccion,
                tiempo_actualizacion = :tiempo_actualizacion,
                dias_retener_logs = :dias_retener_logs,
                notificaciones_email = :notificaciones_email,
                notificaciones_push = :notificaciones_push,
                tema_oscuro = :tema_oscuro,
                fecha_actualizacion = NOW()
                WHERE id_configuracion = 1";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function generarBackup() {
        $backupDir = 'backups/';
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0777, true);
        }

        $backupFile = $backupDir . 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $command = sprintf(
            'mysqldump -h %s -u %s -p%s %s > %s',
            DB_HOST,
            DB_USER,
            DB_PASS,
            DB_NAME,
            $backupFile
        );

        exec($command, $output, $returnVar);

        if ($returnVar === 0 && file_exists($backupFile)) {
            return $backupFile;
        }

        return false;
    }

    public function restaurarBackup($backupFile) {
        $command = sprintf(
            'mysql -h %s -u %s -p%s %s < %s',
            DB_HOST,
            DB_USER,
            DB_PASS,
            DB_NAME,
            $backupFile
        );

        exec($command, $output, $returnVar);
        return $returnVar === 0;
    }

    public function getEstadisticasSistema() {
        $stats = [];

        // Total de usuarios
        $sql = "SELECT COUNT(*) as total FROM usuarios";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['total_usuarios'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total de mascotas
        $sql = "SELECT COUNT(*) as total FROM mascotas";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['total_mascotas'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total de dispositivos
        $sql = "SELECT COUNT(*) as total FROM dispositivos";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['total_dispositivos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total de alertas
        $sql = "SELECT COUNT(*) as total FROM alertas";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['total_alertas'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Alertas por tipo
        $sql = "SELECT tipo, COUNT(*) as total FROM alertas GROUP BY tipo";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['alertas_por_tipo'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Dispositivos por tipo
        $sql = "SELECT tipo, COUNT(*) as total FROM dispositivos GROUP BY tipo";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['dispositivos_por_tipo'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Mascotas por especie
        $sql = "SELECT especie, COUNT(*) as total FROM mascotas GROUP BY especie";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['mascotas_por_especie'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Actividad reciente
        $sql = "SELECT COUNT(*) as total FROM logs WHERE fecha >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['actividad_24h'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        return $stats;
    }

    public function getLogsSistema($limite = 100) {
        $sql = "SELECT l.*, u.nombre as usuario_nombre 
                FROM logs l 
                LEFT JOIN usuarios u ON l.usuario_id = u.id 
                ORDER BY l.fecha DESC 
                LIMIT :limite";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getErroresSistema($limite = 100) {
        $sql = "SELECT * FROM logs 
                WHERE nivel = 'error' 
                ORDER BY fecha DESC 
                LIMIT :limite";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 