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
    // Load credentials from environment variables or .env file
    // NEVER hardcode credentials in version-controlled files
    
    // Try to load from environment variables first
    $servername = getenv('SUPABASE_HOST') ?: ($_ENV['SUPABASE_HOST'] ?? '');
    $username = getenv('SUPABASE_USER') ?: ($_ENV['SUPABASE_USER'] ?? 'postgres');
    $password = getenv('SUPABASE_PASSWORD') ?: ($_ENV['SUPABASE_PASSWORD'] ?? '');
    $database = getenv('SUPABASE_DATABASE') ?: ($_ENV['SUPABASE_DATABASE'] ?? 'postgres');
    $port = getenv('SUPABASE_PORT') ?: ($_ENV['SUPABASE_PORT'] ?? '5432');
    
    // Fallback: Load from .env.local file if it exists (not version controlled)
    $envFile = __DIR__ . '/.env.local';
    if (empty($servername) && file_exists($envFile)) {
        $envVars = parse_ini_file($envFile);
        $servername = $envVars['SUPABASE_HOST'] ?? '';
        $username = $envVars['SUPABASE_USER'] ?? 'postgres';
        $password = $envVars['SUPABASE_PASSWORD'] ?? '';
        $database = $envVars['SUPABASE_DATABASE'] ?? 'postgres';
        $port = $envVars['SUPABASE_PORT'] ?? '5432';
    }
    
    if (empty($servername) || empty($password)) {
        die("Supabase credentials not configured. Please set environment variables or create .env.local file.");
    }
    
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
        private $rowCount = null;
        private $fetchIndex = 0;  // Track current position for iteration
        
        public function __construct($stmt, $error = null) {
            $this->stmt = $stmt;
            $this->error = $error;
        }
        
        // Ensure rows are loaded into cache
        private function ensureRowsLoaded() {
            if ($this->rows === null && $this->stmt) {
                $this->rows = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
                $this->rowCount = count($this->rows);
            }
        }
        
        public function fetch_assoc() {
            if ($this->error) throw new Exception($this->error);
            
            // If rows were already fetched (for count or fetch_all), iterate through cache
            if ($this->rows !== null) {
                if ($this->fetchIndex < count($this->rows)) {
                    return $this->rows[$this->fetchIndex++];
                }
                return false;
            }
            
            // Otherwise fetch directly from statement
            return $this->stmt ? $this->stmt->fetch(PDO::FETCH_ASSOC) : false;
        }
        
        public function fetch_all($mode = null) {
            if ($this->error) throw new Exception($this->error);
            $this->ensureRowsLoaded();
            return $this->rows ?: [];
        }
        
        public function num_rows() {
            // PDO rowCount() doesn't work reliably for SELECT statements
            // We need to fetch all rows and count them
            if ($this->rowCount === null) {
                $this->ensureRowsLoaded();
                if ($this->rows === null) {
                    $this->rowCount = 0;
                }
            }
            return $this->rowCount;
        }
        
        public function __get($name) {
            return $name === 'num_rows' ? $this->num_rows() : null;
        }
        
        // Reset iteration position (useful for re-iterating)
        public function reset() {
            $this->fetchIndex = 0;
        }
    }
    
    if (!function_exists('mysqli_num_rows')) {
        function mysqli_num_rows($result) {
            return $result instanceof PDOResultWrapper ? $result->num_rows() : 0;
        }
    }
}
?>
