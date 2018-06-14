<?php
namespace Zodream\Module\Gzo\Service;

use Zodream\Disk\File;
use Zodream\Disk\FileException;
use Zodream\Helpers\Arr;
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
     * @param bool $isGlobal
     * @return Response
     * @throws \Exception
     * @throws \Zodream\Disk\FileException
     */
    public function installAction($name, $module, $hasTable = false, $hasSeed = false, $hasAssets = false, $isGlobal = false) {
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
        return $this->jsonSuccess();
    }

    public function uninstallAction($name) {
        $files = [Factory::config()->getCurrentFile(), Factory::config()->getDirectory()->file('config.php')];
        foreach ($files as $file) {
            $configs = Factory::config()->getConfigByFile($file);
            if (isset($configs['modules'][$name])) {
                $this->invokeModuleMethod($configs['modules'][$name], 'uninstall');
                unset($configs['modules'][$name]);
                $this->saveConfig($file, $configs);
            }
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
    }

    /**
     * @param $configs
     * @param bool $isGlobal
     * @throws \Exception
     * @throws FileException
     */
    protected function saveModuleConfigs($configs, $isGlobal = true) {
        $file = $isGlobal ? Factory::config()->getDirectory()->file('config.php') : Factory::config()->getCurrentFile();
        $data = Factory::config()->getConfigByFile($file);
        $data = Arr::merge2D($data, $configs);
        $this->saveConfig($file, $data);
    }

    protected function removeConfigs(File $file, $configs) {
        $data = Factory::config()->getConfigByFile($file);
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
        $assetDir->copy(Factory::public_path()->directory('assets'));
    }

    /**
     * @param $file
     * @param $configs
     * @throws FileException
     * @throws \Exception
     */
    protected function saveConfig(File $file, array $configs) {
        $content = Factory::view()
            ->render('Template/config', array(
                'data' => $configs
            ));
        $file->write($content);
    }

}