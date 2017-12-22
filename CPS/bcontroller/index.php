<?php
	session_start();
  //包含需求檔案 ------------------------------------------------------------------------
	include("./class/common_lite.php");
	if($_SESSION['loginType'] == ''){
		ri_jump("logout.php");
	}else{
		switch($_SESSION['loginType']){
			case "ADMIN":
				$recordURL = "record_list";
				$menu_array = array(
				'url' => 'member_list.php',
				'dsc' => '成員管理',
				'imgurl' => 'images/icon_user.png'
				);
				$showMenu = true;
			break;
			case "TEACHER":
				$recordURL = "record_t";			
				ri_jump("memberListS.php");				
			break;
			default:
			ri_jump("logout.php");
			break;		
		}	
	}
	//宣告變數 ----------------------------------------------------------------------------
	$ODb = new run_db("mysql",3306);      //建立資料庫物件

	//解碼
	foreach($_GET as $key => $value){
		$_GET[$key] = base64_decode($value);
	}
	
	if($_GET['s_word']!=''){
		$whereDscArray[] = " `c_title` like '%".$_GET['s_word']."%' ";
	}
	
	$where_dsc = "";
	for($x=0;$x<count($whereDscArray);$x++)
	{
		if($where_dsc == ""){
			$where_dsc = " where ".$whereDscArray[$x];
		}else{
			$where_dsc .= $whereDscArray[$x];
		}
	}
	
	$module_dsc = array(
	'science'=>'科學模組','read'=>'閱讀模組','mathematics'=>'數學模組'
	);

	$speech_dsc = array(
	'switch'=>'選項式對話','free'=>'開放式對話'
	);
	
	//取出單元資料
	$sql_dsc = "select * from `main_data` ".$where_dsc." order by `num` ";//管理員資料
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){
		$sql_data['c_title'] = $row['c_title'];
		$sql_data['c_module_type'] = $module_dsc[$row['c_module_type']];
		$sql_data['c_speech_type'] = $speech_dsc[$row['c_speech_type']];
		$sql_data['up_date'] = $row['up_date'];
		$sql_data['is_open'] = $row['is_open'];		
		$sql_data['is_share'] = $row['is_share'];		
		$sql_data['num'] = $row['num'];

		//取出作業資料
		$sql_dsc_1 = "select * from `operation_data` where `main_data_num`='".$row['num']."' order by `num`";
		$res_1=$ODb->query($sql_dsc_1) or die("載入資料出錯，請聯繫管理員。");
		while($row_1 = mysql_fetch_array($res_1)){
			$work_data['num'] = $row_1['num'];				
			$work_data['c_title'] = $row_1['c_title'];				
			$work_data['up_date'] = $row_1['up_date'];				

			//計算試題數量
			$sql_dsc_2 = "select count(*) as `g_num` from `questions_data` where `operation_data_num`='".$row_1['num']."'";
			$res_2=$ODb->query($sql_dsc_2) or die("載入資料出錯，請聯繫管理員。");
			while($row_2 = mysql_fetch_array($res_2)){
				$work_data['g_num'] = $row_2['g_num'];
			}		
			$work_data_array[$row['num']][] = $work_data;
		}
		
		$sql_data_array[$row['num']] = $sql_data;
	}
	
	//如果是教師就調出已經選擇別人創造的題目
	if($_SESSION['loginType'] == "TEACHER"){
		$sql_dsc = "
		select `a`.*,`b`.`mainData_num`,`b`.`user_type`,`b`.`user_num`
		from `main_data` as `a`
		left join `share_data` as `b` on `b`.`mainData_num`=`a`.`num` 
		where `b`.`user_type`='TEACHER' and `b`.`user_num`='".$_SESSION['swTeacherNum']."' and `a`.`is_share`=1  order by `a`.`num` ";//管理員資料
		$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
		while($row = mysql_fetch_array($res)){
			$mySetShareData[$row['num']] = $row['num'];
			if(!isset($sql_data_array[$row['num']])){
				$sql_data['c_title'] = $row['c_title'];
				$sql_data['c_module_type'] = $module_dsc[$row['c_module_type']];
				$sql_data['c_speech_type'] = $speech_dsc[$row['c_speech_type']];
				$sql_data['up_date'] = $row['up_date'];
				$sql_data['is_open'] = $row['is_open'];		
				$sql_data['is_share'] = $row['is_share'];		
				$sql_data['num'] = $row['num'];

				//取出作業資料
				$sql_dsc_1 = "select * from `operation_data` where `main_data_num`='".$row['num']."' order by `num`";
				$res_1=$ODb->query($sql_dsc_1) or die("載入資料出錯，請聯繫管理員。");
				while($row_1 = mysql_fetch_array($res_1)){
					$work_data['num'] = $row_1['num'];				
					$work_data['c_title'] = $row_1['c_title'];				
					$work_data['up_date'] = $row_1['up_date'];				

					//計算試題數量
					$sql_dsc_2 = "select count(*) as `g_num` from `questions_data` where `operation_data_num`='".$row_1['num']."'";
					$res_2=$ODb->query($sql_dsc_2) or die("載入資料出錯，請聯繫管理員。");
					while($row_2 = mysql_fetch_array($res_2)){
						$work_data['g_num'] = $row_2['g_num'];
					}		
					$work_data_array[$row['num']][] = $work_data;
				}
				
				$sql_data_array[$row['num']] = $sql_data;			
			
			}		
		}
	
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

<script src="./js/jquery-1.10.1.min.js"> </script>
<script src="./js/javascript.js"></script><!-- 頁面收和 -->
<script src="./js/jquery-ui.js"></script>
<script src="./js/jquery.colorbox.js"></script>
<script language="javascript">
var tr_index = 0;//新增作業的index

//顯示校區下拉選單
function add_unit(){
$('#inline').show();
$.colorbox({inline:true,href:"#inline", width:"30%",open:true,onClosed:function(){
$('#inline').hide();
}});
}

function add_operation(keynum){
$('#inline_operation').show();
$('#inline_operation_input_2').val(keynum) ;
$.colorbox({inline:true,href:"#inline_operation", width:"30%",open:true,onClosed:function(){
$('#inline_operation').hide();
}});
}

//新增一筆單元
function up_unit(){
	var is_Go = true;
	var error_dsc ="";
	if($('#inline_input').val() ==''){
		is_Go = false;
		error_dsc +="請輸入單元名稱!!\r\n";
	}
	if($('#c_test_time').val() ==''){
		is_Go = false;
		error_dsc +="請輸入測驗時間!!\r\n";
	}else if(isNaN($('#c_test_time').val()) || $('#c_test_time').val()<0){
		is_Go = false;
		error_dsc +="測驗時間請輸入整數!!\r\n";
	}
	if(is_Go){
		$.ajax({
	    url: './js_function/add_unit.php',
		type:'POST',
		data: {keyNum:$('#inline_input').val(),ModuleType:$('#module_type').val(),c_speech_type:$('#c_speech_type').val(),c_test_time:$('#c_test_time').val()},		
	    error: function(xhr) {
			alert('Ajax request 發生錯誤');
	    },
	    success: function(response) {
			alert('新增單元成功!!');
			location.replace('?tr=');
	    }
	  });
	}
	
	if(error_dsc !=''){
		alert(error_dsc);
	}	
}

//新增一筆作業
function up_operation(){
	var is_Go = true;
	var error_dsc ="";
	if($('#inline_operation_input').val() ==''){
		is_Go = false;
		error_dsc +="請輸入作業名稱!!\r\n";
	}
	if(is_Go){
		$.ajax({
	    url: './js_function/add_operation.php',
		type:'POST',
		data: {keyNum:$('#inline_operation_input_2').val(),namedsc:$('#inline_operation_input').val(),powerdsc:$('#inline_operation_dsc').val()},		
	    error: function(xhr) {
			alert('Ajax request 發生錯誤');
	    },
	    success: function(response) {			
			alert('新增作業成功!!');
			location.replace('?tr='+$('#inline_operation_input_2').val());
	    }
	  });
	}
	
	if(error_dsc !=''){
		alert(error_dsc);
	}	
}

function del_unit(get_num,unit_dsc){
	if(confirm("確定是否刪除下列單元名稱的資料及其底下所有的作業資料內容嗎?\r\n"+unit_dsc)){
		$.ajax({
			url: './js_function/delfunction.php',
			data: {keyNum:get_num,tables:"<?php echo base64_encode("main_data");?>"},		
			error: function(xhr) {
				alert('Ajax request 發生錯誤');
			},
			success: function(response) {				
				alert('資料刪除成功!!');
				location.replace('?tr=');
			}
		});
	}
}

function del_operation(get_num,unit_dsc){
	if(confirm("確定是否刪除下列作業名稱的資料內容嗎?\r\n"+unit_dsc)){
		$.ajax({
			url: './js_function/delfunction.php',
			data: {keyNum:get_num,tables:"<?php echo base64_encode("operation_data");?>"},		
			error: function(xhr) {
				alert('Ajax request 發生錯誤');
			},
			success: function(response) {			
				alert('資料刪除成功!!');
				location.replace('?tr=');
			}
		});
	}
}

function edit_operation(keynum){
	location.href="operation_edit.php?o_num="+keynum+"&o_t_num=<?php echo base64_encode(0);?>";
}

//單元標題搜尋
function search_word(){
	if($('#s_word').val() !=''){
		$.ajax({
			url: './js_function/value_encode.php',
			type:"POST",
			data: {values:$('#s_word').val()},		
			error: function(xhr) {
				alert('Ajax request 發生錯誤');
			},
			success: function(response) {			
				location.replace('?s_word='+response);
			}
		});
	}
}

$(document).ready(function() {
<?php 
	if($_GET['tr']!=''){
	echo "$('#div_area_".$_GET['tr']."').slideDown('fase');";
	}
?>
});

//是否開放單元
function sw_show(getNum,gettype,getButtonId){
	if($('#'+getButtonId).attr('title') == '開放單元'){
		$('#'+getButtonId).attr('title','關閉單元').html('關閉單元').attr('onclick','sw_show('+getNum+',1,"'+getButtonId+'")');
	}else{		
		$('#'+getButtonId).attr('title','開放單元').html('開放單元').attr('onclick','sw_show('+getNum+',0,"'+getButtonId+'")');
	}
	$.ajax({
			url: './js_function/sw_show.php',
			type:"POST",
			data: {keyNum:getNum,getType:gettype},		
			error: function(xhr) {
				alert('Ajax request 發生錯誤');
			},
			success: function(response) {			
				//location.replace('?s_word='+response);
			}
	});
}

//是否開放分享
function sw_practice(getNum,gettype,getButtonId){
	if($('#'+getButtonId).attr('title') == '練習題'){
		$('#'+getButtonId).attr('title','非練習題').html('非練習題').attr('onclick','sw_practice('+getNum+',1,"'+getButtonId+'")');
	}else{		
		$('#'+getButtonId).attr('title','練習題').html('練習題').attr('onclick','sw_practice('+getNum+',0,"'+getButtonId+'")');
	}
	$.ajax({
			url: './js_function/sw_practice.php',
			type:"POST",
			data: {keyNum:getNum,getType:gettype},		
			error: function(xhr) {
				alert('Ajax request 發生錯誤');
			},
			success: function(response) {			
				//location.replace('?s_word='+response);
			}
	});
}
//取消訂閱他人分享的單元
function cancel_sw(getKey){
	$.ajax({
			url: './js_function/dl_share.php',
			type:"POST",
			data: {keyNum:getKey},		
			error: function(xhr) {
				alert('Ajax request 發生錯誤');
			},
			success: function(response) {			
				location.reload();
			}
	});
}

$(function(){
$('#showShareData').colorbox({width:"100%",height:"70%",iframe: true});//註冊登箱內的超連結
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
		<li><a href="../<?php echo $recordURL;?>.php" title="操作歷程瀏覽" target="_blank"><img src="images/icon_recording.png" />操作歷程瀏覽</a></li>
    	<li><a href="<?php echo $menu_array['url'];?>" title="<?php echo $menu_array['dsc'];?>"><img src="<?php echo $menu_array['imgurl'];?>" /><?php echo $menu_array['dsc'];?></a></li>
		<li><a href="logout.php" title="登出系統" ><img src="images/icon_logout.png" />登出系統</a></li>
    </ul>
</aside>
<div id="wrapper">
	<!--列表標題-->
    <div class="search">單元名稱搜尋：<input type="text" name="s_word" id="s_word" value="<?php echo $_GET['s_word'];?>" ><a class="button" title="單元名稱搜尋" onclick="search_word()">搜尋</a><a href="index.php" class="button" title="清除搜尋" >清除搜尋</a></div>    
	<ul>
    	<li><a class="button" title="選取已分享的單元" id="showShareData" href="share_list.php">選取已分享的單元</a></li>	
    	<li><a class="button" title="新增單元" onclick="add_unit()">新增單元</a></li>
    </ul>    
    <table class="title">
        <tr>
            <td width="5%">編號</td>
            <td width="22%">單元名稱</td>
            <td width="10%">對話類型</td>
            <td width="10%">模組類別</td>
            <td width="10%">作業數量</td>
            <td width="38%">編輯</td>
        </tr>
    </table>
<?php 
if(is_array($sql_data_array)){
$x=0;
foreach($sql_data_array as $key => $value){	?>
<!-- 單元 -->
    <div class="accordionButton">
        <table class="list_item">
            <tr>
                <td width="5%"><?php echo ($x+1);?></td>
                <td width="22%"><p class="name"><?php echo $value['c_title'];?></p></td>
                <td width="10%"><?php echo $value['c_speech_type'];?></td>
                <td width="10%"><?php echo $value['c_module_type'];?></td>
                <td width="10%"><?php echo count($work_data_array[$value['num']]);?></td>
                <td width="38%">
					<?php
					//判斷是否是他人分享的單元資料
					if(isset($mySetShareData[$value['num']])){?>
					<a class="button" title="取消使用此分享單元"  onclick="cancel_sw(<?php echo $value['num'];?>)">取消使用此分享單元</a>
					<?php
					}else{
						if($value['is_practice']==1){
					?>
					<a class="button" title="練習題" id="shareButton<?php echo $x;?>" onclick="sw_practice(<?php echo $value['num'];?>,0,'shareButton<?php echo $x;?>')">練習題</a>
					<?php }else{	?>
					<a class="button" title="非練習題" id="shareButton<?php echo $x;?>" onclick="sw_practice(<?php echo $value['num'];?>,1,'shareButton<?php echo $x;?>')">非練習題</a>
					<?php } ?>

						<a class="button" title="新增作業" onclick="add_operation(<?php echo $value['num'];?>)">新增作業</a>                
						<a class="button" title="刪除" onclick="del_unit('<?php echo base64_encode($value['num']); ?>','<?php echo $value['c_title']; ?>')">刪除</a>
					<?php
					}	
					?>
                </td>
            </tr>
        </table>             
    </div>
<!-- 單元 End -->
<!-- 單元 → 試題列表 -->
    <div class="accordionContent" id="div_area_<?php echo $value['num'];?>">
        <table class="list_detial">
            <tr>
                <td width="15%" class="title">排序</td>
                <td width="35%" class="title">作業名稱</td>
                <td width="10%" class="title">試題數量</td>
                <td width="20%" class="title">時間</td>
                <td width="20%" class="title">編輯</td>
            </tr>
			<?php 
			if(is_array($work_data_array[$value['num']])){
				$num =1;
				foreach($work_data_array[$value['num']] as $key=>$value2){	//die(print_r($value));?>
				<tr>
                <td><?php echo $num;?></td>
                <td><?php echo $value2['c_title'];?></td>
                <td><?php echo $value2['g_num'];?></td>
                <td><?php echo $value2['up_date'];?></td>
                <td>
				<?php
				//判斷是否是他人分享的單元資料
				if(!isset($mySetShareData[$value['num']])){
				?>
                    <a class="button" title="編輯" onclick="edit_operation('<?php echo base64_encode($value2['num']);?>')">編輯</a>
                    <a class="button" title="刪除" onclick="del_operation('<?php echo base64_encode($value2['num']);?>','<?php echo $value2['c_title'];?>')">刪除</a>
				<?php 
				}
				?>	
                </td>
            </tr>
			<?php	
				$num++;
				}
			}
			
			?>
        </table>  
    </div>
<!-- 單元 → 試題列表 end -->
<?php	
	$x++;
	}
}
?>
</div>

<div id="inline"  style="display:none;">
<!--更改標籤排列-->
    <ul class="name">
        <li><span>單元名稱</span><input type="text" id="inline_input" value="" size="40"></li>
        <li>
            <span>模組類別</span>
            <select name="module_type" id="module_type">
                <option value="science">科學模組</option>
                <option value="mathematics">數學模組</option>
                <option value="read">閱讀模組</option>
            </select>
        </li>
        <li>
            <span>對話類型</span>
            <select name="c_speech_type" id="c_speech_type">
                <option value="switch">選項式對話</option>
                <option value="free">開放式對話</option>
            </select>
        </li>
        <li>
            <span>測驗時間</span>
            <input type="text" id="c_test_time" value="0" size="10">分鐘
        </li>
    </ul>
    <a class="button" onclick="up_unit()">新增單元</a>
</div>

<div id="inline_operation"  style="display:none;">
<!--table更改為ul排列-->
    <ul class="name">
        <li><span>作業名稱</span><input type="text" id="inline_operation_input" value="" size="40"></li>
        <li><span>能力指標說明</span><textarea id="inline_operation_dsc"></textarea>  </li>
    </ul>
    <a class="button" onclick="up_operation()">新增作業</a>	
    <input type="hidden" id="inline_operation_input_2" value="">    
</div>

</body>
</html>
