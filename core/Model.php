<?php

class Model {
    protected $db;
    protected $table;
    protected $lastError;

    public function __construct() {
        try {
            $this->db = Database::getInstance();
        } catch (Exception $e) {
            error_log("Error al inicializar Model: " . $e->getMessage());
            throw $e;
        }
    }

    protected function query($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en la consulta SQL: " . $e->getMessage() . "\nSQL: " . $sql . "\nParams: " . print_r($params, true));
            return false;
        }
    }

    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $result = $this->query($sql, [':id' => $id]);
        return $result ? $result[0] : null;
    }

    public function findAll($conditions = []) {
        $sql = "SELECT * FROM {$this->table}";
        
        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $key => $value) {
                $whereClauses[] = "$key = :$key";
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }
        
        return $this->query($sql, $conditions);
    }

    public function create($data) {
        $columns = implode(', ', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($values)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error al crear registro: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data) {
        $setClauses = [];
        foreach ($data as $key => $value) {
            $setClauses[] = "$key = :$key";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . " WHERE id = :id";
        $data['id'] = $id;
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            error_log("Error al actualizar registro: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error al eliminar registro: " . $e->getMessage());
            return false;
        }
    }

    public function getLastError() {
        return $this->lastError;
    }
} 