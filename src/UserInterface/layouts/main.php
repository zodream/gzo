<?php
defined('APP_DIR') or exit();
use Zodream\Template\View;
use Zodream\Html\Dark\Layout;
/** @var $this View */
$this->registerCssFile([
    '@font-awesome.min.css',
    '@jquery.autocompleter.css',
    '@prism.css',
    '@zodream.css',
    '@zodream-admin.css',
    '@dialog.css',
    '@gzo.css'
])->registerJsFile([
    '@js.cookie.min.js',
    '@jquery.min.js',
    '@jquery.pjax.min.js',
    '@prism.js',
    '@jquery.autocompleter.min.js',
    '@jquery.dialog.min.js',
    '@jquery.upload.min.js',
    '@main.min.js',
    '@admin.min.js',
    '@gzo.min.js'
])->registerJs(sprintf('var BASE_URI = "%s";', $this->url('./', false)), View::HTML_HEAD);
?>

<?= Layout::mainIfPjax($this, [
    [
        '首页',
        './',
        'fa fa-home',
    ],
    [
        '模板管理',
        false,
        'fa fa-file-alt',
        [
            [
                '生成控制器',
                './home/controller',
                'fa fa-cogs'
            ],
            [
                '生成数据模型',
                './home/model',
                'fa fa-cubes'
            ],
            [
                '生成生成器',
                './home/migration',
                'fa fa-cubes'
            ],
            [
                '生成CRUD',
                './home/crud',
                'fa fa-drum'
            ]
        ],
        true
    ],
    [
        '模块管理',
        false,
        'fa fa-briefcase',
        [
            [
                '安装模块',
                './home/module',
                'fa fa-link'
            ],
            [
                '卸载模块',
                ['./home/module', 'status' => 1],
                'fa fa-unlink'
            ],
            [
                '生成模块',
                ['./home/module', 'status' => 2],
                'fa fa-terminal'
            ]
        ],
        true
    ],
    [
        '数据库管理',
        false,
        'fa fa-database',
        [
            [
                '数据库查询',
                './home/sql',
                'fa fa-search'
            ],
            [
                '导出数据',
                './home/export',
                'fa fa-cloud-download-alt'
            ],
            [
                '导入数据',
                './home/import',
                'fa fa-cloud-upload-alt'
            ],
            [
                '数据复制',
                './home/copy',
                'fa fa-copy'
            ],
        ]
    ]
], $this->contents(), 'ZoDream Generator') ?>
