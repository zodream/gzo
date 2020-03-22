<?php
namespace Zodream\Module\Gzo\Service;

use Zodream\Database\Command;
use Zodream\Disk\File;
use Zodream\Disk\ZipStream;
use Zodream\Helpers\Str;
use Zodream\Module\Gzo\Domain\Database\Schema;
use Zodream\Module\Gzo\Domain\GenerateModel;
use Zodream\Service\Factory;
use ReflectionClass;

class SqlController extends Controller {

    public function importAction($schema = null) {
        $this->renewDB();
        set_time_limit(0);
        GenerateModel::schema($schema)->import($_FILES['file']['tmp_name']);
        return $this->jsonSuccess();
    }

    public function exportAction($schema = null,
                                 $sql_structure = false,
                                 $sql_data = false,
                                 $has_drop = false,
                                 $has_schema = false,
                                 $expire = 10,
                                 $format = 'sql',
                                 $table = null) {
        $this->renewDB();
        $root = Factory::root()->directory('data/sql');
        $root->create();
        $file = $root->file($schema.date('Y-m-d').'.sql');
        set_time_limit(0);
        if ((!$file->exist() || $file->modifyTime() < (time() - $expire * 60))
            && !GenerateModel::schema($schema)
                ->export($file, $table, $has_schema, $sql_structure, $sql_data, $has_drop)) {
            return $this->jsonFailure('导出失败！');
        }
        if ($format != 'zip') {
            return Factory::response()->file($file);
        }
        $zip_file = $root->file($schema.date('Y-m-d').'.zip');
        ZipStream::create($zip_file)->addFile($file)->close();
        return Factory::response()->file($zip_file);
    }

    public function copyAction($dist, $src, $column) {
        $distColumn = [];
        $srcColumn = [];
        $parameters = [];
        foreach ($column as $key => $item) {
            if (!preg_match('/^[a-zA-Z_]+/', $key, $match)) {
                continue;
            }
            $distColumn[] = $match[0];
            if (preg_match('/^"(.+)"$/', $item, $match)) {
                $parameters[] = $match[1];
                $item = '?';
            } else {
                preg_match('/^[a-zA-Z_]+/', $key, $match);
                $item = $match[0];
            }
            $srcColumn[] = $item;
        }
        $sql = sprintf('INSERT INTO %s (%s) SELECT %s FROM %s', $dist,
            implode(',', $distColumn), implode(',', $srcColumn), $src);
        $count = Command::getInstance()->update($sql, $parameters);
        return $this->jsonSuccess($count, sprintf('复制成功 %s 行', $count));
    }

    public function tableAction($schema = null) {
        if (!empty($schema)) {
            $this->renewDB();
        }
        $tables = GenerateModel::schema($schema)->getAllTable();
        return $this->jsonSuccess($tables);
    }

    public function schemaAction() {
        $this->renewDB();
        $data = Schema::getAllDatabaseName();
        $data = array_filter($data, function ($item) {
           return !in_array($item, ['information_schema', 'mysql', 'performance_schema', 'sys']);
        });
        return $this->jsonSuccess(array_values($data));
    }

    public function columnAction($table) {
        $this->renewDB();
        $schema = null;
        if (strpos($table, '.') > 0) {
            list($schema, $table) = explode('.', $table);
        }
        $data = GenerateModel::schema($schema)->table($table)->getAllColumn(true);
        $data = array_map(function ($item) {
            $i = strpos($item['Type'], '(');
            if ($i > 0) {
                $item['Type'] = substr($item['Type'], 0, $i);
            }
            return [
                'value' => $item['Field'],
                'label' => sprintf('%s(%s)', $item['Field'], $item['Type'])
            ];
        }, $data);
        return $this->jsonSuccess($data);
    }
}