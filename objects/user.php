<?php
    require_once './database.php';
    require_once './objects/wedding.php';
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

        private function connect()
        {
            $db = new Database();
            $this->conn = $db->conn;
        }

        private function disconnect()
        {
            $this->conn = null;
        }

        function validateUser()
        {
            $this->connect();
            $query = "SELECT * FROM users u WHERE username=? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$this->gebruikersnaam]);
            $userFound = $stmt->rowCount() == 1;
            if($userFound) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if(!password_verify($this->password, $user['password'])) {
                    $this->password = null;
                    $this->disconnect();
                    return false;
                }
                $this->password = null;
                $this->id = $user['id'];
                $this->emailadres = $user['email'];
                $this->updateWedding();
                $this->disconnect();
                return true;
            } else {
                $this->gebruikersnaam = null;
                $this->disconnect();
                return false;
            }
        }

        function createUser()
        {
            $this->connect();
            $this->password = password_hash($this->password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users(username, password, email) VALUES (:username, :password, :email)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $this->gebruikersnaam);
            $stmt->bindParam(':password', $this->password);
            $stmt->bindParam(':email', $this->emailadres);
            $userCreated = $stmt->execute();
            if($userCreated) {
                $query = "SELECT * FROM users WHERE username=? LIMIT 1";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([$this->gebruikersnaam]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $this->id = $user['id'];
                $this->disconnect();
                return true;
            } else {
                $this->disconnect();
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