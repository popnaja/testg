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
        .error-remark {
            font-size:14pt;
            color:#ff5b42;
        }
        .but-right {
        width:50%;
        float:right;
        }
        #tb-history tr th:nth-child(4){
            width:30%;
        }
        #tb-history tr th:nth-child(1),
        #tb-history tr th:nth-child(2){
        width:30px;
        }
        #tb-history tr th:nth-child(3){
        width:80px;
        }
        #tb-history tr th:nth-child(5),
        #tb-history tr th:nth-child(6),
        #tb-history tr th:nth-child(7),
        #tb-history tr th:nth-child(8),
        #tb-history tr td:nth-child(5),
        #tb-history tr td:nth-child(6),
        #tb-history tr td:nth-child(7),
        #tb-history tr td:nth-child(8){
            display:none;
        }
        @media only screen and (min-width: 769px) {
            [class^='bg-img']{
                height:720px;
            }
            #tb-history tr th:nth-child(5),
            #tb-history tr th:nth-child(6),
            #tb-history tr th:nth-child(7),
            #tb-history tr th:nth-child(8),
            #tb-history tr td:nth-child(5),
            #tb-history tr td:nth-child(6),
            #tb-history tr td:nth-child(7),
            #tb-history tr td:nth-child(8){
                display:table-cell;
            }
        }
</style>
END_OF_TEXT;

$form = new myform('greenform','cheight');

$content = $menu->showhead();
$content .= $menu->showpanel("รายการสิ่งพิมพ์ย้อนหลัง","");

$action = filter_input(INPUT_GET,'action',FILTER_SANITIZE_STRING);
$fid = filter_input(INPUT_GET,'fid',FILTER_SANITIZE_NUMBER_INT);

if($action == "res"&&$db->check_fn($fid,$coid)){
/* =======================================================================RESULT ======================================================*/
    $finfo = $db->view_finfo($fid);
    $fnmeta = $db->view_fn_meta($fid);
    $unit = $type_to_unit[$finfo['type']];
    //title
    
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
        $remark = "จำนวนชิ้นงานสำเร็จน้อยกว่ายอดที่ตั้งไว้ $less $unit ตรวจสอบข้อมูลอีกครั้ง <a href='calculate.php?fid=$fid' title='Edit' class='icon-page-edit'></a>";
    }
    $carbon = $tb->tb_carbon_total($info[2],$info[3],$unit);
    $show_cb = ($carbon[1]<1?number_format($carbon[1]*1000,0)." g.CO2eq/$unit":number_format($carbon[1],3)." kg.CO2eq/$unit");
    
    $edit = $root. "calculate.php?fid=$fid";
    $plink = $root. "print.php?fid=$fid";
    $plink2 = $root. "print.php?type=detail&fid=$fid";
    $csv = $root."csv_download.php";
    $content .= "<div class='print-icon'>"
            . "<div class='down-but'><a href='$edit' title='Edit'><div class='blue-but icon-page-edit'></div></a></div>"
            . "<div class='down-but'><a href='$plink' title='Print' target='_blank'><div class='blue-but icon-print'></div></a></div>"
            . "<div class='down-but'><a href='$plink2' title='Print Detail' target='_blank'><div class='blue-but icon-page-search'></div></a></div>"
            . "<div class='down-but'>"
            . "<a href='csv_download.php?request=detail&fid=$fid' title='Download ข้อมูล'><div id='csv-download' class='blue-but icon-download'></div></a>"
            . "</div>"
            . "</div>"
            . "<div class='c-report'>"
            . "<div class='report-head'>"
            . "<div class='report-title'>"
            . "<h1>$report_title</h1>"
            . "<h2>$customer</h2>"
            . "<h2>$product</h2>"
            . "<h3 class='error-remark'>$remark</h3>"
            . "</div>"
            . "<div class='report-logo'>"
            . "<img src='$logo' alt='รายงานค่าคาร์บอนฟุตพริ้นท์โดยโปรแกรม Smart Greeny' />"
            . "<h4>$show_cb</h4>"
            . "</div>"
            . "</div><!-- .report-head -->"
            . "<h2>ปริมาณคาร์บอนฟุตพริ้นท์ งานพิมพ์จำนวณ ".number_format($amount,0)." $unit</h2>"
            . $carbon[0]
            //. $tb->tb_carbon_tt($info[2])
            . "<h2>รายละเอียดการพิมพ์</h2>"
            . $tb->tb_carbon_sum($info[1])
            . $tb->tb_carbon($acol,$info[0])
            . "</div><!-- .c-report -->";
    
} else if($action == "add"){
/* =======================================================================ADD ======================================================*/
    $machs = $db->get_amach($coid);
    $paper = array("0"=>"-- กระดาษ --")+$db->get_mat($coid,"1",false);
    $plate = $db->get_mat($coid,"5",false);
    $print = array("0"=>"--กระบวนการพิมพ์--") + $db->get_print($coid);
    $p_process = array("0"=>"-- กระบวนการ --") + $db->get_mcat("('finishing')");
    $gas = $db->get_mat($coid,"2",false);
    $vehicle = $db->get_vehicle();
    $cmeta = $db->get_meta("company_meta", "company_id", $coid);
    $content .= "<h1 class='page-title'>คำนวณ Carbon Footprint</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>";

    //add
    $content .= $form->show_st_form()
            . "<div class='col-50'>"
            . $form->show_text("fn","fn","","","ชื่องาน","","label-3070")
            . $form->show_text("cname","cname","","","บริษัทลูกค้า","","label-3070")
            . $form->show_select("type", $printing_t, "label-3070","รูปแบบ")
            . $form->show_num("amount","",1,"","จำนวนชิ้นงาน(เล่ม,แผ่น)","","label-3070")
            . $form->show_hidden("page","page",0)
            . show_design($coid,null)
            . show_waste($coid, null);
    
    //distibution
    $content .= "<div class='form-section'>"
            . "<h4>ส่งสินค้าไปยังผู้ซื้อ</h4>"
            . $form->show_select("dis_type", $distribution, "label-3070","รูปแบบการคำนวณ")
            . "<div id='cal-ef' class='cal-group form-hide'>"
            . $form->show_num("dis_ef",0,0.0001,"","EF/สินค้า 1 กก","","label-3070")
            . "</div>"
            . "<div id='cal-gas' class='cal-group form-hide'>"
            . $form->show_select("dis_gas_type", $gas, "label-3070","ชนิดเชื้อเพลิง")
            . $form->show_num("dis_gas_lperkg",0,0.0001,"","ลิตร/สินค้า 1 กก","","label-3070")
            . "</div>"
            . "<div id='cal-vehicle' class='cal-group form-hide'>"
            . $form->show_select("dis_v_type", $vehicle, "label-3070","พาหนะ")
            . $form->show_num("dis_v_distance",0,0.01,"","ระยะทาง(กม)","","label-3070")
            . $form->show_select("dis_v_goload", $load, "left-50 label-inline","บรรทุกขาไป(%)")
            . $form->show_select("dis_v_backload", $load, "left-50 label-inline","บรรทุกขากลับ(%)")
            . "</div>"
            . "<script>dis_sel();</script>"
            . "</div><!-- .form-section -->"
            . "</div><!-- .col-50 -->";
    
    //finishing
    $content .= "<div class='col-50'>"
            . "<h2>กระบวนการหลังพิมพ์</h2>"
            . "<div class='process-area'>";
    for($i=1;$i<7;$i++){
        $mach = array("0"=>"-- ไม่ใช้งาน --");
        $content .= "<div class='process-box'>"
                . $form->show_select("process_$i", $p_process, "label-inline sel-process")
                . $form->show_select("mach_$i", $mach, "label-inline",null,null,"","mach[]")
                . $form->show_hidden("seq_$i","seq[]",$i)
                . "</div><!-- .process-box -->"
                . ($i==6?"":"<div><img src='".$aroot."/image/arrow-down.png' /></div>");
    }
    $content .= "</div><!-- .process-area -->"
            . "</div><!-- .col-50 -->";

    //component        
    $n=0;
    $content .= "<div class='col-100'>"
            . "<h2 class='sub-title'>ส่วนประกอบ</h2>";
    for($x=0;$x<4;$x++){
        $hid = ($x>1?"form-hide":"");
        $content .= "<div class='col-50 com-sec $hid'>";
        for($i=0;$i<2;$i++){
            $n++;
            if(isset($cmeta['plate_type'])){
                $sel_plate = $form->show_hidden("plate_type_$n","plate_type[]",$cmeta['plate_type']);
            } else {
                $sel_plate = $form->show_select("plate_type_$n", $plate, "label-inline","แม่พิมพ์",null,"","plate_type[]");
            }
            $content .="<div class='print-comp left-50'>"
                    . $form->show_text("name_$n","name[]","","","ส่วน $n","","label-inline")
                    . $form->show_select("pid_$n", $print, "label-inline","การพิมพ์",null,"","pid[]")
                    . "<div class='form-section'>"
                    . "<h4>ขนาดชิ้นงาน</h4>"
                    . $form->show_num("width_$n","",0.01,"","กว้าง(นิ้ว)","","label-inline","min=1","width[]")
                    . $form->show_num("length_$n","",0.01,"","ยาว(นิ้ว)","ขนาดปกเป็นขนาดกางออกรวมสันและปีก","label-inline","min=1","length[]")
                    . $form->show_num("sheet_per_unit_$n","",1,"","แผ่นต่อเล่ม","จำนวณแผ่นชิ้นงานต่อหนังสือ 1 เล่ม = (หน้า/2)","label-inline","min=1","sheet_per_unit[]")
                    . "</div><!-- .form-section -->"
                    . "<div class='form-section'>"
                    . "<h4>กระดาษ</h4>"
                    . $form->show_select("paper_$n", $paper, "label-inline","ชนิดกระดาษ",null,"","paper[]")
                    . $form->show_num("weight_$n","",0.01,"","แกรม","","label-inline","min=1","weight[]")
                    . $form->show_num("m_width_$n",24,0.01,"","กว้าง(นิ้ว)","","label-inline","min=1","m_width[]")
                    . $form->show_num("m_length_$n",35,0.01,"","ยาว(นิ้ว)","","label-inline","min=1","m_length[]")
                    . $form->show_num("sheet_per_plate_$n","",1,"","หน้าต่อกรอบ","ปกคิดหน้ากาง","label-inline","min=1","sheet_per_plate[]")
                    . $form->show_num("input_$n","",1,"","ปริมาณกระดาษ(แผ่น)","","label-inline","min=1","input[]")
                    . "</div><!-- .form-section -->"
                    . "<div class='form-section'>"
                    . "<h4>เพลต</h4>"
                    . $sel_plate
                    . $form->show_num("plate_$n","",0.01,"","แม่พิมพ์(กก)","","label-inline","","plate[]")
                    . "</div><!-- .form-section -->"
                    . "</div><!-- .left-50 -->";
        }    

        $content .= "</div><!-- .col-50 -->";
    }

    $content .= "<p><input type='button' value='เพิ่มส่วนประกอบมากกว่า 4 ส่วน' id='more-comp' class='noselect'/></p>"
            . "<script>"
            . "add_more_comp();"
            . "process_sel(".json_encode($machs).");"
            . "</script>"
            . $form->show_submit("submit","คำนวณ","but-right")
            . $form->show_hidden("request","request","calculate")
            . $form->show_hidden("coid","coid",$coid)
            . $form->show_hidden("redirect","redirect",$root."calculate.php")
            . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", array("fn","type","page","amount","name_1","input_1"),null,null,null,null,array('pid_1','paper_1','plate_type_1'));
    $content .= $form->submitscript("$('#new').submit();")
            ."<script>auto_cal();</script>";
} else if(isset($fid)&&$db->check_fn($fid,$coid)){
/* =========================================================================== EDIT ======================================================*/
    $machs = $db->get_amach($coid);
    $paper = array("0"=>"-- กระดาษ --")+$db->get_mat($coid,"1",false);
    $plate = $db->get_mat($coid,"5",false);
    $gas = $db->get_mat($coid,"2",false);
    $vehicle = $db->get_vehicle();
    $print = array("0"=>"--กระบวนการพิมพ์--") + $db->get_print($coid);
    $p_process = array("0"=>"-- กระบวนการ --") + $db->get_mcat("('finishing')");
    $cmeta = $db->get_meta("company_meta", "company_id", $coid);
    $content .= "<h1 class='page-title'>ปรับค่า</h1>"
            . "<div id='ez-msg'>".  showmsg() ."</div>";
    //load
    $info = $db->view_finfo($fid);
    $pinfo = $db->view_fn_print($fid);
    $fin  = $db->view_fin_process($fid);
    $fnmeta = $db->view_fn_meta($fid);
    $design = (isset($fnmeta['design'])?$fnmeta['design']:"0");
    //edit
    $content .= $form->show_st_form()
            . "<div class='col-50'>"
            . $form->show_text("fn","fn",$info['name'],"","ชื่องาน","","label-3070")
            . $form->show_text("cname","cname",$fnmeta['cname'],"","บริษัทลูกค้า","","label-3070")
            . $form->show_select("type", $printing_t, "label-3070","รูปแบบ",$info['type'])
            . $form->show_num("amount",$info['amount'],1,"","จำนวนชิ้นงาน(เล่ม,แผ่น)","","label-3070")
            . $form->show_hidden("page","page",0)
            . show_design($coid, $design)
            . show_waste($coid, $fnmeta);
   
    //distribution
    $dis_type = $fnmeta['dis_type'];
    $dis_info = json_decode($fnmeta['dis_info'],true);
    $content .= "<div class='form-section'>"
            . "<h4>ส่งสินค้าไปยังผู้ซื้อ</h4>"
            . $form->show_select("dis_type", $distribution, "label-3070","รูปแบบการคำนวณ",$dis_type)
            . "<div id='cal-ef' class='cal-group form-hide'>"
            . $form->show_num("dis_ef",$dis_info['ef'],0.0001,"","EF/สินค้า 1 กก","","label-3070")
            . "</div>"
            . "<div id='cal-gas' class='cal-group form-hide'>"
            . $form->show_select("dis_gas_type", $gas, "label-3070","ชนิดเชื้อเพลิง",$dis_info['gas'])
            . $form->show_num("dis_gas_lperkg",$dis_info['lperkg'],0.0001,"","ลิตร/สินค้า 1 กก","","label-3070")
            . "</div>"
            . "<div id='cal-vehicle' class='cal-group form-hide'>"
            . $form->show_select("dis_v_type", $vehicle, "label-3070","พาหนะ",$dis_info['vehicle'])
            . $form->show_num("dis_v_distance",$dis_info['distance'],0.01,"","ระยะทาง(กม)","","label-3070")
            . $form->show_select("dis_v_goload", $load, "left-50 label-inline","บรรทุกขาไป(%)",$dis_info['goload'])
            . $form->show_select("dis_v_backload", $load, "left-50 label-inline","บรรทุกขากลับ(%)",$dis_info['backload'])
            . "</div>"
            . "<script>dis_sel('$dis_type');</script>"
            . "</div><!-- .form-section -->"
            . "</div><!-- .col-50 -->";
    
    //finishing
    $content .= "<div class='col-50'>"
            . "<h2>กระบวนการหลังพิมพ์</h2>"
            . "<div class='process-area'>";
    for($i=1;$i<7;$i++){
        if(!isset($fin[$i])||$fin[$i]=="0"){
            $maid = 0;
            $mcat = 0;
            $mach = array("0"=>"-- ไม่ใช้งาน --");
        } else {
            $maid = $fin[$i];
            $tm = $db->view_macinfo($maid);
            $mcat = $tm['machine_cat_id'];
            $mach = $db->get_machine($coid,$mcat);
        }
        $content .= "<div class='process-box'>"
                . $form->show_hidden("coid","coid",$coid)
                . $form->show_select("process_$i", $p_process, "label-inline sel-process",null,$mcat)
                . $form->show_select("mach_$i", $mach, "label-inline",null,$maid,"","mach[]")
                . $form->show_hidden("seq_$i","seq[]",$i)
                . "</div><!-- .process-box -->"
                . ($i==6?"":"<div><img src='".$aroot."/image/arrow-down.png' /></div>");
    }
    $content .= "</div><!-- .process-area -->"
            . "</div><!-- .col-50 -->";

    //component        
    $n=0;
    $i=0;
    $content .= "<div class='col-100'>"
            . "<h2 class='sub-title'>ส่วนประกอบ</h2>";
    $comps = sizeof($pinfo,0);
    for($x=0;$x<4;$x++){
        if($x<2){
            $hid = "";
        } else if($comps>4){
            $hid = "";
        } else {
            $hid = "form-hide";
        }
        $content .= "<div class='col-50 com-sec $hid'>";
        for($j=0;$j<2;$j++){
            $n++;
            if(isset($cmeta['plate_type'])){
                $sel_plate = $form->show_hidden("plate_type_$n","plate_type[]",$cmeta['plate_type']);
            } else {
                $sel_plate = $form->show_select("plate_type_$n", $plate, "label-inline","แม่พิมพ์",(isset($pinfo[$i])?$pinfo[$i]['plate_type']:null),"","plate_type[]");
            }
            if(isset($pinfo[$i])){
                $content .="<div class='left-50'>"
                    . $form->show_text("name_$n","name[]",$pinfo[$i]['name'],"","ส่วน $n","","label-inline")
                    . $form->show_select("pid_$n", $print, "label-inline","การพิมพ์",$pinfo[$i]['process_id'],"","pid[]")
                    . "<div class='form-section'>"
                    . "<h4>ขนาดชิ้นงาน</h4>"
                    . $form->show_num("width_$n",$pinfo[$i]['width'],0.01,"","กว้าง(นิ้ว)","","label-inline","min=1","width[]")
                    . $form->show_num("length_$n",$pinfo[$i]['length'],0.01,"","ยาว(นิ้ว)","","label-inline","min=1","length[]")
                    . $form->show_num("sheet_per_unit_$n",$pinfo[$i]['sheet_per_unit'],0.1,"","แผ่นต่อเล่ม","จำนวณแผ่นชิ้นงานต่อหนังสือ 1 เล่ม = (หน้า/2)","label-inline","min=1","sheet_per_unit[]")
                    . "</div><!-- .form-section -->"
                    . "<div class='form-section'>"
                    . "<h4>กระดาษ</h4>"
                    . $form->show_select("paper_$n", $paper, "label-inline","ชนิดกระดาษ",$pinfo[$i]['paper_type'],"","paper[]")
                    . $form->show_num("weight_$n",$pinfo[$i]['weight'],0.01,"","แกรม","","label-inline","min=1","weight[]")
                    . $form->show_num("m_width_$n",$pinfo[$i]['m_width'],0.01,"","กว้าง(นิ้ว)","","label-inline","min=1","m_width[]")
                    . $form->show_num("m_length_$n",$pinfo[$i]['m_length'],0.01,"","ยาว(นิ้ว)","","label-inline","min=1","m_length[]")
                    . $form->show_num("sheet_per_plate_$n",$pinfo[$i]['sheet_per_plate'],1,"","หน้าต่อกรอบ","ปกคิดหน้ากาง","label-inline","min=1","sheet_per_plate[]")
                    . $form->show_num("input_$n",$pinfo[$i]['input'],1,"","ปริมาณกระดาษ(แผ่น)","","label-inline","min=1","input[]")
                    . "</div><!-- .form-section -->"
                    . "<div class='form-section'>"
                    . "<h4>ปริมาณวัสดุ</h4>"
                    . $sel_plate
                    . $form->show_num("plate_$n",$pinfo[$i]['plate'],0.01,"","แม่พิมพ์(กก)","","label-inline","","plate[]")
                    . "</div><!-- .form-section -->"
                    . "</div><!-- .left-50 -->";
            } else {
                $content .="<div class='print-comp left-50'>"
                    . $form->show_text("name_$n","name[]","","","ส่วน $n","","label-inline")
                    . $form->show_select("pid_$n", $print, "label-inline","การพิมพ์",null,"","pid[]")
                    . "<div class='form-section'>"
                    . "<h4>ขนาดชิ้นงาน</h4>"
                    . $form->show_num("width_$n","",0.01,"","กว้าง(นิ้ว)","","label-inline","min=1","width[]")
                    . $form->show_num("length_$n","",0.01,"","ยาว(นิ้ว)","ขนาดปกเป็นขนาดกางออกรวมสันและปีก","label-inline","min=1","length[]")
                    . $form->show_num("sheet_per_unit_$n","",1,"","แผ่นต่อเล่ม","จำนวณแผ่นชิ้นงานต่อหนังสือ 1 เล่ม = (หน้า/2)","label-inline","min=1","sheet_per_unit[]")
                    . "</div><!-- .form-section -->"
                    . "<div class='form-section'>"
                    . "<h4>กระดาษ</h4>"
                    . $form->show_select("paper_$n", $paper, "label-inline","ชนิดกระดาษ",null,"","paper[]")
                    . $form->show_num("weight_$n","",0.01,"","แกรม","","label-inline","min=1","weight[]")
                    . $form->show_num("m_width_$n",24,0.01,"","กว้าง(นิ้ว)","","label-inline","min=1","m_width[]")
                    . $form->show_num("m_length_$n",35,0.01,"","ยาว(นิ้ว)","","label-inline","min=1","m_length[]")
                    . $form->show_num("sheet_per_plate_$n","",1,"","หน้าต่อกรอบ","ปกคิดหน้ากาง","label-inline","min=1","sheet_per_plate[]")
                    . $form->show_num("input_$n","",1,"","ปริมาณกระดาษ(แผ่น)","","label-inline","min=1","input[]")
                    . "</div><!-- .form-section -->"
                    . "<div class='form-section'>"
                    . "<h4>เพลต</h4>"
                    . $sel_plate
                    . $form->show_num("plate_$n","",0.01,"","แม่พิมพ์(กก)","","label-inline","","plate[]")
                    . "</div><!-- .form-section -->"
                    . "</div><!-- .left-50 -->";
            }
            $i++;
        }
        $content .= "</div><!-- .col-50 -->";
    }
    
    $content .= "<p><input type='button' value='เพิ่มส่วนประกอบมากกว่า 4 ส่วน' id='more-comp' class='noselect'/></p>";
    
    //del fn
    if($_SESSION['rms_l']>1){
        $redirect = $root."calculate.php";
        $requrl = $aroot."request.php";
        $content .= "<div id='del-fn-but' class='red-but'>ลบสิ่งพิมพ์</div><!-- .del-but -->"
                . "<script>"
                . "del_function_unit($fid,'$redirect','$requrl');"
                . "</script>";
    }
    
    $content .= "<script>"
            . "add_more_comp();"
            . "process_sel(".json_encode($machs).");"
            . "</script>"
            . $form->show_submit("submit","คำนวณใหม่","but-right")
            . $form->show_hidden("fid","fid",$fid)
            . $form->show_hidden("request","request","edit_fn")
            . $form->show_hidden("redirect","redirect",$root."calculate.php?action=res&fid=$fid")
            . "</div><!-- .col-100 -->";
    $form->addformvalidate("ez-msg", array("fn","type","page","amount","name_1","input_1"),null,null,null,null,array('pid_1','paper_1','plate_type_1'));
    $content .= $form->submitscript("$('#edit').submit();")
            . "<script>auto_cal();</script>";
} else {
    //show all
    $add = $root."calculate.php?action=add";
    $content .= "<h1 class='page-title'>ประวัติการคำนวณ<a class='add-new' href='$add' title='Add New'>Add New</a></h1>";
    //filter
    $sel_comp = "";
    $scomp = (isset($_GET['sc'])?$_GET['sc']:"0");
    if($coid == 1){
        $company = array(
            "0" => "แสดงทั้งหมด",
            "G" => "GHPP",
            "K" => "K.PON"
        );
        $sel_comp = $form->show_select("scomp", $company, "label-inline left-30", "บริษัท", $scomp);
    }
    
    
    $month = (isset($_GET['m'])?$_GET['m']:"0");
    $type = (isset($_GET['t'])?$_GET['t']:"0");
    $search = (isset($_GET['s'])?$_GET['s']:"");
    
    $ct = [];
    foreach($db->get_fn_type($coid) as $k=>$v){
        $ct[$v] = $printing_t[$v];
    }
    $m = array("0"=>"แสดงทั้งหมด")+$db->get_fn_month($coid);
    $t = array("0"=>"แสดงทั้งหมด")+$ct;
    $link = $root."calculate.php";
    $content .= "<div class='col-100'>"
            . $form->show_text_wbutton("name-s", "name-s", "", "ค้นหาตามชื่องาน", "", "", "text-but", null, "",$form->show_button("search-but","Search","but-100"))
            . $form->show_select("month", $m, "label-inline left-30", "เดือน", $month)
            . $form->show_select("type", $t, "label-inline left-30", "ชนิดงาน", $type)
            . $sel_comp
            . "</div><!-- .col-100 -->"
            . "<script>"
            . "sel_cal_month('$link','$month','$type','$scomp','$search');"
            . "search_fn('$link','$month','$type','$scomp');"
            . "inputenter(['name-s'],'search-but');"
            . "</script>";
    
    //show download but
    $csv = $root."csv_download.php";
    $download = "<div class='load-icon'>"
            . "<div class='down-but'>"
            . "<a href='csv_download.php?request=sum_download&month=$month' title='โหลดตาราง'>"
            . "<div id='csv-download' class='blue-but'>โหลดตาราง</div>"
            . "</a></div>"
            . "</div>";
    
    $tb = new mytable();
    $head = array("แก้ไข","พิมพ์","คำนวณ","งาน","จำนวน(เล่ม)","Carbon","Carbon/หน่วย","วันที่","Copy");
    $rec = $db->view_fn($coid,$month,$type,$scomp,$search);
    $refer = $aroot."request.php";
    $url = AROOTS."request.php";
    $content .= "<div class='col-100'>"
            . $tb->show_table($head,$rec,"tb-history")
            . $download
            . "<input type='hidden' id='referurl' value='$refer' />"
            . "<script>"
            //. "del_fn();"
            . "copy_fn('$url');"
            . "</script>"
            . "</div>";
}

$content .= $menu->showfooter();
echo $content;

function show_design($coid,$sel=null){
    global $db;
    global $form;
    $design_p = array("0"=>"-- ไม่นำมาคำนวณ --") + $db->get_design($coid);
    $html = "<div class='form-section'>"
            . "<h4>กระบวนการออกแบบสิ่งพิมพ์</h4>"
            . $form->show_select("design", $design_p, "label-3070","ออกแบบ",$sel)
            . "</div><!-- .form-section -->";
    return $html;
}
function show_waste($coid,$fnmeta=null){
    global $db;
    global $form;
    $cmeta = $db->get_meta("company_meta", "company_id", $coid);
    if($coid=="1"){
        $company = array(
            "Good Head Printing & Packaging Group" => "GHPP",
            "K.PON 1996" => "K.PON"
        );
        $html = $form->show_hidden("ele_type","ele_type",7)
            . $form->show_hidden("paper_waste","paper_waste",36)
            . $form->show_hidden("plate_waste","plate_waste",38);
        $html .= $form->show_select("sub_comp", $company, "label-3070","ผลิคโดย",(isset($fnmeta['sub_comp'])?$fnmeta['sub_comp']:null));
    } else if(isset($cmeta['ele_type'])){
        $html = $form->show_hidden("ele_type","ele_type",$cmeta['ele_type'])
            . $form->show_hidden("paper_waste","paper_waste",$cmeta['paper_waste'])
            . $form->show_hidden("plate_waste","plate_waste",$cmeta['plate_waste']);
    } else {
        $ele_type = $db->get_mat($coid,"7",false);
        $paper_waste = $db->get_mat($coid,"8",false);
        $plate_waste = $db->get_mat($coid,"9",false);
        $html = "<div class='form-section'>"
            . "<h4>แหล่งไฟฟ้า, การจัดการกระดาษเสียและแม่พิมพ์</h4>"
            . $form->show_select("ele_type", $ele_type, "label-3070","ไฟฟ้า",(isset($fnmeta['ele_type'])?$fnmeta['ele_type']:null))
            . $form->show_select("paper_waste", $paper_waste, "label-3070","เศษกระดาษ",(isset($fnmeta['paper_waste'])?$fnmeta['paper_waste']:null))
            . $form->show_select("plate_waste", $plate_waste, "label-3070","แม่พิมพ์ใช้แล้ว",(isset($fnmeta['plate_waste'])?$fnmeta['plate_waste']:null))
            . "</div><!-- .form-section -->";
        return $html;
    }
}