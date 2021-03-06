<?php
defined('APP_DIR') or exit();
echo '<?php';
?>

namespace Module\<?=$module?>\Domain\Migrations;

use Zodream\Database\Migrations\Migration;
use Zodream\Database\Schema\Schema;
use Zodream\Database\Schema\Table;
<?php foreach ($data as $item):?>
use Module\<?=$module?>\Domain\Model\<?=$item['name'].config('app.model')?>;
<?php endforeach;?>


class Create<?=$module?>Tables extends Migration {

    public function up() {
<?php foreach ($data as $i => $item):?>
        <?= $i < 1 ? '$this' : '' ?>->append(<?=$item['name'].config('app.model')?>::tableName(), function(Table $table) {
<?php if (isset($item['status']) && $item['status']):?>
            $table->engine('<?=$item['status']['Engine']?>')
                  ->charset('<?=$item['status']['Collation']?>')
                  ->comment('<?=$item['status']['Comment']?>');
<?php endif;?>
<?php foreach ($item['fields'] as $val):?>
            <?=$val?>;
<?php endforeach;?>
        })<?php endforeach;?>->autoUp();
    }

}