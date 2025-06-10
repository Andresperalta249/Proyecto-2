<?php
require_once __DIR__ . '/../models/ConfiguracionAlerta.php';

class ConfiguracionAlertaController {
    private $modelo;

    public function __construct() {
        $this->modelo = new ConfiguracionAlerta();
    }

    public function index() {
        // Cargar la configuración de alertas desde la base de datos
        $config_alertas = $this->modelo->obtenerConfiguracionGeneral();
        // Cargar la vista de alertas pasando la configuración
        require_once __DIR__ . '/../views/alertas/index.php';
    }

    public function actualizarGeneral() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [];
            foreach ($_POST as $grupo => $sensores) {
                if (!is_array($sensores)) continue;
                $datos[$grupo] = $sensores;
            }
            $resultado = $this->modelo->guardarConfiguracionGeneral($datos);
            if ($resultado) {
                $_SESSION['success'] = 'Configuración de alertas actualizada correctamente.';
            } else {
                $_SESSION['error'] = 'Error al actualizar la configuración de alertas.';
            }
        }
        header('Location: /proyecto-2/alertas');
        exit;
    }
} 