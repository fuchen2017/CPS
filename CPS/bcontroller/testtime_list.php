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
	
	//取出試題清單列表的資料
	$sql_dsc = "select * from `test_time_list` ".$where_dsc." order by `num` ";
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	$inDSC = '';
	while($row = mysql_fetch_array($res)){
		$sql_data['num'] = $row['num'];	
		$sql_data['c_title'] = $row['c_title'];
		$sql_data['begin_time'] = $row['begin_time'];
		$sql_data['end_time'] =$row['end_time'];
		$timeListArray[] = $sql_data;
		$inDSC .="'".$row['num']."',";
	}
	
	//取出每個清單對應的老師以及班級資料資料
	$sql_dsc = "
	select `a`.*,`b`.`num` as `bnum`,`b`.`c_name` as `teacherName`,`c`.`num` as `cnum`,`c`.`c_title`   
	from `test_time_teacher` as `a` 
	left join `teacherdata` as `b` on `b`.`num`=`a`.`teacherdataNum` 
	left join `main_data` as `c` on `c`.`num`=`a`.`main_data_num` 
	order by `a`.`f_num`,`a`.`teacherdataNum`,`a`.`main_data_num` 
	";
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){
		$sql_data['num'] = $row['num'];	
		$sql_data['teacherName'] = $row['teacherName'];
		$sql_data['c_title'] = $row['c_title'];
		$sql_data['grade_dsc'] = $row['grade_dsc'];
		$sql_data['class_dsc'] = $row['class_dsc'];
		$sql_data['begin_time'] = $row['begin_time'];
		$sql_data['end_time'] = $row['end_time'];
		$timeListData[$row['f_num']][] = $sql_data;
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
<script src="./js/jquery-ui-timepicker-addon.js"></script><!-- datapicker外掛包含分鐘 -->

<script language="javascript">
var nowSWArea = '';
//編輯班級的起始與結束時間
function showDiv(getKey,getNowSWArea){
nowSWArea = getNowSWArea;
$.ajax({
	    url: './js_function/set_testTime.php',
		type:'POST',
		data: {keyNum:getKey},		
	    error: function(xhr) {
			alert('Ajax request 發生錯誤');
	    },
	    success: function(response) {
			$('#inline').html('').append(response);
			$('#inline').show();
			$.colorbox({inline:true,href:"#inline", width:"80%",open:true,onClosed:function(){
			$('#inline').hide();
			}});
	    }
  });
}


function del(get_num,unit_dsc){
	if(confirm("確定是否刪除下列清單名稱的資料內容嗎?\r\n"+unit_dsc)){
		$.ajax({
			url: './js_function/delfunction.php',
			data: {keyNum:get_num,tables:"<?php echo base64_encode("test_time_list");?>"},		
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
	echo "$('#div_area_".$_GET['tr']."').show();";
	}
?>
});

function getExcel(getNum,getTitle){
	$('#listNum').val(getNum);
	$('#listName').val(getTitle);
	$('#excelForm1').submit();
}

</script>
<style>  
 .ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }  
 .ui-timepicker-div dl { text-align: left; }  
 .ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }  
 .ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }  
 .ui-timepicker-div td { font-size: 90%; }  
 .ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }  
 .ui-timepicker-rtl{ direction: rtl; }  
 .ui-timepicker-rtl dl { text-align: right; }  
 .ui-timepicker-rtl dl dd { margin: 0 65px 10px 10px; }  
</style> 

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
	<!--列表標題-->
    <div class="search">清單名稱搜尋：<input type="text" name="s_word" id="s_word" value="<?php echo $_GET['s_word'];?>" ><a class="button" title="清單名稱搜尋" onclick="search_word()">搜尋</a><a href="testtime_list.php" class="button" title="清除搜尋" >清除搜尋</a></div>    
	<ul>
    	<li><a class="button" title="新增單元" href="testtime_add.php">新增清單</a></li>
    </ul>    
    <table class="title">
        <tr>
            <td width="5%">編號</td>
            <td width="35%">單元名稱</td>
            <td width="10%">起始時間</td>
            <td width="10%">結束時間</td>
            <td width="40%">編輯</td>
        </tr>
    </table>
<?php 
if(is_array($timeListArray)){
$x=0;
foreach($timeListArray as $key => $value){	?>
	<!-- 清單列表 -->
		<div class="accordionButton">
			<table class="list_item">
				<tr>
					<td width="5%"><?php echo ($x+1);?></td>
					<td width="35%"><p class="name"><?php echo $value['c_title'];?></p></td>
					<td width="10%"><?php echo $value['begin_time'];?></td>
					<td width="10%"><?php echo $value['end_time'];?></td>
					<td width="40%">
					<a class="button" title="下載總成績Excel" onclick="getExcel('<?php echo $value['num'];?>','<?php echo $value['c_title'];?>')" >下載總成績Excel</a>                
					<a class="button" title="編輯清單" href="testtime_edit.php?num=<?php echo base64_encode($value['num']);?>" >編輯清單</a>                
					<a class="button" title="刪除" onclick="del('<?php echo base64_encode($value['num']); ?>','<?php echo $value['c_title']; ?>')">刪除</a>
					</td>
				</tr>
			</table>             
		</div>
	<!-- 清單列表 End -->
<!-- 單元 → 試題列表 -->
    <div class="accordionContent" id="div_area_<?php echo $value['num'];?>">
        <table class="list_detial">
            <tr>
                <td width="5%" class="title">排序</td>
                <td width="15%" class="title">單元名稱</td>
                <td width="10%" class="title">教師姓名</td>
                <td width="15%" class="title">授課年級</td>
                <td width="15%" class="title">授課班級</td>
                <td width="15%" class="title">開始時間</td>
                <td width="15%" class="title">結束時間</td>
                <td width="15%" class="title">編輯時間</td>
            </tr>
			<?php 
			if(is_array($timeListData[$value['num']])){
				$num =1;
				foreach($timeListData[$value['num']] as $key=>$value2){	//die(print_r($value));?>
				<tr>
                <td><?php echo $num;?></td>
                <td><?php echo $value2['c_title'];?></td>
                <td><?php echo $value2['teacherName'];?></td>
                <td><?php echo $value2['grade_dsc'];?></td>
                <td><?php echo $value2['class_dsc'];?></td>
                <td><?php echo $value2['begin_time'];?></td>
                <td><?php echo $value2['end_time'];?></td>
                <td>
                    <a class="button" title="編輯" onclick="showDiv('<?php echo base64_encode($value2['num']); ?>','<?php echo base64_encode($value['num']);?>')">編輯</a>
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
<!-- 更改出題時間的區域 -->
<div id="inline"  style="display:none;">
<?php 	$ODb->close();?>

</div>
<form method="POST" action="../testdig/index.php/rootconfig/adminGetExcel/" id="excelForm1" target="_black">
	<input type="hidden" name="userID" value="<?php echo $_SESSION['xx_user_loginId'];?>">	
	<input type="hidden" name="userPW" value="<?php echo $_SESSION['xx_user_pw'];?>">	
	<input type="hidden" name="listNum" id="listNum" value="">
	<input type="hidden" name="listName" id="listName" value="">
</form>
</body>
</html>
