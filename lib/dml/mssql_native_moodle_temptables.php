<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/moodle_temptables.php');

class mssql_native_moodle_temptables extends moodle_temptables {

    
    public function add_temptable($tablename) {
                $this->temptables[$tablename] = '#' . $this->prefix . $tablename;
    }
}
