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
$redirect = $root.basename(__FILE__);
$menu = new mymenu("th");
$menu->__autoloadall("form");
$menu->__autoloadall("table");
$menu->menu($_SESSION['rms_l']);
$menu->pageTitle = "โปรแกรมคำนวณ Carbon Footprint | ". SITE;
$menu->extrascript = <<<END_OF_TEXT
<style>
#company-tb{
        word-break:break-all;
        }
</style>
END_OF_TEXT;
$content = $menu->showhead();
$content .= $menu->showpanel("ผู้ดูแลระบบ","Company");


$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$cid = filter_input(INPUT_GET,'cid',FILTER_SANITIZE_NUMBER_INT);


if(isset($cid)){
    //load data
    $info = $db->view_company($cid)+$db->get_meta("company_meta", "company_id", $cid);
    //edit
    $form = new myform('edit','cheight');
    $ele_type = $db->get_mat($cid,"7",false);
    $paper_waste = $db->get_mat($cid,"8",false);
    $plate_waste = $db->get_mat($cid,"9",false);
    $waste = "<div class='form-section'>"
            . "<h4>แหล่งไฟฟ้า, การจัดการกระดาษเสียและแม่พิมพ์</h4>"
            . $form->show_select("ele_type", $ele_type, "label-3070","ไฟฟ้า",(isset($info['ele_type'])?$info['ele_type']:null))
            . $form->show_select("paper_waste", $paper_waste, "label-3070","เศษกระดาษ",(isset($info['paper_waste'])?$info['paper_waste']:null))
            . $form->show_select("plate_waste", $plate_waste, "label-3070","แม่พิมพ์ใช้แล้ว",(isset($info['plate_waste'])?$info['plate_waste']:null))
            . "</div><!-- .form-section -->";
    $content .= "<h1 class='page-title'>Edit Company</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-100'>"
            . $form->show_text("name","name",$info['name'],"","Name","","label-inline")
            . $form->show_text("email","email",$info['email'],"","Email","","label-inline")
            . $form->show_text("tel","tel",$info['tel'],"","Tel","","label-inline")
            . $waste
            . $form->show_submit("submit","Update","but-right")
            . $form->show_hidden("request","request","edit_company")
            . $form->show_hidden("cid","cid",$cid)
            . $form->show_hidden("redirect","redirect",$redirect)
            . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", ['name','email','tel'],null,'email');
    $content .= $form->submitscript("$('#edit').submit();");
} else {
    //add
    $form = new myform('new','cheight');
    $content .= "<h1 class='page-title'>Company</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>"
            . $form->show_st_form()
            . "<div class='col-40'>"
            . $form->show_text("name","name","","","Name","","label-inline")
            . $form->show_text("email","email","","","Email","","label-inline")
            . $form->show_text("tel","tel","","","Telephone","","label-inline")
            . $form->show_submit("submit","Add New","but-right")
            . $form->show_hidden("request","request","add_company")
            . $form->show_hidden("redirect","redirect",$root."company.php")
            . $form->show_hidden("referurl","referurl",$aroot."request.php")
            . "</div><!-- .col-40 -->";
    $form->addformvalidate("ez-msg", ['name','email','tel'],null,'email');
    $content .= $form->submitscript("$('#new').submit();");
    
    //show all
    $tb = new mytable();
    $head = ['Name','Email',"Tel",'Added'];
    $rec = $db->view_company();
    $content .= "<div class='col-60'>"
            . $tb->show_table($head,$rec,"company-tb","")
            . "</div>";
}

$content .= $menu->showfooter();
echo $content;

