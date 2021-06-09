<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Service;

use Zodream\Infrastructure\Contracts\Http\Output;
use Zodream\Module\Gzo\Domain\Repositories\DatabaseRepository;

class SqlController extends Controller {

    public function importAction(string $schema = '') {
        DatabaseRepository::import($_FILES['file']['tmp_name'], $schema);
        return $this->renderData(true);
    }

    public function exportAction(Output $output, string $schema = '',
                                 bool $sql_structure = false,
                                 bool $sql_data = false,
                                 bool $has_drop = false,
                                 bool $has_schema = false,
                                 int $expire = 10,
                                 string $format = 'sql',
                                 array|string $table = []) {
        try {
            $file = DatabaseRepository::export($schema, $sql_structure, $sql_data, $has_drop, $has_schema, $expire, $format, $table);
        } catch (\Exception $ex) {
            return $this->renderFailure($ex->getMessage());
        }
        return $output->file($file);
    }

    public function copyAction($dist, $src, $column, bool $preview = false) {
        $distColumn = [];
        $srcColumn = [];
        $parameters = [];
        foreach ($column as $key => $item) {
            if (!preg_match('/^[a-zA-Z_]+/', $key, $match)) {
                continue;
            }
            $distColumn[] = $match[0];
            if (preg_match('/^"(.*)"$/', $item, $match)) {
                $parameters[] = $match[1];
                $item = '?';
            } else {
                preg_match('/^[a-zA-Z_]+/', $item, $match);
                $item = $match[0];
            }
            $srcColumn[] = $item;
        }
        $sql = sprintf('INSERT INTO %s (%s) SELECT %s FROM %s', $dist,
            implode(',', $distColumn), implode(',', $srcColumn), $src);
        if ($preview) {
            return $this->renderData([
                'code' => $sql,
                'parameters' => $parameters
            ]);
        }
        $count = db()->update($sql, $parameters);
        return $this->renderData($count, sprintf('复制成功 %s 行', $count));
    }

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