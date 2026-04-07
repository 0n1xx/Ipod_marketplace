<?php
class Database {
    private $host     = DB_HOST;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $database = DB_NAME;
    private $port     = DB_PORT;
    public $connection = null;

    public function __construct()
    {
        try {
            $this->connection = new PDO(
                "mysql:host=$this->host;port=$this->port;dbname=$this->database;charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection error.");
        }
    }
}
?>