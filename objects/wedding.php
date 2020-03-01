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
        public $kados;
        private $conn;

        function __construct()
        {
            if(func_num_args() == 1) {
                $this->connect();
                $query = "SELECT * FROM bruiloften WHERE userid=? LIMIT 1";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([func_get_arg(0)]);
                $weddingFound = $stmt->rowCount() == 1;
                if($weddingFound) {
                    $wedding = $stmt->fetch(PDO::FETCH_ASSOC);
                    $this->id = $wedding['id'];
                    $this->userid = $wedding['userid'];
                    $this->person1 = $wedding['person1'];
                    $this->person2 = $wedding['person2'];
                    $this->date = $wedding['date'];
                    $this->invitecode = $wedding['invitecode'];
                }
                $this->disconnect();
            }
        }

        private function connect()
        {
            $db = new Database();
            $this->conn = $db->conn;
        }

        private function disconnect()
        {
            $this->conn = null;
        }

        public function create()
        {
            $this->connect();
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
            $this->connect();
            $query = "SELECT * from bruiloften WHERE invitecode=? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$this->invitecode]);
            $weddingCodeValide = $stmt->rowCount() == 1;
            if($weddingCodeValide) {
                $wedding = $stmt->fetch(PDO::FETCH_ASSOC);
                $this->id = $wedding['id'];
                $this->userid = $wedding['userid'];
                $this->person1 = $wedding['person1'];
                $this->person2 = $wedding['person2'];
                $this->date = $wedding['date'];
                $this->disconnect();
                return true;
            } else {
                $this->invitecode = null;
                $this->disconnect();
                return false;
            }
        }

        public function getKados()
        {
            $this->kados = array();
            $this->connect();
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
            $this->disconnect();
        }
    }

    
?>