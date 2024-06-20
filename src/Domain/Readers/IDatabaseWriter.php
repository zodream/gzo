<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Domain\Readers;


use Zodream\Database\Contracts\Schema;
use Zodream\Database\Contracts\Table;

interface IDatabaseWriter {

    /**
     * 写入一行
     * @param string $line
     * @return static
     */
    public function writeLine(mixed $line): static;

    /**
     * 写入多行
     * @param array $lines
     * @return static
     */
    public function writeLines(array $lines): static;

    public function writeSchema(Schema $schema): static;

    /**
     * @param Table $table
     * @param bool $restore 是否需要删除表重新建
     * @return $this
     */
    public function writeTable(Table $table, bool $restore = false): static;
    public function writeTableData(Table $table): static;
    public function writeData(string $tableName, array $data): static;
    public function writeComment(string $comment): static;

    public function close(): void;
}
