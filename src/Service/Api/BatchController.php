<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Service\Api;

use Zodream\Route\Controller\Concerns\BatchAction;

final class BatchController extends Controller {
    use BatchAction;

    public function methods() {
        return [
            'index' => 'POST'
        ];
    }

    public function indexAction() {
        return $this->render($this->invokeBatch([
            'modules' => sprintf('%s@%s', ModuleController::class, 'allAction'),
            'routes' => sprintf('%s@%s', ModuleController::class, 'routeAction'),
            'schemas' => sprintf('%s@%s', DatabaseController::class, 'schemaAction'),
            'tables' => sprintf('%s@%s', DatabaseController::class, 'tableAction'),
            'columns' => sprintf('%s@%s', DatabaseController::class, 'columnAction'),
        ]));
    }
}