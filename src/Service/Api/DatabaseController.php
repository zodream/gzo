<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Service\Api;

use Zodream\Infrastructure\Contracts\Http\Output;
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

    public function importAction(string $schema = '') {
        DatabaseRepository::import($_FILES['file']['tmp_name'], $schema);
        return $this->renderData(true);
    }

    public function exportAction(Output $output, string $schema = '',
                                 bool $hasStructure = false,
                                 bool $hasData = false,
                                 bool $hasDrop = false,
                                 bool $hasSchema = false,
                                 int $expire = 10,
                                 string $format = 'sql',
                                 array|string $table = []) {
        try {
            $file = DatabaseRepository::export($schema, $hasStructure, $hasData, $hasDrop, $hasSchema, $expire, $format, $table);
        } catch (\Exception $ex) {
            return $this->renderFailure($ex->getMessage());
        }
        return $output->file($file);
    }

    public function copyAction(array $dist, array $src, array $column, bool $preview = false) {
        list($sql, $parameters) = DatabaseRepository::copySQL($dist, $src, $column);
        if ($preview) {
            return $this->renderData([
                'code' => $sql,
                'parameters' => $parameters
            ]);
        }
        $count = db()->update($sql, $parameters);
        return $this->renderData($count, sprintf('复制成功 %s 行', $count));
    }
}