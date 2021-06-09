<?php
namespace Zodream\Module\Gzo;
/**
 * Created by PhpStorm.
 * User: ZoDream
 * Date: 2017/1/1
 * Time: 19:22
 */
use Zodream\Disk\Directory;
use Zodream\Module\Gzo\Domain\Output\FileOutput;
use Zodream\Module\Gzo\Domain\Output\MemoryOutput;
use Zodream\Module\Gzo\Domain\Output\Writer;
use Zodream\Route\Exception\NotFoundHttpException;
use Zodream\Route\Controller\Module as BaseModule;
use Zodream\Template\ViewFactory;

class Module extends BaseModule {

    public function boot()
    {
        if (!app()->isDebug()) {
            throw new NotFoundHttpException('当前模块不允许');
        }
        app()->scoped(Writer::class,
            !empty(request()->get('preview')) ? MemoryOutput::class : FileOutput::class);
    }

    /**
     * @return ViewFactory
     */
    public static function view() {
        return (new ViewFactory())->setDirectory(new Directory(__DIR__.'/UserInterface'));
    }
}