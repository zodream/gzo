<?php
namespace Zodream\Module\Gzo\Domain\Database;

use Zodream\Database\Schema\Table as BaseTable;
use Zodream\Disk\File;
use Zodream\Infrastructure\Support\Collection;
use Zodream\Module\Gzo\Domain\InformationSchemaModel;

class Table extends BaseTable {

    public function length() {
        return $this->_data['Data_length'];
    }

    public function maxLength() {
        return $this->_data['Max_data_length'];
    }

    public function rows() {
        return $this->_data['Rows'];
    }

    public function version() {
        return $this->_data['Version'];
    }

    public function collation() {
        return $this->_data['Collation'];
    }

    public function map(callable $func) {
        $data = InformationSchemaModel::column()->where(['TABLE_SCHEMA' => $this->schema->getSchema()])
        ->andWhere(['TABLE_NAME' => $this->getTableName()])->all();
        (new Collection($data))->each(function($item) use ($func) {
            $func((new Column($this, $item['COLUMN_NAME']))
                ->setData($item));
        });
    }

    /**
     * 导入csv数据
     * @param File|string $file
     */
    public function importCsv($file) {
        $this->command()->execute('
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
        $data = $this->getAllColumn();
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
}