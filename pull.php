<?php
	include "config.php";
	
	//抓取图片
	ini_set('memory_limit', '250M');
	set_time_limit(0);
	
	/**
	 * 抓取yousei-raws站所有文章中的大图，保存到服务器
	 */
	/* for($page=0; $page<47; $page++) {
		$url = 'http://yousei-raws.org/news?page='.$page;
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$data = curl_exec($ch);
		curl_close($ch);
		preg_match_all('/<h1>(.*?)<\/h1>/', $data, $title);
		//$title[1];所有标题
		preg_match_all('/<div class="views-field-changed">[\s]*<span class="field-content">(.*?)<\/span>[\s]*<\/div>/', $data, $time);
		//$time[1];所有时间
		preg_match_all('/<img src="(.*?)"/', $data, $img);
		//$img[1];所有图片
		$count = count($title[1]);
		for($i=0; $i<$count; $i++) {
			$arr[$i]['title'] = $title[1][$i];
			$arr[$i]['time'] = date('Y-m-d H:i:s', strtotime($time[1][$i]));
			$arr[$i]['img'] = $img[1][$i];
		}
		$link = connect();
		if(!$link) {
			$error = '数据库连接失败！';
		}else{
			foreach($arr as $k => $v) {
				$sql = "insert into pic(title, time, img) values('{$v['title']}', '{$v['time']}', '{$v['img']}')";
				mysql_query($sql);
			}
		}
		echo '第'.$page.'页添加完成<br />';
	} */
	
	//版本2
	for($page=0; $page<47; $page++) {
		$url = 'http://yousei-raws.org/news?page='.$page;
		$html = file_get_html($url);
		$title = $html -> find('h1');
		$img = $html -> find('img');
		$time = $html -> find('.views-field-changed .field-content');
		$data = array();
		for($i=0; $i<count($title); $i++) {
			$data[$i]['title'] = $title[$i] -> plaintext;
			$data[$i]['img'] = $img[$i] -> src;
			$data[$i]['time'] = $time[$i] -> plaintext;
		}
		$link = connect();
		if(!$link) {
			$error = '数据库连接失败！';
		}else{
			foreach($data as $k => $v) {
				$sql = "insert into pic(title, time, img) values('{$v['title']}', '{$v['time']}', '{$v['img']}')";
				mysql_query($sql);
			}
		}
		echo '第'.$page.'页添加完成<br />';
	}
	
	//抓取到的图片存入nano云及服务器数据库添加记录
	$link = connect();
	if(!$link) {
		$error = '数据库连接失败！';
	}else{
		$sql = "select * from pic where time > '2011-09-07 07:36:00' order by time asc";
		$res = mysql_query($sql);
		
		$uploaddir = 'image';	//设定上传路径
		$path = 'yousei-raws';
		
		while($row = mysql_fetch_assoc($res)) {
			$ch = curl_init();
			$timeout = 5;
			curl_setopt($ch, CURLOPT_URL, $row['img']);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$content = curl_exec($ch);
			$info = curl_getinfo($ch);
			if ($info['http_code'] != 200){
				$content = NULL;
				continue;
			}
			$fileext = fileext($row['img']); 	//取得文件护展名
			$tmp_file = uniqid().".".$fileext;
			if($content)//保存图片到本地	
				@file_put_contents('img/'.$tmp_file, $content);
			curl_close($ch);
			

			$rsp = $nanoyun->get_list(SPACENAME, $uploaddir);
			$dir = json_decode($rsp);
			if(!($dir -> lists -> $path)) {
				$nanoyun->make_dir(SPACENAME, $uploaddir.'/'.$path);
			}
			$filename = $uploaddir.'/'.$path.'/'.$tmp_file;//指定在云存储中的写入位置
			$result = saveToNano('img/'.$tmp_file, $filename);
			if(!connect()) {
				$error = '数据库连接失败！';
			}else{
				if($result) {
					$url = 'http://other.52b25d4165d52.d01.nanoyun.com/'.$filename;
					$now = date('Y-m-d H:i:s', time());
					$sql = "insert into nano_pic(title, `desc`, url, uid, `type`, add_time, story_time, isshow) values('{$row['title']}', '{$row['title']}', '{$url}', 2, 'comic', '{$now}', '{$row['time']}', 'yes')";
					if(mysql_query($sql)) {
						$msg = '上传成功';
					}else{
						$msg = '上传失败';
					}
				}
			}
		}
	}
	
	
	/* $uploaddir = 'image';	//设定上传路径
	$path = 'yousei-raws';
	$source = 'C:/Users/other/Desktop/3871648c83497164832357b7f195c80d.jpg';
	//$source = 'http://yousei-raws.org/sites/default/files/imagecache/cover/covers/3871648c83497164832357b7f195c80d.jpg';
	saveToNano($source, 'image/1.jpg');
	exit;
	
	mysql_connect('localhost', 'root', 'root');
	mysql_select_db('test');
	$sql = "select * from pic order by time asc";
	$res = mysql_query($sql);
	while($row = mysql_fetch_assoc($res)) {
	//获取远程目录
		$rsp = $nanoyun->get_list(SPACENAME, $uploaddir);
		$dir = json_decode($rsp);
		if(!($dir -> lists -> $path)) {
			$nanoyun->make_dir(SPACENAME, $uploaddir.'/'.$path);
		}
		$fileext = fileext($row['img']); 	//取得文件护展名
		$tmp_file = uniqid().".".$fileext;
		$filename = $uploaddir.'/'.$path.'/'.$tmp_file;//指定在云存储中的写入位置
		$result = saveToNano($row['img'], $filename);
		if(!connect()) {
			$error = '数据库连接失败！';
		}else{
			if($result) {
				$url = 'http://other.52b25d4165d52.d01.nanoyun.com/'.$filename;
				$now = date('Y-m-d H:i:s', time());
				$sql = "insert into nano_pic(title, `desc`, url, uid, `type`, add_time, story_time, isshow) values('{$_POST['title']}', '{$_POST['desc']}', '{$url}', 2, 'comic', '{$now}', '{$row['time']}', 'yes')";
				if(mysql_query($sql)) {
					$msg = '上传成功';
				}else{
					$msg = '上传失败';
				}
			}
		}
	} */