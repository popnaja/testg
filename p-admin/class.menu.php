<?php
include_once("myfunction.php");
__autoload("form");
class mymenu{
    public $astyle = array();
    public $ascript = array();
    public $extrascript = "";
    public $pageTitle;
    public $canonical_link = "";
    private $language;
    private $root;
    private $aroot;
    private $throot;
    private $fbmeta = "";
    private $menu = "";
    private $dir;
    public $meta = array(
        'viewport'      => 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0'
    );
    public $cart = 0;
    public $login = false;
    public $login_redirect;
    private $site;
    private $icon;
    private $logo;
    public $contact;
    public function __construct($lang=null) {
        $this->site = SITE;
        $this->language = (isset($lang)?$lang:"en");
        $this->root = ROOTS;
        $this->aroot = AROOTS;
        $this->icon = AROOTS."image/carbon_logo_re2.png";
        $this->logo = AROOTS."image/resoluteMS_logo.jpg";
        $this->dir = dirname(__FILE__)."/";
        $this->astyle[] = $this->aroot."css/fontface.css";
        $this->astyle[] = $this->aroot."css/ifonts.css";
        $this->ascript[] = $this->aroot."js/jquery-1.11.2.min.js";
        
        $this->astyle[] = $this->aroot."/css/class.menu.css";
        $this->ascript[] = $this->aroot."/js/class.menu.js";
    }
    /*function load class use for loading both php js and css */
    public function __autoloadall($class_name) {
        include_once ($this->dir."class.".$class_name .".php");
        $this->astyle[] = $this->aroot."css/class.".$class_name.".css";
        $this->ascript[] = $this->aroot."js/class.".$class_name.".js";
    }
    public function canonical($url){
        $this->canonical_link = "<link rel='canonical' href='$url' />";
    }
    public function fb_meta($title,$url,$des,$img=null){
        $fbmeta = "<meta property='og:title' content='$title' />"
                . "<meta property='og:site_name' content='CalForLife.com' />"
                . "<meta property='og:url' content='$url' />"
                . "<meta property='og:description' content='$des' />"
                . "<meta property='fb:appid' content='1714256045469524' />";
        if(isset($img)){
            $fbmeta .= "<meta property='og:image' content='$img' />";
        } else {
            $fbmeta .= "<meta property='og:image' content='' />";
        }
        $this->fbmeta = $fbmeta;
    }
    private function goggle_ana(){
        $script = <<<END_OF_TEXT
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-69406267-1', 'auto');
  ga('send', 'pageview');

</script>
END_OF_TEXT;
        return $script;
    }
    public function menu($level=null){
        __autoload("pdo_g");
        $db = new greenDB();
        $lmonth = $db->get_lastmonth($_SESSION['rms_c']);
        $greeny = $this->root;
        if($level==9){
            $this->menu = array(
                "หน้าแรก" => $greeny."index.php",
                "รายการสิ่งพิมพ์ย้อนหลัง" => $greeny."calculate.php?m=$lmonth",
                "กระบวนการพิมพ์และเคลือบ" => $greeny."process.php",
                "ออกแบบสิ่งพิมพ์" => $greeny."design.php",
                "เครื่องจักร" => $greeny."machine.php",
                "วัตถุดิบ" => $greeny."mat.php",
                "ผู้ดูแลระบบ" => [
                    "Company" => $greeny."company.php",
                    "User" => $greeny."user.php",
                    "Transport" => $greeny."transport.php",
                    "Material Cat" => $greeny."cat.php",
                    "Machine Cat" => $greeny."machine_cat.php"
                ]
            );
        } else {
            $this->menu = array(
                "หน้าแรก" => $greeny."index.php",
                "รายการสิ่งพิมพ์ย้อนหลัง" => $greeny."calculate.php?m=$lmonth",
                "กระบวนการพิมพ์และเคลือบ" => $greeny."process.php",
                "ออกแบบสิ่งพิมพ์" => $greeny."design.php",
                "เครื่องจักร" => $greeny."machine.php",
                "วัตถุดิบ" => $greeny."mat.php",
                "เปลี่ยนรหัสผ่าน" => $greeny."repass.php",
            );
        }
        
    }
    private function show_flymenu($line_1,$line_2,$menu){
        if($this->login){
            $logincss = "form-hide";
            $outcss = "";
        } else {
            $logincss = "";
            $outcss = "form-hide";
        }
        $html = "<div class='main-nav-show'>"
                . "<span class='nav-line1'>$line_1</span><br/>"
                . "<span class='nav-line2 username'>$line_2</span>"
                . "<span class='icon-chevron-down'></span>"
                . "</div><!-- .main-nav-show -->";
        $html .= "<div class='fly-menu'>"
                . "<span class='triangle-up'></span>"
                . "<div class='for-login $logincss'>"
                . "<div class='fly-but fly-top'><a href='' title='เข้าสู่ระบบ' class='goto-login'><span>Sign in</span></a></div>"
                . "<a href='' title='สมัครสมาชิก' class='goto-register'>สมัครสมาชิก</a>"
                . "</div>"
                . "<div class='for-logout fly-top $outcss username'>Hi, $line_2</div>";
        foreach($menu AS $k=>$v){
            $html .= "<a href='$v' title='$k'>$k</a>";
        }
        $html .= "<div class='for-logout $outcss'>"
                . "<a href='' title='ลงชื่อออก' class='goto-logout icon-logout'>ลงชื่อออก</a>"
                . "</div><!-- .for-logout -->";
        $html .= "</div><!-- .fly-menu -->";
        return $html;
    }
    public function showhead(){
        $head = "<!DOCTYPE html>\n"
                . "<html lang='$this->language'>\n"
                . "<head profile='http://www.w3.org/2005/10/profile'>\n"
                . '<meta http-equiv=Content-Type content="text/html; charset=utf-8">';
        foreach($this->meta as $key => $value){
            $head .= "<meta name='$key' content='$value'>\n";
        }
        $head .= "<title>$this->pageTitle</title>\n";
        $head .= $this->canonical_link;
        $head .= $this->fbmeta;
        foreach($this->astyle as $value){
            $head .= "<link href='$value' type='text/css' rel='stylesheet'>\n";
        }
        foreach($this->ascript as $value){
            $head .= "<script src='$value'></script>\n";
        }
        //icon
        
        $head .= "<link rel='icon' type='image/png' href='$this->icon' />";
        $head .= $this->extrascript;
        $head .= "</head>"
                . "<body>";
        
        //google analytic
        //$head .= $this->goggle_ana();
        
        return $head;
    }
    public function showpanel($active,$sub="",$visible=true){
        $root = $this->root;
        $logout = $this->root."logout.php";
        $uid = (isset($_SESSION['rms_user'])?$_SESSION['rms_user']:0);
        $requrl = ROOTS."p-admin/request.php";
        $rms_white = $this->aroot."image/resolutems_logo_white.png";
        if($visible){
            $panel = "<div id='top-panel'>"
                . "<div id='menu-mobile' class='icon-three-bars'></div>"
                . "<h1 id='logo'><span class='sname'>Smart Greeny by</span></h1>"
                    . "<div class='rms-logo'>"
                    . "<a href='http://www.resolutems.com/' title='Resolute MS'><img src='$rms_white'></a>"
                    . "</div><!-- .rms-logo -->"
                . "<a href='$logout' title='ออกจากระบบ' class='icon-logout'></a>"
                . "<a href='$root' title='หน้าแรก' class='icon-home'></a>"
                . "</div><!-- #top-panel -->"
                . "<div id='panel'>"
                . "<ul id='mymenu'>"
                . $this->show_menuli($active,$sub)
                . "</ul>"
                . "</div><!-- #panel -->"
                . "<script>"
                . "show_mobilem();"
                . "user_log('$uid','$requrl');"
                . "flex_menu();"
                . "</script>\n";
        } else {
            $panel = "";
        }
        $panel = "<div id='wrapper' class='cheight'>\n"
                . $panel
                . "<div id='content' class='cheight'>"
                . $this->loading();
        return $panel;
    }
    public function show_contact(){
        $form = new myform();
        $contact = "<div id='contact-dt' class='p-down'>"
                . "<div class='ct-in'>"
                . "<a href='tel:061-864-8641' title='Call 061-864-8641' class='tel'><span class='icon-call-phone-square'></span>061-864-8641</a>"
                . "<h2>Leave your message: </h2>"
                . $form->show_text("name","name","","","Name","","label-inline")
                . $form->show_text("tel","tel","","","Telephone","","label-inline")
                . $form->show_textarea("msg","",2,10,"","Message","label-inline")
                . $form->show_submit("send","Send","but-right")
                . $form->show_hidden("request","request","add_msg")
                . $form->show_hidden("referurl","referurl",AROOTS."request.php")
                . "</div>"
                . "<div id='contact'>Contact <span class='icon-chevron-down'></span><span class='icon-chevron-up'></span></div>"
                . "</div><!-- #contact-dt -->";
        $form->addformvalidate('ct-msg',['name','tel','msg']);
        $arrn = json_encode($form->array_name);
        $contact .= $form->submitscript("send_msg(e,$arrn);")
                . "<script>pull_down('contact');</script>";
        $this->contact = $contact;
    }
    private function loading(){
        $gif = ROOTS."p-admin/image/ajax-loader.gif";
        return  "<div class='pg-loading-dialog'>"
                . "<div class='ajax-dialog'>"
                . "<h3>Loading...</h3>"
                . "<p></p>"
                . "<div class='ajax-gif'><img src='".$gif."'/></div>"
                . "</div><!-- .ajax-dialog -->"
                . "</div><!-- .pg-loading-dialog -->";
    }
    public function showfooter(){
        $footer = "</div><!-- #content -->"
                . "<footer>"
                . "<div id='popsget_logo'></div>"
                . "<div id='copyright'>"
                . "<p>All material herein &#64 2015 $this->site, All Rights Reserved. <br/>"
                . "Developed by <a href='http://www.resolutems.com/' title='ResoluteMS.com'>Resolute Management Services</a></p>"
                . "</div><!-- #cotyright -->"
                . "</footer>"
                . "</div><!-- #wrapper -->"
                . "</body>"
                . "</html>";
        return $footer;
    }
    
    private function show_menuli($active,$sub=""){
        $green = "Smart Greeny";
        $logo = "<div id='rms-logo'>"
                . "<a href='$this->root' title='$green' target='_blank'>"
                . "<img src='$this->icon' alt='$green'>"
                . "</a>"
                . "</div>";
        $panel = $logo;
        foreach($this->menu as $key => $value){
            if(!is_array($value)){
                $actclass = ($key==$active?"m-active":"");
                $id = str_replace(" ","",$key);
                $panel .= "<li id='$id' class='list-menu $actclass'><a href='$value' class='menu-link' title='$key' data-role='none'>$key</a></li>";
            } else {
                if($key==$active){
                    $actclass = "m-active";
                    $hid_sub = "";
                } else {
                    $actclass = "";
                    $hid_sub = "form-hide";
                }
                $id = str_replace(" ","",$key);
                $panel .= "<li id='$id' class='list-menu $actclass'>";
                $panel .= "<span>$key</span>"
                        . "<ul class='sub-menu $hid_sub'>";
                foreach($value as $keym => $valuem){
                    if($keym == "0"){
                        $panel .= "";
                    } else {
                        $panel .= "<li><a href='$valuem' title='$keym' ".($keym==$sub&&$key==$active ? "class='subactive'" : "")." data-role='none'>$keym</a></li>";
                    }
                }
                $panel .= "</ul></li>\n";
            }
        }
        $panel .= "<script>show_sub_menu();</script>";
        return $panel;
    }
    private function show_mobilem($id,$active,$subact){
        $html = "<ul id='$id' class=''>";
        foreach($this->menu as $k=>$v){
            $act = ($active == $k ? "m-active" : "" );
            if(is_array($v)){
                foreach($v as $key=>$val){
                    $sact = ($subact == $key ? " m-subactive" : "" );
                    $html .= "<li class='sub-m$sact'><a href='$val' title='Go to $key' data-role='none'>$key</a></li>";
                }
            } else {
                $html .= "<li class='$act'><a href='$v' title='Go to $k' data-role='none'>$k</a></li>";
            }
        }
        $html .= "</ul><!-- menu-opt -->";
        return $html;
    }
    public function show_login($redirect=null){
        $form = new myform();
        $re = (isset($redirect)?$redirect:"");
        //$msg = showstatus("test", 0);
        $html = "<div id='login-box' class='float-box form-hide'>"
                . "<span class='icon-remove close-float-box'></span>"
                . "<h2>เข้าสู่ระบบ ".SITE."</h2>"
                . "<div id='float-msg'></div>"
                . "<div class='float-box-inside'>"
                . $form->show_text("email","email","","Email","","","label-inline","email")
                . $form->show_text("z","z","","Password","","","label-inline","password")
                . "<p class='f-p'><a href='' id='forget-pass' title='Forget password'>ลืมรหัสผ่าน?</a></p>"
                . $form->show_submit("login_submit","Login","but-100")
                . $form->show_button("goto-register","สมัครสมาชิก","but-100 goto-register")
                . $form->show_hidden("request","request","login")
                . $form->show_hidden("redirect_l","redirect_l",$re);
        $form->addformvalidate("float-msg",['email','z'],null,'email');
        $arrn = json_encode($form->array_name);
        $html .= $form->submitscript("user_login($arrn);")
                . "</div><!-- .float-box-inside -->"
                . "<script>"
                . "inputenter(['z'],'login_submit');"
                . "</script>"
                . "</div><!-- login-box -->";
        
        return $html;
    }
    public function show_regist($redirect=null){
        $form = new myform();
        $re = (isset($redirect)?$redirect:"");
        $html = "<div id='regist-box' class='float-box form-hide'>"
                . "<span class='icon-remove close-float-box'></span>"
                . "<h2>สมัครสมาชิก ".SITE."</h2>"
                . "<div id='float-msg'></div>"
                . "<div class='float-box-inside'>"
                . $form->show_text("fname","fname","","ชื่อ","","","label-inline")
                . $form->show_text("lname","lname","","นามสกุล","","","label-inline")
                . "<div class='email-div'>"
                . $form->show_text("remail","remail","","Email (จำเป็นสำหรับเข้าสู่ระบบ)","","","label-inline","email")
                . "<span class='check-email'></span>"
                . "</div>"
                . $form->show_text("rpass","rpass","","password","","","label-inline","password"," maxlength='32'")
                . $form->show_text("repass","repass","","repeat the password","","","label-inline","password"," maxlength='32'")
                . "<div id='pass-indicator' class='p-indi'>Strength Indicator</div>"
                . "<p class='f-p'>เป็นสมาชิกแล้ว <a href='' class='goto-login icon-login' title='ไปหน้า Login'>Login</a></p>"
                . $form->show_submit("register","สมัครสมาชิก","but-100")
                . $form->show_hidden("redirect_r","redirect_r",$re)
                . $form->show_hidden("referurl","referurl",ROOTS."/p-admin/front_request.php");
        $form->addformvalidate("float-msg",['remail','rpass'],['rpass','repass'],'remail',null,['check-email'],null,"");
        $arrn = json_encode($form->array_name);
        $html .= $form->submitscript("regist_user($arrn);")
                . "</div><!-- .float-box-inside -->"
                . "<script>"
                . "inputenter(['repass'],'register');"
                . "pass_strength('rpass','repass','pass-indicator');"
                . "check_email('remail');"
                . "get_utz();"
                . "</script>"
                . "</div><!-- register-box -->";
        
        return $html;
    }
    public function show_forget($redirect=null){
        $re = (isset($redirect)?$redirect:"");
        $form = new myform();
        //$msg = showstatus("test", 0);
        $html = "<div id='forget-box' class='float-box form-hide'>"
                . "<span class='icon-remove close-float-box'></span>"
                . "<h2>ลืมรหัสผ่าน ".SITE."</h2>"
                . "<div id='float-msg'></div>"
                . "<div class='float-box-inside'>"
                . $form->show_text("femail","femail","","Email","","","label-inline","email")
                . $form->show_submit("reset_pass","Reset Password","but-100")
                . $form->show_hidden("redirect_f","redirect_f",$re);
        $form->addformvalidate("float-msg",['femail'],null,'femail');
        $arrn = json_encode($form->array_name);
        $html .= $form->submitscript("forget_pass($arrn);")
                . "</div><!-- .float-box-inside -->"
                . "<script>"
                . "inputenter(['femail'],'reset_pass');"
                . "</script>"
                . "</div><!-- forget-box -->";
        
        return $html;
    }
}