<?php

	include "config.php";
	
	$path = date('Ymd', time());
	
	$error = "";
	$msg = "";
	$fileElementName = 'fileToUpload';
	$uploaddir = 'image';	//设定上传路径
	$fileext = fileext($_FILES[$fileElementName]['name']); 	//取得文件护展名
	$_MAX_IMAGE_FILE_SIZE = 1024 * 4096;  //设定上传文件允许最大值4M
	$_IMAGE_FILE_EXT = array('jpg','gif','png','jpeg','swf','zip');	//读取允许上传类型

	if(!empty($_FILES[$fileElementName]['error'])) {
		switch($_FILES[$fileElementName]['error']) {
			case '1':
				$error = '上传文件大小超过服务器的最大限制！';
				break;
			case '2':
				$error = '上传的文件超过最大限制!';
				break;
			case '3':
				$error = '上传的文件只有一部分上传!';
				break;
			case '4':
				$error = '无任何文件上传!';
				break;

			case '6':
				$error = '临时文件夹丢失!';
				break;
			case '7':
				$error = '文件写入磁盘失败!';
				break;
			case '8':
				$error = '不允许上传类型!';
				break;
			case '999':
			default:
				$error = 'No error code avaiable';
		}
	}elseif(empty($_FILES[$fileElementName]['tmp_name']) || $_FILES[$fileElementName]['tmp_name'] == 'none') {
		$error = '无任何文件上传..';
	}elseif(!in_array(strtolower($fileext), $_IMAGE_FILE_EXT)) {
		$error = "非法文件类型."; 
	}elseif(@filesize($_FILES[$fileElementName]['tmp_name']) > $_MAX_IMAGE_FILE_SIZE) {
		$error = "单个文件大小超过限制.";
	}else{
		//文件名称以及路径名称		ctd_map.jpg
		//$uploadfile = $uploaddir."/".$rnd_filename.".".$fileext;
		//放到tmp物理目录下的临时文件
		$tmp_file = uniqid().".".$fileext;
		$msg .= " File Name: " . $_FILES[$fileElementName]['name'] . ", ";
		$msg .= " File Size: " . @filesize($_FILES[$fileElementName]['tmp_name']);
		$msg .= " File Type: " . $fileext;
		$msg .= " File Path: " . $tmp_file;
		
		//获取远程目录
		$rsp = $nanoyun->get_list(SPACENAME, $uploaddir);
		$dir = json_decode($rsp);
		if(!($dir -> lists -> $path)) {
			$rsp = $nanoyun->make_dir(SPACENAME, $uploaddir.'/'.$path);
		}
		$filehandle = fopen($_FILES[$fileElementName]['tmp_name'], 'rb');
		$filename = $uploaddir.'/'.$path.'/'.$tmp_file;//指定在云存储中的写入位置
		$res = $nanoyun->write_file(SPACENAME, $filename, $filehandle);
		$res = json_decode($res);
		fclose($filehandle);
		if(!connect()) {
			$error = '数据库连接失败！';
		}else{
			if($res -> success) {
				$url = 'http://other.52b25d4165d52.d01.nanoyun.com/'.$filename;
				$now = date('Y-m-d H:i:s', time());
				$sql = "insert into nano_pic(title, `desc`, url, uid, add_time, isshow) values('{$_POST['title']}', '{$_POST['desc']}', '{$url}', 0, '{$now}', 'yes')";
				if(mysql_query($sql)) {
					$msg = '上传成功';
				}else{
					$msg = '上传失败';
				}
			}
		}
		
		//for security reason, we force to remove all uploaded file
		@unlink($_FILES[$fileElementName]);
	}
	echo "{";
	echo				"error: '" . $error . "',\n";
	echo				"msg: '" . $msg . "'\n";
	echo "}";
?>