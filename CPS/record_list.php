<?php
	session_start();

  //包含需求檔案 ------------------------------------------------------------------------
	include("./bcontroller/class/common_lite.php");
	if($_SESSION['loginType'] == ''){
		ri_jump("logout.php");
	}else{
		switch($_SESSION['loginType']){
			case "ADMIN":
				$whereDsc = "";
			break;
			case "TEACHER":
			$whereDsc = " where `teacher_user`='".$_SESSION['swTeacherNum']."' ";
			break;
			case "STUDENT":
			$whereDsc = " where `student_user`='".$_SESSION['swStudentNum']."' and `create_user_type`='STUDENT' ";
			break;
			default:
			ri_jump("logout.php");
			break;		
		}	
	}
	
	if($_GET['pg']=='' || !is_numeric($_GET['pg'])){	
			ri_jump("?pg=1");
	}

	$show_data_num = 50;//一頁顯示多少筆資料
	if($_GET['pg']== '1' ){
		$now_pg = 1;
		$pg_dsc = ' limit '.$show_data_num;
	}else{
		$now_pg = $_GET['pg'];
		$pg_dsc = ' limit '.(($_GET['pg']-1) * $show_data_num) .','.$show_data_num ;
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
	
	
	
	//取出測驗資料
	$sql_dsc = "select * from `opt_record` ".$whereDsc." order by `num` desc ".$pg_dsc;//管理員資料
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){
		$test_data['num'] = $row['num'];
		$test_data['main_data_num'] = $row['main_data_num'];		
		$test_data['test_begin_time'] = $row['test_begin_time'];
		$test_data['up_date'] = $row['up_date'];
		$test_data['power_dsc'] = $row['power_dsc'];
		$tarray = explode(',',$row['power_dsc']);
		$total_num = 0;
		foreach($tarray as $value){
			$total_num +=$value;
		}
		$test_data['power_total'] = $total_num;
		$test_data_array[] = $test_data;
	}
	
	//計算總頁數
	$total_pg = 0;
	$sql_dsc = "SELECT count(*) as `get_num` FROM `opt_record` ".$whereDsc;
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){
		if($row['get_num'] > 0 ){
			$num1 = $row['get_num']/$show_data_num;
			$num1_array = explode('.',$num1);
			$total_pg = $num1_array[0];
			if(count($num1_array)>1){
			$total_pg++;
			}
		}
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
<link rel="stylesheet" href="css/admin.css" />
<link rel="stylesheet" href="css/jquery-ui.css" />
<link rel="Stylesheet" href="css/jquery-ui-1.7.1.custom.css" type="text/css" />

<script language="javascript">
//顯示能力值
function show_msg_box(getValue){
$.ajax({
	url: './bcontroller/js_function/get_testResultsList.php',
	data: {num:getValue},
	type:"POST",
	error: function(xhr) {
		//console.log(xhr);
		alert('Ajax request 發生錯誤');
	},
	success: function(response) {
		$('#show_msg_box').html('').append(response).dialog({
			height: '1024',
			width: '768'	
		});
	}
});

}
</script>
</head>

<body>
<div id="wrapper">
	<h1>操作記錄列表</h1>
	<ul>
    	<li><?php echo $_SESSION['loginUserName'];?>您好!!</li>		
    	<li><a class="button" title="試題列表" href="index.php">試題列表</a></li>	
    	<li><a class="button" title="登出" href="logout.php">登出</a></li>
    </ul>    

    <table class="title">
        <tr>
            <td width="10%">編號</td>
            <td width="22%">單元名稱</td>
            <td width="15%">測驗起始時間</td>
            <td width="15%">測驗結束時間</td>
            <td width="20%">取得能力指標總分</td>
            <td width="18%">編輯</td>
        </tr>
    </table>
<?php for($x=0;$x<count($test_data_array);$x++){	?>
<!-- 單元 -->
    <div class="accordionButton">
        <table class="list_item">
            <tr>
                <td width="10%"><?php echo ($x+1);?></td>
                <td width="22%"><p class="name"><?php echo $title_array[$test_data_array[$x]['main_data_num']];?></p></td>
                <td width="15%"><?php echo $test_data_array[$x]['test_begin_time'];?></td>
                <td width="15%"><?php echo $test_data_array[$x]['up_date'];?></td>
                <td width="20%"><a class="button" onclick="show_msg_box('<?php echo $test_data_array[$x]['num'];?>')">觀看成績</a></td>
                <td width="18%">
                    <a class="button" title="觀看操作步驟" href="<?php echo $module_type_array[$test_data_array[$x]['main_data_num']];?>?m_num=<?php echo $test_data_array[$x]['main_data_num'];?>&num=<?php echo $test_data_array[$x]['num'];?>">觀看操作步驟</a>                
                </td>
            </tr>
        </table>             
    </div>
<!-- 單元 End -->
<?php	}	?>	
</div>

<div id="inline"  style="display:none;">
	<div class="buttons">
		<input type="text" id="inline_input" value="">  
		<a class="button" onclick="up_unit()">新增單元</a>	  
	  </div>
</div>
<div id="inline_operation"  style="display:none;">
	<div class="buttons">
		<table>
		<tr>
			<td>
				作業名稱  
			</td>			
			<td>
				<input type="text" id="inline_operation_input" value="">  
			</td>	
		</tr>	
		<tr>
			<td>
				能力指標說明 
			</td>					
			<td>
			<textarea id="inline_operation_dsc"></textarea>  
			</td>
		</tr>
		<tr>
			<td colspan="2" align="center">
				<a class="button" onclick="up_operation()">新增作業</a>	  
			</td>			
		</tr>
		</table>	
		<input type="hidden" id="inline_operation_input_2" value="">  
	  </div>
</div>
<div class="page" align="center">
	 <?php 
		if($total_pg >0){
				if(($now_pg-1) > 0){
				echo '
				<a href="?pg='.($now_pg-1).'" > 上一頁 </a>&nbsp;&nbsp;
				';
				}else{
				echo '
				<a href="?pg='.("1").'" > 上一頁 </a>&nbsp;&nbsp;';
				}
			 
			  if(($now_pg-5)>0){
				echo '<u><a href="?pg='.($now_pg-5).'">'.($now_pg-5).'</a></u>&nbsp;&nbsp;';
			  }
			   if(($now_pg-4)>0){
				echo '<u><a href="?pg='.($now_pg-4).'">'.($now_pg-4).'</a></u>&nbsp;&nbsp;';
			  }
			   if(($now_pg-3)>0){
				echo '<u><a href="?pg='.($now_pg-3).'">'.($now_pg-3).'</a></u>&nbsp;&nbsp;';
			  }
			   if(($now_pg-2)>0){
				echo '<u><a href="?pg='.($now_pg-2).'">'.($now_pg-2).'</a></u>&nbsp;&nbsp;';
			  }
			   if(($now_pg-1)>0){
				echo '<u><a href="?pg='.($now_pg-1).'">'.($now_pg-1).'</a></u>&nbsp;&nbsp;';
			  }
			  
				echo $now_pg."&nbsp;&nbsp;";//目前頁面
				
			   if(($now_pg+1)<=$total_pg){
				echo '<u><a href="?pg='.($now_pg+1).'">'.($now_pg+1).'</a></u>&nbsp;&nbsp;';
			  }			  
			   if(($now_pg+2)<=$total_pg){
				echo '<u><a href="?pg='.($now_pg+2).'">'.($now_pg+2).'</a></u>&nbsp;&nbsp;';
			  }			  
			   if(($now_pg+3)<=$total_pg){
				echo '<u><a href="?pg='.($now_pg+3).'">'.($now_pg+3).'</a></u>&nbsp;&nbsp;';
			  }			  
			   if(($now_pg+4)<=$total_pg){
				echo '<u><a href="?pg='.($now_pg+4).'">'.($now_pg+4).'</a></u>&nbsp;&nbsp;';
			  }			  
			   if(($now_pg+5)<=$total_pg){
				echo '<u><a href="?pg='.($now_pg+5).'">'.($now_pg+5).'</a></u>&nbsp;&nbsp;';
			  }			  
			  
				if(($total_pg-$now_pg) > 0){
				echo '&nbsp;&nbsp;
				<a href="?pg='.($now_pg+1).'" > 下一頁 </a>';
				}else{
				echo '&nbsp;&nbsp;
				<a href="?pg='.($now_pg).'" > 下一頁 </a>';
				}
		}	  
  ?>
</div>				


<div id="show_msg_box" style="display:none;">

</div>
</body>
</html>
