<?php
    require_once './database.php';
    require_once 'kado.php';
    class Wedding {
        public $userid;
        public $person1;
        public $person2;
        public $id;
        public $date;
        public $invitecode;
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
            $query = "INSERT INTO bruiloften(userid, person1, person2, date, invitecode) VALUES (:userid, :person1, :person2, :date, :invitecode)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':userid', $this->userid);
            $stmt->bindValue(':person1', $this->person1);
            $stmt->bindValue(':person2', $this->person2);
            $stmt->bindValue(':date', $this->date);
            $stmt->bindValue(':invitecode', $this->invitecode);
            return $stmt->execute();
        }

        public function getWedding($getBy)
        {
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
            if($stmt->rowCount() != 1) return false;
            $wedding = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $wedding['id'];
            $this->userid = $wedding['userid'];
            $this->person1 = $wedding['person1'];
            $this->person2 = $wedding['person2'];
            $this->date = $wedding['date'];
            $this->invitecode = $wedding['invitecode'];
            $this->getKados();
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
        function validateWeddingCode()
        {
            $query = "SELECT * from bruiloften WHERE invitecode=? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$this->invitecode]);
            if($stmt->rowCount() != 1) return false;
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