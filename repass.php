<?php
session_start();
include_once(dirname(__FILE__)."/p-admin/myfunction.php");
include_once("g_option.php");

if(!isset($_SESSION['rms_user'])){
    header("location:".ROOTS."login.php");
}
$root = ROOTS;
$aroot = AROOTS;
__autoload("menu");
__autoload("pdo_g");
$db = new greenDB();
$menu = new mymenu("th");
$menu->__autoloadall("form");
$menu->menu($_SESSION['rms_l']);
$menu->pageTitle = SITE;
$menu->extrascript = <<<END_OF_TEXT
<style>
</style>
END_OF_TEXT;
$content = $menu->showhead();
$content .= $menu->showpanel("เปลี่ยนรหัสผ่าน","");

$uid = $_SESSION['rms_user'];
//load data
$info = $db->view_uinfo($uid);
$umeta = $db->view_umeta($uid);
//edit
$form = new myform('edit','cheight');
$content .= "<h1 class='page-title'>เปลี่ยนรหัสผ่าน</h1>"
        . "<div id='ez-msg'>".  showmsg() ."</div>"
        . $form->show_st_form()
        . "<div class='col-100'>"
        . $form->show_text("email","email",$info['email'],"","Email","","label-inline",null,"readonly")
        . $form->show_text("pass","pass","","password","","","label-inline","password"," maxlength='32'")
        . $form->show_text("repass","repass","","ใส่ password อีกครั้ง","","","label-inline","password"," maxlength='32'")
        . "<div id='pass-indicator' class='p-indi'>Strength Indicator</div>"
        . $form->show_submit("submit","Update","but-right")
        . $form->show_hidden("request","request","edit_upass")
        . $form->show_hidden("uid","uid",$uid)
        . $form->show_hidden("redirect","redirect",$root."repass.php")
        . "</div><!-- .col-100 -->";
$form->addformvalidate("ez-msg", ['email','pass','repass'],['pass','repass'],'email');
$content .= $form->submitscript("$('#edit').submit();")
        . "<script>"
        . "pass_strength('pass','repass','pass-indicator');"
        . "</script>";

$content .= $menu->showfooter();
echo $content;