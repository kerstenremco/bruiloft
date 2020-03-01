<?php
require_once './database.php';
$database = new Database();
    class Kado {
        public $id;
        public $bruilodfID;
        public $naam;
        public $beschrijving;
        public $img;
        public $order;
        private $conn;

        function __construct()
        {
            $db = new Database();
            $this->conn = $db->conn;
        }

        public function save()
        {
            $query = "UPDATE kados SET naam=:naam, beschrijving=:beschrijving, kados.order=:order WHERE id=:id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':naam', $this->naam);
            $stmt->bindParam(':beschrijving', $this->beschrijving);
            $stmt->bindParam(':order', $this->order);
            $stmt->bindParam(':id', $this->id);
            $stmt->execute();
        }
        public function delete()
        {
            $query = "DELETE FROM kados WHERE id=?";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([$this->id]);
            return $result;
        }
    }

    
?>