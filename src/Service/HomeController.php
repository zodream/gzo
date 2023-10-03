<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Service;

use Zodream\Database\DB;
use Zodream\Disk\File;
use Zodream\Helpers\Str;
use Zodream\Html\Bootstrap\Html;
use Zodream\Module\Gzo\Domain\GenerateModel;
use Zodream\Module\Gzo\Domain\Repositories\ModuleRepository;

class HomeController extends Controller {

    protected string|File $layout = 'main';

    public function indexAction() {
        return $this->show();
    }

    public function modelAction() {
        return $this->show();
    }

    public function migrationAction() {
        return $this->show();
    }

    public function crudAction() {
        return $this->show();
    }

    public function controllerAction() {
        return $this->show();
    }

    public function moduleAction($status = 0) {
        $modules = ModuleRepository::moduleList();
        return $this->show(compact('status', 'modules'));
    }

    public function sqlAction($query = null, $schema = null, $table = null, $action = null, $type = null) {
        if (!empty($action) && !empty($table)) {
            list($crumbs, $data) = $this->getTableAction($schema, $table, $action);
        } elseif (!empty($query)) {
            $crumbs = [
                '服务器：localhost' => url('./home/sql'),
                '数据库：'.$schema => url('./home/sql', compact('schema')),
                '执行Sql结果'
            ];
            $data = GenerateModel::schema($schema)->getRows($query);
        } elseif (!empty($table)) {
            list($crumbs, $data) = $this->getColumnTable($schema, $table, $type);
        } elseif (!empty($schema)) {
            list($crumbs, $data) = $this->getTableTable($schema, $type);
        } else {
            list($crumbs, $data) = $this->getSchemaTable();
        }
        return $this->show(compact('query', 'schema', 'table', 'data', 'crumbs'));
    }

    public function exportAction() {
        return $this->show();
    }

    public function importAction() {
        return $this->show();
    }

    public function copyAction() {
        return $this->show();
    }

    private function getTableAction($schema, $table, $action) {
        $crumbs = [
            '服务器：localhost' => url('./home/sql'),
            '数据库：'.$schema => url('./home/sql', compact('schema')),
            '表：'.$table => url('./home/sql', compact('schema', 'table')),
        ];
        if ($action === 'optimize') {
            $crumbs[] = '优化';
            DB::db()->execute(DB::schemaGrammar()
                ->compileTableOptimize(GenerateModel::schema($schema)->table($table)));
            $data = [['提示' => '优化成功！']];
            return [$crumbs, $data];
        }
        if ($action === 'truncate') {
            $crumbs[] = '清空数据';
            DB::db()->execute(DB::schemaGrammar()
                ->compileTableTruncate(GenerateModel::schema($schema)->table($table)));
            $data = [['提示' => '清空数据并初始化表！']];
            return [$crumbs, $data];
        }
        if ($action === 'clear') {
            $crumbs[] = '删除表数据';
            DB::table(GenerateModel::schema($schema)->table($table))
                ->delete();
            $data = [['提示' => '已删除表的所有数据！']];
            return [$crumbs, $data];
        }
        $crumbs[] = '未知操作';
        $data = [['提示' => '未知操作！']];
        return [$crumbs, $data];
    }

    private function getColumnTable($schema, $table, $type = null) {
        $crumbs = [
            '服务器：localhost' => url('./home/sql'),
            '数据库：'.$schema => url('./home/sql', compact('schema')),
            '表：'.$table
        ];
        if ($type === 'status') {
            $data = DB::information()->columnList(GenerateModel::schema($schema)->table($table), true);
            $data = array_map(function ($item) {
                $args = [
                    '列名' => $item['Field']
                ];
                unset($item['Field']);
                return $args + $item;
            }, $data);
            return [$crumbs, $data];
        }
        $page = DB::table(GenerateModel::schema($schema)->table($table))->page();
        if ($page->isEmpty()) {
            $data = DB::information()->columnList(
                GenerateModel::schema($schema)->table($table));
            $items = [];
            foreach ($data as $item) {
                $items[$item['Field']] = '';
            }
            return [$crumbs, [$items]];
        }
        $page->map(function ($item) {
            foreach ($item as $k => $value) {
                $item[$k] = Str::substr(htmlspecialchars($value), 0, 200, true);
            }
            return $item;
        });
        return [$crumbs, $page];
    }

    private function getTableTable($schema, $type = null) {
        $crumbs = [
            '服务器：localhost' => url('./home/sql'),
            '数据库：'.$schema
        ];
        if ($type === 'status') {
            $data = DB::information()->tableList($schema, true);
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
            return [$crumbs, $data];
        }
        $data = DB::information()->tableList($schema);
        $data = array_map(function ($table) {
            return [
                '数据表' =>
                    Html::a($table, url(null, ['table' => $table])),
                '操作' => implode('&nbsp;&nbsp;', [
                    Html::a('查看', url(null, ['table' => $table])),
                    Html::a('结构', url(null, ['table' => $table, 'type' => 'status'])),
                    Html::a('清空', url(null, ['table' => $table, 'action' => 'truncate'])),
                    Html::a('删除数据', url(null, ['table' => $table, 'action' => 'clear']), ['title' => '清空并保留自增值']),
                ])
            ];
        }, $data);
        return [$crumbs, $data];
    }

    private function getSchemaTable() {
        $crumbs = [
            '服务器：localhost' => url('./home/sql')
        ];
        $data = DB::information()->schemaList();
        $data = array_map(function ($item) {
            return [
                '数据库' => Html::a($item, url(null, ['schema' => $item])),
                '操作' => implode('', [
                    Html::a('查看', url(null, ['schema' => $item])),
                    Html::a('表状态', url(null, ['schema' => $item, 'type' => 'status'])),
                ])
            ];
        }, $data);
        return [$crumbs, $data];
    }

}