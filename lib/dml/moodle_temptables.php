<?php



defined('MOODLE_INTERNAL') || die();

class moodle_temptables {

    
    protected $mdb;
    
    protected $prefix;
    
    protected $temptables;

    
    public function __construct($mdb) {
        $this->mdb        = $mdb;
        $this->prefix     = $mdb->get_prefix();
        $this->temptables = array();
    }

    
    public function add_temptable($tablename) {
                $this->temptables[$tablename] = $tablename;
    }

    
    public function delete_temptable($tablename) {
                unset($this->temptables[$tablename]);
    }

    
    public function get_temptables() {
        return $this->temptables;
    }

    
    public function is_temptable($tablename) {
        return !empty($this->temptables[$tablename]);
    }

    
    public function get_correct_name($tablename) {
        if ($this->is_temptable($tablename)) {
            return $this->temptables[$tablename];
        }
        return null;
    }

    
    public function update_stats() {
                    }

    
    public function dispose() {
                        if ($temptables = $this->get_temptables()) {
            error_log('Potential coding error - existing temptables found when disposing database. Must be dropped!');
            foreach ($temptables as $temptable) {
                 $this->mdb->get_manager()->drop_table(new xmldb_table($temptable));
            }
        }
        $this->mdb = null;
    }
}
