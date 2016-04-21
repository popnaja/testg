<?php
session_start();
include_once(dirname(__FILE__)."/p-admin/myfunction.php");
__autoload("pdo_g");
$db = new greenDB();
$meta = array(
    "l_status" => ""
);
$db->update_user_meta($_SESSION['rms_user'], $meta);
session_unset();
header("location:".ROOTS);
exit();

