<?php
defined('APP_DIR') or exit();
echo '<?php';
?>

<?php if (isset($is_module) && $is_module):?>
namespace Module\<?=$module?>\Service;

use Module\ModuleController;

class <?=$name.APP_CONTROLLER?> extends ModuleController {
<?php else:?>
namespace Service\<?=$module?>;

use Domain\Model\<?=$name.APP_MODEL?>;

class <?=$name.APP_CONTROLLER?> extends Controller {
<?php endif;?>

<?php foreach ($func as $key => $item):?>
    public function <?=$key.APP_ACTION?>() {
        <?=$item?>
    }

<?php endforeach;?>

}