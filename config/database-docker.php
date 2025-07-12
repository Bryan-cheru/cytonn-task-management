<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $connection;
    private $isPostgreSQL = false;

    public function __construct() {
        // Multiple methods to check for DATABASE_URL environment variable
        $database_url = null;
        
        // Method 1: $_ENV superglobal
        if (isset($_ENV['DATABASE_URL'])) {
            $database_url = $_ENV['DATABASE_URL'];
        }
        // Method 2: getenv() function
        elseif (getenv('DATABASE_URL')) {
            $database_url = getenv('DATABASE_URL');
        }
        // Method 3: $_SERVER superglobal
        elseif (isset($_SERVER['DATABASE_URL'])) {
            $database_url = $_SERVER['DATABASE_URL'];
        }
        
        // Debug logging
        error_log("DATABASE_URL detection methods:");
        error_log("  \$_ENV: " . (isset($_ENV['DATABASE_URL']) ? "Found" : "Not found"));
        error_log("  getenv(): " . (getenv('DATABASE_URL') ? "Found" : "Not found"));
        error_log("  \$_SERVER: " . (isset($_SERVER['DATABASE_URL']) ? "Found" : "Not found"));
        error_log("  Final result: " . ($database_url ? "Found" : "Not found"));
        
        if ($database_url && strpos($database_url, 'postgres://') === 0) {
            // Production environment (Render.com with PostgreSQL)
            $url = parse_url($database_url);
            $this->host = $url['host'];
            $this->port = $url['port'] ?? 5432;
            $this->db_name = ltrim($url['path'], '/');
            $this->username = $url['user'];
            $this->password = $url['pass'];
            $this->isPostgreSQL = true;
            
            // Debug logging (without password)
            error_log("PostgreSQL config - Host: {$this->host}, Port: {$this->port}, DB: {$this->db_name}, User: {$this->username}");
        } else {
            // Local development environment (MySQL)
            $this->host = 'localhost';
            $this->db_name = 'cytonn_task_management';
            $this->username = 'root';
            $this->password = '';
            $this->port = 3306;
            $this->isPostgreSQL = false;
            
            // Debug logging
            error_log("MySQL config - Host: {$this->host}, Port: {$this->port}, DB: {$this->db_name}");
        }
        
        // Establish connection immediately in constructor
        $this->establishConnection();
    }

    private function establishConnection() {
        $this->connection = null;

        try {
            if ($this->isPostgreSQL) {
                // PostgreSQL for production (Render.com)
                $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name}";
                error_log("Attempting PostgreSQL connection with DSN: " . $dsn);
            } else {
                // MySQL for local development
                $dsn = "mysql:host={$this->host};dbname={$this->db_name}";
                error_log("Attempting MySQL connection with DSN: " . $dsn);
            }
            
            $this->connection = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 30
            ]);
            
            // Set timezone
            if ($this->isPostgreSQL) {
                $this->connection->exec("SET timezone = 'UTC'");
            } else {
                $this->connection->exec("SET time_zone = '+00:00'");
            }
            
            error_log("Database connection successful!");
            
        } catch(PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
            error_log("DSN used: " . $dsn);
            error_log("Username: " . $this->username);
            die("Database connection failed: " . $exception->getMessage());
        }
    }

    public function getConnection() {
        return $this->connection;
    }
    
    // Add methods to match the local database.php structure
    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }

    public function execute($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($params);
    }

    public function fetch($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function fetchAll($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    public function isPostgreSQL() {
        return $this->isPostgreSQL;
    }
    
    public function setupDatabase() {
        try {
            if ($this->isPostgreSQL) {
                // PostgreSQL setup for Render.com
                $schema = file_get_contents(__DIR__ . '/../database/postgres-schema.sql');
            } else {
                // MySQL setup for local development
                $schema = file_get_contents(__DIR__ . '/../database/cytonn_task_management.sql');
            }
            
            // Execute schema
            $this->connection->exec($schema);
            
            return true;
        } catch (Exception $e) {
            error_log("Database setup error: " . $e->getMessage());
            return false;
        }
    }

    public function testConnection() {
        try {
            if ($this->connection) {
                error_log("Database connection test successful");
                return true;
            }
        } catch (Exception $e) {
            error_log("Database connection test failed: " . $e->getMessage());
            return false;
        }
        return false;
    }
}
?>
