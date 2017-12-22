<?php
	session_start();
	//包含需求檔案 ------------------------------------------------------------------------
	include("./bcontroller/class/common_lite.php");
	include("./bcontroller/module_function/mathematics.php");//數學模組
	include("./bcontroller/module_function/common_use.php");//通用模組

	$head_name_dsc = '';
	$module_html = '';
	$whereDsc ='';

	if($_SESSION['loginType'] == ''){
		ri_jump("logout.php");
	}else{
		$_SESSION['testPowerValue']='0,0,0,0,0,0,0,0,0,0,0,0';//清空能力指標的12項數值
		$_SESSION['questionsPowerValue'] = '[]';//初始化每組對話能力資料

		switch($_SESSION['loginType']){
			case "ADMIN":
				$test_user='';
				$test_user_type='ADMIN';
			break;
			case "TEACHER":
			$test_user=$_SESSION['swTeacherNum'];
			$test_user_type='TEACHER';
			break;
			case "STUDENT":
			$test_user=$_SESSION['swStudentNum'];
			$test_user_type='STUDENT';
			break;
			default:
			ri_jump("logout.php");
			break;
		}
	}
	//宣告變數 ----------------------------------------------------------------------------
	$ODb = new run_db("mysql",3306);      //建立資料庫物件
	if($_GET['m_num'] !=''){
		//檢查此題目是否可以測驗
		if($_SESSION['loginType'] == 'ADMIN'){

		}ELSE{
			$isOk = false;
			if($_SESSION['loginType'] == 'TEACHER'){$teacherNum = $_SESSION['swTeacherNum'];}
			if($_SESSION['loginType'] == 'STUDENT'){$teacherNum = $_SESSION['teacherdataNum'];}
			$sql_dsc="select * from `main_data` where `num`='".$_GET['m_num']."' and `create_user`='".$teacherNum."' and `create_user_type`='TEACHER'  order by `num` limit 1";
			$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
			if(mysql_num_rows($res)==1){
				$isOk=true;
			}
			$sql_dsc="
			select `a`.*,`b`.`mainData_num`,`b`.`user_type`,`b`.`user_num`
			from `main_data` as `a`
			left join `share_data` as `b` on `b`.`mainData_num`=`a`.`num`
			where `b`.`user_type`='TEACHER' and `b`.`user_num`='".$teacherNum."' and `a`.`is_share`=1 order by `a`.`num` limit 1";
			$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
			if(mysql_num_rows($res)==1){
				$isOk=true;
			}
			if($isOk == false){
				ri_jump("index.php");
			}
		}

		$_SESSION['questionsPowerValue'] = get_Questions_Array($_GET['m_num']);//初始化每組對話能力資料

		$sql_dsc = "
		select * from `operation_data` where `main_data_num`='".$_GET['m_num']."' ".$whereDsc." order by `num` limit 1";
		$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
		if(mysql_num_rows($res)==1){
			while($row = mysql_fetch_array($res)){
				$operation_data_num = $row['num'];
			}
		}else{
			ri_jump("logout.php");
		}
	}else{
		ri_jump("logout.php");
	}

	//取出單元資料與作業資料
	$sql_dsc = "
	select `a`.`num`,`a`.`main_data_num`,`a`.`c_title` as `op_dsc`,`b`.`num`,`b`.`c_title` as `main_dsc`,`b`.`c_test_time`,`b`.`c_speech_type`,`b`.`use_sound`
	from `operation_data` as `a`
	left join `main_data` as `b` on `b`.`num`=`a`.`main_data_num`
	where `a`.`num`='".$operation_data_num."'";
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){
		$op_dsc = $row['op_dsc'];//作業名稱
		$main_dsc = $row['main_dsc'];//單元名稱
		$c_test_time = $row['c_test_time'];//測驗時間
		$c_speech_type = $row['c_speech_type'];//對話類型
		$use_sound = $row['use_sound'];//電腦對白是否開啟語音

	}

	//計算作業數量
	$sql_dsc = "
	select count(*) as `op_num` from `operation_data` where `main_data_num`='".$_GET['m_num']."'
	";
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){
		$op_num = $row['op_num'];//作業數量
	}

	//取出試題1資料
	$sql_dsc= "select * from `questions_data` where `operation_data_num`=".$operation_data_num." order by `num` limit 1";
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){
		$questions_data_num = $row['num'];
	}

	//取出會話1相關資料
	$dsc_first="";
	$dsc_0="";
	$dsc_1="";
	$dsc_2="";
	$num =0;
	$free_speech_type_dsc = "";//開放式對話的對話字串
	$free_speech_type_index= "";//開放式對話的key字串

	$sql_dsc= "
	select * from `speak_data`
	where `questions_data_num`=".$questions_data_num." and `c_dsc_type`=0 order by `num`  ";
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){
		if($row['c_user_type'] == 0){//使用者對話
			$dsc_0.= "<li><input type='radio' subid='talk_radio' onclick=\"get_last_pcmsg('".$row['num']."');set_record('radio||speech_radio_".$num."||".$row['questions_data_num']."');set_getPowerKey(".$row['num'].")\"  name='speech_radio' id='speech_radio_".$num."' value='".$row['num']."'  ><label for='speech_radio_".$num."'>".$row['c_dsc']."</label></li>";
			$num++;
			if($free_speech_type_dsc=="" && $free_speech_type_index==""){
				$free_speech_type_dsc.= $row['c_dsc'];
				$free_speech_type_index.= $row['c_dsc'];
			}else{
				$free_speech_type_dsc.= "<tw>".$row['c_dsc'];
				$free_speech_type_index.= "<tw>".$row['c_dsc'];
			}

		}
		if($row['c_user_type'] == 1){
			if($row['pc_serial']==""){
				//一開始電腦的對話
				$dsc_first = $row['c_dsc'];
				$head_type = $row['c_head_type'];
				//判斷要用哪個頭像圖片
				switch($head_type){
					case "1":
						$img_dsc='userf2.png';
					break;
					case "2":
						$img_dsc='userm1.png';
					break;
					case "3":
						$img_dsc='userm2.png';
					break;
					default:
						$img_dsc='userf1.png';
					break;

				}
				if($row['c_head_name']!=''){
				$head_name_dsc = $row['c_head_name'].':';//頭像名稱
				}
			}
		}
		//梅林
		if($row['c_user_type'] == 2 && $row['c_dsc'] !=''){
			$dsc_2 = '
			<div class="chat-1">
			<img src="images/user2.png" />
			<ul>
			<li>'.$row['c_dsc'].'</li>
			</ul>
			</div>
			';
		}

		//自動執行下一步驟
		if($row['c_user_type'] == 3 && $row['speech_del_time'] >0 ){
			if($row['c_head_name']>''){
				$setDsc .= $row['c_head_name']."：".$row['c_dsc'];
			}else{
				$setDsc=''.$row['c_dsc'];
			}
			$dsc_3 = '
			<script language="javascript">
			$( document ).ready(function() {
				setAutoRunTime("'.$row['speech_del_time'].'000","'.$row['c_dsc'].'","'.$row['c_head_type'].'","'.$setDsc.'");
			});
			</script>
			';
		}
	}

	//計算作業數量
	$sql_dsc = "select count(*) as `g_number` from `operation_data` where `main_data_num`='".$_GET['m_num']."'";
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){
		$total_mis = $row['g_number'];
	}

	//計算試題數量
	$sql_dsc = "select count(*) as `g_number` from `questions_data` where `operation_data_num`='".$operation_data_num."'";
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){
		$questions_data_number = $row['g_number'];
	}

	//取出階段說明
	$sql_dsc = "select * from `step_dsc_data` where `questions_data_num`='".$questions_data_num."' and `c_sw_type`=0";
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){
		$q_d_dsc = "說明 1：".$row['c_dsc'];
		$hideModuleBtnDsc = $row['hideModuleBtnDsc'];
		if($row['hide_module_area']==1){//是否先隱藏模組區域
			$hideModuleArea=true;
		}else{
			$hideModuleArea=false;
		}

	}

	//取出會話0的模組
	$has_module = false;//是否有使用模組，若有使用模組就必須控制系統，提示並判斷是否有點選模組的按鈕
	$img_url='';
	$warning_time=0;
	$warning_dsc="";

	$sql_dsc="select * from  `speak_usemodule_data` where `questions_data_num`='".$questions_data_num."' and `c_dsc_type`=0";
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){
		switch($row['module_type']){
			case "mathematics":
			if($row['ckedit_dsc'] !=''){
			$class_num = get_module_type($row['module_num']);
			$module_html = '
				<div class="science_'.$class_num.'">'.$row['ckedit_dsc'].'</div>
				<br>
				<div  id="show_msg_box"  class="show_msg_box" >
				'.$row['ckedit_dsc_memo'].'
				</div>
				';

			}
			if($row['module_num'] !=''){
				$class_num = get_module_type($row['module_num']);
				if($row['warning_time']>0){
					$warning_time=$row['warning_time']."000";
				}
				$warning_dsc = $row['warning_dsc'];
				$other_html = get_downButton($row['btn_dsc'],$row['pc_serial'],$row['questions_data_num'],$row['c_dsc_type']);
				$module_html = '<div class="mathematics_'.$class_num.'">'.get_mathematics_module($row['module_num'],$other_html).'</div>';
				$has_module = true;
			}
			break;
			default:
			break;

		}
	}
 



$ODb->close();

//回傳此題目的對話資料
function get_Questions_Array($getKey){
$ODb = new run_db("mysql",3306);      //建立資料庫物件
$tempArray = array();
$sql_dsc = "
select `a`.`num`,`a`.`main_data_num`,`b`.`num` as `targetNum`,`b`.`operation_data_num`
from `operation_data` as `a`
left join `questions_data` as `b` on `b`.`operation_data_num`=`a`.`num`
where `a`.`main_data_num`='".$_GET['m_num']."'
order by `a`.`num`,`targetNum`";
$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
while($row = mysql_fetch_array($res)){
	$tempArray[$row['targetNum']] = 0;
}
return json_encode($tempArray);
$ODb->close();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>合作問題解決數位學習系統</title>
<script src="./js/jquery-1.10.1.min.js"> </script>
<script src="./js/jquery-ui.js"> </script>
<script src="./js/jquery.colorbox.js"> </script>

<!-- 操作步驟、語音模組、一般通用 -->
<script type="text/javascript" src="./js_function/record.js"></script>
<script type="text/javascript" src="./js_function/speech.js"></script>
<script type="text/javascript" src="./js_function/common_use.js"></script>
<!-- 操作步驟、語音模組、一般通用 end -->

<link rel="stylesheet" href="css/media.css" /><!--倒數計時器 -->
<link rel="stylesheet" href="css/style.css" />
<link rel="stylesheet" href="css/mode.css" />
<link rel="Stylesheet" href="css/jquery-ui-1.7.1.custom.css" type="text/css" />
<script type="text/javascript"   src="http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
<script src="./js/jquery.tabSlideOut.v1.3.js"></script><!-- 左邊工具區 -->
<script src="./js/jQueryRotate.js" type="text/javascript"></script>
<!--倒數計時器 -->
<script src="./js/jquery.countdown.js"> </script>
<!--倒數計時器 end -->

<link rel="stylesheet" href="css/colorbox.css" />

<script language="javascript">
var count_total_mis = <?php echo $total_mis;?>;//總任務數量
var has_module = <?php if($has_module){ echo "true";}else{echo "false";}?>;//是否有使用模組
var d_time = 100;//物件選轉的數度，如果速度太快或太慢，請調整此值
var free_speech_type_dsc = "<?php echo $free_speech_type_dsc;?>";//開放式對話的對話字串
var free_speech_type_index= "<?php echo $free_speech_type_index;?>";//開放式對話的key字串
var c_speech_type = "<?php echo $c_speech_type;?>";//對話類型=>開放或選項
var error_time = <?php echo $warning_time;?>;//按鈕提示時間
var warning_dsc =  "<?php echo $warning_dsc;?>";//按鈕提示訊息
var ajax_obj = "get_operation_data_mathematics.php";
var m_num="<?php echo $_GET['m_num'];?>";
var now_view_type = "write";
var test_user = '<?php echo $test_user;?>';//使用者id
var test_user_type = '<?php echo $test_user_type;?>';//使用者類別
var test_begin_time='<?php echo date("Y-m-d H:i",time());?>';//測驗起始時間

if(error_time > 0 && warning_dsc !=''){
	setTimeout("warning_fun()", error_time);
}

$(function(){
$('#show_msg_box a').colorbox({width:"100%",height:"100%",iframe: true});//註冊登箱內的超連結

//倒數計時器
 $('#counter').countdown({
	image: "css/img/digits.png",
	format: "<?php if($c_test_time>9){echo 'mm';}else{echo 'm';}?>:ss",
	startTime: "<?php echo $c_test_time;?>:00",
    timerEnd: function() {
		var dsc = get_all_recordDsc();
		$.ajax({
			url: './bcontroller/js_function/update_record.php',
			data: {keyNum:"<?PHP ECHO $_GET['m_num'];?>",recordData:dsc,testUser:test_user,testUserType:test_user_type,testBeginTime:test_begin_time},
			type:"POST",
			dataType: "json",
			error: function(xhr) {
				alert('Ajax request 發生錯誤');
			},
			success: function(response) {
				alert("測試時間結束!!");
				location.replace('index.php');
			}
		});
	}
  });
});


//順時針 clockwise 或逆時針 direction
var sw_type = "clockwise";
function chg_sw(type_dsc){
var rotate_css_dsc ="";
 switch(type_dsc){
	case "clockwise"://順時針
	$('#sw_d').show();
	$('#sw_r').hide();
	sw_type = "clockwise";
	rotate_css_dsc ='url(./images/rotate_r.png), default';
	break;
	case "direction"://或逆時針
	$('#sw_d').hide();
	$('#sw_r').show();
	sw_type = "direction";
	rotate_css_dsc ='url(./images/rotate_l.png), default';
	break;
 }
}

//=============================================================================================
//切換選轉或是拖曳
var d_r_type = "d";
function chg_d_r(type_dsc){
	var d_r_css_dsc ="";
	switch(type_dsc){
		case "d":
			$('#sw_drage').show();
			$('#sw_roato').hide();
			$("#ruler").draggable("enable");
			$("#protractor").draggable("enable");
			$("#triangle").draggable("enable");
			d_r_type = "d";
		break;
		case "r"://取消拖曳改成可以旋轉
			$('#sw_drage').hide();
			$('#sw_roato').show();
			$("#ruler").draggable({ disabled: true });
			$("#protractor").draggable({ disabled: true });
			$("#triangle").draggable({ disabled: true });
			d_r_type = "r";
		break;
	}
}

//直尺=>選轉
var ration_val=0;
function ruler_rotation() {
if(d_r_type == "r"){
	var get_obj_id = "ruler";
	var index_dsc = ration_val;
		if (!mouseStillDown) {
			return;
		} // we could have come back from
		if(sw_type =="clockwise"){
			ration_val = ration_val + 1;
			if(ration_val>360){
				ration_val = 0;
			}
		}else{
			ration_val = ration_val - 1;
			if(ration_val < -360){
				ration_val = 0;
			}
		}
		$("#ruler").rotate(
		{
			angle: (ration_val),
			center: ["0%", "50%"]
		});
		set_record("mathematics||m_one||ruler||rotate||"+ration_val);//紀錄直尺選轉角度

		if (mouseStillDown) {
			setTimeout("ruler_rotation()", d_time);
		}
	}
}

//量角器=>選轉
var protractor_rotaion=0;//量角器的旋轉角度
function protractor_rotation() {
	if(d_r_type == "r"){
		var get_obj_id = "protractor";
		var index_dsc = protractor_rotaion;
		if (!mouseStillDown) {
			return;
		} // we could have come back from
		if(sw_type =="clockwise"){
			protractor_rotaion = protractor_rotaion + 1;
			if(protractor_rotaion>360){
				protractor_rotaion = 0;
			}
		}else{
			protractor_rotaion = protractor_rotaion - 1;
			if(protractor_rotaion < -360){
				protractor_rotaion = 0;
			}
		}

		$("#protractor").rotate(
		{
			angle: (protractor_rotaion),
			center: ["150px", "150px"]
		});
		set_record("mathematics||m_one||protractor||rotate||"+protractor_rotaion);//紀錄量角器選轉角度

		if (mouseStillDown) {
			setTimeout("protractor_rotation()", d_time);
		}
	}
}


//三角尺=>選轉
var triangle_rotaion=0;//三角尺的旋轉角度
function triangle_rotation() {
	if(d_r_type == "r"){
		var get_obj_id = "triangle";
		var index_dsc = protractor_rotaion;
		if (!mouseStillDown) {
			return;
		} // we could have come back from
		if(sw_type =="clockwise"){
			protractor_rotaion = protractor_rotaion + 1;
			if(protractor_rotaion>360){
				protractor_rotaion = 0;
			}
		}else{
			protractor_rotaion = protractor_rotaion - 1;
			if(protractor_rotaion < -360){
				protractor_rotaion = 0;
			}
		}

		$("#triangle").rotate(
		{
			angle: (triangle_rotaion),
			center: ["150px", "150px"]
		});
		set_record("mathematics||m_one||triangle||rotate||"+triangle_rotaion);//紀錄三角尺旋轉角度

		if (mouseStillDown) {
			setTimeout("triangle_rotation()", d_time);
		}
	}
}

//=================================================================
//初始化直尺、三角板、圓規
function default_tools(obj_id){
	if ($('#'+obj_id).css('display') == 'none') {
		//物件目前為隱藏狀態=>顯示物件
		show_set_tools(obj_id);
		set_record("mathematics||m_one||obj||show_set_tools||"+obj_id);//顯示物件
	}else{
		//物件目前為顯示狀態=>隱藏物件
		hide_set_tools(obj_id);
		set_record("mathematics||m_one||obj||hide_set_tools||"+obj_id);//顯示物件

	}
}

function show_set_tools(obj_id){
	$("#"+obj_id).show();
	var offset = $(document).scrollTop();
	var viewportWidth = jQuery(window).width(),viewportHeight = jQuery(window).height();
	$("#"+obj_id).offset({ top:(offset+(viewportHeight/2)) , left:((viewportWidth/2)-$("#"+obj_id).width()) });
}

function hide_set_tools(obj_id){
	$("#"+obj_id).offset({ top:"" , left:"" });
	$("#"+obj_id).hide();
}

//===============================================================================
//設定繪圖狀態 0=>畫圖,1=>橡皮擦
function set_pen_type(getKey){
	switch(getKey){
	case 0:
	$('#sw_paint').show();
	$('#sw_clear').hide();
	$('#upCanvas').css("cursor","url(images/icon_pen.png),default");
	break;
	case 1:
	$('#sw_paint').hide();
	$('#sw_clear').show();
	$('#upCanvas').css("cursor","url(images/icon_eraser.png),default");

	break;
	}
	pen_type = getKey;
}

//顯示提示視窗
function show_msg_box(){
$('#show_msg_box').dialog({
	height: 800,
	width: 600
});
}

$( document ).ready(function() {
<?php if($hideModuleArea){echo "hideModuleArea = true;setHideModuleArea();";}?>

});

</script>
<style>
.tools-panel {
	background: #f1f1f1;
	border: 1px solid #dddddd;
	position:fixed;
	top:80px;
	right:-475px;
	min-height:10px;
}

.tools-panel table.options{
	clear:both;
	position:relative;
	top:-140px;
	padding:0;
	margin:0;
}

.tools-panel table td.tool_option{
	text-align:center;
	vertical-align:middle;
	width:95px;
	height:60px;
	background:url(./images/tool_bg.png) no-repeat;
}

.tools-panel table td.tool_option_none{
	text-align:center;
	vertical-align:middle;
	width:95px;
	height:60px;
	background:url(./images/tool_none.png) no-repeat;
}

.tools-panel table td.tool_option_none a{
	cursor:default;
}

.tools-panel table td.tool_option:hover, .tools-panel table td.tool_option:active{
	background:url(./images/tool_bg_hover.png) no-repeat;
}

.tools-panel a.tools-tab {
	background:url(./images/slide_tools.png) no-repeat;
	display:block;
	height:140px;
	width:35px;
	right:32px;
	bottom:0px;
	position:relative;
	float:left;
	text-indent:-9999px;
}

.tools_info{
	text-align:center;
}

.tools_info > div{
	display:block;
	padding:0 10px;
	height:480px;
	width:260px;
	overflow-x:hidden;
	overflow-y:auto;
}

/*尺*/
.ruler{
	cursor: move;
	background:url(./images/ruler.png) no-repeat;
	width:320px;
	height:69px;
	margin:10px auto;
	position:absolute;
}

/*量角器*/
.protractor{
	cursor: move;
	background:url(./images/protractor.png) no-repeat;
	width:300px;
	height:168px;
	margin:10px auto;
	position:absolute;

}

.protractor_l{
	float:left;
	width:20%;
	cursor:move;
}

.protractor_r{
	float:left;
	width:20%;
	cursor:move;
}

.protractor_m{
	float:left;
	width:60%;
	cursor:default;
}

.protractor_m .focus{
	height:100px;
	cursor:url(./images/rotate_r.png), default;
}


/*三角板*/
.triangle{
	clear:both;
	cursor: move;
	background:url(./images/triangle.png) no-repeat;
	width:320px;
	height:175px;
	margin:10px auto;
	position:absolute;
}

/*旋轉切換*/
.switch{
	margin:20px auto;
	padding:0 15px;
	width:210px;
	height:40px;
	line-height:40px;
	background:#f1f1f1;
	font-size:20px;
	color:#666666;
	-moz-border-radius:8px;
	-webkit-border-radius:8px;
	border-radius:8px;
	border:1px solid #dcdcdc;
	background:-moz-linear-gradient( center top, #f9f9f9 5%, #e9e9e9 100% );
	background:-ms-linear-gradient( top, #f9f9f9 5%, #e9e9e9 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#f9f9f9', endColorstr='#e9e9e9');
	background:-webkit-gradient( linear, left top, left bottom, color-stop(5%, #f9f9f9), color-stop(100%, #e9e9e9) );
	background-color:#f9f9f9;
	text-shadow:1px 1px 0px #ffffff;
 	-webkit-box-shadow:inset 1px 1px 0px 0px #ffffff;
 	-moz-box-shadow:inset 1px 1px 0px 0px #ffffff;
 	box-shadow:inset 1px 1px 0px 0px #ffffff;
}

.switch li{
	display:block;
	margin:0 10px;
	color:#666;
	cursor:pointer;
}

.switch img{
	margin:0 2px -7px 2px;
}


</style>
</head>
<body>
<table width="100">
<col width="25">
<col width="75">
  <tr>
  	<!--標頭-->
    <th colspan="2">
    	<div class="th_left">
            <h1>單元名稱：<?php echo $main_dsc;?></h1>
            <div class="mission">
                <h4 id="total_mis_dsc"><?php echo "任務 1/".$total_mis;?> &gt;</h4>
                <p id="mission_dsc"><?php echo $op_dsc;?></p>
            </div>
        </div>

        <!--倒數計時器-->
        <ul class="th_right">
            <li><div id="counter"></div></li>
        </ul>
    </th>
    <!--標頭end-->
  </tr>
  <tr>
  	<!--交談對話框-->
    <td class="talk" >

<?php if($c_speech_type =='free'){?>
<div class="speech">
	<input id="speech_data" type="text" size="15" value=""><br>
	<input id="startStopButton" type="button" value="辨識" onclick="startButton(event)">
	<input type="button" value="輸入" onclick="insert_value()"><br><label id="infoBox"></label>
</div>
<?php }?>
<h3>交談</h3>
        <div id="talk_area">
        <div id="talk_area_point"></div>
		<?php if($dsc_first !=''){?>
			<!--對話 robot-->
            <div class="chat-1" id="robot_dsc">

	            <IFRAME src="robot_img.php?dsc=<?php echo $dsc_first;?>&head_type=<?php echo $head_type;?>&setSound=<?php echo (isset($use_sound) and $use_sound == 'yes')?'yes':'no';?>" width="160"  scrolling="no" frameborder="0" style="display: none;"></IFRAME><!--大頭貼-->

				<img src="./images/<?php echo $img_dsc;?>">	<!-- 電腦對話頭像暫時關閉語音功能改成img頭像	-->
				<ul>
                    <li onclick="replace_pc_speech('<?php echo $dsc_first;?>','<?php echo $head_type;?>')"><?php echo $head_name_dsc.$dsc_first;?></li><!--對話內容-->
                </ul>
            </div>
            <!--對話 robot end-->
		<?php }	?>
		<?php if($c_speech_type !='free'){?>
        	<!--對話-->
            <div class="chat">
                <!--大頭貼-->
                <img src="images/user.png" />
                <!--對話內容-->
                <ul>
                    <?php echo $dsc_0;?>
                </ul>
                <!--對話內容end-->
            </div>
            <!--對話end-->
		<?php }	?>
			<?php echo $dsc_2;//梅林?>
        </div>
    </td>
  	<!--交談對話框end-->
  	<!--右側操作內容-->
    <td class="main" width="80%" >
    	<!--會話階段顯示-->
        <ul class="steps">
		<?php
			for($x=0,$y=1;$x<$op_num;$x++,$y++){
				if($x==0){
					$dsc = 'class="done"';
				}else{
					$dsc ='';
				}
				echo '<li '.$dsc.' id="steps_'.$x.'"><span>'.$y.'</span></li>';
			}
		?>
        </ul>
    	<!--會話階段顯示end-->

        <!--操作區-->
        <div class="operate" >
            <section>
				<h2 id="step_dsc"><?php echo $q_d_dsc;?></h2>
				<div class="button_tips" id="show_module_area_btn" onclick="show_module_area()" title="<?php echo $hideModuleBtnDsc;?>" style="<?php if(!$hideModuleArea){echo "display:none;";}?>" ><?php echo $hideModuleBtnDsc;?></div>
				<div id="module_area" style="<?php if($hideModuleArea){echo "display:none;";}?>" ><!-- 模組出題區域-->
					<!--text-->
				<?php echo $module_html;?>
				</div>
            </section>
        </div>
        <!--操作區end1-->

    </td>
  </tr>
</table>
<!-- 圓規區 -->
<div style="background-image:url(temp_img/close_0.png);height:196px;width:27px;display:none;" id="canDrag">
	<div style="height:19px;width:27px;" class="canBody"></div><!-- 頭 -->
	<div style="height:165px;width:27px;" onclick="open_close()" class="canBody"></div><!-- 收和 -->
	<div style="height:12px;width:27px;" onclick="tool_drag_round()" class="canBody" id="tool_dragArea">
	</div><!-- 繪圖 -->
</div>
<!-- 圓規區 end -->
<div  style="display:none;">
	<IFRAME src="" width="160"  scrolling="no" frameborder="0" id="replacepcspeech"></IFRAME>
</div>
<input type="hidden" id="getPowerKey" value=""><?php  //radio選擇到的key值，for計算能力值用 ?>

</body>
</html>
