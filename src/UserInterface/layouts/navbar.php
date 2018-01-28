<?php
use Zodream\Template\View;
/** @var $this View */
?>

<ul>
    <li><a href="<?=$this->url('./')?>"><i class="fa fa-home"></i><span>首页</span></a></li>
    <li class="expand"><a href="javascript:;">
            <i class="fa fa-briefcase"></i><span>模板管理</span></a>
        <ul>
            <li><a href="<?=$this->url('./home/controller')?>">
                    <i class="fa fa-list"></i><span>生成控制器</span></a></li>
            <li><a href="<?=$this->url('./home/model')?>">
                    <i class="fa fa-list"></i><span>生成数据模型</span></a></li>
            <li><a href="<?=$this->url('./home/crud')?>">
                    <i class="fa fa-edit"></i><span>生成CRUD</span></a></li>
        </ul>
    </li>
    <li class="expand"><a href="javascript:;">
            <i class="fa fa-briefcase"></i><span>模块管理</span></a>
        <ul>
            <li><a href="<?=$this->url('./home/module')?>"><i class="fa fa-list"></i><span>安装模块</span></a></li>
            <li><a href="<?=$this->url('./home/module', ['status' => 1])?>"><i class="fa fa-list"></i><span>卸载模块</span></a></li>
            <li><a href="<?=$this->url('./home/module', ['status' => 2])?>"><i class="fa fa-list"></i><span>生成模块</span></a></li>
        </ul>
    </li>
    <li class="expand"><a href="javascript:;">
            <i class="fa fa-briefcase"></i><span>数据库管理</span></a>
        <ul>
            <li><a href="<?=$this->url('./home/sql')?>"><i class="fa fa-list"></i><span>数据库查询</span></a></li>
            <li><a href="<?=$this->url('./home/export')?>"><i class="fa fa-list"></i><span>导出数据</span></a></li>
            <li><a href="<?=$this->url('./home/import')?>"><i class="fa fa-list"></i><span>导入数据</span></a></li>
        </ul>
    </li>
</ul>