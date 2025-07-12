<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;
    private $isPostgreSQL = false;

    public function __construct() {
        // Force check for DATABASE_URL environment variable
        $database_url = $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL') ?? null;
        
        // Debug logging
        error_log("DATABASE_URL check: " . ($database_url ? "Found" : "Not found"));
        
        if ($database_url && strpos($database_url, 'postgres://') === 0) {
            // Production environment (Render.com with PostgreSQL)
            $url = parse_url($database_url);
            $this->host = $url['host'];
            $this->port = $url['port'] ?? 5432;
            $this->db_name = ltrim($url['path'], '/');
            $this->username = $url['user'];
            $this->password = $url['pass'];
            $this->isPostgreSQL = true;
            
            // Debug logging
            error_log("PostgreSQL config - Host: {$this->host}, Port: {$this->port}, DB: {$this->db_name}");
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
    }

    public function getConnection() {
        $this->conn = null;

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
            
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 30
            ]);
            
            // Set timezone
            if ($this->isPostgreSQL) {
                $this->conn->exec("SET timezone = 'UTC'");
            } else {
                $this->conn->exec("SET time_zone = '+00:00'");
            }
            
            error_log("Database connection successful!");
            
        } catch(PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
            error_log("DSN used: " . $dsn);
            error_log("Username: " . $this->username);
            throw new Exception("Database connection failed: " . $exception->getMessage());
        }

        return $this->conn;
    }
    
    public function isPostgreSQL() {
        return $this->isPostgreSQL;
    }
    
    public function setupDatabase() {
        try {
            $db = $this->getConnection();
            
            if ($this->isPostgreSQL) {
                // PostgreSQL setup for Render.com
                $schema = file_get_contents(__DIR__ . '/../database/postgres-schema.sql');
            } else {
                // MySQL setup for local development
                $schema = file_get_contents(__DIR__ . '/../database/cytonn_task_management.sql');
            }
            
            // Execute schema
            $db->exec($schema);
            
            return true;
        } catch (Exception $e) {
            error_log("Database setup error: " . $e->getMessage());
            return false;
        }
    }

    public function testConnection() {
        try {
            $conn = $this->getConnection();
            if ($conn) {
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
