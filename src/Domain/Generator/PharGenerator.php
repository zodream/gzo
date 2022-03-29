<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Domain\Generator;

use Zodream\Disk\Directory;
use Phar;
use Zodream\Disk\File;
use Zodream\Helpers\Json;

class PharGenerator {

    /**
     * @var Directory
     */
    protected $input;

    /**
     * @var File
     */
    protected $output;

    /**
     * @var File
     */
    protected $entryPoint;

    protected $compression = Phar::GZ;

    protected $regex = null;

    /**
     * @param File|string $output
     */
    public function setOutput(File|string $output) {
        if (!($output instanceof File)) {
            $output = $this->input->getFile($output);
        }
        $this->output = $output;
        return $this;
    }

    /**
     * @param Directory $input
     */
    public function setInput($input) {
        $this->input = $input;
        return $this;
    }

    /**
     * @param File $entryPoint
     */
    public function setEntryPoint($entryPoint) {
        $this->entryPoint = $entryPoint;
        return $this;
    }

    /**
     * @param null $regex
     */
    public function setRegex($regex) {
        $this->regex = $regex;
        return $this;
    }

    /**
     * @param int $compression
     */
    public function setCompression($compression) {
        $this->compression = $compression;
        return $this;
    }



    public function create() {
        logger()->info('start ...');
        $phar = new Phar((string)$this->output);
        $phar->buildFromDirectory((string)$this->input, $this->regex);
        $phar->setDefaultStub((string)$this->entryPoint);
        $phar->compress($this->compression);
        logger()->info('end! file: '. $this->output);
    }


    public function loadComposer() {
        $configs = $this->input->file('composer.json')->read();
        $configs = Json::decode($configs);
        $configs = $configs['extra']['phar-builder'];
        $this->setEntryPoint($configs['entry-point'])
            ->setOutput($configs['name']);
    }

}