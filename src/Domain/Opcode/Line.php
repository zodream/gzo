<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Domain\Opcode;

class Line {

    public $index = 0;

    public $line = 0;

    public $e;

    public $i;

    public $o;

    public $op;

    public $fetch;

    public $ext;

    public $return;

    public $operands;

    /**
     * @var string 解码之后的代码
     */
    public $code;
}