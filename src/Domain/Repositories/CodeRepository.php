<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Domain\Repositories;

use Zodream\Helpers\Str;
use Zodream\Module\Gzo\Domain\Readers\MemoryWriter;

class CodeRepository {

    public static function exchange(string $content, string $source = 'php', string $target = 'c#'): MemoryWriter {
        $output = new MemoryWriter();
        $fuc = 'static::' . lcfirst(Str::studly(sprintf('%s_to_%s', self::formatLanguage($source), self::formatLanguage($target))));
        if (is_callable($fuc)) {
            $output->write($target, call_user_func($fuc, $content));
        } else {
            $output->write($source, $content);
        }
        return $output;
    }

    private static function formatLanguage(string $language): string {
        if (str_contains($language, '++')) {
            return str_replace('++', '_plus', $language);
        } elseif (str_contains($language, '#')) {
            return str_replace('#', '_sharp', $language);
        }
        return $language;
    }

    private static function phpToCSharp(string $content): string {
        $content = preg_replace_callback('/function([^\(]*)\(([^\)]*)\)([^\{]*)\{/', function ($match) {
            $parameters = '';
            if (!empty($match[2])) {
                $parameters = implode(',',
                    array_map(function ($item) {
                        $key = trim($item);
                        if ($key[0] === '$')
                        {
                            return 'object '.substr($key, 1);
                        }
                        return str_replace('$', '', $key);
                    }, explode(',', $match[2])));
            }
            if (empty($match[1])) {
                return sprintf('(%s) => {', $parameters);
            }
            $returnType = 'void';
            if (!empty($match[3])) {
                $returnType = trim(str_replace(':', '', $match[3]));
            }
            $func = static::studly($match[1]);
            if ($returnType === 'void' && ($func === 'Up' || $func === 'Seed'))
            {
                // Migration 实现继承
                $returnType = 'override '. $returnType;
            }
            return sprintf('%s %s(%s) {', $returnType, $func, $parameters);
        }, $content);
        $content = preg_replace_callback('/(->|::)([^\(\)\s]+)/', function ($match) {
            return '.'.$match[2];
        }, $content);

        $content = str_replace(
            ['$this.', '\'', '$', '""', 'RoleRepository.NewPermission', 'RoleRepository.NewRole',
                'Option.Group', 'Model.CREATED_AT', '"created_at"', '"updated_at"'],
            ['', '"', '', 'string.Empty', 'privilege.AddPermission', 'privilege.AddRole',
                'option.AddGroup', 'MigrationTable.COLUMN_CREATED_AT',
                'MigrationTable.COLUMN_CREATED_AT', 'MigrationTable.COLUMN_UPDATED_AT'],
            $content);
        $content = preg_replace_callback('/class\s+(\w+)([^\{]*)/', function ($match) use($content) {
            $name = static::studly($match[1]);
            $impl = trim($match[2]);
            if (empty($impl))
            {
                return 'class '. $name;
            }
            $isFirst = true;
            if (str_contains($impl, 'extends'))
            {
                $isFirst = false;
                str_replace('extends', ':', $impl);
            }
            if (str_contains($impl, 'implements'))
            {
                str_replace('implements', $isFirst ? ':' : ',', $impl);
            }
            if (str_contains($impl, 'Migration'))
            {
                $service = 'IDatabase db';
                if (str_contains($content, 'privilege.'))
                {
                    $service .= ', IPrivilegeManager privilege';
                }
                if (str_contains($content, 'option.'))
                {
                    $service .= ', IGlobeOption option';
                }
                return sprintf('class %s(%s) : Migration(db)', $name, $service);
            }
            return sprintf('class %s %s', $name, $impl);
        }, $content);
        $content = preg_replace_callback('/Append\((\w+)\.TableName\(\),.+?\{/', function ($match) {
            return sprintf('Append<%s>(table => {',
                Str::lastReplace($match[1], 'Model', 'Entity'));
        }, $content);
        $content = preg_replace_callback('/const\s+([^;]+);/', function ($match) {
            return sprintf('public const %s %s;', str_contains($match[1], '"') ? 'string' : 'int', $match[1]);
        }, $content);
        return $content;
    }

    private static function studly(string $value): string {
        $value = trim($value);
        if (empty($value)) {
            return $value;
        }
        if (!str_contains($value, '_')) {
            return ucfirst($value);
        }
        if (strtoupper($value) === $value) {
            return $value;
        }
        return Str::studly($value);
    }
}