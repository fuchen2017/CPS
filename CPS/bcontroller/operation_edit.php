<?php
	session_start();
  //包含需求檔案 ------------------------------------------------------------------------
	include("./class/common_lite.php");
	if($_SESSION['loginType'] == ''){
		ri_jump("logout.php");
	}

	$_SESSION['ckfiner_key'] = 'get_key';
	$_SESSION['dirroot'] =  "/ntcu-speech-test/bcontroller";
	print_r($_SESSION);
	//解碼
	foreach($_GET as $key => $value){
		$_GET[$key] = base64_decode($value);
	}


	if($_GET['o_num']=='' || !is_numeric($_GET['o_num']) || $_GET['o_t_num']=='' || !is_numeric($_GET['o_t_num'])){
		ri_jump("logout.php");
	}

	//宣告變數 ----------------------------------------------------------------------------
	$ODb = new run_db("mysql",3306);      //建立資料庫物件

	//變數宣告區
	$whereDscArray = array();
	$dsc_2_arrray = array();
	$help_robot_dsc_3 = array();
	$hide_module_area3 = '';
	$hideModuleBtnDsc3 = '';
	$module_0_array = array();
	$module_1_array = array();
	$module_2_array = array();
	  $hideModuleBtnDsc1 = '';

	if($_GET['o_t_num']>0){
	$del_num = base64_encode($_GET['o_t_num']-1);
	}else{
	$del_num = base64_encode(0);
	}

	if($_SESSION['loginType'] == "TEACHER"){
		$whereDscArray[] = " and `a`.`create_user`='".$_SESSION['swTeacherNum']."' and `a`.`create_user_type`='TEACHER' ";
	}

	$where_dsc = "";
	for($x=0;$x<count($whereDscArray);$x++)
	{
		if($where_dsc == ""){
			$where_dsc = $whereDscArray[$x];
		}else{
			$where_dsc .= $whereDscArray[$x];
		}
	}


	//取出單元資料與作業資料
	$sql_dsc = "
	select `a`.`num`,`a`.`main_data_num`,`a`.`c_title` as `op_dsc`,`b`.`num`,`b`.`c_title` as `main_dsc`,`b`.`c_module_type`
	from `operation_data` as `a`
	left join `main_data` as `b` on `b`.`num`=`a`.`main_data_num`
	where `a`.`num`='".$_GET['o_num']."'".$where_dsc;
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	if(mysql_num_rows($res)==0){
		ri_jump("logout.php");
	}
	while($row = mysql_fetch_array($res)){
		$op_dsc = $row['op_dsc'];//作業名稱
		$main_dsc = $row['main_dsc'];//單元名稱
		$user_module_type = $row['c_module_type'];//模組類別
		switch($user_module_type){
			case "science":
				//科學模組select的option選項
				$module_select_dsc = array(
				"s_one"=>"選擇溶質介面",
				"s_two"=>"選擇溶質公克數介面",
				"s_three"=>"量測冷劑溫度介面",
				"s_four"=>"量測溶質公克數介面",
				"s_five"=>"實驗結果分享",
				"s_six"=>"物件敘述、結果顯示",
				"s_seven"=>"水淨化作業",
				"s_8"=>"定滑輪",
				"s_9"=>"動滑輪",
				"s_10"=>"定滑輪加動滑輪",
				"s_11"=>"倒水模組",
				"s_12"=>"笛卡爾模組",
				"s_14"=>"新水淨化作業模組",
				"s_15"=>"科學互動模組",
				);
			break;
			case "mathematics":
				//數學模組select的option選項
				$module_select_dsc = array(
				"m_one"=>"數學模組",
				"m_two"=>"20遊戲模組(練習)",
				"m_three"=>"20遊戲模組",
				"m_4"=>"溫度計猜公式模組 1",
				"m_5"=>"溫度計猜公式模組 2",
				"m_6"=>"溫度計猜公式模組 3",

				);
			break;
			case "read":
				//閱讀模組select的option選項
				$module_select_dsc = array(
				"r_one"=>"繪畫板模組",
				"r_two"=>"關連對應模組",
				"r_three"=>"物件順序調整模組",
				"r_four"=>"簡答題模組"
				);
			break;
		}

	}



	//取出試題的資料
	$sql_dsc = "select * from `questions_data` where `operation_data_num`='".$_GET['o_num']."' limit ".$_GET['o_t_num'].",1 ";
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	if(mysql_num_rows($res) == 1){//有試題則取出資料
		while($row = mysql_fetch_array($res)){
			$getID = base64_encode($row['num']);//試題的key
			$questions_dsc = $row['c_dsc'];//試題的對話資料
		}

		//取出對話資料
		$sql_dsc = "select * from `speak_data` where `questions_data_num`='".base64_decode($getID)."' order by `num` asc";
		$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
		while($row = mysql_fetch_array($res)){
			if($row['c_user_type']==0){//使用者
				$user_data['c_dsc'] = $row['c_dsc'];//對話內容
				$user_data['c_power_dsc'] = $row['c_power_dsc'];//能力敘述
				$user_data['c_power_number'] = $row['c_power_number'];//能力指數
				$user_data['pc_serial'] = $row['pc_serial'];//電腦序號
				if($row['c_dsc_type']==0){
					$dsc_0_arrray[] =$user_data;
				}
				if($row['c_dsc_type']==1){
					$dsc_1_arrray[] =$user_data;
				}
				if($row['c_dsc_type']==2){
					$dsc_2_arrray[] =$user_data;
				}
				if($row['c_dsc_type']==3){
					$dsc_3_arrray[] =$user_data;
				}

			}

			if($row['c_user_type']==1){//pc
				if($row['c_dsc_type'] == 0){
					$pc_data['new_pc_type_value'] = $row['new_pc_type_value']; //2015-11-10
					$pc_data['c_sw_type'] = $row['c_sw_type'];
					$pc_data['c_dsc'] = $row['c_dsc'];
					$pc_data['pc_serial'] = $row['pc_serial'];
					$pc_data['speech_del_time'] = $row['speech_del_time'];
					$pc_data['c_head_type'] = $row['c_head_type'];
					$pc_data['c_head_name'] = $row['c_head_name'];
					$pc_data['mp3_path'] = $row['mp3_path'];
					$pc_data0_array[]=$pc_data;
				}//會話1
				if($row['c_dsc_type'] == 1){
					$pc_data['new_pc_type_value'] = $row['new_pc_type_value']; //2015-11-10
					$pc_data['c_sw_type'] = $row['c_sw_type'];
					$pc_data['c_dsc'] = $row['c_dsc'];
					$pc_data['pc_serial'] = $row['pc_serial'];
					$pc_data['speech_del_time'] = $row['speech_del_time'];
					$pc_data['c_head_type'] = $row['c_head_type'];
					$pc_data['c_head_name'] = $row['c_head_name'];
					$pc_data['mp3_path'] = $row['mp3_path'];
					$pc_data1_array[]=$pc_data;
				}//會話2
				if($row['c_dsc_type'] == 2){
					$pc_data['new_pc_type_value'] = $row['new_pc_type_value']; //2015-11-10
					$pc_data['c_sw_type'] = $row['c_sw_type'];
					$pc_data['c_dsc'] = $row['c_dsc'];
					$pc_data['pc_serial'] = $row['pc_serial'];
					$pc_data['speech_del_time'] = $row['speech_del_time'];
					$pc_data['c_head_type'] = $row['c_head_type'];
					$pc_data['c_head_name'] = $row['c_head_name'];
					$pc_data['mp3_path'] = $row['mp3_path'];
					$pc_data2_array[]=$pc_data;
				}//會話3
				if($row['c_dsc_type'] == 3){
					$pc_data['new_pc_type_value'] = $row['new_pc_type_value']; //2015-11-10
					$pc_data['c_sw_type'] = $row['c_sw_type'];
					$pc_data['c_dsc'] = $row['c_dsc'];
					$pc_data['pc_serial'] = $row['pc_serial'];
					$pc_data['speech_del_time'] = $row['speech_del_time'];
					$pc_data['c_head_type'] = $row['c_head_type'];
					$pc_data['c_head_name'] = $row['c_head_name'];
					$pc_data['mp3_path'] = $row['mp3_path'];
					$pc_data3_array[]=$pc_data;
				}//會話4
			}
			if($row['c_user_type']==3){//自動進行
				if($row['c_dsc_type'] == 0){
					$auto_run_dsc_0=$row['c_dsc'];
					$auto_run_num_0=$row['speech_del_time'];
					$auto_run_head_0=$row['c_head_type'];
					$auto_run_name_0=$row['c_head_name'];
					$auto_run_PCSerial_0=$row['pc_serial'];
					$auto_run_mp3_path_0 = $row['mp3_path'];

				}//會話1
				if($row['c_dsc_type'] == 1){
					$auto_run_dsc_1=$row['c_dsc'];
					$auto_run_num_1=$row['speech_del_time'];
					$auto_run_head_1=$row['c_head_type'];
					$auto_run_name_1=$row['c_head_name'];
					$auto_run_PCSerial_1=$row['pc_serial'];
					$auto_run_mp3_path_1 = $row['mp3_path'];

				}//會話2
				if($row['c_dsc_type'] == 2){
					$auto_run_dsc_2=$row['c_dsc'];
					$auto_run_num_2=$row['speech_del_time'];
					$auto_run_head_2=$row['c_head_type'];
					$auto_run_name_2=$row['c_head_name'];
					$auto_run_PCSerial_2=$row['pc_serial'];
					$auto_run_mp3_path_2 = $row['mp3_path'];

				}//會話3
				if($row['c_dsc_type'] == 3){
					$auto_run_dsc_3=$row['c_dsc'];
					$auto_run_num_3=$row['speech_del_time'];
					$auto_run_head_3=$row['c_head_type'];
					$auto_run_name_3=$row['c_head_name'];
					$auto_run_PCSerial_3=$row['pc_serial'];
					$auto_run_mp3_path_3 = $row['mp3_path'];

				}//會話4
			}
			if($row['c_user_type']==2){//梅林
				if($row['c_dsc_type'] == 0){$help_robot_dsc_0=$row['c_dsc'];}//會話1
				if($row['c_dsc_type'] == 1){$help_robot_dsc_1=$row['c_dsc'];}//會話2
				if($row['c_dsc_type'] == 2){$help_robot_dsc_2=$row['c_dsc'];}//會話3
				if($row['c_dsc_type'] == 3){$help_robot_dsc_3=$row['c_dsc'];}//會話4
			}
		}

		//取出設計好的模組
		$sql_dsc = "";
		switch($user_module_type){
			case "science"://科學
			$sql_dsc = "
			select `a`.*,`b`.`num` as `bnum`,`b`.`c_type`,`b`.`c_short_img`
			from `speak_usemodule_data` as `a`
			left join `science_module_list` as `b` on `b`.`num` = `a`.`module_num`
			where `a`.`questions_data_num`='".base64_decode($getID)."' order by `a`.`c_dsc_type` ASC";
			break;
			case "mathematics"://數學
			$sql_dsc = "
			select `a`.*,`b`.`num` as `bnum`,`b`.`c_type`,`b`.`c_short_img`
			from `speak_usemodule_data` as `a`
			left join `mathematics_module_list` as `b` on `b`.`num` = `a`.`module_num`
			where `a`.`questions_data_num`='".base64_decode($getID)."' order by `a`.`c_dsc_type` ASC";
			break;
			case "read"://閱讀
			$sql_dsc = "
			select `a`.*,`b`.`num` as `bnum`,`b`.`c_type`,`b`.`c_short_img`
			from `speak_usemodule_data` as `a`
			left join `read_module_list` as `b` on `b`.`num` = `a`.`module_num`
			where `a`.`questions_data_num`='".base64_decode($getID)."' order by `a`.`c_dsc_type` ASC";
			break;
		}
		if($sql_dsc !=''){
			$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
			while($row = mysql_fetch_array($res)){
				if($row['c_dsc_type']==0){
					$module_0_array['module_num'] = $row['module_num'];
					$module_0_array['ckedit_dsc'] = $row['ckedit_dsc'];
					$module_0_array['ckedit_dsc_memo'] = $row['ckedit_dsc_memo'];
					$module_0_array['c_short_img'] = $row['c_short_img'];
					$module_0_array['c_type'] = $row['c_type'];
					$module_0_array['btn_dsc'] = $row['btn_dsc'];
					$module_0_array['pc_serial'] = $row['pc_serial'];
					$module_0_array['c_short_img'] = $row['c_short_img'];//縮圖陣列
					$module_0_array['li_dsc'] = get_shortimg_data($user_module_type,$row['c_type'],0);
					$module_0_array['warning_time'] = $row['warning_time'];
					$module_0_array['warning_dsc'] = $row['warning_dsc'];
				}
				if($row['c_dsc_type']==1){
					$module_1_array['module_num'] = $row['module_num'];
					$module_1_array['ckedit_dsc'] = $row['ckedit_dsc'];
					$module_1_array['ckedit_dsc_memo'] = $row['ckedit_dsc_memo'];
					$module_1_array['c_short_img'] = $row['c_short_img'];
					$module_1_array['c_type'] = $row['c_type'];
					$module_1_array['btn_dsc'] = $row['btn_dsc'];
					$module_1_array['pc_serial'] = $row['pc_serial'];
					$module_1_array['c_short_img'] = $row['c_short_img'];//縮圖陣列
					$module_1_array['li_dsc'] = get_shortimg_data($user_module_type,$row['c_type'],1);
					$module_1_array['warning_time'] = $row['warning_time'];
					$module_1_array['warning_dsc'] = $row['warning_dsc'];
				}
				if($row['c_dsc_type']==2){
					$module_2_array['module_num'] = $row['module_num'];
					$module_2_array['ckedit_dsc'] = $row['ckedit_dsc'];
					$module_2_array['ckedit_dsc_memo'] = $row['ckedit_dsc_memo'];
					$module_2_array['c_short_img'] = $row['c_short_img'];
					$module_2_array['c_type'] = $row['c_type'];
					$module_2_array['btn_dsc'] = $row['btn_dsc'];
					$module_2_array['pc_serial'] = $row['pc_serial'];
					$module_2_array['c_short_img'] = $row['c_short_img'];//縮圖陣列
					$module_2_array['li_dsc'] = get_shortimg_data($user_module_type,$row['c_type'],2);
					$module_2_array['warning_time'] = $row['warning_time'];
					$module_2_array['warning_dsc'] = $row['warning_dsc'];
				}
				if($row['c_dsc_type']==3){
					$module_3_array['module_num'] = $row['module_num'];
					$module_3_array['ckedit_dsc'] = $row['ckedit_dsc'];
					$module_3_array['ckedit_dsc_memo'] = $row['ckedit_dsc_memo'];
					$module_3_array['c_short_img'] = $row['c_short_img'];
					$module_3_array['c_type'] = $row['c_type'];
					$module_3_array['btn_dsc'] = $row['btn_dsc'];
					$module_3_array['pc_serial'] = $row['pc_serial'];
					$module_3_array['c_short_img'] = $row['c_short_img'];//縮圖陣列
					$module_3_array['li_dsc'] = get_shortimg_data($user_module_type,$row['c_type'],2);
					$module_3_array['warning_time'] = $row['warning_time'];
					$module_3_array['warning_dsc'] = $row['warning_dsc'];
				}
			}
		}

	}else{//尚無試題則新增一筆
		$nowdate =  date("Y-m-d H:i",time());
		$up_dsc ="
		insert into `questions_data` set
		`operation_data_num`='".$_GET['o_num']."',
		`up_date`='".$nowdate."'".$img_sql;
		$res=$ODb->query($up_dsc) or die("更新資料出錯，請聯繫管理員。");
		$getID = mysql_insert_id();

		/*
			新增梅林與自動執行的對話
		*/
		for($x=0;$x<4;$x++){
			$sql = "insert into `speak_data` set `questions_data_num`='".$getID."',`c_dsc_type`=".$x.",`c_user_type`=2,`up_date`='".$nowdate."'";
			$res=$ODb->query($sql) or die("更新資料出錯，請聯繫管理員。");
		}
		for($x=0;$x<4;$x++){
			$sql = "insert into `speak_data` set `questions_data_num`='".$getID."',`c_dsc_type`=".$x.",`c_user_type`=3,`up_date`='".$nowdate."'";
			$res=$ODb->query($sql) or die("更新資料出錯，請聯繫管理員。");
		}


		/*
			新增階段的對話
		*/
		for($x=0;$x<4;$x++){
			$sql = "insert into `step_dsc_data` set `questions_data_num`='".$getID."',`c_sw_type`=".$x.",`up_date`='".$nowdate."',`hideModuleBtnDsc`='確認'";
			$res=$ODb->query($sql) or die("更新資料出錯，請聯繫管理員。");
		}


		$getID = base64_encode($getID);
	}

	//取出階段說明資料
	$step_dsc_0="";
	$step_dsc_1="";
	$step_dsc_2="";
	$step_dsc_3="";

	$sql_dsc="select * from `step_dsc_data` where `questions_data_num`='".base64_decode($getID)."'";
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){
		if($row['c_sw_type'] ==0){
		$step_dsc_0=$row['c_dsc'];
		$hide_module_area0=$row['hide_module_area'];
		if($row['hideModuleBtnDsc']==''){
			$hideModuleBtnDsc0="確認";
		}else{
			$hideModuleBtnDsc0=$row['hideModuleBtnDsc'];
		}
		}
		if($row['c_sw_type'] ==1){
		$step_dsc_1=$row['c_dsc'];
		$hide_module_area1=$row['hide_module_area'];
		if($row['hideModuleBtnDsc']==''){
			$hideModuleBtnDsc1="確認";
		}else{
			$hideModuleBtnDsc1=$row['hideModuleBtnDsc'];
		}
		}
		if($row['c_sw_type'] ==2){
		$step_dsc_2=$row['c_dsc'];
		$hide_module_area2=$row['hide_module_area'];
		if($row['hideModuleBtnDsc']==''){
			$hideModuleBtnDsc2="確認";
		}else{
			$hideModuleBtnDsc2=$row['hideModuleBtnDsc'];
		}
		}
		if($row['c_sw_type'] ==3){
		$step_dsc_3=$row['c_dsc'];
		$hide_module_area3=$row['hide_module_area'];
		if($row['hideModuleBtnDsc']==''){
			$hideModuleBtnDsc3="確認";
		}else{
			$hideModuleBtnDsc3=$row['hideModuleBtnDsc'];
		}
		}
	}


	//計算總試題數量
	$sql_dsc = "select count(*) as `g_number` from `questions_data` where `operation_data_num`='".$_GET['o_num']."'";
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){
		if($row['g_number'] > ($_GET['o_t_num']+1)){
		$button_dsc = "下一個試題";
		}else{
		$button_dsc = "新增試題";
		}
	}

//用來取得模組的縮圖
function get_shortimg_data($typeDsc,$moduleType,$typenumber){
	$ODb = new run_db("mysql",3306);      //建立資料庫物件

	$html_dsc ="";

	switch($typeDsc){
	case "science":
	$table = "science_module_list";
	break;
	case "mathematics":
	$table = "mathematics_module_list";
	break;
	case "read":
	$table = "read_module_list";
	break;
	default:
	$table="";
	break;
	}
	if($table !=''){
		$up_dsc ="select * from `".$table."` where `c_type`='".$moduleType."'";
		$res = $ODb->query($up_dsc) or die("更新資料出錯，請聯繫管理員。");
		while($row = mysql_fetch_array($res)){
			$html_dsc.='<li><div class="model"><img src="./shortImg/'.$row['c_short_img'].'"></div><a  class="button" title="'.$row['c_title'].'" onclick="sw_shortimg(\''.$row['c_short_img'].'\','.$typenumber.','.$row['num'].')">'.$row['c_title'].'</a></li>';
		}
	}else{
		return '';
	}
	return $html_dsc;
}

	$sw_tab=0;
	if(isset($_GET['sw_tab'])){
		$sw_tab=$_GET['sw_tab'];
	}


	$abilityArray = array(//能力指標選項
	'0'=>'(1) 分析與批判思考',
	'1'=>'(2) 互動力',
	'2'=>'(3) 同理心',
	'3'=>'(4) 彈性力',
	'4'=>'(5) 全球議題的知識與理解',
	'5'=>'(6) 跨文化的知識與理解',


	);

	$nextArray = array(//下一步驟選項
	'0'=>array(	'0'=>'對話2','1'=>'對話3','5'=>'對話4','2'=>'下一個試題','3'=>'下一個作業','4'=>'結束單元'),
	'1'=>array(	'6'=>'對話1','1'=>'對話3','5'=>'對話4','2'=>'下一個試題','3'=>'下一個作業','4'=>'結束單元'),
	'2'=>array(	'6'=>'對話1','0'=>'對話2','5'=>'對話4','2'=>'下一個試題','3'=>'下一個作業','4'=>'結束單元'),
	'3'=>array(	'6'=>'對話1','0'=>'對話2','1'=>'對話3','2'=>'下一個試題','3'=>'下一個作業','4'=>'結束單元')

	);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>合作問題解決數位學習系統</title>
<link rel="stylesheet" href="css/edit.css" />
<script src="./js/jquery-1.10.1.min.js"> </script>
<link rel="stylesheet" href="css/jcarousel.responsive.css">
<script src="js/jquery.jcarousel.min.js"></script>
<script src="js/jcarousel.responsive.js"></script>
<script src="js/ckeditor/ckeditor.js" type="text/javascript" ></script>

<script>
$(function(){
	// 預設顯示第一個 Tab
	var _showTab = <?php echo $sw_tab;?>;
	var $defaultLi = $('ul.tabs li').eq(_showTab).addClass('active');
	$($defaultLi.find('a').attr('href')).siblings().hide();

	// 當 li 頁籤被點擊時...
	// 若要改成滑鼠移到 li 頁籤就切換時, 把 click 改成 mouseover
	$('ul.tabs li').click(function() {
		// 找出 li 中的超連結 href(#id)
		var $this = $(this),
			_clickTab = $this.find('a').attr('href');
		// 把目前點擊到的 li 頁籤加上 .active
		// 並把兄弟元素中有 .active 的都移除 class
		$this.addClass('active').siblings('.active').removeClass('active');
		// 淡入相對應的內容並隱藏兄弟元素
		$(_clickTab).stop(false, true).fadeIn().siblings().hide();

		return false;
	}).find('a').focus(function(){
		this.blur();
	});
	for(var x=0,y=1;x<4;x++,y++){
	if(x==<?php echo $sw_tab;?>){
	$('#img_area_'+x).show();
	$('#tab'+y).show();
	}else{
	$('#img_area_'+x).hide();
	$('#tab'+y).hide();
	}

	}

});

//存檔
function save_q(){
	$('#controller_type').val("save");
	$('#area_0_index').val(area_0_index);
	$('#area_1_index').val(area_1_index);
	$('#area_2_index').val(area_2_index);
	$('#area_3_index').val(area_3_index);
	$('#pcarea_0_index').val(pcarea_0_index);
	$('#pcarea_1_index').val(pcarea_1_index);
	$('#pcarea_2_index').val(pcarea_2_index);
	$('#pcarea_3_index').val(pcarea_3_index);
	$('#form').submit();
}

//回上一個試題
function go_back_q(){
	location.href="operation_edit.php?o_num=<?php echo base64_encode($_GET['o_num']);?>&o_t_num=<?php echo base64_encode($_GET['o_t_num']-1);?>";
}

//下一個試題
function go_next_q(){
	location.href="operation_edit.php?o_num=<?php echo base64_encode($_GET['o_num']);?>&o_t_num=<?php echo base64_encode($_GET['o_t_num']+1);?>";
}

//刪除試題
function del_q(){
	if(confirm("確定是否刪除本試題的資料內容嗎?")){
		$.ajax({
			url: './js_function/delfunction.php',
			data: {keyNum:"<?php echo $getID;?>",tables:"<?php echo base64_encode("questions_data");?>"},
			error: function(xhr) {
				alert('Ajax request 發生錯誤');
			},
			success: function(response) {
				alert('資料刪除成功!!');
				location.href="operation_edit.php?o_num=<?php echo base64_encode($_GET['o_num']);?>&o_t_num=<?php echo $del_num;?>";
			}
		});
	}
}

var area_0_index = <?php if(is_array($dsc_0_arrray)){echo count($dsc_0_arrray);}else{echo '2';}?>;
var area_1_index = <?php if(is_array($dsc_1_arrray)){echo count($dsc_1_arrray);}else{echo '2';}?>;
var area_2_index = <?php if(is_array($dsc_2_arrray)){echo count($dsc_2_arrray);}else{echo '2';}?>;
var area_3_index = <?php if(is_array($dsc_3_arrray)){echo count($dsc_3_arrray);}else{echo '2';}?>;

//新增使用者對話
function add_area(getIndex){
	var cIndex=0;
	switch(getIndex){
		case 0:
		cIndex = area_0_index;
		area_0_index++;
		break;
		case 1:
		cIndex = area_1_index;
		area_1_index++;
		break;
		case 2:
		cIndex = area_2_index;
		area_2_index++;
		break;
		case 3:
		cIndex = area_3_index;
		area_3_index++;
		break;
		default:
		break;
	}

	var html_dsc = '<div class="chatbox" id="area_'+getIndex+'_'+cIndex+'"><ul><li>內容<br /> <input name="dsc_'+getIndex+'_'+cIndex+'" value="" /></li><li>能力指標<br /><select name="powerdsc_'+getIndex+'_'+cIndex+'"><option value="">無</option><?php
					foreach($abilityArray as $mykey => $myvalue){
					echo '<option value="'.$mykey.'">'.$myvalue.'</option>';
					}
					?></select></li><li>分數<br /> <input type="number" min="0" size="3" name="powernumber_'+getIndex+'_'+cIndex+'" value="0" /></li><li>電腦對話序號<br /><input type="text" name="pcserial_'+getIndex+'_'+cIndex+'" value=""></li><li><a title="刪除" onclick="$(\'#area_'+getIndex+'_'+cIndex+'\').remove();"><img src="images/icon_delet.png" alt="刪除" /></a></li></ul></div>';
	switch(getIndex){
		case 0:
		$('#add_0').before(html_dsc);
		break;
		case 1:
		$('#add_1').before(html_dsc);
		break;
		case 2:
		$('#add_2').before(html_dsc);
		break;
		case 3:
		$('#add_3').before(html_dsc);
		break;
		default:
		break;
	}
}


var pcarea_0_index = <?php if(count($pc_data0_array)>1){echo count($pc_data0_array);}else{echo '2';}?>;
var pcarea_1_index = <?php if(count($pc_data1_array)>1){echo count($pc_data1_array);}else{echo '2';}?>;
var pcarea_2_index = <?php if(count($pc_data2_array)>1){echo count($pc_data2_array);}else{echo '2';}?>;
var pcarea_3_index = <?php if(count($pc_data3_array)>1){echo count($pc_data3_array);}else{echo '2';}?>;

//新增電腦對話
function add_pcarea(getIndex){
//下一步驟選項
var pcareaSwDsc0 ='<option value="0">對話2</option><option value="1">對話3</option><option value="5">對話4</option><option value="2">下一個試題</option><option value="3">下一個作業</option><option value="4">結束單元</option>';
var pcareaSwDsc1 ='<option value="6">對話1</option><option value="1">對話3</option><option value="5">對話4</option><option value="2">下一個試題</option><option value="3">下一個作業</option><option value="4">結束單元</option>';
var pcareaSwDsc2 ='<option value="6">會話1</option><option value="0">會話2</option><option value="5">會話4</option><option value="2">下一個試題</option><option value="3">下一個作業</option><option value="4">結束單元</option>';
var pcareaSwDsc3 ='<option value="6">會話1</option><option value="0">會話2</option><option value="1">會話3</option><option value="2">下一個試題</option><option value="3">下一個作業</option><option value="4">結束單元</option>';
var cIndex=0;
var pcareaSwDsc="";
switch(getIndex){
		case 0:
		cIndex = pcarea_0_index;
		pcarea_0_index++;
		pcareaSwDsc = pcareaSwDsc0;
		break;
		case 1:
		cIndex = pcarea_1_index;
		pcarea_1_index++;
		pcareaSwDsc = pcareaSwDsc1;
		break;
		case 2:
		cIndex = pcarea_2_index;
		pcarea_2_index++;
		pcareaSwDsc = pcareaSwDsc2;
		break;
		case 3:
		cIndex = pcarea_3_index;
		pcarea_3_index++;
		pcareaSwDsc = pcareaSwDsc3;
		break;
		default:
		break;
}

	var html_dsc = '<div class="chatbox" id="pcarea_'+getIndex+'_'+cIndex+'"><ul><li>電腦對話序號<br /> <input name="robot_pcserial_'+getIndex+'_'+cIndex+'" value="" /></li><li>內容<br /> <input type="text" name="robot_dsc_'+getIndex+'_'+cIndex+'" id="robot_dsc_'+getIndex+'_'+cIndex+'" value="" /></li><li>下一步驟<br /><select name="robot_swtype_'+getIndex+'_'+cIndex+'">'+pcareaSwDsc+'</select></li><li>對話延遲時間(秒)<br /> <input type="number" name="robot_delTime_'+getIndex+'_'+cIndex+'" value="0" /></li><li><a title="刪除" onclick="$(\'#pcarea_'+getIndex+'_'+cIndex+'\').remove();"><img src="images/icon_delet.png" alt="刪除" /></a></li><li>頭像<br><select name="robot_head_'+getIndex+'_'+cIndex+'" id="robot_head_'+getIndex+'_'+cIndex+'"><option value="0">頭像1</option><option value="1">頭像2</option><option value="2">頭像3</option><option value="3">頭像4</option></select>頭像名稱：<input type="text" name="robot_head_name_'+getIndex+'_'+cIndex+'" id="robot_head_name_'+getIndex+'_'+cIndex+'" value="" class="pchead_name"></li></ul></div>';
	switch(getIndex){
		case 0:
		$('#addpc_0').before(html_dsc);
		break;
		case 1:
		$('#addpc_1').before(html_dsc);
		break;
		case 2:
		$('#addpc_2').before(html_dsc);
		break;
		case 3:
		$('#addpc_3').before(html_dsc);
		break;
		default:
		break;
	}



}



//更改目前顯示的會話頁籤
function chg_tab(keynum,keynum2){
	for(var x=0,y=1;x<4;x++,y++){
		if(x==keynum){
			$('#img_area_'+x).show();
			$('#tab'+y).show();
		}else{
			$('#img_area_'+x).hide();
			$('#tab'+y).hide();
		}
	}
	$('#sw_tab').val(keynum);
}

//取得使用者設計的模組縮圖html
function sw_pic(keynum){
var type_dsc = $('#module_sw_'+keynum).val();
var getKey = keynum;//會話1,2,3
if(type_dsc !=''){
	switch(type_dsc){
		case "ckedit":
		$('#module_show_area'+keynum).hide();
		$('#ckedit_area'+keynum).show();
		break;
		default:
		$('#module_area'+getKey).html('');
		$('#module_show_area'+getKey).show();
		$('#ckedit_area'+getKey).hide();
		$.ajax({
			url: './js_function/get_module_shortimg.php',
			type:"POST",
			data: {keyNum:getKey,c_type:type_dsc,typeDsc:"<?php echo $user_module_type;?>"},
			error: function(xhr) {
				alert('Ajax request 發生錯誤');
			},
			success: function(response) {
				if(response !=''){
					console.log(response);
					$('#module_area'+getKey).html(response);
				}
			}
		});
		break;
	}
}else{
	$('#module_show_area'+keynum).hide();
	$('#ckedit_area'+keynum).hide();
}
}

//更換會話區的縮圖
function sw_shortimg(c_short_img,keyNum,moduleNum){
	$('#sw_shortimg'+keyNum).html('');
	var html = '<img src="shortImg/'+c_short_img+'" width="500">';
	$('#sw_shortimg'+keyNum).append(html);
	$('#module_num'+keyNum).val(moduleNum);
}

//播放聲音
function showAudio(getPath){
	$('#playAudio').attr('src',getPath);
}
</script>
</head>
<body>
<form action="questions_data.php" method="post" enctype="multipart/form-data" id="form">
<table class="edit">
  <tr>
  	<!--標頭-->
    <th colspan="2">
    	<h1>單元：<?php echo $main_dsc;?></h1>
    	<h1>作業名稱：<?php echo $op_dsc;?></h1>
    	<h2>試題：試題<?php echo ($_GET['o_t_num']+1);?></h2>
    	<!--右側工具：能力指標，作答時間，刪除，新增試題-->
        <ul>
			<!--<li>能力指標說明<input type="text" size="25" /></li>
            <li>作答時間<input type="text" size="4" maxlength="3" />分</li>-->
            <li><a href="index.php" class="button_g" title="取消" >取消</a></li>
            <li><a class="button_g" title="存檔" onclick="save_q()">存檔</a></li>

			<?php
				if($_GET['o_t_num'] >0){
				echo '
				<li><a class="button" title="回上一試題" onclick="go_back_q()">回上一試題</a></li>
				';
				}
			?>
            <li><a class="button" title="<?php echo $button_dsc;?>" onclick="go_next_q()"><?php echo $button_dsc;?></a></li>
            <li><a class="button" title="刪除試題" onclick="del_q()">刪除試題</a></li>
        </ul>
    </th>
    <!--標頭end-->
  </tr>

  <tr>
    <td class="question" width="30%">
    <p>能力指標說明</p>
    <textarea name="questions_dsc" id="questions_dsc" ><?php echo $questions_dsc;?></textarea>
    </td>

<!--右上角讓使用者選擇模組的區域可以滑動的區塊-->
<td class="main" width="70%" rowspan="2">
<?php
$obj_array = array(
'0'=>array(
		'display_dsc'=>'',
		'module_array_data'=>$module_0_array
	),
'1'=>array(
		'display_dsc'=>'display:none;',
		'module_array_data'=>$module_1_array
	),
'2'=>array(
		'display_dsc'=>'display:none;',
		'module_array_data'=>$module_2_array
	),
'3'=>array(
		'display_dsc'=>'display:none;',
		'module_array_data'=>$module_3_array
	)

);


for($x=0,$y=1;$x<4;$x++,$y++)
{?>
	<div id="img_area_<?php echo $x;?>" style="<?php echo $obj_array[$x]['display_dsc'];?>">
		<h3>會話<?php echo $y;?>：模組種類
		<select name="module_sw_<?php echo $x;?>" id="module_sw_<?php echo $x;?>" onchange="sw_pic(<?php echo $x;?>)">
			<option value="">不使用模組</option>
			<option value="ckedit"  <?php if(isset($obj_array[$x]['module_array_data']['ckedit_dsc'])){echo "selected";}?>>文字編輯模組</option>
			<?php
				foreach($module_select_dsc as $key => $value)
				{
					if($obj_array[$x]['module_array_data']['c_type']==$key){
						$dsc="selected";
					}else
					{
						$dsc="";
					}
					echo '<option value="'.$key.'" '.$dsc.'>'.$value.'</option>';
				}
			?>
		</select>
		</h3>
	<!-- 選擇現有的模組區域-->
	<div id="module_show_area<?php echo $x;?>" <?php if(isset($obj_array[$x]['module_array_data']['c_type']) and ($obj_array[$x]['module_array_data']['c_type']=='' || $obj_array[$x]['module_array_data']['c_type']=='ckedit')){echo 'style="display:none;"';}?>>
	<div class="jcarousel-wrapper">
	<div class="jcarousel">
	<ul class="ope" id="module_area<?php echo $x;?>">
	<?php echo isset($obj_array[$x]['module_array_data']['li_dsc'])?$obj_array[$x]['module_array_data']['li_dsc']:'';?>
	</ul>
	</div>
	<a href="#" class="jcarousel-control-prev">&lsaquo;</a>
	<a href="#" class="jcarousel-control-next">&rsaquo;</a>
	</div>

	<!--修改：ul標籤命名、圖片置入li內-->
	<ul class="img_setting">
	<li class="image">
	<div id="sw_shortimg<?php echo $x;?>">
	<?php if($obj_array[$x]['module_array_data']['module_num'] !=''){
	echo '<img src="shortImg/'.$obj_array[$x]['module_array_data']['c_short_img'].'" width="500">';
	}?>
	</div>
	</li>
	<li>
	<span>按鈕文字</span><input type="text" class="c_title" placeholder="請輸按鈕文字" name="btn_dsc_<?php echo $x;?>" id="btn_dsc_<?php echo $x;?>" value="<?php echo $obj_array[$x]['module_array_data']['btn_dsc'];?>" >
	</li>
	<li>
	<span>電腦對話序號</span><input type="text" class="c_title" placeholder="請輸入電腦對話序號" name="pc_serial_<?php echo $x;?>" id="pc_serial_<?php echo $x;?>" value="<?php echo $obj_array[$x]['module_array_data']['pc_serial'];?>"   >
	</li>
	<li>
	<span>提示時間</span><input type="text" class="c_title" placeholder="請輸入提示時間" name="warning_time<?php echo $x;?>" id="warning_time<?php echo $x;?>" value="<?php echo $obj_array[$x]['module_array_data']['warning_time'];?>">
	</li>
	<li>
	<span>提示訊息</span><textarea name="warning_dsc<?php echo $x;?>" id="warning_dsc<?php echo $x;?>" rows=5 cols=20 ><?php echo $obj_array[$x]['module_array_data']['warning_dsc'];?></textarea>
	</li>
	</ul>
	<input type="hidden" name="module_num<?php echo $x;?>" id="module_num<?php echo $x;?>" value="<?php echo $obj_array[$x]['module_array_data']['module_num'];?>">
	</div>
	<!-- 選擇現有的模組區域 end -->
	<!-- 選擇ckedit -->
	<div id="ckedit_area<?php echo $x;?>" <?php if($obj_array[$x]['module_array_data']['ckedit_dsc']==''){echo 'style="display:none;"';}?>>
	<textarea name="c_ckedit<?php echo $x;?>" id="c_ckedit<?php echo $x;?>"><?php echo $obj_array[$x]['module_array_data']['ckedit_dsc'];?></textarea>
	<br><br>
	<p>提示搜尋說明</p>
	<textarea name="c_ckedit<?php echo $x;?>_memo" id="c_ckedit<?php echo $x;?>_memo"><?php echo $obj_array[$x]['module_array_data']['ckedit_dsc_memo'];?></textarea>

	</div>
	<!-- 選擇ckedit end -->
	</div>
<?php }?>
    </td>
	<!--右上角讓使用者選擇模組的區域  end	-->
  </tr>

  <tr>
  	<!--交談對話框-->
    <td class="talk">
    	<h3>交談</h3>
        <!--交談頁籤-->
        <div class="abgne_tab">
            <ul class="tabs">
                <li><a onclick="chg_tab(0,1)">會話1</a></li>
                <li><a onclick="chg_tab(1,2)">會話2</a></li>
                <li><a onclick="chg_tab(2,3)">會話3</a></li>
                <li><a onclick="chg_tab(3,4)">會話4</a></li>
            </ul>

            <div class="tab_container">
<?php
$objArrayTwo = array(
'0'=>array(
	'pcDataArray'=>$pc_data0_array,
	'dscArray' =>$dsc_0_arrray,
	'helpRobotDsc'=>$help_robot_dsc_0,
	'stepDsc'=>$step_dsc_0,
	'hideModuleArea'=>$hide_module_area0,
	'hideModuleBtnDsc'=>$hideModuleBtnDsc0,
	'autoRunDsc'=>$auto_run_dsc_0,
	'autoRunNum'=>$auto_run_num_0,
	'autoRunHead'=>$auto_run_head_0,
	'autoRunName'=>$auto_run_name_0,
	'autoRunPCSerial'=>$auto_run_PCSerial_0,
	'autoRunMP3'=>$auto_run_mp3_path_0
),
'1'=>array(
	'pcDataArray'=>$pc_data1_array,
	'dscArray' =>$dsc_1_arrray,
	'helpRobotDsc'=>$help_robot_dsc_1,
	'stepDsc'=>$step_dsc_1,
	'hideModuleArea'=>$hide_module_area1,
	'hideModuleBtnDsc'=>$hideModuleBtnDsc1,
	'autoRunDsc'=>$auto_run_dsc_1,
	'autoRunNum'=>$auto_run_num_1,
	'autoRunHead'=>$auto_run_head_1,
	'autoRunName'=>$auto_run_name_1,
	'autoRunPCSerial'=>$auto_run_PCSerial_1,
	'autoRunMP3'=>$auto_run_mp3_path_1
),
'2'=>array(
	'pcDataArray'=>$pc_data2_array,
	'dscArray' =>$dsc_2_arrray,
	'helpRobotDsc'=>$help_robot_dsc_2,
	'stepDsc'=>$step_dsc_2,
	'hideModuleArea'=>$hide_module_area2,
	'hideModuleBtnDsc'=>$hideModuleBtnDsc2,
	'autoRunDsc'=>$auto_run_dsc_2,
	'autoRunNum'=>$auto_run_num_2,
	'autoRunHead'=>$auto_run_head_2,
	'autoRunName'=>$auto_run_name_2,
	'autoRunPCSerial'=>$auto_run_PCSerial_2,
	'autoRunMP3'=>$auto_run_mp3_path_2

),
'3'=>array(
	'pcDataArray'=>$pc_data3_array,
	'dscArray' =>$dsc_3_arrray,
	'helpRobotDsc'=>$help_robot_dsc_3,
	'stepDsc'=>$step_dsc_3,
	'hideModuleArea'=>$hide_module_area3,
	'hideModuleBtnDsc'=>$hideModuleBtnDsc3,
	'autoRunDsc'=>$auto_run_dsc_3,
	'autoRunNum'=>$auto_run_num_3,
	'autoRunHead'=>$auto_run_head_3,
	'autoRunName'=>$auto_run_name_3,
	'autoRunPCSerial'=>$auto_run_PCSerial_3,
	'autoRunMP3'=>$auto_run_mp3_path_3

)

);

//產生4組會話輸入表格
for($mycount=0;$mycount<4;$mycount++){
?>
<div id="tab<?php echo $mycount+1;?>" class="tab_content" >
	<div class="chat">
		<h4>階段說明</h4>
		<input type="text" name="step_dsc_<?php echo $mycount;?>" id="step_dsc_<?php echo $mycount;?>" value="<?php echo $objArrayTwo[$mycount]['stepDsc'];?>" />
	</div>
	操作區隱藏模組
	<input type="checkbox" name="hide_module_area<?php echo $mycount;?>" id="hide_module_area<?php echo $mycount;?>" value="1" <?php if($objArrayTwo[$mycount]['hideModuleArea']==1){echo "checked";}?> />
	按鈕內容
	<input type="text" name="hideModuleBtnDsc<?php echo $mycount;?>" id="hideModuleBtnDsc<?php echo $mycount;?>" value="<?php echo $objArrayTwo[$mycount]['hideModuleBtnDsc'];?>"/>
<div class="chat">
	<h4>PC</h4>
	頭像<select name="robot_first_head_<?php echo $mycount;?>" id="robot_first_head_<?php echo $mycount;?>">
		<option value='0' <?php if($objArrayTwo[$mycount]['pcDataArray'][0]['c_head_type']==0){echo 'selected';};?>>頭像1</option>
		<option value='1' <?php if($objArrayTwo[$mycount]['pcDataArray'][0]['c_head_type']==1){echo 'selected';};?>>頭像2</option>
		<option value='2' <?php if($objArrayTwo[$mycount]['pcDataArray'][0]['c_head_type']==2){echo 'selected';};?>>頭像3</option>
		<option value='3' <?php if($objArrayTwo[$mycount]['pcDataArray'][0]['c_head_type']==3){echo 'selected';};?>>頭像4</option>
    <option value='4' <?php if($objArrayTwo[$mycount]['pcDataArray'][0]['c_head_type']==4){echo 'selected';};?>>頭像5</option>
	</select>
	頭像名稱：<input type="text" name="robot_first_head_name_<?php echo $mycount;?>" id="robot_first_head_name_<?php echo $mycount;?>" value="<?php echo $objArrayTwo[$mycount]['pcDataArray'][0]['c_head_name'];?>" class="pchead_name">
	<br>
	起始對話內容<br /> <input type="text" name="robot_first_dsc_<?php echo $mycount;?>" id="robot_first_dsc_<?php echo $mycount;?>" value="<?php echo $objArrayTwo[$mycount]['pcDataArray'][0]['c_dsc'];?>" />
	<br />
	語音檔案(mp3檔)<br />
	<input type="file" name="robot_first_mp3_path_<?php echo $mycount;?>" id="robot_first_mp3_path_<?php echo $mycount;?>" value=""  accept=".mp3">
<?php
//如果之前有上傳檔案，就顯示檔案資料跟播放模組
if( $objArrayTwo[$mycount]['pcDataArray'][0]['mp3_path'] > ''){
	?>
<div id="first_area_<?php echo $mycount;?>">
<input type="hidden" name="old_first_mp3_path_<?php echo $mycount;?>" value="<?php echo $objArrayTwo[$mycount]['pcDataArray'][0]['mp3_path'];?>" >
<img src="./images/baba.jpg" onclick="showAudio('<?php echo $objArrayTwo[$mycount]['pcDataArray'][0]['mp3_path'];?>')">
<input type="button" value="移除" onclick="$('#first_area_<?php echo $mycount;?>').remove();">

</div>
<?php
	}
?>
</div>
<!--PC 起始對話內容 End-->

<!--使用者-->
<div class="chat">
<h4>使用者</h4>
<?php
//會話1 使用者對話資料
if(is_array($objArrayTwo[$mycount]['dscArray'])){
	$y=0;
	foreach($objArrayTwo[$mycount]['dscArray'] as $value){
		?>
		<div class="chatbox" id="area_<?php echo $mycount;?>_<?php echo $y;?>">
			<ul>
				<li>內容<br /> <input name="dsc_<?php echo $mycount;?>_<?php echo $y;?>" value="<?php echo $value['c_dsc'];?>" /></li>
				<li>能力指標<br />
					<select name="powerdsc_<?php echo $mycount;?>_<?php echo $y;?>">
					<option value="">無</option>
					<?php
					foreach($abilityArray as $mykey => $myvalue){
					$power_sw="";
					if($value['c_power_dsc'] == $mykey && $value['c_power_dsc'] !=''){
						$power_sw="selected";
					}
					echo '<option value="'.$mykey.'" '.$power_sw.' >'.$myvalue.'</option>';
					}
					?>
					</select>
				</li>
				<li>分數<br /> <input type="number" min="3" size="3" name="powernumber_<?php echo $mycount;?>_<?php echo $y;?>" value="<?php echo $value['c_power_number'];?>" /></li>
				<li>電腦對話序號<br />
				<input type="text" name="pcserial_<?php echo $mycount;?>_<?php echo $y;?>" value="<?php echo $value['pc_serial'];?>">
				</li>
				<li><a title="刪除" onclick="$('#area_<?php echo $mycount;?>_<?php echo $y;?>').remove();"><img src="images/icon_delet.png" alt="刪除" /></a></li>
			</ul>
		</div>
<?php
		$y++;
	}
}else{
//會話1 使用者對話資料 預設值
?>
<div class="chatbox" id="area_<?php echo $mycount;?>_0">
	<ul>
		<li>內容<br /> <input name="dsc_<?php echo $mycount;?>_0" value="" /></li>
		<li>能力指標<br />
		<select name="powerdsc_<?php echo $mycount;?>_0">
		<option value="">無</option>
		<?php foreach($abilityArray as $key => $value){
		echo '<option value="'.$key.'">'.$value.'</option>';
		}?>
		</select>
		</li>
		<li>分數<br /> <input type="number" min="0" size="3" name="powernumber_<?php echo $mycount;?>_0" value="0" /></li>
		<li>電腦對話序號<br />
		<input type="text" name="pcserial_<?php echo $mycount;?>_0" value="">
		</li>
		<li><a title="刪除" onclick="$('#area_<?php echo $mycount;?>_0').remove();"><img src="images/icon_delet.png" alt="刪除" /></a></li>
	</ul>
</div>

<div class="chatbox" id="area_<?php echo $mycount;?>_1">
	<ul>
		<li>內容<br /> <input name="dsc_<?php echo $mycount;?>_1" value="" /></li>
		<li>能力指標<br />
		<select name="powerdsc_<?php echo $mycount;?>_1">
		<option value="">無</option>
		<?php foreach($abilityArray as $key => $value){
		echo '<option value="'.$key.'">'.$value.'</option>';
		}?>
		</select>
		</li>
		<li>分數<br /> <input type="number" min="0" size="3" name="powernumber_<?php echo $mycount;?>_1" value="0" /></li>
		<li>電腦對話序號<br />
		<input type="text" name="pcserial_<?php echo $mycount;?>_1" value="">
		</li>
		<li><a title="刪除" onclick="$('#area_<?php echo $mycount;?>_1').remove();"><img src="images/icon_delet.png" alt="刪除" /></a></li>
	</ul></div>
<?php
}
//會話1 使用者對話資料 end

?>


<p class="add" id="add_<?php echo $mycount;?>"><a class="button" title="新增" onclick="add_area(<?php echo $mycount;?>)">新增</a></p>
</div><!--使用者End-->

<!--PC-->
<div class="chat">
	<h4>PC</h4>
<?php
$new_type_array=['1'=>'Pump','2'=>'Hint','3'=>'Prompt','4'=>'Correction','5'=>'Teaching','6'=>'Next_Question'];
//2015-11-10 新增類別
//會話1 PC 對話資料 =>只有一組對話資料時
if(count($objArrayTwo[$mycount]['pcDataArray'])<2){
?>
	<div class="chatbox" id="pcarea_<?php echo $mycount;?>_0">
		<ul>
			<li>電腦對話序號<br /> <input name="robot_pcserial_<?php echo $mycount;?>_0" value="" /></li>
			<li>內容<br /> <input type="text" name="robot_dsc_<?php echo $mycount;?>_0" id="robot_dsc_<?php echo $mycount;?>_0" value="<?php echo $robot_dsc_0;?>" /></li>
			<li>所屬類別<br />
			<select name="new_type_array_<?php echo $mycount;?>_0">
			<?php
			foreach($new_type_array as $_Key => $_Value){
			echo '<option value="'.$_Key.'">'.$_Value.'</option>';
			}
			?>
			</select>
			</li>
			<li>下一步驟<br />
			<select name="robot_swtype_<?php echo $mycount;?>_0">
			<?php
			foreach($nextArray[$mycount] as $myKey => $myValue){
			echo '<option value="'.$myKey.'">'.$myValue.'</option>';
			}
			?>
			</select>
			</li>
			<li>對話延遲時間(秒)<br /> <input type="number" min="0" name="robot_delTime_<?php echo $mycount;?>_0" value="0" /></li>
			<li><a title="刪除" onclick="$('#pcarea_<?php echo $mycount;?>_0').remove();"><img src="images/icon_delet.png" alt="刪除" /></a></li>
			<li>頭像<br><select name="robot_head_<?php echo $mycount;?>_0" id="robot_head_<?php echo $mycount;?>_0">
			<option value='0'>頭像1</option>
			<option value='1'>頭像2</option>
			<option value='2'>頭像3</option>
			<option value='3'>頭像4</option>
      <option value='4'>頭像5</option>
			</select>頭像名稱：<input type="text" name="robot_head_name_<?php echo $mycount;?>_0" id="robot_head_name_<?php echo $mycount;?>_0" value="" class="pchead_name">
			</li>
			<li>語音檔案(mp3檔)<br />
				<input type="file" name="robot_mp3_path_<?php echo $mycount;?>_0" id="robot_mp3_path_<?php echo $mycount;?>_0" value=""  accept=".mp3">
				<input type="hidden" name="old_mp3_path_<?php echo $mycount;?>_0" id="old_mp3_path_<?php echo $mycount;?>_0" value="" >
			</li>
			<li>
				<hr>
			</li>
		</ul>
	</div>
<?php
}else{
	// PC 對話資料 =>1組以上的對話資料時
	for($x=1,$y=0;$x<count($objArrayTwo[$mycount]['pcDataArray']);$x++,$y++){
		if($objArrayTwo[$mycount]['pcDataArray'][$x]['c_sw_type']==0){$sw0="selected";}else{$sw0="";}
		if($objArrayTwo[$mycount]['pcDataArray'][$x]['c_sw_type']==1){$sw1="selected";}else{$sw1="";}
		if($objArrayTwo[$mycount]['pcDataArray'][$x]['c_sw_type']==2){$sw2="selected";}else{$sw2="";}
		if($objArrayTwo[$mycount]['pcDataArray'][$x]['c_sw_type']==3){$sw3="selected";}else{$sw3="";}
		if($objArrayTwo[$mycount]['pcDataArray'][$x]['c_sw_type']==4){$sw4="selected";}else{$sw4="";}
		if($objArrayTwo[$mycount]['pcDataArray'][$x]['c_sw_type']==5){$sw5="selected";}else{$sw5="";}
		if($objArrayTwo[$mycount]['pcDataArray'][$x]['c_sw_type']==6){$sw6="selected";}else{$sw6="";}
		if($objArrayTwo[$mycount]['pcDataArray'][$x]['c_head_type']==0){$sw_h_0="selected";}else{$sw_h_0="";}
		if($objArrayTwo[$mycount]['pcDataArray'][$x]['c_head_type']==1){$sw_h_1="selected";}else{$sw_h_1="";}
		if($objArrayTwo[$mycount]['pcDataArray'][$x]['c_head_type']==2){$sw_h_2="selected";}else{$sw_h_2="";}
		if($objArrayTwo[$mycount]['pcDataArray'][$x]['c_head_type']==3){$sw_h_3="selected";}else{$sw_h_3="";}
    if($objArrayTwo[$mycount]['pcDataArray'][$x]['c_head_type']==4){$sw_h_4="selected";}else{$sw_h_4="";}
?>
	<div class="chatbox" id="pcarea_<?php echo $mycount;?>_<?php echo $y;?>">
		<ul>
			<li>電腦對話序號<br /> <input name="robot_pcserial_<?php echo $mycount;?>_<?php echo $y;?>" value="<?php echo $objArrayTwo[$mycount]['pcDataArray'][$x]['pc_serial'];?>" /></li>
			<li>內容<br /> <input type="text" name="robot_dsc_<?php echo $mycount;?>_<?php echo $y;?>" id="robot_dsc_<?php echo $mycount;?>_<?php echo $y;?>" value="<?php echo $objArrayTwo[$mycount]['pcDataArray'][$x]['c_dsc'];?>" /></li>
			<li>所屬類別<br />
			<select name="new_type_array_<?php echo $mycount;?>_<?php echo $y;?>">
			<?php
			foreach($new_type_array as $_Key => $_Value){
			if($objArrayTwo[$mycount]['pcDataArray'][$x]['new_pc_type_value']==$_Key){$newswDsc="selected";}else{$newswDsc="";}
			echo '<option value="'.$_Key.'"  '.$newswDsc.'>'.$_Value.'</option>';
			}
			?>
			</select>
			</li>
			<li>下一步驟<br />
			<select name="robot_swtype_<?php echo $mycount;?>_<?php echo $y;?>">
			<?php
			foreach($nextArray[$mycount] as $myKey => $myValue){
			if($objArrayTwo[$mycount]['pcDataArray'][$x]['c_sw_type']==$myKey){$swDsc="selected";}else{$swDsc="";}
			echo '<option value="'.$myKey.'" '.$swDsc.'>'.$myValue.'</option>';
			}
			?>
			</select>
			</li>
			<li>對話延遲時間(秒)<br /> <input type="number" min="0" name="robot_delTime_<?php echo $mycount;?>_<?php echo $y;?>" value="<?php echo $objArrayTwo[$mycount]['pcDataArray'][$x]['speech_del_time'];?>" /></li>
			<li><a title="刪除" onclick="$('#pcarea_<?php echo $mycount;?>_<?php echo $y;?>').remove();"><img src="images/icon_delet.png" alt="刪除" /></a></li>
			<li>頭像<br /> <select name="robot_head_<?php echo $mycount;?>_<?php echo $y;?>" id="robot_head_<?php echo $mycount;?>_<?php echo $y;?>">
			<option value="0" <?php echo $sw_h_0;?>>頭像1</option>
			<option value="1" <?php echo $sw_h_1;?>>頭像2</option>
			<option value="2" <?php echo $sw_h_2;?>>頭像3</option>
			<option value="3" <?php echo $sw_h_3;?>>頭像4</option>
      <option value="4" <?php echo $sw_h_4;?>>頭像5</option>
			</select>頭像名稱：<input type="text" name="robot_head_name_<?php echo $mycount;?>_<?php echo $y;?>" id="robot_head_name_<?php echo $mycount;?>_<?php echo $y;?>" value="<?php echo $objArrayTwo[$mycount]['pcDataArray'][$x]['c_head_name'];?>" class="pchead_name">
			</li>
			<li>語音檔案(mp3檔)<br />
				<input type="file" name="robot_mp3_path_<?php echo $mycount;?>_<?php echo $y;?>" id="robot_mp3_path_<?php echo $mycount;?>_<?php echo $y;?>" value=""  accept=".mp3">
<?php
//如果之前有上傳檔案，就顯示檔案資料跟播放模組
if(isset($objArrayTwo[$mycount]['pcDataArray'][$x]['mp3_path']) and  $objArrayTwo[$mycount]['pcDataArray'][$x]['mp3_path'] > ''){
	?>
<div id="path_area_<?php echo $mycount;?>">
<input type="hidden" name="old_mp3_path_<?php echo $mycount;?>_<?php echo $y;?>" id="old_mp3_path_<?php echo $mycount;?>_<?php echo $y;?>" value="<?php echo $objArrayTwo[$mycount]['pcDataArray'][$x]['mp3_path'];?>" >
<img src="./images/baba.jpg" onclick="showAudio('<?php echo $objArrayTwo[$mycount]['pcDataArray'][$x]['mp3_path'];?>')">
<input type="button" value="移除" onclick="$('#path_area_<?php echo $mycount;?>').remove();">
</div>
<?php
	}
?>
			</li>
			<li>
				----------------------------------------------------
			</li>
		</ul>
	</div>
<?php
	}
}
?>
<p class="add" id="addpc_<?php echo $mycount;?>"><a class="button" title="新增" onclick="add_pcarea(<?php echo $mycount;?>)">新增</a></p>
</div>

<div class="chat">
	<h4>自動進入下一步驟提示</h4>
	內容<br />
	<textarea name="auto_run_dsc_<?php echo $mycount;?>" id="auto_run_dsc_<?php echo $mycount;?>" ><?php echo $objArrayTwo[$mycount]['autoRunDsc'];?></textarea>
	<br>執行時間<br />
	自<input type="number" name="autoRunNum<?php echo $mycount;?>" id="autoRunNum<?php echo $mycount;?>" value="<?php if($objArrayTwo[$mycount]['autoRunNum']>0){echo $objArrayTwo[$mycount]['autoRunNum'];}else{echo '0';}?>" min="0">秒以後執行(0秒表示不啟動此功能)<br>
	頭像<select name="autoRunHead<?php echo $mycount;?>" id="autoRunHead<?php echo $mycount;?>">
	<option value='0' <?php if($objArrayTwo[$mycount]['autoRunHead']==0){echo "selected";}?>>頭像1</option>
	<option value='1' <?php if($objArrayTwo[$mycount]['autoRunHead']==1){echo "selected";}?>>頭像2</option>
	<option value='2' <?php if($objArrayTwo[$mycount]['autoRunHead']==2){echo "selected";}?>>頭像3</option>
	<option value='3' <?php if($objArrayTwo[$mycount]['autoRunHead']==3){echo "selected";}?>>頭像4</option>
  <option value='4' <?php if($objArrayTwo[$mycount]['autoRunHead']==4){echo "selected";}?>>頭像5</option>
	</select>頭像名稱：<input type="text" name="autoRunName<?php echo $mycount;?>" id="autoRunName<?php echo $mycount;?>" value="<?php echo $objArrayTwo[$mycount]['autoRunName'];?>" class="pchead_name">
	<br />
	電腦對話序號<input type="text" name="autoRunPCSerial<?php echo $mycount;?>" value="<?php echo $objArrayTwo[$mycount]['autoRunPCSerial'];?>">
	<br />語音檔案(mp3檔)<br />
	<input type="file" name="autoRun_mp3_path_<?php echo $mycount;?>" id="autoRun_mp3_path_<?php echo $mycount;?>" value=""  accept=".mp3">
<?php
//如果之前有上傳檔案，就顯示檔案資料跟播放模組
if(isset($objArrayTwo[$mycount]['autoRunMP3']) and  $objArrayTwo[$mycount]['autoRunMP3'] > ''){
	?>
<div id="auto_area">
<input type="hidden" name="autoRun_mp3_path_<?php echo $mycount;?>" id="autoRun_mp3_path_<?php echo $mycount;?>" value="<?php echo $objArrayTwo[$mycount]['autoRunMP3'];?>"  >
<img src="./images/baba.jpg" onclick="showAudio('<?php echo $objArrayTwo[$mycount]['autoRunMP3'];?>')">
<input type="button" value="移除" onclick="$('#auto_area').remove();">

</div>
<?php
	}
?>
</div>

<div class="chat">
	<h4>梅林</h4>
	內容<br />
	<textarea name="help_robot_dsc_<?php echo $mycount;?>" id="help_robot_dsc_<?php echo $mycount;?>" ><?php echo $objArrayTwo[$mycount]['helpRobotDsc'];?></textarea>
</div>
</div>
<?php }	?>


            </div>
        </div>
    </td>
  </tr>
</table>

<input type="hidden" name="area_0_index" id="area_0_index" value="2">
<input type="hidden" name="area_1_index" id="area_1_index" value="2">
<input type="hidden" name="area_2_index" id="area_2_index" value="2">
<input type="hidden" name="area_3_index" id="area_3_index" value="2">
<input type="hidden" name="pcarea_0_index" id="pcarea_0_index" value="2">
<input type="hidden" name="pcarea_1_index" id="pcarea_1_index" value="2">
<input type="hidden" name="pcarea_2_index" id="pcarea_2_index" value="2">
<input type="hidden" name="pcarea_3_index" id="pcarea_3_index" value="2">
<input type="hidden" name="controller_type" id="controller_type" value=""><!--存檔狀態 -->
<input type="hidden" name="operation_data_num" value="<?php echo base64_encode($_GET['o_num']);?>">
<input type="hidden" name="getID" value="<?php echo $getID;?>">
<input type="hidden" name="q_order" value="<?php echo base64_encode($_GET['o_t_num']);?>">
<input type="hidden" name="module_type" value="<?php echo $user_module_type;?>">
<input type="hidden" name="sw_tab" id="sw_tab" value="<?php echo $sw_tab;?>"><!-- 選擇的會話 -->
</form>

<script type="text/javascript"><!--
CKEDITOR.replace('c_ckedit0', {});
CKEDITOR.replace('c_ckedit1', {});
CKEDITOR.replace('c_ckedit2', {});
CKEDITOR.replace('c_ckedit3', {});
CKEDITOR.replace('c_ckedit0_memo', {});
CKEDITOR.replace('c_ckedit1_memo', {});
CKEDITOR.replace('c_ckedit2_memo', {});
CKEDITOR.replace('c_ckedit3_memo', {});
//--></script>
<?php 	$ODb->close();?>

<audio src="" id="playAudio" autoplay>
</audio>
</body>
</html>
