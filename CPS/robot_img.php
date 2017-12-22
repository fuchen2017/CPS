<?php
switch($_GET['head_type']){
case "1":
$src_dsc = 'http://120.108.208.22:8080/cs/cs.exe?template=Library%2FTemplates%2FServer%2FSpeech.xml&character=CarlaHead&addons=&voice=Neo%20Hui&autoactionlevel=1&flashversion=9&size=650,488&actionscript=3&Text='.$_GET['dsc'];
$img_dsc='userf2.png';

break;
case "2":
$src_dsc = 'http://120.108.208.22:8080/cs/cs.exe?template=Library%2FTemplates%2FServer%2FSpeech.xml&character=CarlHead&addons=&voice=Neo%20Liang&autoactionlevel=2&flashversion=9&size=700,515&actionscript=3&Text='.$_GET['dsc'];
$img_dsc='userm1.png';

break;
case "3":
$src_dsc = 'http://120.108.208.22:8080/cs/cs.exe?template=Library%2FTemplates%2FServer%2FSpeech.xml&character=CarlHead&addons=&voice=Neo%20Liang&autoactionlevel=2&flashversion=9&size=700,515&actionscript=3&Text='.$_GET['dsc'];
$img_dsc='userm2.png';

break;

case "4":
$src_dsc = 'http://120.108.208.22:8080/cs/cs.exe?template=Library%2FTemplates%2FServer%2FSpeech.xml&character=CarlHead&addons=&voice=Neo%20Liang&autoactionlevel=2&flashversion=9&size=700,515&actionscript=3&Text='.$_GET['dsc'];
$img_dsc='userm3.png';

break;

default:
$src_dsc = 'http://120.108.208.22:8080/cs/cs.exe?template=Library%2FTemplates%2FServer%2FSpeech.xml&character=TashaHead&addons=&voice=Neo%20Hui&autoactionlevel=2&customhair=tashahair&customtop=shirt1&flashversion=9&size=650,488&actionscript=3&Text='.$_GET['dsc'];
$img_dsc='userf1.png';

break;

}
//備註：可以使用&size=100,73來控制頭像大小
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>合作問題解決數位學習系統</title>
</head>
<style>
img{
	margin-left:70px;
}
</style>
<body>
<!--
<embed  name="Movie1" width="123" height="100" src="<?php echo $src_dsc;?>" quality="high" bgcolor="#6cd5cf"  menu="false" flashvars="" type="application/x-shockwave-flash">
-->
<img src="./images/<?php echo $img_dsc;?>">
</body>
</html>