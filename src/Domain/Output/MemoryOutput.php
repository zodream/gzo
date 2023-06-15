<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Domain\Output;

use Zodream\Disk\Directory;
use Zodream\Disk\File;
use Zodream\Infrastructure\Contracts\ArrayAble;

class MemoryOutput implements Writer, ArrayAble {

    protected array $items = [];

    public function mkdir(Directory $file): Directory {
        return $file;
    }

    public function write(string|File $file, string $content) {
        $root = (string)app_path();
        $path = (string)$file;
        if (str_starts_with($path, $root)) {
            $path = mb_substr($path, mb_strlen($root));
        }
        $this->items[$path] = compact('path', 'content');
    }

    public function toArray(): array {
        return array_values($this->items);
    }

    public function firstContent() {
        if (count($this->items) < 1) {
            return '';
        }
        $item = current($this->items);
        return empty($item) ? '' : $item['content'];
    }
}