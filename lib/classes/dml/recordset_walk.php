<?php



namespace core\dml;

defined('MOODLE_INTERNAL') || die();


class recordset_walk implements \Iterator {

    
    protected $recordset;

    
    protected $callback;

    
    protected $callbackextra;

    
    public function __construct(\moodle_recordset $recordset, callable $callback, $callbackextra = null) {
        $this->recordset = $recordset;
        $this->callback = $callback;
        $this->callbackextra = $callbackextra;
    }

    
    public function __destruct() {
        $this->close();
    }

    
    public function current() {

        if (!$this->recordset->valid()) {
            return false;
        }

        if (!$record = $this->recordset->current()) {
            return false;
        }

                if (!is_null($this->callbackextra)) {
            return call_user_func($this->callback, $record, $this->callbackextra);
        } else {
            return call_user_func($this->callback, $record);
        }
    }

    
    public function next() {
        return $this->recordset->next();
    }

    
    public function key() {
        return $this->recordset->key();
    }

    
    public function valid() {
        if (!$valid = $this->recordset->valid()) {
            $this->close();
        }
        return $valid;
    }

    
    public function rewind() {
                return;
    }

    
    public function close() {
        $this->recordset->close();
    }
}
