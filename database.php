<?php
    class Database {
        private $dsn;
        private $options = [
            //PDO::ATTR_ERRMODE            => PDO::ERRMODE_WARNING,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        public $conn;
        
        function __construct()
        {
            $this->dsn = 'mysql:host='. DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            try {
                $this->conn = new PDO($this->dsn, DB_USER, DB_PASSWORD, $this->options);
            } catch (\PDOException $e) {
                echo 'Fout met verbinden database!';
                die();
           }
        }
    }
?>