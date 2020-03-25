<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/moodle_temptables.php');

class pgsql_native_moodle_temptables extends moodle_temptables {
    
    public function update_stats() {
        $temptables = $this->get_temptables();
        foreach ($temptables as $temptablename) {
            $this->mdb->execute("ANALYZE {".$temptablename."}");
        }
    }
}
