<?php



defined('MOODLE_INTERNAL') || die();


class string_xml_database_importer extends xml_database_importer {
    
    protected $data;

    
    public function __construct($data, moodle_database $mdb, $check_schema=true) {
        parent::__construct($mdb, $check_schema);
        $this->data = $data;
    }

    
    public function import_database() {
        $parser = $this->get_parser();
        if (!xml_parse($parser, $this->data, true)) {
            throw new dbtransfer_exception('malformedxmlexception');
        }
        xml_parser_free($parser);
    }
}
