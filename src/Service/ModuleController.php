<?php
namespace Zodream\Module\Gzo\Service;

use Zodream\Disk\Directory;
use Zodream\Disk\File;
use Zodream\Disk\FileException;
use Zodream\Helpers\Arr;
use Zodream\Helpers\Json;
use Zodream\Helpers\Str;
use Zodream\Module\Gzo\Domain\Generator\ModuleGenerator;
use ReflectionClass;

class ModuleController extends Controller {

    /**
     *
     * @param null $name
     * @param null $input
     * @param null $output
     * @param string $configs
     * @return void
     */
    public function indexAction($name = null, $input = null, $output = null, $configs = 'module.json') {
        $generator = new ModuleGenerator();
        if (!empty($input)) {
            $input = app_path()->directory($input);
            $configs = $input->file($configs);
        } else {
            $configs =  app_path()->file($configs);
        }
        $configs = Json::decode($configs->read());
        if (!empty($name)) {
            $configs['name'] = $name;
        }
        if (!empty($input)) {
            $configs['input'] = $input;
        }
        if (!empty($output)) {
            $configs['output'] = $output;
        }
        $generator->setConfigs($configs);
        $generator->create();
    }

    /**
     * php artisan gzo/module/install --name= --module=
     * @param string $name
     * @param string $module
     * @param bool $hasTable
     * @param bool $hasSeed
     * @param bool $hasAssets
     * @param bool $isGlobal
     * @throws \Exception
     * @throws \Zodream\Disk\FileException
     */
    public function installAction(
        string $name, string $module,
        bool $hasTable = false, bool $hasSeed = false,
        bool $hasAssets = false, bool $isGlobal = false) {
        $methods = [];
        if ($hasTable) {
            $methods[] = 'install';
        }
        if ($hasSeed) {
            $methods[] = 'seeder';
        }
        $module = trim($module);
        $this->invokeModuleMethod($module, $methods);
        $this->saveModuleConfigs([
            'modules' => [
                $name => $module
            ]
        ], $isGlobal);
        if ($hasAssets) {
            $this->moveAssets($module);
        }
        return $this->renderData('');
    }

    public function uninstallAction($name) {
        $configs = config('route');
        $file = config()->configPath('route');
        if (isset($configs['modules'][$name])) {
            $this->invokeModuleMethod($configs['modules'][$name], 'uninstall');
            unset($configs['modules'][$name]);
            $this->saveConfig($file, $configs);
        }
        return $this->renderData('');
    }

    protected function getModule($module) {
        if (Str::endWith($module, 'Module')) {
            return $module;
        }
        return $module.'\\Module';
    }

    protected function invokeModuleMethod($module, $methods) {
        $module = $this->getModule($module);
        if (!class_exists($module)) {
            return;
        }
        $instance = new $module;
        $instance->boot();
        foreach ((array)$methods as $method) {
            if (empty($method) || !method_exists($instance, $method)) {
                continue;
            }
            call_user_func([$instance, $method]);
        }
    }

    /**
     * @param $configs
     * @param bool $isGlobal
     * @throws \Exception
     * @throws FileException
     */
    protected function saveModuleConfigs($configs, $isGlobal = true) {
        $file = config()->configPath('route');
        $data = config('route');
        $data = Arr::merge2D($data, $configs);
        $this->saveConfig($file, $data);
    }

    protected function removeConfigs(File $file, $configs) {
        $data = config('route');
        $data = Arr::unset2D($data, $configs);
        $this->saveConfig($file, $data);
    }

    /**
     * 复制资源文件到公共目录
     * @param $module
     */
    protected function moveAssets($module) {
        $module = $this->getModule($module);
        if (!class_exists($module)) {
            return;
        }
        $func = new ReflectionClass($module);
        $file = new File($func->getFileName());
        $assetDir = $file->getDirectory()->directory('UserInterface/assets');
        if (!$assetDir->exist()) {
            return;
        }
        $assetDir->copy(public_path()->directory('assets'));
    }

    /**
     * @param $file
     * @param $configs
     * @throws FileException
     * @throws \Exception
     */
    protected function saveConfig(File $file, array $configs) {
        $content = view()
            ->render('Template/config', array(
                'data' => $configs
            ));
        $file->write($content);
    }


    public function generateAction($path = null) {
        if (empty($path)) {
            $path = request()->request('arguments.1');
        }
        return $this->forward(TemplateController::class, 'module', [
            'module' => $path
        ]);
    }

    public function allAction() {
        $data = $this->getModuleList();
        $data = array_map(function ($item) {
            return [
                'value' => 'Module\\'.$item,
                'label' => $item,
            ];
        }, $data);
        return $this->renderData($data);
    }

    public static function installModule(array $modules, array $methods) {
        $instance = new static();
        foreach ($modules as $module) {
            $instance->invokeModuleMethod($module, $methods);
        }
        return true;
    }

    public static function getModuleList() {
        $data = [];
        self::getModulePath(app_path()->directory('Module'), $data);
        return $data;
    }

    private static function getModulePath(Directory $folder, &$data, $prefix = null) {
        $folder->map(function ($file) use (&$data, $prefix) {
            if (!$file instanceof Directory) {
                return;
            }
            if ($file->hasFile('Module.php')) {
                $data[] = $prefix. $file->getName();
                return;
            }
            self::getModulePath($file, $data, $prefix . $file->getName() .'\\');
        });
    }
}