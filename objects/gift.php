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
        
        /**
         * __construct
         *
         * @param  int $weddingId
         * @param  string $name
         * @param  string $_summary
         * @param  string $image
         * @param  int $sequence
         * @param  bool $claimed
         * @return void
         */
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
        
        /**
         * Sla gift op in DB
         *
         * @return void
         */
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

            if($stmt->execute() == false) {
                throw new \Exception('Fout bij opslaan cadeau, probeer het later nogmaals', 500);
            }
        }
        
        /**
         * updateSequence
         * Werk sequence van gift bij
         *
         * @param  mixed $name
         * @param  mixed $weddingId
         * @param  mixed $sequence
         * @return void
         */
        static function updateSequence($name, $weddingId, $sequence)
        {
            $db = new \Database();
            $conn = $db->conn;
            $query = "UPDATE Gifts SET sequence=:sequence WHERE weddingId=:weddingid AND name=:name";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':weddingid', $weddingId);
            $stmt->bindParam(':sequence', $sequence);

            if($stmt->execute() == false) {
                throw new \Exception('Fout bij verwerken van volgorde, probeer het later nogmaals', 500);
            }
        }
        
        /**
         * claimGift
         * Zet gift op claimed
         *
         * @param  mixed $name
         * @param  mixed $weddingId
         * @return void
         */
        static function claimGift($name, $weddingId)
        {
            $db = new \Database();
            $conn = $db->conn;
            $query = "UPDATE Gifts SET claimed=true WHERE weddingId=:weddingid AND name=:name";

            $stmt = $conn->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':weddingid', $weddingId);

            if($stmt->execute() == false) {
                throw new \Exception('Fout bij claimen van gift, probeer het later nogmaals', 500);
            }
        }

        
        /**
         * Haal cadeau op obv weddingid en giftnaam
         *
         * @param  string $weddingid
         * @param  string $giftname
         * @return Gift
         */
        static function getGift($weddingid, $giftname)
        {
            $db =new \Database();
            $conn = $db->conn;
            $query = "SELECT * FROM Gifts WHERE weddingId=:weddingid AND name=:name LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':weddingid', $weddingid);
            $stmt->bindParam(':name', $giftname);
            $stmt->execute();

            if($stmt->rowCount() !== 1) {
                throw new \Exception('Cadeau niet gevonden', 404);
            }

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return new Self(
                $result['weddingId'],
                $result['name'],
                $result['summary'],
                $result['image'],
                $result['sequence'],
                $result['claimed']);
        }
        
        /**
         * create gift
         *
         * @param  int $weddingid
         * @param  string $giftname
         * @param  string $giftsummary
         * @param  string $imagename
         * @return Gift
         */
        static function create($weddingid, $giftname, $giftsummary, $imagename)
        {
            // kijk eerst naar huidige hoogste sequence
            $db =new \Database();
            $conn = $db->conn;
            $query = "SELECT * FROM Gifts WHERE weddingId=? ORDER BY sequence DESC LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->execute([$weddingid]);

            if($stmt->rowCount() == 1) {
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                $sequence = $result['sequence'] + 1;
            } else {
                // er zijn nog geen gifts aangemaakt
                $sequence = 1;
            }

            $query = "INSERT INTO Gifts(weddingId, name, summary, image, sequence) VALUES(:weddingid, :name, :summary, :image, :sequence)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':weddingid', $weddingid);
            $stmt->bindParam(':name', $giftname);
            $stmt->bindParam(':summary', $giftsummary);
            $stmt->bindParam(':image', $imagename);
            $stmt->bindParam(':sequence', $sequence);
            $stmt->execute();

            if($stmt->rowCount() !== 1) {
                throw new \Exception('Fout bij aanmaken cadeau, probeer het later nogmaals', 500);
            }
            $stmt->fetch(\PDO::FETCH_ASSOC);

            return new Self(
                $weddingid,
                $giftname,
                $giftsummary,
                $imagename,
                $sequence,
                false);
        }

        
        /**
         * delete gift
         *
         * @param  int $weddingid
         * @param  string $giftname
         * @return void
         */
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
            if($stmt->rowCount() !== 1) {
                throw new \Exception('Cadeau niet gevonden', 404);
            }

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            if(isset($result['image'])) {
                \helpers\imageHandler::removeImage('gift', $result['image']);
            }
            
            // verwijder gift van DB en return resultaat
            $query = "DELETE FROM Gifts WHERE weddingId=:weddingid AND name=:name";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':weddingid', $weddingid);
            $stmt->bindParam(':name', $giftname);
            if($stmt->execute() == false) {
                throw new \Exception('Cadeau kan niet worden verwijderd, probeer het later nogmaals', 500);
            }
        }
    }
?>