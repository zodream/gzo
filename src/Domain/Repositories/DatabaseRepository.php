<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Domain\Repositories;

use Zodream\Database\DB;
use Zodream\Disk\ZipStream;
use Zodream\Html\Page;
use Zodream\Module\Gzo\Domain\Database\Schema;
use Zodream\Module\Gzo\Domain\GenerateModel;
use Zodream\Database\Schema\Schema as BaseSchema;

class DatabaseRepository {

    public static function import(string $file, string $schema = '') {
        static::renewDB();
        set_time_limit(0);
        GenerateModel::schema($schema)->import($file);
    }

    public static function export(string $schema = '',
                                  bool $hasStructure = false,
                                  bool $hasData = false,
                                  bool $hasDrop = false,
                                  bool $hasSchema = false,
                                  int $expire = 10,
                                  string $format = 'sql',
                                  array|string $table = []) {
        static::renewDB();
        $root = app_path()->directory('data/sql');
        $root->create();
        $file = $root->file($schema.date('Y-m-d').'.sql');
        set_time_limit(0);
        if ((!$file->exist() || $file->modifyTime() < (time() - $expire * 60))
            && !GenerateModel::schema($schema)
                ->export($file, $table, $hasSchema, $hasStructure, $hasData, $hasDrop)) {
            throw new \Exception('导出失败！');
        }
        if ($format != 'zip') {
            return $file;
        }
        $zip_file = $root->file($schema.date('Y-m-d').'.zip');
        ZipStream::create($zip_file)->addFile($file)->close();
        return $zip_file;
    }

    public static function tables(string $schema = '', bool $full = false): array {
        if (!empty($schema)) {
            static::renewDB();
        }
        return DB::information()->tableList($schema, $full);
    }

    public static function schemas(bool $full = false): array {
        static::renewDB();
        $data = Schema::getAllDatabaseName();
        $data = array_filter($data, function ($item) {
            return !in_array($item, ['information_schema', 'mysql', 'performance_schema', 'sys']);
        });
        return array_values($data);
    }

    public static function columns(string $table, string $schema = '', bool $full = false) {
        static::renewDB();
        if (strpos($table, '.') > 0) {
            list($schema, $table) = explode('.', $table);
        }
        $data = DB::information()->columnList(GenerateModel::schema($schema)->table($table), true);
        if ($full) {
            return $data;
        }
        return array_map(function ($item) {
            $i = strpos($item['Type'], '(');
            if ($i > 0) {
                $item['Type'] = substr($item['Type'], 0, $i);
            }
            return [
                'value' => $item['Field'],
                'label' => sprintf('%s(%s)', $item['Field'], $item['Type'])
            ];
        }, $data);
    }

    public static function query(string $sql, string $schema = '', int $page = 1, int $per_page = 20) {
        if (!empty($schema)) {
            DB::db()->changedSchema($schema);
        }
        if (stripos($sql, 'limit') > 0 || stripos($sql, 'offset') > 0) {
            $data = DB::fetch($sql);
            return new Page($data, count($data));
        }
        if (stripos($sql, 'select') === false) {
            return new Page(0, $per_page);
        }
        $total = DB::db()->executeScalar(preg_replace('/select[\s\S]+from/i', 'select count(*) as count from', $sql));
        $page = new Page($total, $per_page);
        $page->setPage(DB::fetch($sql. ' limit '. $page->getLimit()));
        return $page;
    }

    public static function tableCreate() {
        //TODO
    }

    public static function schemaCreate(string $name, string $collation) {
        $schema = new BaseSchema($name);
        if (!empty($collation)) {
            $schema->collation($collation);
        }
        DB::db()->execute(DB::schemaGrammar()->compileSchemaCreate($schema));
    }

    public static function copySQL(array $dist, array $src, array $column): array {
        $maps = [];
        $from = '';
        $join = [];
        foreach ($src as $i => $item) {
            $as = 't'.$i;
            $maps[$item['schema']][$item['table']] = $as;
        }
        foreach ($src as $i => $item) {
            $as = $maps[$item['schema']][$item['table']];
            if ($i < 1) {
                $from = self::renderTable($item, $as);
                continue;
            }
            $join[] = sprintf('left join %s on %s.%s=%s.%s',
                self::renderTable($item, $as), $as, $item['column'],
                $maps[$item['foreignSchema']][$item['foreignTable']],
                $item['foreignColumn'],
            );
        }
        $select = [];
        $field = [];
        $parameters = [];
        foreach ($column as $item) {
            $field[] = $item['dist']['value'];
            if (!isset($item['src']) || empty($item['src'])) {
                $select[] = 'null';
                continue;
            }
            $srcItem = $item['src'];
            if ($srcItem['type'] < 1) {
                $select[] = '?';
                $parameters[] = $srcItem['valueType'] === 'number' ? floatval($srcItem['value']) : $srcItem['value'];
                continue;
            }
            if ($srcItem['appendType'] === 'number') {
                $srcItem['append'] = floatval($srcItem['append']);
            }
            $srcColumn = $srcItem['column'];
            $s = sprintf('%s.%s', $maps[$srcColumn['schema']][$srcColumn['table']], $srcColumn['column']);
            if (!empty($srcColumn['append'])) {
                $select[] = sprintf('%s + ?', $s);
                $parameters[] = $srcColumn['append'];
                continue;
            }
            $select[] = $s;
        }
        $sql = sprintf('INSERT INTO %s (%s) SELECT %s FROM %s %s', self::renderTable($dist),
            implode(',', $field), implode(',', $select), $from, implode(' ', $join));
        return [$sql, $parameters];
    }

    private static function renderTable(array $data, string $as = '') {
        $sql = empty($data['schema']) ? sprintf('`%s`', $data['table'])
            : sprintf('`%s`.`%s`', $data['schema'], $data['table']);
        return empty($as) ? $sql : sprintf('%s as %s', $sql, $as);
    }

    /**
     * 重置默认数据库
     */
    protected static function renewDB() {
        $configs = config('db');
        $configs['database'] = 'information_schema';
        config()->set('db', $configs);
        unset($configs);
    }
}