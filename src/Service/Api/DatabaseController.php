<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Service\Api;

use Zodream\Module\Gzo\Domain\Repositories\DatabaseRepository;

final class DatabaseController extends Controller {


    public function tableAction(string $schema = '') {
        return $this->renderData(DatabaseRepository::tables($schema));
    }

    public function schemaAction() {
        return $this->renderData(DatabaseRepository::schemas());
    }

    public function columnAction(string $table) {
        return $this->renderData(DatabaseRepository::columns($table));
    }
}