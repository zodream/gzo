<?php
use Zodream\Template\View;
/** @var $this View */

$this->title = '生成数据模型模型';

$js = <<<JS
bindCurd();
JS;

$this->registerJs($js, View::JQUERY_READY);
?>

<div class="page-tip">
    <p class="blue">操作提示</p>
    <ul>
        <li>生成数据模型模型</li>
    </ul>
    <span class="toggle"></span>
</div>

<form class="form-inline" data-type="ajax" action="<?=$this->url('./template/model')?>" method="get">
    <div class="input-group">
        <label for="table1">数据表</label>
        <select name="table" id="table1" required>
            <option value="">请选择</option>
        </select>
    </div>
    <div class="input-group">
        <label for="module1">模块名</label>
        <input type="text" id="module1" name="module" placeholder="示例：Home">
    </div>
    <div class="btn-group mt-30">
        <button class="btn btn-primary">生成</button>
        <button type="button" data-type="preview" class="btn btn-info">预览</button>
    </div>
</form>