<?php
defined('APP_DIR') or exit();
echo '<?php';
?>

<?php if (isset($is_module) && $is_module):?>
namespace Module\<?=$module?>\Service;

use Module\ModuleController;

class <?=$name.config('app.controller')?> extends ModuleController {
<?php else:?>
namespace Service\<?=$module?>;

use Domain\Model\<?=$name.config('app.model')?>;

class <?=$name.config('app.controller')?> extends Controller {
<?php endif;?>

<?php foreach ($func as $key => $item):?>
    public function <?=$key.config('app.action')?>() {
        <?=$item?>

    }

<?php endforeach;?>

}