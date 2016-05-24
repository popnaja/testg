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
$form = new myform('myform','cheight');
$vehicle = ["0"=>"--พาหนะ--"]+$db->get_vehicle();
$load = [0=>0,50=>50,75=>75,100=>100];

if($action == "add"){
    $mcat = $db->get_mcat($mgroup);
    //elect
    $elect = "";
    for($i=0;$i<5;$i++){
        $elect .= $form->show_text("name_$i","name[]","","",($i==0?"อุปกรณ์":""),"","left-33 label-inline")
                . $form->show_num("watt_$i","",0.001,"",($i==0?"กินไฟ(วัตต์)":""),"","left-33 label-inline","","watt[]")
                . $form->show_num("amount_$i","",0.01,"",($i==0?"จำนวน":""),"","left-33 label-inline","","amount[]");
    }
    //transit
    $transit = "<div class='tab-section cheight'>"
            . "<h4>การขนส่งขาไป (นำชิ้นงานไปผลิต)</h4>"
            . $form->show_select("go_tid", $vehicle, "label-3070","พาหนะ")
            . $form->show_num("go_dis","",0.01,"","ระยะทาง (กม)","","label-3070")
            . $form->show_select("go_inload", $load, "label-3070","บรรทุกขาไป(%)")
            . $form->show_select("go_outload", $load, "label-3070","บรรทุกขากลับ(%)")
            . "</div><div class='tab-section cheight'>"
            . "<h4>การขนส่งขากลับ (นำชิ้นงานสำเร็จกลับ)</h4>"
            . $form->show_select("back_tid", $vehicle, "label-3070","พาหนะ")
            . $form->show_num("back_dis","",0.01,"","ระยะทาง (กม)","","label-3070")
            . $form->show_select("back_inload", $load, "label-3070","บรรทุกขาไป(%)")
            . $form->show_select("back_outload", $load, "label-3070","บรรทุกขากลับ(%)")
            . "</div>";
    //add
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
            . $form->show_tabs(array("อุปกรณ์","การขนส่ง"), array($elect,$transit), 0)
            . "</div><!-- .col-50 -->";
    
    $content .= $form->show_submit("submit","Add New","but-right")
            . $form->show_hidden("request","request","add_machine")
            . $form->show_hidden("coid","coid",$coid)
            . $form->show_hidden("redirect","redirect",$root."machine.php")
            . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", array('brand','process','unit'));
    $content .= $form->submitscript("$('#myform').submit();")
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
    //elect
    $elect = "";
    for($i=0;$i<$ele_no;$i++){
        $elect .= $form->show_text("name_$i","name[]",$ele[$i]['name'],"",($i==0?"อุปกรณ์":""),"","left-33 label-inline")
                . $form->show_num("watt_$i",$ele[$i]['watt'],0.001,"",($i==0?"กินไฟ(วัตต์)":""),"","left-33 label-inline","","watt[]")
                . $form->show_num("amount_$i",$ele[$i]['unit'],0.01,"",($i==0?"จำนวน":""),"","left-33 label-inline","","amount[]")
                . $form->show_hidden("eid_$i","eid[]",$ele[$i]['id']);
    }
    for($i=0;$i<5;$i++){
        $elect .= $form->show_text("nname_$i","nname[]","","",($i==0?"อุปกรณ์":""),"","left-33 label-inline")
                . $form->show_num("nwatt_$i","",0.001,"",($i==0?"กินไฟ(วัตต์)":""),"","left-33 label-inline","","nwatt[]")
                . $form->show_num("namount_$i","",0.01,"",($i==0?"จำนวน":""),"","left-33 label-inline","","namount[]");
    }
    //transit
    if(isset($mmeta['go_transit'])&&$mmeta['go_transit']!=""){
        $go = json_decode($mmeta['go_transit'],true);
    } else {
        $go = array(
            "tid" => 0,
            "dis" => "",
            "inload" => 0,
            "outload" => 0
        );
    }
    if(isset($mmeta['back_transit'])&&$mmeta['back_transit']!=""){
        $back = json_decode($mmeta['back_transit'],true);
    } else {
        $back = array(
            "tid" => 0,
            "dis" => "",
            "inload" => 0,
            "outload" => 0
        );
    }
    $transit = "<div class='tab-section cheight'>"
            . "<h4>การขนส่งขาไป (นำชิ้นงานไปผลิต)</h4>"
            . $form->show_select("go_tid", $vehicle, "label-3070","พาหนะ",$go['tid'])
            . $form->show_num("go_dis",$go['dis'],0.01,"","ระยะทาง (กม)","","label-3070")
            . $form->show_select("go_inload", $load, "label-3070","บรรทุกขาไป(%)",$go['inload'])
            . $form->show_select("go_outload", $load, "label-3070","บรรทุกขากลับ(%)",$go['outload'])
            . "</div><div class='tab-section cheight'>"
            . "<h4>การขนส่งขากลับ (นำชิ้นงานสำเร็จกลับ)</h4>"
            . $form->show_select("back_tid", $vehicle, "label-3070","พาหนะ",$back['tid'])
            . $form->show_num("back_dis",$back['dis'],0.01,"","ระยะทาง (กม)","","label-3070")
            . $form->show_select("back_inload", $load, "label-3070","บรรทุกขาไป(%)",$back['inload'])
            . $form->show_select("back_outload", $load, "label-3070","บรรทุกขากลับ(%)",$back['outload'])
            . "</div>";
    //edit
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
            . $form->show_tabs(array("อุปกรณ์","การขนส่ง"), array($elect,$transit), 0)
            . "</div><!-- .col-50 -->";
    
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
    $content .= $form->submitscript("$('#myform').submit();")
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

