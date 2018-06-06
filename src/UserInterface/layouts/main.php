<?php
defined('APP_DIR') or exit();
use Zodream\Template\View;
/** @var $this View */
$this->registerCssFile([
    '@font-awesome.min.css',
    '@prism.css',
    '@zodream.css',
    '@zodream-admin.css',
    '@dialog.css',
    '@gzo.css'
])->registerJsFile([
    '@jquery.min.js',
    '@prism.js',
    '@jquery.dialog.min.js',
    '@jquery.upload.min.js',
    '@main.min.js',
    '@gzo.min.js'
]);
?>
<!DOCTYPE html>
<html lang="<?=$this->get('language', 'zh-CN')?>">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="Description" content="<?=$this->description?>" />
    <meta name="keywords" content="<?=$this->keywords?>" />
    <title><?=$this->title?></title>
    <?=$this->header();?>
</head>
<body>
<header>
    <div class="container">
        ZoDream Generator
    </div>
</header>
<div class="container page-box">
    <div class="left-catelog navbar">
        <span class="left-catelog-toggle"></span>
        <ul>
            <li><a href="<?=$this->url('./')?>"><i class="fa fa-home"></i><span>首页</span></a></li>
            <li class="expand"><a href="javascript:;">
                    <i class="fa fa-file-text"></i><span>模板管理</span></a>
                <ul>
                    <li><a href="<?=$this->url('./home/controller')?>">
                            <i class="fa fa-gears"></i><span>生成控制器</span></a></li>
                    <li><a href="<?=$this->url('./home/model')?>">
                            <i class="fa fa-cubes"></i><span>生成数据模型</span></a></li>
                    <li><a href="<?=$this->url('./home/migration')?>">
                            <i class="fa fa-cubes"></i><span>生成生成器</span></a></li>
                    <li><a href="<?=$this->url('./home/crud')?>">
                            <i class="fa fa-circle-o"></i><span>生成CRUD</span></a></li>
                </ul>
            </li>
            <li class="expand"><a href="javascript:;">
                    <i class="fa fa-briefcase"></i><span>模块管理</span></a>
                <ul>
                    <li><a href="<?=$this->url('./home/module')?>"><i class="fa fa-link"></i><span>安装模块</span></a></li>
                    <li><a href="<?=$this->url('./home/module', ['status' => 1])?>"><i class="fa fa-unlink"></i><span>卸载模块</span></a></li>
                    <li><a href="<?=$this->url('./home/module', ['status' => 2])?>"><i class="fa fa-terminal"></i><span>生成模块</span></a></li>
                </ul>
            </li>
            <li class="expand"><a href="javascript:;">
                    <i class="fa fa-database"></i><span>数据库管理</span></a>
                <ul>
                    <li><a href="<?=$this->url('./home/sql')?>"><i class="fa fa-search"></i><span>数据库查询</span></a></li>
                    <li><a href="<?=$this->url('./home/export')?>"><i class="fa fa-cloud-download"></i><span>导出数据</span></a></li>
                    <li><a href="<?=$this->url('./home/import')?>"><i class="fa fa-cloud-upload"></i><span>导入数据</span></a></li>
                </ul>
            </li>
        </ul>
    </div>
    <div class="right-content">
        <?=$content?>
    </div>
</div>
<?=$this->footer()?>
</body>
</html>