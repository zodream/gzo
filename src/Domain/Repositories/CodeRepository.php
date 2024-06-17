<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Domain\Repositories;

use Zodream\Module\Gzo\Domain\Output\MemoryOutput;

class CodeRepository {

    public static function exchange(string $content, string $source = '', string $target = ''): MemoryOutput {
        $output = new MemoryOutput();
        $output->write($source, $content);
        return $output;
    }

}