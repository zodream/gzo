<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Domain\Repositories;

use Zodream\Disk\Directory;

class ModuleRepository {

    public static function all() {
        $data = static::moduleList();
        return array_map(function ($item) {
            return [
                'value' => 'Module\\'.$item,
                'name' => $item,
            ];
        }, $data);
    }

    public static function moduleList() {
        $data = [];
        self::getModulePath(app_path()->directory('Module'), $data);
        return $data;
    }

    private static function getModulePath(Directory $folder, &$data, $prefix = null) {
        $folder->map(function ($file) use (&$data, $prefix) {
            if (!$file instanceof Directory) {
                return;
            }
            if ($file->hasFile('Module.php')) {
                $data[] = $prefix. $file->getName();
                return;
            }
            self::getModulePath($file, $data, $prefix . $file->getName() .'\\');
        });
    }
}