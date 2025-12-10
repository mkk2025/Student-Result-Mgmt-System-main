<?php
// ============================================
// DATABASE CONFIGURATION
// ============================================
// Switch between LOCAL MySQL and SUPABASE PostgreSQL
// Set USE_SUPABASE to true when Supabase is ready

define('USE_SUPABASE', false); // Set to true to use Supabase

if (USE_SUPABASE) {
    // ============================================
    // SUPABASE POSTGRESQL CONNECTION
    // ============================================
    $servername = "db.eusitxkhvigerzeslsym.supabase.co";
    $username = "postgres";
    $password = "imatt223759";
    $database = "postgres";
    $port = "5432";
    
    try {
        $dsn = "pgsql:host=$servername;port=$port;dbname=$database";
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // Create MySQLi-compatible wrapper
        $conn = new MySQLiWrapper($pdo);
    } catch (PDOException $e) {
        die("Supabase connection failed: " . $e->getMessage());
    }
} else {
    // ============================================
    // LOCAL MYSQL CONNECTION (Default)
    // ============================================
    $servername = "127.0.0.1";
    $username = "srms_user";
    $password = "srms_pass123";
    $database = "srms";
    
    $conn = mysqli_connect($servername, $username, $password, $database);
    
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
}

// ============================================
// WRAPPER CLASSES FOR SUPABASE COMPATIBILITY
// ============================================
// Only load when using Supabase

if (USE_SUPABASE) {
    class MySQLiWrapper {
        private $pdo;
        
        public function __construct($pdo) {
            $this->pdo = $pdo;
        }
        
        public function prepare($query) {
            $query = str_replace('`', '"', $query);
            return new PDOStatementWrapper($this->pdo->prepare($query), $this);
        }
        
        public function query($query) {
            $query = str_replace('`', '"', $query);
            try {
                $stmt = $this->pdo->prepare($query);
                $stmt->execute();
                return new PDOResultWrapper($stmt);
            } catch (PDOException $e) {
                return new PDOResultWrapper(null, $e->getMessage());
            }
        }
        
        public function begin_transaction() { return $this->pdo->beginTransaction(); }
        public function commit() { return $this->pdo->commit(); }
        public function rollback() { return $this->pdo->rollBack(); }
        public function close() { $this->pdo = null; }
    }
    
    class PDOStatementWrapper {
        private $stmt;
        private $mysqli;
        private $params = [];
        private $resultVars = [];
        
        public function __construct($stmt, $mysqli) {
            $this->stmt = $stmt;
            $this->mysqli = $mysqli;
        }
        
        public function bind_param($types, ...$values) {
            $this->params = $values;
            return true;
        }
        
        public function execute() {
            return $this->stmt ? $this->stmt->execute($this->params) : false;
        }
        
        public function get_result() {
            return $this->stmt ? new PDOResultWrapper($this->stmt) : new PDOResultWrapper(null);
        }
        
        public function close() { $this->stmt = null; }
        
        public function bind_result(&...$vars) {
            $this->resultVars = $vars;
            return true;
        }
        
        public function fetch() {
            if ($this->stmt && $this->resultVars) {
                $row = $this->stmt->fetch(PDO::FETCH_NUM);
                if ($row) {
                    foreach ($this->resultVars as $i => &$var) {
                        $var = $row[$i] ?? null;
                    }
                    return true;
                }
            }
            return false;
        }
    }
    
    class PDOResultWrapper {
        private $stmt;
        private $rows = null;
        private $error = null;
        
        public function __construct($stmt, $error = null) {
            $this->stmt = $stmt;
            $this->error = $error;
        }
        
        public function fetch_assoc() {
            if ($this->error) throw new Exception($this->error);
            return $this->stmt ? $this->stmt->fetch(PDO::FETCH_ASSOC) : false;
        }
        
        public function fetch_all($mode = null) {
            if ($this->error) throw new Exception($this->error);
            if ($this->rows === null && $this->stmt) {
                $this->rows = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            return $this->rows ?: [];
        }
        
        public function num_rows() {
            return $this->stmt ? $this->stmt->rowCount() : 0;
        }
        
        public function __get($name) {
            return $name === 'num_rows' ? $this->num_rows() : null;
        }
    }
    
    if (!function_exists('mysqli_num_rows')) {
        function mysqli_num_rows($result) {
            return $result instanceof PDOResultWrapper ? $result->num_rows() : 0;
        }
    }
}
?>
