<?php
class Controller {
    protected $db;
    protected $view;
    protected $model;

    public function __construct() {
        require_once 'config/database.php';
        $database = new Database();
        $this->db = $database->getConnection();
    }

    protected function loadModel($model) {
        require_once 'models/' . $model . '.php';
        return new $model($this->db);
    }

    protected function render($view, $data = []) {
        extract($data);
        require_once 'views/' . $view . '.php';
    }

    protected function jsonResponse($data, $status = 200) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    protected function validateRequest($required = []) {
        // Si hay datos en $_POST, úsalos
        $data = $_POST;
        // Si no hay datos en $_POST, intenta obtenerlos del cuerpo JSON
        if (empty($data)) {
            $data = json_decode(file_get_contents('php://input'), true);
        }
        if (!$data) {
            $this->jsonResponse(['error' => 'Datos inválidos'], 400);
        }

        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $this->jsonResponse(['error' => "El campo {$field} es requerido"], 400);
            }
        }

        return $data;
    }

    protected function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(strip_tags($data));
    }
}
?> 