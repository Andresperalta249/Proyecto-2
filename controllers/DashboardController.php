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

            // Obtener estadísticas
            $stats = [
                'mascotas' => $this->mascotaModel->count(['usuario_id' => $_SESSION['user_id']]),
                'dispositivos' => $this->dispositivoModel->count(['usuario_id' => $_SESSION['user_id']]),
                'dispositivos_activos' => $this->dispositivoModel->count([
                    'usuario_id' => $_SESSION['user_id'],
                    'estado' => 'activo'
                ]),
                'alertas' => $this->alertaModel->getEstadisticas($_SESSION['user_id'])
            ];
            $this->logger->info("Estadísticas obtenidas: " . print_r($stats, true));

            // Obtener últimas alertas
            $ultimasAlertas = $this->alertaModel->getAlertasNoLeidas($_SESSION['user_id']);
            $this->logger->info("Últimas alertas obtenidas: " . count($ultimasAlertas));

            // Obtener dispositivos activos
            $dispositivosActivos = $this->dispositivoModel->getDispositivosActivos($_SESSION['user_id']);
            $this->logger->info("Dispositivos activos obtenidos: " . count($dispositivosActivos));

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
                'mascotas' => $this->mascotaModel->count(['usuario_id' => $_SESSION['user_id']]),
                'dispositivos' => $this->dispositivoModel->count(['usuario_id' => $_SESSION['user_id']]),
                'dispositivos_activos' => $this->dispositivoModel->count([
                    'usuario_id' => $_SESSION['user_id'],
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