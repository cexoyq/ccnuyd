<?php
//echo phpinfo();
dl("libthroam.so");
$iipp=$_SERVER["REMOTE_ADDR"];
echo $iipp . "</br>";
////////////////////
$user_IP = ($_SERVER["HTTP_VIA"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
$user_IP = ($user_IP) ? $user_IP : $_SERVER["REMOTE_ADDR"];
echo $user_IP . "</br>";
//////////////////////
if(getenv('HTTP_CLIENT_IP')) {
$onlineip = getenv('HTTP_CLIENT_IP');
} elseif(getenv('HTTP_X_FORWARDED_FOR')) {
$onlineip = getenv('HTTP_X_FORWARDED_FOR');
} elseif(getenv('REMOTE_ADDR')) {
$onlineip = getenv('REMOTE_ADDR');
} else {
$onlineip = $HTTP_SERVER_VARS['REMOTE_ADDR'];
}
echo $onlineip . "</br>";
//////////////////////////
echo get_real_ip();
/////////////////////////////
$ret = otest.chkticket("pm8DIQivseZdVJZBRPCBFHU59GP5YCWNSYQC "," APPID_TEST ",$iipp);
echo $ret;
//////////////////////////////////
function get_real_ip(){
$ip=false;
if(!empty($_SERVER["HTTP_CLIENT_IP"])){
$ip = $_SERVER["HTTP_CLIENT_IP"];
}
if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
$ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
if ($ip) { array_unshift($ips, $ip); $ip = FALSE; }
for ($i = 0; $i < count($ips); $i++) {
if (!eregi ("^(10|172\.16|192\.168)\.", $ips[$i])) {
$ip = $ips[$i];
break;
}
}
}
return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
}


?>
