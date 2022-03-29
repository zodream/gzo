<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo;

/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/10/25
 * Time: 20:19
 */

use ArrayIterator;
use Traversable;
use Zodream\Helpers\Str;

class BlockGenerate implements \IteratorAggregate  {

    protected string $prefix = "    ";

    protected array $blockTag = [
        '{' => '}',
        '(' => ')',
        '[' => ']',
    ];

    protected array $unEndBlock = [];

    protected array $lines = [];

    public function addLine(string $line) {
        $this->lines[] = empty($line) ? '' :
            (str_repeat($this->prefix, count($this->unEndBlock)).$line);
        return $this;
    }

    public function addLineEnd(string $line) {
        return $this->addLine($line.';');
    }

    public function space(string $name) {
        if (count($this->lines) == 0) {
            $this->lines = ['<?php'];
        }
        return $this->addLineEnd('namespace '.$name);
    }

    public function useSpace(array $args) {
        foreach ($args as $key => $item) {
            if (!is_integer($key)) {
                $item .= ' as '.$key;
            }
            $this->addLineEnd('use '.$item);
        }
        return $this;
    }

    public function className(string $name) {
        return $this->startBlock('class '.$name);
    }

    public function readOnly(string $name) {
        return $this->addLineEnd('const '.$name);
    }

    public function privateValue(string $name) {
        return $this->addLineEnd('private $'.$name);
    }

    public function publicValue(string $name) {
        return $this->addLineEnd('public $'.$name);
    }

    public function protectedValue(string $name) {
        return $this->addLineEnd('protected $'.$name);
    }

    public function privateMethod(string $name, mixed $args = null) {
        return $this->startBlock('private function '.
            $name.'('.
            $this->getMethodParam($args).')');
    }

    public function publicMethod(string $name, mixed $args = null) {
        return $this->startBlock('public function '.
            $name.'('.
            $this->getMethodParam($args).')');
    }

    public function protectedMethod(string $name, mixed $args = null) {
        return $this->startBlock('protected function '.
            $name.'('.
            $this->getMethodParam($args).')');
    }

    protected function getMethodParam(mixed $args = null) {
        if (empty($args)) {
            return '';
        }
        if (!is_array($args)) {
            return $args;
        }
        $arg = [];
        foreach ($args as $key => $item) {
            if (!is_integer($key)) {
                $item = $key.' = '.$item;
            }
            $arg[] = '$'.trim($item, '$');
        }
        return implode(', ', $arg);
    }

    public function addBlock(string $arg) {
        $args = explode("\n", $arg);
        foreach ($args as $item) {
            $item = trim($item);
            foreach ($this->blockTag as $key => $tag) {
                if (Str::endWith($item, $key)) {
                    $this->startBlock(trim($item, $key));
                    continue 2;
                }
                if (str_starts_with($item, $tag)) {
                    $this->endBlock();
                    continue 2;
                }
            }
            $this->addLine($item);
        }
        return $this;
    }

    public function startBlock(string $name, string $tag = '{') {
        $this->unEndBlock[] = $tag;
        return $this->addLine(trim($name).' '.$tag);
    }

    public function endBlock() {
        if (empty($this->unEndBlock)) {
            return $this;
        }
        return $this->addLine($this->getUnEndBlock());
    }

    protected function getEndBlockTag(string $tag): string {
        if (array_key_exists($tag, $this->blockTag)) {
            return $this->blockTag[$tag];
        }
        return $tag;
    }

    protected function getUnEndBlock(): string|false {
        if (count($this->unEndBlock) == 0) {
            return false;
        }
        return $this->getEndBlockTag(array_pop($this->unEndBlock));
    }

    public function __toString() {
        while (count($this->unEndBlock) > 0) {
            $this->endBlock();
        }
        return implode("\r\n", $this->lines);
    }

    /**
     * @return array
     */
    public function getLines() {
        return $this->lines;
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator(): Traversable {
        return new ArrayIterator($this->getLines());
    }
}