<?php
namespace Zodream\Module\Gzo\Service;

use Zodream\Helpers\Str;
use Zodream\Module\Gzo\Domain\Generator\ModuleGenerator;

class ComposerController extends Controller {


    public function initAction() {
        $file = app_path()->file('Service/config/config.php');
        if ($file->exist()) {
            return;
        }

        $content = ModuleGenerator::renderTemplate('config', [
            'data' => $this->generateConfigs()
        ]);
        $file->write($content);
        return;
    }

    protected function generateRandomKey() {
        return 'base64:'.base64_encode(
                Str::randomBytes(32)
            );
    }

    protected function generateConfigs() {
        $key = $this->generateRandomKey();
        return [
            'app' => [
                'host' => 'localhost',
                'key' => $key
            ],
            'db' => [
                'database' => 'zodream',
                'user' => 'root',
                'password' => 'root',
                'prefix' => '',
            ],
            'auth' => [
                'home' => '/auth',
                'model' => 'Module\Auth\Domain\Model\UserModel',
            ],
            'mail' => [
                'host' => null,
                'port' => '25',
                'user' => null,
                'name' => 'ZoDream',
                'password' => null,
            ],
            'modules' => [

            ],
            'routes' => [

            ],
        ];
    }
}