<?php
use Zodream\Template\View;
/** @var $this View */

$this->title = '生成数据模型模型';

$url = $this->url('gzo/home/schema');
$js = <<<JS
$.getJSON('{$url}', function (data) { 
    if (data.code != 200) {
        return;
    }
    var html = '<option value="">请选择</option>';
    $.each(data.data, function(i, item) {
        html += '<option value="'+item+'">'+item+'</option>';
    });
    $('#schema1').html(html);
});
JS;


$this->extend('layouts/header')
    ->registerJs($js, View::JQUERY_READY);
?>

<div class="page-tip">
    <p class="blue">操作提示</p>
    <ul>
        <li>生成数据模型模型</li>
    </ul>
    <span class="toggle"></span>
</div>

<form class="form-inline" action="<?=$this->url('gzo/sql/export')?>" target="_blank" method="get">
    <div class="input-group">
        <label for="schema1">数据库</label>
        <select name="schema" id="schema1" required>
            <option value="">请选择</option>
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
        <label for="expire1">有效期（分钟）</label>
        <input id="expire1" type="number" name="expire" placeholder="示例：10" value="10" size="10">
    </div>
    <button class="btn">执行</button>
</form>

<?php
$this->extend('layouts/footer');
?>