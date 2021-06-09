<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Domain\Output;

use Zodream\Disk\File;

class FileOutput implements Writer {

    public function write(string|File $file, string $content) {
        file_put_contents((string)$file, $content);
    }
}