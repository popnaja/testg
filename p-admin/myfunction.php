<?php
include_once("p-config.php");
define("ADMIN_PATH",dirname(__FILE__));
function __autoload($class_name) {
    include_once ('class.'.$class_name .'.php');
}
function __autoloada($class_name) {
    include_once(ADMIN_PATH."/class.".$class_name.".php");
}
function dbConnect($dbname) {
    $conn = new PDO("mysql:host=".DB_HOST.";dbname=".$dbname,DB_USER,DB_PASSWORD);
    //set pdo error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $sql = "SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'";
    $conn->exec($sql);
    return $conn;
}
function current_url(){
    return "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}
function db_error($func,$ex){
    echo "ERROR: $func ". $ex->getMessage();
}
function __loadctclass($class_name){
    include_once (dirname(dirname(__FILE__)). '/p-content/class.'. $class_name .'.php');
}
function __clk($redirect=null){
    if(isset($_SESSION['pg_admin'])){
        return;
    } else if(!cal_cookies()&&!isset($_SESSION['pg_admin'])){
        $_SESSION['login_redirect'] = $redirect;
        header('Location: '.A_SURL."/login.php");
    } else if(!isset($_SESSION['pg_admin'])&&cal_cookies()){
        $_SESSION['pg_admin'] = $_COOKIE['pg_id'];
    }
}
function clear_coupon(){
    if(isset($_SESSION['pg-ckout-track'])){
        unset($_SESSION['pg-ckout-track']);
    }
}
function cal_cookies(){
    //echo "YO</br></br></br>";
    $cal = filter_input(INPUT_COOKIE,'pg_id',FILTER_SANITIZE_NUMBER_INT);
    $cooked = filter_input(INPUT_COOKIE,'pg_date',FILTER_UNSAFE_RAW);
    $ing = filter_input(INPUT_COOKIE,'pg_rb',FILTER_SANITIZE_NUMBER_INT);
    if(isset($cal) && isset($cooked) && isset($ing)) {
        $gpdo = new pdo_get();
        $um = $gpdo->get_usermeta($cal);
        $ua = $gpdo->get_userinfo($cal);
        if(!isset($um['rb'])){
            return false;
        }
        if(!isset($ua['u_added'])){
            return false;
        }
        $rb = $um['rb'];
        $uadd = $ua['u_added'];
        $new = date_sub(date_create($uadd), date_interval_create_from_date_string($rb." days"));
        $new = date_format($new,"Y-m-d H:i:s");
        //var_dump($rb.$new==$ing.$cooked);
        if($rb.$new==$ing.$cooked){
            return true;
        } else {
            //$_SESSION['error'] = "rb.new not same as cookie";
            return false;
        }
    } else {
        return false;
    }
}
function showmsg(){
    if(isset($_SESSION['message'])){
        $html = newstatus($_SESSION['message'],true);
        unset($_SESSION['message']);
    } else if (isset($_SESSION['error'])){
        $html = newstatus($_SESSION['error'],false);
        unset($_SESSION['error']);
    } else {
        $html = "";
    }
    return $html;
}
function showstatus($message=null,$ok=1){
    if($message != ""){
        $class = ($ok==1?"ok":"ng");
        $html = "<div id='ez-msg-wrap'><div id='ez-message' class='$class'>\n"
                . "<button id='closemsg' type='button' class='icon-remove'>\n"
                . "</button>"
                . "<p>$message</p>\n"
                . "</div>\n"
                . "<script>\n"
                . "$(document).ready(function(){"
                . "$('#closemsg').click(function(){"
                . "$('#ez-msg-wrap').remove();"
                . "});"
                . "});"
                . "</script>\n"
                . "</div>\n";
        if(isset($_SESSION['message'])){
            unset($_SESSION['message']);
        }
        if(isset($_SESSION['error'])){
            unset($_SESSION['error']);
        }
    } else {
        return "";
    }
    return $html;
}
function newstatus($message=null,$ok=true){
    if($message != ""){
        $class = ($ok?"ok-msg":"ng-msg");
        $icon = ($ok?"icon-check-mark-circle":"icon-alert");
        $html = "<div id='pg-msg-wrap'><div id='pg-message' class='$class'>"
                . "<span class='icon-remove close-msg'></span>"
                . "<span class='pg-msg-icon $icon'></span>"
                . "<p>$message</p>\n"
                . "</div>\n"
                . "<script>\n"
                . "$(document).ready(function(){"
                . "$('.close-msg').click(function(){"
                . "$(this).parent().parent().remove();"
                . "});"
                . "});"
                . "</script>\n"
                . "</div>\n";
        if(isset($_SESSION['message'])){
            unset($_SESSION['message']);
        }
        if(isset($_SESSION['error'])){
            unset($_SESSION['error']);
        }
    } else {
        return "";
    }
    return $html;
}
function is_thai($string){
    preg_match("/[ก-ฮ]+/",$string,$matches);
    if(sizeof($matches,0)>0){
        return true;
    } else {
        return false;
    }
}
/*+1 to file's name if file alerady exists */
function check_exist($target){
    $ext = ".".pathinfo($target, PATHINFO_EXTENSION);
    if(file_exists($target)){
        $i=0;
        do {
            $i++;
            $ntarget = str_replace($ext,"-".$i.$ext,$target);
        } while (file_exists($ntarget));
        return $ntarget;
    } else {
        return $target;
    }
}
function name_to_url($name,$pid=null){
    function toLower($matches){
        return strtolower($matches[0]);
    }
    $rep = "/^\\s+|\\s+$/"; //clear space,tab before and after
    $rep1 = "/(\\.)+|( )+/"; //replace dot and space with -
    $rep2 = "/[!@#$%^&*()+=\\|\\/\\[\\]\\{\\};:'\",<>\\?\\\]/"; //replace special char with blank
    $rep3 = "/[A-Z]{1}/"; //replace capital with lowercase
    $nname = preg_replace($rep,"",$name);
    $nname = preg_replace($rep1,"-",$nname);
    $nname = preg_replace($rep2,"",$nname);
    $nname = preg_replace_callback($rep3,"toLower",$nname);
    __autoload("pdo_get");
    $gpdo = new pdo_get();
    $n = 2;
    while(!$gpdo->check_post_val("post_slug",$nname,$pid)){
        $nname = $nname."-".$n;
        $n++;
    }
    return $nname;
}
function get_thumbimg($img){
    $ext = pathinfo($img,PATHINFO_EXTENSION);
    return str_replace(".".$ext,"_thumb.$ext",$img);
}
function ifnull($obj,$return=""){
    return (isset($obj)?$obj:$return);
}
function gen_sql($arr,$connect,$n){
    $i=0;
    $res = "";
    $narr = [];
    foreach($arr AS $k=>$v){
        $res .= ($i==0?"":$connect);
        $res .= "$k=:$n$i";
        $narr += [$n.$i=>$v];
        $i++;
    }
    return [$res,$narr];
}
function green_user($page,$level){
    $green_allow = [
        "transport.php" => [9],
        "user.php" => [9],
        "cat.php" => [9],
        "machine_cat.php" => [9],
        "company.php" => [9]
    ];
    if(isset($green_allow[$page])){
        if(in_array($level,$green_allow[$page])){
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}
