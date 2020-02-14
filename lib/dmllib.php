<?php





defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/dml/moodle_database.php');


define('IGNORE_MISSING', 0);

define('IGNORE_MULTIPLE', 1);

define('MUST_EXIST', 2);


class dml_exception extends moodle_exception {
    
    function __construct($errorcode, $a=NULL, $debuginfo=null) {
        parent::__construct($errorcode, '', '', $a, $debuginfo);
    }
}


class dml_connection_exception extends dml_exception {
    
    function __construct($error) {
        $errorinfo = $error;
        parent::__construct('dbconnectionfailed', NULL, $errorinfo);
    }
}


class dml_sessionwait_exception extends dml_exception {
    
    function __construct() {
        parent::__construct('sessionwaiterr');
    }
}


class dml_read_exception extends dml_exception {
    
    public $error;
    
    public $sql;
    
    public $params;

    
    function __construct($error, $sql=null, array $params=null) {
        $this->error  = $error;
        $this->sql    = $sql;
        $this->params = $params;
        $errorinfo = $error."\n".$sql."\n[".var_export($params, true).']';
        parent::__construct('dmlreadexception', NULL, $errorinfo);
    }
}


class dml_multiple_records_exception extends dml_exception {
    
    public $sql;
    
    public $params;

    
    function __construct($sql='', array $params=null) {
        $errorinfo = $sql."\n[".var_export($params, true).']';
        parent::__construct('multiplerecordsfound', null, $errorinfo);
    }
}


class dml_missing_record_exception extends dml_exception {
    
    public $table;
    
    public $sql;
    
    public $params;

    
    function __construct($tablename, $sql='', array $params=null) {
        if (empty($tablename)) {
            $tablename = null;
        }
        $this->tablename = $tablename;
        $this->sql       = $sql;
        $this->params    = $params;

        switch ($tablename) {
            case null:
                $errcode = 'invalidrecordunknown';
                break;
            case 'course':
                $errcode = empty($sql) ? 'invalidcourseid' : 'invalidrecord';
                break;
            case 'course_modules':
                $errcode = 'invalidcoursemodule';
                break;
            case 'user':
                $errcode = 'invaliduser';
                break;
            default:
                $errcode = 'invalidrecord';
                break;
        }
        $errorinfo = $sql."\n[".var_export($params, true).']';
        parent::__construct($errcode, $tablename, $errorinfo);
    }
}


class dml_write_exception extends dml_exception {
    
    public $error;
    
    public $sql;
    
    public $params;

    
    function __construct($error, $sql=null, array $params=null) {
        $this->error  = $error;
        $this->sql    = $sql;
        $this->params = $params;
        $errorinfo = $error."\n".$sql."\n[".var_export($params, true).']';
        parent::__construct('dmlwriteexception', NULL, $errorinfo);
    }
}


class dml_transaction_exception extends dml_exception {
    
    public $transaction;

    
    function __construct($debuginfo=null, $transaction=null) {
        $this->transaction = $transaction;         parent::__construct('dmltransactionexception', NULL, $debuginfo);
    }
}


function setup_DB() {
    global $CFG, $DB;

    if (isset($DB)) {
        return;
    }

    if (!isset($CFG->dbuser)) {
        $CFG->dbuser = '';
    }

    if (!isset($CFG->dbpass)) {
        $CFG->dbpass = '';
    }

    if (!isset($CFG->dbname)) {
        $CFG->dbname = '';
    }

    if (!isset($CFG->dblibrary)) {
        $CFG->dblibrary = 'native';
                switch ($CFG->dbtype) {
            case 'postgres7' :
                $CFG->dbtype = 'pgsql';
                break;

            case 'mssql_n':
                $CFG->dbtype = 'mssql';
                break;

            case 'oci8po':
                $CFG->dbtype = 'oci';
                break;

            case 'mysql' :
                $CFG->dbtype = 'mysqli';
                break;
        }
    }

    if (!isset($CFG->dboptions)) {
        $CFG->dboptions = array();
    }

    if (isset($CFG->dbpersist)) {
        $CFG->dboptions['dbpersist'] = $CFG->dbpersist;
    }

    if (!$DB = moodle_database::get_driver_instance($CFG->dbtype, $CFG->dblibrary)) {
        throw new dml_exception('dbdriverproblem', "Unknown driver $CFG->dblibrary/$CFG->dbtype");
    }

    try {
        $DB->connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass, $CFG->dbname, $CFG->prefix, $CFG->dboptions);
    } catch (moodle_exception $e) {
        if (empty($CFG->noemailever) and !empty($CFG->emailconnectionerrorsto)) {
            $body = "Connection error: ".$CFG->wwwroot.
                "\n\nInfo:".
                "\n\tError code: ".$e->errorcode.
                "\n\tDebug info: ".$e->debuginfo.
                "\n\tServer: ".$_SERVER['SERVER_NAME']." (".$_SERVER['SERVER_ADDR'].")";
            if (file_exists($CFG->dataroot.'/emailcount')){
                $fp = @fopen($CFG->dataroot.'/emailcount', 'r');
                $content = @fread($fp, 24);
                @fclose($fp);
                if((time() - (int)$content) > 600){
                                        @mail($CFG->emailconnectionerrorsto,
                        'WARNING: Database connection error: '.$CFG->wwwroot,
                        $body);
                    $fp = @fopen($CFG->dataroot.'/emailcount', 'w');
                    @fwrite($fp, time());
                }
            } else {
                              @mail($CFG->emailconnectionerrorsto,
                    'WARNING: Database connection error: '.$CFG->wwwroot,
                    $body);
               $fp = @fopen($CFG->dataroot.'/emailcount', 'w');
               @fwrite($fp, time());
            }
        }
                throw $e;
    }

    $CFG->dbfamily = $DB->get_dbfamily(); 
    return true;
}
