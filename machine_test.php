<?php
session_start();
include_once(dirname(__FILE__)."/p-admin/myfunction.php");

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

END_OF_TEXT;
$content = $menu->showhead();
$content .= $menu->showpanel("เครื่องจักร","ข้อมูลการทดสอบ");

$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$testid = filter_input(INPUT_GET,'testid',FILTER_SANITIZE_NUMBER_INT);
$maid = filter_input(INPUT_GET,'maid',FILTER_SANITIZE_NUMBER_INT);

$mat = array("0"=>"-- วัตถุดิบ --")+$db->get_mat($coid,"2,3,6");
$waste = array("0"=>"-- ของเสีย --")+$db->get_mat($coid,"4");

if($action == "add"&&isset($maid)&&$db->check_mach($maid,$coid)){
    //add
    $minfo = $db->view_macinfo($maid);
    $name = $minfo['brand_model']."(".$minfo['process'].")";
    $aunit = $minfo['allocation_unit'];
    
    $form = new myform('new','cheight');
    $content .= "<h1 class='page-title'>ข้อมูลการทดสอบเครื่องจักร</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-100'>"
            . $form->show_text("machine","machine",$name,"","เครื่องจักร","","label-inline",null,"readonly")
            . $form->show_text("date","date","","yyyy-mm-dd","วันที่ทดสอบ","","left-50 label-inline")
            . $form->show_text("user","user","","","ผู้ทดสอบ","","right-50 label-inline")
            . $form->show_num("input","",1,"","ชิ้นงานเข้า ($aunit)","","left-50 label-inline")
            . $form->show_num("output","",1,"","ชิ้นงานสมบูรณ์ ($aunit)","","right-50 label-inline");
    //ไฟฟ้า
    $content .= "<div class='form-section'>"
            . "<h3 class='section-title'>ไฟฟ้า</h3>"
            . $form->show_num("idle_min","",1,"","เวลารอ (นาที)","","left-50 label-inline")
            . $form->show_num("idle_kwh","",0.001,"","ไฟฟ้าที่ใช้ขณะรอ (kwh)","","right-50 label-inline")
            . $form->show_num("load_min","",1,"","เวลาเดินเครื่อง (นาที)","","left-50 label-inline")
            . $form->show_num("load_kwh","",0.001,"","ไฟฟ้าที่ใช้ขณะเดินเครื่อง (kwh)","","right-50 label-inline")
            . "</div><!-- .form-section -->";
    //material in
    $content .= "<div class='form-section left-50'>"
            . "<h3 class='section-title'>วัตถุดิบ (สารขาเข้า)</h3>";
    for($i=0;$i<10;$i++){
        $content .= $form->show_select("in_matid[]", $mat, "left-50 label-inline",null,"0")
                . $form->show_num("in_num[]","",0.000001,"ปริมาณ","","","right-50 label-inline");
    }
    $content .= "</div><!-- .form-section -->";
    
    //material out
    $content .= "<div class='form-section left-50'>"
            . "<h3 class='section-title'>ของเสีย (สารขาออก)</h3>";
    for($i=0;$i<10;$i++){
        $content .= $form->show_select("out_matid[]", $waste, "left-50 label-inline",null,"0")
                . $form->show_num("out_num[]","",0.000001,"ปริมาณ","","","right-50 label-inline");
    }
    $content .= "</div><!-- .form-section -->";

    $content .= $form->show_submit("submit","Add New","but-right")
            . $form->show_hidden("request","request","add_testdata")
            . $form->show_hidden("maid","maid",$maid)
            . $form->show_hidden("redirect","redirect",$root."machine.php")
            . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", ['date','user','input','output','idle_min','idle_kwh','load_min',"load_kwh"]);
    $content .= $form->submitscript("$('#new').submit();")
            . "<script>"
            . "$('#date').datepicker({dateFormat: 'yy-mm-dd'});"
            . "</script>";
} else if(isset($testid)&&$db->check_testid($testid,$coid)){
    //load
    $tinfo = $db->view_testinfo($testid);
    $aunit = $tinfo['allocation_unit'];
    $iinfo = $db->view_test_mat($testid,true);
    $outinfo = $db->view_test_mat($testid,false);
    //edit
    $form = new myform('edit','cheight');
    $content .= "<h1 class='page-title'>ข้อมูลการทดสอบเครื่องจักร</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-100'>"
            . $form->show_text("machine","machine",$tinfo['mach'],"","เครื่องจักร","","label-inline",null,"readonly")
            . $form->show_text("date","date",$tinfo['date'],"yyyy-mm-dd","วันที่ทดสอบ","","left-50 label-inline")
            . $form->show_text("user","user",$tinfo['user'],"","ผู้ทดสอบ","","right-50 label-inline")
            . $form->show_num("input",$tinfo['input'],1,"","ชิ้นงานเข้า ($aunit)","","left-50 label-inline")
            . $form->show_num("output",$tinfo['output_ok'],1,"","ชิ้นงานสมบูรณ์ ($aunit)","","right-50 label-inline");
    //ไฟฟ้า
    $content .= "<div class='form-section'>"
            . "<h3 class='section-title'>ไฟฟ้า</h3>"
            . $form->show_num("idle_min",$tinfo['idle_min'],1,"","เวลารอ (นาที)","","left-50 label-inline")
            . $form->show_num("idle_kwh",$tinfo['idle_kwh'],0.001,"","ไฟฟ้าที่ใช้ขณะรอ (kwh)","","right-50 label-inline")
            . $form->show_num("load_min",$tinfo['load_min'],1,"","เวลาเดินเครื่อง (นาที)","","left-50 label-inline")
            . $form->show_num("load_kwh",$tinfo['load_kwh'],0.001,"","ไฟฟ้าที่ใช้ขณะเดินเครื่อง (kwh)","","right-50 label-inline")
            . "</div><!-- .form-section -->";
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

    $content .= $form->show_submit("submit","Update","but-right")
            . $form->show_hidden("request","request","edit_testdata")
            . $form->show_hidden("testid","testid",$testid)
            . $form->show_hidden("redirect","redirect",$root."machine.php")
            . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", ['date','user','input','output','idle_min','idle_kwh','load_min',"load_kwh"]);
    $content .= $form->submitscript("$('#edit').submit();")
            . "<script>"
            . "$('#date').datepicker({dateFormat: 'yy-mm-dd'});"
            . "</script>";
} else {
    header("location:".$root."machine.php");
    exit();
}

$content .= $menu->showfooter();
echo $content;

