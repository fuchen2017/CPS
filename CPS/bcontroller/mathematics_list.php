<?php
	session_start();
	/*
		備註：此頁面管理數學模組資料
	*/
	//包含需求檔案 ------------------------------------------------------------------------
	include("./class/common_lite.php");
	if($_SESSION['loginType'] == ''){
		ri_jump("logout.php");
	}

	
	//宣告變數 ----------------------------------------------------------------------------
	$ODb = new run_db("mysql",3306);      //建立資料庫物件

	//解碼
	foreach($_GET as $key => $value){
		$_GET[$key] = base64_decode($value);
	}
	
	$menu_array = array(
		'url' => 'member_list.php',
		'dsc' => '成員管理',
		'imgurl' => 'images/icon_user.png'
	);

	if($_SESSION['loginType'] == "TEACHER"){
		$whereDscArray[] = " `create_user`='".$_SESSION['swTeacherNum']."' and `create_user_type`='TEACHER' ";
		$menu_array = array(
			'url' => 'memberListS.php',
			'dsc' => '學生管理',
			'imgurl' => 'images/icon_user.png'
		);
		
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
	//取出單元資料
	$sql_dsc = "select * from `mathematics_module_list` ".$where_dsc." order by `num` ";//管理員資料
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){
		$sql_data['num'] = base64_encode($row['num']);	
		$sql_data['c_title'] = $row['c_title'];
		$sql_data['c_memo'] = $row['c_memo'];
		$sql_data['c_short_img'] = $row['c_short_img'];		
		$sql_data['c_type'] = $row['c_type'];		
		$sql_data['up_date'] = $row['up_date'];
		switch($row['c_type']){
		case "m_one":
		$data_one_array[] = $sql_data;
		break;
		case "m_two":
		$data_two_array[] = $sql_data;
		break;
		case "m_three":
		$data_three_array[] = $sql_data;
		break;	
		case "m_4"://溫度計猜公式的題目模組 1
		$data_4_array[] = $sql_data;
		break;
		case "m_5"://溫度計猜公式的題目模組 2
		$data_5_array[] = $sql_data;
		break;
		case "m_6"://溫度計猜公式的題目模組 3
		$data_6_array[] = $sql_data;
		break;
		default:
		break;
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
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"> </script>
<!-- slider -->
<link rel="stylesheet" href="css/jcarousel.responsive.css">
<script src="js/jquery.jcarousel.min.js"></script>
<script src="js/jcarousel.responsive.js"></script><!-- 跑馬燈模組 -->
<!--img preview-->
<script src="js/main.js"></script>
<!--tabs-->
<script>
	$(function(){
		var _showTab = <?php if(is_numeric($_GET['tab'])){	echo $_GET['tab'];}else{echo '0';}?>;
		var $defaultLi = $('ul.tabs li').eq(_showTab).addClass('active');
		$($defaultLi.find('a').attr('href')).siblings().hide();
	
		$('ul.tabs li').click(function() {
			var $this = $(this),
				_clickTab = $this.find('a').attr('href');
			$this.addClass('active').siblings('.active').removeClass('active');
			$(_clickTab).stop(false, true).fadeIn().siblings().hide();
			return false;
		}).find('a').focus(function(){
			this.blur();
		});
	});

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
    	<li><a href="<?php echo $menu_array['url'];?>" title="<?php echo $menu_array['dsc'];?>"><img src="<?php echo $menu_array['imgurl'];?>" /><?php echo $menu_array['dsc'];?></a></li>
		<li><a href="logout.php" title="登出系統" ><img src="images/icon_logout.png" />登出系統</a></li>
		
    </ul>
</aside>
<!--右側-->
<div id="science_list">
    <!--模組選單-->
    <h2>新增模組：</h2>
    <div class="jcarousel-wrapper">
        <div class="jcarousel">
            <ul>
                <li><div class="model">數學模組</div> <a href="mathematics_module/add_mathematics_1.php?tab=<?php echo base64_encode($_GET['tab']);?>" class="button" title="新增模組">新增模組</a></li>
                <li><div class="model">20遊戲模組(練習題)</div> <a href="mathematics_module/add_mathematics_2.php?tab=<?php echo base64_encode($_GET['tab']);?>" class="button" title="新增模組">新增模組</a></li>
                <li><div class="model">20遊戲模組</div> <a href="mathematics_module/add_mathematics_3.php?tab=<?php echo base64_encode($_GET['tab']);?>" class="button" title="新增模組">新增模組</a></li>
				<li><div class="model">溫度計猜公式模組 1</div> <a href="mathematics_module/add_mathematics_4.php" class="button" title="新增模組">新增模組</a></li>
                <li><div class="model">溫度計猜公式模組 2</div> <a href="mathematics_module/add_mathematics_5.php" class="button" title="新增模組">新增模組</a></li>
                <li><div class="model">溫度計猜公式模組 3</div> <a href="mathematics_module/add_mathematics_6.php" class="button" title="新增模組">新增模組</a></li>            
			</ul>
        </div>
        <a href="#" class="jcarousel-control-prev">&lsaquo;</a>
        <a href="#" class="jcarousel-control-next">&rsaquo;</a>
    </div>
    <!--模組選單End-->
    
    <!--編輯-->
    <h2>現有模組：</h2>
    <div class="abgne_tab">
        <ul class="tabs">
            <li><a href="#tab0" >數學模組</a></li>
            <li><a href="#tab1" >20遊戲模組(練習題)</a></li>
            <li><a href="#tab2" >20遊戲模組</a></li>
            <li><a href="#tab3">溫度計猜公式模組 1</a></li>
            <li><a href="#tab4">溫度計猜公式模組 2</a></li>
            <li><a href="#tab5">溫度計猜公式模組 3</a></li>
			
        </ul>
    
        <div class="tab_container">
            <!--模組1-->
            <div id="tab0" class="tab_content">
                <ul class="list_info">
					<?php
					if(is_array($data_one_array)){
						foreach($data_one_array as $value){
						echo '
					<li>
                    	<a href="mathematics_module/edit_mathematics_1.php?num='.$value['num'].'&tab='.base64_encode(0).'" class="preview" title="數學模組"><img src="shortImg/'.$value['c_short_img'].'"  width="200" height="150"/></a>
                        <p>標題：'.$value['c_title'].'</p>
						<p>備註：'.$value['c_memo'].'</p>
                    </li>						
						';
						}
					}
					?>
                </ul>
            </div>
            <!--模組1 end -->
            <!--模組2-->
            <div id="tab1" class="tab_content">
                <ul class="list_info">
					<?php
					if(is_array($data_two_array)){
						foreach($data_two_array as $value){
						echo '
					<li>
                    	<a href="mathematics_module/edit_mathematics_2.php?num='.$value['num'].'&tab='.base64_encode(1).'" class="preview" title="20遊戲模組(練習題)"><img src="shortImg/'.$value['c_short_img'].'"  width="200" height="150"/></a>
                        <p>標題：'.$value['c_title'].'</p>
						<p>備註：'.$value['c_memo'].'</p>
                    </li>						
						';
						}
					}
					?>
                </ul>
            </div>
            <!--模組2 end -->			
            <!--模組3-->
            <div id="tab2" class="tab_content">
                <ul class="list_info">
					<?php
					if(is_array($data_three_array)){
						foreach($data_three_array as $value){
						echo '
					<li>
                    	<a href="mathematics_module/edit_mathematics_3.php?num='.$value['num'].'&tab='.base64_encode(2).'" class="preview" title="20遊戲模組"><img src="shortImg/'.$value['c_short_img'].'"  width="200" height="150"/></a>
                        <p>標題：'.$value['c_title'].'</p>
						<p>備註：'.$value['c_memo'].'</p>
                    </li>						
						';
						}
					}
					?>
                </ul>
            </div>
            <!--模組3 end -->
			<!--模組4-->        
            <div id="tab3" class="tab_content">
                <ul class="list_info">
					<?php
					if(is_array($data_4_array)){
						foreach($data_4_array as $value){
						echo '
					<li>
                    	<a href="mathematics_module/edit_mathematics_4.php?num='.$value['num'].'" class="preview" title="溫度計猜公式模組 1"><img src="shortImg/'.$value['c_short_img'].'"  width="200" height="150"/></a>
                        <p>標題：'.$value['c_title'].'</p>
						<p>備註：'.$value['c_memo'].'</p>
                    </li>						
						';
						}
					}
					?>
                </ul>
            </div>
			<!--模組4 end --> 				
			<!--模組5-->        
            <div id="tab4" class="tab_content">
                <ul class="list_info">
					<?php
					if(is_array($data_5_array)){
						foreach($data_5_array as $value){
						echo '
					<li>
                    	<a href="mathematics_module/edit_mathematics_5.php?num='.$value['num'].'" class="preview" title="溫度計猜公式模組 2"><img src="shortImg/'.$value['c_short_img'].'"  width="200" height="150"/></a>
                        <p>標題：'.$value['c_title'].'</p>
						<p>備註：'.$value['c_memo'].'</p>
                    </li>						
						';
						}
					}
					?>
                </ul>
            </div>
			<!--模組5 end --> 				
			<!--模組6-->        
            <div id="tab5" class="tab_content">
                <ul class="list_info">
					<?php
					if(is_array($data_6_array)){
						foreach($data_6_array as $value){
						echo '
					<li>
                    	<a href="mathematics_module/edit_mathematics_6.php?num='.$value['num'].'" class="preview" title="溫度計猜公式模組 3"><img src="shortImg/'.$value['c_short_img'].'"  width="200" height="150"/></a>
                        <p>標題：'.$value['c_title'].'</p>
						<p>備註：'.$value['c_memo'].'</p>
                    </li>						
						';
						}
					}
					?>
                </ul>
            </div>
			<!--模組6 end --> 
        </div>
    </div>
    <!--編輯End-->
</div>
<!--右側end-->
<?php 	$ODb->close();?>

</body>
</html>
