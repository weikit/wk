<?php
use yii\helpers\Html;
use weikit\modules\web\assets\WebAsset;

WebAsset::register($view);
?>
<?php $view->beginPage() ?>
<!DOCTYPE html>
<html lang="<?php $app->language ?>">
<head>
	<meta charset="<?php $app->charset ?>" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<?php $view->registerCsrfMetaTags() ?>
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
	<title><?= Html::encode($this->title) ?></title>
	<script type="text/javascript">
		var require = { urlArgs: 'v=20190101' };
		if(navigator.appName == 'Microsoft Internet Explorer'){
			if(navigator.userAgent.indexOf("MSIE 5.0")>0 || navigator.userAgent.indexOf("MSIE 6.0")>0 || navigator.userAgent.indexOf("MSIE 7.0")>0) {
				alert('您使用的 IE 浏览器版本过低, 推荐使用 Chrome 浏览器或 IE8 及以上版本浏览器.');
			}
		}

	</script>
	<?php $view->head() ?>
</head>
<body>
	<?php $view->beginBody() ?>

	<div class="loader" style="display:none">
		<div class="la-ball-clip-rotate">
			<div></div>
		</div>
	</div>
