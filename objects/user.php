<?php
    require_once './database.php';
    require_once 'wedding.php';
    $database = new Database();
    class User {
        public $gebruikersnaam;
        private $password;
        public $emailadres;
        public $id;
        public $wedding;
        private $conn;

        function __construct($gebruikersnaam, $password)
        {
            $this->gebruikersnaam = $gebruikersnaam;
            $this->password = $password;
        }

        function connect()
        {
            $db = new Database();
            $this->conn = $db->conn;
        }

        function disconnect()
        {
            $this->conn = null;
        }

        function validateUser()
        {
            $query = "SELECT u.id, u.username, u.email FROM users u WHERE username=:username AND password=:password LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $this->gebruikersnaam);
            $stmt->bindParam(':password', $this->password);
            $stmt->execute();
            $this->password = null;
            $userFound = $stmt->rowCount() == 1;
            if($userFound) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $this->id = $user['id'];
                $this->emailadres = $user['email'];
                $this->updateWedding();
                return true;
            } else {
                $this->gebruikersnaam = null;
                return false;
            }
        }

        function updateWedding()
        {
            $wedding = new Wedding($this->id);
            if(isset($wedding->id)) {
                $this->wedding = $wedding;
            } else {
                $this->wedding = null;
            }
        }
    }
?>