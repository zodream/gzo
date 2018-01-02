<?php
namespace Zodream\Module\Gzo\Service;

use Zodream\Disk\File;
use Zodream\Helpers\Str;
use Zodream\Service\Factory;
use ReflectionClass;

class ModuleController extends Controller {

    public function indexAction() {
        return $this->show('index');
    }

    /**
     * php artisan gzo/module/install --name= --module=
     * @param $name
     * @param $module
     * @return \Zodream\Infrastructure\Http\Response
     */
    public function installAction($name, $module) {
        $configs = $this->getConfigs();
        $configs['modules'][$name] = $module;
        $this->invokeModuleMethod($module, ['install', 'seeder']);
        $this->saveConfigs($configs);
        return $this->jsonSuccess();
    }

    public function uninstallAction($name) {
        $configs = $this->getConfigs();
        if (isset($configs['modules'][$name])) {
            $this->invokeModuleMethod($configs['modules'][$name], 'uninstall');
            unset($configs['modules'][$name]);
            $this->saveConfigs($configs);
        }
        return $this->jsonSuccess();
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
        foreach ((array)$methods as $method) {
            if (empty($method) || !method_exists($instance, $method)) {
                continue;
            }
            call_user_func([$instance, $method]);
        }
        $this->moveAssets($module);
    }

    protected function getConfigs() {
        return include Factory::config()->getCurrentFile()->getFullName();
    }

    protected function saveConfigs($configs) {
        Factory::config()->getCurrentFile()->write(Factory::view()
            ->render('Template/config', array(
                'data' => $configs
            )));
    }

    /**
     * 复制资源文件到公共目录
     * @param $module
     */
    protected function moveAssets($module) {
        $func = new ReflectionClass($module);
        $file = new File($func->getFileName());
        $assetDir = $file->getDirectory()->directory('UserInterface/assets');
        if (!$assetDir->exist()) {
            return;
        }
        $assetDir->copy(Factory::public_path()->directory('assets'));
    }

}