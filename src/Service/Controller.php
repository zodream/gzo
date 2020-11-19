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

    public function renderFailure($message = '', $code = 400) {
        if (!app('request')->isCli()) {
            return parent::renderFailure($message, $code);
        }
        return $this->showContent($message);
    }

    public function renderData($data = null, $message = null) {
        if (!app('request')->isCli()) {
            return parent::renderData($data, $message);
        }
        return $this->showContent(is_null($message) ? 'true' : $message);
    }

    /**
     * 重置默认数据库
     */
    protected function renewDB() {
        $configs = config('db');
        $configs['database'] = 'information_schema';
        config()->set('db', $configs);
        unset($configs);
    }
}