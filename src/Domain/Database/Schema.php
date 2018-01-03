<?php
namespace Zodream\Module\Gzo\Domain\Database;

use Zodream\Database\Schema\Schema as BaseSchema;
use Zodream\Disk\Stream;
use Zodream\Infrastructure\Support\Collection;

class Schema extends BaseSchema {

    public static function getAllDatabaseName() {
        $data = static::getAllDatabase();
        return array_column($data, 'Database');
    }

    public function map(callable $func) {
        $data = static::getAllTable(true);
        (new Collection($data))->each(function($item) use ($func) {
            $func((new Table($item['Name'], $item))
                ->setComment($item['Comment'])
                ->setEngine($item['Engine'])
                ->setSchema($this));
        });
    }

    public function setSchema($schema = null) {
        parent::setSchema($schema);
        $this->command()->changedDatabase($this->schema);
        return $this;
    }

    public function getRows($sql) {
        return $this->command()->getArray($sql);
    }

    public function import($file) {
        $stream = new Stream($file);
        if (!$stream->openRead()->isResource()) {
            return false;
        }
        $content = '';
        while ($line = $stream->readLine(4096)) {
            $line = preg_replace('/--[\s\S]+/', '', $line);
            if (empty($line)) {
                continue;
            }
            $content .= $line;
            if (strpos($line, ';') !== false) {
                $this->command()->execute($content);
                $content = '';
            }
        }
        $stream->close();
        return true;
    }

    public function export($file, $hasStructure = true, $hasData = true, $hasDrop = true) {
        $stream = new Stream($file);
        if (!$stream->open('w')
            ->isResource()) {
            return false;
        }
        $stream->writeLines([
            '--备份开始',
            '--创建数据库开始',
            'CREATE SCHEMA IF NOT EXISTS `'.$this->schema.'` DEFAULT CHARACTER SET utf8 ;',
            'USE `'.$this->schema.'` ;',
        ]);

        $this->map(function (Table $table) use ($stream, $hasStructure, $hasData, $hasDrop) {
            $stream->writeLine('--创建表 '.$table->getName().' 开始');
            if ($hasDrop) {
                $stream->writeLine($table->getDropSql());
            }
            if ($hasStructure) {
                $stream->writeLine($table->getCreateTableSql());
            }
            if ($hasData) {
                $count = $table->rows();
                for ($i = 0; $i < $count; $i += 20) {
                    $data = $table->query('')->limit($i, $i + 20)->all();
                    if (empty($data)) {
                        continue;
                    }
                    $sql = sprintf('INSERT INTO `%s` (`%s`) VALUES ',
                        $table->getName(),
                        implode('`, `', array_keys($data[0])));
                    $stream->writeLine($sql);
                    $length = count($data);
                    for ($j = 0; $j < $length; $j ++) {
                        $sql = sprintf('(\'%s\')%s',
                            implode("', '", array_values($data[$j])), $j >= $length - 1 ? ';' : ',');
                        $stream->writeLine($sql);
                    }
                }
            }
            $stream->writeLines([
                '',
                ''
            ]);
        });

        $stream->writeLines([
            '--创建数据库结束',
            '--备份结束'
        ])->close();
        return true;
    }
}