<?php
class Model {
    protected $db;
    protected $table;
    public $lastError;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en consulta SQL: " . $e->getMessage());
            return false;
        }
    }

    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $result = $this->query($sql, [':id' => $id]);
        return $result ? $result[0] : false;
    }

    public function findAll($conditions = [], $orderBy = '') {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $paramKey = ':' . $key;
                $where[] = "$key = $paramKey";
                $params[$paramKey] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        
        return $this->query($sql, $params);
    }

    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $result = $this->query($sql, $params);
        return $result ? $result[0]['total'] : 0;
    }

    public function create($data) {
        try {
            $fields = array_keys($data);
            $values = array_values($data);
            $placeholders = str_repeat('?,', count($fields) - 1) . '?';
            
            $sql = "INSERT INTO {$this->table} (" . implode(',', $fields) . ") 
                    VALUES ($placeholders)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error al crear registro: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data) {
        try {
            $fields = array_keys($data);
            $values = array_values($data);
            $set = implode('=?,', $fields) . '=?';
            
            $sql = "UPDATE {$this->table} SET $set WHERE id = ?";
            $values[] = $id;
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error al actualizar registro: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error al eliminar registro: " . $e->getMessage());
            return false;
        }
    }
}
?> 