<?php
defined('APP_DIR') or exit();
echo '<?php';
?>

defined('APP_DIR') or exit();
use Zodream\Html\Bootstrap\FormWidget;
/** @var $this \Zodream\Template\View */
$this->title = '';
?>


<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">增加</h3>
	</div>
	<div class="panel-body">
		<?='<?='?>FormWidget::begin($model)
		->hidden('id')
<?php foreach ($data as $item):?>
        -><?=$item?>

<?php endforeach;?>
		->button()
		->end();
		?>
		<p><?='<?='?>$model->getFirstError()?></p>
	</div>
</div>
