<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/moodle_recordset.php');

class mssql_native_moodle_recordset extends moodle_recordset {

    protected $rsrc;
    protected $current;

    public function __construct($rsrc) {
        $this->rsrc  = $rsrc;
        $this->current = $this->fetch_next();
    }

    public function __destruct() {
        $this->close();
    }

    private function fetch_next() {
        if (!$this->rsrc) {
            return false;
        }
        if (!$row = mssql_fetch_assoc($this->rsrc)) {
            mssql_free_result($this->rsrc);
            $this->rsrc = null;
            return false;
        }

        $row = array_change_key_case($row, CASE_LOWER);
                foreach ($row as $k=>$v) {
            if (is_null($v)) {
                continue;
            }
            if (!is_string($v)) {
                $row[$k] = (string)$v;
            }
        }
        return $row;
    }

    public function current() {
        return (object)$this->current;
    }

    public function key() {
                if (!$this->current) {
            return false;
        }
        $key = reset($this->current);
        return $key;
    }

    public function next() {
        $this->current = $this->fetch_next();
    }

    public function valid() {
        return !empty($this->current);
    }

    public function close() {
        if ($this->rsrc) {
            mssql_free_result($this->rsrc);
            $this->rsrc  = null;
        }
        $this->current = null;
    }
}
