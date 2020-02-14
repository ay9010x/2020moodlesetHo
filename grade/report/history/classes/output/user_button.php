<?php



namespace gradereport_history\output;

defined('MOODLE_INTERNAL') || die;


class user_button extends \single_button implements \renderable {
    
    public function __construct(\moodle_url $url, $label, $method = 'post') {
        parent::__construct($url, $label, $method);
        $this->class = 'singlebutton selectusersbutton gradereport_history_plugin';
        $this->formid = \html_writer::random_id('selectusersbutton');
    }
}
