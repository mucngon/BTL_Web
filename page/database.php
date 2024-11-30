<?php
class Database {
    private $conn;
    
    public function __construct($config) {
        try {
            $this->conn = new PDO(
                "mysql:host={$config['host']};dbname={$config['dbname']}", 
                $config['username'], 
                $config['password']
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Lỗi kết nối: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
}