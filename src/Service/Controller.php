<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Service;

use Zodream\Infrastructure\Contracts\Http\Input;
use Zodream\Infrastructure\Contracts\Http\Output;
use Zodream\Route\Controller\Controller as BaseController;

abstract class Controller extends BaseController {

    public function renderFailure(string|array $message, int $code = 400, int $statusCode = 0): Output {
        if (!request()->isCli()) {
            return parent::renderFailure($message, $code, $statusCode);
        }
        return $this->showContent($message);
    }

    public function renderData(mixed $data, string $message = ''): Output {
        if (!$this->httpContext('request')->isCli()) {
            return parent::renderData($data, $message);
        }
        return $this->showContent(is_null($message) ? 'true' : $message);
    }


}