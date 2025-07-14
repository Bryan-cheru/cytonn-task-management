<?php
// Database configuration for Render.com deployment
class Database {
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $connection;

    public function __construct() {
        // Check if we're on Render.com (they provide DATABASE_URL)
        $database_url = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? getenv('DATABASE_URL') ?? null;
        
        if ($database_url) {
            // Parse Render.com database URL
            $db_url = parse_url($database_url);
            $this->host = $db_url['host'];
            $this->dbname = ltrim($db_url['path'], '/');
            $this->username = $db_url['user'];
            $this->password = $db_url['pass'];
            $port = isset($db_url['port']) ? $db_url['port'] : 5432;
            
            $dsn = "pgsql:host={$this->host};port={$port};dbname={$this->dbname};sslmode=require";
        } else {
            // Local development settings
            $this->host = 'localhost';
            $this->dbname = 'task_management';
            $this->username = 'root';
            $this->password = '';
            
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8";
        }

        try {
            $this->connection = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }

    public function query($sql) {
        return $this->connection->query($sql);
    }

    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    public function commit() {
        return $this->connection->commit();
    }

    public function rollBack() {
        return $this->connection->rollBack();
    }

    // Check if we're using PostgreSQL or MySQL
    public function isPostgreSQL() {
        return strpos($this->connection->getAttribute(PDO::ATTR_DRIVER_NAME), 'pgsql') !== false;
    }

    public function isMySQL() {
        return strpos($this->connection->getAttribute(PDO::ATTR_DRIVER_NAME), 'mysql') !== false;
    }
}
?>
