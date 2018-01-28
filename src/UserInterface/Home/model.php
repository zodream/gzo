<?php
use Zodream\Template\View;
/** @var $this View */

$this->title = '生成数据模型模型';

$url = $this->url('gzo/home/table');
$js = <<<JS
$.getJSON('{$url}', function (data) { 
    if (data.code != 200) {
        return;
    }
    var html = '<option value="">请选择</option>';
    $.each(data.data, function(i, item) {
        html += '<option value="'+item+'">'+item+'</option>';
    });
    $('#table1').html(html);
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

<form class="form-inline" data-type="ajax" action="<?=$this->url('./template/model')?>" method="get">
    <div class="input-group">
        <label for="table1">数据表</label>
        <select name="table" id="table1" required>
            <option value="">请选择</option>
        </select>
    </div>
    <div class="input-group">
        <label for="module1">模块名</label>
        <input type="text" id="module1" name="module" placeholder="示例：Home" size="100">
    </div>
    <button class="btn">生成</button>
</form>

<?php
$this->extend('layouts/footer');
?>