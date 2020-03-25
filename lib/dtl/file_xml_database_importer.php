<?php



defined('MOODLE_INTERNAL') || die();


class file_xml_database_importer extends xml_database_importer {
    
    protected $filepath;

    
    public function __construct($filepath, moodle_database $mdb, $check_schema=true) {
        $this->filepath = $filepath;
        parent::__construct($mdb, $check_schema);
    }

    
    public function import_database() {
        $file = fopen($this->filepath, 'r');
        $parser = $this->get_parser();
        while ($data = fread($file, 65536)) {
            if (!xml_parse($parser, $data, feof($file))) {
                throw new dbtransfer_exception('malformedxmlexception');
            }
        }
        xml_parser_free($parser);
        fclose($file);
    }
}
