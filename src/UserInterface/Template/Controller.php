<?php
defined('APP_DIR') or exit();
echo '<?php';
?>

<?php if (isset($is_module) && $is_module):?>
namespace Module\<?=$module?>\Service;

use Module\<?=$module?>\Domain\Model\<?=$name.config('app.model')?>;
use Module\ModuleController;

class <?=$name.config('app.controller')?> extends ModuleController {
<?php else:?>
namespace Service\<?=$module?>;

use Domain\Model\<?=$name.config('app.model')?>;

class <?=$name.config('app.controller')?> extends Controller {
<?php endif;?>
	protected $rules = array(
		'*' => '@'
	);
	
	public function index<?=config('app.action')?>() {
		$page = <?=$name.config('app.model')?>::page();
		return $this->show(compact('page'));
	}

    public function add<?=config('app.action')?>($id = null) {
        $model = <?=$name.config('app.model')?>::findOrNew($id);
        if ($model->load() && $model->save()) {
            return $this->redirect('./<?=strtolower($name)?>');
        }
        return $this->show(compact('model'));
	}

    public function delete<?=config('app.action')?>($id) {
        <?=$name.config('app.model')?>::where('id', $id)->delete();
        return $this->redirect('./<?=strtolower($name)?>');
	}

    public function view<?=config('app.action')?>($id) {
		$model = <?=$name.config('app.model')?>::find($id);
        return $this->show(compact('model'));
	}
}