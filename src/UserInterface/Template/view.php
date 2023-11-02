<?php
defined('APP_DIR') or exit();
$converters = [
    'content' => 'html',
    'thumb' => 'img',
    'image' => 'img',
    'url' => 'url',
    'email' => 'email',
    'update_at' => 'datetime',
    'create_at' => 'datetime'
];
echo '<?php';
?>

defined('APP_DIR') or exit();
use Zodream\Html\Bootstrap\DetailWidget;
use Zodream\Template\View;
/** @var $this View */
$this->title = '';
?>


<div class="panel panel-default">
	<div class="panel-header">
		<h3 class="panel-title"><?='<?='?>$data['id']?></h3>
	</div>
	<div class="panel-body">
        <?='<?='?>DetailWidget::show([
                'data' => $model,
				'items' => [
<?php foreach ($data as $item):?>
                '<?=$item?>' => '<?=ucwords(str_replace('_', ' ', $item)).(in_array($item, $converters) ? ':'.$converters[$item] : '')?>',
<?php endforeach;?>
            ]
		])?>
	</div>
</div>
