<?php
use Zodream\Template\View;
/** @var $this View */

$this->title = '导入数据';

$js = <<<JS
bindImport();
JS;

$this->registerJs($js, View::JQUERY_READY);
?>

<div class="page-tip">
    <p class="blue">操作提示</p>
    <ul>
        <li>导入数据</li>
    </ul>
    <span class="toggle"></span>
</div>

<form class="form-inline" action="<?=$this->url('./sql/import')?>" enctype="multipart/form-data" method="post">
    <div class="input-group">
        <label for="schema1">数据库</label>
        <select name="schema" id="schema1">
            <option value="">请选择</option>
        </select>
    </div>
    <div class="input-group">
        <label for="name1">sql文件</label>
        <input id="name1" type="file" name="file" required>
    </div>
    <button class="btn">执行</button>
</form>
