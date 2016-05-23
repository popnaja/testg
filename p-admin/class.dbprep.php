<?php
class dbprep{
    private $conn;
    public function __construct() {
        $this->conn = dbConnect(DB_GREEN);
    }
    public function prep_new_company($oid,$coid){
        ini_set('max_execution_time', 6000);
        include_once("class.pdo_g.php");
        $db = new greenDB();

        //copy mat
        $mat = $this->get_mat($oid);
        foreach($mat as $k=>$v){
            $mid = $db->insert_data("mat",array($coid, null,$v['name'], $v['unit'], $v['ef'], $v['cat_id'], $v['ref_id'],$v['evidence']));
            if($v['calculate_type']=="vehicle_distance"){
                $db->add_mat_vehicle($mid, $v['calculate_type'], $v['distance'], $v['transport_id'], $v['load_come'], $v['load_back']);
            } else {
                $db->add_mat_gas($mid, $v['calculate_type'], $v['gas_type'], $v['gas_used']);
            }
        }
        
        //copy machine
        $mach = $this->get_mach($oid);
        foreach($mach as $k=>$v){
            $omaid = $v['machine_id'];
            $otid = $v['id'];
            $maid = $db->add_machine($coid, $v['brand_model'], $v['process'], $v['allocation_unit'], $v['machine_cat_id']);
            $testid = $db->add_testdata($maid, date("Y-m-d"), "admin", $v['input'], $v['output_ok'], $v['idle_min'], $v['idle_kwh'], $v['load_min'], $v['load_kwh']);
            $this->copy_ele($omaid, $maid);
            $this->copy_inout($otid,$testid,$coid);
        }
        
        //copy print
        $print = $this->get_print($oid);
        foreach($print as $k=>$v){
            $ori_printid = $v['id'];
            $pid = $db->add_print($coid, $v['name']);
            $this->copy_process($ori_printid, $pid, $coid);
        }
        
        //create test job
        
    }
    public function get_mat($coid){
        try {
            $sql = <<<END_OF_TEXT
SELECT name,unit,ef,reference,cat_id,mt.* FROM greendb.mat 
LEFT JOIN mat_transport AS mt ON mt.mat_id=mat.id
WHERE company_id=:coid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":coid",$coid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error("get_mat", $ex);
        }
    }
    private function get_mach($coid){
        try {
            $sql = <<<END_OF_TEXT
SELECT brand_model,process,allocation_unit,machine_cat_id,
                    td.*
                    FROM machine AS ma
                    LEFT JOIN test_data AS td on td.machine_id=ma.id
                    WHERE company_id=:coid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":coid",$coid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error("get_mach", $ex);
        }
    }
    private function get_print($coid){
        try {
            $sql = <<<END_OF_TEXT
SELECT id,name
FROM printing
WHERE company_id=:coid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":coid",$coid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error("get_print", $ex);
        }
    }
    private function copy_inout($otid,$testid,$coid){
        try {
            $sql = <<<END_OF_TEXT
CREATE TEMPORARY TABLE tmp2 
SELECT test_data_id,m2.id AS mat_id,amount
FROM input 
LEFT JOIN mat ON mat.id=mat_id
LEFT JOIN mat AS m2 ON m2.name=mat.name AND m2.company_id=$coid
WHERE test_data_id=$otid;
UPDATE tmp2 SET test_data_id=$testid;
INSERT INTO input SELECT * FROM tmp2;
DROP TEMPORARY TABLE IF EXISTS tmp2;
                    
CREATE TEMPORARY TABLE tmp3 
SELECT test_data_id,m2.id AS mat_id,amount
FROM output
LEFT JOIN mat ON mat.id=mat_id
LEFT JOIN mat AS m2 ON m2.name=mat.name AND m2.company_id=$coid
WHERE test_data_id=$otid;
UPDATE tmp3 SET test_data_id=$testid;
INSERT INTO output SELECT * FROM tmp3;
DROP TEMPORARY TABLE IF EXISTS tmp3;
END_OF_TEXT;
            $this->conn->exec($sql);
        } catch (Exception $ex) {
            db_error("copy_inout", $ex);
        }
    }
    private function copy_ele($omaid,$nmaid){
        try {
            //$this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);
            $sql = <<<END_OF_TEXT
CREATE TEMPORARY TABLE tmptable_2 SELECT * FROM electricity WHERE machine_id=$omaid;
UPDATE tmptable_2 SET id = NULL, machine_id=$nmaid;
INSERT INTO electricity SELECT * FROM tmptable_2;
DROP TEMPORARY TABLE IF EXISTS tmptable_2;
END_OF_TEXT;
            $this->conn->exec($sql);
        } catch (Exception $ex) {
            db_error("copy_ele", $ex);
        }
    }
    private function copy_process($ori,$new,$coid){
        try {
            //$this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);
            $sql = <<<END_OF_TEXT
CREATE TEMPORARY TABLE tmp4
    SELECT printing_id,IF(machine_id=0,0,ma2.id)AS machine_id,sequence,input_mult,transit_info 
    FROM print_process AS pp
    LEFT JOIN machine AS ma on ma.id=machine_id
    LEFT JOIN machine AS ma2 on ma2.brand_model=ma.brand_model AND ma2.company_id=$coid
    WHERE printing_id=$ori;
UPDATE tmp4 SET printing_id=$new;
INSERT INTO print_process SELECT * FROM tmp4;
DROP TEMPORARY TABLE IF EXISTS tmp4;
END_OF_TEXT;
            $this->conn->exec($sql);
        } catch (Exception $ex) {
            db_error("copy_ele", $ex);
        }
    }
}

