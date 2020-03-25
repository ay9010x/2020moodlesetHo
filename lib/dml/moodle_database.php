<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/database_column_info.php');
require_once(__DIR__.'/moodle_recordset.php');
require_once(__DIR__.'/moodle_transaction.php');


define('SQL_PARAMS_NAMED', 1);


define('SQL_PARAMS_QM', 2);


define('SQL_PARAMS_DOLLAR', 4);


define('SQL_QUERY_SELECT', 1);


define('SQL_QUERY_INSERT', 2);


define('SQL_QUERY_UPDATE', 3);


define('SQL_QUERY_STRUCTURE', 4);


define('SQL_QUERY_AUX', 5);


abstract class moodle_database {

    
    protected $database_manager;
    
    protected $temptables;
    
    protected $tables  = null;

        
    protected $dbhost;
    
    protected $dbuser;
    
    protected $dbpass;
    
    protected $dbname;
    
    protected $prefix;

    
    protected $dboptions;

    
    protected $external;

    
    protected $reads = 0;
    
    protected $writes = 0;
    
    protected $queriestime = 0;

    
    protected $debug  = 0;

    
    protected $last_sql;
    
    protected $last_params;
    
    protected $last_type;
    
    protected $last_extrainfo;
    
    protected $last_time;
    
    private $loggingquery = false;

    
    protected $used_for_db_sessions = false;

    
    private $transactions = array();
    
    private $force_rollback = false;

    
    private $settingshash;

    
    protected $metacache;

    
    protected $disposed;

    
    private $fix_sql_params_i;
    
    private $inorequaluniqueindex = 1;

    
    protected $skiplogging = false;

    
    public function __construct($external=false) {
        $this->external  = $external;
    }

    
    public function __destruct() {
        $this->dispose();
    }

    
    public abstract function driver_installed();

    
    public function get_prefix() {
        return $this->prefix;
    }

    
    public static function get_driver_instance($type, $library, $external = false) {
        global $CFG;

        $classname = $type.'_'.$library.'_moodle_database';
        $libfile   = "$CFG->libdir/dml/$classname.php";

        if (!file_exists($libfile)) {
            return null;
        }

        require_once($libfile);
        return new $classname($external);
    }

    
    public function get_dbvendor() {
        return $this->get_dbfamily();
    }

    
    public abstract function get_dbfamily();

    
    protected abstract function get_dbtype();

    
    protected abstract function get_dblibrary();

    
    public abstract function get_name();

    
    public abstract function get_configuration_help();

    
    public function get_configuration_hints() {
        debugging('$DB->get_configuration_hints() method is deprecated, use $DB->get_configuration_help() instead');
        return $this->get_configuration_help();
    }

    
    public function export_dbconfig() {
        $cfg = new stdClass();
        $cfg->dbtype    = $this->get_dbtype();
        $cfg->dblibrary = $this->get_dblibrary();
        $cfg->dbhost    = $this->dbhost;
        $cfg->dbname    = $this->dbname;
        $cfg->dbuser    = $this->dbuser;
        $cfg->dbpass    = $this->dbpass;
        $cfg->prefix    = $this->prefix;
        if ($this->dboptions) {
            $cfg->dboptions = $this->dboptions;
        }

        return $cfg;
    }

    
    public function diagnose() {
        return null;
    }

    
    public abstract function connect($dbhost, $dbuser, $dbpass, $dbname, $prefix, array $dboptions=null);

    
    protected function store_settings($dbhost, $dbuser, $dbpass, $dbname, $prefix, array $dboptions=null) {
        $this->dbhost    = $dbhost;
        $this->dbuser    = $dbuser;
        $this->dbpass    = $dbpass;
        $this->dbname    = $dbname;
        $this->prefix    = $prefix;
        $this->dboptions = (array)$dboptions;
    }

    
    protected function get_settings_hash() {
        if (empty($this->settingshash)) {
            $this->settingshash = md5($this->dbhost . $this->dbuser . $this->dbname . $this->prefix);
        }
        return $this->settingshash;
    }

    
    protected function get_metacache() {
        $properties = array('dbfamily' => $this->get_dbfamily(), 'settings' => $this->get_settings_hash());
        return cache::make('core', 'databasemeta', $properties);
    }

    
    protected function get_temp_tables_cache() {
                $properties = array('dbfamily' => $this->get_dbfamily(), 'settings' => $this->get_settings_hash());
        return cache::make('core', 'temp_tables', $properties);
    }

    
    public function create_database($dbhost, $dbuser, $dbpass, $dbname, array $dboptions=null) {
        return false;
    }

    
    public function get_transaction_start_backtrace() {
        if (!$this->transactions) {
            return null;
        }
        $lowesttransaction = end($this->transactions);
        return $lowesttransaction->get_backtrace();
    }

    
    public function dispose() {
        if ($this->disposed) {
            return;
        }
        $this->disposed = true;
        if ($this->transactions) {
            $this->force_transaction_rollback();
        }

        if ($this->temptables) {
            $this->temptables->dispose();
            $this->temptables = null;
        }
        if ($this->database_manager) {
            $this->database_manager->dispose();
            $this->database_manager = null;
        }
        $this->tables  = null;
    }

    
    protected function query_start($sql, array $params=null, $type, $extrainfo=null) {
        if ($this->loggingquery) {
            return;
        }
        $this->last_sql       = $sql;
        $this->last_params    = $params;
        $this->last_type      = $type;
        $this->last_extrainfo = $extrainfo;
        $this->last_time      = microtime(true);

        switch ($type) {
            case SQL_QUERY_SELECT:
            case SQL_QUERY_AUX:
                $this->reads++;
                break;
            case SQL_QUERY_INSERT:
            case SQL_QUERY_UPDATE:
            case SQL_QUERY_STRUCTURE:
                $this->writes++;
            default:
                if ((PHPUNIT_TEST) || (defined('BEHAT_TEST') && BEHAT_TEST) ||
                    defined('BEHAT_SITE_RUNNING')) {

                                        require_once(__DIR__.'/../testing/classes/util.php');
                    testing_util::set_table_modified_by_sql($sql);
                }
        }

        $this->print_debug($sql, $params);
    }

    
    protected function query_end($result) {
        if ($this->loggingquery) {
            return;
        }
        if ($result !== false) {
            $this->query_log();
                        $this->last_sql    = null;
            $this->last_params = null;
            $this->print_debug_time();
            return;
        }

                $type   = $this->last_type;
        $sql    = $this->last_sql;
        $params = $this->last_params;
        $error  = $this->get_last_error();

        $this->query_log($error);

        switch ($type) {
            case SQL_QUERY_SELECT:
            case SQL_QUERY_AUX:
                throw new dml_read_exception($error, $sql, $params);
            case SQL_QUERY_INSERT:
            case SQL_QUERY_UPDATE:
                throw new dml_write_exception($error, $sql, $params);
            case SQL_QUERY_STRUCTURE:
                $this->get_manager();                 throw new ddl_change_structure_exception($error, $sql);
        }
    }

    
    public function query_log($error=false) {
                if ($this->skiplogging) {
            return;
        }

        $logall    = !empty($this->dboptions['logall']);
        $logslow   = !empty($this->dboptions['logslow']) ? $this->dboptions['logslow'] : false;
        $logerrors = !empty($this->dboptions['logerrors']);
        $iserror   = ($error !== false);

        $time = $this->query_time();

                $this->queriestime = $this->queriestime + $time;

        if ($logall or ($logslow and ($logslow < ($time+0.00001))) or ($iserror and $logerrors)) {
            $this->loggingquery = true;
            try {
                $backtrace = debug_backtrace();
                if ($backtrace) {
                                        array_shift($backtrace);
                }
                if ($backtrace) {
                                        array_shift($backtrace);
                }
                $log = new stdClass();
                $log->qtype      = $this->last_type;
                $log->sqltext    = $this->last_sql;
                $log->sqlparams  = var_export((array)$this->last_params, true);
                $log->error      = (int)$iserror;
                $log->info       = $iserror ? $error : null;
                $log->backtrace  = format_backtrace($backtrace, true);
                $log->exectime   = $time;
                $log->timelogged = time();
                $this->insert_record('log_queries', $log);
            } catch (Exception $ignored) {
            }
            $this->loggingquery = false;
        }
    }

    
    protected function query_log_prevent() {
        $this->skiplogging = true;
    }

    
    protected function query_log_allow() {
        $this->skiplogging = false;
    }

    
    protected function query_time() {
        return microtime(true) - $this->last_time;
    }

    
    public abstract function get_server_info();

    
    protected abstract function allowed_param_types();

    
    public abstract function get_last_error();

    
    protected function print_debug($sql, array $params=null, $obj=null) {
        if (!$this->get_debug()) {
            return;
        }
        if (CLI_SCRIPT) {
            echo "--------------------------------\n";
            echo $sql."\n";
            if (!is_null($params)) {
                echo "[".var_export($params, true)."]\n";
            }
            echo "--------------------------------\n";
        } else {
            echo "<hr />\n";
            echo s($sql)."\n";
            if (!is_null($params)) {
                echo "[".s(var_export($params, true))."]\n";
            }
            echo "<hr />\n";
        }
    }

    
    protected function print_debug_time() {
        if (!$this->get_debug()) {
            return;
        }
        $time = $this->query_time();
        $message = "Query took: {$time} seconds.\n";
        if (CLI_SCRIPT) {
            echo $message;
            echo "--------------------------------\n";
        } else {
            echo s($message);
            echo "<hr />\n";
        }
    }

    
    protected function where_clause($table, array $conditions=null) {
                $conditions = is_null($conditions) ? array() : $conditions;

        if (empty($conditions)) {
            return array('', array());
        }

                if (debugging()) {
            $columns = $this->get_columns($table);
            if (empty($columns)) {
                                throw new dml_exception('ddltablenotexist', $table);
            }
            foreach ($conditions as $key=>$value) {
                if (!isset($columns[$key])) {
                    $a = new stdClass();
                    $a->fieldname = $key;
                    $a->tablename = $table;
                    throw new dml_exception('ddlfieldnotexist', $a);
                }
                $column = $columns[$key];
                if ($column->meta_type == 'X') {
                                        throw new dml_exception('textconditionsnotallowed', $conditions);
                }
            }
        }

        $allowed_types = $this->allowed_param_types();
        $where = array();
        $params = array();

        foreach ($conditions as $key=>$value) {
            if (is_int($key)) {
                throw new dml_exception('invalidnumkey');
            }
            if (is_null($value)) {
                $where[] = "$key IS NULL";
            } else {
                if ($allowed_types & SQL_PARAMS_NAMED) {
                                                            $normkey = trim(preg_replace('/[^a-zA-Z0-9_-]/', '_', $key), '-_');
                    if ($normkey !== $key) {
                        debugging('Invalid key found in the conditions array.');
                    }
                    $where[] = "$key = :$normkey";
                    $params[$normkey] = $value;
                } else {
                    $where[] = "$key = ?";
                    $params[] = $value;
                }
            }
        }
        $where = implode(" AND ", $where);
        return array($where, $params);
    }

    
    protected function where_clause_list($field, array $values) {
        if (empty($values)) {
            return array("1 = 2", array());         }

        
        $params = array();
        $select = "";
        $values = (array)$values;
        foreach ($values as $value) {
            if (is_bool($value)) {
                $value = (int)$value;
            }
            if (is_null($value)) {
                $select = "$field IS NULL";
            } else {
                $params[] = $value;
            }
        }
        if ($params) {
            if ($select !== "") {
                $select = "$select OR ";
            }
            $count = count($params);
            if ($count == 1) {
                $select = $select."$field = ?";
            } else {
                $qs = str_repeat(',?', $count);
                $qs = ltrim($qs, ',');
                $select = $select."$field IN ($qs)";
            }
        }
        return array($select, $params);
    }

    
    public function get_in_or_equal($items, $type=SQL_PARAMS_QM, $prefix='param', $equal=true, $onemptyitems=false) {

                if (is_array($items) and empty($items) and $onemptyitems === false) {
            throw new coding_exception('moodle_database::get_in_or_equal() does not accept empty arrays');
        }
                if (is_array($items) and empty($items)) {
            if (is_null($onemptyitems)) {                             $sql = $equal ? ' IS NULL' : ' IS NOT NULL';
                return (array($sql, array()));
            } else {
                $items = array($onemptyitems);                    }
        }

        if ($type == SQL_PARAMS_QM) {
            if (!is_array($items) or count($items) == 1) {
                $sql = $equal ? '= ?' : '<> ?';
                $items = (array)$items;
                $params = array_values($items);
            } else {
                if ($equal) {
                    $sql = 'IN ('.implode(',', array_fill(0, count($items), '?')).')';
                } else {
                    $sql = 'NOT IN ('.implode(',', array_fill(0, count($items), '?')).')';
                }
                $params = array_values($items);
            }

        } else if ($type == SQL_PARAMS_NAMED) {
            if (empty($prefix)) {
                $prefix = 'param';
            }

            if (!is_array($items)){
                $param = $prefix.$this->inorequaluniqueindex++;
                $sql = $equal ? "= :$param" : "<> :$param";
                $params = array($param=>$items);
            } else if (count($items) == 1) {
                $param = $prefix.$this->inorequaluniqueindex++;
                $sql = $equal ? "= :$param" : "<> :$param";
                $item = reset($items);
                $params = array($param=>$item);
            } else {
                $params = array();
                $sql = array();
                foreach ($items as $item) {
                    $param = $prefix.$this->inorequaluniqueindex++;
                    $params[$param] = $item;
                    $sql[] = ':'.$param;
                }
                if ($equal) {
                    $sql = 'IN ('.implode(',', $sql).')';
                } else {
                    $sql = 'NOT IN ('.implode(',', $sql).')';
                }
            }

        } else {
            throw new dml_exception('typenotimplement');
        }
        return array($sql, $params);
    }

    
    protected function fix_table_names($sql) {
        return preg_replace('/\{([a-z][a-z0-9_]*)\}/', $this->prefix.'$1', $sql);
    }

    
    private function _fix_sql_params_dollar_callback($match) {
        $this->fix_sql_params_i++;
        return "\$".$this->fix_sql_params_i;
    }

    
    protected function detect_objects($value) {
        if (is_object($value)) {
            throw new coding_exception('Invalid database query parameter value', 'Objects are are not allowed: '.get_class($value));
        }
    }

    
    public function fix_sql_params($sql, array $params=null) {
        $params = (array)$params;         $allowed_types = $this->allowed_param_types();

                $sql = $this->fix_table_names($sql);

                foreach ($params as $key => $value) {
            $this->detect_objects($value);
            $params[$key] = is_bool($value) ? (int)$value : $value;
        }

                $named_count = preg_match_all('/(?<!:):[a-z][a-z0-9_]*/', $sql, $named_matches);         $dollar_count = preg_match_all('/\$[1-9][0-9]*/', $sql, $dollar_matches);
        $q_count     = substr_count($sql, '?');

        $count = 0;

        if ($named_count) {
            $type = SQL_PARAMS_NAMED;
            $count = $named_count;

        }
        if ($dollar_count) {
            if ($count) {
                throw new dml_exception('mixedtypesqlparam');
            }
            $type = SQL_PARAMS_DOLLAR;
            $count = $dollar_count;

        }
        if ($q_count) {
            if ($count) {
                throw new dml_exception('mixedtypesqlparam');
            }
            $type = SQL_PARAMS_QM;
            $count = $q_count;

        }

        if (!$count) {
                         if ($allowed_types & SQL_PARAMS_NAMED) {
                return array($sql, array(), SQL_PARAMS_NAMED);
            } else if ($allowed_types & SQL_PARAMS_QM) {
                return array($sql, array(), SQL_PARAMS_QM);
            } else {
                return array($sql, array(), SQL_PARAMS_DOLLAR);
            }
        }

        if ($count > count($params)) {
            $a = new stdClass;
            $a->expected = $count;
            $a->actual = count($params);
            throw new dml_exception('invalidqueryparam', $a);
        }

        $target_type = $allowed_types;

        if ($type & $allowed_types) {             if ($count == count($params)) {
                if ($type == SQL_PARAMS_QM) {
                    return array($sql, array_values($params), SQL_PARAMS_QM);                 } else {
                                    }
            }
                        $target_type = $type;
        }

        if ($type == SQL_PARAMS_NAMED) {
            $finalparams = array();
            foreach ($named_matches[0] as $key) {
                $key = trim($key, ':');
                if (!array_key_exists($key, $params)) {
                    throw new dml_exception('missingkeyinsql', $key, '');
                }
                if (strlen($key) > 30) {
                    throw new coding_exception(
                            "Placeholder names must be 30 characters or shorter. '" .
                            $key . "' is too long.", $sql);
                }
                $finalparams[$key] = $params[$key];
            }
            if ($count != count($finalparams)) {
                throw new dml_exception('duplicateparaminsql');
            }

            if ($target_type & SQL_PARAMS_QM) {
                $sql = preg_replace('/(?<!:):[a-z][a-z0-9_]*/', '?', $sql);
                return array($sql, array_values($finalparams), SQL_PARAMS_QM);             } else if ($target_type & SQL_PARAMS_NAMED) {
                return array($sql, $finalparams, SQL_PARAMS_NAMED);
            } else {                                  $this->fix_sql_params_i = 0;
                $sql = preg_replace_callback('/(?<!:):[a-z][a-z0-9_]*/', array($this, '_fix_sql_params_dollar_callback'), $sql);
                return array($sql, array_values($finalparams), SQL_PARAMS_DOLLAR);             }

        } else if ($type == SQL_PARAMS_DOLLAR) {
            if ($target_type & SQL_PARAMS_DOLLAR) {
                return array($sql, array_values($params), SQL_PARAMS_DOLLAR);             } else if ($target_type & SQL_PARAMS_QM) {
                $sql = preg_replace('/\$[0-9]+/', '?', $sql);
                return array($sql, array_values($params), SQL_PARAMS_QM);             } else {                 $sql = preg_replace('/\$([0-9]+)/', ':param\\1', $sql);
                $finalparams = array();
                foreach ($params as $key=>$param) {
                    $key++;
                    $finalparams['param'.$key] = $param;
                }
                return array($sql, $finalparams, SQL_PARAMS_NAMED);
            }

        } else {             if (count($params) != $count) {
                $params = array_slice($params, 0, $count);
            }

            if ($target_type & SQL_PARAMS_QM) {
                return array($sql, array_values($params), SQL_PARAMS_QM);             } else if ($target_type & SQL_PARAMS_NAMED) {
                $finalparams = array();
                $pname = 'param0';
                $parts = explode('?', $sql);
                $sql = array_shift($parts);
                foreach ($parts as $part) {
                    $param = array_shift($params);
                    $pname++;
                    $sql .= ':'.$pname.$part;
                    $finalparams[$pname] = $param;
                }
                return array($sql, $finalparams, SQL_PARAMS_NAMED);
            } else {                                  $this->fix_sql_params_i = 0;
                $sql = preg_replace_callback('/\?/', array($this, '_fix_sql_params_dollar_callback'), $sql);
                return array($sql, array_values($params), SQL_PARAMS_DOLLAR);             }
        }
    }

    
    protected function normalise_limit_from_num($limitfrom, $limitnum) {
        global $CFG;

                if ($limitfrom === null || $limitfrom === '' || $limitfrom === -1) {
            $limitfrom = 0;
        }
        if ($limitnum === null || $limitnum === '' || $limitnum === -1) {
            $limitnum = 0;
        }

        if ($CFG->debugdeveloper) {
            if (!is_numeric($limitfrom)) {
                $strvalue = var_export($limitfrom, true);
                debugging("Non-numeric limitfrom parameter detected: $strvalue, did you pass the correct arguments?",
                    DEBUG_DEVELOPER);
            } else if ($limitfrom < 0) {
                debugging("Negative limitfrom parameter detected: $limitfrom, did you pass the correct arguments?",
                    DEBUG_DEVELOPER);
            }

            if (!is_numeric($limitnum)) {
                $strvalue = var_export($limitnum, true);
                debugging("Non-numeric limitnum parameter detected: $strvalue, did you pass the correct arguments?",
                    DEBUG_DEVELOPER);
            } else if ($limitnum < 0) {
                debugging("Negative limitnum parameter detected: $limitnum, did you pass the correct arguments?",
                    DEBUG_DEVELOPER);
            }
        }

        $limitfrom = (int)$limitfrom;
        $limitnum  = (int)$limitnum;
        $limitfrom = max(0, $limitfrom);
        $limitnum  = max(0, $limitnum);

        return array($limitfrom, $limitnum);
    }

    
    public abstract function get_tables($usecache=true);

    
    public abstract function get_indexes($table);

    
    public abstract function get_columns($table, $usecache=true);

    
    protected abstract function normalise_value($column, $value);

    
    public function reset_caches($tablenames = null) {
        if (!empty($tablenames)) {
            $dbmetapurged = false;
            foreach ($tablenames as $tablename) {
                if ($this->temptables->is_temptable($tablename)) {
                    $this->get_temp_tables_cache()->delete($tablename);
                } else if ($dbmetapurged === false) {
                    $this->tables = null;
                    $this->get_metacache()->purge();
                    $this->metacache = null;
                    $dbmetapurged = true;
                }
            }
        } else {
            $this->get_temp_tables_cache()->purge();
            $this->tables = null;
                        $this->get_metacache()->purge();
            $this->metacache = null;
        }
    }

    
    public function get_manager() {
        global $CFG;

        if (!$this->database_manager) {
            require_once($CFG->libdir.'/ddllib.php');

            $classname = $this->get_dbfamily().'_sql_generator';
            require_once("$CFG->libdir/ddl/$classname.php");
            $generator = new $classname($this, $this->temptables);

            $this->database_manager = new database_manager($this, $generator);
        }
        return $this->database_manager;
    }

    
    public function change_db_encoding() {
        return false;
    }

    
    public function setup_is_unicodedb() {
        return true;
    }

    
    public function set_debug($state) {
        $this->debug = $state;
    }

    
    public function get_debug() {
        return $this->debug;
    }

    
    public function set_logging($state) {
        throw new coding_exception('set_logging() can not be used any more.');
    }

    
    public abstract function change_database_structure($sql, $tablenames = null);

    
    public abstract function execute($sql, array $params=null);

    
    public function get_recordset($table, array $conditions=null, $sort='', $fields='*', $limitfrom=0, $limitnum=0) {
        list($select, $params) = $this->where_clause($table, $conditions);
        return $this->get_recordset_select($table, $select, $params, $sort, $fields, $limitfrom, $limitnum);
    }

    
    public function get_recordset_list($table, $field, array $values, $sort='', $fields='*', $limitfrom=0, $limitnum=0) {
        list($select, $params) = $this->where_clause_list($field, $values);
        return $this->get_recordset_select($table, $select, $params, $sort, $fields, $limitfrom, $limitnum);
    }

    
    public function get_recordset_select($table, $select, array $params=null, $sort='', $fields='*', $limitfrom=0, $limitnum=0) {
        $sql = "SELECT $fields FROM {".$table."}";
        if ($select) {
            $sql .= " WHERE $select";
        }
        if ($sort) {
            $sql .= " ORDER BY $sort";
        }
        return $this->get_recordset_sql($sql, $params, $limitfrom, $limitnum);
    }

    
    public abstract function get_recordset_sql($sql, array $params=null, $limitfrom=0, $limitnum=0);

    
    public function export_table_recordset($table) {
        return $this->get_recordset($table, array());
    }

    
    public function get_records($table, array $conditions=null, $sort='', $fields='*', $limitfrom=0, $limitnum=0) {
        list($select, $params) = $this->where_clause($table, $conditions);
        return $this->get_records_select($table, $select, $params, $sort, $fields, $limitfrom, $limitnum);
    }

    
    public function get_records_list($table, $field, array $values, $sort='', $fields='*', $limitfrom=0, $limitnum=0) {
        list($select, $params) = $this->where_clause_list($field, $values);
        return $this->get_records_select($table, $select, $params, $sort, $fields, $limitfrom, $limitnum);
    }

    
    public function get_records_select($table, $select, array $params=null, $sort='', $fields='*', $limitfrom=0, $limitnum=0) {
        if ($select) {
            $select = "WHERE $select";
        }
        if ($sort) {
            $sort = " ORDER BY $sort";
        }
        return $this->get_records_sql("SELECT $fields FROM {" . $table . "} $select $sort", $params, $limitfrom, $limitnum);
    }

    
    public abstract function get_records_sql($sql, array $params=null, $limitfrom=0, $limitnum=0);

    
    public function get_records_menu($table, array $conditions=null, $sort='', $fields='*', $limitfrom=0, $limitnum=0) {
        $menu = array();
        if ($records = $this->get_records($table, $conditions, $sort, $fields, $limitfrom, $limitnum)) {
            foreach ($records as $record) {
                $record = (array)$record;
                $key   = array_shift($record);
                $value = array_shift($record);
                $menu[$key] = $value;
            }
        }
        return $menu;
    }

    
    public function get_records_select_menu($table, $select, array $params=null, $sort='', $fields='*', $limitfrom=0, $limitnum=0) {
        $menu = array();
        if ($records = $this->get_records_select($table, $select, $params, $sort, $fields, $limitfrom, $limitnum)) {
            foreach ($records as $record) {
                $record = (array)$record;
                $key   = array_shift($record);
                $value = array_shift($record);
                $menu[$key] = $value;
            }
        }
        return $menu;
    }

    
    public function get_records_sql_menu($sql, array $params=null, $limitfrom=0, $limitnum=0) {
        $menu = array();
        if ($records = $this->get_records_sql($sql, $params, $limitfrom, $limitnum)) {
            foreach ($records as $record) {
                $record = (array)$record;
                $key   = array_shift($record);
                $value = array_shift($record);
                $menu[$key] = $value;
            }
        }
        return $menu;
    }

    
    public function get_record($table, array $conditions, $fields='*', $strictness=IGNORE_MISSING) {
        list($select, $params) = $this->where_clause($table, $conditions);
        return $this->get_record_select($table, $select, $params, $fields, $strictness);
    }

    
    public function get_record_select($table, $select, array $params=null, $fields='*', $strictness=IGNORE_MISSING) {
        if ($select) {
            $select = "WHERE $select";
        }
        try {
            return $this->get_record_sql("SELECT $fields FROM {" . $table . "} $select", $params, $strictness);
        } catch (dml_missing_record_exception $e) {
                        throw new dml_missing_record_exception($table, $e->sql, $e->params);
        }
    }

    
    public function get_record_sql($sql, array $params=null, $strictness=IGNORE_MISSING) {
        $strictness = (int)$strictness;         if ($strictness == IGNORE_MULTIPLE) {
            $count = 1;
        } else {
            $count = 0;
        }
        if (!$records = $this->get_records_sql($sql, $params, 0, $count)) {
                        if ($strictness == MUST_EXIST) {
                throw new dml_missing_record_exception('', $sql, $params);
            }
            return false;
        }

        if (count($records) > 1) {
            if ($strictness == MUST_EXIST) {
                throw new dml_multiple_records_exception($sql, $params);
            }
            debugging('Error: mdb->get_record() found more than one record!');
        }

        $return = reset($records);
        return $return;
    }

    
    public function get_field($table, $return, array $conditions, $strictness=IGNORE_MISSING) {
        list($select, $params) = $this->where_clause($table, $conditions);
        return $this->get_field_select($table, $return, $select, $params, $strictness);
    }

    
    public function get_field_select($table, $return, $select, array $params=null, $strictness=IGNORE_MISSING) {
        if ($select) {
            $select = "WHERE $select";
        }
        try {
            return $this->get_field_sql("SELECT $return FROM {" . $table . "} $select", $params, $strictness);
        } catch (dml_missing_record_exception $e) {
                        throw new dml_missing_record_exception($table, $e->sql, $e->params);
        }
    }

    
    public function get_field_sql($sql, array $params=null, $strictness=IGNORE_MISSING) {
        if (!$record = $this->get_record_sql($sql, $params, $strictness)) {
            return false;
        }

        $record = (array)$record;
        return reset($record);     }

    
    public function get_fieldset_select($table, $return, $select, array $params=null) {
        if ($select) {
            $select = "WHERE $select";
        }
        return $this->get_fieldset_sql("SELECT $return FROM {" . $table . "} $select", $params);
    }

    
    public abstract function get_fieldset_sql($sql, array $params=null);

    
    public abstract function insert_record_raw($table, $params, $returnid=true, $bulk=false, $customsequence=false);

    
    public abstract function insert_record($table, $dataobject, $returnid=true, $bulk=false);

    
    public function insert_records($table, $dataobjects) {
        if (!is_array($dataobjects) and !($dataobjects instanceof Traversable)) {
            throw new coding_exception('insert_records() passed non-traversable object');
        }

        $fields = null;
                foreach ($dataobjects as $dataobject) {
            if (!is_array($dataobject) and !is_object($dataobject)) {
                throw new coding_exception('insert_records() passed invalid record object');
            }
            $dataobject = (array)$dataobject;
            if ($fields === null) {
                $fields = array_keys($dataobject);
            } else if ($fields !== array_keys($dataobject)) {
                throw new coding_exception('All dataobjects in insert_records() must have the same structure!');
            }
            $this->insert_record($table, $dataobject, false);
        }
    }

    
    public abstract function import_record($table, $dataobject);

    
    public abstract function update_record_raw($table, $params, $bulk=false);

    
    public abstract function update_record($table, $dataobject, $bulk=false);

    
    public function set_field($table, $newfield, $newvalue, array $conditions=null) {
        list($select, $params) = $this->where_clause($table, $conditions);
        return $this->set_field_select($table, $newfield, $newvalue, $select, $params);
    }

    
    public abstract function set_field_select($table, $newfield, $newvalue, $select, array $params=null);


    
    public function count_records($table, array $conditions=null) {
        list($select, $params) = $this->where_clause($table, $conditions);
        return $this->count_records_select($table, $select, $params);
    }

    
    public function count_records_select($table, $select, array $params=null, $countitem="COUNT('x')") {
        if ($select) {
            $select = "WHERE $select";
        }
        return $this->count_records_sql("SELECT $countitem FROM {" . $table . "} $select", $params);
    }

    
    public function count_records_sql($sql, array $params=null) {
        $count = $this->get_field_sql($sql, $params);
        if ($count === false or !is_number($count) or $count < 0) {
            throw new coding_exception("count_records_sql() expects the first field to contain non-negative number from COUNT(), '$count' found instead.");
        }
        return (int)$count;
    }

    
    public function record_exists($table, array $conditions) {
        list($select, $params) = $this->where_clause($table, $conditions);
        return $this->record_exists_select($table, $select, $params);
    }

    
    public function record_exists_select($table, $select, array $params=null) {
        if ($select) {
            $select = "WHERE $select";
        }
        return $this->record_exists_sql("SELECT 'x' FROM {" . $table . "} $select", $params);
    }

    
    public function record_exists_sql($sql, array $params=null) {
        $mrs = $this->get_recordset_sql($sql, $params, 0, 1);
        $return = $mrs->valid();
        $mrs->close();
        return $return;
    }

    
    public function delete_records($table, array $conditions=null) {
                        if (is_null($conditions) && empty($this->transactions)) {
            return $this->execute("TRUNCATE TABLE {".$table."}");
        }
        list($select, $params) = $this->where_clause($table, $conditions);
        return $this->delete_records_select($table, $select, $params);
    }

    
    public function delete_records_list($table, $field, array $values) {
        list($select, $params) = $this->where_clause_list($field, $values);
        return $this->delete_records_select($table, $select, $params);
    }

    
    public abstract function delete_records_select($table, $select, array $params=null);

    
    public function sql_null_from_clause() {
        return '';
    }

    
    public function sql_bitand($int1, $int2) {
        return '((' . $int1 . ') & (' . $int2 . '))';
    }

    
    public function sql_bitnot($int1) {
        return '(~(' . $int1 . '))';
    }

    
    public function sql_bitor($int1, $int2) {
        return '((' . $int1 . ') | (' . $int2 . '))';
    }

    
    public function sql_bitxor($int1, $int2) {
        return '((' . $int1 . ') ^ (' . $int2 . '))';
    }

    
    public function sql_modulo($int1, $int2) {
        return '((' . $int1 . ') % (' . $int2 . '))';
    }

    
    public function sql_ceil($fieldname) {
        return ' CEIL(' . $fieldname . ')';
    }

    
    public function sql_cast_char2int($fieldname, $text=false) {
        return ' ' . $fieldname . ' ';
    }

    
    public function sql_cast_char2real($fieldname, $text=false) {
        return ' ' . $fieldname . ' ';
    }

    
    public function sql_cast_2signed($fieldname) {
        return ' ' . $fieldname . ' ';
    }

    
    public function sql_compare_text($fieldname, $numchars=32) {
        return $this->sql_order_by_text($fieldname, $numchars);
    }

    
    public function sql_like($fieldname, $param, $casesensitive = true, $accentsensitive = true, $notlike = false, $escapechar = '\\') {
        if (strpos($param, '%') !== false) {
            debugging('Potential SQL injection detected, sql_like() expects bound parameters (? or :named)');
        }
        $LIKE = $notlike ? 'NOT LIKE' : 'LIKE';
                return "$fieldname $LIKE $param ESCAPE '$escapechar'";
    }

    
    public function sql_like_escape($text, $escapechar = '\\') {
        $text = str_replace('_', $escapechar.'_', $text);
        $text = str_replace('%', $escapechar.'%', $text);
        return $text;
    }

    
    public abstract function sql_concat();

    
    public abstract function sql_concat_join($separator="' '", $elements=array());

    
    function sql_fullname($first='firstname', $last='lastname') {
        return $this->sql_concat($first, "' '", $last);
    }

    
    public function sql_order_by_text($fieldname, $numchars=32) {
        return $fieldname;
    }

    
    public function sql_length($fieldname) {
        return ' LENGTH(' . $fieldname . ')';
    }

    
    public function sql_substr($expr, $start, $length=false) {
        if (count(func_get_args()) < 2) {
            throw new coding_exception('moodle_database::sql_substr() requires at least two parameters', 'Originally this function was only returning name of SQL substring function, it now requires all parameters.');
        }
        if ($length === false) {
            return "SUBSTR($expr, $start)";
        } else {
            return "SUBSTR($expr, $start, $length)";
        }
    }

    
    public function sql_position($needle, $haystack) {
                return "POSITION(($needle) IN ($haystack))";
    }

    
    function sql_empty() {
        debugging("sql_empty() is deprecated, please use empty string '' as sql parameter value instead", DEBUG_DEVELOPER);
        return '';
    }

    
    public function sql_isempty($tablename, $fieldname, $nullablefield, $textfield) {
        return " ($fieldname = '') ";
    }

    
    public function sql_isnotempty($tablename, $fieldname, $nullablefield, $textfield) {
        return ' ( NOT ' . $this->sql_isempty($tablename, $fieldname, $nullablefield, $textfield) . ') ';
    }

    
    public function sql_regex_supported() {
        return false;
    }

    
    public function sql_regex($positivematch=true) {
        return '';
    }

    
    public function sql_intersect($selects, $fields) {
        if (!count($selects)) {
            throw new coding_exception('sql_intersect() requires at least one element in $selects');
        } else if (count($selects) == 1) {
            return $selects[0];
        }
        static $aliascnt = 0;
        $rv = '('.$selects[0].')';
        for ($i = 1; $i < count($selects); $i++) {
            $rv .= " INTERSECT (".$selects[$i].')';
        }
        return $rv;
    }

    
    public function replace_all_text_supported() {
        return false;
    }

    
    public function replace_all_text($table, database_column_info $column, $search, $replace) {
        if (!$this->replace_all_text_supported()) {
            return;
        }

                
        $columnname = $column->name;
        $sql = "UPDATE {".$table."}
                       SET $columnname = REPLACE($columnname, ?, ?)
                     WHERE $columnname IS NOT NULL";

        if ($column->meta_type === 'X') {
            $this->execute($sql, array($search, $replace));

        } else if ($column->meta_type === 'C') {
            if (core_text::strlen($search) < core_text::strlen($replace)) {
                $colsize = $column->max_length;
                $sql = "UPDATE {".$table."}
                       SET $columnname = " . $this->sql_substr("REPLACE(" . $columnname . ", ?, ?)", 1, $colsize) . "
                     WHERE $columnname IS NOT NULL";
            }
            $this->execute($sql, array($search, $replace));
        }
    }

    
    public function update_temp_table_stats() {
        $this->temptables->update_stats();
    }

    
    protected function transactions_supported() {
                return true;
    }

    
    public function is_transaction_started() {
        return !empty($this->transactions);
    }

    
    public function transactions_forbidden() {
        if ($this->is_transaction_started()) {
            throw new dml_transaction_exception('This code can not be excecuted in transaction');
        }
    }

    
    public function start_delegated_transaction() {
        $transaction = new moodle_transaction($this);
        $this->transactions[] = $transaction;
        if (count($this->transactions) == 1) {
            $this->begin_transaction();
        }
        return $transaction;
    }

    
    protected abstract function begin_transaction();

    
    public function commit_delegated_transaction(moodle_transaction $transaction) {
        if ($transaction->is_disposed()) {
            throw new dml_transaction_exception('Transactions already disposed', $transaction);
        }
                $transaction->dispose();

        if (empty($this->transactions)) {
            throw new dml_transaction_exception('Transaction not started', $transaction);
        }

        if ($this->force_rollback) {
            throw new dml_transaction_exception('Tried to commit transaction after lower level rollback', $transaction);
        }

        if ($transaction !== $this->transactions[count($this->transactions) - 1]) {
                        $this->force_rollback = true;
            throw new dml_transaction_exception('Invalid transaction commit attempt', $transaction);
        }

        if (count($this->transactions) == 1) {
                        $this->commit_transaction();
        }
        array_pop($this->transactions);

        if (empty($this->transactions)) {
            \core\event\manager::database_transaction_commited();
            \core\message\manager::database_transaction_commited();
        }
    }

    
    protected abstract function commit_transaction();

    
    public function rollback_delegated_transaction(moodle_transaction $transaction, $e) {
        if (!($e instanceof Exception) && !($e instanceof Throwable)) {
                        $e = new \coding_exception("Must be given an Exception or Throwable object!");
        }
        if ($transaction->is_disposed()) {
            throw new dml_transaction_exception('Transactions already disposed', $transaction);
        }
                $transaction->dispose();

                $this->force_rollback = true;

        if (empty($this->transactions) or $transaction !== $this->transactions[count($this->transactions) - 1]) {
                                    throw $e;
        }

        if (count($this->transactions) == 1) {
                        $this->rollback_transaction();
        }
        array_pop($this->transactions);
        if (empty($this->transactions)) {
                        $this->force_rollback = false;
            \core\event\manager::database_transaction_rolledback();
            \core\message\manager::database_transaction_rolledback();
        }
        throw $e;
    }

    
    protected abstract function rollback_transaction();

    
    public function force_transaction_rollback() {
        if ($this->transactions) {
            try {
                $this->rollback_transaction();
            } catch (dml_exception $e) {
                            }
        }

                $this->transactions = array();
        $this->force_rollback = false;

        \core\event\manager::database_transaction_rolledback();
        \core\message\manager::database_transaction_rolledback();
    }

    
    public function session_lock_supported() {
        return false;
    }

    
    public function get_session_lock($rowid, $timeout) {
        $this->used_for_db_sessions = true;
    }

    
    public function release_session_lock($rowid) {
    }

    
    public function perf_get_reads() {
        return $this->reads;
    }

    
    public function perf_get_writes() {
        return $this->writes;
    }

    
    public function perf_get_queries() {
        return $this->writes + $this->reads;
    }

    
    public function perf_get_queries_time() {
        return $this->queriestime;
    }
}
