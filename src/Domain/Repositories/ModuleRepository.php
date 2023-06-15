<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Domain\Repositories;

use ReflectionClass;
use Zodream\Disk\Directory;
use Zodream\Disk\File;
use Zodream\Disk\FileException;
use Zodream\Helpers\Arr;
use Zodream\Helpers\Str;
use Zodream\Module\Gzo\Module;

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

    public static  function install(
        string $name, string $module,
        bool $hasTable = false, bool $hasSeed = false,
        bool $hasAssets = false) {
        $methods = [];
        if ($hasTable) {
            $methods[] = 'install';
        }
        if ($hasSeed) {
            $methods[] = 'seeder';
        }
        $module = trim($module);
        static::invokeModuleMethod($module, $methods);
        static::saveModuleConfigs([
            'modules' => [
                $name => $module
            ]
        ]);
        if ($hasAssets) {
            static::moveAssets($module);
        }
    }

    public static function uninstall(string $name) {
        $configs = config('route');
        $file = config()->configPath('route');
        if (isset($configs['modules'][$name])) {
            static::invokeModuleMethod($configs['modules'][$name], 'uninstall');
            unset($configs['modules'][$name]);
            static::saveConfig($file, $configs);
        }
    }

    protected static function getModule(string $module) {
        if (Str::endWith($module, 'Module')) {
            return $module;
        }
        return $module.'\\Module';
    }

    protected static function invokeModuleMethod(string $module, array|string $methods) {
        $module = static::getModule($module);
        if (!class_exists($module)) {
            return;
        }
        $instance = new $module;
        $instance->boot();
        foreach ((array)$methods as $method) {
            if (empty($method) || !method_exists($instance, $method)) {
                continue;
            }
            call_user_func([$instance, $method]);
        }
    }

    /**
     * @param array $configs
     */
    protected static function saveModuleConfigs(array $configs) {
        $file = config()->configPath('route');
        $data = config('route');
        $data = Arr::merge2D($data, $configs);
        static::saveConfig($file, $data);
    }

    protected static function removeConfigs(File $file, array $configs) {
        $data = config('route');
        $data = Arr::unset2D($data, $configs);
        static::saveConfig($file, $data);
    }

    /**
     * 复制资源文件到公共目录
     * @param $module
     */
    protected static function moveAssets($module) {
        $module = static::getModule($module);
        if (!class_exists($module)) {
            return;
        }
        $func = new ReflectionClass($module);
        $file = new File($func->getFileName());
        $assetDir = $file->getDirectory()->directory('UserInterface/assets');
        if (!$assetDir->exist()) {
            return;
        }
        $assetDir->copy(public_path()->directory('assets'));
    }

    /**
     * @param File $file
     * @param array $configs
     */
    protected static function saveConfig(File $file, array $configs) {
        $content = Module::view()
            ->render('Template/config', array(
                'data' => $configs
            ));
        $file->write($content);
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