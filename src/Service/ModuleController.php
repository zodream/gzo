<?php
namespace Zodream\Module\Gzo\Service;

use Zodream\Disk\File;
use Zodream\Helpers\Str;
use Zodream\Infrastructure\Http\Response;
use Zodream\Service\Factory;
use ReflectionClass;

class ModuleController extends Controller {

    /**
     * @return Response
     * @throws \Exception
     */
    public function indexAction() {
        return $this->show('index');
    }

    /**
     * php artisan gzo/module/install --name= --module=
     * @param string $name
     * @param string $module
     * @param bool $hasTable
     * @param bool $hasSeed
     * @param bool $hasAssets
     * @return Response
     * @throws \Exception
     * @throws \Zodream\Disk\FileException
     */
    public function installAction($name, $module, $hasTable = true, $hasSeed = true, $hasAssets = true) {
        $configs = $this->getConfigs();
        $configs['modules'][$name] = $module;
        $this->invokeModuleMethod($module, $hasTable, $hasSeed, $hasAssets);
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

    protected function invokeModuleMethod($module, $hasTable = true, $hasSeed = true, $hasAssets = true) {
        $module = $this->getModule($module);
        if (!class_exists($module)) {
            return;
        }
        $methods = [];
        if ($hasTable) {
            $methods[] = 'install';
        }
        if ($hasSeed) {
            $methods[] = 'seeder';
        }
        $instance = new $module;
        foreach ($methods as $method) {
            if (empty($method) || !method_exists($instance, $method)) {
                continue;
            }
            call_user_func([$instance, $method]);
        }
        if ($hasAssets) {
            $this->moveAssets($module);
        }
    }

    protected function getConfigs() {
        return include Factory::config()->getCurrentFile()->getFullName();
    }

    /**
     * @param $configs
     * @throws \Exception
     * @throws \Zodream\Disk\FileException
     */
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