<?php
use Zodream\Template\View;
/** @var $this View */

$this->title = '生成控制器';
?>

<div class="page-tip">
    <p class="blue">操作提示</p>
    <ul>
        <li>生成控制器</li>
    </ul>
    <span class="toggle"></span>
</div>


<form class="form-inline" data-type="ajax" action="<?=$this->url('./template/controller')?>" method="get">

    <div class="input-group">
        <label for="module1">模块名</label>
        <input type="text" id="module1" name="module" placeholder="示例：Home" required size="100">
    </div>
    <div class="input-group">
        <label for="name1">文件名</label>
        <input id="name1" type="text" name="name" value="Home" placeholder="示例：Home" size="100">
    </div>
    <button class="btn">生成</button>
    <button type="button" data-type="preview" class="btn">预览</button>
</form>