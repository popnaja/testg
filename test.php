<?php
session_start();
include_once(dirname(__FILE__)."/p-admin/myfunction.php");
include_once("g_option.php");
$root = ROOTS;
$aroot = AROOTS;
__autoload("menu");
__autoload("pdo_g");
$db = new greenDB();

//prep new company
//prep_new_com(8,2);

function prep_new_com($newid,$oid){
    global $db;
    //copy mat
    $mat = $db->get_keypair("mat", "id", "id", "WHERE company_id=$oid");
    foreach($mat as $k=>$mid){
        //add mat
        $minfo = $db->get_info("mat", "id", $mid);
        $nmid = $db->insert_data("mat", array($newid,null,$minfo['name'],$minfo['unit'],$minfo['ef'],$minfo['cat_id'],$minfo['ref_id'],$minfo['evidence']));
        //add mat transport
        $mt = $db->get_info("mat_transport","mat_id",$mid);
        $arrdata = array($nmid);
        foreach($mt as $key=>$v){
            if($key=="mat_id"){
                continue;
            } else {
                array_push($arrdata,$v);
            }
        }
        $db->insert_data("mat_transport", $arrdata);
    }
    echo "Copy mat done.";

    //copy machine
    $mach = $db->get_keypair("machine", "id", "id","WHERE company_id=$oid");
    foreach($mach as $k=>$machid){
        //add machine
        $minfo = $db->get_info("machine","id",$machid);
        $nmachid = $db->insert_data("machine", array($newid,null,$minfo['brand_model'],$minfo['process'],$minfo['allocation_unit'],$minfo['machine_cat_id']));
        //add electric
        $db->copy_ele($machid,$nmachid);
        //add machine meta
        $meta = $db->get_meta("machine_meta", "machine_id", $machid);
        $db->update_meta("machine_meta", "machine_id", $nmachid, $meta);
        //add test data
        $test = $db->get_info("test_data", "machine_id", $machid);
        $arrdata = array(null,$nmachid);
        foreach($test as $key=>$v){
            if($key=="id"||$key=="machine_id"){
                continue;
            } else {
                array_push($arrdata,$v);
            }
        }
        $otid = $test['id'];
        $testid = $db->insert_data("test_data", $arrdata);
        $input = $db->get_keypair("input", "mat_id", "amount", "WHERE test_data_id=$otid");
        foreach($input AS $key=>$val){
            $nmid = $db->find_nmat($key,$newid);
            $db->insert_data("input", array($testid,$nmid,$val));
        }
        $output = $db->get_keypair("output", "mat_id", "amount", "WHERE test_data_id=$otid");
        foreach($output AS $key=>$val){
            $nmid = $db->find_nmat($key,$newid);
            $db->insert_data("output", array($testid,$nmid,$val));
        }
    }
    echo "Copy machine done.";
    //copy print process
    $print = $db->get_keypair("printing", "id", "id","WHERE company_id=$oid");
    foreach($print AS $k=>$pid){
        //add print
        $pinfo = $db->get_info("printing", "id", $pid);
        $npid = $db->insert_data("printing", array($newid,null,$pinfo['name']));
        //add process
        $process = $db->get_infos("print_process", "printing_id", $pid);
        foreach($process AS $key=>$val){
            $nmachid = ($val['machine_id']==0?0:$db->find_nmach($val['machine_id'], $newid));
            $db->insert_data("print_process", array($npid,$nmachid,$val['sequence'],$val['input_mult'],""));
        }
    }
    echo "Copy print done.";
}
