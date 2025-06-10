<?php
require_once 'controllers/Controller.php';
require_once 'models/Configuracion.php';
require_once 'models/Log.php';

class ConfiguracionController extends Controller {
    private $configuracionModel;
    private $logModel;

    public function __construct() {
        parent::__construct();
        $this->configuracionModel = new Configuracion();
        $this->logModel = new Log();
    }

    public function indexAction() {
        if (!$this->isAuthenticated()) {
            $this->redirect('auth/login');
        }

        $configuracion = $this->configuracionModel->getConfiguracion();
        $GLOBALS['content'] = $this->render('configuracion/index', ['configuracion' => $configuracion]);
        $GLOBALS['title'] = 'Configuración del Sistema';
        $GLOBALS['menuActivo'] = 'configuracion';
        require_once 'views/layouts/main.php';
    }

    public function updateAction() {
        if (!$this->isAuthenticated()) {
            $this->redirect('auth/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre_sistema' => $_POST['nombre_sistema'],
                'email_contacto' => $_POST['email_contacto'],
                'telefono_contacto' => $_POST['telefono_contacto'],
                'direccion' => $_POST['direccion'],
                'tiempo_actualizacion' => $_POST['tiempo_actualizacion'],
                'dias_retener_logs' => $_POST['dias_retener_logs'],
                'notificaciones_email' => isset($_POST['notificaciones_email']) ? 1 : 0,
                'notificaciones_push' => isset($_POST['notificaciones_push']) ? 1 : 0,
                'tema_oscuro' => isset($_POST['tema_oscuro']) ? 1 : 0
            ];

            if ($this->configuracionModel->updateConfiguracion($data)) {
                $this->logModel->crearLog($_SESSION['usuario_id'], 'Actualización de configuración del sistema');
                $this->setFlashMessage('Configuración actualizada correctamente', 'success');
            } else {
                $this->setFlashMessage('Error al actualizar la configuración', 'danger');
            }
        }

        $this->redirect('configuracion');
    }

    public function limpiarLogsAction() {
        if (!$this->isAuthenticated()) {
            $this->redirect('auth/login');
        }

        $dias = $_POST['dias'] ?? 30;
        if ($this->logModel->limpiarLogsAntiguos($dias)) {
            $this->logModel->crearLog($_SESSION['usuario_id'], 'Limpieza de logs antiguos');
            $this->setFlashMessage('Logs limpiados correctamente', 'success');
        } else {
            $this->setFlashMessage('Error al limpiar los logs', 'danger');
        }

        $this->redirect('configuracion');
    }

    public function backupAction() {
        if (!$this->isAuthenticated()) {
            $this->redirect('auth/login');
        }

        $backupFile = $this->configuracionModel->generarBackup();
        if ($backupFile) {
            $this->logModel->crearLog($_SESSION['usuario_id'], 'Generación de backup del sistema');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($backupFile) . '"');
            header('Content-Length: ' . filesize($backupFile));
            readfile($backupFile);
            unlink($backupFile);
            exit;
        } else {
            $this->setFlashMessage('Error al generar el backup', 'danger');
            $this->redirect('configuracion');
        }
    }

    public function restoreAction() {
        if (!$this->isAuthenticated()) {
            $this->redirect('auth/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['backup_file'])) {
            $file = $_FILES['backup_file'];
            if ($file['error'] === UPLOAD_ERR_OK) {
                if ($this->configuracionModel->restaurarBackup($file['tmp_name'])) {
                    $this->logModel->crearLog($_SESSION['usuario_id'], 'Restauración de backup del sistema');
                    $this->setFlashMessage('Backup restaurado correctamente', 'success');
                } else {
                    $this->setFlashMessage('Error al restaurar el backup', 'danger');
                }
            } else {
                $this->setFlashMessage('Error al subir el archivo de backup', 'danger');
            }
        }

        $this->redirect('configuracion');
    }
} 