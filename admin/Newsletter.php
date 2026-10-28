<?php
class Newsletter {
    private $db;
    public $error = [];

    public function __construct($db_connection){
        $this->db = $db_connection;
    }

    // Returns true for both a fresh subscription and an already-existing
    // one (email is UNIQUE) — either way the visitor is now on the list
    public function subscribe(string $email): bool {
        try{
            $stmt = $this->db->prepare("INSERT IGNORE INTO newsletter_subscribers (email) VALUES (?)");
            $stmt->execute([$email]);
            return true;
        }
        catch(PDOException $e){
            $this->error[] = "Database error.";
            return false;
        }
    }
}
