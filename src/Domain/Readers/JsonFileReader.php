<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Domain\Readers;

use Zodream\Database\Contracts\SchemaGrammar;
use Zodream\Database\DB;
use Zodream\Disk\File;
use Zodream\Disk\IStreamReader;
use Zodream\Disk\Stream;
use Zodream\Helpers\Json;
use Zodream\Module\Gzo\Domain\Database\Schema;

class JsonFileReader implements IDatabaseReader {

    protected IStreamReader $reader;
    protected SchemaGrammar $grammar;
    public function __construct(File|string|IStreamReader $file) {
        $this->grammar = DB::schemaGrammar();
        if ($file instanceof IStreamReader) {
            $this->reader = $file;
            return;
        }
        $this->reader = new Stream($file);
        if (!$this->reader->openRead()
            ->isResource()) {
            throw new \Exception('open read stream failure');
        }
    }

    protected function formatLine(string $line): string {
        $data = Json::decode($line);
        if (empty($data)) {
            return '';
        }
        if (isset($data[JsonFileWriter::TYPE_KEY])) {
            //
            return $this->formatObjectSQL($data);
        }
        if (isset($data[JsonFileWriter::TABLE_KEY])) {
            //
            return $this->formatDataSQL($data);
        }
        if (isset($data[JsonFileWriter::SQL_KEY])) {
            //
            return $data[JsonFileWriter::SQL_KEY];
        }
        return '';
    }

    protected function formatObjectSQL(array $data): string {
        $type = $data[JsonFileWriter::TYPE_KEY];
        unset($data[JsonFileWriter::TYPE_KEY]);
        if (str_contains($type, 'Schema')) {
            $schema = new Schema($data['name']);
            if (!empty($data['charset'])) {
                $schema->charset($data['charset']);
            }
            if (!empty($data['collation'])) {
                $schema->collation($data['collation']);
            }
            return $this->grammar->compileSchemaCreate($schema).
            $this->grammar->compileSchemaUse($schema);
        }
        if (str_contains($type, 'Table')) {
            $sql = '';
            if (!empty($data['restore'])) {
                $sql = $this->grammar->compileTableDelete($data['name']);
            }
            return $sql.$data[JsonFileWriter::SQL_KEY];
        }
        return '';
    }

    protected function formatDataSQL(array $data): string {
        $tableName = $data[JsonFileWriter::TABLE_KEY];
        unset($data[JsonFileWriter::TABLE_KEY]);
        return SqlFileWriter::compileInsertSQL($tableName, $data);
    }

    public function read(): string|false {
        if ($this->reader->isEnd()) {
            return false;
        }
        $line = $this->reader->readLine();
        if (empty($line)) {
            return $line;
        }
        return $this->formatLine($line);
    }

    public function import(): void {
        while (false !== ($line = $this->read())) {
            if (empty($line)) {
                continue;
            }
            DB::db()->execute($line);
        }
    }

    public function close(): void {
        $this->reader->close();
    }
}