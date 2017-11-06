<?php
namespace Zodream\Module\Gzo\Domain\Opcode;

use Zodream\Disk\Directory;
use Zodream\Disk\File;
use Zodream\Service\Factory;

class DecryptDirectory {

    /**
     * @var Directory
     */
    protected $srcDirectory;
    /**
     * @var Directory
     */
    protected $distDirectory;

    /**
     * @var Directory
     */
    protected $tempDirectory;

    public function __construct($srcDir, $distDir) {
        $this->setSrcDirectory($srcDir);
        $this->setDistDirectory($distDir);
    }

    /**
     * @param Directory $srcDirectory
     */
    public function setSrcDirectory($srcDirectory) {
        $this->srcDirectory = $srcDirectory instanceof Directory ? $srcDirectory : new Directory($srcDirectory);
    }

    /**
     * @param Directory $distDirectory
     */
    public function setDistDirectory($distDirectory) {
        $this->distDirectory = $distDirectory instanceof Directory ? $distDirectory : new Directory($distDirectory);
    }

    /**
     * @return Directory
     */
    public function getTempDirectory() {
        if (!$this->tempDirectory instanceof Directory) {
            $this->tempDirectory = new Directory(Factory::config('temp_dir', Factory::root()->addDirectory('data/temp')));
        }
        return $this->tempDirectory;
    }

    protected function createTempFile(File $file) {
        return $this->getTempDirectory()->addFile(md5($file->getFullName()).'.php', $file->read());
    }

    protected function getNewFile(File $file) {
        return $this->distDirectory->file(substr((string)$file, strlen((string)$this->srcDirectory)));
    }

    public function decode() {
        $this->decodeDir($this->srcDirectory);
    }

    protected function decodeDir(Directory $dir) {
        $dir->map(function($file) {
            if ($file instanceof Directory) {
                $this->decodeDir($file);
                return;
            }
            if (in_array($file->getExtension(), ['php', 'phtml'])) {
                $tempFile = $this->createTempFile($file);
                (new DecryptFile($tempFile, $this->getNewFile($file)))->decode();
                $tempFile->delete();
            }
        });
    }

}