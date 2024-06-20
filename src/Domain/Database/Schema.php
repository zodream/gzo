<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Domain\Database;

use Zodream\Database\Contracts\Schema as SchemaInterface;
use Zodream\Database\Contracts\Table as TableInterface;
use Zodream\Database\DB;
use Zodream\Database\Schema\Schema as BaseSchema;
use Zodream\Disk\File;
use Zodream\Disk\IStreamReader;
use Zodream\Disk\IStreamWriter;
use Zodream\Infrastructure\Support\Collection;
use Exception;
use Zodream\Module\Gzo\Domain\Readers\IDatabaseReader;
use Zodream\Module\Gzo\Domain\Readers\IDatabaseWriter;
use Zodream\Module\Gzo\Domain\Readers\SqlFileReader;
use Zodream\Module\Gzo\Domain\Readers\SqlFileWriter;

class Schema extends BaseSchema {

    const LINE_MAX_LENGTH = 1048576;  // 一行读取的最大长度 1M

    /**
     * @param TableInterface|string $table
     * @return Table
     */
    public function table(TableInterface|string $table): TableInterface {
        if ($table instanceof TableInterface) {
            $this->items[$table->getName()] = $table->setSchema($this);
            return $table;
        }
        if (isset($this->items[$table])) {
            return $this->items[$table];
        }
        return $this->items[$table] = (new Table($table))->setSchema($this);
    }

    public static function getAllDatabaseName(): array {
        return DB::information()->schemaList();
    }

    public function map(callable $func, callable $failure = null): void {
        $data = DB::information()->tableList($this, true);
        (new Collection($data))->each(function($item) use ($func, $failure) {
            $table = (new Table($item['Name']))
                ->setData($item)
                ->comment($item['Comment'])
                ->engine($item['Engine'])
                ->setSchema($this);
            try {
                $func($table);
            } catch (Exception $ex) {
                logger($ex->getMessage());
                if ($failure) {
                    return $failure($table, $ex);
                }
            }
            return null;
        });
    }

    public function name(string $name): SchemaInterface {
        parent::name($name);
        Db::db()->changedSchema($this->name);
        return $this;
    }

    public function getRows($sql) {
        return DB::db()->fetch($sql);
    }

    /**
     * 导入文件，导入一行的文件过大可能会报错
     * @param File|string|IStreamReader|IDatabaseReader $file
     * @return bool
     * @throws Exception
     */
    public function import(File|string|IStreamReader|IDatabaseReader $file): bool {
        $autoClose = $file instanceof IStreamReader || $file instanceof IDatabaseReader;
        $stream = $file instanceof IDatabaseReader ? $file : new SqlFileReader($file);
        $stream->import();
        if ($autoClose) {
            $stream->close();
        }
        return true;
    }

    /**
     * 导出
     * @param File|string|IStreamWriter|IDatabaseWriter $file
     * @param array|string|null $tables
     * @param bool $hasSchema
     * @param bool $hasStructure
     * @param bool $hasData
     * @param bool $hasDrop
     * @return bool
     * @throws Exception
     */
    public function export(File|string|IStreamWriter|IDatabaseWriter $file, array|string|null $tables = null,
                           bool $hasSchema = true, bool $hasStructure = true,
                           bool $hasData = true, bool $hasDrop = true): bool {
        $autoClose = $file instanceof IStreamWriter || $file instanceof IDatabaseWriter;
        $stream = $file instanceof IDatabaseWriter ? $file : new SqlFileWriter($file);
        $stream->writeComment('备份开始');
        $stream->writeComment('创建数据库开始');
        if ($hasSchema) {
            $stream->writeSchema($this);
        }

        $this->map(function (Table $table) use ($stream, $tables, $hasStructure, $hasData, $hasDrop) {
            $tableName = $table->justName();
            if (!empty($tables) && !in_array($tableName, (array)$tables)) {
                $stream->writeComment('跳过表 '.$tableName);
                return;
            }

            $stream->writeComment('创建表 '.$tableName.' 开始');
            if ($hasStructure) {
                $stream->writeTable($table, $hasDrop);
            }
            if ($hasData) {
                $stream->writeTableData($table);
            }
            $stream->writeLines([
                '',
                ''
            ]);
        }, function (Table $table, Exception $ex) use ($stream) {
            $stream->writeComment('跳过表 '.$table->getName());
            $stream->writeComment('导出数据出现错误 '.$ex->getMessage());
        });
        $stream->writeComment('创建数据库结束');
        $stream->writeComment('备份结束');
        if ($autoClose) {
            $stream->close();
        }
        return true;
    }


}