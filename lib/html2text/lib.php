<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/html2text/Html2Text.php');
require_once(__DIR__ . '/override.php');


class core_html2text extends \Html2Text\Html2Text {

    
    function __construct($html = '', $options = array()) {
                parent::__construct($html, $options);

                $this->entSearch[] = '/[ ]+([\n\t])/';
        $this->entReplace[] = '\\1';
    }

    
    protected function strtoupper($str) {
        return core_text::strtoupper($str);
    }
}
