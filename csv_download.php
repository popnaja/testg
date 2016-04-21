<?php
session_start();
include_once(dirname(__FILE__)."/p-admin/myfunction.php");
include_once("g_option.php");
include_once("carbon.php");
__autoload("pdo_g");
if(!$_GET){
    header("location:".ROOTS);
}
$req = filter_input(INPUT_GET,'request',FILTER_SANITIZE_STRING);
$fid = filter_input(INPUT_GET,'fid',FILTER_SANITIZE_NUMBER_INT);
$coid = $_SESSION['rms_c'];
$db = new greenDB();
if($req == "sum_download"){
    //prepare data
    $month = filter_input(INPUT_GET,'month',FILTER_SANITIZE_NUMBER_INT);
    $head[] = array("งาน",'ชนิด',"จำนวน(เล่ม)","หน้า","Carbon","Carbon/หน่วย","วันที่");
    $rec = $db->view_fn_csv($coid,$month);
    $tt = array_merge($head,$rec);
    //var_dump($tt);

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="data.csv";');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');

    $output = fopen("php://output", "w");
    fputs( $output, "\xEF\xBB\xBF" );
    foreach($tt as $k=>$v){
        fputcsv($output,$v);
    }
    exit();
} else if($req == "detail"&&$db->check_fn($fid,$coid)){
    $tt[] = array("กิจกรรม",'องค์ประกอบ',"หน่วย","ปริมาณ","EF","kg.CO2eq(วัสดุ)","การขนส่ง","ชนิดเชื้อเพลิง","EF(เชื้อเพลิง)","ปริมาณการใช้เชื้อเพลิง(ลิตร/กก)","พาหนะ","โหลดขามา(%)","ขากลับ(%)","ระยะทาง(กม)","EF(ขามา)","EF(ขากลับ)","kg.CO2eq(ขนส่ง)","kg.CO2eq","%");
    $rec = calculate_carbon($fid);
    $total_carbon = $rec[3]['total_carbon'];
    foreach($rec[2] AS $k=>$v){
        foreach($v as $kk=>$vv){
            $t = array();
            foreach($vv as $kkk=>$vvv){
                if($kkk!=="material"){
                    array_push($t,$vvv);
                }
            }
            $ttc = $vv['material_carbon']+$vv['transit_carbon'];
            $per = $ttc*100/$total_carbon;
            $tt[] = array_merge(array($k,$kk),$t,array($ttc,$per));
        }
    }
    $tt[] = array("-");
    $tt[] = array("รายละเอียดการพิมพ์");
    //var_dump($rec[0]);
    foreach($rec[1] as $k=>$v){
        array_push($tt,array_merge(array("-",$k),$v));
    }
    $acol = $db->get_comp($fid);
    foreach($rec[0] as $k=>$v){
        foreach($v AS $kk=>$vv){
            if(is_array($vv)){
                $tvv= array();
                for($i=0;$i<$acol;$i++){
                    array_push($tvv,(isset($vv[$i])?$vv[$i]:""));
                }
            } else {
                $tvv = array($vv);
            }
            array_push($tt,array_merge(array($k,$kk),$tvv));
        }
        
    }
    //var_dump($tt);
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="data.csv";');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');

    $output = fopen("php://output", "w");
    fputs( $output, "\xEF\xBB\xBF" );
    foreach($tt as $k=>$v){
        fputcsv($output,$v);
    }
    exit();
} else {
    header("location:".$root."calculate.php");
    exit();
}

    
    





