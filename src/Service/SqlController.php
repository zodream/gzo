<?php
namespace Zodream\Module\Gzo\Service;

use Zodream\Disk\File;
use Zodream\Disk\ZipStream;
use Zodream\Helpers\Str;
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
}