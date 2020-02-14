<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/moodle_recordset.php');

class oci_native_moodle_recordset extends moodle_recordset {

    protected $stmt;
    protected $current;

    public function __construct($stmt) {
        $this->stmt  = $stmt;
        $this->current = $this->fetch_next();
    }

    public function __destruct() {
        $this->close();
    }

    private function fetch_next() {
        if (!$this->stmt) {
            return false;
        }
        if (!$row = oci_fetch_array($this->stmt, OCI_ASSOC + OCI_RETURN_NULLS + OCI_RETURN_LOBS)) {
            oci_free_statement($this->stmt);
            $this->stmt = null;
            return false;
        }

        $row = array_change_key_case($row, CASE_LOWER);
        unset($row['oracle_rownum']);
        array_walk($row, array('oci_native_moodle_database', 'onespace2empty'));
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
        if ($this->stmt) {
            oci_free_statement($this->stmt);
            $this->stmt  = null;
        }
        $this->current = null;
    }
}
