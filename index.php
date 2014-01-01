<?php
	/**
	 * 读取当前空间信息测试
	 */
	include "config.php";
	$admin = $_GET['admin'] ? $_GET['admin'] : '';
	$rsp = $nanoyun->get_space_usage(SPACENAME);
	$rsp = json_decode($rsp);
	if($admin == 'other' && $_GET['del']) {
		if(!connect()) {
			$error = '数据库连接失败！';
		}else{
			$sql = "update nano_pic set isshow = 'no' where id = {$_GET['del']}";
			$res = mysql_query($sql);
		}
	}
?>
<!DOCTYPE HTML>
<html>
<head>
	<meta charset="utf-8">
	<title>22：34 - 新年快乐！</title>
	<style type="text/css">
		body { padding:0; margin:0; background:url(images/bg.jpg); font-size: 12px; color: #444;}
		a { font-size: 12px; text-decoration: none; color: #9E7E6B;}
		a:hover { text-decoration: underline;}
		img { border: 0;}
		#wrap { width: 1250px; margin: 0 auto;}
		#container { width: 1250px; margin: 0 auto;}
		.item { background: #fff; width: 220px; margin: 10px; float: left; box-shadow:0px 1px 3px rgba(0, 0, 0, 0.3);}
		.item .desc { padding: 0 16px; margin: 10px 0; overflow: hidden; word-wrap: break-word;}
		.item .head { width: 50px; height: 50px; float: left;}
		.item .s { float: left; border-left: 1px solid #ccc; height: 50px;}
		.pic img { width: 220px;}
		.item .title { border-top: 1px solid #f2f2f2; padding: 0 16px; background: #fafafa;}
		.title .img { width: 34px; height: 34px; display: block; margin: 16px 0; float: left;}
		.title .text { margin-left: 51px; height: 51px; border-left: 1px solid #f2f2f2; padding: 15px 0 0 15px; line-height: 1.5;}
		.title .inner { height: 37px; overflow: hidden;}
		.clearfix { clear: both;}
		.replyButton { display: block; position: absolute; right: 0; bottom: 0; width: 26px; height: 16px; background: url(images/home_comment_act_icon.png) 0 0 no-repeat; cursor: pointer; -webkit-transition: opacity .2s linear; -webkit-transition-property: opacity,right,bottom; opacity: 0;}
		.replyButton:hover { background-position: 0 -20px; visibility: visible; opacity: 1;}
		.del { opacity: 0.5; -webkit-transition: opacity .2s linear; -webkit-transition-property: opacity,right,bottom;}
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
					<img id="loading" src="images/loading.gif" style="display:none;">
				</form>
			</p>
		</div>
		<div id="container">
			<?php
				if(!connect()) {
					$error = '数据库连接失败！';
				}else{
					$sql = "select * from nano_pic where isshow = 'yes' order by add_time desc";
					$res = mysql_query($sql);
					if($res) {
						while($row = mysql_fetch_assoc($res)) {
							$fileext = fileext($row['url']);
							if($fileext != 'gif') {
								$row['newurl'] = 'phpThumb/phpThumb.php?src='.$row['url'].'&w=220';
							}else{
								$row['newurl'] = $row['url'];
							}
							echo '
								<div class="item">
									<div class="pic">
										<a href="'.$row['url'].'" target="_blank"><img src="'.$row['newurl'].'" /></a>
										<p class="desc">'.$row['desc'].'</p>
										<div class="title">
											<a href="#" class="img"></a>
											<div class="text">
												<div class="inner">
													<a href="#">'.$row['uid'].'</a>&nbsp;上传
													<a title="删除" class="replyButton" _id="'.$row['id'].'"></a>
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
	<script src="js/jquery-1.10.2.min.js"></script>
	<script src="js/jquery.masonry.min.js"></script>
	<script type="text/javascript" src="js/ajaxfileupload.js"></script>
	<script>
		$(document).ajaxStart(function() {
			$("#loading").show();
		}).ajaxComplete(function() {
			$("#loading").hide();
		});
		
		$(function() {
			var $container = $('#container');
			$container.imagesLoaded(function(){
				$container.masonry({
					itemSelector : '.item'
				});
			});
			
			$('.replyButton').on('click', function() {
				var rule = '<?php echo $admin ?>';
				if(rule == 'other') {
					var del = $(this);
					var _id = $(this).attr('_id');
					$.ajax({
						url: '?del=' + _id,
						type: 'get',
						success: function(e) {
							del.parentsUntil('.item').addClass('del');
						}
					});
				}else{
					alert('对不起，您不是管理员！');
				}
			});
		})
		
		function ajaxFileUpload() {
			var title = $('#title').val(),
				desc = $('#desc').val();
			$.ajaxFileUpload({
				url:'doajaxfileupload.php',
				secureuri:false,
				fileElementId:'fileToUpload',
				dataType: 'json',
				data:{name:'logan', id:'id', title: title, desc: desc},
				success: function(data, status) {
					if(typeof(data.error) != 'undefined') {
						if(data.error != '') {
							alert(data.error);
						}else{
							alert(data.msg);
							window.location = '/nano';
						}
					}
				},
				error: function(data, status, e) {
					alert(e);
				}
			})
			return false;
		}
	</script>
</body>
</html>