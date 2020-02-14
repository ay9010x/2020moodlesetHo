<?php



namespace core\log;

defined('MOODLE_INTERNAL') || die();


interface sql_reader extends reader {

    
    public function get_events_select($selectwhere, array $params, $sort, $limitfrom, $limitnum);

    
    public function get_events_select_count($selectwhere, array $params);

    
    public function get_events_select_iterator($selectwhere, array $params, $sort, $limitfrom, $limitnum);

    
    public function get_log_event($data);
}
