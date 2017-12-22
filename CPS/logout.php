<?php
	session_start();
  //包含需求檔案 ------------------------------------------------------------------------
	include("./bcontroller/class/common_lite.php");
	$gowhere = "login.php";
	session_destroy();
	ri_jump($gowhere);	
?>

