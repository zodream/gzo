<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Service;

/**
 * 通过opcode 解密 需要 添加 vld 插件
 * @see https://github.com/derickr/vld
 * @package Zodream\Module\Gzo\Service
 */

class DecryptController extends Controller {

    public function indexAction() {
        return $this->show('index');
    }

}