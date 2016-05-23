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
<style>
        .but-right {
        width:50%;
        float:right;
        }
</style>
END_OF_TEXT;
$content = $menu->showhead();
$content .= $menu->showpanel("เครื่องจักร","");

$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$maid = filter_input(INPUT_GET,'maid',FILTER_SANITIZE_NUMBER_INT);
$mgroup = "('printing','finishing')";

if($action == "add"){
    $mcat = $db->get_mcat($mgroup);
    //add
    $form = new myform('new','cheight');
    $content .= "<h1 class='page-title'>เพิ่มเครื่องจักร</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-100'>"
            . "<div class='col-50'>"
            . "<h3 class='section-title'>รายละเอียด</h3>"
            . $form->show_text("brand","brand","","","ยี่ห้อ-รุ่น","","label-inline")
            . $form->show_select("mcat", $mcat, "label-3070","กระบวนการ")
            . $form->show_text("process","process","","เช่น พิมพ์ 4 สี, 1 สี ,สันกาว ,เย็บลวด","รายละเอียดอื่นๆ","","label-3070")
            . $form->show_select("unit", $allo_unit, "label-3070","หน่วยในการปันส่วน")
            . $form->show_num("max_defect","",1,"","ชิ้นงานเสียสูงสุด (<span class='max-defect-unit'>หน่วย</span>)","","label-3070")
            . "</div><!-- .col-50 -->";
    
    $content .= "<div class='col-50'>"
            . "<h3 class='section-title'>อุปกรณ์อำนวยความสะดวก</h3>";
    for($i=0;$i<5;$i++){
        $content .= $form->show_text("name_$i","name[]","","",($i==0?"อุปกรณ์":""),"","left-33 label-inline")
                . $form->show_num("watt_$i","",0.001,"",($i==0?"กินไฟ(วัตต์)":""),"","left-33 label-inline","","watt[]")
                . $form->show_num("amount_$i","",0.01,"",($i==0?"จำนวน":""),"","left-33 label-inline","","amount[]");
    }
    $content .= "</div><!-- .col-50 -->";
    
    $content .= $form->show_submit("submit","Add New","but-right")
            . $form->show_hidden("request","request","add_machine")
            . $form->show_hidden("coid","coid",$coid)
            . $form->show_hidden("redirect","redirect",$root."machine.php")
            . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", array('brand','process','unit'));
    $content .= $form->submitscript("$('#new').submit();")
            . "<script>"
            . "defect_unit();"
            . "</script>";
    
} else if(isset($maid)&&$db->check_mach($maid,$coid)){
    $mcat = $db->get_mcat($mgroup);
    //load data
    $info = $db->view_macinfo($maid);
    $mmeta = $db->view_mmeta($maid);
    $maxd = (isset($mmeta['max_defect'])?$mmeta['max_defect']:"");
    $ele = $db->view_ele($maid);
    $ele_no = sizeof($ele,0);
    //edit
    $form = new myform('edit','cheight');
    $content .= "<h1 class='page-title'>แก้ไขเครื่องจักร</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-100'>"
            . "<div class='col-50'>"
            . "<h3 class='section-title'>รายละเอียด</h3>"
            . $form->show_text("brand","brand",$info['brand_model'],"","ยี่ห้อ-รุ่น","","label-inline")
            . $form->show_select("mcat", $mcat, "label-3070","กระบวนการ",$info['machine_cat_id'])
            . $form->show_text("process","process",$info['process'],"เช่น พิมพ์ 4 สี, 1 สี ,สันกาว ,เย็บลวด","รายละเอียดอื่นๆ","","label-3070")
            . $form->show_select("unit", $allo_unit, "label-3070","หน่วยในการปันส่วน",$info['allocation_unit'])
            . $form->show_num("max_defect",$maxd,1,"","ชิ้นงานเสียสูงสุด (<span class='max-defect-unit'>".$info['allocation_unit']."</span>)","","label-3070")
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
    $content .= "</div><!-- .col-50 -->";
    
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
            . $form->show_hidden("request","request","edit_machine")
            . $form->show_hidden("maid","maid",$maid)
            . $form->show_hidden("coid","coid",$coid)
            . $form->show_hidden("redirect","redirect",$root."machine.php")
            . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", array('brand','process','unit'));
    $content .= $form->submitscript("$('#edit').submit();")
            . "<script>"
            . "defect_unit();"
            . "</script>";
} else {
    //show all
    $add = $root."machine.php?action=add";
    $content .= "<h1 class='page-title'>รายการเครื่องจักร<a class='add-new' href='$add' title='Add New'>Add New</a></h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>";

    $tb = new mytable();
    $head = array("แกไข","รุ่น ยี่ห้อ",'กระบวนการ','หน่วย','ข้อมูล Test');
    $rec = $db->view_machine($coid,'(8)');
    $content .= "<div class='col-100'>"
            . $tb->show_table($head,$rec)
            . "</div>";
}

$content .= $menu->showfooter();
echo $content;

