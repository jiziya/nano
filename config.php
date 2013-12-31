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
	
	/**
	 *	图片裁剪
	 *
	 */
	/* function imageCut($src_img, $width = 220) {
		list($src_w, $src_h) = getimagesize($src_img);  // 获取原图尺寸
		$dst_scale = $src_scale = $src_h / $src_w; // 原图长宽比
		
		$w = intval($src_w);
		$h = intval($dst_scale*$w);
		$x = 0;
		$y = ($src_h - $h)/3;

		// 剪裁
		$source=imagecreatefromjpeg($src_img);
		$croped=imagecreatetruecolor($w, $h);
		imagecopy($croped,$source,0,0,$x,$y,$src_w,$src_h);
		// 缩放
		$scale = $width/$w;
		$target = imagecreatetruecolor($width, $src_h * $dst_scale);
		$final_w = intval($w*$scale);
		$final_h = intval($h*$scale);
		imagecopysampled($target,$croped,0,0,0,0,$final_w,$final_h,$w,$h);
		// 保存
		//$timestamp = time();
		$file = explode('.', $src_img);
		$path = $file[0].'_'.$width.$file[1];
		imagejpeg($target, $path);
		imagedestroy($target);
		return $path;
	} */
	
	//保存图片到nano
	function saveToNano($source, $target) {
		$filehandle = fopen($source, 'rb');
		global $nanoyun;
		$res = $nanoyun->write_file(SPACENAME, $target, $filehandle);
		$res = json_decode($res);
		fclose($filehandle);
		return $res -> success;
	}