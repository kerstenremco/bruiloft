<?php
require_once './database.php';
require_once './objects/wedding.php';
$database = new Database();
    class Gift {
        private $weddingId;
        private $originalName;
        public $name;
        public $summary;
        public $image;
        public $sequence;
        private $conn;

        function __construct($_weddingId, $_name, $_summary, $_image, $_sequence)
        {
            $db = new Database();
            $this->conn = $db->conn;
            $this->weddingId = $_weddingId;
            $this->originalName = $_name;
            $this->name = $_name;
            $this->summary = $_summary;
            $this->image = $_image;
            $this->sequence = $_sequence;
        }

        public function save()
        {
            $query = "UPDATE Gifts SET name=:name, summary=:summary, image=:image, sequence=:sequence WHERE weddingId=:weddingid AND name=:originalname";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':summary', $this->summary);
            $stmt->bindParam(':image', $this->image);
            $stmt->bindParam(':sequence', $this->sequence);
            $stmt->bindParam(':originalname', $this->originalName);
            $stmt->bindParam(':weddingid', $this->weddingId);
            $result = $stmt->execute();
            return $result;
        }

        static function getGift($weddingid, $giftname)
        {
            $db =new Database();
            $conn = $db->conn;
            $query = "SELECT * FROM Gifts WHERE weddingId=:weddingid AND name=:name LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':weddingid', $weddingid);
            $stmt->bindParam(':name', $giftname);
            $stmt->execute();
            if($stmt->rowCount() !== 1) return null;
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $gift = new Self(
                $result['weddingId'],
                $result['name'],
                $result['summary'],
                $result['image'],
                $result['sequence']);
            return $gift;
        }


        static function delete($weddingid, $giftname)
        {
            $db =new Database();
            $conn = $db->conn;
            $query = "DELETE FROM Gifts WHERE weddingId=:weddingid AND name=:name";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':weddingid', $weddingid);
            $stmt->bindParam(':name', $giftname);
            $result = $stmt->execute();
            if($stmt->rowCount() !== 1) return false;
            return $result;
        }
    }

    
?>