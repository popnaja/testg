<?php
class myDb{
    private $conn;
    public function __construct() {
        $this->conn = dbConnect(DB_GREEN);
    }
    public function check_user($email,$pass){
        try {
            $smtp = $this->conn->prepare("SELECT id FROM user WHERE email=:email AND pass=:pass");
            $smtp->bindParam(":email",$email);
            $smtp->bindParam(":pass",$pass);
            $smtp->execute();
            if($smtp->rowCount()>0){
                return $smtp->fetch(PDO::FETCH_ASSOC)['id'];
            } else {
                return false;
            }
        } catch (Exception $ex) {
            db_error("check_user", $ex);
        }
    }
    public function view_umeta($uid){
        try {
            $stmt = $this->conn->prepare("SELECT meta_key,meta_value FROM user_meta WHERE user_id=:uid");
            $stmt->bindParam(":uid",$uid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error("view_umeta", $ex);
        }
    }
}

