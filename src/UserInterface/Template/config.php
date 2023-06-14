<?php
defined('APP_DIR') or exit();
use Zodream\Helpers\Arr;
echo '<?php';
if (!function_exists('valueToStr')) {
    function valueToStr(mixed $data, int $level = 0, bool $firstNeed = false): string {
        if (!is_array($data)) {
            return sprintf('%s%s', $firstNeed ? str_repeat('    ', $level) : '', var_export($data, true));
        }
        $isAssoc = Arr::isAssoc($data);
        $items = [
            sprintf('%s[', $firstNeed ? str_repeat('    ', $level) : '')
        ];
        foreach ($data as $k => $item) {
            $items[] = sprintf('%s%s,', str_repeat('    ', $level + 1), $isAssoc ? sprintf('%s => %s', var_export($k, true),
                valueToStr($item, $level + 1)) : valueToStr($item, $level + 1));
        }
        $items[] = sprintf('%s]', str_repeat('    ', $level));
        return implode("\r\n", $items);
    }
}
?>

/**
* 配置文件模板
* 
* @author Jason
*/

return <?= valueToStr($data) ?>;