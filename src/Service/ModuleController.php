<?php
namespace Zodream\Module\Gzo\Service;

use Zodream\Helpers\Str;
use Zodream\Service\Factory;

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
        $this->invokeModuleMethod($module, 'install');
        $this->saveConfigs($configs);
        return $this->showContent('true');
    }

    public function uninstallAction($name) {
        $configs = $this->getConfigs();
        if (isset($configs['modules'][$name])) {
            $this->invokeModuleMethod($configs['modules'][$name], 'uninstall');
            unset($configs['modules'][$name]);
            $this->saveConfigs($configs);
        }
        return $this->showContent('true');
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

    protected function getConfigs() {
        return include Factory::config()->getCurrentFile()->getFullName();
    }

    protected function saveConfigs($configs) {
        Factory::config()->getCurrentFile()->write(Factory::view()
            ->render('Template/config', array(
                'data' => $configs
            )));
    }

}