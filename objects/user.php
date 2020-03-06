<?php
    namespace objects;
    class User {
        private $username;
        public $password;
        public $email;
        public $weddingId;
        public $wedding;
        private $conn;

        function __construct($_username, $_email, $_weddingId)
        {
            $db =new \Database();
            $this->conn = $db->conn;
            $this->username = $_username;
            $this->email = $_email;
            $this->weddingId = $_weddingId;
        }

        function save()
        {
            $query = "UPDATE Users SET email=:email, weddingId=:weddingId WHERE username=:username";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':username', $this->username);
            $stmt->bindValue(':email', $this->email);
            $stmt->bindValue(':weddingId', $this->weddingId);
            $result = $stmt->execute();
            return $result;
        }

        static function login($username, $password)
        {
            $db =new \Database();
            $conn = $db->conn;
            $query = "SELECT * FROM users WHERE username=? LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->execute([$username]);
            if($stmt->rowCount() !== 1) return false;
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            if(!password_verify($password, $result['password'])) return false;
            return true;
        }

        static function getUser($username)
        {
            $db =new \Database();
            $conn = $db->conn;
            $query = "SELECT * FROM users WHERE username=? LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->execute([$username]);
            if($stmt->rowCount() !== 1) return false;
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $user = new Self($result['username'], $result['email'], $result['weddingId']);
            if(isset($result['weddingId'])) {
                $user->wedding = Wedding::getWedding($result['weddingId']);
            }
            return $user;
        }

        static function createUser($username, $password, $email)
        {
            $db =new \Database();
            $conn = $db->conn;
            $password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users(username, password, email) VALUES (:username, :password, :email)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':email', $email);
            $result = $stmt->execute();
            if($result == false) {
                $errorCode = $stmt->errorInfo()[1];
                switch ($errorCode) {
                    case 1062:
                        $error = new \helpers\errorHandler('Gebruikersnaam bestaat al', 409);
                        break;
                    default:
                        $error = new \helpers\errorHandler('Gebruiker kan niet worden aangemaakt, probeer het later nogmaals', 503);
                }
                return $error;
            }
            return true;
        }

        function getWedding()
        {
            $wedding = new Wedding();
            $wedding->userid = $this->id;
            if($wedding->getWedding('user') == false) return false;
            $this->wedding = $wedding;
            return true;
        }
    }
?>