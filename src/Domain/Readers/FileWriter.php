<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Domain\Readers;

use Zodream\Disk\Directory;
use Zodream\Disk\File;

class FileWriter implements IFileWriter {

    public function mkdir(Directory $file): Directory {
        $file->create();
        return $file;
    }

    public function write(string|File $file, string $content): void {
        file_put_contents((string)$file, $content);
    }
}