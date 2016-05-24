<?php
session_start();
include_once(dirname(__FILE__)."/p-admin/myfunction.php");
include_once("g_option.php");
$root = ROOTS;
$aroot = AROOTS;
__autoload("menu");
__autoload("pdo_g");
$db = new greenDB();

//correct distribute
/*
$arrfid = $db->get_keypair("function_unit", "id", "id", "");
foreach($arrfid AS $k=>$v){
    $fid = $v;
    $info = $db->get_info("function_unit", "id", $fid);
    $meta = $db->get_meta("fn_meta", "function_unit_id", $fid);
    $dinfo = json_decode($meta['dis_info'],true);
    $ninfo = array(
        "ef" => $dinfo['ef'],
        "gas" => $dinfo['gas'],
        "lperkg" => $dinfo['lperkg']
    );
    $disv = array();
    array_push($disv,array(
        "vehicle" => $dinfo['vehicle'],
        "amount" => $info['amount'],
        "distance" => $dinfo['distance'],
        "go" => $dinfo['goload'],
        "back" => $dinfo['backload']
    ));
    $meta = array(
        "dis_info" => json_encode($ninfo),
        "dis_v_info" => json_encode($disv)
    );
    $db->update_meta("fn_meta", "function_unit_id", $fid, $meta);
}
 * 
 */
