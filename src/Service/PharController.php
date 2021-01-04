<?php
namespace Zodream\Module\Gzo\Service;

use Zodream\Module\Gzo\Domain\Generator\PharGenerator;

class PharController extends Controller {

    protected function rules() {
        return [
            '*' => 'cli'
        ];
    }

    public function indexAction($input) {
        $phar = new PharGenerator();
        $phar->setInput(app_path()->directory($input));
        $phar->loadComposer();
        $phar->create();
    }
}