<?php
	session_start();
  //包含需求檔案 ------------------------------------------------------------------------
	include("./class/common_lite.php");
	if($_SESSION['loginType'] == ''){
		ri_jump("login.php");
	}

	if($_SESSION['zeroteamzero'] != 'IS_LOGIN'){
		//ri_jump("login.php");
	}
	//
	$dialogue = 4;//會話的數量，目前每一試題最多四個會話


	$getID = base64_decode($_POST['getID']);//試題的key值
	$q_order = base64_decode($_POST['q_order']);//下一筆試題的順序值
	$operation_data_num = $_POST['operation_data_num'];//作業的key值
	
	if($getID=='' || !is_numeric($getID) || $q_order=='' || !is_numeric($q_order)){	
			echo '<script language="javascript">window.close();</script>';
			exit;
	}
	
	//宣告變數 ----------------------------------------------------------------------------
	$ODb = new run_db("mysql",3306);      //建立資料庫物件
	$nowdate =  date("Y-m-d H:i",time());
	
	$upFileFload = "upMP3/".date("Ymd",time());
	$upFile = $upFileFload."/";		

	if (!is_dir($upFile)) {      //檢察upload資料夾是否存在
		if (!mkdir($upFile)){ //不存在的話就創建upload資料夾
		//die ("上傳目錄不存在，並且創建失敗");
		}
	}

	
	//更新資料
	$sql_dsc = "
	update `questions_data` 
	set `c_dsc`='".$_POST['questions_dsc']."',`up_date`='".$nowdate."'  
	where `num`='".$getID."'";
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");

	//更新階段敘述資料
	for($x=0;$x<4;$x++){
		if($_POST['hide_module_area'.$x]==1){$addSQl=",`hide_module_area`=1 ";}else{$addSQl=",`hide_module_area`=0 ";}
		if($_POST['hideModuleBtnDsc'.$x]==''){$addSQ2=",`hideModuleBtnDsc`='確認' ";}else{$addSQ2=",`hideModuleBtnDsc`='".$_POST['hideModuleBtnDsc'.$x]."' ";}
		$sql_dsc = "
		update `step_dsc_data` 
		set `c_dsc`='".$_POST['step_dsc_'.$x]."',`up_date`='".$nowdate."'".$addSQl.$addSQ2."   
		where `questions_data_num`='".$getID."' and `c_sw_type`=".$x."";
		$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	}
	
	
	//更新梅林 會話的資料
	for($x=0;$x<$dialogue;$x++){
		$sql_dsc = "update `speak_data` set `c_dsc`='".$_POST['help_robot_dsc_'.$x]."',`up_date`='".$nowdate."' where `questions_data_num`='".$getID."' and `c_dsc_type`=".$x." and `c_user_type`=2";
		$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	}
	//更新自對執行 會話的資料
	for($x=0;$x<$dialogue;$x++){
		$mp3_path = '';//mp3路徑
		if(isset($_POST['oldautoRun_mp3_path_'.$x])){
			$mp3_path = $_POST['oldautoRun_mp3_path_'.$x];
		}
		if(isset( $_FILES['autoRun_mp3_path_'.$x] )){
			//搬移檔案順便將檔案名稱上傳到temp_files資料表
			$file_type_dsc = explode(".",basename($_FILES['autoRun_mp3_path_'.$x]['name']));
			$mtime = explode(" ", microtime()); 
			$startTime = $mtime[1].substr($mtime[0],2);			
			$new_name =  $startTime.".".strtolower($file_type_dsc[1]);
			$uploadfile = $upFile.$new_name;
			if (move_uploaded_file($_FILES['autoRun_mp3_path_'.$x]['tmp_name'], $uploadfile)) {
				$mp3_path = $uploadfile;					
			}
		}		
		
		$sql_dsc = "update `speak_data` 
		set 
		`c_dsc`='".$_POST['auto_run_dsc_'.$x]."',
		`speech_del_time`='".$_POST['autoRunNum'.$x]."',
		`c_head_type`='".$_POST['autoRunHead'.$x]."',
		`c_head_name`='".$_POST['autoRunName'.$x]."',
		`pc_serial`='".$_POST['autoRunPCSerial'.$x]."',
		`mp3_path`='".$mp3_path."',
		`up_date`='".$nowdate."' 
		where `questions_data_num`='".$getID."' and `c_dsc_type`=".$x." and `c_user_type`=3";
		$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	}

	//清掉該試題的使用者對話，然後新增使用者對話資料
	$sql = "delete from `speak_data` where `questions_data_num`='".$getID."' and `c_user_type` in ('0','1')";
	$res=$ODb->query($sql) or die("載入資料出錯，請聯繫管理員。");
	
	$mp3_path = '';//mp3路徑
	if(isset($_POST['old_first_mp3_path_0'])){
		$mp3_path = $_POST['old_first_mp3_path_0'];
	}
	if(isset( $_FILES['robot_first_mp3_path_0'] )){
		//搬移檔案順便將檔案名稱上傳到temp_files資料表
		$file_type_dsc = explode(".",basename($_FILES['robot_first_mp3_path_0']['name']));
		$mtime = explode(" ", microtime()); 
		$startTime = $mtime[1].substr($mtime[0],2);			
		$new_name =  $startTime.".".strtolower($file_type_dsc[1]);
		$uploadfile = $upFile.$new_name;
		if (move_uploaded_file($_FILES['robot_first_mp3_path_0']['tmp_name'], $uploadfile)) {
			$mp3_path = $uploadfile;					
		}
	}
	
	//會話1
	$sql = "
	insert into `speak_data` set 
	`questions_data_num`='".$getID."',
	`c_dsc_type`='0',
	`c_user_type`='1',
	`c_dsc`='".$_POST['robot_first_dsc_0']."',
	`c_head_type`=".$_POST['robot_first_head_0'].",
	`c_head_name`='".$_POST['robot_first_head_name_0']."',
	`mp3_path`='".$mp3_path."',
	`up_date`='".$nowdate."'
	";
	$res=$ODb->query($sql) or die("載入資料出錯，請聯繫管理員。");
		

	for($x=0;$x<$_POST['area_0_index'];$x++){		
		//使用者對談	
		if($_POST['dsc_0_'.$x] !=''){
			$sql = "
			insert into `speak_data` set 
			`questions_data_num`='".$getID."',
			`c_dsc_type`='0',
			`c_user_type`='0',
			`pc_serial`='".$_POST['pcserial_0_'.$x]."',
			`c_dsc`='".$_POST['dsc_0_'.$x]."',
			`c_power_dsc`='".$_POST['powerdsc_0_'.$x]."',
			`c_power_number`='".$_POST['powernumber_0_'.$x]."',
			`up_date`='".$nowdate."'
			";
			$res=$ODb->query($sql) or die("載入資料出錯，請聯繫管理員。");
		}
	}
	for($x=0;$x<$_POST['pcarea_0_index'];$x++){		
		//電腦對話
		if($_POST['robot_dsc_0_'.$x] !=''){
			
			$mp3_path = '';//mp3路徑
			if(isset($_POST['old_mp3_path_0_'.$x])){
				$mp3_path = $_POST['old_mp3_path_0_'.$x];
			}
			if(isset( $_FILES['robot_mp3_path_0_'.$x] )){
				//搬移檔案順便將檔案名稱上傳到temp_files資料表
				$file_type_dsc = explode(".",basename($_FILES['robot_mp3_path_0_'.$x]['name']));
				$mtime = explode(" ", microtime()); 
				$startTime = $mtime[1].substr($mtime[0],2);			
				$new_name =  $startTime.".".strtolower($file_type_dsc[1]);
				$uploadfile = $upFile.$new_name;
				if (move_uploaded_file($_FILES['robot_mp3_path_0_'.$x]['tmp_name'], $uploadfile)) {
					$mp3_path = $uploadfile;					
				}
			}
			
			
			$sql = "
			insert into `speak_data` set 
			`questions_data_num`='".$getID."',
			`c_dsc_type`='0',
			`c_user_type`='1',
			`c_head_type`='".$_POST['robot_head_0_'.$x]."',
			`c_head_name`='".$_POST['robot_head_name_0_'.$x]."',			
			`pc_serial`='".$_POST['robot_pcserial_0_'.$x]."',
			`c_dsc`='".$_POST['robot_dsc_0_'.$x]."',
			`c_sw_type`='".$_POST['robot_swtype_0_'.$x]."',
			`new_pc_type_value`='".$_POST['new_type_array_0_'.$x]."',
			`speech_del_time`='".$_POST['robot_delTime_0_'.$x]."',
			`mp3_path`='".$mp3_path."',
			`up_date`='".$nowdate."'
			";
			
			$res=$ODb->query($sql) or die("載入資料出錯，請聯繫管理員。");
		}
	}

	//會話2
	$mp3_path = '';//mp3路徑
	if(isset($_POST['old_first_mp3_path_1'])){
		$mp3_path = $_POST['old_first_mp3_path_1'];
	}
	if(isset( $_FILES['robot_first_mp3_path_1'] )){
		//搬移檔案順便將檔案名稱上傳到temp_files資料表
		$file_type_dsc = explode(".",basename($_FILES['robot_first_mp3_path_1']['name']));
		$mtime = explode(" ", microtime()); 
		$startTime = $mtime[1].substr($mtime[0],2);			
		$new_name =  $startTime.".".strtolower($file_type_dsc[1]);
		$uploadfile = $upFile.$new_name;
		if (move_uploaded_file($_FILES['robot_first_mp3_path_1']['tmp_name'], $uploadfile)) {
			$mp3_path = $uploadfile;					
		}
	}	
	$sql = "
	insert into `speak_data` set 
	`questions_data_num`='".$getID."',
	`c_dsc_type`='1',
	`c_user_type`='1',
	`c_dsc`='".$_POST['robot_first_dsc_1']."',
	`c_head_type`='".$_POST['robot_first_head_1']."',
	`c_head_name`='".$_POST['robot_first_head_name_1']."',
	`mp3_path`='".$mp3_path."',	
	`up_date`='".$nowdate."'
	";
	$res=$ODb->query($sql) or die("載入資料出錯，請聯繫管理員。");
	for($x=0;$x<$_POST['area_1_index'];$x++){		
		//使用者對談	
		if($_POST['dsc_1_'.$x] !=''){
			$sql = "
			insert into `speak_data` set 
			`questions_data_num`='".$getID."',
			`c_dsc_type`='1',
			`c_user_type`='0',
			`pc_serial`='".$_POST['pcserial_1_'.$x]."',
			`c_dsc`='".$_POST['dsc_1_'.$x]."',
			`c_power_dsc`='".$_POST['powerdsc_1_'.$x]."',
			`c_power_number`='".$_POST['powernumber_1_'.$x]."',
			`up_date`='".$nowdate."'
			";
			$res=$ODb->query($sql) or die("載入資料出錯，請聯繫管理員。");
		}
	}
	for($x=0;$x<$_POST['pcarea_1_index'];$x++){		
		//電腦對話
		if($_POST['robot_dsc_1_'.$x] !=''){
			$mp3_path = '';//mp3路徑
			if(isset($_POST['old_mp3_path_1_'.$x])){
				$mp3_path = $_POST['old_mp3_path_1_'.$x];
			}
			if(isset( $_FILES['robot_mp3_path_1_'.$x] )){
				//搬移檔案順便將檔案名稱上傳到temp_files資料表
				$file_type_dsc = explode(".",basename($_FILES['robot_mp3_path_1_'.$x]['name']));
				$mtime = explode(" ", microtime()); 
				$startTime = $mtime[1].substr($mtime[0],2);			
				$new_name =  $startTime.".".strtolower($file_type_dsc[1]);
				$uploadfile = $upFile.$new_name;
				if (move_uploaded_file($_FILES['robot_mp3_path_1_'.$x]['tmp_name'], $uploadfile)) {
					$mp3_path = $uploadfile;					
				}
			}
			
			$sql = "
			insert into `speak_data` set 
			`questions_data_num`='".$getID."',
			`c_dsc_type`='1',
			`c_user_type`='1',
			`c_head_type`='".$_POST['robot_head_1_'.$x]."',
			`c_head_name`='".$_POST['robot_head_name_1_'.$x]."',			
			`pc_serial`='".$_POST['robot_pcserial_1_'.$x]."',
			`c_dsc`='".$_POST['robot_dsc_1_'.$x]."',
			`c_sw_type`='".$_POST['robot_swtype_1_'.$x]."',
			`new_pc_type_value`='".$_POST['new_type_array_1_'.$x]."',
			`speech_del_time`='".$_POST['robot_delTime_1_'.$x]."',
			`mp3_path`='".$mp3_path."',			
			`up_date`='".$nowdate."'
			";
			$res=$ODb->query($sql) or die("載入資料出錯，請聯繫管理員。");
		}
	}
	
	//會話3
	$mp3_path = '';//mp3路徑
	if(isset($_POST['old_first_mp3_path_2'])){
		$mp3_path = $_POST['old_first_mp3_path_2'];
	}
	if(isset( $_FILES['robot_first_mp3_path_2'] )){
		//搬移檔案順便將檔案名稱上傳到temp_files資料表
		$file_type_dsc = explode(".",basename($_FILES['robot_first_mp3_path_2']['name']));
		$mtime = explode(" ", microtime()); 
		$startTime = $mtime[1].substr($mtime[0],2);			
		$new_name =  $startTime.".".strtolower($file_type_dsc[1]);
		$uploadfile = $upFile.$new_name;
		if (move_uploaded_file($_FILES['robot_first_mp3_path_2']['tmp_name'], $uploadfile)) {
			$mp3_path = $uploadfile;					
		}
	}		
	$sql = "
	insert into `speak_data` set 
	`questions_data_num`='".$getID."',
	`c_dsc_type`='2',
	`c_user_type`='1',
	`c_dsc`='".$_POST['robot_first_dsc_2']."',
	`c_head_type`='".$_POST['robot_first_head_2']."',
	`c_head_name`='".$_POST['robot_first_head_name_2']."',
	`mp3_path`='".$mp3_path."',		
	`up_date`='".$nowdate."'
	";
	$res=$ODb->query($sql) or die("載入資料出錯，請聯繫管理員。");
	for($x=0;$x<$_POST['area_2_index'];$x++){		
		//使用者對談	
		if($_POST['dsc_2_'.$x] !=''){
			$sql = "
			insert into `speak_data` set 
			`questions_data_num`='".$getID."',
			`c_dsc_type`='2',
			`c_user_type`='0',
			`pc_serial`='".$_POST['pcserial_2_'.$x]."',
			`c_dsc`='".$_POST['dsc_2_'.$x]."',
			`c_power_dsc`='".$_POST['powerdsc_2_'.$x]."',
			`c_power_number`='".$_POST['powernumber_2_'.$x]."',
			`up_date`='".$nowdate."'
			";
			$res=$ODb->query($sql) or die("載入資料出錯，請聯繫管理員。");
		}
	}
	for($x=0;$x<$_POST['pcarea_2_index'];$x++){		
		//電腦對話
		if($_POST['robot_dsc_2_'.$x] !=''){
			$mp3_path = '';//mp3路徑
			if(isset($_POST['old_mp3_path_2_'.$x])){
				$mp3_path = $_POST['old_mp3_path_2_'.$x];
			}
			if(isset( $_FILES['robot_mp3_path_2_'.$x] )){
				//搬移檔案順便將檔案名稱上傳到temp_files資料表
				$file_type_dsc = explode(".",basename($_FILES['robot_mp3_path_2_'.$x]['name']));
				$mtime = explode(" ", microtime()); 
				$startTime = $mtime[1].substr($mtime[0],2);			
				$new_name =  $startTime.".".strtolower($file_type_dsc[1]);
				$uploadfile = $upFile.$new_name;
				if (move_uploaded_file($_FILES['robot_mp3_path_2_'.$x]['tmp_name'], $uploadfile)) {
					$mp3_path = $uploadfile;					
				}
			}
			$sql = "
			insert into `speak_data` set 
			`questions_data_num`='".$getID."',
			`c_dsc_type`='2',
			`c_user_type`='1',
			`c_head_type`='".$_POST['robot_head_2_'.$x]."',
			`c_head_name`='".$_POST['robot_head_name_2_'.$x]."',
			`pc_serial`='".$_POST['robot_pcserial_2_'.$x]."',
			`c_dsc`='".$_POST['robot_dsc_2_'.$x]."',
			`c_sw_type`='".$_POST['robot_swtype_2_'.$x]."',
			`new_pc_type_value`='".$_POST['new_type_array_2_'.$x]."',
			`speech_del_time`='".$_POST['robot_delTime_2_'.$x]."',
			`mp3_path`='".$mp3_path."',			
			`up_date`='".$nowdate."'
			";
			$res=$ODb->query($sql) or die("載入資料出錯，請聯繫管理員。");
		}
	}	
	
	//會話4
	$mp3_path = '';//mp3路徑
	if(isset($_POST['old_first_mp3_path_3'])){
		$mp3_path = $_POST['old_first_mp3_path_3'];
	}
	if(isset( $_FILES['robot_first_mp3_path_3'] )){
		//搬移檔案順便將檔案名稱上傳到temp_files資料表
		$file_type_dsc = explode(".",basename($_FILES['robot_first_mp3_path_3']['name']));
		$mtime = explode(" ", microtime()); 
		$startTime = $mtime[1].substr($mtime[0],2);			
		$new_name =  $startTime.".".strtolower($file_type_dsc[1]);
		$uploadfile = $upFile.$new_name;
		if (move_uploaded_file($_FILES['robot_first_mp3_path_3']['tmp_name'], $uploadfile)) {
			$mp3_path = $uploadfile;					
		}
	}	
	$sql = "
	insert into `speak_data` set 
	`questions_data_num`='".$getID."',
	`c_dsc_type`='3',
	`c_user_type`='1',
	`c_dsc`='".$_POST['robot_first_dsc_3']."',
	`c_head_type`='".$_POST['robot_first_head_3']."',
	`c_head_name`='".$_POST['robot_first_head_name_3']."',
	`mp3_path`='".$mp3_path."',
	`up_date`='".$nowdate."'
	";
	$res=$ODb->query($sql) or die("載入資料出錯，請聯繫管理員。");
	for($x=0;$x<$_POST['area_3_index'];$x++){		
		//使用者對談	
		if($_POST['dsc_3_'.$x] !=''){
			$sql = "
			insert into `speak_data` set 
			`questions_data_num`='".$getID."',
			`c_dsc_type`='3',
			`c_user_type`='0',
			`pc_serial`='".$_POST['pcserial_3_'.$x]."',
			`c_dsc`='".$_POST['dsc_3_'.$x]."',
			`c_power_dsc`='".$_POST['powerdsc_3_'.$x]."',
			`c_power_number`='".$_POST['powernumber_3_'.$x]."',
			`up_date`='".$nowdate."'
			";
			$res=$ODb->query($sql) or die("載入資料出錯，請聯繫管理員。");
		}
	}
	for($x=0;$x<$_POST['pcarea_3_index'];$x++){		
		//電腦對話
		if($_POST['robot_dsc_3_'.$x] !=''){
			$mp3_path = '';//mp3路徑
			if(isset($_POST['old_mp3_path_3_'.$x])){
				$mp3_path = $_POST['old_mp3_path_3_'.$x];
			}
			if(isset( $_FILES['robot_mp3_path_3_'.$x] )){
				//搬移檔案順便將檔案名稱上傳到temp_files資料表
				$file_type_dsc = explode(".",basename($_FILES['robot_mp3_path_3_'.$x]['name']));
				$mtime = explode(" ", microtime()); 
				$startTime = $mtime[1].substr($mtime[0],2);			
				$new_name =  $startTime.".".strtolower($file_type_dsc[1]);
				$uploadfile = $upFile.$new_name;
				if (move_uploaded_file($_FILES['robot_mp3_path_3_'.$x]['tmp_name'], $uploadfile)) {
					$mp3_path = $uploadfile;					
				}
			}
			$sql = "
			insert into `speak_data` set 
			`questions_data_num`='".$getID."',
			`c_dsc_type`='3',
			`c_user_type`='1',
			`c_head_type`='".$_POST['robot_head_3_'.$x]."',
			`c_head_name`='".$_POST['robot_head_name_3_'.$x]."',
			`pc_serial`='".$_POST['robot_pcserial_3_'.$x]."',
			`c_dsc`='".$_POST['robot_dsc_3_'.$x]."',
			`c_sw_type`='".$_POST['robot_swtype_3_'.$x]."',
			`new_pc_type_value`='".$_POST['new_type_array_3_'.$x]."',
			`speech_del_time`='".$_POST['robot_delTime_3_'.$x]."',
			`mp3_path`='".$mp3_path."',
			`up_date`='".$nowdate."'
			";
			$res=$ODb->query($sql) or die("載入資料出錯，請聯繫管理員。");
		}
	}	
	
	
	//會話1的module	
	$sql = "delete from `speak_usemodule_data` where `questions_data_num`='".$getID."' and `c_dsc_type`=0";
	$res=$ODb->query($sql) or die("載入資料出錯，請聯繫管理員。");
	if($_POST['module_sw_0']==""){
	
	}else if($_POST['module_sw_0']=="ckedit"){
		$sql = "insert into `speak_usemodule_data` set `questions_data_num`='".$getID."',`c_dsc_type`=0,`module_type`='".$_POST['module_type']."',`module_num`='',`ckedit_dsc`='".$_POST['c_ckedit0']."',`ckedit_dsc_memo`='".$_POST['c_ckedit0_memo']."',`up_date`='".$nowdate."'";
		$res=$ODb->query($sql) or die("載入資料出錯，請聯繫管理員。");	
	}else{
		if(is_numeric($_POST['warning_time0'])){
		$wtime0 = $_POST['warning_time0'];
		}else{
		$wtime0 = 0;
		}		
		$sql = "insert into `speak_usemodule_data` set `questions_data_num`='".$getID."',`c_dsc_type`=0,`module_type`='".$_POST['module_type']."',`module_num`='".$_POST['module_num0']."',`ckedit_dsc`='',`pc_serial`='".$_POST['pc_serial_0']."',`warning_time`=".$wtime0.",`warning_dsc`='".$_POST['warning_dsc0']."',`btn_dsc`='".$_POST['btn_dsc_0']."',`up_date`='".$nowdate."'";
		$res=$ODb->query($sql) or die("載入資料出錯，請聯繫管理員。");	
	}
	
	
	//會話2的module	
	$sql = "delete from `speak_usemodule_data` where `questions_data_num`='".$getID."' and `c_dsc_type`=1";
	$res=$ODb->query($sql) or die("載入資料出錯，請聯繫管理員。");
	if($_POST['module_sw_1']==""){
	
	}else if($_POST['module_sw_1']=="ckedit"){
		$sql = "insert into `speak_usemodule_data` set `questions_data_num`='".$getID."',`c_dsc_type`=1,`module_type`='".$_POST['module_type']."',`module_num`='',`ckedit_dsc`='".$_POST['c_ckedit1']."',`ckedit_dsc_memo`='".$_POST['c_ckedit1_memo']."',`up_date`='".$nowdate."'";
		$res=$ODb->query($sql) or die("載入資料出錯，請聯繫管理員。");	
	}else{
		if(is_numeric($_POST['warning_time1'])){
		$wtime1 = $_POST['warning_time1'];
		}else{
		$wtime1 = 0;
		}	
		$sql = "insert into `speak_usemodule_data` set `questions_data_num`='".$getID."',`c_dsc_type`=1,`module_type`='".$_POST['module_type']."',`module_num`='".$_POST['module_num1']."',`ckedit_dsc`='',`pc_serial`='".$_POST['pc_serial_1']."',`btn_dsc`='".$_POST['btn_dsc_1']."',`warning_time`=".$wtime1.",`warning_dsc`='".$_POST['warning_dsc1']."',`up_date`='".$nowdate."'";
		$res=$ODb->query($sql) or die("載入資料出錯，請聯繫管理員。");	
	}

	//會話3的module	
	$sql = "delete from `speak_usemodule_data` where `questions_data_num`='".$getID."' and `c_dsc_type`=2";
	$res=$ODb->query($sql) or die("載入資料出錯，請聯繫管理員。");
	if($_POST['module_sw_2']==""){
	
	}else if($_POST['module_sw_2']=="ckedit"){
		$sql = "insert into `speak_usemodule_data` set `questions_data_num`='".$getID."',`c_dsc_type`=2,`module_type`='".$_POST['module_type']."',`module_num`='',`ckedit_dsc`='".$_POST['c_ckedit2']."',`ckedit_dsc_memo`='".$_POST['c_ckedit2_memo']."',`up_date`='".$nowdate."'";
		$res=$ODb->query($sql) or die("載入資料出錯，請聯繫管理員。");	
	}else{
		if(is_numeric($_POST['warning_time2'])){
		$wtime2 = $_POST['warning_time2'];
		}else{
		$wtime2 = 0;
		}
		$sql = "insert into `speak_usemodule_data` set `questions_data_num`='".$getID."',`c_dsc_type`=2,`module_type`='".$_POST['module_type']."',`module_num`='".$_POST['module_num2']."',`ckedit_dsc`='',`pc_serial`='".$_POST['pc_serial_2']."',`btn_dsc`='".$_POST['btn_dsc_2']."',`warning_time`=".$wtime2.",`warning_dsc`='".$_POST['warning_dsc2']."',`up_date`='".$nowdate."'";
		$res=$ODb->query($sql) or die("載入資料出錯，請聯繫管理員。");	
	}

	//會話4的module	
	$sql = "delete from `speak_usemodule_data` where `questions_data_num`='".$getID."' and `c_dsc_type`=3";
	$res=$ODb->query($sql) or die("載入資料出錯，請聯繫管理員。");
	if($_POST['module_sw_3']==""){
	
	}else if($_POST['module_sw_3']=="ckedit"){
		$sql = "insert into `speak_usemodule_data` set `questions_data_num`='".$getID."',`c_dsc_type`=3,`module_type`='".$_POST['module_type']."',`module_num`='',`ckedit_dsc`='".$_POST['c_ckedit3']."',`ckedit_dsc_memo`='".$_POST['c_ckedit3_memo']."',`up_date`='".$nowdate."'";
		$res=$ODb->query($sql) or die("載入資料出錯，請聯繫管理員。");	
	}else{
		if(is_numeric($_POST['warning_time3'])){
		$wtime2 = $_POST['warning_time3'];
		}else{
		$wtime2 = 0;
		}
		$sql = "insert into `speak_usemodule_data` set `questions_data_num`='".$getID."',`c_dsc_type`=3,`module_type`='".$_POST['module_type']."',`module_num`='".$_POST['module_num3']."',`ckedit_dsc`='',`pc_serial`='".$_POST['pc_serial_3']."',`btn_dsc`='".$_POST['btn_dsc_3']."',`warning_time`=".$wtime2.",`warning_dsc`='".$_POST['warning_dsc3']."',`up_date`='".$nowdate."'";
		$res=$ODb->query($sql) or die("載入資料出錯，請聯繫管理員。");	
	}
	
	$sw_tab=0;
	if($_POST['controller_type']=="next"){
		$q_order++;
	}else{
		$sw_tab = $_POST['sw_tab'];
	}	
	$ODb->close();
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script language="javascript">
alert('存檔成功!!');
location.replace("operation_edit.php?o_num=<?php echo $operation_data_num;?>&o_t_num=<?php echo base64_encode($q_order);?>&sw_tab=<?php echo base64_encode($sw_tab);?>");

</script>