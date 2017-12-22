<?php
	session_start();
	/*
		備註：此頁面給管理員管理老師資料
	*/
	//包含需求檔案 ------------------------------------------------------------------------
	include("./class/common_lite.php");
	if($_SESSION['loginType'] == '' || $_SESSION['loginType'] != 'ADMIN'){
		ri_jump("login.php");
	}
	
	//宣告變數 ----------------------------------------------------------------------------
	$ODb = new run_db("mysql",3306);      //建立資料庫物件
	
	//解碼
	$url_dsc = '';	
	if($_GET['s_word'] !=''){
		$url_dsc="?s_word=".$_GET['s_word']."&s_type=".$_GET['s_type'];
		$_GET['s_word'] = base64_decode($_GET['s_word']);		
	}
	
	$where_dsc = "";
		
	if($_GET['s_type']=='byName' && $_GET['s_word']>''){
		$where_dsc = " where `c_name` like '%".$_GET['s_word']."%' ";
	}
	if($_GET['s_type']=='byLoginName' && $_GET['s_word']>''){
		$where_dsc = " where `loginId` like '%".$_GET['s_word']."%' ";
	}
	
	//取出教師資料
	$sql_dsc = "select * from `teacherdata` ".$where_dsc." order by `num` ";
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){
		$sql_data['num'] = $row['num'];	
		$sql_data['loginId'] = $row['loginId'];		
		$sql_data['c_name'] = $row['c_name'];
		$sql_data['up_date'] = $row['up_date'];
		$sql_data_array[] = $sql_data;
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
//新增一筆單元
function add_user(){
	var is_Go = true;
	var error_dsc ="";
	if($('#inline_login_name').val() ==''){
		is_Go = false;
		error_dsc +="登入帳號欄位不得為空!!\r\n";
	}	
	if($('#inline_pw').val() ==''){
		is_Go = false;
		error_dsc +="登入密碼欄位不得為空!!\r\n";
	}
	if($('#inline_pw_1').val() ==''){
		is_Go = false;
		error_dsc +="登入密碼確認欄位不得為空!!\r\n";
	}
	if($('#inline_pw').val() !='' && $('#inline_pw_1').val() !=''){
		if($('#inline_pw').val() != $('#inline_pw_1').val()){
			is_Go = false;
			error_dsc +="登入密碼不一緻!!\r\n";
		}
	}	
	if($('#inline_name').val() ==''){
		is_Go = false;
		error_dsc +="教師姓名欄位不得為空!!\r\n";
	}

	if(is_Go){
		$.ajax({
		url: './js_function/teacher_data_controller.php',
		data: {loginName:$('#inline_login_name').val(),swType:"chkuser"},
		type:"POST",
		error: function(xhr) {
		alert('Ajax request 發生錯誤');
		},
		success: function(response) {
			if(response == "ok"){
				$.ajax({
				url: './js_function/teacher_data_controller.php',
				data: {userName:$('#inline_name').val(),loginName:$('#inline_login_name').val(),loginPw:$('#inline_pw').val(),swType:"insert"},
				type:"POST",
				error: function(xhr) {
					alert('Ajax request 發生錯誤');
				},
				success: function(response) {
					alert('新增教師成功!!');
					location.replace('member_list.php');
				}
			  });
			}else{
				alert('登入帳號重複!!');
			}
		}
		});
	}
	
	if(error_dsc !=''){
		alert(error_dsc);
	}	
}


function del_teacher(get_num,unit_dsc){
	if(confirm("確定刪除下列名稱的教師資料及其管理的學生資料嗎?\r\n"+unit_dsc)){
		$.ajax({
			url: './js_function/delfunction.php',
			data: {keyNum:get_num,tables:"<?php echo base64_encode("teacherData");?>"},		
			error: function(xhr) {
				alert('Ajax request 發生錯誤');
			},
			success: function(response) {			
				alert('資料刪除成功!!');
				location.replace('member_list.php');
			}
		});
	}
}

//教師姓名搜尋
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
				location.replace('?s_word='+response+'&s_type='+$('#search_type').val());
			}
		});
	}
}

//顯示新增教師資料的視窗
function showAddWindow(){
$('#inline_adduser').show();
$('#inline_login_name').val('') ;
$('#inline_pw').val('') ;
$('#inline_pw_1').val('') ;
$('#inline_name').val('') ;
$.colorbox({inline:true,href:"#inline_adduser", width:"30%",open:true,onClosed:function(){
$('#inline_adduser').hide();
}});
}

//編輯教師資料
function edit_teacher(getId){
	if(getId !=''){
		$.ajax({
			url: './js_function/teacher_data_controller.php',
			type:"POST",
			data: {getNum:getId,swType:"edit"},		
			error: function(xhr) {
				alert('Ajax request 發生錯誤');
			},
			success: function(response) {			
				var contact = JSON.parse(response);
				$('#inline_edituser').show();
				$('#edit_login_name').html('').append(contact.loginId) ;
				$('#edit_pw').val(contact.pw) ;
				$('#edit_pw_1').val(contact.pw) ;
				$('#edit_name').val(contact.c_name) ;
				$('#edit_num').val(contact.num) ;
				$.colorbox({inline:true,href:"#inline_edituser", width:"30%",open:true,onClosed:function(){
				$('#inline_edituser').hide();
				}});
			}
		});
	}
}

//更新使用者資料
function updateUserData(){
	var is_Go = true;
	var error_dsc ="";
		
	if($('#edit_pw').val() ==''){
		is_Go = false;
		error_dsc +="登入密碼欄位不得為空!!\r\n";
	}
	if($('#edit_pw_1').val() ==''){
		is_Go = false;
		error_dsc +="登入密碼確認欄位不得為空!!\r\n";
	}
	if($('#edit_pw').val() !='' && $('#edit_pw_1').val() !=''){
		if($('#edit_pw').val() != $('#edit_pw_1').val()){
			is_Go = false;
			error_dsc +="登入密碼不一緻!!\r\n";
		}
	}	
	if($('#edit_name').val() ==''){
		is_Go = false;
		error_dsc +="教師姓名欄位不得為空!!\r\n";
	}

	if(is_Go){
			$.colorbox.close();
			$.ajax({
			url: './js_function/teacher_data_controller.php',
			data: {num:$('#edit_num').val(),userName:$('#edit_name').val(),loginPw:$('#edit_pw').val(),swType:"update"},
			type:"POST",
			error: function(xhr) {
				alert('Ajax request 發生錯誤');
			},
			success: function(response) {
				alert('教師資料修改成功!!');
				location.replace('member_list.php<?php echo $url_dsc;?>');
			}
		  });
	}
	
	if(error_dsc !=''){
		alert(error_dsc);
	}
}
</script>
</head>

<body>
<aside>
	<h1><img src="images/title.png" title="合作問題解決數位學習系統" /></h1>
	<ul>
    	<li><a href="index.php" title="題目建置"><img src="images/icon_add.png" />題目建置</a></li>
    	<li><a href="testtime_list.php" title="題目清單管理"><img src="images/icon_add.png" />題目清單管理</a></li>		
    	<li><a href="science_list.php" title="科學模組"><img src="images/icon_science.png" />科學模組</a></li>
    	<li><a href="mathematics_list.php" title="數學模組"><img src="images/icon_math.png" />數學模組</a></li>
    	<li><a href="read_list.php" title="閱讀模組"><img src="images/icon_read.png" />閱讀模組</a></li>		
		<li><a href="../record_list.php" title="操作歷程瀏覽" target="_blank"><img src="images/icon_recording.png" />操作歷程瀏覽</a></li>
    	<li><a href="member_list.php" title="成員管理"><img src="images/icon_user.png" />成員管理</a></li>
		<li><a href="logout.php" title="登出系統" ><img src="images/icon_logout.png" />登出系統</a></li>
		
    </ul>
</aside>
<div id="wrapper">
	<!--列表標題-->
    <div class="search">
		<select id="search_type" >
			<option value="byName" <?php if($_GET['s_type']=='byName'){echo "selected";}?>>教師姓名搜尋：</option>
			<option value="byLoginName" <?php if($_GET['s_type']=='byLoginName'){echo "selected";}?>>登入帳號搜尋：</option>		
		</select>
		<input type="text" name="s_word" id="s_word" value="<?php echo $_GET['s_word'];?>" >
		<a class="button" title="姓名搜尋：" onclick="search_word()">搜尋</a>
		<a href="member_list.php" class="button" title="清除搜尋" >清除搜尋</a>
	</div>    
	<ul>
    	<li><a class="button" title="新增教師" onclick="showAddWindow()">新增教師</a></li>
    </ul>    
    <table class="title">
        <tr>
            <td width="25%">登入帳號</td>
            <td width="25%">教師姓名</td>
            <td width="50%">編輯</td>
        </tr>    
		<?php for($x=0;$x<count($sql_data_array);$x++){	?>
		<tr>
			<td width="25%"><?php echo $sql_data_array[$x]['loginId'];?></td>
			<td width="25%"><?php echo $sql_data_array[$x]['c_name'];?></td>
			<td width="50%">
				<a class="button" title="資料編輯" onclick="edit_teacher('<?php echo base64_encode($sql_data_array[$x]['num']);?>')">資料編輯</a>                
				<a class="button" title="刪除" onclick="del_teacher('<?php echo base64_encode($sql_data_array[$x]['num']); ?>','<?php echo $sql_data_array[$x]['c_name']; ?>')">刪除</a>
				<a class="button" title="學生資料列表" href="memberListS.php?tNum=<?php echo base64_encode($sql_data_array[$x]['num']);?>">學生資料列表</a>
			</td>
		</tr>
		<?php }?>
	</table>
<div id="inline_adduser"  style="display:none;">
<!--table更改為ul排列-->
    <ul class="name">
        <li><span>登入帳號：</span><input type="text" id="inline_login_name" value="" size="40"></li>
        <li><span>登入密碼：</span><input type="password" id="inline_pw" value="" size="40"></li>
        <li><span>登入密碼確認：</span><input type="password" id="inline_pw_1" value="" size="40"></li>
        <li><span>教師姓名：</span><input type="text" id="inline_name" value="" size="40"></li>
    </ul>
    <a class="button" onclick="add_user()">新增教師資料</a>	
</div>
<div id="inline_edituser"  style="display:none;">
<!--table更改為ul排列-->
    <ul class="name">
        <li><span>登入帳號：</span><span id="edit_login_name"></span></li>
        <li><span>登入密碼：</span><input type="password" id="edit_pw" value="" size="40"></li>
        <li><span>登入密碼確認：</span><input type="password" id="edit_pw_1" value="" size="40"></li>
        <li><span>教師姓名：</span><input type="text" id="edit_name" value="" size="40"></li>
    </ul>
    <a class="button" onclick="updateUserData()">確定修改</a>
	<input type="hidden" id="edit_num" value="" >	
</div>
</body>
</html>
