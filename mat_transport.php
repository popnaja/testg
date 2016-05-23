<?php
session_start();
include_once(dirname(__FILE__)."/p-admin/myfunction.php");
include_once("g_option.php");

if(!isset($_SESSION['rms_user'])){
    header("location:".ROOTS."login.php");
}

$root = ROOTS;
$aroot = AROOTS;
$coid = $_SESSION['rms_c'];
__autoload("menu");
__autoload("pdo_g");
$db = new greenDB();
$menu = new mymenu("th");
$menu->__autoloadall("form");
$menu->__autoloadall("table");
$menu->menu($_SESSION['rms_l']);
$menu->pageTitle = "โปรแกรมคำนวณ Carbon Footprint | ". SITE;
$menu->ascript[] = $aroot."js/smartgreeny.js";
$menu->extrascript = <<<END_OF_TEXT

END_OF_TEXT;
$content = $menu->showhead();
$content .= $menu->showpanel("วัตถุดิบ","");


$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$mid = filter_input(INPUT_GET,'mid',FILTER_SANITIZE_NUMBER_INT);

$gas = ["0"=>"--เชื้อเพลิง--"]+$db->get_mat($coid,2);
$vehicle = ["0"=>"--พาหนะ--"]+$db->get_vehicle();
$load = [0=>0,50=>50,75=>75,100=>100];
$type = ["gas"=>"คำนวณจากปริมาณเชื้อเพลิง","vehicle_distance"=>"คำนวณจากพาหนะและระยะทาง","none"=>"ไม่มีการขนส่ง"];

if($action == "add"&&isset($mid)&&$db->check_mat($mid,$coid)){
    //add transport info
    $info = $db->get_info("mat", "id", $mid);

    $form = new myform('new','cheight');
    $content .= "<h1 class='page-title'>บันทึกข้อมูลการขนส่ง</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-50'>"
            . $form->show_text("name","name",$info['name'],"","วัสดุ","","label-3070",null,"readonly")
            . $form->show_select("type", $type, "label-3070","รูปแบบการคำนวณ","vehicle_distance")
            . $form->show_submit("submit","Add New","but-right")
            . "</div><!-- .col-50 -->"
            . "<div class='col-50'>"
            . "<div id='bygas' class='form-section'>"
            . "<h3 class='section-title'>คำนวณจากปริมาณเชื้อเพลิง</h3>"
            . $form->show_select("gas", $gas, "left-50 label-inline","ชนิดเชื้อเพลิง")
            . $form->show_num("used","",0.00001,"","ปริมาณที่ใช้(ลิตร)","ปริมาณเชื้อเพลิงที่ใช้ต่อการขนส่งวัตถุดิบ 1 กิโลกรัมไปและกลับ","right-50 label-inline")
            . "</div><!-- .form-section -->";
    
    $content .=  "<div id='byvehicle' class='form-section'>"
            . "<h3 class='section-title'>คำนวณจากพาหนะและระยะทาง</h3>"
            . $form->show_select("tid", $vehicle, "left-50 label-inline","พาหนะ")
            . $form->show_num("distance","",0.01,"","ระยะทาง (กิโลเมตร)","ระยะทางจากผู้ผลิตถึงโรงงาน","right-50 label-inline")
            . $form->show_select("inload", $load, "left-50 label-inline","บรรทุกขามา/ไป(%)")
            . $form->show_select("outload", $load, "right-50 label-inline","บรรทุกขากลับ(%)")
            . "</div><!-- .form-section -->";

    $content .= ""
            . $form->show_hidden("mid","mid",$mid)
            . $form->show_hidden("request","request","add_mat_transport")
            . $form->show_hidden("redirect","redirect",$root."mat.php")
            . "</div><!-- .col-50 -->";
    $content .= $form->submitscript("$('#new').submit();")
            . "<script>"
            . "sel_transport_type('vehicle_distance');"
            . "</script>";
} else if(isset($mid)&&$db->check_mat($mid,$coid)){
    //load info
    $info = $db->view_mtinfo($mid);
    //edit transport info

    $form = new myform('edit','cheight');
    $content .= "<h1 class='page-title'>บันทึกข้อมูลการขนส่ง</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-50'>"
            . $form->show_text("mat","mat",$info['name'],"","วัตถุดิบ","","label-3070",null,"readonly")
            . $form->show_select("type", $type, "label-3070","รูปแบบการคำนวณ",$info['calculate_type'])
            . $form->show_submit("submit","Update","but-right")
            . "</div><!-- .col-50 -->"
            . "<div class='col-50'>"
            . "<div id='bygas' class='form-section'>"
            . "<h3 class='section-title'>คำนวณจากปริมาณเชื้อเพลิง</h3>"
            . $form->show_select("gas", $gas, "left-50 label-inline","ชนิดเชื้อเพลิง",$info['gas_type'])
            . $form->show_num("used",$info['gas_used'],0.00001,"","ปริมาณที่ใช้(ลิตร)","ปริมาณเชื้อเพลิงที่ใช้ต่อการขนส่งวัตถุดิบ 1 กิโลกรัมไปและกลับ","right-50 label-inline")
            . "</div><!-- .form-section -->";
    
    $content .=  "<div id='byvehicle' class='form-section'>"
            . "<h3 class='section-title'>คำนวณจากพาหนะและระยะทาง</h3>"
            . $form->show_select("tid", $vehicle, "left-50 label-inline","พาหนะ",$info['transport_id'])
            . $form->show_num("distance",$info['distance'],0.1,"","ระยะทาง (กิโลเมตร)","ระยะทางจากผู้ผลิตถึงโรงงาน","right-50 label-inline")
            . $form->show_select("inload", $load, "left-50 label-inline","บรรทุกขามา/ไป(%)",$info['load_come'])
            . $form->show_select("outload", $load, "right-50 label-inline","บรรทุกขากลับ(%)",$info['load_back'])
            . "</div><!-- .form-section -->";

    $content .= $form->show_hidden("request","request","edit_mat_transport")
            . $form->show_hidden("mid","mid",$mid)
            . $form->show_hidden("redirect","redirect",$root."mat.php")
            . "</div><!-- .col-50 -->";
    $content .= $form->submitscript("$('#edit').submit();")
            . "<script>"
            . "sel_transport_type('".$info['calculate_type']."');"
            . "</script>";
} else {
    header("location:".$root."mat.php");
}


$content .= $menu->showfooter();
echo $content;