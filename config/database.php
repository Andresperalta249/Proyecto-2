<?php
class Database {
    private $host = "localhost";
    private $db_name = "iot_pets";
    private $username = "root";
    private $password = "";
    private $conn;
    public $lastError = null;

    public function getConnection() {
        $this->conn = null;
        $this->lastError = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            return $this->conn;
        } catch(PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            return false;
        }
    }
}
?> 