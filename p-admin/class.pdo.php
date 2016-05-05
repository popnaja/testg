<?php
class myDb{
    public $conn;
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
            db_error(__METHOD__,$ex);
        }
    }
    public function view_umeta($uid){
        try {
            $stmt = $this->conn->prepare("SELECT meta_key,meta_value FROM user_meta WHERE user_id=:uid");
            $stmt->bindParam(":uid",$uid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__,$ex);
        }
    }
    public function get_meta($tb,$field,$id){
        try {
            $stmt = $this->conn->prepare("SELECT meta_key,meta_value FROM $tb WHERE $field=:id");
            $stmt->bindParam(":id",$id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function update_meta($tb,$field,$id,$meta){
        try {
            $stmt0 = $this->conn->prepare("SELECT * FROM $tb WHERE $field=:id AND meta_key=:key");
            $stmt0->bindParam(":id",$id);
            $stmt0->bindParam(":key",$key);
            $stmt = $this->conn->prepare("UPDATE $tb SET meta_value=:val WHERE $field=:id AND meta_key=:key");
            $stmt->bindParam(":id",$id);
            $stmt->bindParam(":key",$key);
            $stmt->bindParam(":val",$val);
            $stmt1 = $this->conn->prepare("INSERT INTO $tb VALUES (null,:id,:key,:val)");
            $stmt1->bindParam(":id",$id);
            $stmt1->bindParam(":key",$key);
            $stmt1->bindParam(":val",$val);
            foreach($meta AS $key=>$val){
                $stmt0->execute();
                if($stmt0->rowCount()>0){
                    //update
                    $stmt->execute();
                } else {
                    //add
                    $stmt1->execute();
                }
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function insert_data($tb,$arr){
        try {
            $n = sizeof($arr);
            $prep = "";
            for($i=0;$i<$n;$i++){
                $prep .= ":val$i";
                $prep .= ($i==$n-1?"":",");
            }
            $stmt = $this->conn->prepare("INSERT INTO $tb VALUES($prep)");
            for($i=0;$i<$n;$i++){
                $stmt->bindParam(":val$i",$arr[$i]);
            }
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function update_data($tb,$field,$id,$arrinfo){
        try {
            $sql = gen_sql($arrinfo,",","param");
            $stmt = $this->conn->prepare("UPDATE $tb SET $sql[0] WHERE $field=:id");
            $stmt->bindParam(":id",$id);
            foreach($sql[1] AS $k => $v){
                $stmt->bindParam(":$k",$sql[1][$k]);
            }
            $stmt->execute();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_info($tb,$field,$id){
        try {
            $stmt = $this->conn->prepare("SELECT * FROM $tb WHERE $field=:id");
            $stmt->bindParam(":id",$id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_infos($tb,$field,$id){
        try {
            $stmt = $this->conn->prepare("SELECT * FROM $tb WHERE $field=:id");
            $stmt->bindParam(":id",$id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_keypair($tb,$key,$val,$where=""){
        try {
            $stmt = $this->conn->prepare("SELECT $key,$val FROM $tb $where");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function delete_data($tb,$col,$val){
        try {
            $stmt = $this->conn->prepare("DELETE FROM $tb WHERE $col=:val");
            $stmt->bindParam(":val",$val);
            $stmt->execute();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_mm_arr($tb,$tg,$col,$val){
        try {
            $stmt = $this->conn->prepare("SELECT $tg FROM $tb WHERE $col=:val");
            $stmt->bindParam(":val",$val);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN,0);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
}

