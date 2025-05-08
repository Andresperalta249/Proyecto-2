<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Notificacion.php';
require_once __DIR__ . '/../models/ConfiguracionNotificacion.php';
require_once __DIR__ . '/../models/Usuario.php';

class EnviadorNotificacionesEmail {
    private $notificacionModel;
    private $configuracionModel;
    private $usuarioModel;

    public function __construct() {
        $this->notificacionModel = new Notificacion();
        $this->configuracionModel = new ConfiguracionNotificacion();
        $this->usuarioModel = new Usuario();
    }

    public function enviarNotificacionesDiarias() {
        $usuarios = $this->configuracionModel->obtenerUsuariosParaNotificacionDiaria();
        
        foreach ($usuarios as $usuarioId) {
            $this->enviarResumenNotificaciones($usuarioId, 'diario');
        }
    }

    public function enviarNotificacionesSemanales() {
        $usuarios = $this->configuracionModel->obtenerUsuariosParaNotificacionSemanal();
        
        foreach ($usuarios as $usuarioId) {
            $this->enviarResumenNotificaciones($usuarioId, 'semanal');
        }
    }

    private function enviarResumenNotificaciones($usuarioId, $tipo) {
        // Obtener usuario
        $usuario = $this->usuarioModel->obtenerUsuario($usuarioId);
        if (!$usuario) {
            return;
        }

        // Obtener notificaciones no leídas
        $fechaInicio = $tipo === 'diario' 
            ? date('Y-m-d 00:00:00', strtotime('-1 day'))
            : date('Y-m-d 00:00:00', strtotime('-7 days'));
        
        $notificaciones = $this->notificacionModel->obtenerNotificacionesPorFecha(
            $usuarioId,
            $fechaInicio,
            date('Y-m-d H:i:s')
        );

        if (empty($notificaciones)) {
            return;
        }

        // Preparar contenido del correo
        $asunto = $tipo === 'diario' 
            ? 'Resumen Diario de Notificaciones'
            : 'Resumen Semanal de Notificaciones';

        $contenido = $this->generarContenidoEmail($notificaciones, $tipo);

        // Enviar correo
        $this->enviarEmail($usuario['email'], $asunto, $contenido);

        // Marcar notificaciones como leídas
        foreach ($notificaciones as $notificacion) {
            $this->notificacionModel->marcarComoLeida($notificacion['id'], $usuarioId);
        }
    }

    private function generarContenidoEmail($notificaciones, $tipo) {
        $html = '<!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <title>Resumen de Notificaciones</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .notification { margin-bottom: 20px; padding: 15px; border-left: 4px solid #007bff; }
                        .notification.alert { border-left-color: #dc3545; }
                        .notification.success { border-left-color: #28a745; }
                        .notification.info { border-left-color: #17a2b8; }
                        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="header">
                            <h2>Resumen de Notificaciones</h2>
                            <p>' . ($tipo === 'diario' ? 'Últimas 24 horas' : 'Última semana') . '</p>
                        </div>';

        foreach ($notificaciones as $notificacion) {
            $clase = '';
            switch ($notificacion['tipo']) {
                case 'alerta':
                    $clase = 'alert';
                    break;
                case 'exito':
                    $clase = 'success';
                    break;
                case 'info':
                    $clase = 'info';
                    break;
            }

            $html .= '<div class="notification ' . $clase . '">
                        <h3>' . htmlspecialchars($notificacion['titulo']) . '</h3>
                        <p>' . htmlspecialchars($notificacion['mensaje']) . '</p>
                        <small>' . date('d/m/Y H:i', strtotime($notificacion['fecha_creacion'])) . '</small>
                    </div>';
        }

        $html .= '<div class="footer">
                    <p>Este es un correo automático, por favor no responda a este mensaje.</p>
                    <p>Para gestionar sus notificaciones, visite la configuración de su cuenta.</p>
                </div>
            </div>
        </body>
        </html>';

        return $html;
    }

    private function enviarEmail($destinatario, $asunto, $contenido) {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . SMTP_FROM_EMAIL,
            'Reply-To: ' . SMTP_FROM_EMAIL,
            'X-Mailer: PHP/' . phpversion()
        ];

        return mail($destinatario, $asunto, $contenido, implode("\r\n", $headers));
    }
}

// Ejecutar script
$enviador = new EnviadorNotificacionesEmail();

// Verificar si es un resumen diario o semanal
$tipo = isset($argv[1]) ? $argv[1] : 'diario';

if ($tipo === 'diario') {
    $enviador->enviarNotificacionesDiarias();
} elseif ($tipo === 'semanal') {
    $enviador->enviarNotificacionesSemanales();
} else {
    echo "Tipo de notificación no válido. Use 'diario' o 'semanal'.\n";
    exit(1);
} 