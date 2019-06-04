<?php
/* @var $this yii\web\View */
/* @var $generator \weikit\generators\addon\Generator */
?>
<?= '<?php' ?>


class <?= ucfirst($generator->name) ?>ModuleProcessor extends WeModuleProcessor {
	public function respond() {
		$content = $this->message['content'];
		//这里定义此模块进行消息处理时的具体过程, 请查看微擎文档来编写你的代码
	}
}