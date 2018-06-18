<?php
defined('APP_DIR') or exit();
echo '<?php';
?>

namespace Module\<?=$module?>;

use Zodream\Route\Controller\Module as BaseModule;
<?php if (isset($migration)):?>
use Module\Finance\Domain\Migrations\<?=$migration?>;
<?php endif;?>

class Module extends BaseModule {

<?php if (isset($migration)):?>
    public function getMigration() {
        return new <?=$migration?>();
    }
<?php endif;?>

}