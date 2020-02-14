<?php



defined('MOODLE_INTERNAL') || die();


class string_xml_database_exporter extends xml_database_exporter {
    
    protected $data;

    
    protected function output($text) {
        $this->data .= $text;
    }

    
    public function get_output() {
        return $this->data;
    }

    
    public function export_database($description=null) {
        $this->data = '';
        parent::export_database($description);
    }
}
