<?php
__autoload("pdo");
class greenDB extends myDB{
    public function __construct() {
        parent::__construct();
    }
    public function add_transport($name,$max,$ref){
        try {
            $stmt = $this->conn->prepare("INSERT INTO transport VALUES (null,:name,:max,:ref)");
            $stmt->bindParam(":name",$name);
            $stmt->bindParam(":max",$max);
            $stmt->bindParam(":ref",$ref);
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function edit_transport($id,$name,$max,$ref){
        try {
            $stmt = $this->conn->prepare("UPDATE transport SET name=:name,maxload=:max,reference=:ref WHERE id=:id");
            $stmt->bindParam(":name",$name);
            $stmt->bindParam(":max",$max);
            $stmt->bindParam(":ref",$ref);
            $stmt->bindParam(":id",$id);
            $stmt->execute();
            return ($stmt->rowCount()>0?true:false);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function check_transport_name($name,$tid=0){
        try {
            $stmt = $this->conn->prepare("SELECT id FROM transport WHERE name=:name AND id<>:tid");
            $stmt->bindParam(":name",$name);
            $stmt->bindParam(":tid",$tid);
            $stmt->execute();
            return ($stmt->rowCount()>0?false:true);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function add_transport_load($tid,$arrloadef){
        try {
            $stmt = $this->conn->prepare("INSERT INTO transport_ef VALUES (null,:tid,:load,:ef)");
            $stmt->bindParam(":tid",$tid);
            $stmt->bindParam(":load",$load);
            $stmt->bindParam(":ef",$ef);
            foreach($arrloadef AS $k=>$v){
                $load = $k;
                $ef = $v;
                $stmt->execute();
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function edit_tload($arr){
        try {
            $stmt = $this->conn->prepare("UPDATE transport_ef SET tload=:load, ef=:ef WHERE id=:id");
            $stmt->bindParam(":id",$id);
            $stmt->bindParam(":load",$load);
            $stmt->bindParam(":ef",$ef);
            foreach($arr as $k=>$v){
                $id = $k;
                $load = $v[0];
                $ef = $v[1];
                $stmt->execute();
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_transport($tid = null){
        try {
            if(isset($tid)){
                $sql = "SELECT * FROM transport WHERE id=$tid";
            } else {
                $sql = <<<END_OF_TEXT
                    SELECT
                    CONCAT("<a href='transport.php?tid=",id,"' title='Edit'>",name,"</a>"),
                    maxload,reference
                    FROM transport ORDER BY name ASC
END_OF_TEXT;
            }
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return (isset($tid)?$stmt->fetch(PDO::FETCH_ASSOC):$stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_transport_ef($tid){
        try {
            $stmt = $this->conn->prepare("SELECT tload,ef FROM transport_ef WHERE transport_id=:tid");
            $stmt->bindParam(":tid",$tid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_load($tid){
        try {
            $stmt = $this->conn->prepare("SELECT id,tload,ef FROM transport_ef WHERE transport_id=:tid");
            $stmt->bindParam(":tid",$tid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function check_machinecat_name($name,$cid=0){
        try {
            $stmt = $this->conn->prepare("SELECT id FROM machine_cat WHERE name=:name AND id<>:cid");
            $stmt->bindParam(":name",$name);
            $stmt->bindParam(":cid",$cid);
            $stmt->execute();
            return ($stmt->rowCount()>0?false:true);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function add_machine_cat($name,$group,$des){
        try {
            $stmt = $this->conn->prepare("INSERT INTO machine_cat VALUES (null,:name,:mgroup,:des)");
            $stmt->bindParam(":name",$name);
            $stmt->bindParam(":mgroup",$group);
            $stmt->bindParam(":des",$des);
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function edit_machine_cat($cid,$name,$group,$des){
        try {
            $stmt = $this->conn->prepare("UPDATE machine_cat SET name=:name,mgroup=:group,des=:des WHERE id=:cid");
            $stmt->bindParam(":name",$name);
            $stmt->bindParam(":group",$group);
            $stmt->bindParam(":des",$des);
            $stmt->bindParam(":cid",$cid);
            $stmt->execute();
            return ($stmt->rowCount()>0?true:false);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function check_cat_name($name,$cid=0){
        try {
            $stmt = $this->conn->prepare("SELECT id FROM cat WHERE name=:name AND id<>:cid");
            $stmt->bindParam(":name",$name);
            $stmt->bindParam(":cid",$cid);
            $stmt->execute();
            return ($stmt->rowCount()>0?false:true);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function add_cat($name,$des){
        try {
            $stmt = $this->conn->prepare("INSERT INTO cat VALUES (null,:name,:des)");
            $stmt->bindParam(":name",$name);
            $stmt->bindParam(":des",$des);
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function edit_cat($cid,$name,$des){
        try {
            $stmt = $this->conn->prepare("UPDATE cat SET name=:name,des=:des WHERE id=:cid");
            $stmt->bindParam(":name",$name);
            $stmt->bindParam(":des",$des);
            $stmt->bindParam(":cid",$cid);
            $stmt->execute();
            return ($stmt->rowCount()>0?true:false);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_cat($cid=null){
        if(isset($cid)){
            $sql = "SELECT * FROM cat WHERE id=$cid";
        } else {
            $sql = <<<END_OF_TEXT
                    SELECT CONCAT("<a href='cat.php?cid=",id,"' title='Edit'>",name,"</a>"),
                    des,ccat.num
                    FROM cat
                    LEFT JOIN (SELECT cat_id,COUNT(*) AS num FROM mat GROUP BY cat_id) AS ccat ON ccat.cat_id=cat.id
                    
END_OF_TEXT;
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function view_mcat($mcid){
        $stmt = $this->conn->prepare("SELECT * FROM machine_cat WHERE id=:mcid");
        $stmt->bindParam(":mcid",$mcid);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function view_machine_cat(){
        $sql = <<<END_OF_TEXT
SELECT CONCAT("<a href='machine_cat.php?cid=",id,"' title='Edit'>",name,"</a>"),
des,ccat.num
FROM machine_cat AS cat
LEFT JOIN (SELECT machine_cat_id,COUNT(*) AS num FROM machine GROUP BY machine_cat_id) AS ccat ON ccat.machine_cat_id=cat.id
END_OF_TEXT;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function get_cat(){
        $stmt = $this->conn->prepare("SELECT id,name FROM cat ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    public function check_mat_name($name,$mid=0,$coid){
        try {
            $stmt = $this->conn->prepare("SELECT id FROM mat WHERE name=:name AND company_id=:coid AND id<>:mid");
            $stmt->bindParam(":name",$name);
            $stmt->bindParam(":mid",$mid);
            $stmt->bindParam(":coid",$coid);
            $stmt->execute();
            return ($stmt->rowCount()>0?false:true);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_mat($cat=0,$coid){
        $filter = ($cat==0?"":"AND cat_id=$cat");
        $sql = <<<END_OF_TEXT
SELECT 
CONCAT("<a href='mat.php?mid=",mat.id,"'>",mat.name,"</a>"),
unit,ef,reference,cat.name,
IF(ISNULL(calculate_type)
    ,CONCAT("<a href='mat_transport.php?action=add&mid=",mat.id,"' title='Add' class='a-red'>เพิ่ม</a>")
    ,CONCAT("<a href='mat_transport.php?mid=",mat.id,"' title='Edit'>แก้ไข</a>")
)
FROM mat
LEFT JOIN cat ON cat.id=cat_id
LEFT JOIN mat_transport AS mt ON mt.mat_id=mat.id
WHERE company_id=:coid $filter
ORDER BY cat.name ASC,mat.name ASC

END_OF_TEXT;
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":coid",$coid);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function view_matinfo($mid){
        try {
            $stmt = $this->conn->prepare("SELECT * FROM mat WHERE id=:mid");
            $stmt->bindParam(":mid",$mid);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function add_mat($coid,$name,$unit,$ef,$ref,$cat){
        try {
            $stmt = $this->conn->prepare("INSERT INTO mat VALUES (:coid,null,:name,:unit,:ef,:ref,:cat)");
            $stmt->bindParam(":coid",$coid);
            $stmt->bindParam(":name",$name);
            $stmt->bindParam(":unit",$unit);
            $stmt->bindParam(":ef",$ef);
            $stmt->bindParam(":ref",$ref);
            $stmt->bindParam(":cat",$cat);
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function edit_mat($mid,$name,$unit,$ef,$ref,$cat){
        try {
            $stmt = $this->conn->prepare("UPDATE mat SET name=:name,unit=:unit,ef=:ef,reference=:ref,cat_id=:cat WHERE id=:mid");
            $stmt->bindParam(":mid",$mid);
            $stmt->bindParam(":name",$name);
            $stmt->bindParam(":unit",$unit);
            $stmt->bindParam(":ef",$ef);
            $stmt->bindParam(":ref",$ref);
            $stmt->bindParam(":cat",$cat);
            $stmt->execute();
            return ($stmt->rowCount()>0?true:false);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_user($cid=null,$page=null,$perpage=null){
        $off = (isset($perpage)?$perpage*($page-1):0);
        $lim_sql = (isset($perpage)?"LIMIT :lim OFFSET :off":"");
        $filter = (isset($cid)?" WHERE um.meta_value=$cid":"");
        $sql = <<<END_OF_TEXT
SELECT CONCAT("<span class='icon-remove del-user' uid='",user.id,"'></span>"),
CONCAT("<a href='user.php?uid=",user.id,"'>",user.email,"</a>"),
cm.name,
DATE_FORMAT(user.added,'%d-%b-%y'),
CONCAT("<span class='",IF(um1.meta_value<now(),'user-expired',''),"'>",DATE_FORMAT(um1.meta_value,'%d-%b-%y'),"</span>")
FROM user
LEFT JOIN user_meta AS um ON um.user_id=user.id AND um.meta_key='user_company'
LEFT JOIN user_meta AS um1 ON um1.user_id=user.id AND um1.meta_key='user_expired'
LEFT JOIN company as cm ON cm.id=um.meta_value
$filter
ORDER BY cm.name ASC , um1.meta_value DESC
$lim_sql
END_OF_TEXT;
        $stmt = $this->conn->prepare($sql);
        if(isset($perpage)){
            $stmt->bindParam(":lim",$perpage,PDO::PARAM_INT);
            $stmt->bindParam(":off",$off,PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    public function view_uinfo($uid){
        try {
            $stmt = $this->conn->prepare("SELECT id,email FROM user WHERE id=:uid");
            $stmt->bindParam(":uid",$uid);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_umeta($uid){
        try {
            $stmt = $this->conn->prepare("SELECT meta_key,meta_value FROM user_meta WHERE user_id=:uid");
            $stmt->bindParam(":uid",$uid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function check_company_name($name,$cid=0){
        try {
            $stmt = $this->conn->prepare("SELECT id FROM company WHERE name=:name AND id<>:cid");
            $stmt->bindParam(":name",$name);
            $stmt->bindParam(":cid",$cid);
            $stmt->execute();
            return ($stmt->rowCount()>0?false:true);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }   
    }
    public function add_company($name,$email,$tel,$date){
        try {
            $stmt = $this->conn->prepare("INSERT INTO company VALUES (null,:name,:email,:tel,:date)");
            $stmt->bindParam(":name",$name);
            $stmt->bindParam(":email",$email);
            $stmt->bindParam(":tel",$tel);
            $stmt->bindParam(":date",$date);
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function edit_company($cid,$arrinfo){
        try {
            $sql = gen_sql($arrinfo,",","param");
            $stmt = $this->conn->prepare("UPDATE company SET $sql[0] WHERE id=:cid");
            $stmt->bindParam(":cid",$cid);
            foreach($sql[1] AS $k => $v){
                $stmt->bindParam(":$k",$sql[1][$k]);
            }
            $stmt->execute();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_company($cid=null){
        try {
            if(isset($cid)){
                $sql = "SELECT * FROM company WHERE id=:cid";
            } else {
                $sql = <<<END_OF_TEXT
                        SELECT 
                        CONCAT("<a href='company.php?cid=",id,"' title='Edit'>",name,"</a>"),
                        email,tel,added
                        FROM company
END_OF_TEXT;
            }
            $stmt = $this->conn->prepare($sql);
            (isset($cid)?$stmt->bindParam(":cid",$cid):"");
            $stmt->execute();
            return (isset($cid)?$stmt->fetch(PDO::FETCH_ASSOC):$stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function sel_company(){
        try {
            $stmt = $this->conn->prepare("SELECT id,name FROM company");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function check_email($email,$uid=0){
        try {
            $stmt = $this->conn->prepare("SELECT id FROM user WHERE email=:email AND id<>:uid");
            $stmt->bindParam(":email",$email);
            $stmt->bindParam(":uid",$uid);
            $stmt->execute();
            return ($stmt->rowCount()>0?false:true);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }   
    }
    public function add_user($email,$pass,$date){
        try {
            $stmt = $this->conn->prepare("INSERT INTO user VALUES (null,:email,:pass,:date)");
            $stmt->bindParam(":email",$email);
            $stmt->bindParam(":pass",$pass);
            $stmt->bindParam(":date",$date);
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function update_user_meta($uid,$meta){
        try {
            $stmt0 = $this->conn->prepare("SELECT * FROM user_meta WHERE user_id=:uid AND meta_key=:key");
            $stmt0->bindParam(":uid",$uid);
            $stmt0->bindParam(":key",$key);
            $stmt = $this->conn->prepare("UPDATE user_meta SET meta_value=:val WHERE user_id=:uid AND meta_key=:key");
            $stmt->bindParam(":uid",$uid);
            $stmt->bindParam(":key",$key);
            $stmt->bindParam(":val",$val);
            $stmt1 = $this->conn->prepare("INSERT INTO user_meta VALUES (null,:uid,:key,:val)");
            $stmt1->bindParam(":uid",$uid);
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
    public function edit_user($uid,$arrinfo){
        try {
            $sql = gen_sql($arrinfo,",","param");
            $stmt = $this->conn->prepare("UPDATE user SET $sql[0] WHERE id=:uid");
            $stmt->bindParam(":uid",$uid);
            foreach($sql[1] AS $k => $v){
                $stmt->bindParam(":$k",$sql[1][$k]);
            }
            $stmt->execute();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_repass($email){
        try{
            date_default_timezone_set("GMT");
            $endt = date_add(date_create(null),  date_interval_create_from_date_string("23 hours 59 minutes 59 seconds"));
            $end = date_format($endt,"Y-m-d H:i:s");
            $stmt = $this->conn->prepare("SELECT id,added FROM user WHERE email=:email");
            $stmt->bindParam(":email",$email);
            $stmt->execute();
            if($stmt->rowCount()>0){
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $id = $row['id'];
                $p = substr(md5($row['added']),0,7);
                $arrmeta = [
                    "rq" => $p,
                    "rq_expired" => $end
                ];
                $this->update_user_meta($id,$arrmeta);
                return $p;
            } else {
                return false;
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
            return false;
        }
    }
    public function checkr($r){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
um.user_id,
um1.meta_value AS expired
FROM user_meta AS um
LEFT JOIN user_meta AS um1 ON um1.user_id=um.user_id AND um1.meta_key='rq_expired'
WHERE um.meta_value=:r AND um.meta_key='rq'
END_OF_TEXT;
           $stmt = $this->conn->prepare($sql);
           $stmt->bindParam(":r",$r);
           $stmt->execute();
           if($stmt->rowCount()>0){
               $row = $stmt->fetch(PDO::FETCH_ASSOC);
               $uid = $row['user_id'];
               $expt = date_create($row['expired']);
               $now = date_create(null,timezone_open("GMT"));
               if($now<$expt){
                   return $uid;
               } else {
                   return false;
               }
           } else {
               return false;
           }
        } catch (Exception $ex) {
           db_error(__METHOD__, $ex);
           return false;
       }
   }
    public function view_mat_transport(){
        try {
            $sql = <<<END_OF_TEXT
SELECT CONCAT("<a href='mat_transport.php?mid=",mat_id,"' title='Edit'>",mat.name,"</a>"),
calculate_type
FROM mat_transport AS mt
LEFT JOIN mat ON mat.id=mt.mat_id
                    
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_mat($coid,$cat,$wunit=true){
        try {
            $sql = ($wunit?"CONCAT(name,' (',unit,')')":"name");
            $stmt = $this->conn->prepare("SELECT id,$sql FROM mat WHERE company_id=:coid AND cat_id IN ($cat) ORDER BY name");
            $stmt->bindParam(":coid",$coid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_mat_fort($cat){
        try {
            $stmt = $this->conn->prepare("SELECT id,CONCAT(name,' (',unit,')') FROM mat WHERE cat_id IN ($cat) AND id NOT IN (SELECT mat_id FROM mat_transport) ORDER BY name ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_vehicle(){
        try {
            $stmt = $this->conn->prepare("SELECT id,CONCAT(name,' บรรทุกสูงสุด ',maxload,' ตัน') FROM transport");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function add_mat_gas($mid,$type,$gas,$used){
        try {
            $stmt = $this->conn->prepare("INSERT INTO mat_transport VALUES(:mid,:type,:gas,:used,null,null,null,null)");
            $stmt->bindParam(":mid",$mid);
            $stmt->bindParam(":type",$type);
            $stmt->bindParam(":gas",$gas);
            $stmt->bindParam(":used",$used);
            $stmt->execute();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function edit_mat_gas($mid,$type,$gas,$used){
        try {
            $stmt = $this->conn->prepare("UPDATE mat_transport SET calculate_type=:type,gas_type=:gas,gas_used=:used,distance=null,transport_id=null,load_come=null,load_back=null WHERE mat_id=:mid");
            $stmt->bindParam(":mid",$mid);
            $stmt->bindParam(":type",$type);
            $stmt->bindParam(":gas",$gas);
            $stmt->bindParam(":used",$used);
            $stmt->execute();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function add_mat_vehicle($mid,$type,$dis,$tid,$in,$out){
        try {
            $stmt = $this->conn->prepare("INSERT INTO mat_transport VALUES(:mid,:type,null,null,:dis,:tid,:come,:back)");
            $stmt->bindParam(":mid",$mid);
            $stmt->bindParam(":type",$type);
            $stmt->bindParam(":dis",$dis);
            $stmt->bindParam(":tid",$tid);
            $stmt->bindParam(":come",$in);
            $stmt->bindParam(":back",$out);
            $stmt->execute();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function edit_mat_vehicle($mid,$type,$dis,$tid,$in,$out){
        try {
            $stmt = $this->conn->prepare("UPDATE mat_transport SET calculate_type=:type,gas_type=null,gas_used=null,distance=:dis,transport_id=:tid,load_come=:come,load_back=:back WHERE mat_id=:mid");
            $stmt->bindParam(":mid",$mid);
            $stmt->bindParam(":type",$type);
            $stmt->bindParam(":dis",$dis);
            $stmt->bindParam(":tid",$tid);
            $stmt->bindParam(":come",$in);
            $stmt->bindParam(":back",$out);
            $stmt->execute();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_mtinfo($mid){
        try {
            $stmt = $this->conn->prepare("SELECT mat_transport.*,mat.name FROM mat_transport LEFT JOIN mat ON mat.id=mat_id WHERE mat_id=:mid");
            $stmt->bindParam(":mid",$mid);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_machine($coid,$exid=null){
        try {
            $exsql = (isset($exid)?"AND machine_cat_id NOT IN $exid":"");
            $sql = <<<END_OF_TEXT
                    SELECT 
                    CONCAT("<a href='machine.php?maid=",machine.id,"' title='Edit' class='icon-page-edit'></a>"),
                    brand_model,
                    process,
                    allocation_unit,
                    IF(ISNULL(td.id)
                        ,CONCAT("<a href='machine_test.php?action=add&maid=",machine.id,"' title='เพิ่มช้อมูลการทดสอบ' class='a-red'>เพิ่ม</a>")
                        ,CONCAT("<a href='machine_test.php?testid=",td.id,"' title='แก้ไขข้อมูลการทดสอบ'>ปรับ</a>")
                    )
                    FROM machine
                    LEFT JOIN test_data AS td ON td.machine_id=machine.id
                    WHERE company_id=:coid $exsql
                    ORDER BY machine_cat_id ASC, brand_model ASC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":coid",$coid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_design($coid,$mcat){
        try {
            $sql = <<<END_OF_TEXT
                    SELECT 
                    CONCAT("<a href='design.php?maid=",machine.id,"' title='Edit' class='icon-page-edit'></a>"),
                    brand_model,
                    td.date
                    FROM machine
                    LEFT JOIN test_data AS td on td.machine_id=machine.id
                    WHERE company_id=:coid AND machine_cat_id=:mcat
                    ORDER BY td.date DESC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":coid",$coid);
            $stmt->bindParam(":mcat",$mcat);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function add_machine($coid,$brand,$process,$unit,$cat){
        try {
            $stmt = $this->conn->prepare("INSERT INTO machine VALUES (:coid,null,:brand,:process,:unit,:cat)");
            $stmt->bindParam(":coid",$coid);
            $stmt->bindParam(":brand",$brand);
            $stmt->bindParam(":process",$process);
            $stmt->bindParam(":unit",$unit);
            $stmt->bindParam(":cat",$cat);
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function edit_machine($maid,$brand,$process,$unit,$cat){
        try {
            $stmt = $this->conn->prepare("UPDATE machine SET brand_model=:brand,process=:process,allocation_unit=:unit,machine_cat_id=:cat WHERE id=:maid");
            $stmt->bindParam(":brand",$brand);
            $stmt->bindParam(":process",$process);
            $stmt->bindParam(":unit",$unit);
            $stmt->bindParam(":maid",$maid);
            $stmt->bindParam(":cat",$cat);
            $stmt->execute();
            return ($stmt->rowCount()>0?true:false);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function update_mmeta($maid,$meta){
        try {
            $stmt0 = $this->conn->prepare("SELECT * FROM machine_meta WHERE machine_id=:maid AND meta_key=:key");
            $stmt0->bindParam(":maid",$maid);
            $stmt0->bindParam(":key",$key);
            $stmt = $this->conn->prepare("UPDATE machine_meta SET meta_value=:val WHERE machine_id=:maid AND meta_key=:key");
            $stmt->bindParam(":maid",$maid);
            $stmt->bindParam(":key",$key);
            $stmt->bindParam(":val",$val);
            $stmt1 = $this->conn->prepare("INSERT INTO machine_meta VALUES (null,:maid,:key,:val)");
            $stmt1->bindParam(":maid",$maid);
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
    public function add_ele($maid,$aname,$awatt,$anum){
        try {
            $stmt = $this->conn->prepare("INSERT INTO electricity VALUES (null,:maid,:name,:watt,:unit)");
            $stmt->bindParam(":maid",$maid);
            $stmt->bindParam(":name",$name);
            $stmt->bindParam(":watt",$watt);
            $stmt->bindParam(":unit",$num);
            $n = sizeof($aname,0);
            for($i=0;$i<$n;$i++){
                if($aname[$i]==""){
                    continue;
                } else {
                    $name = $aname[$i];
                    $watt = $awatt[$i];
                    $num = $anum[$i];
                    $stmt->execute();
                }
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function edit_ele($aeid,$aname,$awatt,$anum){
        try {
            $stmt = $this->conn->prepare("UPDATE electricity SET name=:name,watt=:watt,unit=:unit WHERE id=:eid");
            $stmt->bindParam(":eid",$eid);
            $stmt->bindParam(":name",$name);
            $stmt->bindParam(":watt",$watt);
            $stmt->bindParam(":unit",$num);
            $n = sizeof($aname,0);
            for($i=0;$i<$n;$i++){
                $eid = $aeid[$i];
                $name = $aname[$i];
                $watt = $awatt[$i];
                $num = $anum[$i];
                $stmt->execute();
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_ele($maid){
        try {
            $stmt = $this->conn->prepare("SELECT * FROM electricity WHERE machine_id=:maid");
            $stmt->bindParam(":maid",$maid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_macinfo($maid){
        $stmt = $this->conn->prepare("SELECT *,td.id AS test_id FROM machine LEFT JOIN test_data AS td ON td.machine_id=machine.id WHERE machine.id=:maid LIMIT 1");
        $stmt->bindParam(":maid",$maid);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function view_mmeta($maid){
        try {
            $stmt = $this->conn->prepare("SELECT meta_key,meta_value FROM machine_meta WHERE machine_id=:maid");
            $stmt->bindParam(":maid",$maid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_mcat($group=null){
        $sql = (isset($group)?"WHERE mgroup IN $group":"");
        $stmt = $this->conn->prepare("SELECT id,name FROM machine_cat $sql");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    public function get_design($coid){
        $stmt = $this->conn->prepare("SELECT id,brand_model FROM machine WHERE company_id=:coid AND machine_cat_id=8");
        $stmt->bindParam(":coid",$coid);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    public function get_machine($coid,$cat=null){
        $sql = (isset($cat)?"AND machine_cat_id=:cat":"");
        $stmt = $this->conn->prepare("SELECT id,CONCAT(brand_model,' (',process,')') FROM machine WHERE company_id=:coid $sql");
        $stmt->bindParam(":coid",$coid);
        (isset($cat)?$stmt->bindParam(":cat",$cat):"");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    public function get_amach($coid){
        try {
            $stmt = $this->conn->prepare("SELECT id,machine_cat_id,CONCAT(brand_model,' (',process,')') AS name FROM machine WHERE company_id=:coid");
            $stmt->bindParam(":coid",$coid);
            $stmt->execute();
            $res = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                $mcat = $row['machine_cat_id'];
                $mid = $row['id'];
                $name = $row['name'];
                $res[$mcat][$mid] = $name;
            }
            return $res;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_machine_aunit(){
        $stmt = $this->conn->prepare("SELECT id,allocation_unit FROM machine");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    public function add_testdata($maid,$date,$user,$input,$output,$idleh,$idlew,$loadh,$loadw){
        try {
            $stmt = $this->conn->prepare("INSERT INTO test_data VALUES (null,:maid,:date,:user,:input,:output,:idleh,:loadh,:idlew,:loadw)");
            $stmt->bindParam(":maid",$maid);
            $stmt->bindParam(":date",$date);
            $stmt->bindParam(":user",$user);
            $stmt->bindParam(":input",$input);
            $stmt->bindParam(":output",$output);
            $stmt->bindParam(":idleh",$idleh);
            $stmt->bindParam(":idlew",$idlew);
            $stmt->bindParam(":loadh",$loadh);
            $stmt->bindParam(":loadw",$loadw);
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function edit_testdata($testid,$arrinfo){
        try {
            $sql = gen_sql($arrinfo,",","param");
            $stmt = $this->conn->prepare("UPDATE test_data SET $sql[0] WHERE id=:testid");
            $stmt->bindParam(":testid",$testid);
            foreach($sql[1] AS $k => $v){
                $stmt->bindParam(":$k",$sql[1][$k]);
            }
            $stmt->execute();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function add_test_mat($testid,$amat,$anum,$input=true){
        try {
            $tb = ($input?"input":"output");
            $stmt = $this->conn->prepare("INSERT INTO $tb VALUES (:testid,:mid,:amount)");
            $stmt->bindParam(":testid",$testid);
            $stmt->bindParam(":mid",$mid);
            $stmt->bindParam(":amount",$amount);
            $n = sizeof($amat,0);
            for($i=0;$i<$n;$i++){
                if($amat[$i]>0){
                    $mid = $amat[$i];
                    $amount = $anum[$i];
                    $stmt->execute();
                }
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function edit_test_mat($testid,$amat,$anum,$input=true){
        try {
            $tb = ($input?"input":"output");
            $stmt = $this->conn->prepare("DELETE FROM $tb WHERE test_data_id=:testid");
            $stmt->bindParam(":testid",$testid);
            $stmt->execute();
            $this->add_test_mat($testid, $amat, $anum, $input);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_machine_test(){
        $sql = <<<END_OF_TEXT
                SELECT 
                CONCAT("<a href='machine_test.php?testid=",td.id,"' title='Edit'>",ma.brand_model,"</a>"),
                ma.process,
                date,
                user
                FROM test_data AS td
                LEFT JOIN machine AS ma ON ma.id=machine_id
END_OF_TEXT;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function view_testinfo($testid){
        try{
            $sql = <<<END_OF_TEXT
                    SELECT td.*,
                        CONCAT(ma.brand_model," (",ma.process,")") AS mach,ma.allocation_unit
                            FROM test_data AS td
                                LEFT JOIN machine AS ma ON ma.id=td.machine_id 
                                    WHERE td.id=:testid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":testid",$testid);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_test_mat($testid,$input=true){
        try {
            $tb = ($input?"input":"output");
            $stmt = $this->conn->prepare("SELECT mat_id,amount FROM $tb WHERE test_data_id=:testid");
            $stmt->bindParam(":testid",$testid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_fn($coid,$month=0,$type=0,$comp=0,$search){
        try {
            //CONCAT("<span class='icon-remove del-fn' fid='",fn.id,"'></span>"),
            $filter = "";
            $join = "";
            if($month!="0"){
                $filter .= "AND DATE_FORMAT(added,'%Y-%m')='$month'";
            }
            if($type!="0"){
                $filter .= " AND fn.type='$type'";
            }
            if($comp!="0"){
                $join .= "LEFT JOIN fn_meta AS mt2 ON mt2.function_unit_id=fn.id AND mt2.meta_key='sub_comp'";
                $filter .= " AND SUBSTRING(mt2.meta_value,1,1)='$comp'";
            }
            if($search!=""){
                $filter .= " AND name LIKE '%$search%'";
            }
            $sql = <<<END_OF_TEXT
SELECT

CONCAT("<a href='calculate.php?fid=",fn.id,"' title='Edit' class='icon-page-edit'></a>"),
CONCAT("<a href='print.php?fid=",fn.id,"' title='พิมพ์' class='icon-print'></a>"),
CONCAT("<a href='calculate.php?action=res&fid=",fn.id,"' title='คำนวณ'><div class='blue-but'>คำนวณ</div></a>"),
name,FORMAT(amount,0),
FORMAT(mt.meta_value,2),
FORMAT(mt1.meta_value,3),
added,
CONCAT("<a href='' copy-id='",fn.id,"' title='Copy' class='copy-fn'>Copy</a>")
FROM function_unit AS fn
LEFT JOIN fn_meta AS mt ON mt.function_unit_id=fn.id AND mt.meta_key='job_carbon'
LEFT JOIN fn_meta AS mt1 ON mt1.function_unit_id=fn.id AND mt1.meta_key='carbon_per_unit'
$join
WHERE fn.company_id=:coid
$filter
ORDER BY added DESC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":coid",$coid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_fn_meta($fid){
        try{
            $stmt = $this->conn->prepare("SELECT meta_key,meta_value FROM fn_meta WHERE function_unit_id=:fid");
            $stmt->bindParam(":fid",$fid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_fn_csv($coid,$month=0){
        try {
            //
            if($month!=0){
                $filter = "AND DATE_FORMAT(added,'%Y-%m')='$month'";
            } else {
                $filter = "";
            }
            $sql = <<<END_OF_TEXT
SELECT
name,type,amount,page,
CAST(mt.meta_value AS DECIMAL(10,3)),
CAST(mt1.meta_value AS DECIMAL(10,3)),
added
FROM function_unit AS fn
LEFT JOIN fn_meta AS mt ON mt.function_unit_id=fn.id AND mt.meta_key='job_carbon'
LEFT JOIN fn_meta AS mt1 ON mt1.function_unit_id=fn.id AND mt1.meta_key='carbon_per_unit'
WHERE fn.company_id=:coid
$filter
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":coid",$coid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_NUM);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_fn_month($coid){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
DATE_FORMAT(added,'%Y-%m'),DATE_FORMAT(added,'%b-%y') AS month 
FROM function_unit 
WHERE company_id=:coid
GROUP BY month ORDER BY added ASC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":coid",$coid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_fn_type($coid){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
type
FROM function_unit 
WHERE company_id=:coid
GROUP BY type ORDER BY added ASC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":coid",$coid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN,0);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_finfo($fid){
        try {
            $sql = <<<END_OF_TEXT
                    SELECT
                    * FROM function_unit WHERE id=:fid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":fid",$fid);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_fn_print($fid){
        try {
            $sql = <<<END_OF_TEXT
                    SELECT * FROM fn_print WHERE function_unit_id=:fid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":fid",$fid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function add_fn($coid,$name,$type,$amount,$page){
        try {
            $added = date_format(date_create(null,timezone_open("Asia/Bangkok")),"Y-m-d");
            $stmt = $this->conn->prepare("INSERT INTO function_unit VALUES (:coid,null,:name,:type,:amount,:page,:added)");
            $stmt->bindParam(":coid",$coid);
            $stmt->bindParam(":name",$name);
            $stmt->bindParam(":type",$type);
            $stmt->bindParam(":amount",$amount);
            $stmt->bindParam(":page",$page);
            $stmt->bindParam(":added",$added);
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function edit_fn($fid,$arrinfo){
        try {
            $sql = gen_sql($arrinfo,",","param");
            $stmt = $this->conn->prepare("UPDATE function_unit SET $sql[0] WHERE id=:fid");
            $stmt->bindParam(":fid",$fid);
            foreach($sql[1] AS $k => $v){
                $stmt->bindParam(":$k",$sql[1][$k]);
            }
            $stmt->execute();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function update_fn_meta($fid,$meta){
        try {
            $stmt0 = $this->conn->prepare("SELECT * FROM fn_meta WHERE function_unit_id=:fid AND meta_key=:key");
            $stmt0->bindParam(":fid",$fid);
            $stmt0->bindParam(":key",$key);
            $stmt = $this->conn->prepare("UPDATE fn_meta SET meta_value=:val WHERE function_unit_id=:fid AND meta_key=:key");
            $stmt->bindParam(":fid",$fid);
            $stmt->bindParam(":key",$key);
            $stmt->bindParam(":val",$val);
            $stmt1 = $this->conn->prepare("INSERT INTO fn_meta VALUES (null,:fid,:key,:val)");
            $stmt1->bindParam(":fid",$fid);
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
    public function add_print($coid,$name){
        try {
            $stmt = $this->conn->prepare("INSERT INTO printing VALUES (:coid,null,:name)");
            $stmt->bindParam(":coid",$coid);
            $stmt->bindParam(":name",$name);
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function add_process($pid,$amaid,$aseq,$amult,$atransit){
        try {
            $stmt = $this->conn->prepare("INSERT INTO print_process VALUES (:pid,:maid,:seq,:mult,:transit)");
            $stmt->bindParam(":maid",$maid);
            $stmt->bindParam(":pid",$pid);
            $stmt->bindParam(":seq",$seq);
            $stmt->bindParam(":mult",$mult);
            $stmt->bindParam(":transit",$transit);
            $n = sizeof($amaid,0);
            for($i=0;$i<$n;$i++){
                $maid = $amaid[$i];
                $seq = $aseq[$i];
                $mult = $amult[$i];
                $transit = (isset($atransit[$i])?$atransit[$i]:"");
                $stmt->execute();
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function edit_process($pid,$amaid,$aseq,$amult,$atransit){
        try {
            $stmt = $this->conn->prepare("DELETE FROM print_process WHERE printing_id=:pid");
            $stmt->bindParam(":pid",$pid);
            $stmt->execute();
            $this->add_process($pid,$amaid,$aseq,$amult,$atransit);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function edit_print($pid,$arrinfo){
        try {
            $sql = gen_sql($arrinfo,",","param");
            $stmt = $this->conn->prepare("UPDATE printing SET $sql[0] WHERE id=:pid");
            $stmt->bindParam(":pid",$pid);
            foreach($sql[1] AS $k => $v){
                $stmt->bindParam(":$k",$sql[1][$k]);
            }
            $stmt->execute();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_comp($coid){
        $sql = <<<END_OF_TEXT
                SELECT 
                id,name
                FROM printing
                WHERE company_id=:coid
END_OF_TEXT;
        $sql1 = <<<END_OF_TEXT
                SELECT machine_id,sequence,
                CONCAT(mcat.name," (",ma.brand_model,")") AS proc
                FROM print_process AS pc
                LEFT JOIN machine AS ma ON ma.id=machine_id
                LEFT JOIN machine_cat AS mcat ON mcat.id=ma.machine_cat_id
                WHERE printing_id=:pid
                ORDER BY sequence ASC
END_OF_TEXT;
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":coid",$coid);
        $stmt->execute();
        $stmt1 = $this->conn->prepare($sql1);
        $stmt1->bindParam(":pid",$pid);
        $res = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $pid = $row['id'];
            $stmt1->execute();
            $r = $stmt1->fetchALL(PDO::FETCH_COLUMN,2);
            $res[] = [
                "edit" => "<a href='process.php?pid=$pid' title='Edit' class='icon-page-edit'></a>",
                "name" => $row['name'],
                "process" => implode("=>",$r)
            ];
        }
        return $res;
    }
    public function view_pinfo($pid){
        $sql = <<<END_OF_TEXT
                SELECT *
                FROM printing WHERE id=:pid
END_OF_TEXT;
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":pid",$pid);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function view_process($pid){
        $sql = <<<END_OF_TEXT
                SELECT sequence,machine_id,input_mult,transit_info
                FROM print_process WHERE printing_id=:pid ORDER BY sequence ASC
END_OF_TEXT;
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":pid",$pid);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function get_print($coid){
        try {
            $stmt = $this->conn->prepare("SELECT id,name FROM printing WHERE company_id=:coid");
            $stmt->bindParam(":coid",$coid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function del_fn_print_meta($fpid){
        try {
            $stmt = $this->conn->prepare("DELETE FROM fn_print_meta WHERE fn_print_id=:fpid");
            $stmt->bindParam(":fpid",$fpid);
            $stmt->execute();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function add_fn_print($fid,$apid,$aname,$apaper,$aweight,$am_width,$am_length,$aspp,$awidth,$alength,$asheet,$ainput,$apt,$aplate){
        try {
            $stmt = $this->conn->prepare("INSERT INTO fn_print VALUES(null,:fid,:pid,:name,:type,:weight,:m_width,:m_length,:sheet_plate,:width,:length,:sheet_unit,:input,:pt,:plate)");
            $stmt->bindParam(":fid",$fid);
            $stmt->bindParam(":pid",$pid);
            $stmt->bindParam(":name",$name);
            $stmt->bindParam(":type",$type);
            $stmt->bindParam(":weight",$weight);
            $stmt->bindParam(":m_width",$m_width);
            $stmt->bindParam(":m_length",$m_length);
            $stmt->bindParam(":sheet_plate",$sheet_plate);
            $stmt->bindParam(":width",$width);
            $stmt->bindParam(":length",$length);
            $stmt->bindParam(":sheet_unit",$sheet_unit);
            $stmt->bindParam(":input",$input);
            $stmt->bindParam(":pt",$plate_type);
            $stmt->bindParam(":plate",$plate);
            $n = sizeof($apid,0);
            for($i=0;$i<$n;$i++){
                if($apid[$i]>0){
                    $pid = $apid[$i];
                    $name = $aname[$i];
                    $type = $apaper[$i];
                    $weight = $aweight[$i];
                    $m_width = $am_width[$i];
                    $m_length = $am_length[$i];
                    $sheet_plate = $aspp[$i];
                    $width = $awidth[$i];
                    $length = $alength[$i];
                    $sheet_unit = $asheet[$i];
                    $input = $ainput[$i];
                    $plate_type = $apt[$i];
                    $plate = $aplate[$i];
                    $stmt->execute();
                }
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function edit_fn_print($fid,$apid,$aname,$apaper,$aweight,$am_width,$am_length,$aspp,$awidth,$alength,$asheet,$ainput,$apt,$aplate){
        try {
            $stmt = $this->conn->prepare("DELETE FROM fn_print WHERE function_unit_id=:fid");
            $stmt->bindParam(":fid",$fid);
            $stmt->execute();
            $this->add_fn_print($fid,$apid,$aname,$apaper,$aweight,$am_width,$am_length,$aspp,$awidth,$alength,$asheet,$ainput,$apt,$aplate);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function add_finish($fid,$amach,$aseq,$atran){
        try {
            $stmt = $this->conn->prepare("INSERT INTO finishing VALUES (:fid,:maid,:seq,:transit)");
            $stmt->bindParam(":maid",$maid);
            $stmt->bindParam(":fid",$fid);
            $stmt->bindParam(":seq",$seq);
            $stmt->bindParam(":transit",$transit);
            $n = sizeof($amach,0);
            for($i=0;$i<$n;$i++){
                $maid = $amach[$i];
                $seq = $aseq[$i];
                $transit = (isset($atran[$i])?$atran[$i]:"");
                $stmt->execute();
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function edit_finish($fid,$amach,$aseq,$atran){
        try {
            $stmt = $this->conn->prepare("DELETE FROM finishing WHERE function_unit_id=:fid");
            $stmt->bindParam(":fid",$fid);
            $stmt->execute();
            $this->add_finish($fid, $amach, $aseq,$atran);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_fin_process($fid){
        try {
            $stmt = $this->conn->prepare("SELECT sequence,machine_id FROM finishing WHERE function_unit_id=:fid");
            $stmt->bindParam(":fid",$fid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    
    public function mach_ele_kwh($maid,$hour){
        try {
            $stmt = $this->conn->prepare("SELECT SUM(watt*unit) AS wn FROM electricity WHERE machine_id=:maid");
            $stmt->bindParam(":maid",$maid);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_NUM)[0]*$hour/1000;
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_ef($tdid,$ratio,$input=true){
        try {
            $tb = ($input?"input":"output");
            $sql = <<<END_OF_TEXT
SELECT io.mat_id,mat.name AS material,mat.unit,ROUND(amount*:ratio,5) AS amount
FROM $tb AS io 
LEFT JOIN mat ON mat.id=io.mat_id
WHERE test_data_id=:tdid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":ratio",$ratio);
            $stmt->bindParam(":tdid",$tdid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_ef($mid,$amount){
        try {
            $sql = <<<END_OF_TEXT
SELECT mat.name AS material,mat.unit,:amount AS amount,mat.ef,ROUND(mat.ef*:amount,5) AS material_carbon,
calculate_type,
mat2.name AS gas,mat2.ef AS gas_ef,mt.gas_used AS liter_per_kg,
tr.name,mt.load_come,mt.load_back,distance,trc.ef AS ef_come,trb.ef AS ef_back, 
IF(calculate_type='gas',ROUND(:amount*mt.gas_used*mat2.ef,5),ROUND((:amount*distance/1000)*(trc.ef+trb.ef/(tr.maxload)),5)) AS transit_carbon
FROM mat
LEFT JOIN mat_transport AS mt ON mt.mat_id=mat.id
LEFT JOIN mat AS mat2 ON mat2.id=mt.gas_type
LEFT JOIN transport as tr ON tr.id=mt.transport_id
LEFT JOIN transport_ef AS trc ON trc.transport_id=mt.transport_id AND trc.tload=mt.load_come
LEFT JOIN transport_ef AS trb ON trb.transport_id=mt.transport_id AND trb.tload=mt.load_back
WHERE mat.id=:mid;
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":mid",$mid);
            $stmt->bindParam(":amount",$amount);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_print_mach($pid){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
machine_id,sequence,mcat.name,ma.machine_cat_id,input_mult,
ma.brand_model,ma.process,ma.allocation_unit
FROM print_process
LEFT JOIN machine AS ma ON ma.id=machine_id
LEFT JOIN machine_cat AS mcat ON mcat.id=ma.machine_cat_id
WHERE printing_id=:pid AND machine_id>0
ORDER BY sequence ASC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":pid",$pid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function view_testdata($maid){
        try {
            //get last test data
            $stmt = $this->conn->prepare("SELECT * FROM test_data WHERE machine_id=:maid ORDER BY date DESC LIMIT 1");
            $stmt->bindParam(":maid",$maid);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_carbon($fid){
        try {
            $sql = <<<END_OF_TEXT
SELECT pr.id ,mt.meta_key,mt.meta_value
FROM fn_print AS pr 
LEFT JOIN fn_print_meta AS mt ON mt.fn_print_id=pr.id
WHERE function_unit_id=:fid
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":fid",$fid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_comp($fid){
        try {
            $sql = <<<END_OF_TEXT
SELECT count(id) FROM fn_print WHERE function_unit_id=:fid GROUP BY function_unit_id
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":fid",$fid);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_NUM)[0];
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_comp_detail($fid){
        try {
            $sql = <<<END_OF_TEXT
SELECT 
fn.id,process_id,fn.name,paper_type,weight,m_width,m_length,width,length,input,plate_type,plate,sheet_per_plate,sheet_per_unit,
mat.name as paper,
pt.name AS process_name,
1 AS mult
FROM fn_print as fn
LEFT JOIN mat ON mat.id = fn.paper_type
LEFT JOIN printing AS pt ON pt.id=fn.process_id
WHERE function_unit_id=:fid
ORDER BY fn.id ASC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":fid",$fid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_finishing($fid){
        try {
            //คำนวณทำเล่ม
    $sql = <<<END_OF_TEXT
SELECT fin.machine_id,ma.brand_model,mcat.name,ma.process,ma.allocation_unit,fn.page,fn.amount,ma.machine_cat_id
FROM finishing as fin
LEFT JOIN function_unit AS fn on fn.id=fin.function_unit_id
LEFT JOIN machine AS ma ON ma.id=fin.machine_id
LEFT JOIN machine_cat AS mcat ON mcat.id=ma.machine_cat_id
WHERE function_unit_id=:fid AND fin.machine_id>0
ORDER BY sequence ASC
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":fid",$fid);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }   
    }
    public function check_mat($mid,$coid){
        $stmt = $this->conn->prepare("SELECT id FROM mat WHERE id=:mid AND company_id=:coid");
        $stmt->bindParam(":mid",$mid);
        $stmt->bindParam(":coid",$coid);
        $stmt->execute();
        if($stmt->rowCount()>0){
            return true;
        } else {
            return false;
        }
    }
    public function check_mach($maid,$coid){
        $stmt = $this->conn->prepare("SELECT id FROM machine WHERE id=:maid AND company_id=:coid");
        $stmt->bindParam(":maid",$maid);
        $stmt->bindParam(":coid",$coid);
        $stmt->execute();
        if($stmt->rowCount()>0){
            return true;
        } else {
            return false;
        }
    }
    public function check_process($pid,$coid){
        $stmt = $this->conn->prepare("SELECT id FROM printing WHERE id=:pid AND company_id=:coid");
        $stmt->bindParam(":pid",$pid);
        $stmt->bindParam(":coid",$coid);
        $stmt->execute();
        if($stmt->rowCount()>0){
            return true;
        } else {
            return false;
        }
    }
    public function check_fn($fid,$coid){
        $stmt = $this->conn->prepare("SELECT id FROM function_unit WHERE id=:fid AND company_id=:coid");
        $stmt->bindParam(":fid",$fid);
        $stmt->bindParam(":coid",$coid);
        $stmt->execute();
        if($stmt->rowCount()>0){
            return true;
        } else {
            return false;
        }
    }
    public function check_testid($testid,$coid){
        $sql = <<<END_OF_TEXT
SELECT td.id 
FROM test_data AS td 
LEFT JOIN machine AS ma ON ma.id=td.machine_id 
WHERE td.id=:testid AND company_id=:coid
END_OF_TEXT;
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":testid",$testid);
        $stmt->bindParam(":coid",$coid);
        $stmt->execute();
        if($stmt->rowCount()>0){
            return true;
        } else {
            return false;
        }
    }
    public function view_exid($coid){
        try {
            $stmt = $this->conn->prepare("SELECT id FROM function_unit WHERE company_id=:coid LIMIT 1");
            $stmt->bindParam(":coid",$coid);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC)['id'];
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function copy_fn($fid,$nfid){
        try {
            $sql = <<<END_OF_TEXT
CREATE TEMPORARY TABLE tmp1 SELECT * FROM fn_meta WHERE function_unit_id=$fid;
UPDATE tmp1 SET id = NULL, function_unit_id=$nfid;
INSERT INTO fn_meta SELECT * FROM tmp1;
DROP TEMPORARY TABLE IF EXISTS tmp1;
                    
CREATE TEMPORARY TABLE tmp1 SELECT * FROM fn_print WHERE function_unit_id=$fid;
UPDATE tmp1 SET id = NULL, function_unit_id=$nfid;
INSERT INTO fn_print SELECT * FROM tmp1;
DROP TEMPORARY TABLE IF EXISTS tmp1;
                    
CREATE TEMPORARY TABLE tmp1 SELECT * FROM finishing WHERE function_unit_id=$fid;
UPDATE tmp1 SET function_unit_id=$nfid;
INSERT INTO finishing SELECT * FROM tmp1;
DROP TEMPORARY TABLE IF EXISTS tmp1;
END_OF_TEXT;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
    public function get_lastmonth($coid){
        try {
            $stmt = $this->conn->prepare("SELECT DATE_FORMAT(added,'%Y-%m') FROM function_unit WHERE company_id=:coid ORDER BY added DESC LIMIT 1");
            $stmt->bindParam(":coid",$coid);
            $stmt->execute();
            if($stmt->rowCount()>0){
                return $stmt->fetch(PDO::FETCH_NUM)[0];
            } else {
                return 0;
            }
        } catch (Exception $ex) {
            db_error(__METHOD__, $ex);
        }
    }
}
