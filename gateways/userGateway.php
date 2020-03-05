<?php
include_once './../database.php';
class userGateway
{
    private $db = new Database();
    private $conn = $db->conn;
    public static function getUserById()
    {
        $query = "SELECT * FROM users WHERE id=? LIMIT 1";
        $stmt = self::$this->conn->prepare($query);
        $stmt->execute([$this->id]);
        if ($stmt->rowCount() !== 1) return false;
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->gebruikersnaam = $user['username'];
        $this->emailadres = $user['email'];
        $this->updateWedding();
        return true;
    }
}
