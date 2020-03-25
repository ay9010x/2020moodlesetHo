<?php



namespace gradereport_singleview\local\ui;

defined('MOODLE_INTERNAL') || die;


class empty_element extends element {

    
    public function __construct($msg = null) {
        if (is_null($msg)) {
            $this->text = '&nbsp;';
        } else {
            $this->text = $msg;
        }
    }

    
    public function html() {
        return $this->text;
    }
}
