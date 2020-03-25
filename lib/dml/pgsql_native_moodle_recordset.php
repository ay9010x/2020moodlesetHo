<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/moodle_recordset.php');


class pgsql_native_moodle_recordset extends moodle_recordset {

    protected $result;
    
    protected $current;
    protected $bytea_oid;
    protected $blobs = array();

    public function __construct($result, $bytea_oid) {
        $this->result    = $result;
        $this->bytea_oid = $bytea_oid;

                $numrows = pg_num_fields($result);
        for($i=0; $i<$numrows; $i++) {
            $type_oid = pg_field_type_oid($result, $i);
            if ($type_oid == $this->bytea_oid) {
                $this->blobs[] = pg_field_name($result, $i);
            }
        }

        $this->current = $this->fetch_next();
    }

    public function __destruct() {
        $this->close();
    }

    private function fetch_next() {
        if (!$this->result) {
            return false;
        }
        if (!$row = pg_fetch_assoc($this->result)) {
            pg_free_result($this->result);
            $this->result = null;
            return false;
        }

        if ($this->blobs) {
            foreach ($this->blobs as $blob) {
                $row[$blob] = $row[$blob] !== null ? pg_unescape_bytea($row[$blob]) : null;
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
        if ($this->result) {
            pg_free_result($this->result);
            $this->result  = null;
        }
        $this->current = null;
        $this->blobs   = null;
    }
}
