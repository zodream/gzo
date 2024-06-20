<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Domain\Readers;

use Zodream\Database\Contracts\Schema;
use Zodream\Database\Contracts\SchemaGrammar;
use Zodream\Database\Contracts\Table;
use Zodream\Database\Utils;
use Zodream\Module\Gzo\Domain\Database\Table as GzoTable;
use Zodream\Database\DB;
use Zodream\Disk\File;
use Zodream\Disk\IStreamWriter;
use Zodream\Disk\Stream;

class SqlFileWriter implements IDatabaseWriter {

    const LINE_MAX_LENGTH = 1048576;  // 一行读取的最大长度 1M

    protected IStreamWriter $writer;
    protected SchemaGrammar $grammar;

    public  function __construct(File|string|IStreamWriter $file) {
        $this->grammar = DB::schemaGrammar();
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


    public function writeLine(mixed $line): static
    {
        $this->writer->writeLine($line);
        return $this;
    }

    public function writeLines(array $lines): static
    {
        $this->writer->writeLines($lines);
        return $this;
    }

    public function writeSchema(Schema $schema): static
    {
        $this->writer->writeLines([
            $this->grammar->compileSchemaCreate($schema),
            $this->grammar->compileSchemaUse($schema),
        ]);
        return $this;
    }

    public function writeTable(Table $table, bool $restore = false): static {
        if ($restore) {
            $this->writer->writeLine($this->grammar->compileTableDelete(
                $table instanceof GzoTable ? $table->justName() : $table->getName()));
        }
        $this->writer->writeLine(DB::information()->tableCreateSql($table));
        return $this;
    }

    public function writeTableData(Table $table): static {
        $count = DB::table($table->getName())->count();
        if ($count === 0) {
            return $this;
        }
        $tableName = $table instanceof GzoTable ? $table->justName() : $table->getName();
        $columnFields = $table->getFieldsType();
        $this->writer->writeLine($this->grammar->compileTableLock($tableName));
        $onlyMaxSize = empty($table->avgRowLength()) ? 20
            : max(20, (int)floor(self::LINE_MAX_LENGTH / $table->avgRowLength() / 8)); // 每次取的的最大行数 根据平均行大小取值；
        for ($i = 0; $i < $count; $i += $onlyMaxSize) {
            $data = DB::table($table->getName())->limit($i, $onlyMaxSize)->get();
            if (empty($data)) {
                continue;
            }
            $column_sql = sprintf('INSERT INTO %s (`%s`) VALUES ',
                Utils::wrapName($tableName),
                implode('`,`', array_keys($data[0])));
            $this->writer->write($column_sql);
            $length = count($data);
            $size = 0;
            for ($j = 0; $j < $length; $j ++) {
                $sql = sprintf('(%s)', $this->getRowSql($data[$j], $columnFields));
                $size += strlen($sql);
                // 计算字符长度， 进行再分行
                if ($size < self::LINE_MAX_LENGTH && $j < $length - 1) {
                    $this->writer->write($sql.',');
                    continue;
                }
                $size = 0;
                if ($j >= $length - 1) {
                    $this->writer->writeLine($sql.';');
                    break;
                }
                $this->writer->writeLines([
                    $sql.';'
                ])->write($column_sql);
            }
        }
        $this->writer->writeLine($this->grammar->compileTableUnlock($tableName));
        return $this;
    }

    public function writeData(string $tableName, array $data): static {
        $this->writer->writeLine(static::compileInsertSQL($tableName, $data, []));
        return $this;
    }

    public function writeComment(string $comment): static {
        $this->writer->writeLine('-- '.$comment);
        return $this;
    }

    public function close(): void {
        $this->writer->close();
    }


    public static function compileInsertSQL(string $tableName, array $data, array $columnFields = []): string {
        if (empty($data)) {
            return sprintf('INSERT INTO %s (NULL)', Utils::wrapName($tableName));
        }
        return sprintf('INSERT INTO %s (`%s`) VALUES (%s)',
            Utils::wrapName($tableName),
            implode('`,`', array_keys($data[0])),
            static::getRowSql($data, $columnFields)
        );
    }

    /**
     * 获取插入数据
     * @param array $data
     * @param array $columnFields
     * @return string
     */
    protected static function getRowSql(array $data, array $columnFields): string {
        $args = [];
        foreach ($data as $key => $item) {
            if (is_null($item)) {
                $args[] = 'NULL';
                continue;
            }
            if (array_key_exists($key, $columnFields) && $columnFields[$key]) {
                $args[] = $item;
                continue;
            }
            $args[] = DB::engine()->escapeString($item);
        }
        return implode(',',$args);
    }
}