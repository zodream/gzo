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
use Zodream\Disk\Stream;
use Zodream\Infrastructure\Support\Collection;
use Exception;

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
     * @param File|string|IStreamReader $file
     * @return bool
     */
    public function import(File|string|IStreamReader $file): bool {
        $autoClose = $file instanceof IStreamReader;
        if (!$autoClose) {
            $stream = new Stream($file);
            if (!$stream->openRead()->isResource()) {
                return false;
            }
        } else {
            $stream = $file;
        }
        $content = '';
        while ($line = $stream->readLine(self::LINE_MAX_LENGTH)) {
            if (str_starts_with($line, '--') || $line == '') {
                continue;
            }
            $content .= $line;
            if (!str_ends_with(trim($line), ';')) {
                continue;
            }
            DB::db()->execute($content);
            $content = '';
        }
        if ($autoClose) {
            $stream->close();
        }
        return true;
    }

    /**
     * 导出
     * @param File|string|IStreamWriter $file
     * @param array|string|null $tables
     * @param bool $hasSchema
     * @param bool $hasStructure
     * @param bool $hasData
     * @param bool $hasDrop
     * @return bool
     */
    public function export(File|string|IStreamWriter $file, array|string|null $tables = null,
                           bool $hasSchema = true, bool $hasStructure = true,
                           bool $hasData = true, bool $hasDrop = true): bool {
        $autoClose = $file instanceof IStreamWriter;
        if (!$autoClose) {
            $stream = new Stream($file);
            if (!$stream->open('w')
                ->isResource()) {
                return false;
            }
        } else {
            $stream = $file;
        }
        $stream->writeLines([
            '-- 备份开始',
            '-- 创建数据库开始'
        ]);

        if ($hasSchema) {
            $stream->writeLines([
                'CREATE SCHEMA IF NOT EXISTS `'.$this->name.'` DEFAULT CHARACTER SET utf8;',
                'USE `'.$this->name.'`;',
            ]);
        }

        $this->map(function (Table $table) use ($stream, $tables, $hasStructure, $hasData, $hasDrop) {
            $tableName = $table->justName();
            if (!empty($tables) && !in_array($tableName, (array)$tables)) {
                $stream->writeLines([
                    '-- 跳过表 '.$tableName,
                    '',
                    ''
                ]);
                return;
            }
            $grammar = DB::schemaGrammar();
            $stream->writeLine('-- 创建表 '.$tableName.' 开始');
            if ($hasDrop) {
                $stream->writeLine($grammar->compileTableDelete($tableName));
            }
            if ($hasStructure) {
                $stream->writeLine(DB::information()->tableCreateSql($table));
            }
            $count = DB::table($table->getName())->count();
            if ($hasData && $count > 0) {
                $columnFields = $table->getFieldsType();
                $stream->writeLine($grammar->compileTableLock($tableName));
                $onlyMaxSize = empty($table->avgRowLength()) ? 20
                    : max(20, (int)floor(self::LINE_MAX_LENGTH / $table->avgRowLength() / 8)); // 每次取的的最大行数 根据平均行大小取值；
                for ($i = 0; $i < $count; $i += $onlyMaxSize) {
                    $data = DB::table($table->getName())->limit($i, $onlyMaxSize)->all();
                    if (empty($data)) {
                        continue;
                    }
                    $column_sql = sprintf('INSERT INTO `%s` (`%s`) VALUES ',
                        $tableName,
                        implode('`,`', array_keys($data[0])));
                    $stream->write($column_sql);
                    $length = count($data);
                    $size = 0;
                    for ($j = 0; $j < $length; $j ++) {
                        $sql = sprintf('(%s)', $this->getRowSql($data[$j], $columnFields));
                        $size += strlen($sql);
                        // 计算字符长度， 进行再分行
                        if ($size < self::LINE_MAX_LENGTH && $j < $length - 1) {
                            $stream->write($sql.',');
                            continue;
                        }
                        $size = 0;
                        if ($j >= $length - 1) {
                            $stream->writeLine($sql.';');
                            break;
                        }
                        $stream->writeLines([
                            $sql.';'
                        ])->write($column_sql);
                    }
                }
                $stream->writeLine($grammar->compileTableUnlock($tableName));
            }
            $stream->writeLines([
                '',
                ''
            ]);
        }, function (Table $table, Exception $ex) use ($stream) {
            $stream->writeLines([
                '-- 跳过表 '.$table->getName(),
                '-- 导出数据出现错误 '.$ex->getMessage(),
                '',
                ''
            ]);
        });

        $stream->writeLines([
            '-- 创建数据库结束',
            '-- 备份结束'
        ]);
        if ($autoClose) {
            $stream->close();
        }
        return true;
    }

    /**
     * 获取插入数据
     * @param array $data
     * @param array $columnFields
     * @return string
     */
    protected function getRowSql(array $data, array $columnFields): string {
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