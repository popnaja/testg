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
__autoload("menu");
__autoload("pdo_g");
$db = new greenDB();
$menu = new mymenu("th");
$menu->__autoloadall("form");
$menu->__autoloadall("table");
$menu->menu($_SESSION['rms_l']);
$menu->pageTitle = "โปรแกรมคำนวณ Carbon Footprint | ". SITE;
$menu->extrascript = <<<END_OF_TEXT

END_OF_TEXT;
$content = $menu->showhead();
$content .= $menu->showpanel("ผู้ดูแลระบบ","Transport");


$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$tid = filter_input(INPUT_GET,'tid',FILTER_SANITIZE_NUMBER_INT);


if($action == "add"){
    //add
    $form = new myform('new','cheight');
    $content .= "<h1 class='page-title'>เพิ่มวิธีการขนส่ง</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-50'>"
            . $form->show_text("name","name","","","ชื่อ","","label-inline")
            . $form->show_num("maxload","",1,"","โหลดสูงสุด (ตัน)","","label-inline")
            . $form->show_select("ref", $refer, "left-50 label-inline","ที่มา","แนวทางการประเมิณฯ")
            . "</div><!-- .col-50 -->"
            . "<div class='col-50'>";
    $load = [0,50,75,100];
    $ck = ['name','maxload','ref'];
    for($i=0;$i<4;$i++){
        $content .= $form->show_text("load_$i","load[]",$load[$i],"","โหลด(%)","","left-50 label-inline")
            . $form->show_text("ef_$i","ef[]","","","EF (tkm)","","right-50 label-inline");
        array_push($ck,"load_$i","ef_$i");
    }
    $content .= $form->show_submit("submit","Add New","but-right")
            . $form->show_hidden("request","request","add_new_transport")
            . $form->show_hidden("redirect","redirect",$root."transport.php")
            . "</div><!-- .col-50 -->";
    $form->addformvalidate("ez-msg", $ck);
    $content .= $form->submitscript("$('#new').submit();");
    
} else if(isset($tid)){
    //load data
    $info = $db->view_transport($tid);
    $load = $db->view_load($tid);
    //edit
    $form = new myform('edit','cheight');
    $content .= "<h1 class='page-title'>แก้ไขการขนส่ง</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-50'>"
            . $form->show_text("name","name",$info['name'],"","ชื่อ","","label-inline")
            . $form->show_num("maxload",$info['maxload'],1,"","โหลดสูงสุด (ตัน)","","label-inline")
            . $form->show_select("ref", $refer, "left-50 label-inline","ที่มา",$info['reference'])
            . "</div><!-- .col-50 -->"
            . "<div class='col-50'>";
    $ck = ['name','maxload','ref'];
    for($i=0;$i<4;$i++){
        $content .= $form->show_text("load_$i","load[]",$load[$i]['tload'],"","โหลด(%)","","left-50 label-inline")
            . $form->show_text("ef_$i","ef[]",$load[$i]['ef'],"","EF (tkm)","","right-50 label-inline")
            . $form->show_hidden("lid_$i","lid[]",$load[$i]['id']);
        array_push($ck,"load_$i","ef_$i");
    }
    $content .= $form->show_submit("submit","update","but-right")
            . $form->show_hidden("request","request","edit_transport")
            . $form->show_hidden("tid","tid",$tid)
            . $form->show_hidden("redirect","redirect",$root."transport.php")
            . "</div><!-- .col-50 -->";
    $form->addformvalidate("ez-msg", $ck);
    $content .= $form->submitscript("$('#edit').submit();");
} else {
    //show all
    $add = $root."transport.php?action=add";
    $content .= "<h1 class='page-title'>ขนส่ง<a class='add-new' href='$add' title='Add new Transport'>Add New</a></h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>";
    
    $tb = new mytable();
    $head = ["ชื่อ",'โหลดสูงสุด(ตัน)','ที่มา'];
    $rec = $db->view_transport();
    $content .= "<div class='col-100'>"
            . $tb->show_table($head,$rec)
            . "</div>";
}

$content .= $menu->showfooter();
echo $content;

