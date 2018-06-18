# gzo
Generate Web Page

## 根据 html 静态页面文件生成架构

```PHP
php artisan gzo/module --input=
``` 

    name 模块名，默认文件夹名
    
    input 静态资源所在文件夹
    
    output 输出文件夹，默认输入文件夹
    
    configs 对应关系json, 默认输入文件夹下 module.json
    
配置

```JSON

{
    "name": "",
    "input": "F:\\Desktop\\www",
    "output": "",
    "tables": {
        "file": [
            "id",
            "name"
        ],
        "pAth": {
            "id": ""
        }
    },
    "controllers": {
        "home": [
            "index",
            "edit"
        ],
        "other": {
            "index": "return $this->show();"
        }
    },
    "controllers": "@views",   //根据页面生成
    "views": {
        "home": {
            "index": "index.html",
            "other": "im.html"
        }
    },
    "views": "@controllers",   // 根据控制器生成空的页面
    "views": "@input",         // 根据文件夹
    "views": "./",
    "assets": {                // 复制资源文件夹或文件
        "sass": "css",
        "ts": "js"
    }
}

```

## 生成 phpunit 测试文件

    gzo/test/project 源文件夹 目标文件夹

```PHP
php artisan gzo/test/project E:\Git\http\src E:\Git\http\tests
```
