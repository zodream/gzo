<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Service\Api;

use Zodream\Module\Gzo\Domain\Generator\ModuleGenerator;
use Zodream\Module\Gzo\Domain\Output\MemoryOutput;
use Zodream\Module\Gzo\Domain\Repositories\TemplateRepository;
use Zodream\Module\Gzo\Domain\Repositories\CodeRepository;

final class TemplateController extends Controller {

    public function indexAction(string $module,
                                string $table,
                                string $name = '',
                                bool $hasController = true,
                                bool $hasView = true,
                                bool $hasModel = true) {
        TemplateRepository::crud($module, $table, $name, $hasController, $hasView, $hasModel);
        $output = TemplateRepository::output();
        if ($output instanceof MemoryOutput) {
            return $this->renderData($output->toArray());
        }
        return $this->renderData(true);
    }

    public function confAction($name, $data) {
        ModuleGenerator::renderConfigs($name, $data);
        return $this->renderData(true);
    }

    public function modelAction(string $table, string $module = '') {
        TemplateRepository::model($table, $module);
        $output = TemplateRepository::output();
        if ($output instanceof MemoryOutput) {
            return $this->renderData($output->toArray());
        }
        return $this->renderData(true);
    }

    public function migrationAction(array|string $table, string $module) {
        TemplateRepository::migration($table, $module);
        $output = TemplateRepository::output();
        if ($output instanceof MemoryOutput) {
            return $this->renderData($output->toArray());
        }
        return $this->renderData(true);
    }

    public function controllerAction(string $module, string $name = 'Home') {
        TemplateRepository::controller($module, $name);
        $output = TemplateRepository::output();
        if ($output instanceof MemoryOutput) {
            return $this->renderData($output->toArray());
        }
        return $this->renderData(true);
    }

    public function moduleAction(string $module, array|string $table = []) {
        TemplateRepository::module($module, $table);
        $output = TemplateRepository::output();
        if ($output instanceof MemoryOutput) {
            return $this->renderData($output->toArray());
        }
        return $this->renderData(true);
    }

    public function exchangeAction(string $content, string $source = '', string $target = '') {
        return $this->renderData(CodeRepository::exchange($content, $source, $target)->toArray());
    }
}