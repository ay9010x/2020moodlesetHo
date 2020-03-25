<?php



defined('MOODLE_INTERNAL') || die();

class gradereport_singleview_screen_testable extends \gradereport_singleview\local\screen\screen {

    
    public function test_load_users() {
        return $this->load_users();
    }

    
    public function init($selfitemisempty = false) {}

    
    public function item_type() {}

    
    public function html() {}
}
