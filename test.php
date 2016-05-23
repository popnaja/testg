<?php
session_start();
include_once(dirname(__FILE__)."/p-admin/myfunction.php");
include_once("g_option.php");
$root = ROOTS;
$aroot = AROOTS;
__autoload("menu");
__autoload("pdo_g");
$db = new greenDB();

//correct mat table
$oldtonew = array(
    "แนวทางการประเมิณฯ" => 2,
    "Supplier" => 1,
    "Thai Research" => 3,
    "International Research" => 4,
    "Custom" => 5
);
/*
$mat = $db->get_keypair("mat", "id", "reference");
foreach($mat as $id=>$oldref){
    $newref = $oldtonew[$oldref];
    $db->update_data("mat", "id", $id, array("ref_id"=>$newref,"evidence"=>($newref==2?"Updated:2016-02":"")));
}
$trans = $db->get_keypair("transport", "id", "reference");
foreach($trans as $id=>$oldref){
    $newref = $oldtonew[$oldref];
    $db->update_data("transport", "id", $id, array("ref_id"=>$newref,"evidence"=>($newref==2?"Updated:2016-02":"")));
}
 * 
 */

