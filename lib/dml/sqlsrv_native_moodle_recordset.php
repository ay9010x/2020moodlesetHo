<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/moodle_recordset.php');

class sqlsrv_native_moodle_recordset extends moodle_recordset {

    protected $rsrc;
    protected $current;

    
    protected $buffer = null;

    
    protected $db;

    public function __construct($rsrc, sqlsrv_native_moodle_database $db) {
        $this->rsrc    = $rsrc;
        $this->current = $this->fetch_next();
        $this->db      = $db;
    }

    
    public function transaction_starts() {
        if ($this->buffer !== null) {
            $this->unregister();
            return;
        }
        if (!$this->rsrc) {
            $this->unregister();
            return;
        }
                raise_memory_limit('2G');
        $this->buffer = array();

        while($next = $this->fetch_next()) {
            $this->buffer[] = $next;
        }
    }

    
    private function unregister() {
        if ($this->db) {
            $this->db->recordset_closed($this);
            $this->db = null;
        }
    }

    public function __destruct() {
        $this->close();
    }

    private function fetch_next() {
        if (!$this->rsrc) {
            return false;
        }
        if (!$row = sqlsrv_fetch_array($this->rsrc, SQLSRV_FETCH_ASSOC)) {
            sqlsrv_free_stmt($this->rsrc);
            $this->rsrc = null;
            $this->unregister();
            return false;
        }

        unset($row['sqlsrvrownumber']);
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
        if ($this->buffer === null) {
            $this->current = $this->fetch_next();
        } else {
            $this->current = array_shift($this->buffer);
        }
    }

    public function valid() {
        return !empty($this->current);
    }

    public function close() {
        if ($this->rsrc) {
            sqlsrv_free_stmt($this->rsrc);
            $this->rsrc  = null;
        }
        $this->current = null;
        $this->buffer  = null;
        $this->unregister();
    }
}
