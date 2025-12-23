<?php
// includes/database.php

class Database {
    private $host = 'localhost';
    private $user = 'root';
    private $pass = '';
    private $dbname = 'panel_payment';
    private $conn;
    private $stmt;
    
    public function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
        
        try {
            $this->conn = new PDO($dsn, $this->user, $this->pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public function query($sql) {
        $this->stmt = $this->conn->prepare($sql);
    }
    
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value): $type = PDO::PARAM_INT; break;
                case is_bool($value): $type = PDO::PARAM_BOOL; break;
                case is_null($value): $type = PDO::PARAM_NULL; break;
                default: $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }
    
    public function execute() {
        return $this->stmt->execute();
    }
    
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }
    
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }
    
    public function rowCount() {
        return $this->stmt->rowCount();
    }
    
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
}