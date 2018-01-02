<?php
namespace Zodream\Module\Gzo\Service;

use Zodream\Module\Gzo\Domain\GenerateModel;

class HomeController extends Controller {

    public function indexAction() {
        return $this->show();
    }

    public function modelAction() {
        return $this->show();
    }

    public function tableAction() {
        $tables = GenerateModel::schema()->getAllTable();
        return $this->jsonSuccess($tables);
    }

    public function crudAction() {
        return $this->show();
    }

    public function controllerAction() {
        return $this->show();
    }

    public function moduleAction($status = 0) {
        return $this->show(compact('status'));
    }
}