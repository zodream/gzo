<?php
namespace Zodream\Module\Gzo\Domain;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/3/17
 * Time: 22:49
 */
/**
SHOW DATABASES                                //列出 MySQL Server 数据库。
SHOW TABLES [FROM db_name]                    //列出数据库数据表。
SHOW CREATE TABLES tbl_name                    //导出数据表结构。
SHOW TABLE STATUS [FROM db_name]              //列出数据表及表状态信息。
SHOW COLUMNS FROM tbl_name [FROM db_name]     //列出资料表字段
SHOW FIELDS FROM tbl_name [FROM db_name]，DESCRIBE tbl_name [col_name]。
SHOW FULL COLUMNS FROM tbl_name [FROM db_name]//列出字段及详情
SHOW FULL FIELDS FROM tbl_name [FROM db_name] //列出字段完整属性
SHOW INDEX FROM tbl_name [FROM db_name]       //列出表索引。
SHOW STATUS                                  //列出 DB Server 状态。
SHOW VARIABLES                               //列出 MySQL 系统环境变量。
SHOW PROCESSLIST                             //列出执行命令。
SHOW GRANTS FOR user                         //列出某用户权限
 */
use Zodream\Database\Model\Model;
use Zodream\Module\Gzo\Domain\Database\Schema;

class GenerateModel extends Model {

    public static function schema($name = null) {
        return new Schema($name);
    }

    public static function getValidate($value) {
        $result = '';
        if ($value['Null'] == 'NO' && is_null($value['Default'])) {
            $result = 'required';
        }
        if ($value['Type'] == 'text') {
            return $result;
        }

        if(!preg_match('#(.+?)\(([0-9]+)\)#', $value['Type'], $match)) {
            return $result;
        }
        $ext = !empty($match[2]) ? ':0,'.$match[2] : '';
        if (!empty($match[2]) && in_array($match[1], ['int', 'tinyint', 'smallint'])) {
            $ext = ':0,'.(pow(10, $match[2]) - 1);
        }
        switch ($match[1]) {
            case 'int':
                $result .= '|int';
                break;
            case 'tinyint':
            case 'smallint':
                $result .= '|int'.$ext;
                break;
            case 'char':
            case 'varchar':
            default:
                $result .= '|string'.$ext;
                break;
        }
        return trim($result, '|');
    }

    /**
     * 数据模型中的列生成
     * @param array $columns
     * @return array
     */
    public static function getFill(array $columns) {
        $pk = false;
        $rules = $labels = $property = [];
        foreach ($columns as $key => $value) {
            $labels[$value['Field']] = ucwords(str_replace('_', ' ', $value['Field']));
            $property[$value['Field']] = static::converterType($value['Type']);
            if ($value['Key'] == 'PRI'
//                || $value['Key'] == 'UNI'
            ) {
                $pk = $value['Field'];
            }
            if ($value['Extra'] === 'auto_increment') {
                continue;
            }
            $rules[$value['Field']] = static::getValidate($value);
        }
        return [
            $pk,
            $rules,
            $labels,
            $property
        ];
    }

    public static function getFields(array $columns) {
        $data = [];
        foreach ($columns as $value) {
            $item = self::parseFieldType($value);
            if (is_null($value['Default'])) {
                $item .= sprintf('->defaultVal(\'%s\')', $value['Default']);
            } elseif ($value['Null'] == 'NO') {
                $item .= '->notNull()';
            }
            if (!empty($value['COLUMN_COMMENT']) && $value['COLUMN_COMMENT'] != '') {
                $item .= sprintf('->comment(\'%s\')', $value['COLUMN_COMMENT']);
            }
            $data[] = $item;
        }
        return $data;
    }

    protected static function parseFieldType($field) {
        if ($field['Field'] == 'id') {
            return '$table->set(\'id\')->pk()->ai()';
        }
        if (in_array($field['Field'], ['updated_at', 'created_at', 'deleted_at'])) {
            return sprintf('$table->timestamp(\'%s\')', $field['Field']);
        }
        $type = strtolower($field['Type']);
        if (strpos($type, '(') === false) {
            $type .= '()';
        }
        return sprintf('$table->set(\'%s\')->%s', $field['Field'], $type);
    }

    protected static function converterType($type) {
        $type = explode('(', $type)[0];
        switch (strtoupper(trim($type))) {
            case 'INT':
            case 'BOOL':
            case 'TINYINT':
            case 'SMALLINT':
            case 'REAL':
            case 'MEDIUMINT':
            case 'BIGINT':
                return 'integer';
            case 'DOUBLE':
                return 'double';
            case 'FLOAT':
            case 'DECIMAL':
                return 'float';
            default:
                return 'string';
        }
    }
}