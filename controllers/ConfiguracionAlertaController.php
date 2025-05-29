<?php
class ConfiguracionAlertaController extends Controller {
    private $configuracionModel;
    private $dispositivoModel;

    public function __construct() {
        parent::__construct();
        $this->configuracionModel = $this->loadModel('ConfiguracionAlerta');
        $this->dispositivoModel = $this->loadModel('Dispositivo');
    }

    public function indexAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $porPagina = 10;

        $dispositivos = $this->dispositivoModel->findAll();
        $configuraciones = $this->configuracionModel->getConfiguracionesActivas($pagina, $porPagina);
        $totalConfiguraciones = $this->configuracionModel->getTotalConfiguracionesActivas();
        $totalPaginas = ceil($totalConfiguraciones / $porPagina);

        $title = 'Configuración de Alertas';
        $menuActivo = 'configuracion_alertas';
        $content = $this->render('configuracion_alertas/index', [
            'dispositivos' => $dispositivos,
            'configuraciones' => $configuraciones,
            'pagina' => $pagina,
            'totalPaginas' => $totalPaginas
        ]);
        require_once 'views/layouts/main.php';
    }

    public function crearAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'dispositivo_id' => $_POST['dispositivo_id'],
                    'tipo_alerta' => $_POST['tipo_alerta'],
                    'valor_minimo' => $_POST['valor_minimo'],
                    'valor_maximo' => $_POST['valor_maximo'],
                    'prioridad' => $_POST['prioridad'],
                    'estado' => true
                ];

                if ($this->configuracionModel->crearConfiguracion($data)) {
                    $_SESSION['success'] = 'Configuración creada exitosamente';
                } else {
                    $_SESSION['error'] = 'Error al crear la configuración';
                }
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
            redirect('configuracion-alerta');
        }
    }

    public function actualizarAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id = $_POST['id'];
                $data = [
                    'valor_minimo' => $_POST['valor_minimo'],
                    'valor_maximo' => $_POST['valor_maximo'],
                    'prioridad' => $_POST['prioridad'],
                    'estado' => isset($_POST['estado']) ? true : false
                ];

                if ($this->configuracionModel->actualizarConfiguracion($id, $data)) {
                    $_SESSION['success'] = 'Configuración actualizada exitosamente';
                } else {
                    $_SESSION['error'] = 'Error al actualizar la configuración';
                }
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
            redirect('configuracion-alerta');
        }
    }

    public function eliminarAction($id) {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        try {
            if ($this->configuracionModel->eliminarConfiguracion($id)) {
                $_SESSION['success'] = 'Configuración eliminada exitosamente';
            } else {
                $_SESSION['error'] = 'Error al eliminar la configuración';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        redirect('configuracion-alerta');
    }

    public function validarAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tipo_alerta = $_POST['tipo_alerta'];
            $valor = $_POST['valor'];
            $dispositivo_id = isset($_POST['dispositivo_id']) ? $_POST['dispositivo_id'] : null;

            try {
                $resultado = $this->configuracionModel->validarValores($tipo_alerta, $valor, $dispositivo_id);
                echo json_encode(['success' => true, 'valido' => $resultado]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
        }
    }

    public function actualizarGeneralAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $tipos = ['temperatura', 'ritmo_cardiaco', 'bateria', 'inactividad'];
                $dispositivos = $this->dispositivoModel->findAll();
                
                foreach ($dispositivos as $dispositivo) {
                    foreach ($tipos as $tipo) {
                        $data = [
                            'dispositivo_id' => $dispositivo['id'],
                            'tipo_alerta' => $tipo,
                            'valor_minimo' => $_POST[$tipo]['min'],
                            'valor_maximo' => $_POST[$tipo]['max'],
                            'prioridad' => $_POST[$tipo]['prioridad'],
                            'estado' => true
                        ];

                        $configExistente = $this->configuracionModel->findOne([
                            'dispositivo_id' => $dispositivo['id'],
                            'tipo_alerta' => $tipo
                        ]);

                        if ($configExistente) {
                            $this->configuracionModel->actualizarConfiguracion($configExistente['id'], $data);
                        } else {
                            $this->configuracionModel->crearConfiguracion($data);
                        }
                    }
                }

                $mensaje = 'Configuración de alertas actualizada exitosamente';
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    echo json_encode(['success' => true, 'message' => $mensaje]);
                    exit;
                } else {
                    $_SESSION['success'] = $mensaje;
                    redirect('alertas');
                }
            } catch (Exception $e) {
                $mensaje = $e->getMessage();
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    echo json_encode(['success' => false, 'error' => $mensaje]);
                    exit;
                } else {
                    $_SESSION['error'] = $mensaje;
                    redirect('alertas');
                }
            }
        }
    }
} 