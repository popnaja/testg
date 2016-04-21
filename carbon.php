<?php
function calculate_carbon($fid){
    global $db;
    $ele = array();
    $mat = array();
    $waste = array();
    
    //load meta
    $fnmeta = $db->view_fn_meta($fid);
    
    //design process
    if(isset($fnmeta['design'])&&$fnmeta['design']>0){
        $maid = $fnmeta['design'];
        $cid = 0;
        $dinfo = $db->view_macinfo($maid);
        $seq = $dinfo["brand_model"];
        $res[$seq]["กระบวนการ"][$cid] = "ออกแบบสิ่งพิมพ์";
        $res[$seq]["เวลาทำงาน,ขั่วโมง"][$cid] = round($dinfo['load_min']/60,3);
        $elect = round($db->mach_ele_kwh($maid, $dinfo['load_min']/60),3);
        $res[$seq]["ไฟฟ้า,kwh"][$cid] = $elect;
        $res3['การออกแบบ']["ไฟฟ้า"] = $db->get_ef($fnmeta['ele_type'],$elect);
        //วัตถุดิบ
        $ratio = 1;
        $tdid = $dinfo['test_id'];
        $in_mat = $db->view_ef($tdid, $ratio, true);
        $res[$seq]['สารเข้า'] = "";
        foreach($in_mat as $key=>$v){
            $res[$seq][$v['material'].",".$v['unit']][$cid ] = $v['amount'];
            $res3['การออกแบบ'][$v['material']] = $db->get_ef($v['mat_id'],$v['amount']);
        }
        //ของเสียอื่นๆ
        $out_mat = $db->view_ef($tdid, $ratio, false);
        $res[$seq]['สารออก'] = "";
        foreach($out_mat as $kk=>$v){
            $res[$seq][$v['material'].",".$v['unit']][$cid] = $v['amount'];
            $res3['การออกแบบ'][$v['material']] = $db->get_ef($v['mat_id'],$v['amount']);
        }
        
    }
    $res3['วัตถุดิบ'] = array();
    //get components
    $comp = $db->get_comp_detail($fid);
    foreach($comp as $k=>$com){
        $cid = $k;
        $input = $com['input'];
        $paper_w = round($input*$com['m_width']*$com['m_length']*$com['weight']/500/3100,5);
        
        //collect data
        $res2['ส่วนประกอบ'][$cid] = $com['name'];
        $res2["ชนิดกระดาษ"][$cid] = $com['paper'];
        $res2["ขนาด กว้าง(นิ้ว)xยาว(นิ้ว)"][$cid] = $com['m_width']. " x ".$com['m_length'];
        $res2["น้ำหนักกระดาษ(g/m2)"][$cid] = $com['weight'];
        $res2["จำนวน,แผ่น"][$cid] = $com['input'];
        $res2["กระดาษ,กก"][$cid] = $paper_w;
        $res2["แผ่นแม่พิมพ์,กก"][$cid] = $com['plate'];
        $res3['วัตถุดิบ'] += array(
            $com['name'].",".$com['paper'] => $db->get_ef($com['paper_type'], $paper_w),
            $com['name'].",แม่พิมพ์" => $db->get_ef($com['plate_type'], $com['plate'])
        );

        //loop each machine
        $machine = $db->get_print_mach($com['process_id']);
        foreach($machine as $ind=>$mach){
            $seq = $mach['name'];
            //rename seq if has duplicate machine
            if(isset($s_no[$cid][$seq])){
                $s_no[$cid][$seq]++;
                $seq = $seq."(".$s_no[$cid][$seq].")";
            } else {
                $s_no[$cid][$seq] = 1;
            }
            
            $maid = $mach['machine_id'];
            $mmeta = $db->view_mmeta($maid);
            $maxd = (isset($mmeta['max_defect'])&&$mmeta['max_defect']!=""?$mmeta['max_defect']:INF);
            $td = $db->view_testdata($maid);

            //cal new input = output
            $aunit = $mach['allocation_unit'];
            $ratio = $input/($td['input']==0?1:$td['input']);
            $mcat = $mach['machine_cat_id'];
            
            //check if cat = design process
            if($mcat == 8){
                continue;
            }
            
            
            //if machine_cat_id = 1 (cut) output= mult*output
            if($mcat==1){
                $output = $mach['input_mult']*round($ratio*$td['output_ok']);
                $damage = $mach['input_mult']*$input-$output;
            } else {
                $output = round($ratio*$td['output_ok']);
                $damage = $input-$output;
            }
            //check max defect
            if($damage>$maxd){
                $damage = $maxd;
                $output = $input-$damage;
            }
            //add info to comp
            $comp[$k]['mult'] = $comp[$k]['mult']*$mach['input_mult'];

            $res[$seq]["กระบวนการ"][$cid ] = ($mcat==1?$mach['process']." แบ่ง ".$mach['input_mult']:$mach['process']);
            $res[$seq]["เครื่องจักร"][$cid ] = $mach['brand_model'];
            $res[$seq]["ชิ้นงานเข้า,$aunit"][$cid] = $input;
            $res[$seq]["ชิ้นงานสมบูรณ์,$aunit"][$cid ] = $output;

            $input = $output;

            //electricity
            $hour = $ratio*($td['idle_min']+$td['load_min'])/60;
            $tt_ele = round($db->mach_ele_kwh($maid, $hour)+$ratio*($td['idle_kwh']+$td['load_kwh']),3);
            $res[$seq]["เวลาทำงาน,ขั่วโมง"][$cid] = round($hour,3);
            $res[$seq]["ไฟฟ้าเครื่องจักร,kwh"][$cid] = round($ratio*($td['idle_kwh']+$td['load_kwh']),3);
            $res[$seq]["ไฟฟ้าอื่นๆ,kwh"][$cid] = round($db->mach_ele_kwh($maid, $hour),3);
            if(isset($ele[$seq])){
                $ele[$seq]['amount'] += $tt_ele;
            } else {
                $ele[$seq]['id'] = $fnmeta['ele_type'];
                $ele[$seq]['amount'] = $tt_ele;
            }

            //วัตถุดิบ
            $tdid = $td['id'];
            $in_mat = $db->view_ef($tdid, $ratio, true);
            $res[$seq]['สารเข้า'] = "";
            foreach($in_mat as $key=>$v){
                $res[$seq][$v['material'].",".$v['unit']][$cid ] = $v['amount'];
                if(isset($mat[$v['material']])){
                    $mat[$v['material']]['amount'] += $v['amount'];
                } else {
                    $mat[$v['material']]['id'] = $v['mat_id'];
                    $mat[$v['material']]['amount'] = $v['amount'];
                }
            }

            //รีไซเคิลกระดาษ
            $out_mat = $db->view_ef($tdid, $ratio, false);
            $res[$seq]['สารออก'] = "";
            $res[$seq]["ชิ้นงานเสีย,$aunit"][$cid] = $damage;
            $paper_waste = round($damage*$com['m_width']*$com['m_length']*$com['weight']/$comp[$k]['mult']/500/3100,5);
            $res[$seq]["ชิ้นงานเสีย,กก"][$cid] = $paper_waste;
            if(isset($waste['รีไซเคิลกระดาษ'])){
                $waste['รีไซเคิลกระดาษ']['amount'] += $paper_waste;
            } else {
                $waste['รีไซเคิลกระดาษ']['id'] = $fnmeta['paper_waste'];
                $waste['รีไซเคิลกระดาษ']['amount'] = $paper_waste;
            }
            //แม่พิพม์ใช้แล้ว
            if($seq=="พิมพ์"){
                $res[$seq]["รีไซเคิลแม่พิมพ์,กก"][$cid ] = $com['plate'];
                if(isset($waste['รีไซเคิลแม่พิมพ์'])){
                    $waste['รีไซเคิลแม่พิมพ์']['amount'] += $com['plate'];
                } else {
                    $waste['รีไซเคิลแม่พิมพ์']['id'] = $fnmeta['plate_waste'];
                    $waste['รีไซเคิลแม่พิมพ์']['amount'] = $com['plate'];
                }
            }
            //ของเสียอื่นๆ
            foreach($out_mat as $kk=>$v){
                $res[$seq][$v['material'].",".$v['unit']][$cid] = $v['amount'];
                if(isset($waste[$v['material']])){
                    $waste[$v['material']]['amount'] += $v['amount'];
                } else {
                    $waste[$v['material']]['id'] = $v['mat_id'];
                    $waste[$v['material']]['amount'] = $v['amount'];
                }

            }
        }
        //ชิ้นงานส่งต่อไปทำเล่ม
        $in['no'][$cid] = $output;
    }
    $in['type'] = $aunit;
    $n = sizeof($in['no'],0);
    //---------------------------- ทำเล่ม --------------------------------
    $finish = $db->get_finishing($fid);
    $x = 0;
    foreach($finish AS $ind=>$mach){
        $seq = $mach['name'];
        $maid = $mach['machine_id'];
        $mmeta = $db->view_mmeta($maid);
        $maxd = (isset($mmeta['max_defect'])&&$mmeta['max_defect']!=""?$mmeta['max_defect']:INF);
        $td = $db->view_testdata($maid);
        $aunit = $mach['allocation_unit'];

        //process พับ
        if($mach['machine_cat_id']=="4"){
            $td= $db->view_testdata($maid);
            $mcat = $mach['machine_cat_id'];
            foreach($comp as $k=>$com){
                $cid = $k;
                $input = $in['no'][$cid];
                $ratio = $input/$td['input'];
                $output = round($ratio*$td['output_ok']);
                $damage = $input-$output;
                //check max defect
                if($damage>$maxd){
                    $damage = $maxd;
                    $output = $input-$damage;
                }
                
                $res[$seq]["กระบวนการ"][$cid] = ($mcat==1?$mach['process']." แบ่ง ".$mach['input_mult']:$mach['process']);
                $res[$seq]["เครื่องจักร"][$cid] = $mach['brand_model'];
                $res[$seq]["ชิ้นงานเข้า,$aunit"][$cid] = $input;
                $res[$seq]["ชิ้นงานสมบูรณ์,$aunit"][$cid] = $output;
                
                $in['no'][$cid]=$output;

                //electricity
                $hour = $ratio*($td['idle_min']+$td['load_min'])/60;
                $tt_ele = round($db->mach_ele_kwh($maid, $hour)+$ratio*($td['idle_kwh']+$td['load_kwh']),3);
                $res[$seq]["เวลาทำงาน,ขั่วโมง"][$cid] = round($hour,3);
                $res[$seq]["ไฟฟ้าเครื่องจักร,kwh"][$cid] = round($ratio*($td['idle_kwh']+$td['load_kwh']),3);
                $res[$seq]["ไฟฟ้าอื่นๆ,kwh"][$cid] = round($db->mach_ele_kwh($maid, $hour),3);
                if(isset($ele[$seq])){
                    $ele[$seq]['amount'] += $tt_ele;
                } else {
                    $ele[$seq]['id'] = $fnmeta['ele_type'];
                    $ele[$seq]['amount'] = $tt_ele;
                }

                //วัตถุดิบ
                $tdid = $td['id'];
                $in_mat = $db->view_ef($tdid, $ratio, true);
                $res[$seq]['สารเข้า'] = "";
                foreach($in_mat as $key=>$v){
                    $res[$seq][$v['material'].",".$v['unit']][$cid ] = $v['amount'];
                    if(isset($mat[$v['material']])){
                        $mat[$v['material']]['amount'] += $v['amount'];
                    } else {
                        $mat[$v['material']]['id'] = $v['mat_id'];
                        $mat[$v['material']]['amount'] = $v['amount'];
                    }
                }

                //รีไซเคิลกระดาษ
                $out_mat = $db->view_ef($tdid, $ratio, false);
                $res[$seq]['สารออก'] = "";
                $res[$seq]["ชิ้นงานเสีย,$aunit"][$cid] = $damage;
                $paper_waste = round($damage*$com['m_width']*$com['m_length']*$com['weight']/$comp[$k]['mult']/500/3100,5);
                $res[$seq]["ชิ้นงานเสีย,กก"][$cid] = $paper_waste;
                if(isset($waste['รีไซเคิลกระดาษ'])){
                    $waste['รีไซเคิลกระดาษ']['amount'] += $paper_waste;
                } else {
                    $waste['รีไซเคิลกระดาษ']['id'] = $fnmeta['paper_waste'];
                    $waste['รีไซเคิลกระดาษ']['amount'] = $paper_waste;
                }
                //แม่พิพม์ใช้แล้ว
                if($seq=="พิมพ์"){
                    $res[$seq]["รีไซเคิลแม่พิมพ์,กก"][$cid ] = $com['plate'];
                    if(isset($waste['รีไซเคิลแม่พิมพ์'])){
                        $waste['รีไซเคิลแม่พิมพ์']['amount'] += $com['plate'];
                    } else {
                        $waste['รีไซเคิลแม่พิมพ์']['id'] = $fnmeta['plate_waste'];
                        $waste['รีไซเคิลแม่พิมพ์']['amount'] = $com['plate'];
                    }
                }
                //ของเสียอื่นๆ
                foreach($out_mat as $kk=>$v){
                    $res[$seq][$v['material'].",".$v['unit']][$cid] = $v['amount'];
                    if(isset($waste[$v['material']])){
                        $waste[$v['material']]['amount'] += $v['amount'];
                    } else {
                        $waste[$v['material']]['id'] = $v['mat_id'];
                        $waste[$v['material']]['amount'] = $v['amount'];
                    }
                }
            }
            //แปลงแผ่นงานเป็นเล่ม
            if($x==0){
                for($i=0;$i<$n;$i++){
                    $in['book'][$i] = $in['no'][$i]*$comp[$i]['sheet_per_plate']/$comp[$i]['sheet_per_unit']/$comp[$i]['mult'];
                }
                $in['min'] = min($in['book']);
                $x++;
            }
            //var_dump($in);
        } else {
            $res[$seq]["กระบวนการ"][$n-1] = $mach['process'];
            $res[$seq]["เครื่องจักร"][$n-1] = $mach['brand_model'];
            //แปลงแผ่นงานเป็นเล่ม
            if($x==0){
                for($i=0;$i<$n;$i++){
                    $in['book'][$i] = $in['no'][$i]*$comp[$i]['sheet_per_plate']/$comp[$i]['sheet_per_unit']/$comp[$i]['mult'];
                }
                $in['min'] = min($in['book']);
                $x++;
            }
            if($mach['machine_cat_id']=="5"){
                //process เรียง
                $ninput = array_sum($in['no']);
                //var_dump($ninput);
                $ratio = $ninput/$td['input'];
                $tin = "";
                $tok = "";
                $tdm = "";
                $tdmw = "";
                for($i=0;$i<$n;$i++){
                    $tin .= ($i==0?"":" / ").$in['no'][$i];
                    $output = round(min($ratio*$td['output_ok'],$in['min']*$comp[$i]['sheet_per_unit']/$comp[$i]['sheet_per_plate']*$comp[$i]['mult']));
                    $tok .= ($i==0?"":" / ").$output;
                    $damage = $in['no'][$i]-$output;
                    $dmw = round($damage/$comp[$i]['mult']*$comp[$i]['m_width']*$comp[$i]['m_length']*$comp[$i]["weight"]/500/3100,5);

                    $tdm .= ($i==0?"":" / ").$damage;
                    $tdmw .= ($i==0?"":" / ").$dmw;
                    //แปลงเป็นเล่ม

                    $in['no'][$i] = $output*$comp[$i]['sheet_per_plate']/$comp[$i]['mult']/$comp[$i]['sheet_per_unit'];
                    //for total
                    if(isset($waste['รีไซเคิลกระดาษ'])){
                        $waste['รีไซเคิลกระดาษ']['amount'] += $dmw;
                    } else {
                        $waste['รีไซเคิลกระดาษ']['id'] = $fnmeta['paper_waste'];
                        $waste['รีไซเคิลกระดาษ']['amount'] = $dmw;
                    }
                }
                $in['type'] = "ชิ้น/เล่ม";
                $res[$seq]["ชิ้นงานเข้า,$aunit"][$n-1 ] = $tin;
                $res[$seq]["ชิ้นงานสมบูรณ์,$aunit"][$n-1 ] = $tok;
            } else {
                if($in['type'] !== $aunit){
                    for($i=0;$i<$n;$i++){
                        $in['no'][$i] = $in['no'][$i]*$comp[$i]['sheet_per_plate']/$comp[$i]['mult']/$comp[$i]['sheet_per_unit'];
                    }
                    $in['type'] = "ชิ้น/เล่ม";
                }
                $ninput = min($in['no']);
                $ratio = $ninput/$td['input'];
                $output = round($ratio*$td['output_ok']);
                $damage = $ninput-$output;
                //check max defect
                if($damage>$maxd){
                    $damage = $maxd;
                    $output = $ninput-$damage;
                }
                $res[$seq]["ชิ้นงานเข้า,$aunit"][$n-1 ] = $ninput;
                $res[$seq]["ชิ้นงานสมบูรณ์,$aunit"][$n-1 ] = $output;
                
                $in['no'][$n-1] = $output;
            }

            //electricity
            $hour = $ratio*($td['idle_min']+$td['load_min'])/60;
            $res[$seq]["เวลาทำงาน,ขั่วโมง"][$n-1 ] = round($hour,3);
            $res[$seq]["ไฟฟ้าเครื่องจักร,kwh"][$n-1 ] = round($ratio*($td['idle_kwh']+$td['load_kwh']),3);
            $res[$seq]["ไฟฟ้าอื่นๆ,kwh"][$n-1 ] = round($db->mach_ele_kwh($maid, $hour),3);
            $tt_ele = round($ratio*($td['idle_kwh']+$td['load_kwh']),3)+round($db->mach_ele_kwh($maid, $hour),3);
            if(isset($ele[$seq])){
                $ele[$seq]['amount'] += $tt_ele;
            } else {
                $ele[$seq]['id'] = $fnmeta['ele_type'];
                $ele[$seq]['amount'] = $tt_ele;
            }
            //วัตถุดิบ
            $in_mat = $db->view_ef($td['id'], $ratio, true);
            $res[$seq]['สารเข้า'] = "";
            foreach($in_mat as $k=>$v){
                $res[$seq][$v['material'].",".$v['unit']][$n-1 ] = $v['amount'];
                if(isset($mat[$v['material']])){
                    $mat[$v['material']]['amount'] += $v['amount'];
                } else {
                    $mat[$v['material']]['id'] = $v['mat_id'];
                    $mat[$v['material']]['amount'] = $v['amount'];
                }
            }
            //ของเสีย
            $out_mat = $db->view_ef($td['id'], $ratio, false);
            $res[$seq]['สารออก'] = "";
            foreach($out_mat as $k=>$v){
                $res[$seq][$v['material'].",".$v['unit']][$n-1] = $v['amount'];
                if(isset($waste[$v['material']])){
                    $waste[$v['material']]['amount'] += $v['amount'];
                } else {
                    $waste[$v['material']]['id'] = $v['mat_id'];
                    $waste[$v['material']]['amount'] = $v['amount'];
                }
            }
            //ของเผื่อ
            if($mach['machine_cat_id']=="5"){
                //รีไซเคิลกระดาษ
                $res[$seq]["ชิ้นงานเผื่อ,$aunit"][$n-1] = $tdm;
                $res[$seq]["ชิ้นงานเผื่อ,กก"][$n-1] = $tdmw;
            } else {
                //รีไซเคิลกระดาษ
                $res[$seq]["ชิ้นงานเสีย,$aunit"][$n-1] = $damage;

                //machine not ตัดสัน
                if($mach['machine_cat_id']!="7"){
                    //คำนวณกลับเป็นแผ่น-> น้ำหนัก
                    $weight = 0;
                    for($i=0;$i<$n;$i++){
                        //แผ่น = เล่ม * (หน้า/เล่ม)/(หน้า/แผ่น)
                        $sheet = $damage*$comp[$i]['sheet_per_unit']/$comp[$i]['sheet_per_plate'];
                        $weight += round($sheet*$comp[$i]['m_width']*$comp[$i]['m_length']*$comp[$i]["weight"]/500/3100,5);
                    }
                    $res[$seq]["ชิ้นงานเสีย,กก"][$n-1] = $weight;

                    if(isset($waste['รีไซเคิลกระดาษ'])){
                        $waste['รีไซเคิลกระดาษ']['amount'] += $weight;
                    } else {
                        $waste['รีไซเคิลกระดาษ']['id'] = $fnmeta['paper_waste'];
                        $waste['รีไซเคิลกระดาษ']['amount'] = $weight;
                    }
                } else {
                    $weight = 0;
                    for($i=0;$i<$n;$i++){
                        //แผ่น = เล่ม * (หน้า/เล่ม)/(หน้า/แผ่น)
                        $sheet = ($damage)*$comp[$i]['sheet_per_unit'];
                        $weight += round($sheet*$comp[$i]['width']*$comp[$i]['length']*$comp[$i]["weight"]/500/3100,5);
                    }
                    $res[$seq]["ชิ้นงานเสีย,กก"][$n-1] = $weight;

                    if(isset($waste['รีไซเคิลกระดาษ'])){
                        $waste['รีไซเคิลกระดาษ']['amount'] += $weight;
                    } else {
                        $waste['รีไซเคิลกระดาษ']['id'] = $fnmeta['paper_waste'];
                        $waste['รีไซเคิลกระดาษ']['amount'] = $weight;
                    }
                }
            }
        }
    }
    //คำนวณเล่ม
    $fin_no = sizeof($finish,0);
    if($fin_no==0){
        for($i=0;$i<$n;$i++){
            $in['book'][$i] = $in['no'][$i]*$comp[$i]['sheet_per_plate']/$comp[$i]['sheet_per_unit']/$comp[$i]['mult'];
        }
        $in['min'] = min($in['book']);
        $bcut = $in['min'];
    } else {
        $bcut = $in['no'][$n-1]+$damage;
    }
    
    
    
    //คำนวณ หนังสือเกิน
    $finfo = $db->view_finfo($fid);
    $over = $output-$finfo['amount'];
    //var_dump($in);
    if($over<0){
        $res[$seq]["ชิ้นงานเผื่อเสีย,".$mach['allocation_unit']][$n-1] = "ชิ้นงานน้อยกว่าแผน ".abs($over);
        $over = 0;
        for($i=0;$i<$n;$i++){
            $res2["ส่วนประกอบที่หมดก่อน"][$i] = ($in['book'][$i]==$in['min']?"*":"");
        }
    } else {
        $res[$seq]["ชิ้นงานเผื่อเสีย,".$mach['allocation_unit']][$n-1] = $over;
    }
    $weight = 0;
    for($i=0;$i<$n;$i++){
        //แผ่น = เล่ม * (หน้า/เล่ม)/(หน้า/แผ่น)
        $sheet = ($over)*$comp[$i]['sheet_per_unit'];
        $weight += round($sheet*$comp[$i]['width']*$comp[$i]['length']*$comp[$i]["weight"]/500/3100,5);
    }
    $res[$seq]["ชิ้นงานเผื่อเสีย,กก"][$n-1] = $weight;
    $waste['รีไซเคิลกระดาษ']['amount'] += $weight;
    
    //คำนวณเศษกระดาษ
    $weight2 = 0;
    for($i=0;$i<$n;$i++){
        //แผ่น = เล่ม * (หน้า/เล่ม)/(หน้า/แผ่น)
        $sheet = $bcut*$comp[$i]['sheet_per_unit']/$comp[$i]['sheet_per_plate'];
        $area_print = $comp[$i]['width']*$comp[$i]['length']*$comp[$i]['sheet_per_plate'];
        $area_sheet = $comp[$i]['m_width']*$comp[$i]['m_length'];
        $weight2 += round($sheet*($area_sheet-$area_print)*$comp[$i]["weight"]/500/3100,5);
    }
    $res[$seq]["เศษกระดาษ,กก"][$n-1] = $weight2;
    $waste['รีไซเคิลกระดาษ']['amount'] += $weight2;

    
    foreach($mat as $k=>$v){
        $res3['วัตถุดิบ'][$k] = $db->get_ef($v['id'],$v['amount']);
    }
    foreach($ele as $k=>$v){
        $res3['การผลิต'][$k] = $db->get_ef($v['id'],$v['amount']);
    }
    foreach($waste as $k=>$v){
        $res3['ของเสียจากการผลิต'][$k] = $db->get_ef($v['id'],$v['amount']);
    }
    
    $wg = 0;
    $fg = min($output,$finfo['amount']);
    for($i=0;$i<$n;$i++){
        //แผ่น = เล่ม * (หน้า/เล่ม)/(หน้า/แผ่น)
        $sheet = ($fg)*$comp[$i]['sheet_per_unit'];
        $wg += round($sheet*$comp[$i]['width']*$comp[$i]['length']*$comp[$i]["weight"]/500/3100,5);
    }
    //distribution
    if(isset($fnmeta['dis_type'])&&$fnmeta['dis_type']!="cal-none"){
        
        $dis_info = json_decode($fnmeta['dis_info'],true);
        if($fnmeta['dis_type']=="cal-gas"){
            $gasinfo = $db->view_matinfo($dis_info['gas']);
            $gas_name = $gasinfo['name'];
            $gas_ef = $gasinfo['ef'];
            $tcarbon = $wg*$dis_info['lperkg']*$gas_ef;
            $res3['ขนส่งสินค้า']["สินค้า"] = array(
                'material' => "Finished Goods",
                "unit" => "กก",
                "amount" => $wg,
                "ef" => "0",
                "material_carbon" => "0",
                "calculate_type" => $fnmeta['dis_type'],
                "gas" => $gas_name,
                "gas_ef" => $gas_ef,
                "liter_per_kg" => $dis_info['lperkg'],
                "name" => "",
                "load_come" => "0",
                "load_back" => "0",
                'distance' => "0",
                'ef_come' => "0",
                'ef_back' => "0",
                'transit_carbon' => number_format($tcarbon,3)
            );
        } else if($fnmeta['dis_type']=="cal-ef"){
            $tcarbon = $wg*$dis_info['ef'];
            $res3['ขนส่งสินค้า']["สินค้า"] = array(
                'material' => "Finished Goods",
                "unit" => "กก",
                "amount" => $wg,
                "ef" => "0",
                "material_carbon" => "0",
                "calculate_type" => "",
                "gas" => "0",
                "gas_ef" => "0",
                "liter_per_kg" => "0",
                "name" => "ค่าเฉลี่ยการขนส่ง",
                "load_come" => "0",
                "load_back" => "0",
                'distance' => "0",
                'ef_come' => $dis_info['ef'],
                'ef_back' => "0",
                'transit_carbon' => number_format($tcarbon,3)
            );
        } else if($fnmeta['dis_type']=="cal-vehicle"){
            $vinfo = $db->view_transport($dis_info['vehicle']);
            $vef = $db->get_transport_ef($dis_info['vehicle']);
            $v_name = $vinfo['name'];
            $ef_come = $vef[$dis_info['goload']];
            $ef_back = $vef[$dis_info['backload']];
            $tcarbon = $wg/1000*$dis_info['distance']*($ef_come+$ef_back/$vinfo['maxload']);
            $res3['ขนส่งสินค้า']["สินค้า"] = array(
                'material' => "Finished Goods",
                "unit" => "กก",
                "amount" => $wg,
                "ef" => "0",
                "material_carbon" => "0",
                "calculate_type" => "",
                "gas" => "",
                "gas_ef" => "",
                "liter_per_kg" => "0",
                "name" => $v_name,
                "load_come" => $dis_info['goload'],
                "load_back" => $dis_info['backload'],
                'distance' => $dis_info['distance'],
                'ef_come' => $ef_come,
                'ef_back' => $ef_back,
                'transit_carbon' => number_format($tcarbon,3)
            );
        }
    }
    
    //using
    $res3['การใช้งาน']["สินค้า"] = array(
        'material' => "Finished Goods",
        "unit" => "กก",
        "amount" => $wg,
        "ef" => "0",
        "material_carbon" => "0",
        "calculate_type" => "",
        "gas" => "",
        "gas_ef" => "0",
        "liter_per_kg" => "0",
        "name" => "",
        "load_come" => "0",
        "load_back" => "0",
        'distance' => "0",
        'ef_come' => "0",
        'ef_back' => "0",
        'transit_carbon' => "0"
    );
    
    //dispose
    //using
    $res3['หลังการใช้งาน']["รีไซเคิลกระดาษ 59% (ฝังกลบ 41%)"] = $db->get_ef($fnmeta['paper_waste'],$wg);
    
    //update fn meta
    $total = 0;
    foreach($res3 as $k=>$v){
        $st[$k] = 0;
        foreach($v as $kk=>$vv){
            $total += $vv['material_carbon']+$vv['transit_carbon'];
            $st[$k] += $vv['material_carbon']+$vv['transit_carbon'];
        }
    }
    
    //var_dump($finfo['amount']);
    //var_dump($output);
    $minout = min($finfo['amount'],$output);
    $meta = array(
        "job_carbon" => $total,
        "carbon_per_unit" => $total/($minout==0?1:$minout)
    );
    $db->update_fn_meta($fid,$meta);
    $res4 = array(
        "output" => $minout,
        "total_carbon" => $total,
        "stage_carbon" => $st,
        "column" => $n
    );
    return array($res,$res2,$res3,$res4);
}

