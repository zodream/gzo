<?php
defined('APP_DIR') or exit();
echo '<?php';
?>

/**
* 配置文件模板
* 
* @author Jason
*/

return [
<?php foreach ($data as $key => $item):?>
<?php if (!is_array($item)):?>
    '<?=$key?>' => '<?=$item?>',
<?php else:?>
    '<?=$key?>' => [
<?php foreach ($item as $k => $it):?>
<?php if (!is_array($it)):?>
        '<?=$k?>' => '<?=$it?>',
<?php else:?>
        '<?=$k?>' => [
<?php foreach ($it as $k1 => $it1):?>
                '<?=$k1?>' => <?=var_export($it1, true) ?>,
<?php endforeach;?>
        ],
<?php endif;?>
<?php endforeach;?>
    ],
<?php endif;?>
<?php endforeach;?>
];