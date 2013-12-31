<?php
/**
 * 读取当前空间信息测试
 */
include "config.php";
$admin = $_GET['admin'] ? $_GET['admin'] : '';
$rsp = $nanoyun->get_space_usage(SPACENAME);
$rsp = json_decode($rsp);
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<style type="text/css">
body { padding:0; margin:0; background:url(bg.jpg); font-size: 12px; color: #444;}
a { font-size: 12px; text-decoration: none; color: #9E7E6B;}
a:hover { text-decoration: underline;}
#wrap { width: 1250px; margin: 0 auto;}
#container { width: 1250px; margin: 0 auto;}
.item { background: #fff; width: 220px; margin: 10px; float: left; -moz-box-shadow:0px 1px 3px rgba(0, 0, 0, 0.3); -webkit-box-shadow:0px 1px 3px rgba(0, 0, 0, 0.3);}
.item .desc { padding: 0 16px; margin: 10px 0; overflow: hidden; word-wrap: break-word;}
.item .head { width: 50px; height: 50px; float: left;}
.item .s { float: left; border-left: 1px solid #ccc; height: 50px;}
.pic img { width: 220px;}
.item .title { border-top: 1px solid #f2f2f2; padding: 0 16px;}
.title .img { width: 34px; height: 34px; display: block; margin: 16px 0; float: left;}
.title .text { margin-left: 51px; height: 51px; border-left: 1px solid #f2f2f2; padding: 15px 0 0 15px; line-height: 1.5;}
.title .inner { height: 37px; overflow: hidden;}
.clearfix { clear: both;}
</style>
</head>
<body>
	<div id="wrap">
		<div id="header">
			<p><?php echo $rsp -> space -> name ?>已使用<?php echo round(($rsp -> space -> size)/1024/1024, 2) ?>M空间</p>
			<p>
				<form name="form" action="" method="POST" enctype="multipart/form-data">
					标题：<input type="text" name="name" id="title">
					简介：<input type="text" name="desc" id="desc">
					<input id="fileToUpload" type="file" size="45" name="fileToUpload" class="input">
					<button class="button" id="buttonUpload" onclick="return ajaxFileUpload();">Upload</button>
					<img id="loading" src="loading.gif" style="display:none;">
				</form>
			</p>
		</div>
		<div id="container">
			<?php
				/* $list = $nanoyun->get_list(SPACENAME, 'image');
				$lists = json_decode($list);
				foreach($lists -> lists as $v) {
					echo '<div class="item"><div class="pic"><img src="'.$v -> url.'" /><p>设置ajax请求的开关,如需动态加载、需要打开这个开关</p></div></div>';
				} */
				if(!connect()) {
					$error = '数据库连接失败！';
				}else{
					$sql = "select * from nano_pic where isshow = 'yes' order by add_time desc";
					$res = mysql_query($sql);
					if($res) {
						while($row = mysql_fetch_assoc($res)) {
							echo '
								<div class="item">
									<div class="pic">
										<a href="'.$row['url'].'" target="_blank"><img src="phpThumb/phpThumb.php?src='.$row['url'].'&w=220" /></a>
										<p class="desc">'.$row['desc'].'</p>
										<div class="title">
											<a href="#" class="img"></a>
											<div class="text">
												<div class="inner">
													<a href="#">'.$row['uid'].'</a>&nbsp;上传
													<a title="回复" class="replyButton"></a>
												</div>
											</div>
										</div>
									</div>
								</div>';
						}
						echo '<div class="clearfix"></div>';
					}
					mysql_close(connect());
				}
			?>
		</div>
	</div>
	<script src="jquery-1.10.2.min.js"></script>
	<script src="jquery.masonry.min.js"></script>
	<script type="text/javascript" src="ajaxfileupload.js"></script>
	<script type="text/javascript">
		$(function() {
			var $container = $('#container');
			$container.imagesLoaded(function(){
				$container.masonry({
					itemSelector : '.item'
				});
			});
		})
		
		function ajaxFileUpload() {
			$("#loading").ajaxStart(function() {
				$(this).show();
			}).ajaxComplete(function() {
				$(this).hide();
			});
			var title = $('#title').val(),
				desc = $('#desc').val();
			$.ajaxFileUpload({
				url:'doajaxfileupload.php',
				secureuri:false,
				fileElementId:'fileToUpload',
				dataType: 'JSON',
				data:{name:'logan', id:'id', title: title, desc: desc},
				success: function (data, status) {
					if(typeof(data.error) != 'undefined') {
						if(data.error != '') {
							alert(data.error);
						}else{
							alert(data.msg);
							window.location = '/nano';
						}
					}
				},
				error: function (data, status, e) {
					alert(e);
				}
			})
			return false;
		}
	</script>
</body>
</html>