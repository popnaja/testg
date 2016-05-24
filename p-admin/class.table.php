<?php
class mytable {
    public function show_table($arrheader,$arrdata,$id="",$ex_script="") {
        $html = "<div id='$id' class='ez-table'>"
                . "<table>"
                . "<tr class='tb-head tb-row'>";
        foreach($arrheader as $value){
            $html .= "<th>$value</th>";
        }
        $html .= "</tr>";
        if(isset($arrdata)&&sizeof($arrdata,0)>0){
            foreach($arrdata as $key=>$val){
                if(!is_array($val)){
                    break;
                } else {
                    $html .= "<tr class='tb-data tb-row'>";
                    foreach($val as $k => $v){
                        $html .= "<td>".$v."</td>";
                    }
                    $html .= "</tr>";
                }
            }
        } else {
            $len = sizeof($arrheader,0);
            $html .= "<tr><td colspan='$len' class='td-span5 tb-noinfo'>No information.</td></tr>";
        }
        $html .= "</table>"
                . "<script>$ex_script</script>"
                . "</div><!-- #$id -->\n";
        return $html;
    }
    public function tb_carbon_total($arr,$info,$unit){
        $total = $info['total_carbon'];
        $st = $info['stage_carbon'];
        $amount = ($info['output']==0?1:$info['output']);
        $html = "<table class='full-report'>"
                . "<thead>"
                . "<tr class='tb-carbon-head'>"
                . "<th>กิจกรรม</th>"
                . "<th>องค์ประกอบ</th>"
                . "<th>kg.CO2eq</th>"
                . "<th>%</th>"
                . "<th>kg.CO2eq<br/>รวม</th>"
                . "<th>%</th>"
                . "</tr>"
                . "</thead>";
        //var_dump($arr);
        foreach($arr as $k=>$v){
            $rows = sizeof($v,0);
            $html .= "<tr><th rowspan='$rows'>$k</th>";
            $i = 0;
            foreach($v as $kk=>$vv){
                $tt = $vv['material_carbon']+$vv['transit_carbon'];
                $percent = $tt*100/$total;
                $html .= ($i==0?"":"<tr>");
                $html .= "<th>$kk</th>"
                        . "<td>".number_format($tt,3)."</td>"
                        . "<td>".number_format($percent,1)."</td>";
                if($i==0){
                    $html .= "<td rowspan='$rows'>".number_format($st[$k],3)."</td>"
                            . "<td rowspan='$rows'>".number_format($st[$k]*100/$total,1)."</td>"
                            . "</tr>";
                } else {
                    $html .= "</tr>";
                }
                $i++;
            }
        }
        $unit = 
        $html .= "<tr class='cb-total'><th colspan='2'>ผลรวม</th><td>".number_format($total,3)."<td>kg.CO2eq</td><td></td><td></td></tr>"
                . "<tr class='cb-total'><th colspan='2'>คาร์บอนฟุตพริ้นท์</th><td>".number_format($total/$amount,3)."<td>kg.CO2eq/$unit</td><td></td><td></td></tr>";
        $html .= "</table>";
        return [$html,number_format($total/$amount,3)];
    }
    public function tb_carbon_tt($arr,$info){
        $total = $info['total_carbon'];
        $st = $info['stage_carbon'];
        $amount = $info['output'];
        $html = "<table class='full-report'>"
                . "<tr class='tb-carbon-head'>"
                . "<th>กิจกรรม</th>"
                . "<th>องค์ประกอบ</th>"
                . "<th>หน่วย</th>"
                . "<th>ปริมาณ</th>"
                . "<th>EF</th>"
                . "<th>kg.CO2eq<br/>(วัตถุดิบ)</th>"
                . "<th>น้ำมัน</th>"
                . "<th>EF</th>"
                . "<th>ลิคร/กก</th>"
                . "<th>พาหนะ</th>"
                . "<th>ระยะทาง<br/>(กม)</th>"
                . "<th>โหลด<br/>(%)</th>"
                . "<th>EF</th>"
                . "<th>kg.CO2eq<br/>(ขนส่ง)</th>"
                . "<th>kg.CO2eq<br/>(รวม)</th>"
                . "<th>%</th>"
                //. "<th>kg.CO2eq<br/>รวม</th>"
                //. "<th>%</th>"
                . "</tr>";
        //var_dump($arr);
        foreach($arr as $k=>$v){
            $rows = sizeof($v,0);
            $html .= "<tr><th rowspan='$rows'>$k</th>";
            $i = 0;
            foreach($v as $kk=>$vv){
                $tt = $vv['material_carbon']+$vv['transit_carbon'];
                $percent = $tt*100/$total;
                $html .= ($i==0?"":"<tr>");
                $html .= "<th>$kk</th>"
                        . "<td>".$vv['unit']."</td>"
                        . "<td>".number_format($vv['amount'],2)."</td>"
                        . "<td>".number_format($vv['ef'],3)."</td>"
                        . "<td>".number_format($vv['material_carbon'],3)."</td>"
                        . "<td>".$vv['gas']."</td>"
                        . "<td>".$vv['gas_ef']."</td>"
                        . "<td>".number_format($vv['liter_per_kg'],2)."</td>"
                        . "<td>".$vv['name']."</td>"
                        . "<td>".$vv['distance']."</td>"
                        . "<td>มา(".$vv['load_come'].")<br/>กลับ(".$vv['load_back'].")</td>"
                        . "<td>มา(".$vv['ef_come'].")<br/>กลับ(".$vv['ef_back'].")</td>"
                        ."";
                
                        
                $html .= "<td>".number_format($vv['transit_carbon'],3)."</td>"
                        . "<td>".number_format($tt,2)."</td>"
                        . "<td>".number_format($percent,1)."</td>";
                /*
                if($i==0){
                    $html .= "<td rowspan='$rows'>".number_format($st[$k],3)."</td>"
                            . "<td rowspan='$rows'>".number_format($st[$k]*100/$total,1)."</td>";
                } else {
                    $html .= "</tr>";
                }
                 * 
                 */
                $html .= "</tr>";
                $i++;
            }
        }
        $html .= "<tr class='cb-total'>"
                . "<th colspan='2'>ผลรวม</th>"
                . "<td colspan='2'>".number_format($total,3)."</td>"
                . "<td colspan='2'>kg.CO2eq</td>"
                . "<td colspan='10'></td>"
                . "</tr>"
                . "<tr class='cb-total'>"
                . "<th colspan='2'>คาร์บอนฟุตพริ้นท์</th>"
                . "<td colspan='2'>".number_format($total/$amount,3)."</td>"
                . "<td colspan='2'>kg.CO2eq/หน่วย</td>"
                . "<td colspan='10'></td></tr>";
        $html .= "</table>";
        return [$html,number_format($total/$amount,3)];
    }
    public function tb_carbon($acol,$arr){
        $col = 1;
        foreach($arr as $k=>$v){
            foreach($v as $kk=>$vv){
                $col = max(sizeof($vv,0),$col);
            }
        }
        $html = "<table class='full-report'>";
        //get colume id
        foreach($arr as $k=>$v){
            foreach($v as $kk=>$vv){
                $wu = explode(",",$kk);
                if(sizeof($wu,0)>1){
                    $html .= "<tr><th>$wu[0]</th><th>$wu[1]</th>";
                } else {
                    if(is_array($vv)){
                        $html .= "<tr class='row-hilight'><th colspan='2'>$kk</th>";
                    } else {
                        $html .= "<tr class='row-color'><th colspan='2'>$kk</th>";
                    }
                }
                if(is_array($vv)){
                    for($i=0;$i<$acol;$i++){
                        if(isset($vv[$i])){
                            if(is_numeric($vv[$i])){
                                $dd = number_format($vv[$i],3);
                            } else {
                                $dd = $vv[$i];
                            }
                            $html .= "<td>".$dd."</td>";
                        } else {
                            $html .= "<td></td>";
                        }
                    }
                } else {
                    $html .= "<td colspan='$col'>$vv</td>";
                }
                $html .= "</tr>";
            }
        }
        $html .= "</table>";
        return $html;
    }
    public function tb_carbon_sum($arr){
        $html = "<table class='full-report'>";
        foreach($arr as $k=>$v){
            $wu = explode(",",$k);
            if(sizeof($wu,0)>1){
                $html .= "<tr><th>$wu[0]</th><th>$wu[1]</th>";
            } else {
                $html .= "<tr><th colspan='2'>$k</th>";
            }
            foreach($v as $key=>$val){
                $remark = ($val=="*"?"class='tb-red-bg'":"");
                $html .= "<td $remark>$val</td>";
            }
            $html .= "</tr>";
        }
        return $html;
    }
    public function tb_limited($ahead,$adata,$iperpage){
        $pages = ceil(sizeof($adata,0)/$iperpage);

        $tb .= "<div class='ez-table'>";
        //navigation 
        $tb .= "<div class='tb-nav'>"
                . "<a href='' title='Go to first page.' class='tb-nav-firstpage '></a>"
                . "</div>";
        $tb .= "</div><!-- .ez-tabel -->";
        return $tb;
    }
    public function tb_one($header,$arrdata,$id=""){
        $html = "<div id='$id' class='ez-table'>"
                . "<table>"
                . "<tr>";
        $html .= "<th>$header</th>"
                . "</tr>";
        if(!is_array($arrdata)){
            $html .= "<tr><td>-</td></tr>";
        } else {
            foreach($arrdata AS $k=>$v){
                $html .= "<tr><td><a href='$k' title='$v'>$v</a></td></tr>";
            }
        }
        $html .= "</table>"
                . "</div><!-- #$id -->\n";
        return $html;
    }
    public function show_filter($url,$name,$arr,$current,$show){
        __autoloada("form");
        $base = $this->prep_get_url($url, $name);
        $form = new myform();
        $html = "<div class='tb-filter'>"
                . $form->show_select($name,array("0"=>"$show")+$arr,"label-inline",null,$current)
                . "</div><!-- .tb-filter -->"
                . "<script>"
                . "tb_filter('$name','$base')"
                . "</script>";
        return $html;
    }
    public function show_pagenav($url,$page,$max){
        if($max<=1){
            return "";
        }
        $base = $this->prep_get_url($url, "page");
        $first = $base."1";
        $last = $base.$max;
        switch($page){
            case 1 :
                $pclass = "nav-inactive";
                $nclass = "";
                $prev = $base."1";
                $next = $base.($page+1);
                break;
            case $max:
                $pclass = "";
                $nclass = "nav-inactive";
                $prev = $base.($page-1);
                $next = $base.$max;
                break;
            default:
                $pclass = "";
                $nclass = "";
                $prev = $base.($page-1);
                $next = $base.($page+1);
        }
        $nav = "<div class='page-nav'>"
                . "<a href='$first' title='Go to the first page' class='pnav-icon icon-jump-left $pclass'></a>"
                . "<a href='$prev' title='Go to the previous page' class='pnav-icon icon-triangle-left $pclass'></a>";
        $nav .= "<select class='page-sel'>";
        for($i=1;$i<=$max;$i++){
            $sel = ($page==$i?" selected='selected'":"");
            $nav.= "<option value='$i'$sel>$i</option>";
        }
        $nav .= "</select>"
                . "<span class='tt-page'> / $max</span>";
        $nav .= "<a href='$next' title='Go to the next page' class='pnav-icon icon-triangle-right $nclass'></a>"
                . "<a href='$last' title='Go to the last page' class='pnav-icon icon-jump-right $nclass'></a>";
        $nav .= "</div><!-- .page-nav -->"
                . "<script>page_change('$base','page-sel');</script>";
        
        return $nav;
    }
    public function show_new_pagenav($base,$now,$max){
        $html = "<div class='all-pages'>";
        if($now<7){
            for($i=1;$i<=11;$i++){
                switch($i){
                    case $now :
                        $html .= "<span>$i</span>";
                        break;
                    case 10:
                        $html .= "<span>...</span>";
                        break;
                    case 11:
                        $html .= "<a href='".$base.$max."/'>$max</a>";
                        break;
                    default:
                        $html .= "<a href='".$base.$i."/'>$i</a>";
                }
            }
        } else if($now>=7 && $now<($max-5)) {
            for($i=1;$i<=11;$i++){
                switch($i){
                    case 1 :
                        $html .= "<a href='".$base.$i."/'>$i</a>";
                        break;
                    case 2 :
                        $html .= "<span>...</span>";
                        break;
                    case 10:
                        $html .= "<span>...</span>";
                        break;
                    case 11:
                        $html .= "<a href='".$base.$max."/'>$max</a>";
                        break;
                    case 6:
                        $html .= "<span>$now</span>";
                        break;
                    default:
                        if($i<6){
                            $num = $now-(6-$i);
                            $html .= "<a href='".$base.$num."/'>$num</a>";
                        } else {
                            $num = $now+($i-6);
                            $html .= "<a href='".$base.$num."/'>$num</a>";
                        }
                }
            }
        } else {
            for($i=1;$i<=11;$i++){
                switch($i){
                    case 1:
                        $html .= "<a href='".$base.$i."/'>$i</a>";
                        break;
                    case 2:
                        $html .= "<span>...</span>";
                        break;
                    default:
                        $num = $max-(11-$i);
                        if($num==$now){
                            $html .= "<span>$num</span>";
                        } else {
                            $html .= "<a href='".$base.$num."/'>$num</a>";
                        }
                }
            }
        }
        $html .= "</div><!-- .all-pages -->";
        return $html;
    }
    public function show_option($rowkey,$rowval,$colkey,$colval){
        __autoload("form");
        $form = new myform();
        $html = "<div id='op-wrap'>"
                . "<div class='op-tb cheight'>"
                . $form->show_num("row_val",$rowval,1,"1-9999","Rows/page","","label-4050 cheight")
                . $form->show_checkbox("op-tb-check","col_val",$colval,"op-tb-col cheight")
                . $form->show_button("op-tb-apply","Apply","sm-but-right")
                . $form->show_hidden("row_key","row_key",$rowkey)
                . $form->show_hidden("col_key","col_key",$colkey);
        $arrn = json_encode($form->array_name);
        $colv = json_encode($colval);
        $html .= "</div><!-- .op-tb -->"
                . "</div>"
                . "<div class='op-but icon-widget'></div>"
                . $form->submitscript("opt_apply($arrn,$colv);")
                . "<script>"
                . "opt_show();"
                . "</script>";
        return $html;
    }
    public function prep_get_url($url,$name){
        if(is_integer(strpos($url,"?"))){
            $t = explode("?",$url);
            $base = $t[0];
            $get = explode("&",$t[1]);
            foreach($get as $k=>$v){
                if(strlen($v)<1){
                    unset($get[$k]);
                } else if(is_integer(strpos($v,$name."="))){
                    unset($get[$k]);
                }
            }
            if(count($get)>0){
                $res = $base."?".implode("&",$get)."&$name=";
            } else {
                $res = $base."?$name=";
            }
            return $res;
        } else {
            return $url."?$name=";
        }
    }
}
