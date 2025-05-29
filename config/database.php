<?php
class Database {
    private static $instance = null;
    private $conn;
    private $stmt;
    private $cache = [];

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                PDO::ATTR_PERSISTENT => true, // Conexiones persistentes
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true, // Consultas en buffer
            ];
            
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
            error_log("Conexión a la base de datos establecida correctamente");
        } catch (PDOException $e) {
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function query($sql) {
        try {
            $this->stmt = $this->conn->prepare($sql);
            return $this;
        } catch (PDOException $e) {
            error_log("Error en la consulta SQL: " . $e->getMessage());
            throw new Exception("Error en la consulta SQL");
        }
    }

    public function bind($param, $value, $type = null) {
        try {
            if (is_null($type)) {
                switch (true) {
                    case is_int($value):
                        $type = PDO::PARAM_INT;
                        break;
                    case is_bool($value):
                        $type = PDO::PARAM_BOOL;
                        break;
                    case is_null($value):
                        $type = PDO::PARAM_NULL;
                        break;
                    default:
                        $type = PDO::PARAM_STR;
                }
            }
            $this->stmt->bindValue($param, $value, $type);
            return $this;
        } catch (PDOException $e) {
            error_log("Error al vincular parámetros: " . $e->getMessage());
            throw new Exception("Error al vincular parámetros");
        }
    }

    public function execute() {
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al ejecutar la consulta: " . $e->getMessage());
            throw new Exception("Error al ejecutar la consulta");
        }
    }

    public function resultSet() {
        $cacheKey = md5($this->stmt->queryString);
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        $this->execute();
        $results = $this->stmt->fetchAll();
        $this->cache[$cacheKey] = $results;
        return $results;
    }

    public function single() {
        $cacheKey = md5($this->stmt->queryString);
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        $this->execute();
        $result = $this->stmt->fetch();
        $this->cache[$cacheKey] = $result;
        return $result;
    }

    public function rowCount() {
        return $this->stmt->rowCount();
    }

    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }

    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    public function commit() {
        return $this->conn->commit();
    }

    public function rollBack() {
        return $this->conn->rollBack();
    }

    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }

    // Método para limpiar la caché
    public function clearCache() {
        $this->cache = [];
    }

    // Método para establecer el tiempo de vida de la caché
    public function setCacheTTL($ttl) {
        $this->cacheTTL = $ttl;
    }

    // Método para optimizar consultas
    public function optimizeQuery($sql) {
        // Eliminar espacios innecesarios
        $sql = preg_replace('/\s+/', ' ', trim($sql));
        
        // Convertir a minúsculas para mejor caché
        $sql = strtolower($sql);
        
        return $sql;
    }

    // Prevenir la clonación del objeto
    private function __clone() {}

    // Prevenir la deserialización del objeto
    public function __wakeup() {
        throw new Exception("No se puede deserializar una instancia de singleton");
    }
}
?> 