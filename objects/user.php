<?php
    require_once './database.php';
    require_once './objects/wedding.php';
    class User {
        public $gebruikersnaam;
        public $password;
        public $emailadres;
        public $id;
        public $wedding;
        private $conn;

        function __construct()
        {
            $db =new Database();
            $this->conn = $db->conn;
        }

        function validateUser()
        {
            $query = "SELECT * FROM users u WHERE username=? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$this->gebruikersnaam]);
            if($stmt->rowCount() !== 1) return false;
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if(!password_verify($this->password, $user['password'])) return false;
            $this->id = $user['id'];
            return true;
        }

        function getUser()
        {
            $query = "SELECT * FROM users WHERE id=? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$this->id]);
            if($stmt->rowCount() !== 1) return false;
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->gebruikersnaam = $user['username'];
            $this->emailadres = $user['email'];
            $this->updateWedding();
            return true;
        }

        function createUser()
        {
            $this->password = password_hash($this->password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users(username, password, email) VALUES (:username, :password, :email)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $this->gebruikersnaam);
            $stmt->bindParam(':password', $this->password);
            $stmt->bindParam(':email', $this->emailadres);
            if($stmt->execute() == false) return false;
            $query = "SELECT * FROM users WHERE username=? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$this->gebruikersnaam]);
            if($stmt->rowCount() !== 1) return false;
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $user['id'];
            return true;
        }

        function updateWedding()
        {
            $wedding = new Wedding();
            $wedding->userid = $this->id;
            if($wedding->getWedding('user') == false) return false;
            $this->wedding = $wedding;
            return true;
        }
    }
?>