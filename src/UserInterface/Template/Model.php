<?php
defined('APP_DIR') or exit();
echo '<?php';
?>
declare(strict_types=1);
<?php if (isset($is_module) && $is_module):?>
namespace Module\<?=$module?>\Domain\Model;
<?php else:?>
namespace Domain\Model<?= empty($module) ? '' : ('\\'. $module) ?>;
<?php endif;?>

use Domain\Model\Model;
/**
 * Class <?=$name.config('app.model')?>

<?php foreach ($property as $key => $item):?>
 * @property <?=$item?> $<?=$key?>

<?php endforeach;?>
 */
class <?=$name.config('app.model')?> extends Model {

<?php if (isset($pk) && $pk != 'id'):?>
    protected string $primaryKey = '<?=$pk?>';
<?php endif;?>

	public static function tableName(): string {
        return '<?=$table?>';
    }

	protected function rules(): array {
		return [
<?php foreach ($rules as $key => $item):?>
            '<?=$key?>' => '<?=$item?>',
<?php endforeach;?>
        ];
	}

	protected function labels(): array {
		return [
<?php foreach ($labels as $key => $item):?>
            '<?=$key?>' => '<?=$item?>',
<?php endforeach;?>
        ];
	}

<?php foreach ($foreignKeys as $item):?>
    public function get<?=ucfirst($item['link_table'])?>() {
        return $this->hasOne('<?=$item['link_table']?>', '<?=$item['link_column']?>', '<?=$item['column']?>');
    }

<?php endforeach;?>
}