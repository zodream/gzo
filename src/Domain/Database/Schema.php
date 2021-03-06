<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Domain\Database;

use Zodream\Database\Contracts\Schema as SchemaInterface;
use Zodream\Database\Contracts\Table as TableInterface;
use Zodream\Database\DB;
use Zodream\Database\Schema\Schema as BaseSchema;
use Zodream\Disk\File;
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

    public function map(callable $func, callable $failure = null) {
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
                if ($failure) return $failure($table, $ex);
            }
        });
    }

    public function name(string $schema): SchemaInterface {
        parent::name($schema);
        Db::db()->changedSchema($this->name);
        return $this;
    }

    public function getRows($sql) {
        return DB::db()->fetch($sql);
    }

    /**
     * 导入文件，导入一行的文件过大可能会报错
     * @param File|string $file
     * @return bool
     * @throws Exception
     */
    public function import($file) {
        $stream = new Stream($file);
        if (!$stream->openRead()->isResource()) {
            return false;
        }
        $content = '';
        while ($line = $stream->readLine(self::LINE_MAX_LENGTH)) {
            if (substr($line, 0, 2) == '--' || $line == '') {
                continue;
            }
            $content .= $line;
            if (substr(trim($line), -1, 1) !== ';') {
                continue;
            }
            DB::db()->execute($content);
            $content = '';
        }
        $stream->close();
        return true;
    }

    /**
     * 导出
     * @param $file
     * @param null $tables
     * @param bool $hasSchema
     * @param bool $hasStructure
     * @param bool $hasData
     * @param bool $hasDrop
     * @return bool
     */
    public function export($file, $tables = null, $hasSchema = true, $hasStructure = true, $hasData = true, $hasDrop = true) {
        $stream = new Stream($file);
        if (!$stream->open('w')
            ->isResource()) {
            return false;
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
            if (!empty($tables) && !in_array($table->getName(), (array)$tables)) {
                $stream->writeLines([
                    '-- 跳过表 '.$table->getName(),
                    '',
                    ''
                ]);
                return;
            }
            $grammar = DB::schemaGrammar();
            $stream->writeLine('-- 创建表 '.$table->getName().' 开始');
            if ($hasDrop) {
                $stream->writeLine($grammar->compileTableDelete($table));
            }
            if ($hasStructure) {
                $stream->writeLine(DB::information()->tableCreateSql($table));
            }
            $count = $table->rows();
            if ($hasData && $count > 0) {
                $columnFields = $table->getFieldsType();
                $stream->writeLine($grammar->compileTableLock($table));
                $onlyMaxSize = max(20, (int)floor(self::LINE_MAX_LENGTH / $table->avgRowLength() / 8)); // 每次取的的最大行数 根据平均行大小取值；
                for ($i = 0; $i < $count; $i += $onlyMaxSize) {
                    $data = DB::table($table->getName())->limit($i, $onlyMaxSize)->all();
                    if (empty($data)) {
                        continue;
                    }
                    $column_sql = sprintf('INSERT INTO `%s` (`%s`) VALUES ',
                        $table->getName(),
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
                $stream->writeLine($grammar->compileTableUnlock($table));
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
        ])->close();
        return true;
    }

    /**
     * 获取插入数据
     * @param $data
     * @param $columnFields
     * @return string
     */
    protected function getRowSql($data, $columnFields) {
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
            $args[] = sprintf('\'%s\'',
                str_replace(
                    ["\r\n", "\r", "\n", '\\\'', '\''],
                    ["\n", '\r\n', '\r\n', '\'', '\\\''], $item));
        }
        return implode(',',$args);
    }
}