<?php
	
	define('APPKEY', '2411138751');//设置App Key
	define('APPSECRET', 'cfed84fa278f4a8c85e6d59f28f47ceb');//设置App Secret
	define('SPACENAME', 'other');//空间名称

	include "nanoyun.class.php";
	$nanoyun = new Nanoyun(APPKEY, APPSECRET);
	
	//数据库连接
	function connect() {
		$link = mysql_connect('115.28.51.190', 'other', 'epo0ch');
		mysql_select_db('nano');
		mysql_set_charset('utf8');
		return $link;
	}

	/**
	 * 取得文件扩展
	 *
	 * @param $filename 文件名
	 * @return 扩展名
	 */
	function fileext($filename) {
		return strtolower(trim(substr(strrchr($filename, '.'), 1, 10)));
	}
	
	
	//保存图片到nano
	function saveToNano($source, $target) {
		$filehandle = fopen($source, 'rb');
		global $nanoyun;
		$res = $nanoyun->write_file(SPACENAME, $target, $filehandle);
		$res = json_decode($res);
		fclose($filehandle);
		return $res -> success;
	}