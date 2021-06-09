<?php
namespace Zodream\Module\Gzo\Domain\Database;

use Zodream\Database\Contracts\SqlBuilder;
use Zodream\Database\DB;
use Zodream\Database\Schema\Table as BaseTable;
use Zodream\Disk\File;
use Zodream\Infrastructure\Support\Collection;
use Zodream\Module\Gzo\Domain\InformationSchemaModel;

class Table extends BaseTable {

    protected array $data = [];

    public function getName(): string
    {
        if (empty($this->schema)) {
            return parent::getName();
        }
        return sprintf('`%s`.`%s`', $this->schema->getName(), $this->name);
    }

    public function setData(array $data) {
        $this->data = $data;
        return $this;
    }

    /**
     * 总长度
     * @return integer
     */
    public function length() {
        return $this->data['Data_length'];
    }

    /**
     * 平均每行的长度
     * @return integer
     */
    public function avgRowLength() {
        return $this->data['Avg_row_length'];
    }

    public function maxLength() {
        return $this->data['Max_data_length'];
    }

    /**
     * 空间碎片大小，可以进行碎片整理优化
     * @return integer
     */
    public function dataFree() {
        return $this->data['Data_free'];
    }

    /**
     * 行数
     * @return integer
     */
    public function rows() {
        return $this->data['Rows'];
    }

    public function version() {
        return $this->data['Version'];
    }

    /**
     * 编码
     * @return string
     */
    public function getCollation(): string {
        return $this->data['Collation'];
    }

    /**
     * @param callable $func
     * @throws \Exception
     */
    public function map(callable $func) {
        if (empty($this->schema)) {
            $this->schema = new Schema();
        }
        $data = InformationSchemaModel::columns()
            ->where(['TABLE_SCHEMA' => $this->schema->getName()])
        ->where(['TABLE_NAME' => $this->getName()])->all();
        (new Collection($data))->each(function($item) use ($func) {
            $func((new Column($item['COLUMN_NAME']))
                ->setTable($this)
                ->setData($item));
        });
    }

    /**
     * 导入csv数据
     * @param File|string $file
     * @throws \Exception
     */
    public function importCsv(string|File $file) {
        Db::db()->execute('
            LOAD DATA LOCAL INFILE "'.(string)$file.'"
            INTO TABLE '.$this->getName().'
            FIELDS TERMINATED by \',\'
            LINES TERMINATED BY \'\n\'
        ');
    }

    /**
     * 获取列是不是数值
     * @return array
     */
    public function getFieldsType() {
        $data = DB::information()->columnList($this);
        $args = [];
        foreach ($data as $field) {
            $args[$field['Field']] = $this->isNumeric($field['Type']);
        }
        return $args;
    }

    protected function isNumeric($type) {
        $type = strtoupper(trim(explode('(', $type)[0]));
        return in_array($type, [
            'SMALLINT', 'BIGINT', 'FLOAT',
            'DOUBLE', 'DECIMAL', 'INT', 'TINYINT']);
    }

    /**
     * 获取表的完整信息
     * @return array|mixed
     * @throws \Exception
     */
    public function getStatus() {
        return DB::db()->first(DB::schemaGrammar()->compileTableQuery($this));
    }

    /**
     * @return SqlBuilder
     */
    public function query() {
        return DB::table($this);
    }
}