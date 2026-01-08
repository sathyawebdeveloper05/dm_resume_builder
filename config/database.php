<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;
    private $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_PERSISTENT => true,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ];

    public function __construct() {
        // Load configuration
        require_once __DIR__ . '/config.php';
        
        $this->host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password, $this->options);
            
            // Test connection
            $this->conn->query("SELECT 1");
            
        } catch(PDOException $exception) {
            $this->logError("Connection error: " . $exception->getMessage());
            
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                die("Database connection failed: " . $exception->getMessage());
            } else {
                die("Database connection failed. Please try again later.");
            }
        }
        
        return $this->conn;
    }

    public function beginTransaction() {
        if ($this->conn) {
            return $this->conn->beginTransaction();
        }
        return false;
    }

    public function commit() {
        if ($this->conn) {
            return $this->conn->commit();
        }
        return false;
    }

    public function rollback() {
        if ($this->conn) {
            return $this->conn->rollback();
        }
        return false;
    }

    public function lastInsertId() {
        if ($this->conn) {
            return $this->conn->lastInsertId();
        }
        return 0;
    }

    public function prepare($sql) {
        if ($this->conn) {
            return $this->conn->prepare($sql);
        }
        throw new PDOException("Database connection not established");
    }

    public function query($sql) {
        if ($this->conn) {
            return $this->conn->query($sql);
        }
        throw new PDOException("Database connection not established");
    }

    private function logError($message) {
        $log_dir = __DIR__ . '/../../logs/';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $log_file = $log_dir . 'database_errors.log';
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[$timestamp] $message" . PHP_EOL;
        
        file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
    }

    // Helper methods for common operations
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        return $stmt->execute();
    }

    public function update($table, $data, $where, $params = []) {
        $set = '';
        foreach ($data as $key => $value) {
            $set .= "$key = :set_$key, ";
        }
        $set = rtrim($set, ', ');
        
        $sql = "UPDATE $table SET $set WHERE $where";
        $stmt = $this->prepare($sql);
        
        // Bind set values
        foreach ($data as $key => $value) {
            $stmt->bindValue(":set_$key", $value);
        }
        
        // Bind where parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        return $stmt->execute();
    }

    public function select($table, $columns = '*', $where = null, $params = [], $limit = null, $offset = null) {
        $sql = "SELECT $columns FROM $table";
        
        if ($where) {
            $sql .= " WHERE $where";
        }
        
        if ($limit) {
            $sql .= " LIMIT $limit";
            if ($offset) {
                $sql .= " OFFSET $offset";
            }
        }
        
        $stmt = $this->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $this->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        return $stmt->execute();
    }

    public function count($table, $where = null, $params = []) {
        $sql = "SELECT COUNT(*) as count FROM $table";
        
        if ($where) {
            $sql .= " WHERE $where";
        }
        
        $stmt = $this->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['count'] ?? 0;
    }

    public function exists($table, $where, $params = []) {
        return $this->count($table, $where, $params) > 0;
    }

    public function closeConnection() {
        $this->conn = null;
    }

    public function __destruct() {
        $this->closeConnection();
    }
}
// NO CLOSING TAG - REMOVE THE ?> 