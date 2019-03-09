<?php
namespace Zodream\Module\Gzo;
/**
 * Created by PhpStorm.
 * User: ZoDream
 * Date: 2017/1/1
 * Time: 19:22
 */
use Zodream\Disk\Directory;
use Zodream\Route\Controller\Module as BaseModule;
use Zodream\Template\ViewFactory;

class Module extends BaseModule {

    /**
     * @return ViewFactory
     */
    public static function view() {
        return (new ViewFactory())->setDirectory(new Directory(__DIR__.'/UserInterface'));
    }
}