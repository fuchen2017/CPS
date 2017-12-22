<?php
	session_start();
	/*
		備註：此頁面顯示目前有提供分享的單元資料
	*/
	//包含需求檔案 ------------------------------------------------------------------------
	include("./class/common_lite.php");
	if($_SESSION['loginType'] == ''){
		ri_jump("logout.php");
	}else{
		switch($_SESSION['loginType']){
			case "ADMIN":
			$whereDsc=" `user_type`='ADMIN' ";
			$whereDsc2=" `create_user_type`='TEACHER' ";
			break;			
			case "TEACHER":	
			$whereDsc=" `user_type`='TEACHER' and  `user_num`='".$_SESSION['swTeacherNum']."' ";
			$whereDsc2=" `create_user`!='".$_SESSION['swTeacherNum']."' ";
			break;
			default:
			ri_jump("logout.php");
			break;		
		}	
	}	
	//宣告變數 ----------------------------------------------------------------------------
	$ODb = new run_db("mysql",3306);      //建立資料庫物件
	
	$module_dsc = array(
	'science'=>'科學模組','read'=>'閱讀模組','mathematics'=>'數學模組'
	);

	$speech_dsc = array(
	'switch'=>'選項式對話','free'=>'開放式對話'
	);

	
	//1.先取出該使用者已經選取的單元資料
	$sql_dsc = "select * from `share_data` where ".$whereDsc." order by `num` ";//管理員資料
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){
		$swNumArray[$row['mainData_num']] = $row['mainData_num'];
	}
	
	//2.調出所有分享的單元資料	
	$sql_dsc = "select * from `main_data` where `is_share`=1 and ".$whereDsc2."  order by `num` ";//管理員資料
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){
		if(!isset($swNumArray[$row['num']])){
				$sql_data['num'] = $row['num'];	
				$sql_data['c_title'] = $row['c_title'];
				$sql_data['c_module_type'] = $module_dsc[$row['c_module_type']];
				$sql_data['c_speech_type'] = $speech_dsc[$row['c_speech_type']];
				$sql_data['c_test_time'] = $row['c_test_time'];	
				$sql_data_array[] = $sql_data;
		}
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>合作問題解決數位學習系統</title>
<script src="./js/jquery-1.10.1.min.js"> </script>
<script src="./js/jquery-ui.js"></script>
<link rel="stylesheet" href="css/admin.css" />
<link rel="stylesheet" href="css/normalize.css" />
<link rel="stylesheet" href="css/jquery-ui.css" />
<link rel="stylesheet" href="css/colorbox.css" />
<script language="javascript">

function saveSW(){
	var swNum="";
	$('input:checkbox').each(function () {   
        if($(this).prop("checked")){ 
            swNum = swNum + $(this).val()+","; 
        } 
    });
	$.ajax({
			url: './js_function/up_share.php',
			type:"POST",
			data: {keyNum:swNum},		
			error: function(xhr) {
				alert('Ajax request 發生錯誤');
			},
			success: function(response) {	
				parent.$.fn.colorbox.close();
				parent.location.reload();
			}
	});
	if(swNum==''){
	parent.$.fn.colorbox.close();
	}
}
function closeWin(){
	parent.$.fn.colorbox.close();
}
</script>
</head>
<body>
<div id="wrapper">
	<ul>
    	<li><a class="button" title="存檔" onclick="saveSW()">存檔</a></li>	
    	<li><a class="button" title="取消" onclick="closeWin()">取消</a></li>
    </ul>    

    <table class="title">
        <tr>
            <td width="10%">選取</td>
            <td width="60%">單元名稱</td>
            <td width="10%">對話類型</td>
            <td width="10%">模組類別</td>
            <td width="10%">作業數量</td>
        </tr>
    </table>
<?php for($x=0;$x<count($sql_data_array);$x++){	?>
    <div class="accordionButton">
        <table class="list_item">
            <tr>
                <td width="10%"><input type="checkbox" value="<?php echo $sql_data_array[$x]['num'];?>"></td>
                <td width="60%"><p class="name"><?php echo $sql_data_array[$x]['c_title'];?></p></td>
                <td width="10%"><?php echo $sql_data_array[$x]['c_speech_type'];?></td>
                <td width="10%"><?php echo $sql_data_array[$x]['c_module_type'];?></td>
                <td width="10%"><?php echo count($work_data_array[$sql_data_array[$x]['num']]);?></td>
            </tr>
        </table>             
    </div>

<?php	}	?>	
</div>
</body>
</html>
