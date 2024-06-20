<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Domain\Readers;


interface IDatabaseReader {

    public function read(): string|false;

    public function import(): void;

    public function close(): void;
}