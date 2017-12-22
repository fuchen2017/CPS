<?php

 /*//index.php 應用程序頁面
 header('Content-Type:text/html; charset=utf-8');
 $sso_address      = 'http://www.c.com/sso_login.php'; //你SSO所在的域名
 $callback_address = 'http://' . $_SERVER['HTTP_HOST']
     . str_replace('index.php', '', $_SERVER['SCRIPT_NAME'])
     . 'callback.php'; //callback地址用於回撥設定cookie

 if (isset($_COOKIE['sign'])) {
     exit("歡迎您{$_COOKIE['sign']} <a href=\"login.php?logout\">退出</a>");
 } else {
     echo '您還未登入 <a href="' . $sso_address . '?callback=' . $callback_address . '">點此登入</a>';
 }
 ?>
 <iframe src="<?php echo $sso_address ?>?callback=<?php echo $callback_address ?>" frameborder="0" width="0"
         height="0"></iframe>

 <?php
 //callback.php 回撥頁面用來設定跨域COOKIE
 header('Content-Type:text/html; charset=utf-8');
 if (empty($_GET)) {
     exit('您還未登入');
 } else {
     foreach ($_GET as $key => $val) {
         setcookie($key, $val, 0, '');
     }
     header("location:index.php");
 }
 ?>

 <?php
 //connect.php 用來檢測登入狀態的頁面，內嵌在頁面的iframe中
 header('Content-Type:text/html; charset=utf-8');
 if (isset($_COOKIE['sign'])) {
     $callback = urldecode($_GET['callback']);
     unset($_GET['callback']);
     $query    = http_build_query($_COOKIE);
     $callback = $callback . "?{$query}";
 } else {
     exit;
 }
 ?>
 <html>
 <script type="text/javascript">top.location.href = "<?php echo $callback; ?>";</script>
 </html>
 <?php
  //login.php SSO登入頁面
 header('Content-Type:text/html; charset=utf-8');
 if (isset($_GET['logout'])) {
     setcookie('sign', '', -300);
     unset($_GET['logout']);
     header('location:index.php');
 }

 if (isset($_POST['username']) && isset($_POST['password'])) {
     setcookie('sign', $_POST['username'], 0, '');
     header("location:" . $_POST['callback'] . "?sign={$_POST['username']}");
 }

 if (empty($_COOKIE['sign'])) {
     ?>

     <form method="post">
         <p>使用者名稱：<input type="text" name="username"/></p>
         <p>密 碼：<input type="password" name="password"/></p>
          <input type="hidden" name="callback" value="<?php echo $_GET['callback']; ?>"/>
         <input type="submit" value="登入"/>
     </form>


     <?php
 } else {
     $query = http_build_query($_COOKIE);
     echo "系統檢測到您已登入 {$_COOKIE['sign']} <a href=\"{$_GET['callback']}?{$query}\">授權</a> <a href=\"?logout\">退出</a>";
 }
*/



$ch = curl_init();
curl_setopt($ch , CURLOPT_URL , "https://cosci.tw/run/?name=Ck1Caz1503463765710");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
echo $result;
curl_close($ch);
//https://cosci.tw/run/?name=Ck1Caz1503463765710
//https://shopee.tw/
?>
