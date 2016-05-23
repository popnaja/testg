<?php
session_start();
include_once(dirname(__FILE__)."/p-admin/myfunction.php");
include_once("g_option.php");
if(!isset($_SESSION['rms_user'])){
    header("location:".ROOTS."login.php");
} else if(!green_user(basename(__FILE__),$_SESSION['rms_l'])){
    $_SESSION['error'] = "กรุณา login เพื่อใช้งานหน้าพิเศษ";
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
<style>
        .but-right {
            width:50%;
            float:right;
        }
</style>
END_OF_TEXT;
$content = $menu->showhead();
$content .= $menu->showpanel("วัตถุดิบ","");


$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$mid = filter_input(INPUT_GET,'mid',FILTER_SANITIZE_NUMBER_INT);

$ref = $db->get_keypair("ref", "id", "name");

if($action == "add"){
    //add
    $cat = $db->get_cat();
    $form = new myform('new','cheight');
    $content .= "<h1 class='page-title'>เพิ่มวัตถุดิบ</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-100'>"
            . "<div class='col-50'>"
            . $form->show_text("name","name","","","ชื่อ","","label-inline")
            . $form->show_select("cat", $cat, "label-inline","แคตากอรี่")
            . $form->show_text("unit","unit","กก","","หน่วย","","left-50 label-inline")
            . $form->show_num("ef","",0.00000001,"","EF (kgCO2e/หน่วย)","","right-50 label-inline")
            . "</div><div class='col-50'>"
            . $form->show_select("ref", $ref, "label-inline","ที่มา","แนวทางการประเมิณฯ")
            . $form->show_textarea("evidence", "", 4, 10, "", "อ้างอิง", "label-inline")
            . "</div>";
            
    $content .= $form->show_submit("submit","Add New","but-right")
            . $form->show_hidden("coid","coid",$coid)
            . $form->show_hidden("request","request","add_new_mat")
            . $form->show_hidden("redirect","redirect",$root."mat.php")
            . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", ['name','unit','ef','ref','cat']);
    $content .= $form->submitscript("$('#new').submit();");
    
} else if(isset($mid)&&$db->check_mat($mid,$coid)){
    //load data
    $info = $db->get_info("mat", "id", $mid);
    //edit
    $cat = $db->get_cat();
    $form = new myform('edit','cheight');
    $content .= "<h1 class='page-title'>แก้ไขวัตถุดิบ</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-100'>"
            . "<div class='col-50'>"
            . $form->show_text("name","name",$info['name'],"","ชื่อ","","label-inline")
            . $form->show_select("cat", $cat, "label-inline","แคตากอรี่",$info['cat_id'])
            . $form->show_text("unit","unit",$info['unit'],"","หน่วย","","left-50 label-inline")
            . $form->show_num("ef",$info['ef'],0.00000001,"","EF (kgCO2e/หน่วย)","","right-50 label-inline")
            . "</div><div class='col-50'>"
            . $form->show_select("ref", $ref, "label-inline","ที่มา",$info['ref_id'])
            . $form->show_textarea("evidence", $info['evidence'], 4, 10, "", "อ้างอิง", "label-inline")
            . "</div>";
            
    //del mat
    if($_SESSION['rms_l']>1){
        $redirect = $root."mat.php";
        $requrl = $aroot."request.php";
        $content .= "<div id='del-mat-but' class='red-but'>ลบวัตถุดิบ</div><!-- .del-but -->"
                . "<script>"
                . "del_mat($mid,'$redirect','$requrl');"
                . "</script>";
    }
    $content .= $form->show_submit("submit","Update","but-right")
            . $form->show_hidden("request","request","edit_mat")
            . $form->show_hidden("mid","mid",$mid)
            . $form->show_hidden("coid","coid",$coid)
            . $form->show_hidden("redirect","redirect",$root."mat.php")
            . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", ['name','unit','ef','ref','cat']);
    $content .= $form->submitscript("$('#edit').submit();");
} else {
    //show all
    $mcat = ["0"=>"แสดงทั้งหมด"]+$db->get_cat();
    $form = new myform('filter','cheight');
    $add = $root."mat.php?action=add";
    $content .= "<h1 class='page-title'>รายการวัตถุดิบ<a class='add-new' href='$add' title='Add new Transport'>Add New</a></h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>";
    
    //filter
    $cat = filter_input(INPUT_GET,'cat',FILTER_SANITIZE_NUMBER_INT);
    $link = $root."mat.php";
    $content .= "<div class='col-100'>"
            . $form->show_select("cat", $mcat, "label-inline left-33", "กลุ่มวัตถุดิบ", (isset($cat)?$cat:0))
            . "</div><!-- .col-100 -->"
            . "<script>"
            . "sel_matcat('$link');"
            . "</script>";
    
    $tb = new mytable();
    
    $head = ["ชื่อ",'หน่วย','ef',"ที่มา","กลุ่ม","ข้อมูลการขนส่ง"];
    $rec = $db->view_mat($cat,$coid);
    $content .= "<div class='col-100'>"
            . $tb->show_table($head,$rec)
            . "</div>";
}

$content .= $menu->showfooter();
echo $content;

