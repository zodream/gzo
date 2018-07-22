<?php
defined('APP_DIR') or exit();
echo '<?php';
?>

<?php if (isset($is_module) && $is_module):?>
namespace Module\<?=$module?>\Service;

use Module\<?=$module?>\Domain\Model\<?=$name.APP_MODEL?>;
use Module\ModuleController;

class <?=$name.config('app.controller')?> extends ModuleController {
<?php else:?>
namespace Service\<?=$module?>;

use Domain\Model\<?=$name.APP_MODEL?>;

class <?=$name.config('app.controller')?> extends Controller {
<?php endif;?>
	protected $rules = array(
		'*' => '@'
	);
	
	public function index<?=config('app.action')?>() {
		$page = <?=$name.APP_MODEL?>::findPage();
		return $this->show(array(
			'title' => '',
			'page' => $page
		));
	}

    public function add<?=config('app.action')?>($id = null) {
        $model = <?=$name.APP_MODEL?>::findOrNew($id);
        if ($model->load() && $model->save()) {
            return $this->redirect(['<?=$name?>']);
        }
        return $this->show([
            'model' => $model
        ]);
	}

    public function delete<?=config('app.action')?>($id) {
        <?=$name.APP_MODEL?>::where(['id' => $id])->delete();
        return $this->redirect(['<?=$name?>']);
	}

    public function view<?=config('app.action')?>($id) {
		$model = <?=$name.APP_MODEL?>::find($id);
        return $this->show([
            'model' => $model
        ]);
	}
}