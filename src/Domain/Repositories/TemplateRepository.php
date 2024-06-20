<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Domain\Repositories;

use Zodream\Database\DB;
use Zodream\Database\Schema\Schema;
use Zodream\Disk\Directory;
use Zodream\Helpers\Str;
use Zodream\Module\Gzo\Domain\GenerateModel;
use Zodream\Module\Gzo\Domain\Generator\ModuleGenerator;
use Zodream\Module\Gzo\Domain\Readers\FileWriter;
use Zodream\Module\Gzo\Domain\Readers\MemoryWriter;
use Zodream\Module\Gzo\Domain\Readers\IFileWriter;

class TemplateRepository {

    public static function crud(string $module,
                                string $table,
                                string $name = '',
                                bool $hasController = true,
                                bool $hasView = true,
                                bool $hasModel = true) {
        if (!empty($name)) {
            $name = Str::studly($name);
        }
        $columns = DB::information()->columnList($table, true);
        if ($hasController) {
            static::controller($module, $name);
        }
        if ($hasModel) {
            static::createModel(app_path()->childDirectory('Domain/Model/'.$module),
                $table, $module, $name, $columns, true);
        }
        if ($hasView) {
            static::createView(app_path()
                ->addDirectory('UserInterface')
                ->addDirectory($module), $name, $columns);
        }
    }

    public static function model(string $table, string $module = '') {
        $root = app_path()->childDirectory('Domain/Model/'.$module);
        static::createModel($root, $table, $module);
    }

    public static function migration(array|string $table, string $module) {
        $root = app_path()->childDirectory('Module/'.$module.'/Domain/Migrations');
        static::createMigration($root, $table, $module);
    }

    public static function controller(string $module, string $name = 'Home') {
        $root = app_path()->childDirectory('Service/'.$module);
        if (!$root->hasFile('Controller.php')) {
            static::output()->write($root->childFile('Controller.php'), static::baseController($module));
        }
        $name = Str::lastReplace($name, config('app.controller'));
        static::createController($root, $name, $module);
    }

    public static function module(string $module, array|string $table = []) {
        if (str_starts_with($module, 'Module\\')) {
            $module = substr($module, 7);
        }
        $root = app_path()->childDirectory('Module/'.$module);
        $domainRoot = $root->childDirectory('Domain');
        $module = str_replace('/', '\\', $module);
        $moduleConfigs = [
            'module' => $module
        ];
        if (!empty($table)) {
            $moduleConfigs['migration'] = true;
            static::createMigration($domainRoot->childDirectory('Migrations'),
                $table, $module);
        }

        static::output()->write($root->childFile('Module.php'), ModuleGenerator::renderTemplate('Module', $moduleConfigs));
        $modelRoot = static::output()->mkdir($domainRoot->childDirectory('Model'));
        $controllerRoot = static::output()->mkdir($root->childDirectory('Service'));
        $viewRoot = static::output()->mkdir($root->childDirectory('UserInterface'));

        static::createController($controllerRoot, 'Home', $module, true);
        static::createView($viewRoot, 'Home', []);

        foreach ((array)$table as $item) {
            $columns = DB::information()->columnList($item, true);
            $name = Str::studly($item);
            static::createController($controllerRoot, $name, $module, true);
            static::createModel($modelRoot, $item, $module, $name, $columns, true);
            static::createView($viewRoot, $name, $columns);
        }
    }

    protected static function createModel(Directory $root,
                                   string $table,
                                   string $module,
                                   string $name = '',
                                   array $columns = [],
                                   bool $is_module = false) {
        if (empty($columns)) {
            $columns = DB::information()->columnList($table, true);
        }
        if (empty($name)) {
            $name = Str::studly($table);
        }
        $template = static::makeModel($name, $table, $columns, $module, $is_module);
        static::output()->write($root->childFile($name.config('app.model').'.php'), $template);
    }

    protected static function createController(Directory $root, string $name, string $module, bool $is_module = false) {
        $template = static::makeController($name, $module, $is_module);
        static::output()->write($root->childFile($name.config('app.controller').'.php'), $template);
    }

    protected static function createMigration(Directory $root,
                                       string|array $table,
                                       string $module) {
        $template = static::makeMigration($table, $module);
        static::output()->write($root->childFile(sprintf('Create%sTables.php', $module)), $template);
    }

    protected static function createView(Directory $root, string $name, array $columns) {
        if (!$root->hasDirectory('layout')) {
            static::output()->write($root->childFile('layouts/main.php'),
                ModuleGenerator::renderTemplate('layout'));
        }
        $root = static::output()->mkdir($root->childDirectory($name));
        static::output()->write($root->childFile('index.php'),
            static::viewIndex($name, $columns));
        if (empty($columns)) {
            return;
        }
        static::output()->write($root->childFile('add.php'),
            static::viewEdit($name, $columns));
        static::output()->write($root->childFile('detail.php'), static::viewDetail($name, $columns));
    }


    /**
     * 生成基控制器
     * @param $module
     * @return string
     * @throws \Exception
     */
    protected static function baseController(string $module) {
        return ModuleGenerator::renderTemplate('BaseController', array(
            'module' => $module
        ));
    }

    /**
     * 生成控制器
     * @param string $name
     * @param string $module
     * @param bool $is_module
     * @return string
     * @throws \Exception
     */
    protected static function makeController(string $name, string $module, bool $is_module = false) {
        return ModuleGenerator::renderTemplate('Controller', [
            'module' => $module,
            'name' => $name,
            'is_module' => $is_module
        ]);
    }

    /**
     * 生成数据模型
     * @param string $name
     * @param string $table
     * @param array $columns
     * @param $module
     * @param bool $is_module
     * @return string
     * @throws \Exception
     */
    protected static function makeModel(string $name, string $table, array $columns, string $module, bool $is_module = false) {
        $data = GenerateModel::getFill($columns);
        $foreignKeys = DB::information()->foreignKeys(
            (new Schema(DB::engine()->config('database')))
                ->table($table)
        );
        $prefix = DB::engine()->config('prefix');
        foreach ($foreignKeys as &$item) {
            if (!empty($prefix)) {
                $item['link_table'] = Str::firstReplace($prefix, '', $item['link_table']);
            }
        }
        return ModuleGenerator::renderTemplate('Model', [
            'name' => $name,
            'table' => $table,
            'rules' => $data[1],
            'pk' => $data[0],
            'labels' => $data[2],
            'property' => $data[3],
            'module' => $module,
            'foreignKeys' => $foreignKeys,
            'is_module' => $is_module
        ]);
    }

    protected static function makeMigration(array|string $tables, string $module) {
        $data = [];
        foreach ((array)$tables as $table) {
            $model = GenerateModel::schema()->table($table);
            $columns = DB::information()->columnList($model, true);;
            $fields = GenerateModel::getFields($columns);
            $data[] = [
                'name' => Str::studly($table),
                'table' => $table,
                'fields' => $fields,
                'status' => $model->getStatus()
            ];
        }

        return ModuleGenerator::renderTemplate('Migration', [
            'data' => $data,
            'module' => $module,
        ]);
    }



    /**
     * 生成主视图列表
     * @param string $name
     * @param array $columns
     * @return string
     * @throws \Exception
     */
    protected static function viewIndex(string $name, array $columns) {
        $data = [];
        foreach ($columns as $value) {
            $data[$value['Field']] = $value['Field'];
        }
        return ModuleGenerator::renderTemplate('index', array(
            'data'   => $data,
            'name'   => $name
        ));
    }

    /**
     * 生成编辑视图
     * @param string $name
     * @param array $columns
     * @return string
     * @throws \Exception
     */
    protected static function viewEdit(string $name, array $columns) {
        $data = [];
        foreach ($columns as $value) {
            $data[] = static::viewForm($value);
        }
        return ModuleGenerator::renderTemplate('add', array(
            'data'   => $data,
            'name'   => $name
        ));
    }

    /**
     * 生成单页查看视图
     * @param string $name
     * @param array $columns
     * @return string
     * @throws \Exception
     */
    protected static function viewDetail(string $name, array $columns) {
        $data = [];
        foreach ($columns as $value) {
            $data[] = $value['Field'];
        }
        return ModuleGenerator::renderTemplate('view', array(
            'data'   => $data,
            'name'   => $name
        ));
    }

    /**
     * 视图中表单的生成
     * @param array $value
     * @return string
     */
    private static function viewForm(array $value) {
        $required = null;
        if ($value['Null'] === 'NO') {
            $required = ", 'required' => true";
        }
        switch (explode('(', $value['Type'])[0]) {
            case 'enum':
                $str = rtrim(substr($value['Type'], strpos($value['Type'], '(')), ')');
                return "select('{$value['Field']}', [{$str}])";
            case 'text':
                return "textArea('{$value['Field']}', ['label' => '{$value['Field']}'{$required}])";
            case 'int':
            case 'varchar':
            case 'char':
            default:
                return "text('{$value['Field']}', ['label' => '{$value['Field']}'{$required}])";
        }
    }

    /**
     * @return IFileWriter|FileWriter|MemoryWriter
     * @throws \Exception
     */
    public static function output() {
        return app(IFileWriter::class);
    }
}