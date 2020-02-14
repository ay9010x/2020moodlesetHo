<?php



defined('MOODLE_INTERNAL') || die();


class moodle_transaction {
    
    private $start_backtrace;
    
    private $database = null;

    
    public function __construct($database) {
        $this->database = $database;
        $this->start_backtrace = debug_backtrace();
        array_shift($this->start_backtrace);
    }

    
    public function get_backtrace() {
        return $this->start_backtrace;
    }

    
    public function is_disposed() {
        return empty($this->database);
    }

    
    public function dispose() {
        return $this->database = null;
    }

    
    public function allow_commit() {
        if ($this->is_disposed()) {
            throw new dml_transaction_exception('Transactions already disposed', $this);
        }
        $this->database->commit_delegated_transaction($this);
    }

    
    public function rollback($e) {
        if ($this->is_disposed()) {
            throw new dml_transaction_exception('Transactions already disposed', $this);
        }
        $this->database->rollback_delegated_transaction($this, $e);
    }
}
