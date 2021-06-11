<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Service\Api;

use Zodream\Module\Gzo\Domain\Repositories\ModuleRepository;

final class ModuleController extends Controller {

    public function installAction(string $name, string $module,
                                  bool $hasTable = false, bool $hasSeed = false,
                                  bool $hasAssets = false) {
        ModuleRepository::install($name, $module, $hasTable, $hasSeed, $hasAssets);
        return $this->renderData(true);
    }

    public function uninstallAction(string $name) {
        ModuleRepository::uninstall($name);
        return $this->renderData(true);
    }

    public function allAction() {
        return $this->renderData(ModuleRepository::all());
    }
}