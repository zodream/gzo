<?php
namespace Zodream\Module\Gzo\Domain\Opcode;


class Decrypt {

    /**
     * @var array
     */
    protected $lines = [
        0 => '<?php'
    ];

    protected $content;

    public function __construct($content) {
        $this->setContent($content);
    }

    /**
     * @param mixed $content
     */
    public function setContent($content) {
        $this->content = $content;
    }

    public function addLine($num, $str) {
        $this->lines[$num] = $str;
        return $this;
    }

    public function addLines(array $lines) {
        foreach ($lines as $key => $line) {
            $this->addLine($key, $line);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function decode() {
        echo $this->content;
        $parts = explode('branch:', $this->content);
        foreach ($parts as $part) {
            $this->addLines((new DecryptBlock($part))->decode());
        }
        return $this->getContent();
    }

    protected function getContent() {
        ksort($this->lines);
        $max = max(array_keys($this->lines));
        $lines = [];
        for ($i = 0; $i <= $max; $i ++) {
            $lines[$i] = array_key_exists($i, $this->lines) ? $this->lines[$i] : '';
        }
        return implode(PHP_EOL, $lines);
    }


}