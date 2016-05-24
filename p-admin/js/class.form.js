$("document").ready(function(){
    /* pg-select */
    var input = $(".pg-select");
    var tg = $(".pg-select input");
    var list = $(".pg-select ul");
    var item = $(".pg-select ul li");
    var qty,isin;
    input.on("click",function(){
        var i = input.index($(this));
        if(list.eq(i).hasClass("form-hide")){
            list.addClass("form-hide");
            list.eq(i).removeClass("form-hide");
        } else {
            list.addClass("form-hide");
        }
        
    });
    item.on("click",function(){
        qty = $(this).attr("pg-val");
        $(this).parent().siblings("div").children("input").val(qty);
    });
    input.on("mouseout",function(){
        isin = false;
    });
    input.on("mouseover",function(){
        isin = true;
    });
    $("body").on("click",function(){
        if(!isin){
            list.addClass("form-hide");
        }
    });
    
});
//close msg
function close_msg(){
    $('.close-msg').click(function(){
        $(this).parent().parent().remove();
    });
}
$(document).ready(function(){
    close_msg();
});
/*inputenter when hit enter will submit botton */
function inputenter(arrayinput,butid){
    var button = $("#"+butid);
    $.each(arrayinput,function(k,v){
       $("#"+v).keypress(function(e){
           //enter
           if(e.which === 10 || e.which === 13){
               e.preventDefault();
               button.trigger("click");
           }
       });
    });
}
function show_msg(tgid,msg,ok){
    var tg = $("#"+tgid);
    var dclass,icon;
    if(ok){
        dclass = "ok-msg";
        icon = "icon-check-mark-circle";
    } else {
        dclass = "ng-msg";
        icon = "icon-alert";
    }
    var html = "<div id='pg-msg-wrap'><div id='pg-message' class='"+dclass+"'>\n\
<span class='icon-remove close-msg'></span>\n\
<span class='pg-msg-icon "+icon+"'></span>\n\
<p>"+msg+"</p></div>\n\
<script>close_msg();</script>\n\
</div>";
    $("#pg-msg-wrap").remove();
    tg.html(html);
}
//dialog
function pg_dialog(title,msg){
    var html = "<div class='pg-dialog'>\n\
<div>\n\
<h3>"+title+"</h3>\n\
<p>"+msg+"</p>\n\
<div class='ok-but'><input type='button' value='OK' onclick='close_dialog()' /></div>\n\
</div>\n\
</div><!-- .pg-dialog -->";
    $("#content").prepend(html);
}
function close_dialog(){
    $(".pg-dialog").remove();
    $("body").removeClass("pg-loading");
}

//my pg loading use with css in form.css
function pg_loading(st,title,msg){
    //change text
    if(typeof title !== "undefined"){
        $(".ajax-dialog h3").html(title);
        $(".ajax-dialog p").html(msg);
    }
    var body = $("body");
    var dialog = $(".pg-loading-dialog");
    if(st){
        dialog.fadeIn("fast","linear");
        body.addClass("pg-loading");
    } else {
        $(".ajax-dialog h3").html("Loading....");
        $(".ajax-dialog p").html("");
        dialog.fadeOut("slow","linear");
        body.removeClass("pg-loading");
    }
    
}
function post_ajax(data,url,silent){
    if(typeof url === "undefined"){
        url = "http://localhost/smartgreeny/p-admin/request.php";
    }
    if(silent!==true){
        pg_loading(true);
    }
    $.ajax({
        url:url,
        type:'POST',
        dataType:"json",
        data:data,
        success: function(response) {
            //console.log("SUCCESS"+JSON.stringify(response));
            pg_loading(false);
            var flag = response[0];
            if(flag==='redirect'){
                var url = response[1];
                window.location.replace(url);
            } else if (flag==="showmsg"){
                var tg = $("#ez_msg");
                tg.html(response[1]);
                $(window).scrollTop(0);
            } else if (flag==="reload"){
                location.reload();
            } else if (flag==="html_replace"){
                var target = $("#"+response[1]);
                var html = response[2];
                target.html(html);
                if(typeof response[3] !== "undefined"){
                    $("#"+response[3]).html(response[4]);
                }
            } else if (flag==="html_after"){
                var target = $("#"+response[1]);
                var html = response[2];
                target.after(html);
            } else if (flag==="show_term"){
                show_term(response);
            } else if (flag==="myOK"){
                pg_dialog(response[1],response[2]);
                $("body").addClass("pg-loading");
            }
        },
        error: function(err){
            console.log("ERROR"+JSON.stringify(err));
            pg_loading(false);
        }
    });
}
function get_val(arrname){
    var adata = {},t;
    //console.log(JSON.stringify(arrname));
    $.each(arrname,function(k,v){
        if(v==='text'||v==='number'||v==='hidden'){
            adata[k]=$("input[name="+k+"]").val();
        } else if(v==="select"){
            adata[k]=$("#"+k+" :selected").val();
        } else if(v==="textarea"){
            adata[k]=$("#"+k).val();
        } else if(v==="radio"){
            adata[k]=$("input[name="+k+"]:checked").val();
        } else if(v==="checkbox"){
            t=$("input[name="+k+"]:checked");
            adata[k]=[];
            $.each(t,function(){
               adata[k].push($(this).val());
            });
        }
    });
    return adata;
}
function update_fb_cache(url){
    $(document).ready(function(){
        $.post(
            'https://graph.facebook.com',
            {
                id: url,
                scrape: true
            },
            function(response){
                //console.log(response);
            }
        );
    });
}
function scroll_an(id){
    var s_top = $("#"+id).offset().top-40;
    $("html body").animate({scrollTop:s_top},300);
}
function silent_ajax(data,url){
    $.ajax({
        url:url,
        type:'POST',
        dataType:"json",
        data:data,
        success: function(response) {
            //console.log("SUCCESS"+JSON.stringify(response));
        },
        error: function(err){
            console.log("ERROR"+JSON.stringify(err));
        }
    });
}
/*validate no blank */
function valNoBlank(id){
    var ele = $("#"+id);
    var val = ele.val();
    if(val){
        ele.css({'border-color':'rgb(238,238,238)'});
        ele.parent().css({'background-color':"initial"});
        return true;
    } else {
        ele.attr("placeholder","Required");
        ele.css({'border-color':'#ff5b42'});
        ele.parent().css({'background-color':"#ff9282"});
        return false;
    }
}
function valZero(arr){
    var res = 0;
    console.log(arr);
    for(var i in arr){
        var ele = $("#"+arr[i]);
        var val = ele.val();
        console.log(val);
        if(parseFloat(val)===0){
            ele.css({'border-color':'#ff5b42'});
            ele.parent().css({'background-color':"#ff9282"});
            res--;
        } else {
            ele.css({'border-color':'rgb(238,238,238)'});
            ele.parent().css({'background-color':"initial"});
            res++;
        }
    }
    if(res===arr.length){
        return true;
    }  else {
        return false;
    }
}
function valSel(arr){
    var res =0;
    for(var i in arr){
        var ele = $("#"+arr[i]);
        var val = ele.val();
        if(val==="none"||val==="0"||val===0){
            ele.css({'border-color':'#ff5b42'});
            ele.parent().css({'background-color':"#ff9282"});
            res--;
        } else {
            ele.css({'border-color':'rgb(238,238,238)'});
            ele.parent().css({'background-color':"initial"});
            res++;
        }
    }
    if(res===arr.length){
        return true;
    }  else {
        return false;
    }
}
function valNoMatch(id1,id2){
    var ele1 = $("#"+id1);
    var ele2 = $("#"+id2);
    var val1 = ele1.val();
    var val2 = ele2.val();
    var reg_pass = /^[a-zA-Z0-9-_.!@#$%^&\*()_\+=|\[-\]]{4,30}$/;
    if(val1!==""&&val1.search(reg_pass)===-1){
        ele1.val("");
        ele1.attr("placeholder","รหัสผ่านอย่างน้อย 4 ตัว.");
        ele1.css({'border-color':'#ff5b42'});
        ele1.parent().css({'background-color':"#ff9282"});
    } else if(val1!==val2){
        ele2.val("");
        ele2.attr("placeholder","Not Match");
        ele2.css({'border-color':'#ff5b42'});
        ele2.parent().css({'background-color':"#ff9282"});
        return false;
    } else {
        ele2.css({'border-color':'rgb(238,238,238)'});
        ele2.parent().css({'background-color':"initial"});
        return true;
    }
}
function valEmail(id){
    var email = $("#"+id);
    var email_filter = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-z0-9](?:[a-zA-z0-9-]{0,61}[a-zA-z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-z0-9]{0,61}[a-zA-z0-9])?)+$/;
    if(email.val().search(email_filter) !== -1){
        email.attr("placeholder","");
        email.css({'border-color':"rgb(238,238,238)"});
        email.parent().css({'background-color':"initial"});
        return true;
    } else {
        email.val("");
        email.attr("placeholder","Required email format, example@gmail.com");
        email.css({'border-color':'#ff5b42)'});
        email.parent().css({'background-color':"#ff9282"});
        return false;
    }
}
function valPreg(arrp){
    var ele,val,res=0,len=0;
    $.each(arrp,function(k,v){
        ele = $("#"+v);
        val = ele.val();
        //console.log(val);
        //console.log(val.search(RegExp(k,"i")));
        len++;
        if(val.search(RegExp(k,"i")) === -1){
            ele.css({'border-color':'#ff5b42)'});
            ele.parent().css({'background-color':"#ff9282"});
            res--;
        } else {
            ele.css({'border-color':"rgb(238,238,238)"});
            ele.parent().css({'background-color':"initial"});
            res++;
        }
    });
    if(res===len){
        return true;
    }  else {
        return false;
    }
}
function nameOk(cl){
    var check = $("."+cl);
    var tg = check.parent().parent();
    if(check.hasClass("icon-remove")){
        tg.css({'background-color':"#ff9282"});
        return false;
    } else {
        tg.css({'background-color':"initial"});
        return true;
    }
}
function pass_strength(passid,repassid,indicatorid){
    var html,bg,bor;
    var indi = $("#"+indicatorid);
    var pass = $("#"+passid);
    var repass = $("#"+repassid);
    pass.on("keyup",function(e){
        if(e.which===9){
            return;
        }
        //console.log(e.which);
        var pval = pass.val();
        var len = (pval.length>7?1:0);
        var res = has_uln(pval);
        var num = res['num'];
        var up = res['up'];
        var low = res['low'];
        var sp = res['sp'];
        var final = len+num+up+low+sp;
        switch(final) {
            case 1 :
                indi.html("Very weak");
                indi.css({
                   "background-color":"#ffa0a0",
                   "border-color":"#f04040"
                });
                break;
            case 2 :
                indi.html("Very weak");
                indi.css({
                   "background-color":"#ffa0a0",
                   "border-color":"#f04040"
                });
                break;
            case 3 :
                indi.html("Weak");
                indi.css({
                   "background-color":"#ffb78c",
                   "border-color":"#ff853c"
                });
                break;
            case 4 :
                indi.html("Medium");
                indi.css({
                   "background-color":"#ffec8b",
                   "border-color":"#ffcc00"
                });
                break;
            case 5 :
                indi.html("Strong");
                indi.css({
                   "background-color":"#8fe65c",
                   "border-color":"#0ab36e"
                });
                break;
            default :
                indi.html("Strength Indicator");
                indi.css({
                   "background-color":"#f1f1f1",
                   "border-color":"rgb(150,150,150)"
                });
        }
        html = indi.html();
        bg = indi.css("background-color");
        bor = indi.css("border-color");
        //console.log(final);
    });
    repass.on("keyup",function(e){
        if(e.which===9){
            return;
        }
        var pval = pass.val();
        var rval = repass.val();
        //console.log(pval);
        //console.log(rval);
       if(pval===rval || rval===""){
            indi.html(html);
            indi.css({
               "background-color":bg,
               "border-color":bor
            });
       } else {
            indi.html("Mismatch");
            indi.css({
               "background-color":"#ffa0a0",
               "border-color":"#f04040"
            });
       }
    });
}
function has_uln(str){
    var res = {"num":0,"up":0,"low":0,"sp":0};
    var fnum = new RegExp("[0-9]");
    var fup = new RegExp("[A-Z]");
    var flow = new RegExp("[a-z]");
    var fsp = new RegExp("[!@#$%^&*()_+=]|[-]");
    if(fnum.test(str)){
        res['num']=1;
    } 
    if(fup.test(str)){
        res['up']=1;
    }
    if(flow.test(str)){
        res['low']=1;
    }
    if(fsp.test(str)){
        res['sp']=1;
    }
    return res;
}
function show_more(id,tgclass,st){
    var but = $("#"+id);
    var tg = $("."+tgclass);
    var c = 0;
    if(typeof st !== "undefined"){
        c = st;
    }
    tg.eq(c).removeClass("form-hide");
    but.on("click",function(e){
        e.preventDefault();
        tg.eq(c+1).removeClass("form-hide");
        c++;
    });
}
function pg_tab_act(){
    $(document).ready(function(){
        var but = $(".pg-tab-tab");
        var data = $(".pg-tab-item");
        but.on("click",function(e){
            e.preventDefault();
            var i = but.index($(this));
            but.removeClass("pg-tab-active");
            $(this).addClass("pg-tab-active");
            data.addClass("form-hide");
            data.eq(i).removeClass("form-hide");
        });
    });
}
function check_name(id,pid){
    var input = $("#"+id);
    var url = $("#referurl").val();
    var reg = /[a-zก-ฮ]+/i; //atleast 1 normal letter
    input.on("blur",function(){
        var val = input.val();
        if(val.search(reg)===-1){
            cross("name-check","icon-remove","อย่างน้อย 1 ตัวอักษร a-z,ก-ฮ");
        } else {
            var data = {};
            if(typeof pid === "number"){
                data['pid'] = pid;
            }
            data['request'] = "check_name";
            data['name'] = val;
            data['field'] = 'post_title';
            ajax_name("name-check",data,url);
        }
    });
}
function check_tname(id,tid){
    var input = $("#"+id);
    var url = $("#referurl").val();
    var reg = /[a-zก-ฮ]+/i; //atleast 1 normal letter
    input.on("blur",function(){
        check();
    });
    $("#parent").on("change",function(){
        check();
    });
    function check(){
        var val = input.val();
        if(val.search(reg)===-1){
            cross("tname-check","icon-remove","อย่างน้อย 1 ตัวอักษร a-z,ก-ฮ");
        } else {
            var data = {};
            if(typeof tid === "number"){
                data['tid'] = tid;
            }
            data['request'] = "check_tname";
            data['name'] = val;
            data['tax'] = $("#taxonomy").val();
            data['parent'] = $("#parent").val();
            ajax_name("tname-check",data,url);
        }
    }
}
function check_coupon(id,pid){
    var input = $("#"+id);
    var url = $("#referurl").val();
    console.log(url);
    var reg = /^[A-Z0-9]{4,10}$/; //A-Z และ หรือ 0-9 จำนวน 4 ภึง 10 ตัวอักษร
    input.on("blur",function(){
        var val = input.val();
        if(val.search(reg)===-1){
            cross("name-check","icon-remove","A-Z และ หรือ 0-9 จำนวน 4 ภึง 10 ตัวอักษร");
        } else {
            var data = {};
            if(typeof pid === "number"){
                data['pid'] = pid;
            }
            data['request'] = "check_name";
            data['name'] = val;
            data['field'] = 'post_title';
            ajax_name("name-check",data,url);
        }
    });
}
function check_email(id,uid){
    var input = $("#"+id);
    var data={};
    var url = $("#referurl").val();
    var email_filter = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-z0-9](?:[a-zA-z0-9]{0,61}[a-zA-z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-z0-9]{0,61}[a-zA-z0-9])?)+$/;
    input.on("blur",function(){
        var val = input.val();
        if(val.search(email_filter) === -1){
            cross("check-email","icon-remove","รูปแบบอีเมลไม่ถูกต้อง ตัวอย่างอีเมล admin@gmail.com");
        } else {
            data['request'] = "check_email";
            data['email'] = val;
            if(typeof uid === "number"){
                data['uid'] = uid;
            }
            ajax_name("check-email",data,url);
        }
    });
}
function ajax_name(tg,data,url){
    $.ajax({
        url:url,
        type:'POST',
        dataType:"json",
        data:data,
        success: function(response) {
            //console.log(response);
            if(response[1]==="icon-remove"){
                cross(tg,response[1],response[2]);
            } else {
                check(tg,response[1]);
            }
        },
        error: function(err){
            console.log("ERROR"+JSON.stringify(err));
        }
    });
}
function cross(tgclass,ac,msg){
    var tg = $("."+tgclass);
    tg.css({"color":"#f04040"});
    tg.removeClass("icon-check");
    tg.html("<span class='des'><span class='des-arr-up'></span>"+msg+"</span>");
    tg.addClass(ac);
}
function check(tgclass,ac){
    var tg = $("."+tgclass);
    tg.css({"color":"#0ab36e"});
    tg.removeClass("icon-remove");
    tg.html("<span class='des'><span class='des-arr-up'></span>This name is OK.</span>");
    tg.addClass(ac);
}
function letter_nav(){
    var let = $(".nav-l");
    var url = $("#referurl").val();
    var data={};
    let.on("click",function(){
        data['letter'] = $(this).text();
        data['request'] = "get_fav_byletter";
        data['uid'] = $("#uid").val();
        post_ajax(data,url);
    });
}
function show_submit_error(id){
    var tg = $("#"+id);
    var html = "<div id='pg-msg-wrap' class='submit-error'>\n\
<div id='pg-message' class='ng-msg'>\n\
<span class='icon-remove close-msg'></span>\n\
<span class='pg-msg-icon icon-alert'></span>\n\
<p>กรุณาตรวจสอบข้อมูลใหม่อีกครั้ง</p>\n\
</div>\n\
<script>close_msg();</script>\n\
</div>\n\
";
    $(".submit-error").remove();
    tg.html(html);
}
function close_msg(){
    $('.close-msg').click(function(){
        $(this).parent().parent().remove();
    });
}
function sub_section(){
    $(document).ready(function(){
        var check = $(".sub-sec-check input");
        var tg = $(".sub-sec");
        var hid = $(".sub-sec-hid");
        $.each(check,function(){
            if($(this).prop("checked")){
                var i = check.index($(this));
                tg.eq(i).removeClass("form-hide");
            }
        });
        check.on("change",function(){
            var i = check.index($(this));
            if($(this).prop("checked")){
                tg.eq(i).removeClass("form-hide");
            } else {
                tg.eq(i).addClass("form-hide");
            }
        });
    });
}
function show_unit(){
    var input = $(".pg-units input");
    input.on("blur",function(){
        var val = $(this).val();
        var i = input.index($(this));
        $("#us_"+i).val(val);
        $("#uh_"+i).val(val);
        $("#ud_"+i).val(val);
    });
    //show stock same as unit
    var tg = $(".stk-item");
    var addu = $("#add-more-unit");
    var i = 0;
    tg.eq(i).removeClass("form-hide");
    addu.on("click",function(e){
        e.preventDefault();
        i++;
        tg.eq(i).removeClass("form-hide");
    });
    
    //show manage stock
    var manage = $(".pg-mstk select");
    manage.on("change",function(){
        var tg = $(this).parent().siblings(".pg-mstk-child");
        if($(this).val()==="yes"){
            tg.removeClass("form-hide");
        } else {
            tg.addClass("form-hide");
        }
    });
}
function get_utz(){
    var tz = { 
        "-12":'Pacific/Kwajalein', 
        "-11":'Pacific/Samoa', 
        "-10":'Pacific/Honolulu', 
        "-9":'America/Juneau', 
        "-8":'America/Los_Angeles', 
        "-7":'America/Denver', 
        "-6":'America/Mexico_City', 
        "-5":'America/New_York', 
        "-4":'America/Caracas', 
        "-3.5":'America/St_Johns', 
        "-3":'America/Argentina/Buenos_Aires', 
        "-2":'Atlantic/Azores',
        "-1":'Atlantic/Azores', 
        "0":'Europe/London', 
        "1":'Europe/Paris', 
        "2":'Europe/Helsinki', 
        "3":'Europe/Moscow', 
        "3.5":'Asia/Tehran', 
        "4":'Asia/Baku', 
        "4.5":'Asia/Kabul', 
        "5":'Asia/Karachi', 
        "5.5":'Asia/Calcutta', 
        "6":'Asia/Colombo', 
        "7":'Asia/Bangkok', 
        "8":'Asia/Singapore', 
        "9":'Asia/Tokyo', 
        "10":'Pacific/Guam', 
        "11":'Asia/Magadan', 
        "12":'Asia/Kamchatka' 
    };
    var usertime = new Date();
    var utz = -usertime.getTimezoneOffset()/60;
    var utzn = tz[utz];
    if(typeof utzn === "undefined"){
        utzn = "GMT";
    }
    $("#register").after("<input type='hidden' name='utz' value='"+utzn+"'/>");
}
function view_schedule(){
    $(document).ready(function(){
        var sch = $('#s-date');
        var sel = $("#status");
        var tg = $("#form-schedule");
        sch.datepicker({
            dateFormat: 'yy-mm-dd',
            minDate: 0
        });
        check_sch();
        sel.on("change",function(){
            check_sch();
        });
        function check_sch(){
            if(sel.val()==="schedule"){
                tg.removeClass("form-hide");
                var reg = /^[0-9]{4}-[0-1]{1}[0-9]{1}-[0-3]{1}[0-9]{1}$/;
                sch.on("change",function(){
                    var val = sch.val();
                    if(val.search(reg) === -1){
                        cross("date-check","icon-remove","รูปแบบวันที่ไม่ถูกต้อง ตัวอย่างวันที่ 2015-10-01");
                    } else {
                        check("date-check","icon-check");
                    }
                });
            } else {
                tg.addClass("form-hide");
                sch.val("");
                check("date-check","icon-check");
            }
        }
    });
}
function view_more_section(cl){
    $(document).ready(function(){
        var but = $("#view-more-but");
        var n = $("."+cl).length;
        but.on("click",function(){
            var hid = $("."+cl+".form-hide").length;
            var next = n-hid;
            $("."+cl).eq(next).removeClass("form-hide");
            if(next===n){
                but.hide();
            }
        });
    });
}