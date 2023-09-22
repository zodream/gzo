<?php
defined('APP_DIR') or exit();
echo '<?php';
?>

namespace Service\<?=$module?>;

use Module\ModuleController as BaseController;
use Zodream\Disk\File;

abstract class Controller extends BaseController {

    public string|File $layout = 'main';

}