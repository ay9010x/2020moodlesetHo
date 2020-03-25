<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class search_indexed extends base {

    
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventsearchindexed');
    }

    
    public function get_description() {
        if (!empty($this->userid)) {
            return "The user with id '{$this->userid}' updated the search engine data";
        } else {
            return 'The search engine data has been updated';
        }
    }

    
    public function get_url() {
        return new \moodle_url('/report/search/index.php');
    }
}
