<?php
require_once './database.php';
require_once './objects/wedding.php';
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
            global $errorMessage, $errorCode;
            $wedding = new Wedding();
            $wedding->userid = $_SESSION['userID'];
            if ($wedding->getWedding('user') == false) {
                $errorMessage = 'Er is geen bruiloft bekend bij deze gebruiker';
                $errorCode = 404;
                return false;
            }
            $query = "SELECT * FROM kados WHERE id=? AND bruiloftID=? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$this->id, $wedding->id]);
            if($stmt->rowCount() !== 1) {
                $errorMessage = 'Kado niet gevonden';
                $errorCode = 404;
                return false;
            }
            $query = "DELETE FROM kados WHERE id=? AND bruiloftID=?";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([$this->id, $wedding->id]);
            if($result == false) {
                $errorMessage = 'Cadeau kan niet worden verwijderd';
                $errorCode = 503;
                return false;
            }
            return true;
        }
    }

    
?>