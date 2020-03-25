<?php





class phpunit_ArrayDataSet extends PHPUnit_Extensions_Database_DataSet_AbstractDataSet {
    
    protected $tables = array();

    
    public function __construct(array $data) {
        foreach ($data AS $tableName => $rows) {
            $firstrow = reset($rows);

            if (array_key_exists(0, $firstrow)) {
                                $columnsInFirstRow = true;
                $columns = $firstrow;
                $key = key($rows);
                unset($rows[$key]);
            } else {
                                $columnsInFirstRow = false;
                $columns = array_keys($firstrow);
            }

            $metaData = new PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData($tableName, $columns);
            $table = new PHPUnit_Extensions_Database_DataSet_DefaultTable($metaData);

            foreach ($rows AS $row) {
                if ($columnsInFirstRow) {
                    $row = array_combine($columns, $row);
                }
                $table->addRow($row);
            }
            $this->tables[$tableName] = $table;
        }
    }

    protected function createIterator($reverse = FALSE) {
        return new PHPUnit_Extensions_Database_DataSet_DefaultTableIterator($this->tables, $reverse);
    }

    public function getTable($tableName) {
        if (!isset($this->tables[$tableName])) {
            throw new InvalidArgumentException("$tableName is not a table in the current database.");
        }

        return $this->tables[$tableName];
    }
}
