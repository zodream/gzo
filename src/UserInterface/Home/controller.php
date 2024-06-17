<?php
defined('APP_DIR') or exit();
use Zodream\Template\View;
/** @var $this View */

$this->title = '生成控制器';
?>

<div class="page-tooltip-bar">
    <p class="tooltip-header">操作提示</p>
    <ul>
        <li>生成控制器</li>
    </ul>
    <span class="tooltip-toggle"></span>
</div>


<form class="form-inline" data-type="ajax" action="<?=$this->url('./template/controller')?>" method="get">

    <div class="input-group">
        <label for="module1">模块名</label>
        <input type="text" id="module1" name="module" class="form-control" placeholder="示例：Home" required >
    </div>
    <div class="input-group">
        <label for="name1">文件名</label>
        <input id="name1" type="text" name="name" value="Home"  class="form-control" placeholder="示例：Home">
    </div>
    
    <div class="btn-group mt-30">
        <button class="btn btn-primary">生成</button>
        <button type="button" data-type="preview" class="btn btn-info">预览</button>
    </div>
</form>