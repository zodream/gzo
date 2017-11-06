<?php
namespace Zodream\Module\Gzo\Domain\Opcode;

use Zodream\Disk\File;
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
        $deScriptSpec = array(
            0 => array("pipe", "r"),    // stdin
            1 => array("pipe", "w"),    // stdout
            2 => array("pipe", "w")     // stderr
        );
        $cmd = Factory::config('php_path', 'php').' -dvld.active=1 '.$this->srcFile;  // 替换为你要执行的shell脚本
        $pro = proc_open($cmd, $deScriptSpec, $pipes, null, null);
        // $pro为false，表明命令执行失败
        if ($pro == false) {
            return false;
        }
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $status = proc_close($pro);  // 释放proc
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