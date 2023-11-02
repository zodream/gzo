<?php
defined('APP_DIR') or exit();
echo '<?php';
?>

defined('APP_DIR') or exit();
use Zodream\Template\View;
/** @var $this View */
?>
<!DOCTYPE html>
<html lang="<?='<?='?>$this->get('language', 'zh-CN')?>">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?='<?='?>$this->text($this->title)?>-<?='<?='?>__('site title')?></title>
    <meta name="Keywords" content="<?='<?='?>$this->text($this->get('keywords'))?>" />
    <meta name="Description" content="<?='<?='?>$this->text($this->get('description'))?>" />
    <?='<?='?>$this->header()?>
</head>
<body>

    <?='<?='?>$this->contents()?>

<?='<?='?>$this->footer()?>
</body>
</html>