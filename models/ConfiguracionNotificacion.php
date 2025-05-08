<?php

class ConfiguracionNotificacion extends Model {
    public function __construct() {
        parent::__construct();
    }

    public function obtenerConfiguracion($usuarioId) {
        $sql = "SELECT * FROM configuracion_notificaciones WHERE usuario_id = :usuario_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        $config = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$config) {
            // Si no existe configuración, crear una por defecto
            $config = $this->crearConfiguracionPorDefecto($usuarioId);
        } else {
            // Decodificar campos JSON
            $config['tipos'] = json_decode($config['tipos'], true);
            $config['metodos'] = json_decode($config['metodos'], true);
        }

        return $config;
    }

    public function actualizarConfiguracion($usuarioId, $config) {
        // Validar y preparar datos
        $tipos = [
            'alertas' => $config['tipos']['alertas'] ?? false,
            'dispositivos' => $config['tipos']['dispositivos'] ?? false,
            'mascotas' => $config['tipos']['mascotas'] ?? false
        ];

        $metodos = [
            'push' => $config['metodos']['push'] ?? false,
            'email' => $config['metodos']['email'] ?? false
        ];

        $frecuenciaEmail = in_array($config['frecuencia_email'], ['inmediato', 'diario', 'semanal']) 
            ? $config['frecuencia_email'] 
            : 'inmediato';

        $horaInicio = $this->validarHora($config['hora_inicio']) ? $config['hora_inicio'] : '08:00:00';
        $horaFin = $this->validarHora($config['hora_fin']) ? $config['hora_fin'] : '22:00:00';
        $notifUrgentes = (bool)($config['notif_urgentes'] ?? true);

        // Verificar si existe configuración
        $sql = "SELECT id FROM configuracion_notificaciones WHERE usuario_id = :usuario_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        $existe = $stmt->fetch();

        if ($existe) {
            // Actualizar configuración existente
            $sql = "UPDATE configuracion_notificaciones SET 
                    tipos = :tipos,
                    metodos = :metodos,
                    frecuencia_email = :frecuencia_email,
                    hora_inicio = :hora_inicio,
                    hora_fin = :hora_fin,
                    notif_urgentes = :notif_urgentes
                    WHERE usuario_id = :usuario_id";
        } else {
            // Crear nueva configuración
            $sql = "INSERT INTO configuracion_notificaciones 
                    (usuario_id, tipos, metodos, frecuencia_email, hora_inicio, hora_fin, notif_urgentes)
                    VALUES 
                    (:usuario_id, :tipos, :metodos, :frecuencia_email, :hora_inicio, :hora_fin, :notif_urgentes)";
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'usuario_id' => $usuarioId,
            'tipos' => json_encode($tipos),
            'metodos' => json_encode($metodos),
            'frecuencia_email' => $frecuenciaEmail,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'notif_urgentes' => $notifUrgentes
        ]);
    }

    private function crearConfiguracionPorDefecto($usuarioId) {
        $config = [
            'usuario_id' => $usuarioId,
            'tipos' => [
                'alertas' => true,
                'dispositivos' => true,
                'mascotas' => true
            ],
            'metodos' => [
                'push' => true,
                'email' => true
            ],
            'frecuencia_email' => 'inmediato',
            'hora_inicio' => '08:00:00',
            'hora_fin' => '22:00:00',
            'notif_urgentes' => true
        ];

        $this->actualizarConfiguracion($usuarioId, $config);
        return $config;
    }

    private function validarHora($hora) {
        return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $hora);
    }

    public function verificarNotificacionPermitida($usuarioId, $tipo, $urgente = false) {
        $config = $this->obtenerConfiguracion($usuarioId);
        
        // Si es urgente y está permitido, siempre permitir
        if ($urgente && $config['notif_urgentes']) {
            return true;
        }

        // Verificar si el tipo está habilitado
        if (!isset($config['tipos'][$tipo]) || !$config['tipos'][$tipo]) {
            return false;
        }

        // Verificar horario
        $horaActual = date('H:i:s');
        if ($horaActual < $config['hora_inicio'] || $horaActual > $config['hora_fin']) {
            return false;
        }

        return true;
    }

    public function obtenerMetodosNotificacion($usuarioId) {
        $config = $this->obtenerConfiguracion($usuarioId);
        return $config['metodos'];
    }

    public function obtenerFrecuenciaEmail($usuarioId) {
        $config = $this->obtenerConfiguracion($usuarioId);
        return $config['frecuencia_email'];
    }

    public function obtenerUsuariosParaNotificacionDiaria() {
        $sql = "SELECT usuario_id 
                FROM configuracion_notificaciones 
                WHERE frecuencia_email = 'diario' 
                AND metodos->>'$.email' = 'true'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function obtenerUsuariosParaNotificacionSemanal() {
        $sql = "SELECT usuario_id 
                FROM configuracion_notificaciones 
                WHERE frecuencia_email = 'semanal' 
                AND metodos->>'$.email' = 'true'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
} 