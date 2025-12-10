<?php
// ============================================
// SUPABASE POSTGRESQL CONFIGURATION
// ============================================
// Replace these values with your Supabase credentials

$servername = "YOUR_SUPABASE_HOST"; // e.g., db.xxxxx.supabase.co
$username = "postgres"; // Usually 'postgres'
$password = "YOUR_SUPABASE_PASSWORD"; // From Supabase dashboard
$database = "postgres"; // Usually 'postgres'
$port = "5432"; // Usually 5432

// PDO Connection (works with PostgreSQL)
try {
    $dsn = "pgsql:host=$servername;port=$port;dbname=$database";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Create a MySQLi-compatible wrapper using PDO
    // This allows existing code to work with minimal changes
    class MySQLiWrapper {
        private $pdo;
        private $lastResult;
        
        public function __construct($pdo) {
            $this->pdo = $pdo;
        }
        
        public function prepare($query) {
            // Convert MySQL syntax to PostgreSQL
            $query = str_replace('`', '"', $query); // Backticks to double quotes
            $query = preg_replace('/LIMIT\s+(\d+)\s*,\s*(\d+)/i', 'LIMIT $2 OFFSET $1', $query); // LIMIT offset, count
            return new PDOStatementWrapper($this->pdo->prepare($query), $this);
        }
        
        public function query($query) {
            $query = str_replace('`', '"', $query);
            $query = preg_replace('/LIMIT\s+(\d+)\s*,\s*(\d+)/i', 'LIMIT $2 OFFSET $1', $query);
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            $this->lastResult = $stmt;
            return new PDOResultWrapper($stmt);
        }
        
        public function begin_transaction() {
            return $this->pdo->beginTransaction();
        }
        
        public function commit() {
            return $this->pdo->commit();
        }
        
        public function rollback() {
            return $this->pdo->rollBack();
        }
        
        public function close() {
            $this->pdo = null;
        }
        
        public function getLastResult() {
            return $this->lastResult;
        }
    }
    
    class PDOStatementWrapper {
        private $stmt;
        private $mysqli;
        private $params = [];
        
        public function __construct($stmt, $mysqli) {
            $this->stmt = $stmt;
            $this->mysqli = $mysqli;
        }
        
        public function bind_param($types, ...$values) {
            $this->params = $values;
            return true;
        }
        
        public function execute() {
            return $this->stmt->execute($this->params);
        }
        
        public function get_result() {
            $this->mysqli->lastResult = $this->stmt;
            return new PDOResultWrapper($this->stmt);
        }
        
        public function close() {
            $this->stmt = null;
        }
    }
    
    class PDOResultWrapper {
        private $stmt;
        private $rows = null;
        
        public function __construct($stmt) {
            $this->stmt = $stmt;
        }
        
        public function fetch_assoc() {
            return $this->stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        public function fetch_all($mode = null) {
            if ($this->rows === null) {
                $this->rows = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            return $this->rows;
        }
        
        public function num_rows() {
            return $this->stmt->rowCount();
        }
        
        public function __get($name) {
            if ($name === 'num_rows') {
                return $this->num_rows();
            }
            return null;
        }
    }
    
    // Create wrapper instance
    $conn = new MySQLiWrapper($conn);
    
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

