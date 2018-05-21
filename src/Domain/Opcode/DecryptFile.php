<?php
namespace Zodream\Module\Gzo\Domain\Opcode;

use Zodream\Disk\File;
use Zodream\Infrastructure\Support\Process;
use Zodream\Service\Factory;

class DecryptFile {
    /**
     * @var File
     */
    protected $srcFile;

    /**
     * @var File
     */
    protected $distFile;

    public function __construct($srcFile, $distFile = null) {
        $this->setSrcFile($srcFile);
        if (!empty($distFile)) {
            $this->setDistFile($distFile);
        }
    }

    /**
     * @param File $srcFile
     */
    public function setSrcFile($srcFile) {
        $this->srcFile = $srcFile instanceof File ? $srcFile : new File($srcFile);
    }

    /**
     * @param File $distFile
     */
    public function setDistFile($distFile) {
        $this->distFile = $distFile instanceof File ? $distFile : new File($distFile);
    }

    /**
     * 获取CMD 执行内容
     * @return bool|string
     */
    public function getContent() {
        $cmd = Factory::config('php_path', 'php').' -dvld.active=1 '.$this->srcFile;  // 替换为你要执行的shell脚本
        $process = Process::factory($cmd);
        $status = $process->start()->join()->stop();
        extract($process->getOutput());
        return $stderr;
    }

    public function decode() {
        $content = (new Decrypt($this->getContent()))->decode();
        if ($this->distFile) {
            return $this->distFile->write($content);
        }
        echo $content;
    }
}