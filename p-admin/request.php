<?php
session_start();
include_once("myfunction.php");
__autoload("pdo");
__autoload("pdo_g");
if(!$_POST){
    header("location:".ROOTS);
}
$req = filter_input(INPUT_POST,'request',FILTER_SANITIZE_STRING);
$dbm = new myDb();
$db = new greenDB();
if($req == "add_msg"){
    //post contact
    $ct = new myContact();
    date_default_timezone_set("Asia/Bangkok");
    $added = date("Y-m-d H:i:s");
    $id = $ct->add_contact($_POST['name'], "", $_POST['tel'], $_POST['msg'], $added);
    //alert email to admin
    include_once("email_function.php");
    //send email to admin
    //admin_alert_email("",$_POST['name'],$_POST['tel'], $_POST['msg']);
    alert_w_netdesign("", $_POST['name'], $_POST['tel'], $_POST['msg']);
    //show msg
    echo json_encode(["myOK","Message","Thank you, we will reply back ASAP."]);
} else if($req == "login"){
    //check username and password
    $uid = $dbm->check_user($_POST['email'], md5($_POST['z']));
    if($uid==false){
        $_SESSION['error'] = "Email or Password is incorrected.";
        header("location:".$_POST['error_direct']);
    } else {
        $umeta = $dbm->view_umeta($uid);
        $today = date_format(date_create(null,timezone_open("Asia/Bangkok")),"Y-m-d");
        if($umeta['user_expired']<$today){
            $_SESSION['error'] = "รหัสผ่านหมดอายุ โปรดติดต่อเจ้าหน้าที่ 061-864-8641";
            header("location:".$_POST['error_direct']);
        } else if(isset($umeta['l_status'])&&$umeta['l_status']!=""){
            $now = date_create(null,timezone_open("Asia/Bangkok"));
            $last = date_create($umeta['l_status'],timezone_open("Asia/Bangkok"));
            $interval = $now->format("U")-$last->format("U");
            if($interval/60<3){
                $_SESSION['error'] = "บัญญชีนี้ถูกใช้งานอยู่ กรุณาลองใหม่อีกครั้งหลังจาก 3 นาที";
                header("location:".$_POST['error_direct']);
            } else {
                $_SESSION['rms_user'] = $uid;
                $_SESSION['rms_l'] = $umeta['user_level'];
                $_SESSION['rms_c'] = $umeta['user_company'];
                header("location:".$_POST['redirect']);
            }
        } else {
            $_SESSION['rms_user'] = $uid;
            $_SESSION['rms_l'] = $umeta['user_level'];
            $_SESSION['rms_c'] = $umeta['user_company'];
            header("location:".$_POST['redirect']);
        }
    }
} else if($req == "update_u_log"){
    $time = date_format(date_create(null,timezone_open("Asia/Bangkok")),"Y-m-d H:i:s");
    $meta = array(
        "l_status"=>$time
    );
    $db->update_user_meta($_POST['uid'], $meta);
    echo json_encode("");
} else if($req == "add_company"){
    //check name
    if(!$db->check_company_name($_POST['name'])){
        //name dup
        $_SESSION['error'] = "Name ซ้ำโปรดลองชื่อใหม่";
        header("location:".$_POST['redirect']);
    } else {
        //add company
        $date = date_format(date_create(null,timezone_open("Asia/Bangkok")),"Y-m-d H:i:s");
        $cid = $db->add_company($_POST['name'],$_POST['email'],$_POST['tel'],$date);

        /*prepdata
        __autoload("dbprep");
        $pdb = new dbprep();
        $pdb->prep_new_company(1, $cid);
         * 
         */
        
        $_SESSION['message'] = "เพิ่ม Company ใหม่สำเร็จ";
        header("location:".$_POST['redirect']);
    }
} else if($req == "edit_company"){
    //check name
    if(!$db->check_company_name($_POST['name'],$_POST['cid'])){
        //name dup
        $_SESSION['error'] = "Name ซ้ำโปรดลองชื่อใหม่";
        header("location:".$_POST['redirect']);
    } else {
        //edit user
        $arrdata = array(
            "name" => $_POST['name'],
            "email" => $_POST['email'],
            "tel" => $_POST['tel']
        );
        $db->edit_company($_POST['cid'],$arrdata);
        
        //update meta
        $meta = array(
            "ele_type" => $_POST['ele_type'],
            "paper_waste" => $_POST['paper_waste'],
            "plate_waste" => $_POST['plate_waste'],
            "plate_type" => $_POST['plate_type']
        );
        $db->update_meta("company_meta", "company_id", $_POST['cid'], $meta);
        $_SESSION['message'] = "แก้ไข Company สำเร็จ";
        header("location:".$_POST['redirect']);
    }
} else if($req == "add_new_transport"){
    //check name
    if(!$db->check_transport_name($_POST['name'])){
        //name dup
        $_SESSION['error'] = "ชื่อซ้ำโปรดลองชื่อใหม่";
        header("location:".$_POST['redirect']);
    } else {
        //add transport
        $tid = $db->add_transport($_POST['name'], $_POST['maxload'], $_POST['ref']);
        for($i=0;$i<4;$i++){
            $arrloadef[$_POST['load'][$i]] = $_POST['ef'][$i];
        }
        //add load
        $db->add_transport_load($tid, $arrloadef);
        //message
        $_SESSION['message'] = "สร้างรูปแบบการขนส่งใหม่สำเร็จ";
        header("location:".$_POST['redirect']);
    }
} else if($req == "edit_transport"){
    //check name
    if(!$db->check_transport_name($_POST['name'],$_POST['tid'])){
        //name dup
        $_SESSION['error'] = "ชื่อซ้ำโปรดลองชื่อใหม่";
        header("location:".$_POST['redirect']);
    } else {
        //edit transport
        $tid = $db->edit_transport($_POST['tid'],$_POST['name'], $_POST['maxload'], $_POST['ref']);
        for($i=0;$i<4;$i++){
            $arr[$_POST['lid'][$i]] = [$_POST['load'][$i],$_POST['ef'][$i]];
        }
        //edit load
        $db->edit_tload($arr);
        //message
        $_SESSION['message'] = "แก้ไขการขนส่งสำเร็จ";
        header("location:".$_POST['redirect']);
    }
} else if($req == "add_mat_cat"){
    //check name
    if(!$db->check_cat_name($_POST['name'])){
        //name dup
        $_SESSION['error'] = "ชื่อซ้ำโปรดลองชื่อใหม่";
        header("location:".$_POST['redirect']);
    } else {
        //add
        $db->add_cat($_POST['name'],$_POST['des']);
        $_SESSION['message'] = "เพิ่มแคดตากอรี่ใหม่สำเร็จ";
        header("location:".$_POST['redirect']);
    }
} else if($req == "edit_mat_cat"){
    //check name
    if(!$db->check_cat_name($_POST['name'],$_POST['cid'])){
        //name dup
        $_SESSION['error'] = "ชื่อซ้ำโปรดลองชื่อใหม่";
        header("location:".$_POST['redirect']);
    } else {
        //edit
        $db->edit_cat($_POST['cid'],$_POST['name'],$_POST['des']);
        $_SESSION['message'] = "แก้ไขแคดตากอรี่สำเร็จ";
        header("location:".$_POST['redirect']);
    }
} else if($req == "add_machine_cat"){
    //check name
    if(!$db->check_machinecat_name($_POST['name'])){
        //name dup
        $_SESSION['error'] = "ชื่อซ้ำโปรดลองชื่อใหม่";
        header("location:".$_POST['redirect']);
    } else {
        //add
        $db->add_machine_cat($_POST['name'],$_POST['group'],$_POST['des']);
        $_SESSION['message'] = "เพิ่มแคดตากอรี่ใหม่สำเร็จ";
        header("location:".$_POST['redirect']);
    }
} else if($req == "edit_machine_cat"){
    //check name
    if(!$db->check_machinecat_name($_POST['name'],$_POST['cid'])){
        //name dup
        $_SESSION['error'] = "ชื่อซ้ำโปรดลองชื่อใหม่";
        header("location:".$_POST['redirect']);
    } else {
        //edit
        $db->edit_machine_cat($_POST['cid'],$_POST['name'],$_POST['group'],$_POST['des']);
        $_SESSION['message'] = "แก้ไขแคดตากอรี่สำเร็จ";
        header("location:".$_POST['redirect']);
    }
} else if($req == "add_user"){
    //check email
    if(!$db->check_email($_POST['email'])){
        //name dup
        $_SESSION['error'] = "Email ซ้ำโปรดลองชื่อใหม่";
        header("location:".$_POST['redirect']);
    } else {
        //add user
        $date = date_format(date_create(null,timezone_open("Asia/Bangkok")),"Y-m-d H:i:s");
        $uid = $db->add_user($_POST['email'],md5($_POST['repass']),$date);
        $meta = [
            "user_level" => $_POST['user_level'],
            "user_expired" => $_POST['user_expired'],
            "user_company" => $_POST['user_company']
        ];
        $db->update_user_meta($uid, $meta);
        $_SESSION['message'] = "เพิ่ม User ใหม่สำเร็จ";
        header("location:".$_POST['redirect']);
    }
} else if($req == "edit_user"){
    //check email
    if(!$db->check_email($_POST['email'],$_POST['uid'])){
        //name dup
        $_SESSION['error'] = "Email ซ้ำโปรดลองชื่อใหม่";
        header("location:".$_POST['redirect']);
    } else {
        //edit user
        if($_POST['repass']!=""){
            $arrinfo = array(
                "email" => $_POST['email'],
                "pass" => md5($_POST['repass'])
            );
        } else {
            $arrinfo = array(
                "email" => $_POST['email']
            );
        }
        $db->edit_user($_POST['uid'],$arrinfo);
        $meta = [
            "user_level" => $_POST['user_level'],
            "user_expired" => $_POST['user_expired'],
            "user_company" => $_POST['user_company']
        ];
        $db->update_user_meta($_POST['uid'], $meta);
        $_SESSION['message'] = "แก้ไข User สำเร็จ";
        header("location:".$_POST['redirect']);
    }
} else if($req == "edit_upass"){
    $arrinfo = array(
        "pass" => md5($_POST['repass'])
    );
    $db->edit_user($_POST['uid'],$arrinfo);
    $_SESSION['message'] = "แก้ไขรหัสผ่านสำเร็จ";
    header("location:".$_POST['redirect']);
} else if($req == "pass-reset"){
    include_once("email_function.php");
    $email = strtolower($_POST['email']);
    $np = $db->get_repass($email);
    if(!is_string($np)){
        $_SESSION['error'] = "อีเมลไม่ถูกต้อง";
        header("Location:".$_POST['error_direct']."?f");
    } else {
        $rurl = $_POST['error_direct']."?r=".$np;
        if(php_mailer_netdesign($email, email_ct($rurl), "ขอเปลี่ยนรหัสผ่าน")){
            $_SESSION['message'] = "ส่งอีเมลขอเปลียนรหัสผ่านเรียบร้อยแล้ว";
            header("Location:".$_POST['error_direct']);
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาด กรุณาติดต่อผู้ดูแลระบบ";
            header("Location:".$_POST['error_direct']."?f");
        }
    } 
} else if($req == "del_user"){
    //del user
    __autoload("pdo_d");
    $del = new delDB();
    $del->del_user($_POST['uid']);
    $msg = newstatus("ลบผู้ใช้สำเร็จ");
    echo json_encode(["showmsg",$msg]);
} else if($req == "add_new_mat"){
    //check name
    if(!$db->check_mat_name($_POST['name'],0,$_POST['coid'])){
        //name dup
        $_SESSION['error'] = "ชื่อซ้ำโปรดลองชื่อใหม่";
        header("location:".$_POST['redirect']);
    } else {
        //add
        $db->add_mat($_POST['coid'],$_POST['name'],$_POST['unit'],$_POST['ef'],$_POST['ref'],$_POST['cat']);
        $_SESSION['message'] = "เพิ่มวัสดุใหม่สำเร็จ";
        header("location:".$_POST['redirect']);
    }
} else if($req == "edit_mat"){
    //check name
    if(!$db->check_mat_name($_POST['name'],$_POST['mid'],$_POST['coid'])){
        //name dup
        $_SESSION['error'] = "ชื่อซ้ำโปรดลองชื่อใหม่";
        header("location:".$_POST['redirect']);
    } else {
        //add
        $db->edit_mat($_POST['mid'],$_POST['name'],$_POST['unit'],$_POST['ef'],$_POST['ref'],$_POST['cat']);
        $_SESSION['message'] = "แก้ไขวัสดุสำเร็จ";
        header("location:".$_POST['redirect']);
    }
} else if($req == "delete_mat"){
     __autoload("pdo_d");
    $del = new delDB();
    $del->del_mat($_POST['mid']);
    echo json_encode(array("redirect",$_POST['redirect']));
} else if($req == "add_mat_transport"){
    //add
    if($_POST['type']=="gas"){
        $db->add_mat_gas($_POST['mid'],$_POST['type'],$_POST['gas'],$_POST['used']);
    } else if($_POST['type'] == "none"){
        $db->add_mat_gas($_POST['mid'],$_POST['type'],"","");
    } else {
        $db->add_mat_vehicle($_POST['mid'],$_POST['type'],$_POST['distance'],$_POST['tid'],$_POST['inload'],$_POST['outload']);
    }
    $_SESSION['message'] = "เพิ่มการขนส่งวัสดุสำเร็จ";
    header("location:".$_POST['redirect']);
} else if($req == "edit_mat_transport"){
    //edit
    if($_POST['type']=="gas"){
        $db->edit_mat_gas($_POST['mid'],$_POST['type'],$_POST['gas'],$_POST['used']);
    } else if($_POST['type'] == "none"){
        $db->edit_mat_gas($_POST['mid'],$_POST['type'],"","");
    } else {
        $db->edit_mat_vehicle($_POST['mid'],$_POST['type'],$_POST['distance'],$_POST['tid'],$_POST['inload'],$_POST['outload']);
    }
    $_SESSION['message'] = "แก้ไขการขนส่งวัสดุสำเร็จ";
    header("location:".$_POST['redirect']);
} else if($req == "add_design"){
    //add
    $maid = $db->add_machine($_POST['coid'],$_POST['brand'], $_POST['process'], $_POST['unit'],$_POST['mcat']);
    if(!is_numeric($maid)){
        $_SESSION['message'] = "Error occured, please contact admin.";
    } else {
        //add electricity
        $db->add_ele($maid,$_POST['name'],$_POST['watt'],$_POST['amount']);
        
        //add design data
        $testid = $db->add_testdata($maid, $_POST['date'], $_POST['user'], $_POST['input'], $_POST['output'], $_POST['idle_min'], $_POST['idle_kwh'], $_POST['load_min'], $_POST['load_kwh']);

        //add input
        $db->add_test_mat($testid, $_POST['in_matid'], $_POST['in_num'], true);
        //add output
        $db->add_test_mat($testid, $_POST['out_matid'], $_POST['out_num'], false);
        
        $_SESSION['message'] = "เพิ่มกระบวนการออกแบบสำเร็จ";
    }
    header("location:".$_POST['redirect']);
} else if($req == "edit_design"){
     //edit design
    $db->edit_machine($_POST['maid'],$_POST['brand'], $_POST['process'], $_POST['unit'],$_POST['mcat']);
    //edit electricity
    if(isset($_POST['eid'])){
        $db->edit_ele($_POST['eid'],$_POST['name'],$_POST['watt'],$_POST['amount']);
    }
    //add new electricity
    $db->add_ele($_POST['maid'],$_POST['nname'],$_POST['nwatt'],$_POST['namount']);
 
    //edit test data
    $arrinfo = [
        "date" => $_POST['date'],
        "user" => $_POST['user'],
        "load_min" => $_POST['load_min']
    ];
    $db->edit_testdata($_POST['testid'],$arrinfo);
    
    //edit input
    $db->edit_test_mat($_POST['testid'], $_POST['in_matid'], $_POST['in_num'], true);
    //edit output
    $db->edit_test_mat($_POST['testid'], $_POST['out_matid'], $_POST['out_num'], false);
    
    $_SESSION['message'] = "แก้ไขกระบวนการออกแบบสำเร็จ";
    header("location:".$_POST['redirect']);
} else if($req == "add_machine"){
    //add
    $maid = $db->add_machine($_POST['coid'],$_POST['brand'], $_POST['process'], $_POST['unit'],$_POST['mcat']);
    
    //add electricity
    if(!is_numeric($maid)){
        $_SESSION['message'] = "Error occured, please contact admin.";
    } else {
        $db->add_ele($maid,$_POST['name'],$_POST['watt'],$_POST['amount']);
        $_SESSION['message'] = "เพิ่มเครื่องจักรสำเร็จ";
    }
    //machine meta
    $db->update_mmeta($maid,array("max_defect"=>$_POST['max_defect']));
    
    header("location:".$_POST['redirect']);
    
} else if($req == "edit_machine"){
    //edit machine
    $db->edit_machine($_POST['maid'],$_POST['brand'], $_POST['process'], $_POST['unit'],$_POST['mcat']);
    //edit electricity
    if(isset($_POST['eid'])){
        $db->edit_ele($_POST['eid'],$_POST['name'],$_POST['watt'],$_POST['amount']);
    }
    //add new electricity
    $db->add_ele($_POST['maid'],$_POST['nname'],$_POST['nwatt'],$_POST['namount']);
    //machine meta
    $db->update_mmeta($_POST['maid'],array("max_defect"=>$_POST['max_defect']));
    
    $_SESSION['message'] = "แก้ไขเครื่องจักรสำเร็จ";
    header("location:".$_POST['redirect']);
} else if($req == "del_machine"){
    __autoload("pdo_d");
    $del = new delDB();
    $del->del_machine($_POST['maid']);
    echo json_encode(array("redirect",$_POST['redirect']));
} else if($req == "add_testdata"){
    //add test data
    $testid = $db->add_testdata($_POST['maid'], $_POST['date'], $_POST['user'], $_POST['input'], $_POST['output'], $_POST['idle_min'], $_POST['idle_kwh'], $_POST['load_min'], $_POST['load_kwh']);
    
    //add input
    $db->add_test_mat($testid, $_POST['in_matid'], $_POST['in_num'], true);
    //add output
    $db->add_test_mat($testid, $_POST['out_matid'], $_POST['out_num'], false);
    //message
    $_SESSION['message'] = "เพิ่มข้อมูลทดสอบสำเร็จ";
    header("location:".$_POST['redirect']);
} else if($req == "edit_testdata"){
    //edit test data
    $arrinfo = [
        "date" => $_POST['date'],
        "user" => $_POST['user'],
        "input" => $_POST['input'],
        "output_ok" => $_POST['output'],
        "idle_min" => $_POST['idle_min'],
        "idle_kwh" => $_POST['idle_kwh'],
        "load_min" => $_POST['load_min'],
        "load_kwh" => $_POST['load_kwh']  
    ];
    $db->edit_testdata($_POST['testid'],$arrinfo);
    
    //edit input
    $db->edit_test_mat($_POST['testid'], $_POST['in_matid'], $_POST['in_num'], true);
    //edit output
    $db->edit_test_mat($_POST['testid'], $_POST['out_matid'], $_POST['out_num'], false);
    
    //message
    $_SESSION['message'] = "แก้ไขข้อมูลทดสอบสำเร็จ";
    header("location:".$_POST['redirect']);
} else if ($req == "add_print"){
    //add component
    $pid = $db->add_print($_POST['coid'],$_POST['name']);
    
    //add process
    $transit = (isset($_POST['transit'])?$_POST['transit']:"");
    $db->add_process($pid, $_POST['mach'], $_POST['seq'],$_POST['mult'],$transit);
    
    //message
    $_SESSION['message'] = "เพิ่มกระบวนการพิมพ์สำเร็จ";
    header("location:".$_POST['redirect']);
} else if ($req == "edit_print"){
    $arrinfo = [
        "name" => $_POST['name']
    ];
    $db->edit_print($_POST['pid'],$arrinfo);
    
    //edit process
    $transit = (isset($_POST['transit'])?$_POST['transit']:"");
    $db->edit_process($_POST['pid'],$_POST['mach'],$_POST['seq'],$_POST['mult'],$transit);
    
    //message
    $_SESSION['message'] = "แก้ไขกระบวนการพิมพ์สำเร็จ";
    header("location:".$_POST['redirect']);
} else if($req == "delete_process"){
    __autoload("pdo_d");
    $del = new delDB();
    $del->del_printing($_POST['pid']);
    echo json_encode(array("redirect",$_POST['redirect']));
} else if($req == "calculate"){
    //add function table
    $fid = $db->add_fn($_POST['coid'],$_POST['fn'], $_POST['type'],$_POST['amount'],$_POST['page']);

    //add fn_meta
    $meta = array(
        'cname' => $_POST['cname'],
        'ele_type' => $_POST['ele_type'],
        "paper_waste" =>$_POST['paper_waste'],
        "plate_waste" =>$_POST['plate_waste'],
        "dis_type" => $_POST['dis_type'],
        "dis_info" => json_encode(array(
            "ef"=>$_POST['dis_ef'],
            "gas" => $_POST['dis_gas_type'],
            "lperkg" => $_POST['dis_gas_lperkg'],
            "vehicle" => $_POST['dis_v_type'],
            "distance" => $_POST['dis_v_distance'],
            "goload" => $_POST['dis_v_goload'],
            "backload" => $_POST['dis_v_backload']
            )),
        "design" => $_POST['design']
    );
    if($_POST['coid']==1){
        $meta['sub_comp'] = $_POST['sub_comp'];
    }
    $db->update_fn_meta($fid,$meta);
    
    //match fn with comp
    $db->add_fn_print($fid, $_POST['pid'],$_POST['name'],$_POST['paper'],$_POST['weight'],$_POST['m_width'],$_POST['m_length'],$_POST['sheet_per_plate'], $_POST['width'], $_POST['length'],$_POST['sheet_per_unit'],$_POST['input'],$_POST['plate_type'],$_POST['plate']);
    
    //add finishing process
    $transit = (isset($_POST['transit'])?$_POST['transit']:"");
    $db->add_finish($fid,$_POST['mach'],$_POST['seq'],$transit);
    
    header("location:".$_POST['redirect']."?action=res&fid=$fid");
} else if($req == "edit_fn"){
    //edit function table
    $arrinfo = [
        "name" => $_POST['fn'],
        "type" => $_POST['type'],
        "amount" => $_POST['amount'],
        "page" => $_POST['page']
    ];
    $db->edit_fn($_POST['fid'],$arrinfo);

    //edit fn_meta
    $meta = array(
        'cname' => $_POST['cname'],
        'ele_type' => $_POST['ele_type'],
        "paper_waste" =>$_POST['paper_waste'],
        "plate_waste" =>$_POST['plate_waste'],
        "dis_type" => $_POST['dis_type'],
        "dis_info" => json_encode(array(
            "ef"=>$_POST['dis_ef'],
            "gas" => $_POST['dis_gas_type'],
            "lperkg" => $_POST['dis_gas_lperkg'],
            "vehicle" => $_POST['dis_v_type'],
            "distance" => $_POST['dis_v_distance'],
            "goload" => $_POST['dis_v_goload'],
            "backload" => $_POST['dis_v_backload']
            )),
        "design" => $_POST['design']
    );
    if($_POST['coid']==1){
        $meta['sub_comp'] = $_POST['sub_comp'];
    }
    $db->update_fn_meta($_POST['fid'],$meta);
    
    //edit comp
    $db->edit_fn_print($_POST['fid'], $_POST['pid'],$_POST['name'],$_POST['paper'],$_POST['weight'],$_POST['m_width'],$_POST['m_length'],$_POST['sheet_per_plate'], $_POST['width'], $_POST['length'],$_POST['sheet_per_unit'],$_POST['input'],$_POST['plate_type'],$_POST['plate']);
    
    //edit finishing
    $transit = (isset($_POST['transit'])?$_POST['transit']:"");
    $db->edit_finish($_POST['fid'],$_POST['mach'],$_POST['seq'],$transit);
    
    header("location:".$_POST['redirect']);
} else if($req == "del_fn"){
    __autoload("pdo_d");
    $del = new delDB();
    $del->del_fn($_POST['fid']);
    echo json_encode(array("redirect",$_POST['redirect']));
} else if($req == "copy_fn"){
    $fid = filter_input(INPUT_POST,'fid',FILTER_SANITIZE_NUMBER_INT);
    $info = $db->view_finfo($fid);
    $fnmeta = $db->view_fn_meta($fid);
    //add function table
    $nfid = $db->add_fn($info['company_id'],$info['name']."(2)", $info['type'],$info['amount'],$info['page']);

    //duplicate meta, component and finishing process
    $db->copy_fn($fid,$nfid);
    echo json_encode(array("reload"));
}

class myContact{
    private $conn;
    public function __construct() {
        $this->conn = dbConnect(DB_RMS);
    }
    public function add_contact($name,$email,$tel,$msg,$added){
        try {
            $stmt = $this->conn->prepare("INSERT INTO contact VALUES (null,:name,:email,:tel,:msg,:added)");
            $stmt->bindParam(":name",$name);
            $stmt->bindParam(":email",$email);
            $stmt->bindParam(":tel",$tel);
            $stmt->bindParam(":msg",$msg);
            $stmt->bindParam(":added",$added);
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (Exception $ex) {
            db_error("add_contact",$ex);
        }
    }
}