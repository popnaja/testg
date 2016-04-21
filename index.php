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
$menu->menu($_SESSION['rms_l']);
$menu->pageTitle = "โปรแกรมคำนวณคาร์บอนฟุตพริ้นท์ Carbon Footprint Calculator | ". SITE;
$menu->extrascript = <<<END_OF_TEXT
<style>
       .logo {
        text-align:center;
        }
        .logo h1 {
        position:relative;
        top:-30px;
   }
        .blue-but {
        display:inline-block;
        width:300px;
        padding-top:25px;
        padding-bottom:25px;
        font-size:20px;
        margin-bottom:30px;
        }
        .blue-but a {
            color:#fff;
            text-decoration:none;
        }
#start-cal {
    margin-top:30px;
    padding-top:70px;
    padding-bottom:70px;
}
</style>
END_OF_TEXT;
$content = $menu->showhead();
$content .= $menu->showpanel("หน้าแรก","");

$img = $aroot."image/carbon_logo_re2.png";
$exid = $db->view_exid($coid);
$link1 = $root."calculate.php?action=res&fid=$exid";
$link2 = $root."calculate.php?action=add";
$content .= "<div class='logo'>"
        . "<img src='$img' />"
        . "<div>"
        . "<a href='$link1' title='ตัวอย่างการคำนวณ'><div class='blue-but'>ตัวอย่างการคำนวณ</div></a><br/>"
        . "<a href='$link2' title='เริ่มคำนวณ'><div id='start-cal' class='blue-but'>เริ่มคำนวณ</div></a><br/>"
        . "</div>"
        . "</div>";

$content .= $menu->showfooter();
echo $content;