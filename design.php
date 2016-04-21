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
$menu->astyle[] = $aroot."js/jquery-ui-1.11.4.custom/jquery-ui.css";
$menu->ascript[] = $aroot."js/jquery-ui-1.11.4.custom/jquery-ui.min.js";
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
$content .= $menu->showpanel("ออกแบบสิ่งพิมพ์","");

$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$maid = filter_input(INPUT_GET,'maid',FILTER_SANITIZE_NUMBER_INT);

$mat = array("0"=>"-- วัตถุดิบ --")+$db->get_mat($coid,"1,2,3,6");
$waste = array("0"=>"-- ของเสีย --")+$db->get_mat($coid,"4");

if($action == "add"){
    $mcat = $db->get_mcat();
    //add
    $form = new myform('new','cheight');
    $content .= "<h1 class='page-title'>เพิ่มกระบวนการออกแบบ</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-100'>"
            . "<div class='col-50'>"
            . "<h3 class='section-title'>รายละเอียด</h3>"
            . $form->show_text("brand","brand","","เช่น ออกแบบแผ่นพับ A4 2 หน้า","ชื่องาน","","label-inline")
            . $form->show_text("date","date","","yyyy-mm-dd","วันที่ออกแบบ","","left-50 label-inline")
            . $form->show_text("user","user","","","ผู้ออกแบบ","","right-50 label-inline")
            . $form->show_num("load_min","",1,"","เวลาที่ใช้ในการออกแบบ (นาที)","","label-inline")
            . $form->show_hidden("process","process"," ")
            . $form->show_hidden("input","input",1)
            . $form->show_hidden("output","output",1)
            . $form->show_hidden("mcat","mcat",8)
            . $form->show_hidden("idle_min","idle_min",0)
            . $form->show_hidden("idle_kwh","idle_kwh",0)
            . $form->show_hidden("load_kwh","load_kwh",0)
            . $form->show_hidden("unit","unit","ชิ้น/เล่ม")
            . $form->show_hidden("max_defect","max_defect","")
            . "</div><!-- .col-50 -->";
    
    $content .= "<div class='col-50'>"
            . "<h3 class='section-title'>อุปกรณ์อำนวยความสะดวก</h3>";
    for($i=0;$i<8;$i++){
        $content .= $form->show_text("name_$i","name[]","","",($i==0?"อุปกรณ์":""),"","left-33 label-inline")
                . $form->show_num("watt_$i","",0.001,"",($i==0?"กินไฟ(วัตต์)":""),"","left-33 label-inline","","watt[]")
                . $form->show_num("amount_$i","",0.01,"",($i==0?"จำนวน":""),"","left-33 label-inline","","amount[]");
    }
    $content .= "</div><!-- .col-50 -->"
            . "</div><!-- .col-100 -->";
    
    //material in
    $content .= "<div class='form-section col-50'>"
            . "<h3 class='section-title'>วัตถุดิบ (สารขาเข้า)</h3>";
    for($i=0;$i<10;$i++){
        $content .= $form->show_select("in_matid[]", $mat, "left-50 label-inline",null,"0")
                . $form->show_num("in_num[]","",0.000001,"ปริมาณ","","","right-50 label-inline");
    }
    $content .= "</div><!-- .form-section -->";
    
    //material out
    $content .= "<div class='form-section col-50'>"
            . "<h3 class='section-title'>ของเสีย (สารขาออก)</h3>";
    for($i=0;$i<10;$i++){
        $content .= $form->show_select("out_matid[]", $waste, "left-50 label-inline",null,"0")
                . $form->show_num("out_num[]","",0.000001,"ปริมาณ","","","right-50 label-inline");
    }
    $content .= "</div><!-- .form-section -->";
    
    $content .= $form->show_submit("submit","Add New","but-right")
            . $form->show_hidden("request","request","add_design")
            . $form->show_hidden("coid","coid",$coid)
            . $form->show_hidden("redirect","redirect",$root."design.php")
            . "<script>"
            . "$('#date').datepicker({dateFormat: 'yy-mm-dd'});"
            . "</script>";
    $form->addformvalidate("ez-msg", array('brand','date','user','load_min'));
    $content .= $form->submitscript("$('#new').submit();");
    
} else if(isset($maid)&&$db->check_mach($maid,$coid)){
    $mcat = $db->get_mcat();
    //load data
    $info = $db->view_macinfo($maid);
    $iinfo = $db->view_test_mat($info['test_id'],true);
    $outinfo = $db->view_test_mat($info['test_id'],false);
    $ele = $db->view_ele($maid);
    $ele_no = sizeof($ele,0);
    //edit
    $form = new myform('edit','cheight');
    $content .= "<h1 class='page-title'>แก้ไขเครื่องจักร</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-100'>"
            . "<div class='col-100'>"
            . "<div class='col-50'>"
            . "<h3 class='section-title'>รายละเอียด</h3>"
            . $form->show_text("brand","brand",$info['brand_model'],"เช่น ออกแบบแผ่นพับ A4 2 หน้า","ชื่องาน","","label-inline")
            . $form->show_text("date","date",$info['date'],"yyyy-mm-dd","วันที่ออกแบบ","","left-50 label-inline")
            . $form->show_text("user","user",$info['user'],"","ผู้ออกแบบ","","right-50 label-inline")
            . $form->show_num("load_min",$info['load_min'],1,"","เวลาที่ใช้ในการออกแบบ (นาที)","","label-inline")
            . $form->show_hidden("process","process"," ")
            . $form->show_hidden("input","input",1)
            . $form->show_hidden("output","output",1)
            . $form->show_hidden("mcat","mcat",8)
            . $form->show_hidden("idle_min","idle_min",0)
            . $form->show_hidden("idle_kwh","idle_kwh",0)
            . $form->show_hidden("load_kwh","load_kwh",0)
            . $form->show_hidden("unit","unit","ชิ้น/เล่ม")
            . $form->show_hidden("max_defect","max_defect","")
            . "</div><!-- .col-50 -->";

    $content .= "<div class='col-50'>"
            . "<h3 class='section-title'>สิ่งอำนวยความสะดวก</h3>";
    for($i=0;$i<$ele_no;$i++){
        $content .= $form->show_text("name_$i","name[]",$ele[$i]['name'],"",($i==0?"อุปกรณ์":""),"","left-33 label-inline")
                . $form->show_num("watt_$i",$ele[$i]['watt'],0.001,"",($i==0?"กินไฟ(วัตต์)":""),"","left-33 label-inline","","watt[]")
                . $form->show_num("amount_$i",$ele[$i]['unit'],0.01,"",($i==0?"จำนวน":""),"","left-33 label-inline","","amount[]")
                . $form->show_hidden("eid_$i","eid[]",$ele[$i]['id']);
    }
    for($i=0;$i<5;$i++){
        $content .= $form->show_text("nname_$i","nname[]","","",($i==0?"อุปกรณ์":""),"","left-33 label-inline")
                . $form->show_num("nwatt_$i","",0.001,"",($i==0?"กินไฟ(วัตต์)":""),"","left-33 label-inline","","nwatt[]")
                . $form->show_num("namount_$i","",0.01,"",($i==0?"จำนวน":""),"","left-33 label-inline","","namount[]");
    }
    $content .= "</div><!-- .col-50 -->"
            . "</div><!-- .col-100 -->";
    
    //material in
    $content .= "<div class='form-section left-50'>"
            . "<h3 class='section-title'>วัตถุดิบ (สารขาเข้า)</h3>";
    for($i=0;$i<10;$i++){
        $mid = (isset($iinfo[$i])?$iinfo[$i]['mat_id']:0);
        $amount = (isset($iinfo[$i])?$iinfo[$i]['amount']:"");
        $content .= $form->show_select("in_matid[]", $mat, "left-50 label-inline",null,$mid)
                . $form->show_num("in_num[]",$amount,0.000001,"ปริมาณ","","","right-50 label-inline");
    }
    $content .= "</div><!-- .form-section -->";
    
    //material out
    $content .= "<div class='form-section left-50'>"
            . "<h3 class='section-title'>ของเสีย (สารขาออก)</h3>";
    for($i=0;$i<10;$i++){
        $mid = (isset($outinfo[$i])?$outinfo[$i]['mat_id']:0);
        $amount = (isset($outinfo[$i])?$outinfo[$i]['amount']:"");
        $content .= $form->show_select("out_matid[]", $waste, "left-50 label-inline",null,$mid)
                . $form->show_num("out_num[]",$amount,0.000001,"ปริมาณ","","","right-50 label-inline");
    }
    $content .= "</div><!-- .form-section -->";
    
    //del machine
    if($_SESSION['rms_l']>1){
        $redirect = $root."machine.php";
        $requrl = $aroot."request.php";
        $content .= "<div id='del-machine-but' class='red-but'>ลบเครื่องจักร</div><!-- .del-but -->"
                . "<script>"
                . "del_machine($maid,'$redirect','$requrl');"
                . "</script>";
    }
    
    $content .= $form->show_submit("submit","Update","but-right")
            . $form->show_hidden("request","request","edit_design")
            . $form->show_hidden("maid","maid",$maid)
            . $form->show_hidden("testid","testid",$info['test_id'])
            . $form->show_hidden("coid","coid",$coid)
            . $form->show_hidden("redirect","redirect",$root."design.php");
    $form->addformvalidate("ez-msg", array('brand','date','user','load_min'));
    $content .= $form->submitscript("$('#edit').submit();");
} else {
    //show all
    $add = $root."design.php?action=add";
    $content .= "<h1 class='page-title'>รายการงานออกแบบ<a class='add-new' href='$add' title='Add New'>Add New</a></h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>";

    $tb = new mytable();
    $head = array("แกไข","งานออกแบบ",'วันที่');
    $rec = $db->view_design($coid,8);
    $content .= "<div class='col-100'>"
            . $tb->show_table($head,$rec)
            . "</div>";
}

$content .= $menu->showfooter();
echo $content;

