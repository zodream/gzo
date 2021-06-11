<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Domain\Repositories;

use Zodream\Database\DB;
use Zodream\Disk\ZipStream;
use Zodream\Html\Page;
use Zodream\Module\Gzo\Domain\Database\Schema;
use Zodream\Module\Gzo\Domain\GenerateModel;

class DatabaseRepository {

    public static function import(string $file, string $schema = '') {
        static::renewDB();
        set_time_limit(0);
        GenerateModel::schema($schema)->import($file);
    }

    public static function export(string $schema = '',
                                  bool $sql_structure = false,
                                  bool $sql_data = false,
                                  bool $has_drop = false,
                                  bool $has_schema = false,
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
                ->export($file, $table, $has_schema, $sql_structure, $sql_data, $has_drop)) {
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