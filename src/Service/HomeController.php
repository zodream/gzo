<?php
namespace Zodream\Module\Gzo\Service;

use Zodream\Html\Bootstrap\Html;
use Zodream\Module\Gzo\Domain\Database\Schema;
use Zodream\Module\Gzo\Domain\GenerateModel;
use Zodream\Service\Routing\Url;

class HomeController extends Controller {

    public function indexAction() {
        return $this->show();
    }

    public function modelAction() {
        return $this->show();
    }

    public function tableAction() {
        $tables = GenerateModel::schema()->getAllTable();
        return $this->jsonSuccess($tables);
    }

    public function schemaAction() {
        $data = Schema::getAllDatabaseName();
        return $this->jsonSuccess($data);
    }

    public function crudAction() {
        return $this->show();
    }

    public function controllerAction() {
        return $this->show();
    }

    public function moduleAction($status = 0) {
        return $this->show(compact('status'));
    }

    public function sqlAction($query = null, $schema = null, $table = null) {
        if (!empty($query)) {
            $crumbs = [
                '服务器：localhost' => Url::to('gzo/home/sql'),
                '数据库：'.$schema => Url::to('gzo/home/sql', compact('schema')),
                '执行Sql结果'
            ];
            $data = GenerateModel::schema($schema)->getRows($query);
        } elseif (!empty($table)) {
            $crumbs = [
                '服务器：localhost' => Url::to('gzo/home/sql'),
                '数据库：'.$schema => Url::to('gzo/home/sql', compact('schema')),
                '表：'.$table
            ];
            $data = GenerateModel::schema($schema)->table($table)->getAllColumn(true);
            $data = array_map(function ($item) {
                $args = [
                    '列名' => $item['Field']
                ];
                unset($item['Field']);
                return $args + $item;
            }, $data);
        } elseif (!empty($schema)) {
            $crumbs = [
                '服务器：localhost' => Url::to('gzo/home/sql'),
                '数据库：'.$schema
            ];
            $data = GenerateModel::schema($schema)->getAllTable(true);
            $data = array_map(function ($item) {
                $args = [
                    '数据表' =>
                    Html::a($item['Name'], Url::to(null, ['table' => $item['Name']]))
                ];
                unset($item['Name']);
                return $args + $item;
            }, $data);
        } else {
            $crumbs = [
                '服务器：localhost' => Url::to('gzo/home/sql')
            ];
            $data = Schema::getAllDatabase();
            $data = array_map(function ($item) {
                return [
                    '数据库' => Html::a($item['Database'], Url::to(null, ['schema' => $item['Database']]))
                ];
            }, $data);
        }
        return $this->show(compact('query', 'schema', 'table', 'data', 'crumbs'));
    }

    public function exportAction() {
        return $this->show();
    }

    public function importAction() {
        return $this->show();
    }
}