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

<form class="form-inline" action="<?=$this->url('gzo/sql/import')?>" enctype="multipart/form-data" method="post">
    <div class="input-group">
        <label for="schema1">数据库</label>
        <select name="schema" id="schema1" required>
            <option value="">请选择</option>
        </select>
    </div>
    <div class="input-group">
        <label for="name1">sql文件</label>
        <input id="name1" type="file" name="file" required>
    </div>
    <button class="btn">执行</button>
</form>

<?php
$this->extend('layouts/footer');
?>