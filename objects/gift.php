<?php
namespace objects;
$database = new \Database();
    class Gift {
        private $weddingId;
        private $originalName;
        public $name;
        public $summary;
        public $image;
        public $sequence;
        public $claimed;
        private $conn;

        function __construct($weddingId, $name, $_summary, $image, $sequence, $claimed)
        {
            $db = new \Database();
            $this->conn = $db->conn;
            $this->weddingId = $weddingId;
            $this->originalName = $name;
            $this->name = $name;
            $this->summary = $_summary;
            $this->image = $image;
            $this->sequence = $sequence;
            $this->claimed = $claimed;
        }

        public function save()
        {
            $query = "UPDATE Gifts SET name=:name, summary=:summary, image=:image, sequence=:sequence, claimed=:claimed WHERE weddingId=:weddingid AND name=:originalname";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':summary', $this->summary);
            $stmt->bindParam(':image', $this->image);
            $stmt->bindParam(':sequence', $this->sequence);
            $stmt->bindParam(':originalname', $this->originalName);
            $stmt->bindParam(':weddingid', $this->weddingId);
            $stmt->bindParam(':claimed', $this->claimed);
            $result = $stmt->execute();
            return $result;
        }

        static function getGift($weddingid, $giftname)
        {
            $db =new \Database();
            $conn = $db->conn;
            $query = "SELECT * FROM Gifts WHERE weddingId=:weddingid AND name=:name LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':weddingid', $weddingid);
            $stmt->bindParam(':name', $giftname);
            $stmt->execute();
            if($stmt->rowCount() !== 1) return null;
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $gift = new Self(
                $result['weddingId'],
                $result['name'],
                $result['summary'],
                $result['image'],
                $result['sequence'],
                $result['claimed']);
            return $gift;
        }

        static function create($weddingid, $giftname, $giftsummary, $imagename)
        {
            $db =new \Database();
            $conn = $db->conn;
            $query = "SELECT * FROM Gifts WHERE weddingId=? ORDER BY sequence DESC LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->execute([$weddingid]);
            if($stmt->rowCount() == 1) {
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                $sequence = $result['sequence'] + 1;
            } else $sequence = 1;
            $query = "INSERT INTO Gifts(weddingId, name, summary, image, sequence) VALUES(:weddingid, :name, :summary, :image, :sequence)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':weddingid', $weddingid);
            $stmt->bindParam(':name', $giftname);
            $stmt->bindParam(':summary', $giftsummary);
            $stmt->bindParam(':image', $imagename);
            $stmt->bindParam(':sequence', $sequence);
            $stmt->execute();
            if($stmt->rowCount() !== 1) return null;
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $gift = new Self(
                $weddingid,
                $giftname,
                $giftsummary,
                $imagename,
                $sequence,
                false);
            return $gift;
        }


        static function delete($weddingid, $giftname)
        {
            // connect database
            $db =new \Database();
            $conn = $db->conn;

            // Zoek gift om te controleren of er een afbeelding is gekoppeld en verwijder indien nodig
            $query = "SELECT * FROM Gifts WHERE weddingId=:weddingid AND name=:name";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':weddingid', $weddingid);
            $stmt->bindParam(':name', $giftname);
            $stmt->execute();
            if($stmt->rowCount() !== 1) return false;
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            if(isset($result['image'])) \helpers\imageHandler::removeImage('gift', $result['image']);
            
            // verwijder gift van DB en return resultaat
            $query = "DELETE FROM Gifts WHERE weddingId=:weddingid AND name=:name";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':weddingid', $weddingid);
            $stmt->bindParam(':name', $giftname);
            $result = $stmt->execute();
            return $result;
        }
    }
?>