<?php
namespace Zodream\Module\Gzo\Service;

use Zodream\Module\Gzo\Domain\Generator\ClassGenerator;
use Zodream\Module\Gzo\Domain\Generator\TestGenerator;

class TestController extends Controller {

    public function indexAction() {
        return $this->show('index');
    }

    /**
     * 单个文件生成
     * @param $escapedClassName
     * @param $escapedSourcePath
     * @param $generatedFilePath
     */
    public function fileAction($escapedClassName,
                               $escapedSourcePath,
                               $generatedFilePath) {
        $generator = new ClassGenerator($escapedClassName,
            $escapedSourcePath,
            $escapedClassName . 'Test', $generatedFilePath);
        $generator->write();
        return $this->show();
    }

    /**
     * 目录生成
     * @param $source
     * @param $target
     */
    public function projectAction($source, $target) {
        $sourceDir      = realpath($source);
        $targetDir      = realpath($target);
        $dirIterator    = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir)
        );
        $sourcePathStrLength    = strlen($sourceDir);
        foreach ($dirIterator as $filePath => $fileInfo) {
            // @var SplFileInfo $fileInfo
            if (
                $fileInfo->isDir() ||
                substr($filePath, -13) === 'Interface.php'
            ) {
                continue;
            }
            $targetFilePath = $targetDir . preg_replace(
                    '/\.php$/i',
                    'Test.php',
                    substr($filePath, $sourcePathStrLength)
                );
            if (file_exists($targetFilePath)) {
                logger()->info('Skip:     Test file for \'$filePath\' already exists');
                continue;
            }
            $fullClassName = $this->extractFullClassNameFromFile($filePath);
            if ($fullClassName === false) {
                logger()->debug("Class name could not be extracted from '$filePath'");
                continue;
            }
            $targetFileName = basename($targetFilePath);
            $sourceDirPath = dirname($filePath);
            $targetDirPath = dirname($targetFilePath);
            $generatedFilePath = "$sourceDirPath/$targetFileName";
            if (!is_dir($targetDirPath)) {
                mkdir($targetDirPath, 0777, true);
            }
            $escapedClassName = $fullClassName;
            $escapedSourcePath = $filePath;
            $generator = new TestGenerator($escapedClassName,
                $escapedSourcePath,
                $escapedClassName . 'Test', $generatedFilePath);
            $generator->write();
            if (!file_exists($generatedFilePath)) {
                logger()->debug("Failed to generate test file for '$fullClassName'");
                continue;
            }
            rename($generatedFilePath, $targetFilePath);
            logger()->info("Test file successfully created for '$fullClassName'");
        }
        // 完成
        return $this->showContent('Done!');
    }

    protected function extractFullClassNameFromFile($filePath) {
        if (!file_exists($filePath)) {
            return false;
        }
        $namespace  = null;
        $classname  = null;
        $cnMatches  = null;
        $nsMatches  = null;
        $file       = file_get_contents($filePath);
        if (preg_match_all('/\n\s*(abstract\s|final\s)*class\s+(?<name>[^\s;]+)\s*/i', $file, $cnMatches, PREG_PATTERN_ORDER)) {
            $classname  = array_pop($cnMatches['name']);
            if (preg_match_all('/namespace\s+(?<name>[^\s;]+)\s*;/i', $file, $nsMatches, PREG_PATTERN_ORDER)) {
                $namespace  = array_pop($nsMatches['name']);
            }
        }
        if (empty($classname)) {
            return false;
        }
        return "$namespace\\$classname";
    }
}