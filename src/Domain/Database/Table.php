<?php
namespace Zodream\Module\Gzo\Domain\Database;

use Zodream\Database\Schema\Table as BaseTable;
use Zodream\Disk\File;
use Zodream\Infrastructure\Support\Collection;
use Zodream\Module\Gzo\Domain\InformationSchemaModel;

class Table extends BaseTable {

    /**
     * 总长度
     * @return integer
     */
    public function length() {
        return $this->_data['Data_length'];
    }

    /**
     * 平均每行的长度
     * @return integer
     */
    public function avgRowLength() {
        return $this->_data['Avg_row_length'];
    }

    public function maxLength() {
        return $this->_data['Max_data_length'];
    }

    /**
     * 空间碎片大小，可以进行碎片整理优化
     * @return integer
     */
    public function dataFree() {
        return $this->_data['Data_free'];
    }

    /**
     * 行数
     * @return integer
     */
    public function rows() {
        return $this->_data['Rows'];
    }

    public function version() {
        return $this->_data['Version'];
    }

    /**
     * 编码
     * @return string
     */
    public function collation() {
        return $this->_data['Collation'];
    }

    /**
     * @param callable $func
     * @throws \Exception
     */
    public function map(callable $func) {
        if (empty($this->schema)) {
            $this->schema = new Schema();
        }
        $data = InformationSchemaModel::column()
            ->where(['TABLE_SCHEMA' => $this->schema->getSchema()])
        ->where(['TABLE_NAME' => $this->getTableName()])->all();
        (new Collection($data))->each(function($item) use ($func) {
            $func((new Column($this, $item['COLUMN_NAME']))
                ->setData($item));
        });
    }

    /**
     * 导入csv数据
     * @param File|string $file
     * @throws \Exception
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

    /**
     * 获取表的完整信息
     * @return array|mixed
     * @throws \Exception
     */
    public function getStatus() {
        $sql = sprintf('SHOW TABLE STATUS WHERE `Name` = \'%s\'', $this->getTableName());
        $data = $this->command()
            ->getArray($sql);
        if (empty($data)) {
            return $data;
        }
        return reset($data);
    }
}