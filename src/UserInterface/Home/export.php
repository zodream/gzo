<?php
use Zodream\Template\View;
/** @var $this View */

$this->title = '导出数据';

$js = <<<JS
bindExport();
JS;

$this->registerJs($js, View::JQUERY_READY);
?>

<div class="page-tip">
    <p class="blue">操作提示</p>
    <ul>
        <li>导出数据</li>
    </ul>
    <span class="toggle"></span>
</div>

<form class="form-inline" action="<?=$this->url('./sql/export')?>" target="_blank" method="get">
    <div class="input-group">
        <label for="schema1">数据库</label>
        <select name="schema" id="schema1" required>
            <option value="">请选择</option>
        </select>
    </div>
    <div id="table-box" class="input-group" style="display: none">
        <label for="table1">数据表</label>
        <select name="table[]" id="table1" multiple size="10">
        </select>
    </div>
    <div class="input-group">
        <input id="structure1" type="checkbox" checked name="sql_structure" value="1">
        <label for="structure1">生成结构</label>
    </div>
    <div class="input-group">
        <input id="data1" type="checkbox" checked name="sql_data" value="1">
        <label for="data1">生成数据</label>
    </div>
    <div class="input-group">
        <input id="hasDrop1" type="checkbox" checked name="has_schema" value="1">
        <label for="hasDrop1">生成数据库</label>
    </div>
    <div class="input-group">
        <input id="hasDrop1" type="checkbox" checked name="has_drop" value="1">
        <label for="hasDrop1">添加 DROP </label>
    </div>
    <div class="input-group">
        <label for="schema1">导出格式</label>
        <select name="format" id="format">
            <option value="sql">SQL 文件</option>
            <option value="zip">ZIP 文件</option>
        </select>
    </div>
    <div class="input-group">
        <label for="expire1">有效期（分钟）</label>
        <input id="expire1" type="number" name="expire" placeholder="示例：10" value="10" size="10">
    </div>

    <button class="btn">执行</button>
</form>