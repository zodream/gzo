<?php
use Zodream\Template\View;
use Zodream\Html\Bootstrap\TableWidget;
use Zodream\Html\Bootstrap\BreadcrumbWidget;
/** @var $this View */

$this->title = '执行sql语句';
?>

<?=BreadcrumbWidget::show([
        'links' => $crumbs
])?>

<div class="page-tip">
    <p class="blue">操作提示</p>
    <ul>
        <li>执行sql语句</li>
        <li>默认显示所有数据库名、点击数据库显示此数据库下的所有数据表、点击数据表显示所有列信息</li>
    </ul>
    <span class="toggle"></span>
</div>


<form class="form-default" action="<?=$this->url('./home/sql')?>" method="post">
    <div class="input-group">
        <label for="query1">sql语句</label>
        <textarea name="query" id="query1" rows="5" placeholder="sql语句"><?=$query?></textarea>
    </div>
    <button class="btn">执行</button>
    <input type="hidden" name="schema" value="<?=$schema?>">
    <input type="hidden" name="table" value="<?=$table?>">
</form>

<div class="content-box" style="overflow: auto">
    <?=TableWidget::show([
            'data' => $data
    ])?>
</div>