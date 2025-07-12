<?php
// Force PostgreSQL connection for debugging
class DatabaseTest {
    private $conn;
    
    public function __construct() {
        $database_url = getenv('DATABASE_URL');
        
        if (!$database_url) {
            throw new Exception("DATABASE_URL environment variable not found");
        }
        
        $url = parse_url($database_url);
        
        $host = $url['host'];
        $port = $url['port'] ?? 5432;
        $dbname = ltrim($url['path'], '/');
        $username = $url['user'];
        $password = $url['pass'];
        
        // Force PostgreSQL DSN
        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
        
        echo "Connection details:<br>";
        echo "Host: {$host}<br>";
        echo "Port: {$port}<br>";
        echo "Database: {$dbname}<br>";
        echo "Username: {$username}<br>";
        echo "DSN: {$dsn}<br><br>";
        
        try {
            $this->conn = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 30
            ]);
            
            echo "✅ PostgreSQL connection successful!<br>";
            
            // Test query
            $stmt = $this->conn->query("SELECT current_database(), version()");
            $result = $stmt->fetch();
            echo "Connected to database: " . $result['current_database'] . "<br>";
            echo "PostgreSQL version: " . $result['version'] . "<br>";
            
        } catch (PDOException $e) {
            echo "❌ Connection failed: " . $e->getMessage() . "<br>";
            throw $e;
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
}
?>
