<?php
namespace Zodream\Module\Gzo\Service;

use Zodream\Service\Factory;

class ModuleController extends Controller {

    public function indexAction() {
        return $this->show('index');
    }

    public function installAction($name, $module) {
        $configs = Factory::config()->getCurrentFile();
    }

    public function uninstallAction($name) {

    }

}