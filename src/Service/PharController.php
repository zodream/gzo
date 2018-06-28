<?php
namespace Zodream\Module\Gzo\Service;

use Zodream\Module\Gzo\Domain\Generator\PharGenerator;
use Zodream\Service\Factory;

class PharController extends Controller {

    protected function rules() {
        return [
            '*' => 'cli'
        ];
    }

    public function indexAction($input) {
        $phar = new PharGenerator();
        $phar->setInput(Factory::root()->directory($input));
        $phar->loadComposer();
        $phar->create();
    }
}