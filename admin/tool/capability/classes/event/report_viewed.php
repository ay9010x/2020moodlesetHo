<?php


namespace tool_capability\event;


class report_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->context = \context_system::instance();
    }

    
    public static function get_name() {
        return get_string('eventreportviewed', 'tool_capability');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the capability overview report.";
    }

    
    protected function get_legacy_logdata() {
        return array(SITEID, 'admin', 'tool capability', 'tool/capability/index.php');
    }

    
    public function get_url() {
        return new \moodle_url('/admin/tool/capability/index.php');
    }
}

