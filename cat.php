<?php
session_start();
include_once(dirname(__FILE__)."/p-admin/myfunction.php");

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
$content .= $menu->showpanel("ผู้ดูแลระบบ","Material Cat");


$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$cid = filter_input(INPUT_GET,'cid',FILTER_SANITIZE_NUMBER_INT);

if(isset($cid)){
    //load data
    $info = $db->view_cat($cid)[0];
    //edit
    $form = new myform('edit','cheight');
    $content .= "<h1 class='page-title'>แก้แคดตากอรี่วัสดุ</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-100'>"
            . $form->show_text("name","name",$info['name'],"","ชื่อ","","label-inline")
            . $form->show_textarea("des", $info['des'], 4, 10, "", "คำอธิบาย", "label-inline")
            . $form->show_submit("submit","Update","but-right")
            . $form->show_hidden("request","request","edit_mat_cat")
            . $form->show_hidden("cid","cid",$cid)
            . $form->show_hidden("redirect","redirect",$root."cat.php?cid=$cid")
            . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", ['name']);
    $content .= $form->submitscript("$('#edit').submit();");
} else {
    //add
    $form = new myform('new','cheight');
    $content .= "<h1 class='page-title'>แคดตากอรี่วัสดุ</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-50'>"
            . $form->show_text("name","name","","","ชื่อ","","label-inline")
            . $form->show_textarea("des", "", 4, 10, "", "คำอธิบาย", "label-inline")
            . $form->show_submit("submit","Add New","but-right")
            . $form->show_hidden("request","request","add_mat_cat")
            . $form->show_hidden("redirect","redirect",$root."cat.php")
            . "</div><!-- .col-50 -->";
    $form->addformvalidate("ez-msg", ['name']);
    $content .= $form->submitscript("$('#new').submit();");
    
    //show all
    $tb = new mytable();
    $head = ["ชื่อ",'คำอธิบาย','จำนวนวัสดุ'];
    $rec = $db->view_cat();
    $content .= "<div class='col-50'>"
            . $tb->show_table($head,$rec)
            . "</div>";
}

$content .= $menu->showfooter();
echo $content;

