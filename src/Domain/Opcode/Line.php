<?php
namespace Zodream\Module\Gzo\Domain\Opcode;

class Line {

    public $index = 0;

    public $line = 0;

    public $op;

    public $fetch;

    public $ext;

    public $return;

    public $operands;

    /**
     * @var 解码之后的代码
     */
    public $code;

    public static function parse(array $lines) {
        $index = [];
        $args = [];
        $i = 0;
        foreach ($lines as $line) {
            if (strpos($line, 'line') === 0) {
                $index = [
                    0,
                    strpos($line, '#*'),
                    strpos($line, 'E'),
                    strpos($line, 'I'),
                    strpos($line, 'O'),
                    strpos($line, 'op'),
                    strpos($line, 'fetch'),
                    strpos($line, 'ext'),
                    strpos($line, 'return'),
                    strpos($line, 'operands'),
                ];
                continue;
            }
            if (empty($index)) {
                return;
            }
            if (strpos($line, '---') === 0) {
                continue;
            }
            if (empty(trim($line))) {
                break;
            }
            $arg = new static();
            list($arg->line, $arg->index, $a, $b, $c, $arg->op, $arg->fetch, $arg->ext, $arg->return, $arg->operands)
                = static::splitLine($line, $index);
            if (empty($arg->line)) {
                $arg->line = $i;
            } else {
                $i = $arg->line;
            }
            $args[$i][] = $arg;
        }

        return $args;
    }
    protected static function splitLine($line, array $args) {
        $data = [];
        $count = count($args);
        for ($i = 0; $i < $count; $i ++) {
            $arg = $i < $count - 1 ? substr($line, $args[$i], $args[$i + 1] - $args[$i]) :
                substr($line, $args[$i]);
            $data[] = trim($arg);
        }
        return $data;
    }
}