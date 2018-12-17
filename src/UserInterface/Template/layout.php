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
    <meta name="keywords" content="zodream,梦想开源" />
    <title><?='<?='?>$this->title?>-ZoDream 梦想开源</title>
    <?='<?='?>$this->header()?>
</head>
<body>

    <?='<?='?>$content?>

<?='<?='?>$this->footer()?>
</body>
</html>