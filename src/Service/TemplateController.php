<?php
namespace Zodream\Module\Gzo\Service;

use Zodream\Database\DB;
use Zodream\Database\Schema\Schema;
use Zodream\Disk\Directory;
use Zodream\Helpers\Str;
use Zodream\Module\Gzo\Domain\GenerateModel;
use Zodream\Module\Gzo\Domain\Generator\ModuleGenerator;

class TemplateController extends Controller {

    public function indexAction($module,
                                string $table,
                                $name = null,
                                $hasController = true,
                                $hasView = true,
                                $hasModel = true) {
        if (!empty($name)) {
            $name = Str::studly($name);
        }
        $columns = DB::information()->columnList($table, true);
        if ($hasController) {
            $this->controllerAction($module, $name);
        }
        if ($hasModel) {
            $this->createModel(app_path()->addDirectory('Domain')
                ->addDirectory('Model')->addDirectory($module),
                $table, $module, $name, $columns, true);
        }
        if ($hasView) {
            $this->createView(app_path()
                ->addDirectory('UserInterface')
                ->addDirectory($module), $name, $columns);
        }
        return $this->renderData(true);
    }

    public function confAction($name, $data) {
        ModuleGenerator::renderConfigs($name, $data);
        return $this->renderData(true);
    }

    public function modelAction($table, $module = null, $preview = null) {
        if (!empty($preview)) {
            return $this->renderData([
                'code' => $this->createModel(null, $table, $module)
            ]);
        }
        $root = app_path()->addDirectory('Domain')
            ->addDirectory('Model')->addDirectory($module);
        $this->createModel($root, $table, $module);
        return $this->renderData(true);
    }

    public function migrationAction($table, $module, $preview = null) {
        if (!empty($preview)) {
            return $this->renderData([
                'code' => $this->createMigration(null, $table, $module)
            ]);
        }
        $root = app_path()->addDirectory('Module')
            ->addDirectory($module)->addDirectory('Domain')
            ->addDirectory('Migrations');
        $this->createMigration($root, $table, $module);
        return $this->renderData(true);
    }

    public function controllerAction($module, $name = 'Home', $preview = null) {
        if (!empty($preview)) {
            return $this->renderData([
                'code' => $this->createController(null, $name, $module)
            ]);
        }
        $root = app_path()->addDirectory('Service')
            ->addDirectory($module);
        if (!$root->hasFile('Controller.php')) {
            $root->addFile('Controller.php', $this->baseController($module));
        }
        $name = Str::lastReplace($name, config('app.controller'));
        $this->createController($root, $name, $module);
        return $this->renderData(true);
    }

    public function moduleAction($module, $table = null) {
        if (str_starts_with($module, 'Module\\')) {
            $module = substr($module, 7);
        }
        $root = app_path()->addDirectory('Module')
            ->addDirectory($module);
        $domainRoot = $root->addDirectory('Domain');
        $module = str_replace('/', '\\', $module);
        $moduleConfigs = [
            'module' => $module
        ];
        if (!empty($table)) {
            $moduleConfigs['migration'] = true;
            $this->createMigration($domainRoot->addDirectory('Migrations'),
                $table, $module);
        }
        $root->addFile('Module.php', ModuleGenerator::renderTemplate('Module', $moduleConfigs));
        $modelRoot = $domainRoot->addDirectory('Model');
        $controllerRoot = $root->addDirectory('Service');
        $viewRoot = $root->addDirectory('UserInterface');

        $this->createController($controllerRoot, 'Home', $module, true);
        $this->createView($viewRoot, 'Home', []);

        foreach ((array)$table as $item) {
            $columns = DB::information()->columnList($item, true);
            $name = Str::studly($item);
            $this->createController($controllerRoot, $name, $module, true);
            $this->createModel($modelRoot, $item, $module, $name, $columns, true);
            $this->createView($viewRoot, $name, $columns);
        }
        return $this->renderData(true);
    }

    protected function createController($root, $name, $module, $is_module = false) {
        $template = $this->makeController($name, $module, $is_module);
        if (!$root instanceof Directory) {
            return $template;
        }
        $root->addFile($name.config('app.controller').'.php', $template);
    }

    protected function createModel($root,
                                   string $table,
                                   $module,
                                   $name = null,
                                   array $columns = [],
                                   $is_module = false) {
        if (empty($columns)) {
            $columns = DB::information()->columnList($table, true);
        }
        if (empty($name)) {
            $name = Str::studly($table);
        }
        $template = $this->makeModel($name, $table, $columns, $module, $is_module);
        if (!$root instanceof Directory) {
            return $template;
        }
        $root->addFile($name.config('app.model').'.php', $template);
    }

    protected function createMigration($root,
                                       $table,
                                       $module) {
        $template = $this->makeMigration($table, $module);
        if (!$root instanceof Directory) {
            return $template;
        }
        $root->addFile(sprintf('Create%sTables.php', $module), $template);
    }

    protected function createView(Directory $root, $name, array $columns) {
        if (!$root->hasDirectory('layout')) {
            $root->addDirectory('layouts')
                ->addFile('main.php', ModuleGenerator::renderTemplate('layout'));
        }
        $root = $root->addDirectory($name);
        $root->addFile('index.php', $this->viewIndex($name, $columns));
        if (empty($columns)) {
            return;
        }
        $root->addFile('add.php', $this->viewEdit($name, $columns));
        $root->addFile('detail.php', $this->viewDetail($name, $columns));
    }

    /**
     * 生成基控制器
     * @param $module
     * @return string
     * @throws \Exception
     */
    protected function baseController($module) {
        return ModuleGenerator::renderTemplate('BaseController', array(
            'module' => $module
        ));
    }

    /**
     * 生成控制器
     * @param string $name
     * @param string $module
     * @param bool $is_module
     * @return bool
     * @throws \Exception
     */
    protected function makeController($name, $module, $is_module = false) {
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
     * @return bool
     * @throws \Exception
     */
    protected function makeModel($name, $table, array $columns, $module, $is_module = false) {
        $data = GenerateModel::getFill($columns);
        $foreignKeys = (new Schema())->table($table)->getForeignKeys();
        foreach ($foreignKeys as &$item) {
            $item['table'] = Str::firstReplace('zd_', '', $item['REFERENCED_TABLE_NAME']);
            $item['column'] = $item['COLUMN_NAME'];
            $item['key'] = $item['REFERENCED_COLUMN_NAME'];
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

    protected function makeMigration($tables, $module) {
        $data = [];
        foreach ((array)$tables as $table) {
            $model = GenerateModel::schema()->table($table);
            $columns = $model->getAllColumn(true);
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
    protected function viewIndex($name, array $columns) {
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
     * @return bool
     * @throws \Exception
     */
    protected function viewEdit($name, array $columns) {
        $data = [];
        foreach ($columns as $value) {
            $data[] = $this->_viewForm($value);
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
     * @return bool
     * @throws \Exception
     */
    protected function viewDetail($name, array $columns) {
        $data = [];
        foreach ($columns as $key => $value) {
            $data[] = $value['Field'];
        }
        return ModuleGenerator::renderTemplate('view', array(
            'data'   => $data,
            'name'   => $name
        ));
    }

    /**
     * 视图中表单的生成
     * @param $value
     * @return string
     */
    private function _viewForm($value) {
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

}