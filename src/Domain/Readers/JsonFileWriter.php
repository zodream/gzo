<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Domain\Readers;

use Zodream\Database\Contracts\Schema;
use Zodream\Database\Contracts\Table;
use Zodream\Database\DB;
use Zodream\Disk\File;
use Zodream\Disk\IStreamWriter;
use Zodream\Disk\Stream;
use Zodream\Helpers\Json;
use Zodream\Module\Gzo\Domain\Database\Table as GzoTable;

/**
 * 非常规 json，大数据是使用每行一个单独的json合并的，不能直接整个文件json 解析
 */
class JsonFileWriter implements IDatabaseWriter {

    const TYPE_KEY = '$type';
    const TABLE_KEY = '$table';
    const SQL_KEY = '$sql';

    protected IStreamWriter $writer;

    public  function __construct(File|string|IStreamWriter $file) {
        if ($file instanceof IStreamWriter) {
            $this->writer = $file;
            return;
        }
        $this->writer = new Stream($file);
        if (!$this->writer->open('w')
            ->isResource()) {
            throw new \Exception('open write stream failure');
        }
    }

    public function writeLine(mixed $line): static {
        $this->writer->writeLine(is_array($line) || is_object($line) ? Json::encode($line) : $line);
        return $this;
    }

    public function writeLines(array $lines): static {
        foreach ($lines as $line) {
            $this->writeLine($line);
        }
        return $this;
    }

    public function writeSchema(Schema $schema): static {
        $this->writer->writeLine(Json::encode([
            self::TYPE_KEY => get_class($schema),
            'name' => $schema->getName(),
            'charset' => $schema->getCharset(),
            'collation' => $schema->getCollation()
        ]));
        return $this;
    }

    public function writeTable(Table $table, bool $restore = false): static {
        $this->writer->writeLine(Json::encode([
            self::TYPE_KEY => get_class($table),
            'name' => $table->getName(),
            'restore' => $restore,
            self::SQL_KEY => DB::information()->tableCreateSql($table)
        ]));
        return $this;
    }

    public function writeTableData(Table $table): static {
        $tableName = $table instanceof GzoTable ? $table->justName() : $table->getName();
        DB::table($table->getName())->each(function (array $data) use ($tableName) {
             $this->writeData($tableName, $data);
        });
        return $this;
    }

    public function writeData(string $tableName, array $data): static {
        $data[self::TABLE_KEY] = $tableName;
        $this->writer->writeLine(Json::encode($data));
        return $this;
    }

    public function writeComment(string $comment): static {
        return $this;
    }

    public function close(): void {
        $this->writer->close();
    }
}