<?php
use Zodream\Template\View;
/** @var $this View */

$this->title = '生成CRUD';

$js = <<<JS
bindCopy();
JS;

$this->registerJs($js, View::JQUERY_READY);
?>

<div class="page-tooltip-bar">
    <p class="tooltip-header">操作提示</p>
    <ul>
        <li>进行数据表复制</li>
        <li>支持多个表同时复制到一个表</li>
    </ul>
    <span class="tooltip-toggle"></span>
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

    <div class="btn-group mt-30">
        <button class="btn btn-primary">复制</button>
        <button class="btn btn-info" data-action="preview" type="button">预览</button>
        <a class="btn btn-danger" data-type="reset">清空</a>
    </div>
</form>

<div class="dialog-select">
    <div class="dialog-body">
        <p><select name="schame" class="form-control"></select>
        <select name="table" class="form-control"></select></p>
        <button>确定</button>
    </div>
</div>

<div class="dialog-column-select">
    <div class="dialog-body">
        <p>
            <input type="radio" name="type" value="0" checked>
            <input type="text" name="value" class="form-control" placeholder="请输入内容">
        </p>
        <p>
            <input type="radio" name="type" value="1">
            <select name="column" class="form-control"></select>
        </p>
        <button>确定</button>
    </div>
</div>