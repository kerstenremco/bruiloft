<?php
    class Database {
        private $host = 'localhost';
        private $db = 'bruiden';
        private $user = 'root';
        private $pass = 'rootroot';
        private $charset = 'utf8mb4';
        private $dsn;
        private $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        public $conn;
        
        function __construct()
        {
            $this->dsn = "mysql:host=$this->host;dbname=$this->db;charset=$this->charset";
            try {
                $this->conn = new PDO($this->dsn, $this->user, $this->pass, $this->options);
            } catch (\PDOException $e) {
                echo "DB connection error";
           }
        }
    }
?>