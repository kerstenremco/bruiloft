<?php
    namespace objects;
    class User {
        private $username;
        private $password;
        public $email;
        public $weddingId;
        public $wedding;
        private $conn;
        
        /**
         * __construct
         *
         * @param  string $username
         * @param  string $email
         * @param  id $weddingId
         * @return void
         */
        function __construct($username, $email, $weddingId)
        {
            $db =new \Database();
            $this->conn = $db->conn;
            $this->username = $username;
            $this->email = $email;
            $this->weddingId = $weddingId;
        }

        function get($element) 
        {
            return $this->$element;
        }
                
        /**
         * Sla huidige gebruiker op in DB. Return true indien gelukt
         *
         * @return bool
         */
        function save()
        {
            $query = "UPDATE Users SET email=:email, weddingId=:weddingId WHERE username=:username";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':username', $this->username);
            $stmt->bindValue(':email', $this->email);
            $stmt->bindValue(':weddingId', $this->weddingId);
            if($stmt->execute() == false) throw new \Exception('Fout bij opslaan gebruiker, probeer het later nogmaals', 500);
            return true;
        }

                
        /**
         * controleert inloggegevens
         *
         * @param  string $username
         * @param  string $password
         * @return User
         */
        static function login($username, $password)
        {
            $db =new \Database();
            $conn = $db->conn;
            $query = "SELECT * FROM users WHERE username=? LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->execute([$username]);
            if($stmt->rowCount() !== 1) throw new \Exception('Gebruikersnaam of wachtwoord verkeerd', 401);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            if(!password_verify($password, $result['password'])) throw new \Exception('Gebruikersnaam of wachtwoord verkeerd', 401);
            return new self($result['username'], $result['email'], $result['weddingId']);
        }
        
        /**
         * Get user by username
         *
         * @param  string $username
         * @return User
         */
        static function getUser($username)
        {
            $db =new \Database();
            $conn = $db->conn;
            $query = "SELECT * FROM users WHERE username=? LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->execute([$username]);

            if($stmt->rowCount() !== 1) throw new \Exception('Gebruiker niet gevonden', 404);

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $user = new Self($result['username'], $result['email'], $result['weddingId']);

            if(isset($user->weddingId)) $user->wedding = Wedding::getWedding($user->weddingId);
            return $user;
        }
        
        /**
         * Maak gebruiker aan
         *
         * @param  string $username
         * @param  string $password
         * @param  string $password2
         * @param  string $email
         * @return User
         */
        static function createUser($username, $password, $password2, $email)
        {
            if(strlen($password) < 8) throw new \Exception('Wachtwoord moet minimaal 8 karakters lang zijn', 400);

            if ($password != $password2) throw new \Exception('Wachtwoorden zijn niet gelijk', 400);
            $password = password_hash($password, PASSWORD_DEFAULT);

            // maak gebruiker aan in DB
            $db =new \Database();
            $conn = $db->conn;
            $query = "INSERT INTO users(username, password, email) VALUES (:username, :password, :email)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':email', $email);
            $result = $stmt->execute();

            // indien er een fout is ontstaan, geef foutmelding
            if($result == false) {
                $errorCode = $stmt->errorInfo()[1];
                switch ($errorCode) {
                    case 1062:
                        throw new \Exception('Gebruikersnaam bestaat al', 409);
                        break;
                    default:
                        throw new \Exception('Gebruiker kan niet worden aangemaakt, probeer het later nogmaals', 503);
                }
            }

            // roep login functie aan om zeker te zijn dat de gebruiker daadwerkelijk bestaat in DB en return user instance via login functie
            return Self::login($username, $password2);
        }
    }
?>