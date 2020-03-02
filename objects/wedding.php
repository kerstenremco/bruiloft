<?php
    require_once './database.php';
    require_once 'kado.php';
    class Wedding {
        public $userid;
        public $partneruserid;
        public $person1;
        public $person2;
        public $id;
        public $date;
        public $invitecode;
        public $linkingcode;
        public $kados = array();
        private $conn;

        function __construct()
        {
            $db =new Database();
            $this->conn = $db->conn;
        }

        public function create()
        {
            $this->generateInvitecode();
            $this->generateLinkingcode();
            $query = "INSERT INTO bruiloften(userid, person1, person2, date, invitecode, linkingcode) VALUES (:userid, :person1, :person2, :date, :invitecode, :linkingcode)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':userid', $this->userid);
            $stmt->bindValue(':person1', $this->person1);
            $stmt->bindValue(':person2', $this->person2);
            $stmt->bindValue(':date', $this->date);
            $stmt->bindValue(':invitecode', $this->invitecode);
            $stmt->bindValue(':linkingcode', $this->linkingcode);
            if($stmt->execute() == false) {
                $errorMessage = 'Bruiloft kan niet worden aangemaakt';
                $errorCode = 503;
                return false;
            }
            return true;
        }

        public function getWedding($getBy)
        {
            global $errorMessage, $errorCode;
            switch($getBy) {
                case 'user':
                    $query = "SELECT * FROM bruiloften WHERE userid=? LIMIT 1";
                    $param = [$this->userid];
                    break;
                case 'id':
                    $query = "SELECT * FROM bruiloften WHERE id=? LIMIT 1";
                    $param = [$this->id];
                    break;
                default:
                    return false;
            }
            $stmt = $this->conn->prepare($query);
            $stmt->execute($param);
            if($stmt->rowCount() != 1) {
                $errorMessage = 'Deze bruiloft bestaat niet';
                $errorCode = 404;
                return false;
            }
            $wedding = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $wedding['id'];
            $this->userid = $wedding['userid'];
            $this->partneruserid = $wedding['partneruserid'];
            $this->person1 = $wedding['person1'];
            $this->person2 = $wedding['person2'];
            $this->date = $wedding['date'];
            $this->invitecode = $wedding['invitecode'];
            $this->linkingcode = $wedding['linkingcode'];
            $this->getKados();
            return true;
        }

        function connectToPartner()
        {
            global $errorMessage, $errorCode;
            $query = "SELECT * FROM bruiloften WHERE linkingcode=? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$this->linkingcode]);
            if($stmt->rowCount() != 1) {
                $errorMessage = 'Geen bruiloft gevonden met deze linkcode';
                $errorCode = 404;
                return false;
            }
            $foundWedding = $stmt->fetch(PDO::FETCH_ASSOC);
            if($foundWedding['userid'] == $this->partneruserid) {
                $errorMessage = 'Je bent al lid van deze bruiloft';
                $errorCode = 409;
                return false;
            }
            $query = "UPDATE bruiloften SET partneruserid=?, linkingcode=null WHERE linkingcode=? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$this->partneruserid, $this->linkingcode]);
            if($stmt->rowCount() != 1) {
                $errorMessage = 'Code kan niet worden gekoppeld, probeer het later nogmaals';
                $errorCode = 503;
                return false;
            }
            return true;
        }

        private function generateInvitecode() {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 10; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            $this->invitecode = $randomString;
        }

        private function generateLinkingcode() {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 10; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            $this->linkingcode = $randomString;
        }


        function validateWeddingCode()
        {
            global $errorMessage, $errorCode;
            $query = "SELECT * from bruiloften WHERE invitecode=? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$this->invitecode]);
            if($stmt->rowCount() != 1) {
                $errorMessage = 'Dit is geen geldige uitnodigingscode';
                $errorCode = 404;
                return false;
            }
            $wedding = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $wedding['id'];
            return true;
        }

        public function getKados()
        {
            $query = "SELECT * FROM kados WHERE bruiloftID=? ORDER BY kados.order";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$this->id]);
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $kado = new Kado();
                $kado->id = $row['id'];
                $kado->bruiloftID = $row['bruiloftID'];
                $kado->naam = $row['naam'];
                $kado->beschrijving = $row['beschrijving'];
                $kado->img = $row['img'];
                $kado->order = $row['order'];
                array_push($this->kados, $kado);
            }
        }
    }

    
?>