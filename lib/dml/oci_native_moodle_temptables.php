<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/moodle_temptables.php');

class oci_native_moodle_temptables extends moodle_temptables {

    
    protected $unique_session_id;     
    protected $counter;

    
    public function __construct($mdb, $unique_session_id) {
        $this->unique_session_id = $unique_session_id;
        $this->counter = 1;
        parent::__construct($mdb);
    }

    
    public function add_temptable($tablename) {
                $this->temptables[$tablename] = $this->prefix . $this->unique_session_id . $this->counter;
        $this->counter++;
    }
}
