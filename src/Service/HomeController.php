<?php
namespace Zodream\Module\Gzo\Service;

use Zodream\Disk\Directory;
use Zodream\Html\Bootstrap\Html;
use Zodream\Module\Gzo\Domain\Database\Schema;
use Zodream\Module\Gzo\Domain\GenerateModel;
use Zodream\Service\Factory;

class HomeController extends Controller {

    public $layout = 'main';

    public function indexAction() {
        return $this->show();
    }

    public function modelAction() {
        return $this->show();
    }

    public function migrationAction() {
        return $this->show();
    }

    public function tableAction($schema = null) {
        if (!empty($schema)) {
            $this->renewDB();
        }
        $tables = GenerateModel::schema($schema)->getAllTable();
        return $this->jsonSuccess($tables);
    }

    public function schemaAction() {
        $this->renewDB();
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
        $modules = $this->getModuleList();
        return $this->show(compact('status', 'modules'));
    }

    protected function getModuleList() {
        $data = [];
        Factory::root()->directory('Module')
            ->map(function ($file) use (&$data) {
                if ($file instanceof Directory && $file->hasFile('Module.php')) {
                    $data[] = $file->getName();
                }
            });
        return $data;
    }

    public function sqlAction($query = null, $schema = null, $table = null, $action = null) {
        if ($action == 'optimize' && !empty($table)) {
            $crumbs = [
                '服务器：localhost' => url('./home/sql'),
                '数据库：'.$schema => url('./home/sql', compact('schema')),
                '表：'.$table => url('./home/sql', compact('schema', 'table')),
                '优化'
            ];
            GenerateModel::schema($schema)->table($table)->optimize();
            $data = [['提示' => '优化成功！']];
        } elseif (!empty($query)) {
            $crumbs = [
                '服务器：localhost' => url('./home/sql'),
                '数据库：'.$schema => url('./home/sql', compact('schema')),
                '执行Sql结果'
            ];
            $data = GenerateModel::schema($schema)->getRows($query);
        } elseif (!empty($table)) {
            $crumbs = [
                '服务器：localhost' => url('./home/sql'),
                '数据库：'.$schema => url('./home/sql', compact('schema')),
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
                '服务器：localhost' => url('./home/sql'),
                '数据库：'.$schema
            ];
            $data = GenerateModel::schema($schema)->getAllTable(true);
            $data = array_map(function ($item) {
                $tip = '';
                if ($item['Data_free'] > 0) {
                    $tip = sprintf('(可%s)', Html::a('优化', url(null, ['table' => $item['Name'], 'action' => 'optimize'])));
                }
                $args = [
                    '数据表' =>
                    Html::a($item['Name'], url(null, ['table' => $item['Name']])).$tip
                ];
                unset($item['Name']);
                return $args + $item;
            }, $data);
        } else {
            $crumbs = [
                '服务器：localhost' => url('./home/sql')
            ];
            $data = Schema::getAllDatabase();
            $data = array_map(function ($item) {
                return [
                    '数据库' => Html::a($item['Database'], url(null, ['schema' => $item['Database']]))
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