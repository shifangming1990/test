<?php
@session_start();
error_reporting(E_ALL^E_NOTICE^E_WARNING);
date_default_timezone_set('Asia/Shanghai');

$MySql_host = 'localhost';
$MySql_username = 'root';
$MySql_password = 'Cxnews@2018';
$MySql_name = 'zxcx';
$MySql_prefix= 'system_';

require_once("mysql.class.php");
require_once("request.class.php");

$row = $dsql->GetOne("SELECT * FROM `#@__token` WHERE id=1");
$wx_appid  = $row['appid'];
$wx_secret = $row['appsecret'];
require_once("weixin.jssdk.php");

$api_baseurl = "https://app.cxbtv.cn/palmchangxing-api";
?>