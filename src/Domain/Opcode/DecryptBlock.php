<?php
namespace Zodream\Module\Gzo\Domain\Opcode;

class DecryptBlock {

    protected $content;

    protected $data = [];

    public function __construct($content) {
        $this->setContent($content);
    }

    /**
     * @param mixed $content
     */
    public function setContent($content) {
        $this->content = $content;
    }

    public function def($key, $value) {
        $this->data[trim($key)] = trim($value);
        return $this;
    }

    public function get($key) {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }
        return null;
    }

    protected function setDefault(array $lines) {
        foreach ($lines as $line) {
            if (strpos($line, 'compiled vars:') !== false) {
                break;
            }
        }
        $args = explode(',', substr($line, 14));
        foreach ($args as $arg) {
            if (empty($arg)) {
                continue;
            }
            list($k, $v) = explode('=', $arg);
            $this->def($k, $v);
        }

    }

    /**
     * @return string[]
     */
    public function decode() {
        $lines = explode("\n", $this->content);
        $this->setDefault($lines);
        $lines = $this->getLines($lines);
        ksort($lines);
        $data = [];
        foreach ($lines as $key => $line) {
            $data[$key] = (new DecryptLine($line, $this))->decode();
        }
        return $data;
    }

    public function getLines(array $lines) {
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
                continue;
            }
            if (strpos($line, '---') === 0) {
                continue;
            }
            if (empty(trim($line))) {
                break;
            }
            $arg = new Line();
            list($arg->line, $arg->index, $a, $b, $c, $arg->op, $arg->fetch, $arg->ext, $arg->return, $arg->operands)
                = $this->splitLine($line, $index);
            if (empty($arg->line)) {
                $arg->line = $i;
            } else {
                $i = $arg->line;
            }
            $args[$i][] = $arg;
        }
        return $args;
    }

    protected function splitLine($line, array $args) {
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