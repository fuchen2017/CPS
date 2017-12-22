<?php
session_start();
//包含需求檔案 ------------------------------------------------------------------------
include("./bcontroller/class/common_lite.php");
$ODb = new run_db("mysql",3306);      //建立資料庫物件

$keyArray = array(
'70'=>'19036',
'72'=>'19042',
'74'=>'18179',
);

function getButton($getArray){
	$ODb = new run_db("mysql",3306);      //建立資料庫物件
try {
	$up_dsc ="
	select * 
	from `speak_data` 
	where `questions_data_num`='".$getArray['value']."' and `c_user_type`=1 and `pc_serial`='".$getArray['value1']."' and `c_dsc_type`='".$getArray['value2']."'";
	$res = $ODb->query($up_dsc);
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "<br>".$sql_dsc;
}
	
	while($row = mysql_fetch_array($res)){
		return $row['num'];
	}
	return '';
}

function ckData($get11Array){	
$getNum = $get11Array['tkey'];
$TEMPrECORD = $get11Array['two'];
$returnArray = array();

	$ODb = new run_db("mysql",3306);      //建立資料庫物件
try {
	$up_dsc ="select * from `speak_data` where `num`='".$getNum."'";
	$res = $ODb->query($up_dsc);
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "<br>".$sql_dsc;
}
	
	while($row = mysql_fetch_array($res)){
		$questions_data_num	= $row['questions_data_num'];
		$c_dsc_type	= $row['c_dsc_type'];
		$pc_serial = $row['pc_serial'];
		$TEMPrECORD[$row['questions_data_num']] = $TEMPrECORD[$row['questions_data_num']] + $row['c_power_number'];
	}
	
	//取得operation_data_num 現在的作業num
try {
	$up_dsc ="select * from `questions_data` where `num`='".$questions_data_num."'";	
	$res = $ODb->query($up_dsc);
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "<br>".$sql_dsc;
}	
	while($row = mysql_fetch_array($res)){
		$operation_data_num	= $row['operation_data_num'];
	}
	
	//取得next_operation_data_num 下一個作業num
	$next_operation_data_num='';
try {
	$up_dsc ="select `main_data_num` from `operation_data` where `num`='".$operation_data_num."'";
	$res = $ODb->query($up_dsc);
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "<br>".$sql_dsc;
}	
	while($row = mysql_fetch_array($res)){
		$main_data_num	= $row['main_data_num'];
		$up_dsc ="select `num` from `operation_data` where `num`>'".$operation_data_num."' and `main_data_num`='".$main_data_num."' order by `num` limit 1";
		$res = $ODb->query($up_dsc) or die("更新資料出錯，請聯繫管理員。");
		while($row = mysql_fetch_array($res)){
			$next_operation_data_num = $row['num'];
		}		
	}
	
	
	//取得下一試題的num
try {
	$up_dsc ="select * from `questions_data` where `num`>'".$questions_data_num."' and `operation_data_num`='".$operation_data_num."' order by `num` limit 1";
	
	$res = $ODb->query($up_dsc);
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "<br>".$sql_dsc;
}	
	while($row = mysql_fetch_array($res)){
		$next_questions_data_num = $row['num'];		
	}
	
	//根據選項，找出下一步驟要做啥
try {
	$up_dsc ="select * from `speak_data` where `pc_serial`='".$pc_serial."' and `questions_data_num`='".$questions_data_num."' and `c_dsc_type`=".$c_dsc_type ." and `c_user_type`=1";

	$res = $ODb->query($up_dsc);
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "<br>".$sql_dsc;
}	
		while($row = mysql_fetch_array($res)){
			$c_sw_type = $row['c_sw_type'];
		}
		switch($c_sw_type){
			case "0"://第二會話
				$getNextDscArray =  get_talk($questions_data_num,1);
				$returnArray[] = $getNextDscArray;
				$returnArray[] = $TEMPrECORD;
				return $returnArray;
			break;
			case "1"://第三會話
				$getNextDscArray =  get_talk($questions_data_num,2);
				$returnArray[] = $getNextDscArray;
				$returnArray[] = $TEMPrECORD;
				return $returnArray;
				
			break;
			case "2"://下一個試題
				$getNextDscArray =  get_talk($next_questions_data_num,0);
				$returnArray[] = $getNextDscArray;
				$returnArray[] = $TEMPrECORD;
				return $returnArray;

			break;
			case "3"://下一個作業
				$questions_data_num = get_first_questions_data_num($next_operation_data_num);
				if($questions_data_num !=''){
					$getNextDscArray =  get_talk($questions_data_num,0);
$returnArray[] = $getNextDscArray;
$returnArray[] = $TEMPrECORD;
return $returnArray;
					
				}
			break;
			case "4"://結束
$returnArray[] = '';
$returnArray[] = $TEMPrECORD;
return $returnArray;
			break;
			case "5"://第四會話
				$getNextDscArray =  get_talk($questions_data_num,3);
$returnArray[] = $getNextDscArray;
$returnArray[] = $TEMPrECORD;
return $returnArray;
				
			break;
			case "6"://第一會話
				$getNextDscArray =  get_talk($questions_data_num,0);
$returnArray[] = $getNextDscArray;
$returnArray[] = $TEMPrECORD;
return $returnArray;
				
			break;
			default:
			break;
		}
$ODb->close();	
		
}




function get_talk($getNum,$typeNum){
	$ODb = new run_db("mysql",3306);      //建立資料庫物件	
	$tempArray = array();
try {
	$sql_dsc ="select * from `speak_data` where `questions_data_num`='".$getNum."' and `c_dsc_type`=".$typeNum." order by `num` ";
	$res = $ODb->query($sql_dsc);
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "<br>".$sql_dsc;
}	
	if(mysql_num_rows($res)>0){
		while($row = mysql_fetch_array($res)){
			//使用者對話
			if($row['c_user_type'] == 0){
				$tempArray[] = $row['num'];
			}
		}
	}
	$ODb->close();	
	
	return $tempArray;
}

function get_first_questions_data_num($operation_data_num){
	$ODb = new run_db("mysql",3306);      //建立資料庫物件
try {
	$up_dsc ="select * from `questions_data` where `operation_data_num`='".$operation_data_num."' order by `num` limit 1";
	$res = $ODb->query($up_dsc);
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "<br>".$sql_dsc;
}		
	while($row = mysql_fetch_array($res)){
		$num = $row['num'];		
	}
	$ODb->close();	
	
	return $num;

}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>合作問題解決數位學習系統</title>
<script src="./js/jquery-1.10.1.min.js"></script>

<script language="javascript">
<?php
if( isset( $_GET['mainID'] )){
?>
	//開始自動播放舊資料
	var allTime = 30000;//每30秒觸發一個
	setTimeout(function() {
		location.reload();
	}, allTime);

<?php	
}
?>
function chgType(){
	var tempDsc = $('#swOpt').val();
	 location.href = "update_olddata_test.php?mainID="+tempDsc;
}

function stopPG(){
	 location.href = "update_olddata_test.php";
}
</script>
</head>
<body>
請選擇要更新的題目:
<select id="swOpt">
	<option value="70" <?php if( isset( $_GET['mainID'] ) and $_GET['mainID'] == '70'){echo 'selected';} ?>>製作思樂冰</option>
	<option value="72" <?php if( isset( $_GET['mainID'] ) and $_GET['mainID'] == '72'){echo 'selected';} ?>>河內塔</option>
	<option value="74" <?php if( isset( $_GET['mainID'] ) and $_GET['mainID'] == '74'){echo 'selected';} ?>>畢業旅行規劃</option>
</select>
<input type="button" onclick="chgType()" value="開始執行">
<input type="button" onclick="stopPG()" value="停止執行">
<hr>
<?php
if( isset( $_GET['mainID'] ) and isset($keyArray[$_GET['mainID']]) ){
	$mainID = $_GET['mainID'];
	$beginKey = $keyArray[$mainID];
	try {
	$sql_dsc = "
	select `num`,`record_value` 
	from `opt_record` 
	where `main_data_num`='".$mainID."' and `create_user_type`='STUDENT' and `power_dsc` > '' and `questions_power_dsc`='' order by `num` DESC limit 50
	";
	$res = $ODb->query($sql_dsc);

} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "<br>".$sql_dsc;
}
$allvalueArray = array();
$keyDSC = array(
'speech_radio_0'=>'0',
'speech_radio_1'=>'1',
'speech_radio_2'=>'2',
'speech_radio_3'=>'3',
'speech_radio_4'=>'4',
'speech_radio_5'=>'5',
'speech_radio_6'=>'6',
);

if(count($row) == 0){
	echo '所有資料已經更新。無任何資料需要處理!!';	
}
while($row = mysql_fetch_array($res)){
	$tempArray = explode('<tw>',$row['record_value']);
	$allvalueArray = array();
	$TEMPrECORD = get_Questions_Array();//臨時資料表 => 每組對話選項 得到的能力值
		foreach($tempArray as $value){		
			$tempArray2 = explode('||',$value);
			if( $tempArray2[0] == 'radio' ){
				$allvalueArray[] = array(
				'type'=>'radio',
				'value'=>$keyDSC[$tempArray2[1]]
				);
			}		
			if( $tempArray2[0] == 'module_btn' ){
				$allvalueArray[] = $keyDSC[$tempArray2[1]];
				$allvalueArray[] = array(
				'type'=>'module_btn',
				'value'=>$tempArray2[1],
				'value1'=>$tempArray2[2],
				'value2'=>$tempArray2[3]
				);
			}		
		}

		$getSelectArray = array();
		$tempArray = array();
		$x=0;
		foreach($allvalueArray as $dataArray ){
			if($x == 0){
				$tempArray['tkey'] = $beginKey;
				$tempArray['two'] = $TEMPrECORD;
				$getArray = ckData($tempArray);
				$getSelectArray = $getArray[0];
				$TEMPrECORD = $getArray[1];
			}else{
				if( $dataArray['type'] == 'radio' ){
					$tempArray['tkey'] = $getSelectArray[$dataArray['value']];
					$tempArray['two'] = $TEMPrECORD;
					if($tempArray['tkey'] == ''){					
						die();
					}
					$getArray = ckData($tempArray);
					$getSelectArray = $getArray[0];
					$TEMPrECORD = $getArray[1];
					
				}
				if( $dataArray['type'] == 'module_btn' ){				
					$tempArray['tkey'] = getButton($dataArray);;
					$tempArray['two'] = $TEMPrECORD;
					if($tempArray['tkey'] == ''){
						die();
					}				
					$getArray = ckData($tempArray);
					$getSelectArray = $getArray[0];
					$TEMPrECORD = $getArray[1];
				}
			}
			$x++;
		}
		
		
		$t_dsc = "
		update `opt_record` 
		set `questions_power_dsc`='".json_encode($TEMPrECORD)."' 
		where `num`='".$row['num']."'
		";
		echo $t_dsc."<br>";
		$ODb->query($t_dsc) or die("更新資料出錯，請聯繫管理員。");
	}
}

//回傳此題目的對話資料
function get_Questions_Array(){
$ODb = new run_db("mysql",3306);      //建立資料庫物件
$tempArray = array();
try {
$sql_dsc = "
select `a`.`num`,`a`.`main_data_num`,`b`.`num` as `targetNum`,`b`.`operation_data_num`  
from `operation_data` as `a` 
left join `questions_data` as `b` on `b`.`operation_data_num`=`a`.`num` 
where `a`.`main_data_num`='".$_GET['mainID']."' 
order by `a`.`num`,`targetNum`";
	$res = $ODb->query($sql_dsc);
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "<br>".$sql_dsc;
}
while($row = mysql_fetch_array($res)){
	$tempArray[$row['targetNum']] = 0;
}
return $tempArray;
$ODb->close();	
}

$ODb->close();
?>
<script language="javascript">

</script>

</body>
</html>