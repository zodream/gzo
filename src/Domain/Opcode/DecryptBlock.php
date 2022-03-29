<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Domain\Opcode;

class DecryptBlock {

    protected $content;

    protected $data = [
        'EVAL' => 'eval',
        'INCLUDE' => 'include',
        'INCLUDE_ONCE' => 'include_once',
        'REQUIRE' => 'require',
        'REQUIRE_ONCE' => 'require_once',
        '<bool>' => 'true',
        '<array>' => '[]',
        'null' => 'null'
    ];

    protected $indexLines = [];

    protected $deLines = [];

    protected $lineMax;

    protected $className;

    protected $funcName;

    /**
     * 方法参数
     * @var array
     */
    protected $funcArguments = [];

    private $_switch_list = [];

    public function __construct($content) {
        $this->setContent($content);
    }

    /**
     * @param mixed $content
     */
    public function setContent($content) {
        $this->content = $content;
    }

    public function addArgument($key, $value = null) {
        if (!is_null($value)) {
            $key .= ' = '.$value;
        }
        $this->funcArguments[] = $key;
    }

    public function addLine(Line $line) {
        $this->indexLines[$line->index] = $line;
    }

    public function addDeLine($i, $code) {
        $this->deLines[$i] = $code;
    }

    /**
     * @param $index
     * @return Line
     */
    public function getLine($index) {
        return $this->indexLines[$index];
    }

    public function isLast($i) {
        return $this->lineMax == $i;
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

    public function beginSwitchBlock($key) {
        $this->_switch_list[$key] = true;
    }

    public function endSwitchBlock($key) {
        $this->_switch_list[$key] = false;
    }

    public function isSwitchBlock($key) {
        return array_key_exists($key, $this->_switch_list) && $this->_switch_list[$key];
    }


    protected function setDefault(array $lines) {
        foreach ($lines as $line) {
            if (str_contains($line, 'compiled vars:')) {
                break;
            }
        }
        $args = explode(',', substr($line, 14));
        foreach ($args as $arg) {
            if (empty($arg) || !str_contains($arg, '=')) {
                continue;
            }
            list($k, $v) = explode('=', $arg);
            $this->def($k, $v);
        }
    }

    /**
     * @param mixed $funcName
     */
    public function setFuncName($funcName) {
        $this->funcName = trim($funcName);
    }

    /**
     * @param mixed $className
     */
    public function setClassName($className) {
        $this->className = trim($className);
    }

    protected function setName(array $lines) {
        if (str_starts_with($lines[0], 'Function')) {
            $this->setFuncName(substr($lines[0], 8, strpos($lines[0], ':') - 8));
            return;
        }
        if (str_starts_with($lines[0], 'Class')) {
            $this->setFuncName(substr($lines[0], 5, strpos($lines[0], ':') - 5));
            return;
        }
    }

    /**
     * @return string[]
     */
    public function decode() {
        $lines = is_array($this->content) ? $this->content : explode("\n", $this->content);
        $this->setName($lines);
        $this->setDefault($lines);
        $lines = $this->getLines($lines);
        ksort($lines);
        $this->deLines = [];
        foreach ($lines as $key => $line) {
            $this->deLines[$key] = (new DecryptLine($line, $this))->decode();
        }
        return $this->createLines();
    }

    public function createLines() {
        if (empty($this->deLines)) {
            return [];
        }
        if (empty($this->className) && empty($this->funcName)) {
            return $this->deLines;
        }
        $lineNo = array_keys($this->deLines);
        $min = min($lineNo);
        $max = max($lineNo);
        if (!empty($this->funcName)) {
            $this->deLines[$min] = sprintf('function %s (%s) {', $this->funcName, implode(', ', $this->funcArguments));
        }
        if (!empty($this->className)) {
            $this->deLines[$min] = sprintf('class %s {', $this->className);
        }
        $this->deLines[$max] = '}';
        return $this->deLines;
    }

    public function getLines(array $lines) {
        $index = [];
        $args = [];
        $i = 0;
        foreach ($lines as $line) {
            if (str_starts_with($line, 'line')) {
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
            if (str_starts_with($line, '---')) {
                continue;
            }
            if (empty(trim($line))) {
                break;
            }
            $arg = new Line();
            list($arg->line, $arg->index, $arg->e, $arg->i, $arg->o, $arg->op, $arg->fetch, $arg->ext, $arg->return, $arg->operands)
                = $this->splitLine($line, $index);
            if (empty($arg->line)) {
                $arg->line = $i;
            } else {
                $i = $arg->line;
            }
            $this->addLine($arg);
            $args[$i][] = $arg;
        }
        $this->lineMax = $i;
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