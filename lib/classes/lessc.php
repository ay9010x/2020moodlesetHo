<?php



defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/lessphp/Autoloader.php');
Less_Autoloader::register();


class core_lessc extends Less_Parser {

    
    public function parse_file_content($filepath) {
        $this->parse(file_get_contents($filepath));
    }

}
