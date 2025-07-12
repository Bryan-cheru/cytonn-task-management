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
        // Parse DATABASE_URL environment variable (for Render.com)
        $database_url = getenv('DATABASE_URL');
        
        if ($database_url) {
            // Production environment (Render.com with PostgreSQL)
            $url = parse_url($database_url);
            $this->host = $url['host'];
            $this->port = $url['port'] ?? 5432;
            $this->db_name = ltrim($url['path'], '/');
            $this->username = $url['user'];
            $this->password = $url['pass'];
            $this->isPostgreSQL = true;
        } else {
            // Local development environment (MySQL)
            $this->host = 'localhost';
            $this->db_name = 'cytonn_task_management';
            $this->username = 'root';
            $this->password = '';
            $this->port = 3306;
            $this->isPostgreSQL = false;
        }
    }

    public function getConnection() {
        $this->conn = null;

        try {
            if ($this->isPostgreSQL) {
                // PostgreSQL for production (Render.com)
                $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name}";
            } else {
                // MySQL for local development
                $dsn = "mysql:host={$this->host};dbname={$this->db_name}";
            }
            
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Set timezone
            if ($this->isPostgreSQL) {
                $this->conn->exec("SET timezone = 'UTC'");
            } else {
                $this->conn->exec("SET time_zone = '+00:00'");
            }
            
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
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
}
?>
