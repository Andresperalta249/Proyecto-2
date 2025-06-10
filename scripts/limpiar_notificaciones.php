<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Notificacion.php';

class LimpiadorNotificaciones {
    private $notificacionModel;
    private $diasRetencion;

    public function __construct($diasRetencion = 30) {
        $this->notificacionModel = new Notificacion();
        $this->diasRetencion = $diasRetencion;
    }

    public function limpiarNotificacionesAntiguas() {
        try {
            // Obtener estadísticas antes de la limpieza
            $estadisticasAntes = $this->obtenerEstadisticas();

            // Ejecutar limpieza
            $this->notificacionModel->eliminarNotificacionesAntiguas($this->diasRetencion);

            // Obtener estadísticas después de la limpieza
            $estadisticasDespues = $this->obtenerEstadisticas();

            // Calcular diferencias
            $notificacionesEliminadas = $estadisticasAntes['total'] - $estadisticasDespues['total'];
            $notificacionesLeidasEliminadas = $estadisticasAntes['leidas'] - $estadisticasDespues['leidas'];
            $notificacionesNoLeidasEliminadas = $estadisticasAntes['no_leidas'] - $estadisticasDespues['no_leidas'];

            // Registrar resultados
            $this->registrarResultados([
                'fecha' => date('Y-m-d H:i:s'),
                'dias_retencion' => $this->diasRetencion,
                'notificaciones_eliminadas' => $notificacionesEliminadas,
                'leidas_eliminadas' => $notificacionesLeidasEliminadas,
                'no_leidas_eliminadas' => $notificacionesNoLeidasEliminadas,
                'espacio_liberado' => $this->calcularEspacioLiberado($notificacionesEliminadas)
            ]);

            return true;
        } catch (Exception $e) {
            $this->registrarError($e->getMessage());
            return false;
        }
    }

    private function obtenerEstadisticas() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN leida = 1 THEN 1 ELSE 0 END) as leidas,
                    SUM(CASE WHEN leida = 0 THEN 1 ELSE 0 END) as no_leidas
                FROM notificaciones";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function calcularEspacioLiberado($cantidadNotificaciones) {
        // Estimación aproximada del espacio promedio por notificación
        $espacioPromedio = 1024; // 1KB por notificación
        return $cantidadNotificaciones * $espacioPromedio;
    }

    private function registrarResultados($datos) {
        $logFile = __DIR__ . '/../logs/limpieza_notificaciones.log';
        $mensaje = sprintf(
            "[%s] Limpieza completada - Retención: %d días - Eliminadas: %d (Leídas: %d, No leídas: %d) - Espacio liberado: %d bytes\n",
            $datos['fecha'],
            $datos['dias_retencion'],
            $datos['notificaciones_eliminadas'],
            $datos['leidas_eliminadas'],
            $datos['no_leidas_eliminadas'],
            $datos['espacio_liberado']
        );

        file_put_contents($logFile, $mensaje, FILE_APPEND);
    }

    private function registrarError($mensaje) {
        $logFile = __DIR__ . '/../logs/limpieza_notificaciones.log';
        $error = sprintf(
            "[%s] ERROR: %s\n",
            date('Y-m-d H:i:s'),
            $mensaje
        );

        file_put_contents($logFile, $error, FILE_APPEND);
    }
}

// Ejecutar script
$limpiador = new LimpiadorNotificaciones();
$limpiador->limpiarNotificacionesAntiguas(); 