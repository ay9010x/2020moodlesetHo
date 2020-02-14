<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/moodle_recordset.php');


class pdo_moodle_recordset extends moodle_recordset {

    private $sth;
    protected $current;

    public function __construct($sth) {
        $this->sth = $sth;
        $this->sth->setFetchMode(PDO::FETCH_ASSOC);
        $this->current = $this->fetch_next();
    }

    public function __destruct() {
        $this->close();
    }

    private function fetch_next() {
        if ($row = $this->sth->fetch()) {
            $row = array_change_key_case($row, CASE_LOWER);
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
        if ($this->sth) {
            $this->sth->closeCursor();
            $this->sth = null;
        }
        $this->current = null;
    }
}
