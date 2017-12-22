<?php
	session_start();
  //包含需求檔案 ------------------------------------------------------------------------
	include("./class/common_lite.php");
	if($_SESSION['loginType'] == ''){
		ri_jump("logout.php");
	}else{
		switch($_SESSION['loginType']){
			case "ADMIN":
				$menu_array = array(
				'url' => 'member_list.php',
				'dsc' => '成員管理',
				'imgurl' => 'images/icon_user.png'
				);
				$showMenu = true;
			break;
			default:
			ri_jump("logout.php");
			break;		
		}	
	}
	
	//宣告變數 ----------------------------------------------------------------------------
	$ODb = new run_db("mysql",3306);      //建立資料庫物件
	if($_POST['send_data']=='has_post_value' ){
		//新增題目
		$nowdate =  date("Y-m-d H:i",time());		
		$up_dsc ="insert into `test_time_list`
		set 
		`c_title`='".$_POST['c_title']."',
		`begin_time`='".$_POST['begin_time']."',
		`end_time`='".$_POST['end_time']."',
		`up_date`='".$nowdate."'";
		$res=$ODb->query($up_dsc) or die("更新資料出錯，請聯繫管理員。");
		$getID = mysql_insert_id();
		
		//新增試題清單
		$sql2='';
		for($x=0;$x<$_POST['topicIndex'];$x++){
			if(isset($_POST['topicSwitch'.$x])){
				$tmpstr = "'". $getID ."','". $_POST['topicOrder'.$x] ."','". $_POST['topicSwitch'.$x] ."','". $nowdate ."'";
				$sql2 .= "(".$tmpstr."),";			
			}
		}
		
		if($sql2>''){
			$sql2 = substr($sql2,0,-1);
			$up_dsc="INSERT INTO `test_time_topic` (`f_num`,`c_order`,`main_data_num`,`up_date`) VALUES ".$sql2;
			$res=$ODb->query($up_dsc) or die("更新資料出錯，請聯繫管理員。");
		}
		//新增老師清單
		$sql2='';
		for($x=0;$x<$_POST['teacherIndex'];$x++){
			if(isset($_POST['teacherSwitch'.$x])){
				for($y=0;$y<$_POST['topicIndex'];$y++){
				if(isset($_POST['topicSwitch'.$x])){
//先調出該老師應該有的班級
$sql_dsc = "SELECT * FROM  `studentdata` 
WHERE  `teacherdataNum` ='".$_POST['teacherSwitch'.$x]."' 
GROUP BY  `grade_dsc` ,`class_dsc`";
$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
while($row = mysql_fetch_array($res)){
$tmpstr = "'". $getID ."','". $_POST['topicSwitch'.$y] ."','". $_POST['teacherSwitch'.$x] ."','".$row['grade_dsc']."','".$row['class_dsc']."','".$_POST['begin_time']."','".$_POST['end_time']."','". $nowdate ."'";
$sql2 .= "(".$tmpstr."),";			
}					
					}
				}
			}
		}
		
		if($sql2>''){
			$sql2 = substr($sql2,0,-1);
			$up_dsc="INSERT INTO `test_time_teacher` (`f_num`,`main_data_num`,`teacherdataNum`,`grade_dsc`,`class_dsc`,`begin_time`,`end_time`,`up_date`) VALUES ".$sql2;
			$res=$ODb->query($up_dsc) or die("更新資料出錯，請聯繫管理員。");
		}
		
		
		ri_jump("testtime_list.php");
	}	
	
	//所有試題資料
		$sql_dsc = "select * from `main_data` order by `num`";
		$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");		
		while($row = mysql_fetch_array($res)){
			$mainData['num'] = $row['num'];
			$mainData['c_title'] = $row['c_title'];
			$mainDataArray[] = $mainData;
		}
		//所有老師資料
		$sql_dsc = "select * from `teacherdata` order by `num`";
		$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");		
		while($row = mysql_fetch_array($res)){
			$teacherData['num'] = $row['num'];
			$teacherData['c_name'] = $row['c_name'];
			$teacherDataArray[] = $teacherData;
		}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>合作問題解決數位學習系統</title>
<link rel="stylesheet" href="css/admin.css" />
<link rel="stylesheet" href="css/normalize.css" />
<link rel="stylesheet" href="css/jquery-ui.css" />
<link rel="stylesheet" href="css/colorbox.css" />

<script type="text/javascript" src="./js/jquery-1.10.1.min.js"> </script>
<script type="text/javascript" src="./js/javascript.js"></script><!-- 頁面收和 -->
<script type="text/javascript" src="./js/jquery-ui.js"></script>
<script type="text/javascript" src="./js/jquery.colorbox.js"></script>
<script type="text/javascript" src="./js/custom_language_zh.js"></script>


<script language="javascript">
var is_send=false;
var topic_index=0;//試題索引值
var teacher_index=0;//老師索引值
function ck_value(){
var isGo = true;
var err_dsc = '';
var ck_array =  ["c_title","begin_time","end_time"];
var err_array =  ["請輸入清單標題!","請選擇開放時間!","請選擇結束時間!"];
var type_array =  ["text","text","text"];

for(var x=0;x< ck_array.length;x++){
	switch(type_array[x]){
		case "text":
			if($('#'+ck_array[x]).val() ==''){
			err_dsc = err_dsc + err_array[x] +'\r\n';
			isGo = false;
			}
		break;
		case "number":
			if(!$.isNumeric($('#'+ck_array[x]).val()) ){
				err_dsc = err_dsc + err_array[x] +'\r\n';
				isGo = false;				
			}		
		break;
}
}	
	
	
	if(isGo){
		if(!is_send){
			$('#topicIndex').val(topic_index);
			$('#teacherIndex').val(teacher_index);
			$('#form1').submit();
			is_send = true;
		}
	}
	
	if(err_dsc !=''){
		alert(err_dsc);
	}
}

//新增試題
function add_topic(){
	$.ajax({
			url: './js_function/add_topic.php',
			type:"POST",
			data: {topicNum:topic_index},		
			error: function(xhr) {
				alert('Ajax request 發生錯誤');
			},
			success: function(response) {
				topic_index++;			
				$('#topicList').append(response);
			}
	});

}
//刪除試題
function delTopic(getKey){
	$('#topicLi'+getKey).remove();
}

//新增老師
function add_teahcer(){
	$.ajax({
			url: './js_function/add_teacher.php',
			type:"POST",
			data: {teacherNum:teacher_index},		
			error: function(xhr) {
				alert('Ajax request 發生錯誤');
			},
			success: function(response) {
				teacher_index++;			
				$('#teacherList').append(response);
			}
	});

}
//刪除老師
function delTeahcer(getKey){
	$('#teacherLi'+getKey).remove();
}

$(function() {
	$('#begin_time').datepicker({
		dateFormat: 'yy-mm-dd',
		changeYear: true,
		changeMonth:true
	});
	$('#end_time').datepicker({dateFormat: 'yy-mm-dd',changeYear : true,changeMonth : true});
});
</script>
</head>
<body>
<aside>
	<h1><img src="images/title.png" title="合作問題解決數位學習系統" /></h1>
	<ul>
    	<?php if($showMenu){?><li><a href="index.php" title="題目建置"><img src="images/icon_add.png" />題目建置</a></li><?php }?>
    	<?php if($showMenu){?><li><a href="testtime_list.php" title="題目清單管理"><img src="images/icon_add.png" />題目清單管理</a></li><?php }?>
    	<?php if($showMenu){?><li><a href="science_list.php" title="科學模組"><img src="images/icon_science.png" />科學模組</a></li><?php }?>
    	<?php if($showMenu){?><li><a href="mathematics_list.php" title="數學模組"><img src="images/icon_math.png" />數學模組</a></li><?php }?>
    	<?php if($showMenu){?><li><a href="read_list.php" title="閱讀模組"><img src="images/icon_read.png" />閱讀模組</a></li><?php }?>	
		<li><a href="../record_list.php" title="操作歷程瀏覽" target="_blank"><img src="images/icon_recording.png" />操作歷程瀏覽</a></li>
    	<li><a href="<?php echo $menu_array['url'];?>" title="<?php echo $menu_array['dsc'];?>"><img src="<?php echo $menu_array['imgurl'];?>" /><?php echo $menu_array['dsc'];?></a></li>
		<li><a href="logout.php" title="登出系統" ><img src="images/icon_logout.png" />登出系統</a></li>
    </ul>
</aside>
<div id="wrapper">
<form action="testtime_add.php" method="POST" enctype="multipart/form-data" id="form1">
 <table >
	<tr><td><span>清單標題:</span><input type="text" name="c_title" id="c_title" value=""></td></tr>
	<tr><td><span>開放時間:</span><input type="text" name="begin_time" id="begin_time" value=""></td></tr>
	<tr><td><span>結束時間:</span><input type="text" name="end_time" id="end_time"  value=""></td></tr>
	<tr>
		<td>
		<ul id="topicList"><input type="button" value="新增題目" onclick="add_topic()">
		</ul>
		</td>
	</tr>
	<tr>
		<td>
		<ul id="teacherList"><input type="button" value="新增老師" onclick="add_teahcer()">
		</ul>
		</td>
	</tr>
	<tr><td><input type="button" value="送出" onclick="ck_value()"></td></tr>
	<input type="hidden" name="send_data" value="has_post_value">
	<input type="hidden"  name="topicIndex" id="topicIndex" value="0">
	<input type="hidden" name="teacherIndex" id="teacherIndex" value="0">

</table>
</form>  
</div>
</body>
</html>
