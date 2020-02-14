<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/moodle_recordset.php');


class mysqli_native_moodle_recordset extends moodle_recordset {

    protected $result;
    protected $current;

    public function __construct($result) {
        $this->result  = $result;
        $this->current = $this->fetch_next();
    }

    public function __destruct() {
        $this->close();
    }

    private function fetch_next() {
        if (!$this->result) {
            return false;
        }
        if (!$row = $this->result->fetch_assoc()) {
            $this->result->close();
            $this->result = null;
            return false;
        }

        $row = array_change_key_case($row, CASE_LOWER);
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
        if ($this->result) {
            $this->result->close();
            $this->result  = null;
        }
        $this->current = null;
    }
}
