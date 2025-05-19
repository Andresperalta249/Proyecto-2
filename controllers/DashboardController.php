<?php
class DashboardController extends Controller {
    private $mascotaModel;
    private $dispositivoModel;
    private $alertaModel;
    private $logModel;
    private $logger;

    public function __construct() {
        try {
            $this->logger = Logger::getInstance();
            $this->logger->info("Iniciando DashboardController");
            
            parent::__construct();
            $this->logger->info("Constructor padre completado");
            
            $this->logger->info("Cargando modelos...");
            $this->mascotaModel = $this->loadModel('Mascota');
            $this->logger->info("Modelo Mascota cargado");
            
            $this->dispositivoModel = $this->loadModel('Dispositivo');
            $this->logger->info("Modelo Dispositivo cargado");
            
            $this->alertaModel = $this->loadModel('Alerta');
            $this->logger->info("Modelo Alerta cargado");
            
            $this->logModel = $this->loadModel('Log');
            $this->logger->info("Modelo Log cargado");
        } catch (Exception $e) {
            $this->logger->error("Error en constructor: " . $e->getMessage());
            $this->logger->error("Trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    public function indexAction() {
        try {
            $this->logger->info("Iniciando indexAction");
            $this->logger->debug("SESSION: " . print_r($_SESSION, true));
            
            if (!isset($_SESSION['user_id'])) {
                $this->logger->warning("Usuario no autenticado");
                redirect('auth/login');
            }

            $this->logger->info("Usuario autenticado, ID: " . $_SESSION['user_id']);

            // Verificar si es admin/superadmin y tiene permiso para ver todos los dispositivos
            $esAdmin = in_array($_SESSION['user_role'] ?? 0, [1, 2]);
            $tienePermiso = function_exists('verificarPermiso') ? verificarPermiso('ver_todas_mascotas') : false;

            if ($esAdmin && $tienePermiso) {
                $totalMascotas = $this->mascotaModel->count();
                $totalDispositivos = $this->dispositivoModel->count();
                // Estadísticas de sensores globales
                $db = Database::getInstance();
                $rowSensores = $db->query('SELECT COUNT(*) as total FROM datos_sensores')->single();
                $totalSensores = $rowSensores ? $rowSensores['total'] : 0;
                $rowTemp = $db->query('SELECT AVG(temperatura) as promedio FROM datos_sensores')->single();
                $promedioTemp = $rowTemp && $rowTemp['promedio'] !== null ? $rowTemp['promedio'] : 0;

                // Obtener dispositivos activos con información del propietario
                $dispositivosActivos = $db->query('
                    SELECT d.*, u.nombre as propietario_nombre, m.nombre as mascota_nombre 
                    FROM dispositivos d 
                    LEFT JOIN usuarios u ON d.propietario_id = u.id 
                    LEFT JOIN mascotas m ON d.mascota_id = m.id 
                    WHERE d.estado = "activo"
                ')->resultSet();
            } else {
                $totalMascotas = $this->mascotaModel->count(['propietario_id' => $_SESSION['user_id']]);
                $totalDispositivos = $this->dispositivoModel->count(['propietario_id' => $_SESSION['user_id']]);
                // Estadísticas de sensores por usuario
                $db = Database::getInstance();
                $rowSensores = $db->query('SELECT COUNT(*) as total FROM datos_sensores ds JOIN dispositivos d ON ds.dispositivo_id = d.id WHERE d.propietario_id = ' . intval($_SESSION['user_id']))->single();
                $totalSensores = $rowSensores ? $rowSensores['total'] : 0;
                $rowTemp = $db->query('SELECT AVG(ds.temperatura) as promedio FROM datos_sensores ds JOIN dispositivos d ON ds.dispositivo_id = d.id WHERE d.propietario_id = ' . intval($_SESSION['user_id']))->single();
                $promedioTemp = $rowTemp && $rowTemp['promedio'] !== null ? $rowTemp['promedio'] : 0;

                // Obtener dispositivos activos con información del propietario para el usuario actual
                $dispositivosActivos = $db->query('
                    SELECT d.*, u.nombre as propietario_nombre, m.nombre as mascota_nombre 
                    FROM dispositivos d 
                    LEFT JOIN usuarios u ON d.propietario_id = u.id 
                    LEFT JOIN mascotas m ON d.mascota_id = m.id 
                    WHERE d.propietario_id = ' . intval($_SESSION['user_id']) . ' 
                    AND d.estado = "activo"
                ')->resultSet();
            }

            // Obtener estadísticas
            $stats = [
                'mascotas' => $totalMascotas,
                'dispositivos' => $totalDispositivos,
                'dispositivos_activos' => count($dispositivosActivos),
                'alertas' => $this->alertaModel->getEstadisticas($_SESSION['user_id']),
                'total_sensores' => $totalSensores,
                'promedio_temperatura' => round($promedioTemp, 2)
            ];
            $this->logger->info("Estadísticas obtenidas: " . print_r($stats, true));

            // Obtener últimas alertas
            $ultimasAlertas = $this->alertaModel->getAlertasNoLeidas($_SESSION['user_id']);
            $this->logger->info("Últimas alertas obtenidas: " . count($ultimasAlertas));

            // Obtener actividad reciente
            $actividadReciente = $this->logModel->getActividadReciente($_SESSION['user_id']);
            $this->logger->info("Actividad reciente obtenida: " . count($actividadReciente));

            $title = 'Panel de Control';
            $this->logger->info("Renderizando vista");
            
            try {
                $content = $this->render('dashboard/index', [
                    'stats' => $stats,
                    'ultimasAlertas' => $ultimasAlertas,
                    'dispositivosActivos' => $dispositivosActivos,
                    'actividadReciente' => $actividadReciente
                ]);
                $this->logger->info("Vista renderizada correctamente");
                
                $this->logger->info("Cargando layout");
                $layoutFile = ROOT_PATH . '/views/layouts/main.php';
                $this->logger->debug("Layout file exists: " . file_exists($layoutFile));
                
                require_once $layoutFile;
                $this->logger->info("Layout cargado correctamente");
            } catch (Exception $e) {
                $this->logger->error("Error al renderizar vista/layout: " . $e->getMessage());
                $this->logger->error("Trace: " . $e->getTraceAsString());
                throw $e;
            }
        } catch (Exception $e) {
            $this->logger->error("Error en indexAction: " . $e->getMessage());
            $this->logger->error("Trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    public function getStatsAction() {
        if (!isset($_SESSION['user_id'])) {
            $this->logger->warning("Intento de acceso no autorizado a getStatsAction");
            $this->jsonResponse([
                'success' => false,
                'error' => 'Acceso denegado'
            ], 403);
        }

        try {
            $stats = [
                'mascotas' => $this->mascotaModel->count(['propietario_id' => $_SESSION['user_id']]),
                'dispositivos' => $this->dispositivoModel->count(['propietario_id' => $_SESSION['user_id']]),
                'dispositivos_activos' => $this->dispositivoModel->count([
                    'propietario_id' => $_SESSION['user_id'],
                    'estado' => 'activo'
                ]),
                'alertas' => $this->alertaModel->getEstadisticas($_SESSION['user_id'])
            ];

            $this->logger->info("Estadísticas obtenidas para usuario " . $_SESSION['user_id']);
            $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->logger->error("Error al obtener estadísticas: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => 'Error al obtener estadísticas'
            ], 500);
        }
    }
}
?> 