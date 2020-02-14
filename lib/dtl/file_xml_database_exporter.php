<?php



defined('MOODLE_INTERNAL') || die();


class file_xml_database_exporter extends xml_database_exporter {
    
    protected $filepath;
    
    protected $file;

    
    public function __construct($filepath, moodle_database $mdb, $check_schema=true) {
        parent::__construct($mdb, $check_schema);
        $this->filepath = $filepath;
    }

    
    protected function output($text) {
        fwrite($this->file, $text);
    }

    
    public function export_database($description=null) {
        global $CFG;
                $this->file = fopen($this->filepath, 'wb');
        parent::export_database($description);
        fclose($this->file);
        @chmod($this->filepath, $CFG->filepermissions);
    }
}
