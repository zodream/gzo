<?php
namespace Zodream\Module\Gzo\Service;

use Zodream\Infrastructure\Http\Request;
use Zodream\Route\Controller\ModuleController as BaseController;

abstract class Controller extends BaseController {
    protected function getActionArguments($action, $vars = array()) {
        if (!app('request')->isCli()) {
            return parent::getActionArguments($action, $vars);
        }
        $args = app('request')->argv('arguments');
        if (empty($args)) {
            return parent::getActionArguments($action, app('request')->argv('options'));
        }
        array_shift($args);
        return $args;
    }

    public function jsonFailure($message = '', $code = 400) {
        if (!app('request')->isCli()) {
            return parent::jsonFailure($message, $code);
        }
        return $this->showContent($message);
    }

    public function jsonSuccess($data = null, $message = null) {
        if (!app('request')->isCli()) {
            return parent::jsonSuccess($data, $message);
        }
        return $this->showContent(is_null($message) ? 'true' : $message);
    }
}