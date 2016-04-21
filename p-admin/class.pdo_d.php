<?php
class delDB{
    private $conn;
    public function __construct() {
        $this->conn = dbConnect(DB_GREEN);
    }
    public function del_mat($mid){
        try {
            $stmt = $this->conn->prepare("DELETE FROM mat_transport WHERE mat_id=:mid");
            $stmt->bindParam(":mid",$mid);
            $stmt->execute();
            $stmt1 = $this->conn->prepare("DELETE FROM output WHERE mat_id=:mid");
            $stmt1->bindParam(":mid",$mid);
            $stmt1->execute();
            $stmt2 = $this->conn->prepare("DELETE FROM input WHERE mat_id=:mid");
            $stmt2->bindParam(":mid",$mid);
            $stmt2->execute();
            $stmt3 = $this->conn->prepare("SELECT function_unit_id FROM fn_print WHERE paper_type=:mid OR plate_type=:mid");
            $stmt3->bindParam(":mid",$mid);
            $stmt3->execute();
            $fid = $stmt3->fetchAll(PDO::FETCH_COLUMN,0);
            $stmt4 = $this->conn->prepare("SELECT function_unit_id FROM fn_meta WHERE meta_value=:mid");
            $stmt4->bindParam(":mid",$mid);
            $stmt4->execute();
            $fid = array_unique(array_merge($fid,$stmt4->fetchAll(PDO::FETCH_COLUMN,0)));
            foreach($fid as $k=>$v){
                $this->del_fn($v);
            }
            $stmt5 = $this->conn->prepare("DELETE FROM mat WHERE id=:mid");
            $stmt5->bindParam(":mid",$mid);
            $stmt5->execute();
        } catch (Exception $ex) {
            db_error("del_mat", $ex);
        }
    }
    public function del_fn($fid){
        try {
            $stmt = $this->conn->prepare("DELETE FROM fn_print WHERE function_unit_id=:fid");
            $stmt->bindParam(":fid",$fid);
            $stmt->execute();
            $stmt1 = $this->conn->prepare("DELETE FROM finishing WHERE function_unit_id=:fid");
            $stmt1->bindParam(":fid",$fid);
            $stmt1->execute();
            $stmt2 = $this->conn->prepare("DELETE FROM fn_meta WHERE function_unit_id=:fid");
            $stmt2->bindParam(":fid",$fid);
            $stmt2->execute();
            $stmt3 = $this->conn->prepare("DELETE FROM function_unit WHERE id=:fid");
            $stmt3->bindParam(":fid",$fid);
            $stmt3->execute();
        } catch (Exception $ex) {
            db_error("del_fn", $ex);
        }
    }
    public function del_printing($pid){
        try {
            //del fn_print
            $stmt = $this->conn->prepare("DELETE FROM fn_print WHERE process_id=:pid");
            $stmt->bindParam(":pid",$pid);
            $stmt->execute();
            $stmt1 = $this->conn->prepare("DELETE FROM print_process WHERE printing_id=:pid");
            $stmt1->bindParam(":pid",$pid);
            $stmt1->execute();
            $stmt2 = $this->conn->prepare("DELETE FROM printing WHERE id=:pid");
            $stmt2->bindParam(":pid",$pid);
            $stmt2->execute();
        } catch (Exception $ex) {
            db_error("del_printing", $ex);
        }
    }
    public function del_machine($maid){
        try {
            $sql = <<<END_OF_TEXT
                    
END_OF_TEXT;
            $stmt = $this->conn->prepare("DELETE FROM electricity WHERE machine_id=:maid");
            $stmt->bindParam(":maid",$maid);
            $stmt->execute();
            $stmt1 = $this->conn->prepare("DELETE FROM input WHERE test_data_id in (SELECT id FROM test_data WHERE machine_id=:maid)");
            $stmt1->bindParam(":maid",$maid);
            $stmt1->execute();
            $stmt2 = $this->conn->prepare("DELETE FROM output WHERE test_data_id in (SELECT id FROM test_data WHERE machine_id=:maid)");
            $stmt2->bindParam(":maid",$maid);
            $stmt2->execute();
            $stmt3 = $this->conn->prepare("DELETE FROM test_data WHERE machine_id=:maid");
            $stmt3->bindParam(":maid",$maid);
            $stmt3->execute();
            $stmt4 = $this->conn->prepare("DELETE FROM print_process WHERE machine_id=:maid");
            $stmt4->bindParam(":maid",$maid);
            $stmt4->execute();
            $stmt5 = $this->conn->prepare("DELETE FROM finishing WHERE machine_id=:maid");
            $stmt5->bindParam(":maid",$maid);
            $stmt5->execute();
            $stmt6 = $this->conn->prepare("DELETE FROM machine WHERE id=:maid");
            $stmt6->bindParam(":maid",$maid);
            $stmt6->execute();
        } catch (Exception $ex) {
            db_error("del_machine", $ex);
        }
    }
    public function del_user($uid){
        try {
            $stmt = $this->conn->prepare("DELETE FROM user_meta WHERE user_id=:uid");
            $stmt->bindParam(":uid",$uid);
            $stmt->execute();
            $stmt1 = $this->conn->prepare("DELETE FROM user WHERE id=:uid");
            $stmt1->bindParam(":uid",$uid);
            $stmt1->execute();
        } catch (Exception $ex) {
            db_error("del_user", $ex);
        }
    }
}