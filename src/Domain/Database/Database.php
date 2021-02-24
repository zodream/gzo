<?php
namespace Zodream\Module\Gzo\Domain\Database;

use Zodream\Infrastructure\Support\Collection;
use Zodream\Module\Gzo\Domain\InformationSchemaModel;

class Database {

    public static function map(callable $func) {
        $data = InformationSchemaModel::schemas()->get();
        (new Collection($data))->each(function($item) use ($func) {
            $schema = $item['SCHEMA_NAME'];
            if (in_array($schema, ['mysql', 'performance_schema', 'information_schema'])) {
                return;
            }
            $func((new Schema($schema))
                ->charset($item['DEFAULT_CHARACTER_SET_NAME'])
                ->collation($item['DEFAULT_COLLATION_NAME']), $item);
        });
    }
}