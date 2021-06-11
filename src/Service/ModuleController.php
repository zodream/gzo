<?php
namespace Zodream\Module\Gzo\Service;

use Zodream\Helpers\Json;
use Zodream\Module\Gzo\Domain\Generator\ModuleGenerator;
use Zodream\Module\Gzo\Domain\Repositories\ModuleRepository;

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
     * @throws \Exception
     * @throws \Zodream\Disk\FileException
     */
    public function installAction(
        string $name, string $module,
        bool $hasTable = false, bool $hasSeed = false,
        bool $hasAssets = false) {
        ModuleRepository::install($name, $module, $hasTable, $hasSeed, $hasAssets);
        return $this->renderData('');
    }

    public function uninstallAction(string $name) {
        ModuleRepository::uninstall($name);
        return $this->renderData('');
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
        return $this->renderData(ModuleRepository::all());
    }
}