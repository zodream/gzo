<?php
defined('APP_DIR') or exit();
echo '<?php';
?>

defined('APP_DIR') or exit();
use Zodream\Template\View;
/** @var $this View */
$this->title = '<?=isset($title) ? $title : ''?>';
<?=isset($scripts) ? $scripts : ''?>

?>

<?=isset($content) ? $content : ''?>