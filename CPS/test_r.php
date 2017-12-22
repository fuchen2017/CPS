<?php
	session_start();
	//包含需求檔案 ------------------------------------------------------------------------
	include("./bcontroller/class/common_lite.php");
	include("./bcontroller/module_function/read.php");//科學模組
	include("./bcontroller/module_function/common_use.php");//通用模組

	$head_name_dsc = '';
	$module_html = '';
	$dsc_0 ='';
	$dsc_1 ='';
	$dsc_2 ='';
	$dsc_3 ='';
	
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
		select * from `operation_data` where `main_data_num`='".$_GET['m_num']."'  order by `num` limit 1";
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
	select `a`.`num`,`a`.`main_data_num`,`a`.`c_title` as `op_dsc`,`b`.`num`,`b`.`c_title` as `main_dsc`,`b`.`c_test_time`,`b`.`c_speech_type`  
	from `operation_data` as `a` 
	left join `main_data` as `b` on `b`.`num`=`a`.`main_data_num`  
	where `a`.`num`='".$operation_data_num."'";
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){
		$op_dsc = $row['op_dsc'];//作業名稱
		$main_dsc = $row['main_dsc'];//單元名稱
		$c_test_time = $row['c_test_time'];//測驗時間
		$c_speech_type = $row['c_speech_type'];//對話類型
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
		//使用者對話
		if($row['c_user_type'] == 0){
			$dsc_0.= "<li><input type='radio' subid='talk_radio' name='speech_radio' id='speech_radio_".$num."' onclick=\"get_last_pcmsg('".$row['num']."');set_record('radio||speech_radio_".$num."');set_getPowerKey(".$row['num'].")\" value='".$row['num']."' ><label for='speech_radio_".$num."'>".$row['c_dsc']."</label></li>";
			$num++;
			if($free_speech_type_dsc=="" && $free_speech_type_index==""){
				$free_speech_type_dsc.= $row['c_dsc'];
				$free_speech_type_index.= $row['num'];
			}else{
				$free_speech_type_dsc.= "<tw>".$row['c_dsc'];
				$free_speech_type_index.= "<tw>".$row['num'];
			}
		}
		
		//一開始電腦的對話
		if($row['c_user_type'] == 1){
			if($row['pc_serial']==""){				
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
		$setDsc = '';
		if($row['c_user_type'] == 3 && $row['speech_del_time'] >0 ){
			if($row['c_head_name']>''){
				$setDsc .= $row['c_head_name']."：".$row['c_dsc'];
			}else{
				$setDsc=''.$row['c_dsc'];
			}
			$dsc_3 = '
			<script language="javascript">
			$( document ).ready(function() {
				setAutoRunTime("'.$row['speech_del_time'].'000","'.$row['c_dsc'].'","'.$row['c_head_type'].'","'.$setDsc.'","'.$row['num'].'");
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
			case "read":

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
				$module_html = '<div class="read_'.$class_num.'">'.get_read_module($row['module_num']).$other_html.'</div>';
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
<!--倒數計時器 -->
<script src="./js/jquery.countdown.js"> </script>
<!--倒數計時器 end -->
<!-- 操作步驟、語音模組、一般通用 -->
<script type="text/javascript" src="./js_function/record.js"></script> 
<script type="text/javascript" src="./js_function/speech.js"></script> 
<script type="text/javascript" src="./js_function/common_use.js"></script> 
<!-- 操作步驟、語音模組、一般通用 end -->

<link rel="stylesheet" href="css/style.css" />
<link rel="stylesheet" href="css/mode.css" />
<link rel="stylesheet" href="css/jquery-ui-1.7.1.custom.css" />
<link rel="stylesheet" href="css/colorbox.css" />
<link rel="stylesheet" href="css/media.css" /><!--倒數計時器 -->
<script src="./js/jquery.tabSlideOut.v1.3.js"></script><!-- 左邊工具區 -->


<script language="javascript">
var count_total_mis = <?php echo $total_mis;?>;//總任務數量
var has_module = <?php if($has_module){ echo "true";}else{echo "false";}?>;//是否有使用模組
var free_speech_type_dsc = "<?php echo $free_speech_type_dsc;?>";//開放式對話的對話字串
var free_speech_type_index= "<?php echo $free_speech_type_index;?>";//開放式對話的key字串
var c_speech_type = "<?php echo $c_speech_type;?>";//對話類型=>開放或選項
var error_time = <?php echo $warning_time;?>;//按鈕提示時間
var warning_dsc =  "<?php echo $warning_dsc;?>";//按鈕提示訊息
var ajax_obj = "get_operation_data_read.php";
var m_num="<?php echo $_GET['m_num'];?>";
var now_view_type = "write";
var test_user = '<?php echo $test_user;?>';//使用者id
var test_user_type = '<?php echo $test_user_type;?>';//使用者類別
var test_begin_time='<?php echo date("Y-m-d H:i",time());?>';//測驗起始時間


if(error_time > 0 && warning_dsc !=''){
	setTimeout("warning_fun()", error_time);
}	
/*
//使用者選擇對話選項後，取得對應的動作
function sw_dsc(getkeynum){
if(!has_module){//如果有使用模組時，使用者對話選項不動作
	can_insert_value=true;
	error_time =0;
	warning_dsc="";
	$('#warning_area').hide();
	$("#talk_area :input[subid='talk_radio']").removeAttr('id').removeAttr("name").attr("disabled","disabled");
	$.ajax({
			url: './bcontroller/js_function/'+ajax_obj,
			data: {keyNum:getkeynum,speechtype:c_speech_type},
			dataType: "json",
			error: function(xhr) {
				alert('Ajax request 發生錯誤');
			},
			success: function(response) {	
				switch(response['type_dsc']){
				case "talk2"://會話2
				case "talk3"://會話3
					if(response['module_dsc']!=''){
						$('#module_area').html("");
						$('#module_area').html(response['module_dsc']);
					}
					$('#steps_'+steps_index).attr("class","done");
					$('#talk_area_point').after(response['re_code']);
					$('#step_dsc').html("Step "+steps_index+"："+response['step_dsc']);
					if(response['warning_time']>0){error_time =response['warning_time']; }
					if(response['warning_dsc'] !=''){warning_dsc =response['warning_dsc'];}
					if(error_time > 0 && warning_dsc !=''){
					setTimeout("warning_fun()", response['warning_dsc']);
					}	
					has_module = response['has_module'];					
					steps_index++;
				break;
				case "next_q"://下一個試題
					$('#module_area').html("");
					if(response['module_dsc']!='' && response['module_dsc']!= null ){
						$('#module_area').html(response['module_dsc']);
					}		
					$('#mission_dsc').html(response['mission_dsc']);//任務敘述
					$('#talk_area').html("").append('<div id="talk_area_point"></div>');
					$('#talk_area_point').after(response['re_code']);
					$('#step_dsc').html("Step 1："+response['step_dsc']);	
					if(response['warning_time']>0){error_time =response['warning_time']; }
					if(response['warning_dsc'] !=''){warning_dsc =response['warning_dsc'];}
					if(error_time > 0 && warning_dsc !=''){
					setTimeout("warning_fun()", response['warning_dsc']);
					}					
					has_module = response['has_module'];
					reset_steps();
				break;
				case "next_w"://下一個作業
					$('#module_area').html("");
					if(response['module_dsc']!='' && response['module_dsc']!= null ){
						$('#module_area').html(response['module_dsc']);
					}		
					$('#mission_dsc').html(response['mission_dsc']);//任務敘述
					$('#talk_area').html("").append('<div id="talk_area_point"></div>');
					$('#talk_area_point').after(response['re_code']);
					$('#step_dsc').html("Step 1："+response['step_dsc']);
					$("#total_mis_dsc").html("任務 "+total_mis+"/<?php echo $total_mis;?>");
					if(response['warning_time']>0){error_time =response['warning_time']; }
					if(response['warning_dsc'] !=''){warning_dsc =response['warning_dsc'];}
					if(error_time > 0 && warning_dsc !=''){
					setTimeout("warning_fun()", response['warning_dsc']);
					}					
					has_module = response['has_module'];					
					total_mis++;	
					reset_steps();				
				break;
				case "end"://結束
				var dsc = get_all_recordDsc();
					$.ajax({
						url: './bcontroller/js_function/update_record.php',
						data: {keyNum:"<?PHP ECHO $_GET['m_num'];?>",recordData:dsc,testUser:test_user,testUserType:test_user_type,testBeginTime:test_begin_time},
						type:"POST",
						dataType: "json",
						error: function(xhr) {
							//console.log(xhr);
							alert('Ajax request 發生錯誤');
						},
						success: function(response) {
							alert("單元結束!!");
							location.replace('index.php');
						}
					});
				break;				
				}
			}
		});
	}
}


//for閱讀模組用=>須按下按鈕並檢查所有的radio是否有填寫
function sw_dsc_for_read(getkeynum,pc_serial){
/*
先檢查是否有選擇對話
var sw_num = $("#talk_area :input[subid='talk_radio']:checked:not(:disabled)").length;
if(sw_num==1 || c_speech_type=="free"){
	can_insert_value=true;
	error_time =0;
	warning_dsc="";
	$('#warning_area').hide();
	$("#talk_area :input[subid='talk_radio']").removeAttr('id').removeAttr("name").attr("disabled","disabled");
	$.ajax({
			url: './bcontroller/js_function/get_operation_data_read.php',
			data: {keyNum:getkeynum,pcSerial:pc_serial,speechtype:c_speech_type},
			dataType: "json",
			error: function(xhr) {
				alert('Ajax request 發生錯誤');
			},
			success: function(response) {
				switch(response['type_dsc']){
				case "talk2"://會話2
				case "talk3"://會話3
					if(response['module_dsc']!=''){
						$('#module_area').html("");
						$('#module_area').html(response['module_dsc']);
					}
					$('#steps_'+steps_index).attr("class","done");
					$('#talk_area_point').before(response['re_code']);
					$('#step_dsc').html("Step "+steps_index+"："+response['step_dsc']);
					if(response['warning_time']>0){error_time =response['warning_time']; }
					if(response['warning_dsc'] !=''){warning_dsc =response['warning_dsc'];}
					if(error_time > 0 && warning_dsc !=''){
					setTimeout("warning_fun()", response['warning_dsc']);
					}	
					has_module = response['has_module'];					
					steps_index++;
				break;
				case "next_q"://下一個試題
					$('#module_area').html("");
					if(response['module_dsc']!='' && response['module_dsc']!= null ){
						$('#module_area').html(response['module_dsc']);
					}		
					$('#mission_dsc').html(response['mission_dsc']);//任務敘述
					$('#talk_area').html("").append('<div id="talk_area_point"></div>');
					$('#talk_area_point').after(response['re_code']);
					$('#step_dsc').html("Step 1："+response['step_dsc']);	
					if(response['warning_time']>0){error_time =response['warning_time']; }
					if(response['warning_dsc'] !=''){warning_dsc =response['warning_dsc'];}
					reset_steps();
					if(error_time > 0 && warning_dsc !=''){
					setTimeout("warning_fun()", response['warning_dsc']);
					}
					has_module = response['has_module'];
				break;
				case "next_w"://下一個作業
					$('#module_area').html("");
					if(response['module_dsc']!='' && response['module_dsc']!= null ){
						$('#module_area').html(response['module_dsc']);
					}		
					$('#mission_dsc').html(response['mission_dsc']);//任務敘述
					$('#talk_area').html("").append('<div id="talk_area_point"></div>');
					$('#talk_area_point').after(response['re_code']);
					$('#step_dsc').html("Step 1："+response['step_dsc']);
					$("#total_mis_dsc").html("任務 "+total_mis+"/<?php echo $total_mis;?>");
					if(response['warning_time']>0){error_time =response['warning_time']; }
					if(response['warning_dsc'] !=''){warning_dsc =response['warning_dsc'];}					
					total_mis++;		
					reset_steps();	
					if(error_time > 0 && warning_dsc !=''){
					setTimeout("warning_fun()", response['warning_dsc']);
					}	
					has_module = response['has_module'];
					
				break;
				case "end"://結束
				var dsc = get_all_recordDsc();
					$.ajax({
						url: './bcontroller/js_function/update_record.php',
						data: {keyNum:"<?PHP ECHO $_GET['m_num'];?>",recordData:dsc,testUser:test_user,testUserType:test_user_type,testBeginTime:test_begin_time},
						type:"POST",
						dataType: "json",
						error: function(xhr) {
							//console.log(xhr);
							alert('Ajax request 發生錯誤');
						},
						success: function(response) {
							alert("單元結束!!");
							location.replace('index.php');
						}
					});
				break;				
				}
			}
		});
	}else{
	alert('請選擇對話！！');
	}		
}

//當使用者選擇對話後，先調出對應電腦的對話，然後根據延遲時間在取得下一個對話
function get_last_pcmsg(getkeynum){
if(!has_module){//如果有使用模組時，使用者對話選項不動作
	$.ajax({
		url: './bcontroller/js_function/get_last_pcmsg.php',
		data: {keyNum:getkeynum},
		dataType: "json",
		error: function(xhr) {
			alert('Ajax request 發生錯誤');
		},
		success: function(response) {
			if(response['speech_del_time']>0){
				$('#talk_area_point').after(response['pc_html']);
				setTimeout("sw_dsc("+response['keyNum']+")", response['speech_del_time']);
			}else{
				sw_dsc(response['keyNum']);				
			}
		}
	});
}
}

//當使用者選擇對話後，先調出對應電腦的對話，然後根據延遲時間在取得下一個對話
function get_lastmodule_pcmsg(getkeynum,pc_serial){
/*
先檢查是否有選擇對話
var sw_num = $('#talk_area :input[subid="talk_radio"]:checked:not(:disabled)').length;
	if(sw_num==1 || c_speech_type=="free"){
		$.ajax({
			url: './bcontroller/js_function/get_last_pcmsg.php',
			data: {keyNum:getkeynum,pcSerial:pc_serial},
			dataType: "json",
			error: function(xhr) {
				alert('Ajax request 發生錯誤');
			},
			success: function(response) {
				if(response['speech_del_time']>0){
					$('#talk_area_point').after(response['pc_html']);
					setTimeout("sw_dsc_for_read("+response['keyNum']+",'"+response['pcSerial']+"')", response['speech_del_time']);
					
				}else{				
					sw_dsc_for_read(response['keyNum'],response['pcSerial']);				
				}
			}
		});
	}else{
	alert('請選擇對話！！');
	}		
}
*/
var error_time = <?php echo $warning_time;?>;
var warning_dsc =  "<?php echo $warning_dsc;?>";
if(error_time > 0 && warning_dsc !=''){
	setTimeout("warning_fun()", error_time);
}	


//顯示提示視窗
function show_msg_box(){
$('#show_msg_box').dialog({
	height: 800,
	width: 600	
});
}

$(function () {
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
				//console.log(xhr);
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

$( document ).ready(function() {
<?php if($hideModuleArea){echo "hideModuleArea = true;setHideModuleArea();";}?>

});

</script>
</head>
<body>
<table width="100">
<col width="30">
<col width="70">
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
        </ul>    </th>
    <!--標頭end-->
  </tr>
 
  <tr>
  	<!--交談對話框-->
    <td class="talk" >
    	

<h3>交談</h3>
        <div id="talk_area">
        <div id="talk_area_point"></div>
		<?php if($dsc_first !=''){?>
			<!--對話 robot-->
            <div class="chat-1" id="robot_dsc">                
			<!-- <IFRAME src="robot_img.php?dsc=<?php echo $dsc_first;?>&head_type=<?php echo $head_type;?>" width="160"  scrolling="no" frameborder="0"></IFRAME><!--大頭貼-->             
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
			<?php echo $dsc_3;//自動進行下一步驟?>        </div> 
  <?php if($c_speech_type =='free'){?>		
<div class="speech">
	<textarea id="speech_data" type="text" cols="40" rows="6" value=""></textarea>
	<br>
	<input id="startStopButton" type="hidden" value="辨識" onclick="startButton(event)" >
	<input type="button" value="輸入" onclick="insert_value()">
	<?php }?>
	<br>
	<label id="infoBox"></label>
</div>  </td>
  	<!--交談對話框end-->      
  	<!--右側操作內容-->
    <td class="main" width="80%" >
    	<!--作業數量顯示-->
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
    	<!--作業數量顯示end-->

        <!--操作區-->
        <div class="operate" >
            <section>		
				<h2 id="step_dsc"><?php echo $q_d_dsc;?></h2>
				<div class="button_tips" id="show_module_area_btn" onclick="show_module_area()" title="<?php echo $hideModuleBtnDsc;?>" style="<?php if(!$hideModuleArea){echo "display:none;";}?>" ><?php echo $hideModuleBtnDsc;?></div>
				<div id="module_area" style="<?php if($hideModuleArea){echo "display:none;";}?>" ><!-- 模組出題區域-->
				<?php echo $module_html;?>				</div>			
            </section>
        </div>
        <!--操作區end-->    </td>
  </tr>
</table>
<div  style="display:none;">
	<IFRAME src="" width="160"  scrolling="no" frameborder="0" id="replacepcspeech"></IFRAME>
</div>
<input type="hidden" id="getPowerKey" value=""><?php  //radio選擇到的key值，for計算能力值用 ?>
</body>
</html>
