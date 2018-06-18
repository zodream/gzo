<?php
defined('APP_DIR') or exit();
echo '<?php';
?>

defined('APP_DIR') or exit();
use Zodream\Infrastructure\Html;
use Zodream\Template\View;
use Zodream\Html\Bootstrap\TableWidget;
/** @var $this View */
/** @var $page \Zodream\Html\Page */
$this->title = '';
?>
<div class="row">
	<div class="col-md-3 col-md-offset-2">
        <?='<?='?>Html::a('新增', '<?=$name?>/add', ['class' => 'btn btn-primary'])?>
	</div>
</div>

<?='<?='?>TableWidget::show([
    'page' => $page,
    'columns' => [
<?php foreach ($data as $key => $item):?>
        '<?=$key?>' => '<?=ucwords(str_replace('_', ' ', $item))?>',
<?php endforeach;?>
        [
            'label' => 'Action',
            'key' => 'id',
            'format' => function($id) {
                return Html::a('查看', ['<?=$name?>/view', 'id' => $id]).
                    Html::a('编辑', ['<?=$name?>/edit', 'id' => $id]).
                    Html::a('删除', ['<?=$name?>/delete', 'id' => $id]);
            }
        ]
    ]
])?>
