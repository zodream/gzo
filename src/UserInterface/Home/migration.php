<?php
defined('APP_DIR') or exit();
use Zodream\Template\View;
/** @var $this View */

$this->title = '生成数据生成器';

$js = <<<JS
bindCurd();
JS;

$this->registerJs($js, View::JQUERY_READY);
?>

<div class="page-tooltip-bar">
    <p class="tooltip-header">操作提示</p>
    <ul>
        <li>生成数据生成器</li>
    </ul>
    <span class="tooltip-toggle"></span>
</div>

<form class="form-inline" data-type="ajax" action="<?=$this->url('./template/migration')?>" method="get">
    <div class="input-group">
        <label for="table1">数据表</label>
        <select name="table[]" class="height-auto form-control" id="table1" required multiple size="10">
            <option value="">请选择</option>
        </select>
    </div>
    <div class="input-group">
        <label for="module1">模块名</label>
        <input type="text" id="module1" name="module" class="form-control" value="Test" placeholder="示例：Home" required>
    </div>
    <div class="btn-group mt-30">
        <button class="btn btn-primary">生成</button>
        <button type="button" data-type="preview" class="btn btn-info">预览</button>
    </div>
</form>