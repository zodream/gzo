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

    public static function all(): array {
        $data = static::moduleList();
        return array_map(function ($item) {
            return [
                'value' => 'Module\\'.$item,
                'name' => $item,
            ];
        }, $data);
    }

    /**
     * 安装模块
     * @param string|array $name 为数组表示批量 [name => module]
     * @param string $module 当name 为 字符串才起作用
     * @param bool $hasTable
     * @param bool $hasSeed
     * @param bool $hasAssets
     * @return void
     * @throws \Exception
     */
    public static  function install(
        string|array $name, string $module = '',
        bool $hasTable = false, bool $hasSeed = false,
        bool $hasAssets = false): void {
        $methods = [];
        if ($hasTable) {
            $methods[] = 'install';
        }
        if ($hasSeed) {
            $methods[] = 'seeder';
        }
        $items = is_array($name) ? $name : [
            $name => trim($module)
        ];
        $instanceItems = [];
        foreach ($items as $moduleName) {
            $moduleName = static::getModule($moduleName);
            if (!class_exists($moduleName)) {
                continue;
            }
            $instance = new $moduleName;
            $instance->boot();
            $instanceItems[] = $instance;
        }

        foreach ($methods as $method) {
            foreach ($instanceItems as $instance) {
                if (empty($method) || !method_exists($instance, $method)) {
                    continue;
                }
                call_user_func([$instance, $method]);
            }
        }
        static::saveModuleConfigs([
            'modules' => $items
        ]);
        if ($hasAssets) {
            static::moveAssets($module);
        }
    }

    /**
     * 卸载模块
     * @param string|array $name
     * @return void
     */
    public static function uninstall(string|array $name): void {
        $configs = config('route');
        $file = config()->configPath('route');
        foreach ((array)$name as $path) {
            static::invokeModuleMethod($configs['modules'][$path], 'uninstall');
            unset($configs['modules'][$path]);
        }
        static::saveConfig($file, $configs);
    }

    protected static function getModule(string $module): string {
        if (Str::endWith($module, 'Module')) {
            return $module;
        }
        return $module.'\\Module';
    }

    protected static function invokeModuleMethod(string $module, array|string $methods): void {
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
    protected static function saveModuleConfigs(array $configs): void {
        $file = config()->configPath('route');
        $data = config('route');
        $data = Arr::merge2D($data, $configs);
        static::saveConfig($file, $data);
    }

    protected static function removeConfigs(File $file, array $configs): void {
        $data = config('route');
        $data = Arr::unset2D($data, $configs);
        static::saveConfig($file, $data);
    }

    /**
     * 复制资源文件到公共目录
     * @param string $module
     * @throws \Exception
     */
    protected static function moveAssets(string $module): void {
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
    protected static function saveConfig(File $file, array $configs): void {
        $content = Module::view()
            ->render('Template/config', array(
                'data' => $configs
            ));
        $file->write($content);
    }


    public static function moduleList(): array {
        $data = [];
        self::getModulePath(app_path()->directory('Module'), $data);
        return $data;
    }

    private static function getModulePath(Directory $folder, array &$data, string $prefix = ''): void {
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