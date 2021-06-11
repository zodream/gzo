<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Service\Api;

use Zodream\Module\Gzo\Domain\Repositories\ModuleRepository;

final class ModuleController extends Controller {

    public function indexAction() {
    }

    public function allAction() {
        return $this->renderData(ModuleRepository::all());
    }
}