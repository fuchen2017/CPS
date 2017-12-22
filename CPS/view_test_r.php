<?php
	session_start();
	//包含需求檔案 ------------------------------------------------------------------------
	include("./bcontroller/class/common_lite.php");
	include("./bcontroller/module_function/read.php");//閱讀模組
	include("./bcontroller/module_function/common_use.php");//通用模組
	
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
	$_SESSION['questionsPowerValue'] = get_Questions_Array($_GET['m_num']);//初始化每組對話能力資料
		
		$sql_dsc = "
		select * from `operation_data` where `main_data_num`='".$_GET['m_num']."' order by `num` limit 1";
		$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
		if(mysql_num_rows($res)>0){
			while($row = mysql_fetch_array($res)){
				$operation_data_num = $row['num'];
			}
		}else{
			ri_jump("login.php");	
		}
	}else{
		ri_jump("login.php");	
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
		if($row['c_user_type'] == 0){//使用者對話
			$dsc_0.= "<li><input type='radio' subid='talk_radio' onclick=\"get_last_pcmsg('".$row['num']."')\" id='speech_radio_".$num."' value='".$row['num']."' name='speech_radio' ><label for='radio_".$num."'>".$row['c_dsc']."</label></li>";
			$num++;
			if($free_speech_type_dsc=="" && $free_speech_type_index==""){
				$free_speech_type_dsc.= $row['c_dsc'];
				$free_speech_type_index.= $row['num'];
			}else{
				$free_speech_type_dsc.= "<tw>".$row['c_dsc'];
				$free_speech_type_index.= "<tw>".$row['num'];
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
	$sql_dsc = "select `c_dsc` from `step_dsc_data` where `questions_data_num`='".$questions_data_num."' and `c_sw_type`=0";
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

			if($row['ckedit_dsc'] !=''){$module_html = $row['ckedit_dsc'];}
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
	//取出紀錄
	$sql_dsc="select `record_value` from  `opt_record` where `main_data_num`='".$_GET['m_num']."' and `num`='".$_GET['num']."'";
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){	
	$record_value_array = explode('<tw>',$row['record_value']);
	
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
<script src="./js/jquery.tabSlideOut.v1.3.js"></script><!-- 左邊工具區 -->


<link rel="stylesheet" href="css/style.css" />
<link rel="stylesheet" href="css/mode.css" />
<link rel="stylesheet" href="css/jquery-ui-1.7.1.custom.css" />
<link rel="stylesheet" href="css/colorbox.css" />
<link rel="stylesheet" href="css/media.css" /><!--倒數計時器 -->


<script language="javascript">
var count_total_mis = <?php echo $total_mis;?>;//總任務數量

<?php 
if(is_array($record_value_array)){
	foreach($record_value_array as $value){
echo '
record_array.push("'.$value.'");
';
	}
}
?>
var has_module = <?php if($has_module){ echo "true";}else{echo "false";}?>;//是否有使用模組
var free_speech_type_dsc = "<?php echo $free_speech_type_dsc;?>";//開放式對話的對話字串
var free_speech_type_index= "<?php echo $free_speech_type_index;?>";//開放式對話的key字串
var c_speech_type = "<?php echo $c_speech_type;?>";//對話類型=>開放或選項
var ajax_obj = "get_operation_data_read.php";
var m_num="<?php echo $_GET['m_num'];?>";
var error_time = 0;
var warning_dsc =  "";
var now_view_type = "view";


//顯示提示視窗
function show_msg_box(){
$('#show_msg_box').dialog({
	height: 800,
	width: 600	
});
}

$(function () {
$('#show_msg_box a').colorbox({width:"100%",height:"100%",iframe: true});

});

$( document ).ready(function() {
	<?php 
	if($hideModuleArea){
		echo "hideModuleArea = true;setHideModuleArea();";
	}
	?>
    play_record();
});


</script>
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
               <!-- <IFRAME src="robot_img.php?dsc=<?php echo $dsc_first;?>&head_type=<?php echo $head_type;?>" width="160"  scrolling="no" frameborder="0"></IFRAME><!--大頭貼-->             
				<img src="./images/<?php echo $img_dsc;?>">	<!-- 電腦對話頭像暫時關閉語音功能改成img頭像	-->
				<ul>
                    <li><?php echo $dsc_first;?></li><!--對話內容-->
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
				<?php echo $module_html;?>	
				</div>			
            </section>
        </div>
        <!--操作區end-->

    </td>
  </tr>
</table>
<input type="hidden" id="tempIndexKey" value="<?php echo $_GET['num'];?>">

</body>
</html>
