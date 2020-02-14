<?php





defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/xmlize.php');

require_once($CFG->libdir.'/xmldb/xmldb_constants.php');

require_once($CFG->libdir.'/xmldb/xmldb_object.php');
require_once($CFG->libdir.'/xmldb/xmldb_file.php');
require_once($CFG->libdir.'/xmldb/xmldb_structure.php');
require_once($CFG->libdir.'/xmldb/xmldb_table.php');
require_once($CFG->libdir.'/xmldb/xmldb_field.php');
require_once($CFG->libdir.'/xmldb/xmldb_key.php');
require_once($CFG->libdir.'/xmldb/xmldb_index.php');

require_once($CFG->libdir.'/ddl/sql_generator.php');
require_once($CFG->libdir.'/ddl/database_manager.php');




class ddl_exception extends moodle_exception {
    
    function __construct($errorcode, $a=NULL, $debuginfo=null) {
        parent::__construct($errorcode, '', '', $a, $debuginfo);
    }
}


class ddl_table_missing_exception extends ddl_exception {
    
    function __construct($tablename, $debuginfo=null) {
        parent::__construct('ddltablenotexist', $tablename, $debuginfo);
    }
}


class ddl_field_missing_exception extends ddl_exception {
    
    function __construct($fieldname, $tablename, $debuginfo=null) {
        $a = new stdClass();
        $a->fieldname = $fieldname;
        $a->tablename = $tablename;
        parent::__construct('ddlfieldnotexist', $a, $debuginfo);
    }
}


class ddl_change_structure_exception extends ddl_exception {
    
    public $error;
    public $sql;
    
    function __construct($error, $sql=null) {
        $this->error = $error;
        $this->sql   = $sql;
        $errorinfo   = $error."\n".$sql;
        parent::__construct('ddlexecuteerror', NULL, $errorinfo);
    }
}


class ddl_dependency_exception extends ddl_exception {

    function __construct($targettype, $targetname, $offendingtype, $offendingname, $debuginfo=null) {
        $a = new stdClass();
        $a->targettype = $targettype;
        $a->targetname = $targetname;
        $a->offendingtype = $offendingtype;
        $a->offendingname = $offendingname;

        parent::__construct('ddldependencyerror', $a, $debuginfo);
    }
}
