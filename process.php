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
        #tb-process table tr th:nth-child(1) {
            width:80px;
        }
        #tb-process table tr th:nth-child(2) {
            width:100px;
        }
        #del-process-but {
            margin-bottom:25px;
        }
        .but-right {
            width:50%;
            float:right;
        }
</style>
END_OF_TEXT;
$content = $menu->showhead();
$content .= $menu->showpanel("กระบวนการพิมพ์และเคลือบ","");

$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$pid = filter_input(INPUT_GET,'pid',FILTER_SANITIZE_NUMBER_INT);

$p_process = array("0"=>"-- กระบวนการ --") + $db->get_mcat("('printing')");
$machs = $db->get_amach($coid);
if($action == "add"){
    //add
    $form = new myform('new','cheight');
    $content .= "<h1 class='page-title'>เพิ่มกระบวนการพิมพ์</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-100'>"
            . "<div class='col-40'>"
            . $form->show_text("name","name","","เช่น พิมพ์ 4 สีไม่เคลือบ","ชื่อ","","label-inline")
            . "</div><!-- .col-40 -->"
            . "<div class='col-60'>"
            . "<h2>กระบวนการผลิต</h2>"
            . "<div class='process-area'>";
    $pno = 6;
    for($j=1;$j<$pno;$j++){
        $mach = array("0"=>"-- ไม่ใช้งาน --");
        $content .= "<div class='process-box'>"
                . $form->show_select("process_$j", $p_process, "label-inline sel-process")
                . $form->show_select("mach_$j", $mach, "label-inline",null,null,"","mach[]")
                . $form->show_num("mult_$j",1,1,"","ผ่าเป็น(ส่วน)","","label-3070 form-hide","","mult[]")
                . $form->show_hidden("seq_$j","seq[]",$j)
                . "</div><!-- .process-box -->"
                //. "<div class='transit-box'>"
                //. $form->show_select("transit[]", $wip_transit,"label-inline")
                //. "</div>"
                . ($j==($pno-1)?"":"<div><img src='".$aroot."/image/arrow-down.png' /></div>");
    }
    $content .= "</div></div><!-- .col-60 -->";
    
    $content .= $form->show_submit("submit","Add New","but-right")
            . $form->show_hidden("request","request","add_print")
            . $form->show_hidden("coid","coid",$coid)
            . $form->show_hidden("redirect","redirect",$root."process.php")
            . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", array('name'),null,null,null,null,array('process_1','mach_1'));
    $content .= $form->submitscript("$('#new').submit();");
} else if(isset($pid)&&$db->check_process($pid,$coid)){
    //load data
    $pinfo = $db->view_pinfo($pid);
    $print_process = $db->view_process($pid);
    //edit
    $form = new myform('edit','cheight');
    $content .= "<h1 class='page-title'>แก้ไขกระบวนการพิมพ์</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-100'>"
            . "<div class='col-40'>"
            . $form->show_text("name","name",$pinfo['name'],"เช่น ปก 4 สีกระดาษอาร์มมัน 160 ไม่เคลือบ","ชื่อส่วนประกอบ","","label-inline")
            . "</div><!-- .col-40 -->"
            . "<div class='col-60'>"
            . "<h2>กระบวนการผลิต</h2>"
            . "<div class='process-area'>";
    $pno = 6;
    for($j=1;$j<$pno;$j++){
        if(!isset($print_process[$j-1])||$print_process[$j-1]['machine_id']=="0"){
            $maid = 0;
            $mcat = 0;
            $mach = array("0"=>"-- ไม่ใช้งาน --");
            $mult = 1;
        } else {
            $maid = $print_process[$j-1]['machine_id'];
            $tm = $db->view_macinfo($maid);
            $mcat = $tm['machine_cat_id'];
            $mach = $db->get_machine($coid,$mcat);
            $mult = $print_process[$j-1]['input_mult'];
        }
        $cut_class = ($mcat==1?"":"form-hide");
        $content .= "<div class='process-box'>"
                . $form->show_select("process_$j", $p_process, "label-inline sel-process",null,$mcat)
                . $form->show_select("mach_$j", $mach, "label-inline",null,$maid ,"","mach[]")
                . $form->show_num("mult_$j",$mult,1,"","ผ่าเป็น(ส่วน)","","label-3070 $cut_class","","mult[]")
                . $form->show_hidden("seq_$j","seq[]",$j)
                . "</div><!-- .process-box -->"
                . ($j==($pno-1)?"":"<div><img src='".$aroot."/image/arrow-down.png' /></div>");
    }
    $content .= "</div></div><!-- .col-60 -->";
    
    //del process
    if($_SESSION['rms_l']>1){
        $redirect = $root."process.php";
        $requrl = $aroot."request.php";
        $content .= "<div id='del-process-but' class='red-but'>ลบกระบวนการพิมพ์</div><!-- .del-but -->"
                . "<script>"
                . "del_process($pid,'$redirect','$requrl');"
                . "</script>";
    }
    
    $content .= $form->show_submit("submit","Update","but-right")
            . $form->show_hidden("request","request","edit_print")
            . $form->show_hidden("pid","pid",$pid)
            . $form->show_hidden("redirect","redirect",$root."process.php")
            . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", array('name'),null,null,null,null,array('process_1','mach_1'));
    $content .= $form->submitscript("$('#edit').submit();");
} else {
    //show all
    $add = $root."process.php?action=add";
    $content .= "<h1 class='page-title'>รายการกระบวนการพิมพ์และเคลือบ<a class='add-new' href='$add' title='Add New'>Add New</a></h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>";
    
    $tb = new mytable();
    $head = array("แก้ไข","ชื่อ","Process");
    $rec = $db->view_comp($coid);
    $content .= "<div class='col-100'>"
            . $tb->show_table($head,$rec,"tb-process")
            . "</div>";
}
$content .= "<script>"
        . "process_sel(".json_encode($machs).");"
        . "</script>";

$content .= $menu->showfooter();
echo $content;