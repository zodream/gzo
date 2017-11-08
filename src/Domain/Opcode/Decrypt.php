<?php
namespace Zodream\Module\Gzo\Domain\Opcode;


use Zodream\Helpers\Str;

class Decrypt {

    /**
     * @var array
     */
    protected $lines = [
        1 => '<?php'
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
        $parts = $this->splitParts();
        foreach ($parts as $part) {
            echo "\n", implode("\n", (array)$part), " END\n";
            $this->addLines((new DecryptBlock($part))->decode());
        }
        return $this->getContent();
    }

    protected function splitParts() {
        $lines = explode("\n", $this->content);
        $parts = [];
        $part = [];
        foreach ($lines as $line) {
            $line = trim($line, "\r");
            if (!Str::startsWith($line, ['Function', 'Class'])) {
                $part[] = $line;
                continue;
            }
            $parts[] = $part;
            $part = [$line];
        }
        $parts[] = $part;
        return $parts;
    }

    protected function getContent() {
        ksort($this->lines);
        $max = max(array_keys($this->lines));
        $lines = [];
        for ($i = 1; $i <= $max; $i ++) {
            $lines[$i] = array_key_exists($i, $this->lines) ? $this->lines[$i] : '';
        }
        return implode(PHP_EOL, $lines);
    }


}