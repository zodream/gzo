<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Domain\Output;

use Zodream\Disk\Directory;
use Zodream\Disk\File;

interface Writer {
    public function mkdir(Directory $file): Directory;

    public function write(string|File $file, string $content);
}