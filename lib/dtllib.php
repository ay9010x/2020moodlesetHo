<?php





defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/ddllib.php');
require_once($CFG->libdir.'/dtl/database_exporter.php');
require_once($CFG->libdir.'/dtl/xml_database_exporter.php');
require_once($CFG->libdir.'/dtl/file_xml_database_exporter.php');
require_once($CFG->libdir.'/dtl/string_xml_database_exporter.php');
require_once($CFG->libdir.'/dtl/database_mover.php');
require_once($CFG->libdir.'/dtl/database_importer.php');
require_once($CFG->libdir.'/dtl/xml_database_importer.php');
require_once($CFG->libdir.'/dtl/file_xml_database_importer.php');
require_once($CFG->libdir.'/dtl/string_xml_database_importer.php');


class dbtransfer_exception extends moodle_exception {
    
    function __construct($errorcode, $a=null, $link='', $debuginfo=null) {
        global $CFG;
        if (empty($link)) {
            $link = "$CFG->wwwroot/$CFG->admin/";
        }
        parent::__construct($errorcode, 'core_dbtransfer', $link, $a, $debuginfo);
    }
}

