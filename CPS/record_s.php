<?php
	session_start();

  //包含需求檔案 ------------------------------------------------------------------------
	include("./bcontroller/class/common_lite.php");
	$showButton = false;
	
	if($_SESSION['loginType'] == ''){
		ri_jump("logout.php");
	}else{
		switch($_SESSION['loginType']){
			case "ADMIN":
				$whereDsc = "";
			break;
			case "TEACHER":
				if($_GET['s']=='' && $_GET['t']==''){
					ri_jump("logout.php");
				}else{
					if($_GET['s']>''){
					if(!is_numeric($_GET['s']))
					ri_jump("logout.php");
					}
				}
				$getKey = $_GET['s'];
				if($_GET['s']==''){
					$whereDsc = " where `teacher_user`='".$_SESSION['swTeacherNum']."' ";				
					$userName = getUserName('teacherdata',$_SESSION['swTeacherNum'])."教師";
				}else{//調出學生的資料
					$whereDsc = " where `student_user`='".$getKey."' ";				
					$userName = getUserName('studentdata',$getKey)."學生";
				}
				
				$showButton=true;//顯示回上一頁(回教師頁面)
			break;
			case "STUDENT":
			$whereDsc = " where `student_user`='".$_SESSION['swStudentNum']."' and `create_user_type`='STUDENT' ";
			$userName = getUserName('studentdata',$_SESSION['swStudentNum'])."學生";
			break;
			default:
			ri_jump("logout.php");
			break;		
		}	
	}	
	//宣告變數 ----------------------------------------------------------------------------
	$ODb = new run_db("mysql",3306);      //建立資料庫物件

	//取出單元資料
	$sql_dsc = "select * from `main_data`";//管理員資料
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){
		$title_array[$row['num']] = $row['c_title'];		
		switch($row['c_module_type']){
		case "science":
		$module_type_array[$row['num']] = "view_test.php";
		break;
		case "mathematics":
		$module_type_array[$row['num']] = "view_test_m.php";
		break;
		case "read":
		$module_type_array[$row['num']] = "view_test_r.php";
		break;
		}
	}
	
	$NameArray = array('0'=>'練習題');
	
	//取出測驗資料
	$sql_dsc = "select * from `opt_record` ".$whereDsc." order by `num` desc";
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){
		$test_data['num'] = $row['num'];
		$test_data['main_data_num'] = $row['main_data_num'];		
		$test_data['test_begin_time'] = $row['test_begin_time'];
		$test_data['up_date'] = $row['up_date'];
		$test_data['power_dsc'] = $row['power_dsc'];
		$DataArray[$row['timelist_num']][] = $test_data;
		if(!isset($testNameArray[$row['timelist_num']])){
		$NameArray[$row['timelist_num']]= $row['timelist_dsc'];
		}
	}
		
	function getUserName($tableDsc,$key){
		$ODb = new run_db("mysql",3306);      //建立資料庫物件	
		$sql_dsc = "select `c_name` from `".$tableDsc."` where `num`='".$key."'";
		$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
		while($row = mysql_fetch_array($res)){
			return $row['c_name'];
		}
		$ODb->close();
		return '';
	}
	$ODb->close();


	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>合作問題解決數位學習系統</title>
<script src="./js/jquery-1.10.1.min.js"> </script>
<script src="./js/javascript.js"></script><!-- 頁面收和 -->
<script src="./js/jquery-ui.js"></script>
<script src="./js/jquery.colorbox.js"></script>
<link rel="stylesheet" href="css/admin.css" />
<link rel="stylesheet" href="css/colorbox.css" />
<link rel="stylesheet" href="css/jquery-ui.css" />
<link rel="Stylesheet" href="css/jquery-ui-1.7.1.custom.css" type="text/css" />

<script language="javascript">
//顯示能力值
function show_msg_box(getValue,getValue2){
	$.ajax({
		url: './bcontroller/js_function/get_testResultsList.php',
		data: {num:getValue,snum:getValue2,swType:'oneData'},
		type:"POST",
		error: function(xhr) {
			//console.log(xhr);
			alert('Ajax request 發生錯誤');
		},
		success: function(response) {
			$('#show_msg_box').append(response).show();
			$.colorbox({inline:true,href:"#show_msg_box", width:"40%",height:"90%",open:true,onClosed:function(){
			$('#show_msg_box').html('').hide();
			}});
		}
	});
}

//顯示總分數
function show_total_box(getValue,getValue2){
	$.ajax({
		url: './bcontroller/js_function/get_testResultsList.php',
		data: {num:getValue,snum:getValue2,swType:'totalData'},
		type:"POST",
		error: function(xhr) {
			//console.log(xhr);
			//alert('Ajax request 發生錯誤');
		},
		success: function(response) {
			$('#show_msg_box').append(response).show();
			$.colorbox({inline:true,href:"#show_msg_box", width:"40%",height:"90%",open:true,onClosed:function(){
			$('#show_msg_box').html('').hide();
			}});
		}
	});
}

$(document).ready(function() {
<?php 
	if($_GET['tr']!=''){
	echo "$('#div_area_".$_GET['tr']."').slideDown('fase');";
	}
?>
});

</script>
</head>

<body>
<div id="wrapper">
	<h1>操作記錄列表 （受測人：<?php echo $userName;?>）</h1>
	<ul>
    	<li><?php echo $_SESSION['loginUserName'];?>您好!!</li>	
		<?php if($showButton){?>
    	<li><a class="button" title="回上一頁" href="record_t.php">回上一頁</a></li>	
		<?php }	?>		
    	<li><a class="button" title="試題列表" href="index.php">試題列表</a></li>	
    	<li><a class="button" title="登出" href="logout.php">登出</a></li>
    </ul>    
    <table class="title">
        <tr>
            <td width="10%">編號</td>
            <td width="50%">測驗清單名稱</td>
            <td width="40%">動作</td>
        </tr>
    </table>
<?php 
$outNum = 0;
foreach($NameArray as $myKey => $myDSC){
?>
<!-- 單元 -->
    <div class="accordionButton">
        <table class="list_item">
            <tr>
                <td width="10%"><?php echo ($outNum+1);?></td>
                <td width="50%"><p class="name"><?php echo $myDSC;?></p></td>
                <td width="40%"><p class="name">成績紀錄</p></td>
             <!--   <td width="40%"><a class="button" onclick="show_total_box('<?php echo $myKey;?>','<?php echo $_GET['s'];?>')">觀看總成績</a></td>  -->
            </tr>
        </table>             
    </div>
<!-- 單元 End -->
<!-- 單元 → 試題列表 -->
    <div class="accordionContent" id="div_area_<?php echo $myKey;?>">
        <table class="list_detial">
            <tr>
				<td width="10%">編號</td>
				<td width="22%">測驗單元名稱</td>
				<td width="15%">測驗起始時間</td>
				<td width="15%">測驗結束時間</td>
				<td width="20%">取得能力指標總分</td>
				<td width="18%">編輯</td>
            </tr>
			<?php 
			if(is_array($DataArray[$myKey])){
				$num =1;
				foreach($DataArray[$myKey] as $key2 => $value2){ ?>
				<tr>
                <td width="10%"><?php echo $num;?></td>
                <td width="22%"><p class="name"><?php echo $title_array[$value2['main_data_num']];?></p></td>
                <td width="15%"><?php echo $value2['test_begin_time'];?></td>
                <td width="15%"><?php echo $value2['up_date'];?></td>
                <td width="20%"><a class="button" onclick="show_msg_box('<?php echo $value2['num'];?>','<?php echo $_GET['s'];?>')">觀看成績</a></td>
                <td width="18%">
                    <a class="button" title="觀看操作步驟" href="<?php echo $module_type_array[$value2['main_data_num']];?>?m_num=<?php echo $value2['main_data_num'];?>&num=<?php echo $value2['num'];?>">觀看操作步驟</a>                
                </td>
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
	$outNum++;
}
?>	
</div>
<div id="show_msg_box" style="display:none;">

</div>
</body>
</html>
