<?php



namespace core\log;

defined('MOODLE_INTERNAL') || die();


interface sql_internal_table_reader extends sql_reader {

    
    public function get_internal_log_table_name();
}
