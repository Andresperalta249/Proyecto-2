<?php
class Controller {
    protected $db;
    protected $view;

    public function __construct() {
        // Cargar la configuración primero
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', dirname(__DIR__));
        }
        
        // Cargar la configuración
        if (!defined('DB_HOST')) {
            require_once ROOT_PATH . '/config/config.php';
        }
        
        // Cargar la clase Database
        require_once ROOT_PATH . '/core/Database.php';
        
        // Inicializar la base de datos
        try {
            $this->db = Database::getInstance();
        } catch (Exception $e) {
            error_log("Error al inicializar la base de datos: " . $e->getMessage());
            throw new Exception("Error al inicializar la base de datos");
        }

        // Inicializar la vista
        require_once ROOT_PATH . '/core/View.php';
        $this->view = new View();
    }

    protected function loadModel($model) {
        $modelFile = ROOT_PATH . '/models/' . $model . '.php';
        if (!file_exists($modelFile)) {
            error_log("Modelo no encontrado: " . $modelFile);
            throw new Exception("Modelo no encontrado: " . $model);
        }
        require_once $modelFile;
        return new $model();
    }

    protected function render($view, $data = []) {
        $viewFile = ROOT_PATH . '/views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            error_log("Vista no encontrada: " . $viewFile);
            throw new Exception("Vista no encontrada: " . $view);
        }
        extract($data);
        ob_start();
        require_once $viewFile;
        return ob_get_clean();
    }

    protected function redirect($url) {
        header('Location: ' . BASE_URL . $url);
        exit;
    }
}
?> 