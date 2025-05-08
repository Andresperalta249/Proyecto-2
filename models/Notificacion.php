<?php
require_once 'models/Model.php';

class Notificacion extends Model {
    public function __construct() {
        parent::__construct();
    }

    public function crearNotificacion($usuarioId, $titulo, $mensaje, $tipo = 'info', $enlace = null) {
        $sql = "INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo, enlace, fecha_creacion) 
                VALUES (:usuario_id, :titulo, :mensaje, :tipo, :enlace, NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'usuario_id' => $usuarioId,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'tipo' => $tipo,
            'enlace' => $enlace
        ]);
    }

    public function obtenerNotificaciones($usuarioId, $limite = 10) {
        $sql = "SELECT * FROM notificaciones 
                WHERE usuario_id = :usuario_id 
                ORDER BY fecha_creacion DESC 
                LIMIT :limite";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function marcarComoLeida($notificacionId, $usuarioId) {
        $sql = "UPDATE notificaciones 
                SET leida = 1, fecha_lectura = NOW() 
                WHERE id = :id AND usuario_id = :usuario_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $notificacionId,
            'usuario_id' => $usuarioId
        ]);
    }

    public function marcarTodasComoLeidas($usuarioId) {
        $sql = "UPDATE notificaciones 
                SET leida = 1, fecha_lectura = NOW() 
                WHERE usuario_id = :usuario_id AND leida = 0";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['usuario_id' => $usuarioId]);
    }

    public function eliminarNotificacion($notificacionId, $usuarioId) {
        $sql = "DELETE FROM notificaciones 
                WHERE id = :id AND usuario_id = :usuario_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $notificacionId,
            'usuario_id' => $usuarioId
        ]);
    }

    public function eliminarNotificacionesAntiguas($dias = 30) {
        $sql = "DELETE FROM notificaciones 
                WHERE fecha_creacion < DATE_SUB(NOW(), INTERVAL :dias DAY)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['dias' => $dias]);
    }

    public function contarNoLeidas($usuarioId) {
        $sql = "SELECT COUNT(*) as total 
                FROM notificaciones 
                WHERE usuario_id = :usuario_id AND leida = 0";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'];
    }

    public function obtenerNotificacionesPorTipo($usuarioId, $tipo, $limite = 10) {
        $sql = "SELECT * FROM notificaciones 
                WHERE usuario_id = :usuario_id AND tipo = :tipo 
                ORDER BY fecha_creacion DESC 
                LIMIT :limite";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerNotificacionesPorFecha($usuarioId, $fechaInicio, $fechaFin) {
        $sql = "SELECT * FROM notificaciones 
                WHERE usuario_id = :usuario_id 
                AND fecha_creacion BETWEEN :fecha_inicio AND :fecha_fin 
                ORDER BY fecha_creacion DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerEstadisticas($usuarioId) {
        $sql = "SELECT 
                    COUNT(*) as total_notificaciones,
                    SUM(CASE WHEN leida = 1 THEN 1 ELSE 0 END) as notificaciones_leidas,
                    SUM(CASE WHEN leida = 0 THEN 1 ELSE 0 END) as notificaciones_no_leidas,
                    COUNT(DISTINCT tipo) as tipos_diferentes,
                    MAX(fecha_creacion) as ultima_notificacion
                FROM notificaciones 
                WHERE usuario_id = :usuario_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function enviarNotificacionEmail($usuarioId, $titulo, $mensaje) {
        // Obtener información del usuario
        $sql = "SELECT email, nombre FROM usuarios WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $usuarioId]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            return false;
        }

        // Configurar el correo
        $to = $usuario['email'];
        $subject = $titulo;
        $headers = "From: " . SITE_NAME . " <" . SITE_EMAIL . ">\r\n";
        $headers .= "Reply-To: " . SITE_EMAIL . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        // Crear el cuerpo del correo
        $body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
                    .content { padding: 20px; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #6c757d; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>" . SITE_NAME . "</h2>
                    </div>
                    <div class='content'>
                        <p>Hola " . $usuario['nombre'] . ",</p>
                        <p>" . $mensaje . "</p>
                    </div>
                    <div class='footer'>
                        <p>Este es un correo automático, por favor no responda a este mensaje.</p>
                    </div>
                </div>
            </body>
            </html>
        ";

        // Enviar el correo
        return mail($to, $subject, $body, $headers);
    }

    public function enviarNotificacionPush($usuarioId, $titulo, $mensaje) {
        // Obtener el token de FCM del usuario
        $sql = "SELECT fcm_token FROM usuarios WHERE id = :id AND fcm_token IS NOT NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $usuarioId]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario || !$usuario['fcm_token']) {
            return false;
        }

        // Configurar la notificación push
        $data = [
            'to' => $usuario['fcm_token'],
            'notification' => [
                'title' => $titulo,
                'body' => $mensaje,
                'icon' => '/assets/img/logo.png',
                'click_action' => SITE_URL
            ],
            'data' => [
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'fecha' => date('Y-m-d H:i:s')
            ]
        ];

        // Enviar la notificación usando Firebase Cloud Messaging
        $headers = [
            'Authorization: key=' . FCM_SERVER_KEY,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
} 