<?php
define('APPKEY', '2411138751');//设置App Key
define('APPSECRET', 'cfed84fa278f4a8c85e6d59f28f47ceb');//设置App Secret
define('SPACENAME', 'other');//空间名称

include "nanoyun.class.php";
$nanoyun = new Nanoyun(APPKEY, APPSECRET);

function connect() {
	$link = mysql_connect('115.28.51.190', 'other', 'epo0ch');
	mysql_select_db('nano');
	mysql_set_charset('utf8');
	return $link;
}