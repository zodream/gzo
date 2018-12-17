<?php
defined('APP_DIR') or exit();
echo '<?php';
?>

namespace Module\<?=$module?>;

use Zodream\Route\Controller\Module as BaseModule;
<?php if (isset($migration)):?>
use Module\<?=$module?>\Domain\Migrations\<?=$migration === true ? sprintf('Create%sTables', $module) : $migration?>;
<?php endif;?>

class Module extends BaseModule {

<?php if (isset($migration)):?>
    public function getMigration() {
        return new <?=$migration === true ? sprintf('Create%sTables', $module) : $migration?>();
    }
<?php endif;?>

}