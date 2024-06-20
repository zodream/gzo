<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Domain\Readers;


use Zodream\Database\DB;
use Zodream\Disk\File;
use Zodream\Disk\IStreamReader;
use Zodream\Disk\Stream;

class SqlFileReader implements IDatabaseReader {

    protected IStreamReader $reader;
    public function __construct(File|string|IStreamReader $file) {
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

    public function read(): string|false {
        if ($this->reader->isEnd()) {
            return false;
        }
        $content = '';
        while (false !== ($line = $this->reader->readLine(SqlFileWriter::LINE_MAX_LENGTH))) {
            if (str_starts_with($line, '--') || $line == '') {
                continue;
            }
            $content .= $line;
            if (!str_ends_with(trim($line), ';')) {
                continue;
            }
            break;
        }
        return $content;
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