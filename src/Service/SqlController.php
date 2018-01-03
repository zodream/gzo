<?php
namespace Zodream\Module\Gzo\Service;

use Zodream\Disk\File;
use Zodream\Helpers\Str;
use Zodream\Module\Gzo\Domain\GenerateModel;
use Zodream\Service\Factory;
use ReflectionClass;

class SqlController extends Controller {

    public function importAction($schema = null) {
        GenerateModel::schema($schema)->import($_FILES['file']['tmp_name']);
        return $this->jsonSuccess();
    }

    public function exportAction($schema = null,
                                 $sql_structure = true,
                                 $sql_data = true,
                                 $has_drop = true) {
        $root = Factory::root()->directory('data/sql');
        $root->create();
        $file = $root->file($schema.date('Y-m-d').'.sql');
        if (!GenerateModel::schema($schema)
            ->export($file, $sql_structure, $sql_data, $has_drop)) {
            return $this->jsonFailure('导出失败！');
        }
        return Factory::response()->file($file);
    }
}