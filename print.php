<?php
session_start();
include_once(dirname(__FILE__)."/p-admin/myfunction.php");
include_once("g_option.php");
include_once("carbon.php");

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
$menu->astyle[] = $root."css/report.css";
$menu->ascript[] = $aroot."js/smartgreeny.js";
$menu->extrascript = <<<END_OF_TEXT
<style>
body{
    padding:0; !important;
    background-color:#fff;
}
</style>
END_OF_TEXT;
$content = $menu->showhead();


$fid = filter_input(INPUT_GET,'fid',FILTER_SANITIZE_NUMBER_INT);
$type = filter_input(INPUT_GET,'type',FILTER_SANITIZE_STRING);

if(isset($fid)&&$type=="detail"&&$db->check_fn($fid,$coid)){
    $tb = new mytable();
    $info = calculate_carbon($fid);
    $output = $info[3]['output'];
    $finfo = $db->view_finfo($fid);
    if($output>=$finfo['amount']){
        $amount = $finfo['amount'];
    } else {
        $amount = $output;
        $less = number_format($finfo['amount']-$output,0);
    }
    $carbon = $tb->tb_carbon_tt($info[2],$info[3]);
    $content .= "<div class='print-land'>"
            . "<h2>รายละเอียดการคำนวณ Carbon Footprint</h2>"
            . "<h3>ผลิตภัณฑ์ : ".$finfo['name']."</h3>"
            . $carbon[0]
            . "</div><!-- .print-land -->";
} else if(isset($fid)&&$db->check_fn($fid,$coid)){
    $finfo = $db->view_finfo($fid);
    $fnmeta = $db->view_fn_meta($fid);
    $unit = $type_to_unit[$finfo['type']];
    
    //report title
    $report_title = "Carbon Footprint by ".($coid==1?$fnmeta['sub_comp']:"Smart Greeny");
    $customer = "บริษัท : ".$fnmeta['cname'];
    $product = "ผลิตภัณฑ์ : ".$finfo['name'];
    $logo = $aroot."image/carbon_logo_re2.png";
    
    //show res
    $tb = new mytable();
    $info = calculate_carbon($fid);
    $output = $info[3]['output'];
    $acol = $info[3]['column'];
    if($output>=$finfo['amount']){
        $amount = $finfo['amount'];
        $remark = "";
    } else {
        $amount = $output;
        $less = number_format($finfo['amount']-$output,0);
        $remark = "จำนวนชิ้นงานสำเร็จน้อยกว่ายอดที่ตั้งไว้ $less หน่วย ตรวจสอบข้อมูลอีกครั้ง <a href='calculate.php?fid=$fid' title='Edit' class='icon-page-edit'></a>";
    }
    $logo = $aroot."image/carbon_logo_re2.png";
    $carbon = $tb->tb_carbon_total($info[2],$info[3],$unit);
    $show_cb = ($carbon[1]<1?number_format($carbon[1]*1000,0)." g.CO2eq/$unit":number_format($carbon[1],3)." kg.CO2eq/$unit");
    $content .= "<div class='print-report'>"
            . "<div class='report-sum'>"
            . "<div class='report-head'>"
            . "<div class='report-title'>"
            . "<h1>$report_title</h1>"
            . "<h2>$customer</h2>"
            . "<h2>$product</h2>"
            . "</div>"
            . "<div class='report-logo'>"
            . "<img src='$logo' alt='รายงานค่าคาร์บอนฟุตพริ้นท์โดยโปรแกรม Smart Greeny' />"
            . "<h4>$show_cb</h4>"
            . "</div>"
            . "</div><!-- .report-head -->"
            . "<h2 class='tb-title'>ปริมาณคาร์บอนฟุตพริ้นท์ งานพิมพ์จำนวน ".number_format($amount,0)." $unit</h2>"
            . $carbon[0]
            . "</div><!-- .report-sum -->"
            . "<div class='report-detail'>"
            . "<h2 class='tb-title'>รายละเอียดการพิมพ์</h2>"
            . $tb->tb_carbon_sum($info[1])
            . $tb->tb_carbon($acol,$info[0])
            . "</div>"
            . "</div><!-- .c-report -->"
            . "<script>"
            . "open_print();"
            . "</script>";
} else {
    header("location:".$root."calculate.php");
}

echo $content;

