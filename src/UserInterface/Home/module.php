<?php
use Zodream\Template\View;
/** @var $this View */

$this->title = '模块管理';

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

<div class="zd-tab">
    <div class="zd-tab-head">
        <div class="zd-tab-item <?= $status == 0 ? 'active' : ''?>">
            安装模块
        </div>
        <div class="zd-tab-item <?= $status == 1 ? 'active' : ''?>">
            卸载模块
        </div>
        <div class="zd-tab-item <?= $status == 2 ? 'active' : ''?>">
            生成模块
        </div>
    </div>
    <div class="zd-tab-body">
        <div class="zd-tab-item <?= $status == 0 ? 'active' : ''?>">

            <div class="page-tip">
                <p class="blue">操作提示</p>
                <ul>
                    <li>安装模块，模块内部是一个整体，包含控制器、模型、视图</li>
                    <li>安装模块的同时会自动创建必要的数据表、同时可能有相关填充数据</li>
                </ul>
                <span class="toggle"></span>
            </div>

            <form class="form-inline" data-type="ajax" action="<?=$this->url('gzo/module/install')?>" method="get">
                <div class="input-group">
                    <label for="name1">路&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;由</label>
                    <input id="name1" type="text" name="name" placeholder="示例：blog" required size="20">
                </div>
                <div class="input-group">
                    <label for="module1">命名空间</label>
                    <input type="text" id="module1" name="module" placeholder="示例：Module\Blog" required size="100">
                </div>
                <div class="input-group">
                    <input id="installTable1" type="checkbox" checked name="hasTable" value="1">
                    <label for="installTable1">创建数据结构</label>
                </div>
                <div class="input-group">
                    <input id="hasSeed1" type="checkbox" checked name="hasSeed" value="1">
                    <label for="hasSeed1">生成测试数据</label>
                </div>
                <div class="input-group">
                    <input id="hasAssets1" type="checkbox" checked name="hasAssets" value="1">
                    <label for="hasAssets1">复制资源文件</label>
                </div>
                <button class="btn">安装</button>
            </form>
        </div>
        <div class="zd-tab-item <?= $status == 1 ? 'active' : ''?>">
            <div class="page-tip">
                <p class="blue">操作提示</p>
                <ul>
                    <li>卸载模块，同时会删除数据表</li>
                </ul>
                <span class="toggle"></span>
            </div>
            <form class="form-inline" data-type="ajax" action="<?=$this->url('gzo/module/uninstall')?>" method="get">
                <div class="input-group">
                    <label for="name2">路&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;由</label>
                    <input type="text" id="name2" name="name" placeholder="示例：blog" required>
                </div>
                <button class="btn">卸载</button>
            </form>
        </div>
        <div class="zd-tab-item <?= $status == 2 ? 'active' : ''?>">
            <div class="page-tip">
                <p class="blue">操作提示</p>
                <ul>
                    <li>生成模块代码，此功能需要一定的编写代码能力</li>
                </ul>
                <span class="toggle"></span>
            </div>
            <form class="form-inline" data-type="ajax" action="<?=$this->url('gzo/template/module')?>" method="get">
                <div class="input-group">
                    <label for="module2">命名空间</label>
                    <input type="text" id="module2" name="module" placeholder="示例：Module\Blog" size="100" required>
                </div>
                <div class="input-group">
                    <label for="table1">数&nbsp;&nbsp;据&nbsp;&nbsp;表</label>
                    <select name="table" id="table1">
                        <option value="">请选择</option>
                    </select>
                </div>
                <button class="btn">生成</button>
            </form>
        </div>
    </div>
</div>

<?php
$this->extend('layouts/footer');
?>