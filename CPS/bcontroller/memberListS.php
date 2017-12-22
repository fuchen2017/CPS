<?php
	session_start();
	/*
		備註：此頁面管理學生資料
	*/	
	//包含需求檔案 ------------------------------------------------------------------------
	include("./class/common_lite.php");
	if($_SESSION['loginType'] == ''){
		ri_jump("login.php");
	}

	
	//宣告變數 ----------------------------------------------------------------------------
	$ODb = new run_db("mysql",3306);      //建立資料庫物件

	//解碼
	$url_dsc = '';	
	if($_GET['tNum'] !=''){
		$getTnum = base64_decode($_GET['tNum']);		
	}
	
	$menu_array = array(
		'url' => 'member_list.php',
		'dsc' => '成員管理',
		'imgurl' => 'images/icon_user.png'
	);
	$showMenu = true;
	$recordURL = "record_list";
	if($_SESSION['loginType'] == 'TEACHER'){
		$recordURL = "record_t";
		$getTnum = $_SESSION['swTeacherNum'];
		$menu_array = array(
			'url' => 'memberListS.php',
			'dsc' => '學生管理',
			'imgurl' => 'images/icon_user.png'
		);
		$showMenu = false;
	}
	
	$sql="select * from `teacherdata` where `num`='".$getTnum."' ";
	$res=$ODb->query($sql) or die("載入資料出錯，請聯繫管理員。");
	if(mysql_num_rows($res)==1){
		while($row = mysql_fetch_array($res)){
		$teacher_name = $row['c_name'];
		}
	}else{
		ri_jump("logout.php");
	}
	
	if($_GET['s_word'] !=''){
		$url_dsc="&s_word=".$_GET['s_word']."&s_type=".$_GET['s_type'];
		$_GET['s_word'] = base64_decode($_GET['s_word']);		
	}
	
	$where_dsc = "";
		
	if($_GET['s_type']=='byName' && $_GET['s_word']>''){
		$where_dsc = " and `c_name` like '%".$_GET['s_word']."%' ";
	}
	if($_GET['s_type']=='byLoginName' && $_GET['s_word']>''){
		$where_dsc = " and `loginId` like '%".$_GET['s_word']."%' ";
	}
	
	//取出學生資料
	$sql_dsc = "select * from `studentdata` where `teacherdataNum`='".$getTnum."' ".$where_dsc." order by `num` ";
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
//新增一筆學生資料
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
	if($('#inline_cityCode').val() ==''){
		is_Go = false;
		error_dsc +="縣市代碼欄位不得為空!!\r\n";
	}
	if($('#inline_cityName').val() ==''){
		is_Go = false;
		error_dsc +="縣市名欄位不得為空!!\r\n";
	}
	if($('#inline_schoolName').val() ==''){
		is_Go = false;
		error_dsc +="學校名稱欄位不得為空!!\r\n";
	}
	if($('#inline_educationDsc').val() ==''){
		is_Go = false;
		error_dsc +="國、高中欄位不得為空!!\r\n";
	}
	if($('#inline_gradeDsc').val() ==''){
		is_Go = false;
		error_dsc +="授課年級欄位不得為空!!\r\n";
	}
	if($('#inline_classDsc').val() ==''){
		is_Go = false;
		error_dsc +="授課班級欄位不得為空!!\r\n";
	}
	if($('#inline_studentId').val() ==''){
		is_Go = false;
		error_dsc +="學生學號欄位不得為空!!\r\n";
	}
	if($('#inline_name').val() ==''){
		is_Go = false;
		error_dsc +="學生姓名欄位不得為空!!\r\n";
	}

	if(is_Go){
		$.ajax({
		url: './js_function/student_data_controller.php',
		data: {
		loginName:$('#inline_login_name').val(),
		swType:"chkuser",
		tNum:"<?php echo $getTnum;?>"
		},
		type:"POST",
		error: function(xhr) {
		alert('Ajax request 發生錯誤');
		},
		success: function(response) {
			if(response == "ok"){
				$.ajax({
				url: './js_function/student_data_controller.php',
				data: {
				userName:$('#inline_name').val(),
				loginName:$('#inline_login_name').val(),
				loginPw:$('#inline_pw').val(),
				cityCode:$('#inline_cityCode').val(),
				cityName:$('#inline_cityName').val(),
				schoolName:$('#inline_schoolName').val(),
				educationDsc:$('#inline_educationDsc').val(),
				gradeDsc:$('#inline_gradeDsc').val(),
				classDsc:$('#inline_classDsc').val(),
				studentId:$('#inline_studentId').val(),
				sex:$('input[name=inline_sex]:checked').val(),
				swType:"insert",
				tNum:"<?php echo $getTnum;?>",
				tName:'<?php echo $teacher_name;?>'
				},
				type:"POST",
				error: function(xhr) {
					alert('Ajax request 發生錯誤');
				},
				success: function(response) {
					alert('新增學生成功!!');
					location.replace('memberListS.php?tNum=<?php echo base64_encode($getTnum);?>');
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


function del_student(get_num,unit_dsc){
	if(confirm("確定刪除下列名稱的學生資料嗎?\r\n"+unit_dsc)){
		$.ajax({
			url: './js_function/delfunction.php',
			data: {keyNum:get_num,tables:"<?php echo base64_encode("studentData");?>"},		
			error: function(xhr) {
				alert('Ajax request 發生錯誤');
			},
			success: function(response) {			
				alert('資料刪除成功!!');
				location.replace('memberListS.php?tNum=<?php echo base64_encode($getTnum);?>');
			}
		});
	}
}

//學生姓名搜尋
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
				location.replace('?tNum=<?php echo base64_encode($getTnum);?>&s_word='+response+'&s_type='+$('#search_type').val());
			}
		});
	}
}

//顯示新增學生資料的視窗
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

//編輯學生資料
function edit_student(getId){
	if(getId !=''){
		$.ajax({
			url: './js_function/student_data_controller.php',
			type:"POST",
			data: {getNum:getId,swType:"edit",tNum:"<?php echo $getTnum;?>"},		
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
				$('#edit_cityCode').val(contact.city_code) ;
				$('#edit_cityName').val(contact.city_name) ;
				$('#edit_schoolName').val(contact.school_name) ;
				$('#edit_educationDsc').val(contact.education_dsc) ;
				$('#edit_gradeDsc').val(contact.grade_dsc) ;
				$('#edit_classDsc').val(contact.class_dsc) ;
				$('#edit_studentId').val(contact.student_id) ;
				$('input[name=edit_sex]').each(function () {
					if($(this).val()==contact.sex_dsc){
						$(this).attr("checked","checked");
					}else{
						$(this).removeAttr("checked");
					}
				});
				
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
		error_dsc +="學生姓名欄位不得為空!!\r\n";
	}

	if(is_Go){
			$.colorbox.close();
			$.ajax({
			url: './js_function/student_data_controller.php',
			data: {
			num:$('#edit_num').val(),
			userName:$('#edit_name').val(),
			loginPw:$('#edit_pw').val(),
			swType:"update",
			tNum:"<?php echo $getTnum;?>",
			cityCode:$('#edit_cityCode').val(),
			cityName:$('#edit_cityName').val(),
			schoolName:$('#edit_schoolName').val(),
			educationDsc:$('#edit_educationDsc').val(),
			gradeDsc:$('#edit_gradeDsc').val(),
			classDsc:$('#edit_classDsc').val(),
			studentId:$('#edit_studentId').val(),
			sex:$('input[name=edit_sex]:checked').val()
			},
			type:"POST",
			error: function(xhr) {
				alert('Ajax request 發生錯誤');
			},
			success: function(response) {
				alert('學生資料修改成功!!');
				location.replace('memberListS.php?tNum=<?php echo base64_encode($getTnum);?><?php echo $url_dsc;?>');
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
    	<?php if($showMenu){?><li><a href="index.php" title="題目建置"><img src="images/icon_add.png" />題目建置</a></li><?php }?>
    	<?php if($showMenu){?><li><a href="testtime_list.php" title="題目清單管理"><img src="images/icon_add.png" />題目清單管理</a></li><?php }?>		
    	<?php if($showMenu){?><li><a href="science_list.php" title="科學模組"><img src="images/icon_science.png" />科學模組</a></li></li><?php }?>
    	<?php if($showMenu){?><li><a href="mathematics_list.php" title="數學模組"><img src="images/icon_math.png" />數學模組</a></li></li><?php }?>
    	<?php if($showMenu){?><li><a href="read_list.php" title="閱讀模組"><img src="images/icon_read.png" />閱讀模組</a></li></li><?php }?>
		<li><a href="../<?php echo $recordURL;?>.php" title="操作歷程瀏覽" target="_blank"><img src="images/icon_recording.png" />操作歷程瀏覽</a></li>
    	<li><a href="<?php echo $menu_array['url'];?>" title="<?php echo $menu_array['dsc'];?>"><img src="<?php echo $menu_array['imgurl'];?>" /><?php echo $menu_array['dsc'];?></a></li>
		<li><a href="logout.php" title="登出系統" ><img src="images/icon_logout.png" />登出系統</a></li>
		
    </ul>
</aside>
<div id="wrapper">
	<!--列表標題-->
    <div class="search">
		<select id="search_type" >
			<option value="byName" <?php if($_GET['s_type']=='byName'){echo "selected";}?> >學生姓名搜尋：</option>
			<option value="byLoginName" <?php if($_GET['s_type']=='byLoginName'){echo "selected";}?>>登入帳號搜尋：</option>		
		</select>
		<input type="text" name="s_word" id="s_word" value="<?php echo $_GET['s_word'];?>" >
		<a class="button" title="姓名搜尋：" onclick="search_word()">搜尋</a>
		<a href="memberListS.php?tNum=<?php echo base64_encode($getTnum);?>" class="button" title="清除搜尋" >清除搜尋</a>
	</div>    
	<ul>
    	<li><a class="button" title="新增學生" onclick="showAddWindow()">新增學生</a></li>
    </ul>    
    <table class="title">
        <tr>
            <td width="25%">登入帳號</td>
            <td width="25%">學生姓名</td>
            <td width="50%">編輯</td>
        </tr>    
		<?php for($x=0;$x<count($sql_data_array);$x++){	?>
		<tr>
			<td width="25%"><?php echo $sql_data_array[$x]['loginId'];?></td>
			<td width="25%"><?php echo $sql_data_array[$x]['c_name'];?></td>
			<td width="50%">
				<a class="button" title="資料編輯" onclick="edit_student('<?php echo base64_encode($sql_data_array[$x]['num']);?>')">資料編輯</a>                
				<a class="button" title="刪除" onclick="del_student('<?php echo base64_encode($sql_data_array[$x]['num']); ?>','<?php echo $sql_data_array[$x]['c_name']; ?>')">刪除</a>
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
        <li><span>縣市代碼：</span><input type="text" id="inline_cityCode" value="" size="40"></li>
        <li><span>縣市名：</span><input type="text" id="inline_cityName" value="" size="40"></li>
        <li><span>學校名稱：</span><input type="text" id="inline_schoolName" value="" size="40"></li>
        <li><span>國、高中：</span><input type="text" id="inline_educationDsc" value="" size="40"></li>
        <li><span>授課年級：</span><input type="text" id="inline_gradeDsc" value="" size="40"></li>
        <li><span>授課班級：</span><input type="text" id="inline_classDsc" value="" size="40"></li>
        <li><span>學生學號：</span><input type="text" id="inline_studentId" value="" size="40"></li>
        <li><span>學生性別：</span><input type="radio" id="inline_sex_0" name="inline_sex" value="女"  checked ><label for="inline_sex_0">女</label><input type="radio" id="inline_sex_1" name="inline_sex" value="男" ><label for="inline_sex_1">男</label></li>
        <li><span>學生姓名：</span><input type="text" id="inline_name" value="" size="40"></li>
    </ul>
    <a class="button" onclick="add_user()">新增學生資料</a>	<a class="button" onclick="$.colorbox.close();">關閉視窗</a>
</div>
<div id="inline_edituser"  style="display:none;">
<!--table更改為ul排列-->
    <ul class="name">
        <li><span>登入帳號：</span><span id="edit_login_name"></span></li>
        <li><span>登入密碼：</span><input type="password" id="edit_pw" value="" size="40"></li>
        <li><span>登入密碼確認：</span><input type="password" id="edit_pw_1" value="" size="40"></li>
        <li><span>縣市代碼：</span><input type="text" id="edit_cityCode" value="" size="40"></li>
        <li><span>縣市名：</span><input type="text" id="edit_cityName" value="" size="40"></li>
        <li><span>學校名稱：</span><input type="text" id="edit_schoolName" value="" size="40"></li>
        <li><span>國、高中：</span><input type="text" id="edit_educationDsc" value="" size="40"></li>
        <li><span>授課年級：</span><input type="text" id="edit_gradeDsc" value="" size="40"></li>
        <li><span>授課班級：</span><input type="text" id="edit_classDsc" value="" size="40"></li>
        <li><span>學生學號：</span><input type="text" id="edit_studentId" value="" size="40"></li>
        <li><span>學生性別：</span><input type="radio" id="edit_sex_0" name="edit_sex" value="女"  checked ><label for="edit_sex_0">女</label><input type="radio" id="edit_sex_1" name="edit_sex" value="男" ><label for="edit_sex_1">男</label></li>		
        <li><span>學生姓名：</span><input type="text" id="edit_name" value="" size="40"></li>
    </ul>
    <a class="button" onclick="updateUserData()">確定修改</a> 	<a class="button" onclick="$.colorbox.close();">關閉視窗</a>
	<input type="hidden" id="edit_num" value="" >	
</div>
</body>
</html>
