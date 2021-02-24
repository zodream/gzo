<?php
declare(strict_types=1);
namespace Zodream\Module\Gzo\Domain\Database;

use Zodream\Database\Schema\Column as BaseColumn;

class Column extends BaseColumn {
    protected array $data = [];

    public function setData(array $data) {
        $this->data = $data;
        return $this;
    }

    public function type() {
        return $this->data['DATA_TYPE'];
    }

    public function length() {
        return $this->data['NUMERIC_PRECISION'];
    }

    public function maxLength() {
        return $this->data['CHARACTER_MAXIMUM_LENGTH'];
    }

    public function getDefault() {
        return $this->data['COLUMN_DEFAULT'];
    }

    public function isPK() {
        return $this->data['COLUMN_KEY'] == 'PRI';
    }

    public function canNull() {
        return $this->data['IS_NULLABLE'] != 'NO';
    }

    public function isAuto() {
        return $this->data['EXTRA'] == 'auto_increment';
    }

    public function getComment(): string {
        return $this->data['COLUMN_COMMENT'];
    }
}