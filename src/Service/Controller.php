<?php
namespace Zodream\Module\Gzo\Service;

use Zodream\Infrastructure\Http\Request;
use Zodream\Route\Controller\ModuleController as BaseController;

abstract class Controller extends BaseController {
    protected function getActionArguments($action, $vars = array()) {
        if (!Request::isCli()) {
            return parent::getActionArguments($action, $vars);
        }
        $args = Request::argv('arguments');
        if (empty($args)) {
            return parent::getActionArguments($action, Request::argv('options'));
        }
        array_shift($args);
        return $args;
    }

    protected function setActionArguments($name) {
        return Request::request($name);
    }

    public function jsonFailure($message = '', $code = 400) {
        if (!Request::isCli()) {
            return parent::jsonFailure($message, $code);
        }
        return $this->showContent($message);
    }

    public function jsonSuccess($data = null, $message = null) {
        if (!Request::isCli()) {
            return parent::jsonSuccess($data, $message);
        }
        return $this->showContent(is_null($message) ? 'true' : $message);
    }
}