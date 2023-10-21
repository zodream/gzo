<?php
use Zodream\Template\View;
/** @var $this View */

$this->title = '生成数据生成器';

$js = <<<JS
bindCurd();
JS;

$this->registerJs($js, View::JQUERY_READY);
?>

<div class="page-tip">
    <p class="blue">操作提示</p>
    <ul>
        <li>生成数据生成器</li>
    </ul>
    <span class="toggle"></span>
</div>

<form class="form-inline" data-type="ajax" action="<?=$this->url('./template/migration')?>" method="get">
    <div class="input-group">
        <label for="table1">数据表</label>
        <select name="table[]" class="height-auto" id="table1" required multiple size="10">
            <option value="">请选择</option>
        </select>
    </div>
    <div class="input-group">
        <label for="module1">模块名</label>
        <input type="text" id="module1" name="module" value="Test" placeholder="示例：Home" required>
    </div>
    <div class="btn-group mt-30">
        <button class="btn btn-primary">生成</button>
        <button type="button" data-type="preview" class="btn btn-info">预览</button>
    </div>
</form>