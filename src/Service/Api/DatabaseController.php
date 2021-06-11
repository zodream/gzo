<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Service\Api;

use Zodream\Module\Gzo\Domain\Repositories\DatabaseRepository;

final class DatabaseController extends Controller {


    public function tableAction(string $schema = '', bool $full = false) {
        return $this->renderData(DatabaseRepository::tables($schema, $full));
    }

    public function schemaAction(bool $full = false) {
        return $this->renderData(DatabaseRepository::schemas($full));
    }

    public function columnAction(string $table, string $schema = '', bool $full = false) {
        return $this->renderData(DatabaseRepository::columns($table, $schema, $full));
    }

    public function queryAction(string $sql, string $schema = '', int $page = 1, int $per_page = 20) {
        return $this->renderPage(DatabaseRepository::query($sql, $schema, $page, $per_page));
    }
}