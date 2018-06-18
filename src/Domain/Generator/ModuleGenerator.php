<?php
namespace Zodream\Module\Gzo\Domain\Generator;

use Zodream\Disk\Directory;
use Zodream\Disk\File;
use Zodream\Disk\FileObject;
use Zodream\Helpers\Arr;
use Zodream\Helpers\Str;
use Zodream\Infrastructure\Http\Request;
use Zodream\Service\Factory;

/**
 * 根据页面模板生成模块
 * @package Zodream\Module\Gzo\Domain\Generator
 */
class ModuleGenerator {

    protected $name;

    /**
     * @var Directory
     */
    protected $input;

    /**
     * @var Directory
     */
    protected $output;

    /**
     * @var array
     */
    protected $configs;

    /**
     * @param mixed $name
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @param Directory $input
     */
    public function setInput($input) {
        if (empty($this->name)) {
            $this->setName($input->getName());
        }
        if (empty($this->output)) {
            $this->setOutput($input);
        }
        $this->input = $input;
        return $this;
    }

    /**
     * @param Directory $output
     */
    public function setOutput($output) {
        $this->output = $output;
        return $this;
    }

    /**
     * @param array $configs
     */
    public function setConfigs($configs) {
        if (isset($configs['name']) && !empty($configs['name'])) {
            $this->setName($configs['name']);
        }
        if (isset($configs['output']) && !empty($configs['output'])) {
            $this->setInput(Factory::root()->directory($configs['output']));
        }
        if (isset($configs['input']) && !empty($configs['input'])) {
            $this->setInput(Factory::root()->directory((string)$configs['input']));
        }
        $this->configs = $configs;
        return $this;
    }

    public function create() {
        $this->debug('start ...');
        $module = Str::studly($this->name);
        $this->createFolder();
        $this->createModule();
        if (isset($this->configs['assets'])) {
            $this->createAssets();
        }
        if (isset($this->configs['tables'])) {
            $this->createTable();
        }
        if (isset($this->configs['views'])) {
            $root = $this->output->childDirectory('UserInterface');
            $root->addFile('layouts/main.php', self::renderTemplate('layout'));
            $this->createView($root, $this->getViewConfigs());
        }
        if (isset($this->configs['controllers'])) {
            $root = $this->output->childDirectory('Service');
            $root->addFile('Controller.php', self::renderTemplate('BaseController', array(
                'module' => $module
            )));
            $this->createController($root, null, $this->getControllerConfigs());
        }
        $this->debug('end ...');
    }

    protected function getControllerConfigs() {
        if (is_array($this->configs['controllers'])) {
            return $this->configs['controllers'];
        }
        if ($this->configs['controllers'] == '@views') {
            return $this->getByController($this->getViewConfigs());
        }
        return [];
    }

    protected function getViewConfigs() {
        if (is_array($this->configs['views'])) {
            return $this->configs['views'];
        }
        if ($this->configs['views'] == '@controllers') {
            return $this->getByController($this->configs['controllers']);
        }
        if (!is_string($this->configs['views'])) {
            return [];
        }
        $root = $this->getFolder($this->configs['views']);
        return $this->getByFolder($root);
    }

    protected function getByFolder(Directory $root) {
        $data = [];
        $root->map(function (FileObject $file) use ($data) {
            if (in_array($file->getName(), ['assets', 'js', 'css', 'image', 'images', 'img']))
            if (!$file instanceof File) {
                $data[$file->getName()] = $this->getByFolder($file);
                return;
            }
            if (!in_array($file->getExtension(), ['html', 'htm'])) {
                return;
            }
            $data[$file->getNameWithoutExtension()] = $file;
        });
        return $data;
    }

    protected function getByController($data) {
        $args = [];
        foreach ($data as $key => $item) {
            if (is_numeric($key)) {
                $key = $item;
            }
            $args[$key] = is_array($item) ? $this->getByController($item) : null;
        }
        return $args;
    }

    public function createFolder() {
        $data = [
            'Domain',
            'Domain/Migrations',
            'Domain/Model',
            'Service',
            'UserInterface',
            'UserInterface/assets',
            'UserInterface/layouts',
        ];
        foreach ($data as $name) {
            $this->output->addDirectory($name);
            $this->debug('mdir '.$name);
        }
    }

    public function createModule() {
        $name = Str::studly($this->name);
        $data = [
            'module' => $name,
        ];
        if (isset($this->configs['tables'])) {
            $data['migration'] = sprintf('Create%sTables', $name);
        }
        $this->output->addFile('Module.php', self::renderTemplate('Module', $data));
        $this->debug('new file Module.php');
    }

    public function createAssets() {
        foreach ($this->configs['assets'] as $key => $item) {
            $dist = $this->output->directory('UserInterface/assets/'.$key);
            $dist->create();
            $this->getFolder($item)->copy($dist);
            $this->debug('cp '.$item);
        }
    }

    public function getFolder($file) {
        if ($file instanceof Directory) {
            return $file;
        }
        if (strpos($file, '@input') === 0) {
            return $this->input->directory(substr($file, 6));
        }
        if (strpos($file, './') === 0) {
            return $this->input->directory(substr($file, 2));
        }
        return $this->input->directory($file);
    }

    public function getFile($file) {
        if ($file instanceof File) {
            return $file;
        }
        if (strpos($file, '@input') === 0) {
            return $this->input->file(substr($file, 6));
        }
        if (strpos($file, './') === 0) {
            return $this->input->file(substr($file, 2));
        }
        return $this->input->file($file);
    }

    public function createTable() {
        $data = [];
        $module = Str::studly($this->name);
        foreach ($this->configs['tables'] as $table => $fields) {
            if (is_numeric($table)) {
                list($table, $fields) = [$fields, []];
            }
            $name = Str::studly($table);
            list($fields, $rules, $labels) = $this->formatFields($fields);
            $data[] = [
                'name' => $name,
                'table' => $table,
                'fields' => $fields,
            ];
            $tpl = self::renderTemplate('Model', [
                'name' => $name,
                'table' => $table,
                'rules' => $rules,
                'pk' => [],
                'labels' => $labels,
                'property' => [],
                'module' => $module,
                'foreignKeys' => [],
                'is_module' => true
            ]);
            $file = sprintf('Domain/Model/%s%s.php', $name, APP_MODEL);
            $this->output->addFile($file, $tpl);
            $this->debug('new file '.$file);
        }
        $file = sprintf('Domain/Migrations/Create%sTables.php', $module);
        $this->output->addFile($file, self::renderTemplate('Migration', [
            'data' => $data,
            'module' => $module,
        ]));
        $this->debug('new file '.$file);
    }


    protected function createView(Directory $root, array $data) {
        foreach ($data as $key => $item) {
            if (is_array($item)) {
                $this->createView($root->addDirectory(Str::studly($key)), $item);
                continue;
            }
            if (is_numeric($key)) {
                list($key, $item) = [$item, null];
            }
            $name = strtolower(Str::studly($key));
            $file = $root->file($name.'.php');
            $file->write( self::renderTemplate('emptyIndex', $this->getViewContent($item)));
            $this->debug('new file '.$file);
        }
    }

    protected function getViewContent($name = null) {
        if (empty($name)) {
            return [];
        }
        $content = $this->getFile($name)->read();
        $data = [];
        if (preg_match('#<title>(.+?)</title>#i', $content, $match)) {
            $data['title'] = $match[1];
        }
        $scripts = [];
        $content = preg_replace_callback('#\<script[\s\S]+?(src="([^"]*)")?[\s\S]*?\>([\s\S]*?)\</script\>#i', function ($match) use (&$scripts) {
            if (!empty($match['2'])) {
                $scripts[] = sprintf('->registerJsFile(\'%s\')', $match[2]);
                return '';
            }
            $scripts[] = sprintf('->registerJs("%s")', htmlspecialchars($match[3]));
            return '';
        }, $content);
        $content = preg_replace_callback('#<style[\s\S]+?>([\s\S]+?)</style>#i', function ($match) use (&$scripts) {
            $scripts[] = sprintf('->registerCss("%s")', htmlspecialchars($match[1]));
            return '';
        }, $content);
        $content = preg_replace_callback('#<link[\s\S]+?href="(.+?)">#i', function ($match) use (&$scripts) {
            $scripts[] = sprintf('->registerCssFile(\'%s\')', $match[1]);
            return '';
        }, $content);
        if (!empty($scripts)) {
            $data['scripts'] = sprintf('$this%s;', implode(PHP_EOL, $scripts));
        }
        $content = preg_replace('#<[\s\S]+?<body>#i', '', $content);
        $content = preg_replace('#\</body\>[\s\S]+#i', '', $content);
        $content = preg_replace_callback('/(\<a[\s\S]+?href=)"([^#"\']+?)"/i', function ($match) {
            return sprintf('%s"%s"', $match[1], $this->getRealUri($match[2]));
        }, $content);
        $data['content'] = $content;
        return $data;
    }

    protected function getRealUri($uri) {
        if (strpos($uri, '//') !== false) {
            return $uri;
        }
        if (strpos($uri, 'javascript') === 0) {
            return $uri;
        }
        if (strpos($uri, './') === 0) {
            $uri = substr($uri, 2);
        }
        return sprintf('<?=$this->url(\'./%s\')?>', $this->getRelativeUri($uri));
    }

    protected function getRelativeUri($uri) {
        if (!is_array($this->configs['views'])) {
            return basename($uri, '.html');
        }
        return $this->getPathByLoop($uri, $this->configs['views']);
    }

    protected function getPathByLoop($uri, array $data) {
        foreach ($data as $key => $item) {
            if (is_array($item)){
                if (false !== ($path = $this->getPathByLoop($uri, $item))) {
                    return $key.'/'.$path;
                }
                continue;
            }
            if (strpos($item, $uri) !== false) {
                return $key;
            }
        }
        return false;
    }

    protected function createController(Directory $root, $name, $data) {
        $name = Str::studly($name);
        if (Arr::isMultidimensional($data)) {
            $root = $root->addDirectory($name);
            foreach ($data as $key => $item) {
                $this->createController($root, $key, $item);
            }
            return;
        }
        if (empty($name)) {
            $name = 'Home';
        }
        $func = [];
        foreach ($data as $key => $item) {
            if (is_numeric($key)) {
                list($key, $item) = [$item, null];
            }
            if (is_null($item)) {
                $item = 'return $this->show();';
            }
            $func[$key] = $item;
        }
        $file = $name.APP_CONTROLLER.'.php';
        $root->addFile($file, self::renderTemplate('EmptyController', [
            'module' => Str::studly($this->name),
            'name' => $name,
            'is_module' => true,
            'func' => $func
        ]));
        $this->debug('new file '.$file);
    }

    protected function formatFields($fields) {
        $data = $rules = $labels = [];
        foreach ($fields as $key => $item) {
            if (is_numeric($key)) {
                $key = $item;
                $item = ucwords(str_replace('_', ' ', $item));
            }
            $data[] = sprintf('$table->set(\'%s\')->varchar(35)->notNull()->comment(\'%s\')', $key, $item);
            $rules[$key] = '';
            $labels[$key] = $item;
        }
        return [$data, $rules, $labels];
    }


    /**
     * @param $name
     * @param array $data
     * @return string
     * @throws \Exception
     * @throws \Zodream\Disk\FileException
     */
    public static function renderTemplate($name, $data = []) {
        return Factory::view()
            ->render('Template/'.$name, $data);
    }

    protected function debug($content) {
        if (!Request::isCli()) {
            return;
        }
        echo $content,PHP_EOL;
    }

}