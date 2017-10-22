<?php
namespace Zodream\Module\Gzo\Domain\Opcode;

class DecryptFunc extends Decrypt {

    public $name;

    /**
     * @var Line[]
     */
    public $lines;

    /**
     * DecryptFunc constructor.
     * @param string $name
     * @param Line[] $lines
     */
    public function __construct($name, $lines) {
        $this->name = $name;
        $this->lines = $lines;
    }


}