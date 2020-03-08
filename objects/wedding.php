<?php
namespace objects;

use Exception;

class Wedding {
        private $id;
        public $person1;
        public $person2;
        public $weddingdate;
        public $invitecode;
        public $linkingcode;
        public $gifts = array();
        private $conn;

                
        /**
         * __construct
         *
         * @param  int $id
         * @param  string $person1
         * @param  string $person2
         * @param  date $weddingdate
         * @param  string $invitecode
         * @param  string $linkingcode
         * @return void
         */
        function __construct($id, $person1, $person2, $weddingdate, $invitecode, $linkingcode)
        {
            $db =new \Database();
            $this->conn = $db->conn;
            $this->id = $id;
            $this->person1 = $person1;
            $this->person2 = $person2;
            $this->weddingdate = $weddingdate;
            $this->invitecode = $invitecode;
            $this->linkingcode = $linkingcode;
        }

        function get($element) 
        {
            return $this->$element;
        }
                
        /**
         * Slaat huidig object op in database
         *
         * @return void
         */
        function save()
        {
            $this->weddingdate = str_replace('/', '-', $this->weddingdate);
            $this->weddingdate = date("Y-m-d", strtotime($this->weddingdate));
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
        
        /**
         * create wedding
         *
         * @param  string $person1
         * @param  string $person2
         * @param  date $weddingdate
         * @return wedding
         */
        static function create($person1, $person2, $weddingdate)
        {
            $weddingdate = str_replace('/', '-', $weddingdate);
            $weddingdate = date("Y-m-d", strtotime($weddingdate));

            $db =new \Database();
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

            if($stmt->execute() == false) throw new \Exception('Bruiloft kan niet worden aangemaakt', 503);

            try { $wedding =  Self::getWedding($conn->lastInsertId()); }
            catch(Exception $e) { throw new \Exception('Bruiloft kan niet worden aangemaakt', 503); }

            return $wedding;
        }
        
        /**
         * getWedding
         *
         * @param  int $id
         * @return wedding
         */
        static function getWedding($id)
        {
            $db =new \Database();
            $conn = $db->conn;
            $query = "SELECT *, DATE_FORMAT(weddingdate, '%d/%m/%Y') AS formateddate FROM Weddings WHERE weddingId=? LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->execute([$id]);

            if($stmt->rowCount() !== 1) throw new \Exception('Bruiloft kan niet worden gevonden', 404);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            $wedding = new Self($result['weddingId'], $result['name_person1'], $result['name_person2'], $result['formateddate'], $result['invitecode'],$result['linkingcode']);
            $wedding->getGifts();
            return $wedding;
        }
        
                
        /**
         * return wedding obv weddingcode
         *
         * @param  string $weddingcode
         * @return wedding
         */
        static function validateWeddingCode($weddingcode)
        {
            $db =new \Database();
            $conn = $db->conn;
            $query = "SELECT * from weddings WHERE invitecode=? LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->execute([$weddingcode]);
            if($stmt->rowCount() != 1) throw new \Exception('Deze code is niet geldig! Neem contact op met het bruidspaar.', 404);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return new self($result['weddingId'], $result['name_person1'], $result['name_person2'], $result['weddingdate'], $result['invitecode'], $result['linkingcode']);
        }
        
        /**
         * return wedding obv linkingcode
         *
         * @param  string $linkingcode
         * @return wedding
         */
        static function validateLinkingCode($linkingcode)
        {
            $db =new \Database();
            $conn = $db->conn;

            // Zoek wedding
            $query = "SELECT * from weddings WHERE linkingcode=? LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->execute([$linkingcode]);

            // als geen wedding gevonden, stuur foutmelding
            if($stmt->rowCount() != 1) throw new \Exception('Deze code is niet geldig!', 404);

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return new self($result['weddingId'], $result['name_person1'], $result['name_person2'], $result['weddingdate'], $result['invitecode'], $result['linkingcode']);
        }
        
        /**
         * Haal cadeaus op behorende bij wedding en zet in this->gifts
         *
         * @return void
         */
        private function getGifts()
        {
            $query = "SELECT * FROM Gifts WHERE weddingId=? ORDER BY sequence";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$this->id]);
            
            while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $gift = new Gift(
                    $row['weddingId'],
                    $row['name'],
                    $row['summary'],
                    $row['image'],
                    $row['sequence'],
                    $row['claimed']
                );
                array_push($this->gifts, $gift);
            }
        }

         /**
         * generateCode
         *
         * @return random string
         */
        static private function generateCode() {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 10; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }
    }

    
?>