<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Domain\Readers;

use Zodream\Disk\Directory;
use Zodream\Disk\File;

interface IFileWriter {
    public function mkdir(Directory $file): Directory;

    public function write(string|File $file, string $content): void;
}