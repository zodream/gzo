<?php
defined('APP_DIR') or exit();
use Zodream\Template\View;
use Zodream\Html\Bootstrap\TableWidget;
/** @var $this View */

$this->title = '代码转换';
?>

<div class="page-tooltip-bar">
    <p class="tooltip-header">操作提示</p>
    <ul>
        <li>代码转换</li>
    </ul>
    <span class="tooltip-toggle"></span>
</div>


<form class="form-default" data-type="ajax" action="<?=$this->url('./template/exchange')?>" method="post">
    <div class="row form-inline">
        <div class="col-md-6">
            <div class="input-group">
                <label for="source">源语言</label>
                <input type="text" id="source" name="source" class="form-control" value="php" placeholder="源代码语言">
            </div>
        </div>
        <div class="col-md-6">
            <div class="input-group">
                <label for="target">转换语言</label>
                <input type="text" id="target" name="target" class="form-control" value="c#" placeholder="转换代码语言" required>
            </div>
        </div>
    </div>
    <div class="input-group">
        <label for="content">代码</label>
        <textarea name="content" id="content" rows="5" class="form-control" placeholder="代码"></textarea>
    </div>
    <button class="btn" type="button" data-type="preview">预览转换</button>
</form>