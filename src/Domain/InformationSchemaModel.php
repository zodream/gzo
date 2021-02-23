<?php
namespace Zodream\Module\Gzo\Domain;

use Zodream\Database\Query\Builder;

class InformationSchemaModel extends Builder {

    public function addPrefix($table) {
        return sprintf('`information_schema`.`%s`', $table);
    }

    /**
     * 查询关于数据库的信息
     * @return static
     */
    public static function schemas() {
        return (new static())->from('SCHEMATA');
    }

    /**
     * 查询关于数据库中的表的信息
     * @return static
     */
    public static function tables() {
        return (new static())->from('TABLES');
    }

    /**
     * 查询表中的列信息
     * @return static
     */
    public static function columns() {
        return (new static())->from('COLUMNS');
    }
}