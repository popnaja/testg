<?php
session_start();
include_once(dirname(__FILE__)."/p-admin/myfunction.php");

$root = ROOTS;
__autoload("menu");
$menu = new mymenu("th");
$menu->__autoloadall("form");
$menu->pageTitle = "เข้าสู่ระบบ | ". SITE;
$menu->extrascript = <<<END_OF_TEXT
<style>
        body{
            background-color:#00925e;
        }
        #content,footer {
            padding-left:0;
        }
        #login-box {
            width:300px;
            margin-left:auto;
            margin-right:auto;
            text-align:center;
            box-sizing:border-box;
            padding: 20px;
            padding-top:0;
            box-shadow: 2px 3px 4px rgba(0,0,0,0.2);
            background-color: #f5f5f5;
            color: rgba(0,0,0,.84);
        }
        #img-logo {
            width:250px;
            height:auto;
        }
        a{
            text-decoration:none;
        }
        a:hover {
            text-decoration:underline;
        }
        #forget-pass {
            display:block;
            padding-bottom:25px;
        }
        
        #back-tologin {
            display:inline-block;
            padding-top:10px;
        }
        h3 {
            position:relative;
            top:-25px;
        }
</style>
END_OF_TEXT;
$menu->show_contact();
$content = $menu->showhead();
$content .= $menu->showpanel("", "", false);

$form = new myform("ulogin");
$logo = AROOTS."image/carbon_logo_re2.png";
$fg = $root."login.php?f";
$login = $root."login.php";
$content .= $menu->contact
        . "<div id='login-box'>"
        . "<div><img id='img-logo' src='$logo' alt='Smart Greeny Logo' /></div>"
        . showmsg()
        . "<div class='float-box-inside'>";
$f = filter_input(INPUT_GET,'f',FILTER_SANITIZE_STRING);
if(isset($f)){
    //forget pass
    $content .= $form->show_st_form()
        . $form->show_text("email","email","","Email","","","label-inline","email")
        . $form->show_submit("login_submit","ขอเปลี่ยนรหัสผ่าน","but-100")
        . $form->show_hidden("request","request","pass-reset")
        . $form->show_hidden("error_direct","error_direct",$root."login.php")
        . $form->show_hidden("redirect","redirect",$root)
        . "<a href='$login' id='back-tologin' title='กลับไปหน้าเข้าสู่ระบบ'>กลับไปหน้าเข้าสู่ระบบ</a>";
$form->addformvalidate("float-msg",array('email'),null,'email');
$arrn = json_encode($form->array_name);
$content .= $form->submitscript("$('#ulogin').submit();")
        . "</div><!-- .float-box-inside -->"
        . "</div><!-- login-box -->";
} else if(isset($_GET['r'])){
    //reset pass
    __autoload("pdo_g");
    $db = new greenDB();
    $rq = $db->checkr($_GET['r']);
    if(!is_numeric($rq)){
        $_SESSION['error']="การขอเปลี่ยนรหัสผ่านหมดอายุ";
        header("location:".$root."login.php");
        exit();
    } else {
        $content .= "<h3>ตั้งรหัสผ่านใหม่</h3>"
            . $form->show_st_form()
            . $form->show_text("pass","pass","","password","","","label-inline","password"," maxlength='32'")
            . $form->show_text("repass","repass","","ใส่ password อีกครั้ง","","","label-inline","password"," maxlength='32'")
            . "<div id='pass-indicator' class='p-indi'>Strength Indicator</div>"
            . $form->show_submit("submit","Update","but-right")
            . $form->show_hidden("request","request","edit_upass")
            . $form->show_hidden("uid","uid",$rq)
            . $form->show_hidden("redirect","redirect",$root."login.php");
        $form->addformvalidate("ez-msg", ['pass','repass'],['pass','repass']);
        $content .= $form->submitscript("$('#edit').submit();")
                . "<script>"
                . "pass_strength('pass','repass','pass-indicator');"
                . "</script>"
                . "</div><!-- .float-box-inside -->"
                . "</div><!-- login-box -->";
    }
} else {
    $content .= $form->show_st_form()
        . $form->show_text("email","email","","Email","","","label-inline","email")
        . $form->show_text("z","z","","Password","","","label-inline","password")
        . "<a href='$fg' id='forget-pass' title='Forget password'>ลืมรหัสผ่าน?</a>"
        . $form->show_submit("login_submit","Login","but-100")
        . $form->show_hidden("request","request","login")
        . $form->show_hidden("error_direct","error_direct",$root."login.php")
        . $form->show_hidden("redirect","redirect",$root);
$form->addformvalidate("float-msg",array('email','z'),null,'email');
$arrn = json_encode($form->array_name);
$content .= $form->submitscript("$('#ulogin').submit();")
        . "</div><!-- .float-box-inside -->"
        . "<script>"
        . "inputenter(['z'],'login_submit');"
        . "</script>"
        . "</div><!-- login-box -->";
}
    
$content .= $menu->showfooter();
echo $content;

