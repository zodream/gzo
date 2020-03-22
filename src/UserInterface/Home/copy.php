<?php
use Zodream\Template\View;
/** @var $this View */

$this->title = '生成CRUD';

$js = <<<JS
bindCopy();
JS;

$this->registerJs($js, View::JQUERY_READY);
?>

<div class="page-tip">
    <p class="blue">操作提示</p>
    <ul>
        <li>进行数据表复制</li>
        <li>支持多个表同时复制到一个表</li>
    </ul>
    <span class="toggle"></span>
</div>

<form class="form-inline" data-type="post" action="<?=$this->url('./sql/copy')?>" method="post">
    <div class="panel copy-panel">
        <div class="panel-header">
            目标表：<span class="dist-item" data-action="table-select">请选择</span>
            &lt;-
            数据表：
            <span data-action="table-add" class="fa fa-plus"></span>
        </div>
        <div class="panel-body">
        </div>
    </div>

    <button class="btn">复制</button>
</form>

<div class="dialog-select">
    <div class="dialog-body">
        <select name="schame"></select>
        <select name="table"></select>
        <button>确定</button>
    </div>
</div>

<div class="dialog-column-select">
    <div class="dialog-body">
        <p>
            <input type="radio" name="type" value="0" checked>
            <input type="text" name="value" placeholder="请输入内容">
        </p>
        <p>
            <input type="radio" name="type" value="1">
            <select name="column"></select>
        </p>
        <button>确定</button>
    </div>
</div>