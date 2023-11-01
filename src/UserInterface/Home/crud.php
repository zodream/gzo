<?php
use Zodream\Template\View;
/** @var $this View */

$this->title = '生成CRUD';

$js = <<<JS
bindCurd();
JS;
$this->registerJs($js, View::JQUERY_READY);
?>

<div class="page-tooltip-bar">
    <p class="tooltip-header">操作提示</p>
    <ul>
        <li>生成CRUD 数据库增查改删</li>
        <li>控制器、模型、视图至少选择一个，不然无法生成</li>
    </ul>
    <span class="tooltip-toggle"></span>
</div>

<form class="form-inline" data-type="ajax" action="<?=$this->url('./template')?>" method="get">
    <div class="input-group">
        <label for="module1">模块名</label>
        <input type="text" id="module1" name="module" class="form-control" placeholder="示例：Blog" required>
    </div>
    <div class="input-group">
        <label for="table1">数据表</label>
        <select name="table" id="table1" class="form-control" required>
            <option value="">请选择</option>
        </select>
    </div>
    <div class="input-group">
        <label for="name1">文件名</label>
        <input id="name1" type="text" name="name" class="form-control" placeholder="示例：Home">
    </div>
    <div class="input-group">
        <input id="hasController1" type="checkbox" checked name="hasController" value="1">
        <label for="hasController1">生成控制器</label>
    </div>
    <div class="input-group">
        <input id="hasModel1" type="checkbox" checked name="hasModel" value="1">
        <label for="hasModel1">生成数据模型</label>
    </div>
    <div class="input-group">
        <input id="hasView1" type="checkbox" checked name="hasView" value="1">
        <label for="hasView1">生成视图</label>
    </div>
    <button class="btn btn-primary">生成</button>
</form>