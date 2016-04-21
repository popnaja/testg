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
$menu->astyle[] = $aroot."js/jquery-ui-1.11.4.custom/jquery-ui.css";
$menu->ascript[] = $aroot."js/jquery-ui-1.11.4.custom/jquery-ui.min.js";
$menu->extrascript = <<<END_OF_TEXT
<style>
#user-tb th:first-child,
#user-tb td:first-child {
    width:20px;
}
#user-tb td {
    word-break:break-all;
}
.user-expired {
        color:red;
}
</style>
END_OF_TEXT;
$content = $menu->showhead();
$content .= $menu->showpanel("ผู้ดูแลระบบ","User");


$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$uid = filter_input(INPUT_GET,'uid',FILTER_SANITIZE_NUMBER_INT);

$cs = $db->sel_company();
if(isset($uid)){
    //load data
    $info = $db->view_uinfo($uid);
    $umeta = $db->view_umeta($uid);
    //edit
    $form = new myform('edit','cheight');
    $content .= "<h1 class='page-title'>แก้ User</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-100'>"
            . $form->show_text("email","email",$info['email'],"","Email","","label-inline")
            . $form->show_text("pass","pass","","password","","","label-inline","password"," maxlength='32'")
            . $form->show_text("repass","repass","","ใส่ password อีกครั้ง","","","label-inline","password"," maxlength='32'")
            . "<div id='pass-indicator' class='p-indi'>Strength Indicator</div>"
            . $form->show_select('user_company', $cs, "label-inline", "User Company",$umeta['user_company'])
            . $form->show_select('user_level', $user_level, "label-inline", "User Level",$umeta['user_level'])
            . $form->show_text("user_expired","user_expired",$umeta['user_expired'],"","User Expired","","label-inline")
            . $form->show_submit("submit","Update","but-right")
            . $form->show_hidden("request","request","edit_user")
            . $form->show_hidden("uid","uid",$uid)
            . $form->show_hidden("redirect","redirect",$root."user.php?uid=$uid")
            . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", ['email'],['pass','repass']);
    $content .= $form->submitscript("$('#edit').submit();")
            . "<script>"
            . "pass_strength('pass','repass','pass-indicator');"
            . "$('#user_expired').datepicker({dateFormat: 'yy-mm-dd'});"
            . "</script>";
} else {
    //add
    $form = new myform('new','cheight');
    $content .= "<h1 class='page-title'>User</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-40'>"
            . $form->show_text("email","email","","","Email","","label-inline")
            . $form->show_text("pass","pass","","password","","","label-inline","password"," maxlength='32'")
            . $form->show_text("repass","repass","","ใส่ password อีกครั้ง","","","label-inline","password"," maxlength='32'")
            . "<div id='pass-indicator' class='p-indi'>Strength Indicator</div>"
            . $form->show_select('user_company', $cs, "label-inline", "User Company")
            . $form->show_select('user_level', $user_level, "label-inline", "User Level")
            . $form->show_text("user_expired","user_expired","","","User Expired","","label-inline")
            . $form->show_submit("submit","Add New","but-right")
            . $form->show_hidden("request","request","add_user")
            . $form->show_hidden("redirect","redirect",$root."user.php")
            . $form->show_hidden("referurl","referurl",$aroot."request.php")
            . "</div><!-- .col-40 -->";
    $form->addformvalidate("ez-msg", ['email','pass','repass'],['pass','repass']);
    $content .= $form->submitscript("$('#new').submit();")
            . "<script>"
            . "pass_strength('pass','repass','pass-indicator');"
            . "$('#user_expired').datepicker({dateFormat: 'yy-mm-dd'});"
            . "</script>";
    
    //show all
    $company = $db->get_keypair("company", "id", "name");
    $page = (isset($_GET['page'])?filter_input(INPUT_GET,'page',FILTER_SANITIZE_STRING):1);
    $cid = (isset($_GET['cid'])&&$_GET['cid']>0?$_GET['cid']:null);
    $tb = new mytable();
    $head = ["<span class='icon-remove'></span>",'Email',"Company",'Added',"Expired"];
    $iperpage = 20;
    $rec = $db->view_user($cid,$page,$iperpage);
    $all_rec = $db->view_user($cid);
    $max = ceil(count($all_rec)/$iperpage);
    $content .= "<div class='col-60'>"
            . $tb->show_filter(current_url(), "cid", $company, $cid,"บริษัท")
            . $tb->show_pagenav(current_url(), $page, $max)
            . $tb->show_table($head,$rec,"user-tb","user_del()")
            . "</div>";
}

$content .= $menu->showfooter();
echo $content;

