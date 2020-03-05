<?php
    require_once './database.php';
    require_once 'gift.php';
    class Wedding {
        public $id;
        public $person1;
        public $person2;
        public $weddingdate;
        public $invitecode;
        public $linkingcode;
        public $gifts = array();
        private $conn;

        function __construct($_id, $_person1, $_person2, $_weddingdate, $_invitecode, $_linkingcode)
        {
            $db =new Database();
            $this->conn = $db->conn;
            $this->id = $_id;
            $this->person1 = $_person1;
            $this->person2 = $_person2;
            $this->weddingdate = $_weddingdate;
            $this->invitecode = $_invitecode;
            $this->linkingcode = $_linkingcode;
        }

        function save()
        {
            $query = "UPDATE Weddings SET name_person1=:nameperson1, name_person2=:nameperson2, linkingcode=:linkingcode, weddingdate=:weddingdate WHERE weddingId=:weddingid";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':nameperson1', $this->person1);
            $stmt->bindValue(':nameperson2', $this->person2);
            $stmt->bindValue(':linkingcode', $this->linkingcode);
            $stmt->bindValue(':weddingdate', $this->weddingdate);
            $stmt->bindValue(':weddingid', $this->id);
            $result = $stmt->execute();
            return $result;
        }

        static function create($person1, $person2, $weddingdate)
        {
            $db =new Database();
            $conn = $db->conn;
            $invitecode = Self::generateCode();
            $linkingcode = Self::generateCode();
            $query = "INSERT INTO Weddings(name_person1, name_person2, invitecode, linkingcode, weddingdate) VALUES (:person1, :person2, :invitecode, :linkingcode, :weddingdate)";
            $stmt = $conn->prepare($query);
            $stmt->bindValue(':person1', $person1);
            $stmt->bindValue(':person2', $person2);
            $stmt->bindValue(':weddingdate', $weddingdate);
            $stmt->bindValue(':invitecode', $invitecode);
            $stmt->bindValue(':linkingcode', $linkingcode);
            if($stmt->execute() == false) {
                $error = new errorHandler('Bruiloft kan niet worden aangemaakt', 503);
                return $error;
            }
            return $conn->lastInsertId();
        }

        static function getWedding($id)
        {
            $db =new Database();
            $conn = $db->conn;
            $query = "SELECT * FROM Weddings WHERE weddingId=? LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->execute([$id]);
            if($stmt->rowCount() !== 1) return null;
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $wedding = new Self(
                $result['weddingId'],
                $result['name_person1'],
                $result['name_person2'],
                $result['weddingdate'],
                $result['invitecode'],
                $result['linkingcode']);
            $wedding->getGifts();
            return $wedding;
        }

        static private function generateCode() {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 10; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }


        static function validateWeddingCode($weddingcode)
        {
            $db =new Database();
            $conn = $db->conn;
            $query = "SELECT * from weddings WHERE invitecode=? LIMIT 1";
            $stmt = $conn->prepare($query);
            $result = $stmt->execute([$weddingcode]);
            if($stmt->rowCount() != 1) {
                $error = new errorHandler('Deze code is niet geldig! Neem contact op met het bruidspaar.', 404);
                return $error;
            }
            $wedding = $stmt->fetch(PDO::FETCH_ASSOC);
            return $wedding['weddingId'];
        }

        static function validateLinkingCode($linkingcode)
        {
            $db =new Database();
            $conn = $db->conn;

            // Zoek wedding
            $query = "SELECT * from weddings WHERE linkingcode=? LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->execute([$linkingcode]);

            // als geen wedding gevonden, stuur foutmelding
            if($stmt->rowCount() != 1) {
                $error = new errorHandler('Deze code is niet geldig!', 404);
                return $error;
            }

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $wedding = new Self(
                $result['weddingId'],
                $result['name_person1'],
                $result['name_person2'],
                $result['weddingdate'],
                $result['invitecode'],
                $result['linkingcode']);
            return $wedding;
        }

        private function getGifts()
        {
            $query = "SELECT * FROM Gifts WHERE weddingId=? ORDER BY sequence";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$this->id]);
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $gift = new Gift(
                    $row['weddingId'],
                    $row['name'],
                    $row['summary'],
                    $row['image'],
                    $row['sequence']
                );
                array_push($this->gifts, $gift);
            }
        }
    }

    
?>