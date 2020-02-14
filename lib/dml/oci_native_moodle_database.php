<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/moodle_database.php');
require_once(__DIR__.'/oci_native_moodle_recordset.php');
require_once(__DIR__.'/oci_native_moodle_temptables.php');


class oci_native_moodle_database extends moodle_database {

    protected $oci     = null;

    
    private $last_stmt_error = null;
    
    private $commit_status = null;

    
    private $last_error_reporting;
    
    private $unique_session_id;

    
    public function driver_installed() {
        if (!extension_loaded('oci8')) {
            return get_string('ociextensionisnotpresentinphp', 'install');
        }
        return true;
    }

    
    public function get_dbfamily() {
        return 'oracle';
    }

    
    protected function get_dbtype() {
        return 'oci';
    }

    
    protected function get_dblibrary() {
        return 'native';
    }

    
    public function get_name() {
        return get_string('nativeoci', 'install');
    }

    
    public function get_configuration_help() {
        return get_string('nativeocihelp', 'install');
    }

    
    public function diagnose() {
        return null;
    }

    
    public function connect($dbhost, $dbuser, $dbpass, $dbname, $prefix, array $dboptions=null) {
        if ($prefix == '' and !$this->external) {
                        throw new dml_exception('prefixcannotbeempty', $this->get_dbfamily());
        }
        if (!$this->external and strlen($prefix) > 2) {
                        $a = (object)array('dbfamily'=>'oracle', 'maxlength'=>2);
            throw new dml_exception('prefixtoolong', $a);
        }

        $driverstatus = $this->driver_installed();

        if ($driverstatus !== true) {
            throw new dml_exception('dbdriverproblem', $driverstatus);
        }

                                        $this->commit_status = OCI_COMMIT_ON_SUCCESS;

        $this->store_settings($dbhost, $dbuser, $dbpass, $dbname, $prefix, $dboptions);
        unset($this->dboptions['dbsocket']);

                
        if (empty($this->dbhost)) {
                        $dbstring = $this->dbname;
        } else {
            if (empty($this->dboptions['dbport'])) {
                $this->dboptions['dbport'] = 1521;
            }
            $dbstring = '//'.$this->dbhost.':'.$this->dboptions['dbport'].'/'.$this->dbname;
        }

        ob_start();
        if (empty($this->dboptions['dbpersist'])) {
            $this->oci = oci_new_connect($this->dbuser, $this->dbpass, $dbstring, 'AL32UTF8');
        } else {
            $this->oci = oci_pconnect($this->dbuser, $this->dbpass, $dbstring, 'AL32UTF8');
        }
        $dberr = ob_get_contents();
        ob_end_clean();


        if ($this->oci === false) {
            $this->oci = null;
            $e = oci_error();
            if (isset($e['message'])) {
                $dberr = $e['message'];
            }
            throw new dml_connection_exception($dberr);
        }

                $this->query_log_prevent();

                if (!$this->oci_package_installed()) {
            try {
                $this->attempt_oci_package_install();
            } catch (Exception $e) {
                                            }
            if (!$this->oci_package_installed()) {
                throw new dml_exception('dbdriverproblem', 'Oracle PL/SQL Moodle support package MOODLELIB is not installed! Database administrator has to execute /lib/dml/oci_native_moodle_package.sql script.');
            }
        }

                $sql = 'SELECT DBMS_SESSION.UNIQUE_SESSION_ID() FROM DUAL';
        $this->query_start($sql, null, SQL_QUERY_AUX);
        $stmt = $this->parse_query($sql);
        $result = oci_execute($stmt, $this->commit_status);
        $this->query_end($result, $stmt);
        $records = null;
        oci_fetch_all($stmt, $records, 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
        oci_free_statement($stmt);
        $this->unique_session_id = reset($records[0]);

                
                $this->query_log_allow();

                $this->temptables = new oci_native_moodle_temptables($this, $this->unique_session_id);

        return true;
    }

    
    public function dispose() {
        parent::dispose();         if ($this->oci) {
            oci_close($this->oci);
            $this->oci = null;
        }
    }


    
    protected function query_start($sql, array $params=null, $type, $extrainfo=null) {
        parent::query_start($sql, $params, $type, $extrainfo);
                $this->last_error_reporting = error_reporting(0);
    }

    
    protected function query_end($result, $stmt=null) {
                error_reporting($this->last_error_reporting);
        if ($stmt and $result === false) {
                        if (is_resource($stmt)) {
                $e = oci_error($stmt);
                if ($e !== false) {
                    $this->last_stmt_error = $e['message'];
                }
            }
            oci_free_statement($stmt);
        }
        parent::query_end($result);
    }

    
    public function get_server_info() {
        static $info = null; 
        if (is_null($info)) {
            $this->query_start("--oci_server_version()", null, SQL_QUERY_AUX);
            $description = oci_server_version($this->oci);
            $this->query_end(true);
            preg_match('/(\d+\.)+\d+/', $description, $matches);
            $info = array('description'=>$description, 'version'=>$matches[0]);
        }

        return $info;
    }

    
    protected function fix_table_names($sql) {
        if (preg_match_all('/\{([a-z][a-z0-9_]*)\}/', $sql, $matches)) {
            foreach($matches[0] as $key=>$match) {
                $name = $matches[1][$key];
                if ($this->temptables && $this->temptables->is_temptable($name)) {
                    $sql = str_replace($match, $this->temptables->get_correct_name($name), $sql);
                } else {
                    $sql = str_replace($match, $this->prefix.$name, $sql);
                }
            }
        }
        return $sql;
    }

    
    protected function allowed_param_types() {
        return SQL_PARAMS_NAMED;
    }

    
    public function get_last_error() {
        $error = false;
                if (!empty($this->last_stmt_error)) {
            $error = $this->last_stmt_error;
            $this->last_stmt_error = null;
        } else {             $e = oci_error($this->oci);
            if ($e !== false) {
                $error = $e['message'];
            }
        }
        return $error;
    }

    
    protected function parse_query($sql) {
        $stmt = oci_parse($this->oci, $sql);
        if ($stmt == false) {
            throw new dml_connection_exception('Can not parse sql query');         }
        return $stmt;
    }

    
    protected function tweak_param_names($sql, array $params) {
        if (empty($params)) {
            return array($sql, $params);
        }

        $newparams = array();
        $searcharr = array();         foreach ($params as $name => $value) {
                        if (strlen($name) <= 28) {
                $newname = 'o_' . $name;
            } else {
                $newname = 'o_' . substr($name, 2);
            }
            $newparams[$newname] = $value;
            $searcharr[':' . $name] = ':' . $newname;
        }
                uksort($searcharr, array('oci_native_moodle_database', 'compare_by_length_desc'));

        $sql = str_replace(array_keys($searcharr), $searcharr, $sql);
        return array($sql, $newparams);
    }

    
    public function get_tables($usecache=true) {
        if ($usecache and $this->tables !== null) {
            return $this->tables;
        }
        $this->tables = array();
        $prefix = str_replace('_', "\\_", strtoupper($this->prefix));
        $sql = "SELECT TABLE_NAME
                  FROM CAT
                 WHERE TABLE_TYPE='TABLE'
                       AND TABLE_NAME NOT LIKE 'BIN\$%'
                       AND TABLE_NAME LIKE '$prefix%' ESCAPE '\\'";
        $this->query_start($sql, null, SQL_QUERY_AUX);
        $stmt = $this->parse_query($sql);
        $result = oci_execute($stmt, $this->commit_status);
        $this->query_end($result, $stmt);
        $records = null;
        oci_fetch_all($stmt, $records, 0, -1, OCI_ASSOC);
        oci_free_statement($stmt);
        $records = array_map('strtolower', $records['TABLE_NAME']);
        foreach ($records as $tablename) {
            if ($this->prefix !== false && $this->prefix !== '') {
                if (strpos($tablename, $this->prefix) !== 0) {
                    continue;
                }
                $tablename = substr($tablename, strlen($this->prefix));
            }
            $this->tables[$tablename] = $tablename;
        }

                $this->tables = array_merge($this->tables, $this->temptables->get_temptables());

        return $this->tables;
    }

    
    public function get_indexes($table) {
        $indexes = array();
        $tablename = strtoupper($this->prefix.$table);

        $sql = "SELECT i.INDEX_NAME, i.UNIQUENESS, c.COLUMN_POSITION, c.COLUMN_NAME, ac.CONSTRAINT_TYPE
                  FROM ALL_INDEXES i
                  JOIN ALL_IND_COLUMNS c ON c.INDEX_NAME=i.INDEX_NAME
             LEFT JOIN ALL_CONSTRAINTS ac ON (ac.TABLE_NAME=i.TABLE_NAME AND ac.CONSTRAINT_NAME=i.INDEX_NAME AND ac.CONSTRAINT_TYPE='P')
                 WHERE i.TABLE_NAME = '$tablename'
              ORDER BY i.INDEX_NAME, c.COLUMN_POSITION";

        $stmt = $this->parse_query($sql);
        $result = oci_execute($stmt, $this->commit_status);
        $this->query_end($result, $stmt);
        $records = null;
        oci_fetch_all($stmt, $records, 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
        oci_free_statement($stmt);

        foreach ($records as $record) {
            if ($record['CONSTRAINT_TYPE'] === 'P') {
                                continue;
            }
            $indexname = strtolower($record['INDEX_NAME']);
            if (!isset($indexes[$indexname])) {
                $indexes[$indexname] = array('primary' => ($record['CONSTRAINT_TYPE'] === 'P'),
                                             'unique'  => ($record['UNIQUENESS'] === 'UNIQUE'),
                                             'columns' => array());
            }
            $indexes[$indexname]['columns'][] = strtolower($record['COLUMN_NAME']);
        }

        return $indexes;
    }

    
    public function get_columns($table, $usecache=true) {

        if ($usecache) {
            if ($this->temptables->is_temptable($table)) {
                if ($data = $this->get_temp_tables_cache()->get($table)) {
                    return $data;
                }
            } else {
                if ($data = $this->get_metacache()->get($table)) {
                    return $data;
                }
            }
        }

        if (!$table) {             return array();
        }

        $structure = array();

                                        $sql = "SELECT CNAME, COLTYPE, nvl(CHAR_LENGTH, WIDTH) AS WIDTH, SCALE, PRECISION, NULLS, DEFAULTVAL,
                  DECODE(NVL(TRIGGER_NAME, '0'), '0', '0', '1') HASTRIGGER
                  FROM COL c
             LEFT JOIN USER_TAB_COLUMNS u ON (u.TABLE_NAME = c.TNAME AND u.COLUMN_NAME = c.CNAME AND u.DATA_TYPE = 'VARCHAR2')
             LEFT JOIN USER_TRIGGERS t ON (t.TABLE_NAME = c.TNAME AND TRIGGER_TYPE = 'BEFORE EACH ROW' AND c.CNAME = 'ID')
                 WHERE TNAME = UPPER('{" . $table . "}')
              ORDER BY COLNO";

        list($sql, $params, $type) = $this->fix_sql_params($sql, null);

        $this->query_start($sql, null, SQL_QUERY_AUX);
        $stmt = $this->parse_query($sql);
        $result = oci_execute($stmt, $this->commit_status);
        $this->query_end($result, $stmt);
        $records = null;
        oci_fetch_all($stmt, $records, 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
        oci_free_statement($stmt);

        if (!$records) {
            return array();
        }
        foreach ($records as $rawcolumn) {
            $rawcolumn = (object)$rawcolumn;

            $info = new stdClass();
            $info->name = strtolower($rawcolumn->CNAME);
            $info->auto_increment = ((int)$rawcolumn->HASTRIGGER) ? true : false;
            $matches = null;

            if ($rawcolumn->COLTYPE === 'VARCHAR2'
             or $rawcolumn->COLTYPE === 'VARCHAR'
             or $rawcolumn->COLTYPE === 'NVARCHAR2'
             or $rawcolumn->COLTYPE === 'NVARCHAR'
             or $rawcolumn->COLTYPE === 'CHAR'
             or $rawcolumn->COLTYPE === 'NCHAR') {
                $info->type          = $rawcolumn->COLTYPE;
                $info->meta_type     = 'C';
                $info->max_length    = $rawcolumn->WIDTH;
                $info->scale         = null;
                $info->not_null      = ($rawcolumn->NULLS === 'NOT NULL');
                $info->has_default   = !is_null($rawcolumn->DEFAULTVAL);
                if ($info->has_default) {

                                        if ($rawcolumn->DEFAULTVAL === 'NULL') {
                        $info->default_value = null;
                    } else if ($rawcolumn->DEFAULTVAL === "' ' ") {                         $info->default_value = "";
                    } else if ($rawcolumn->DEFAULTVAL === "' '") {                         $info->default_value = "";
                    } else {
                        $info->default_value = trim($rawcolumn->DEFAULTVAL);                         $info->default_value = substr($info->default_value, 1, strlen($info->default_value)-2);                     }
                } else {
                    $info->default_value = null;
                }
                $info->primary_key   = false;
                $info->binary        = false;
                $info->unsigned      = null;
                $info->unique        = null;

            } else if ($rawcolumn->COLTYPE === 'NUMBER') {
                $info->type       = $rawcolumn->COLTYPE;
                $info->max_length = $rawcolumn->PRECISION;
                $info->binary     = false;
                if (!is_null($rawcolumn->SCALE) && $rawcolumn->SCALE == 0) {                                         if ($info->name === 'id') {
                        $info->primary_key   = true;
                        $info->meta_type     = 'R';
                        $info->unique        = true;
                        $info->has_default   = false;
                    } else {
                        $info->primary_key   = false;
                        $info->meta_type     = 'I';
                        $info->unique        = null;
                    }
                    $info->scale = 0;

                } else {
                                        $info->meta_type     = 'N';
                    $info->primary_key   = false;
                    $info->unsigned      = null;
                    $info->unique        = null;
                    $info->scale         = $rawcolumn->SCALE;
                }
                $info->not_null      = ($rawcolumn->NULLS === 'NOT NULL');
                $info->has_default   = !is_null($rawcolumn->DEFAULTVAL);
                if ($info->has_default) {
                    $info->default_value = trim($rawcolumn->DEFAULTVAL);                 } else {
                    $info->default_value = null;
                }

            } else if ($rawcolumn->COLTYPE === 'FLOAT') {
                $info->type       = $rawcolumn->COLTYPE;
                $info->max_length = (int)($rawcolumn->PRECISION * 3.32193);
                $info->primary_key   = false;
                $info->meta_type     = 'N';
                $info->unique        = null;
                $info->not_null      = ($rawcolumn->NULLS === 'NOT NULL');
                $info->has_default   = !is_null($rawcolumn->DEFAULTVAL);
                if ($info->has_default) {
                    $info->default_value = trim($rawcolumn->DEFAULTVAL);                 } else {
                    $info->default_value = null;
                }

            } else if ($rawcolumn->COLTYPE === 'CLOB'
                    or $rawcolumn->COLTYPE === 'NCLOB') {
                $info->type          = $rawcolumn->COLTYPE;
                $info->meta_type     = 'X';
                $info->max_length    = -1;
                $info->scale         = null;
                $info->scale         = null;
                $info->not_null      = ($rawcolumn->NULLS === 'NOT NULL');
                $info->has_default   = !is_null($rawcolumn->DEFAULTVAL);
                if ($info->has_default) {
                                        if ($rawcolumn->DEFAULTVAL === 'NULL') {
                        $info->default_value = null;
                    } else if ($rawcolumn->DEFAULTVAL === "' ' ") {                         $info->default_value = "";
                    } else if ($rawcolumn->DEFAULTVAL === "' '") {                         $info->default_value = "";
                    } else {
                        $info->default_value = trim($rawcolumn->DEFAULTVAL);                         $info->default_value = substr($info->default_value, 1, strlen($info->default_value)-2);                     }
                } else {
                    $info->default_value = null;
                }
                $info->primary_key   = false;
                $info->binary        = false;
                $info->unsigned      = null;
                $info->unique        = null;

            } else if ($rawcolumn->COLTYPE === 'BLOB') {
                $info->type          = $rawcolumn->COLTYPE;
                $info->meta_type     = 'B';
                $info->max_length    = -1;
                $info->scale         = null;
                $info->scale         = null;
                $info->not_null      = ($rawcolumn->NULLS === 'NOT NULL');
                $info->has_default   = !is_null($rawcolumn->DEFAULTVAL);
                if ($info->has_default) {
                                        if ($rawcolumn->DEFAULTVAL === 'NULL') {
                        $info->default_value = null;
                    } else if ($rawcolumn->DEFAULTVAL === "' ' ") {                         $info->default_value = "";
                    } else if ($rawcolumn->DEFAULTVAL === "' '") {                         $info->default_value = "";
                    } else {
                        $info->default_value = trim($rawcolumn->DEFAULTVAL);                         $info->default_value = substr($info->default_value, 1, strlen($info->default_value)-2);                     }
                } else {
                    $info->default_value = null;
                }
                $info->primary_key   = false;
                $info->binary        = true;
                $info->unsigned      = null;
                $info->unique        = null;

            } else {
                                $info->type          = $rawcolumn->COLTYPE;
                $info->meta_type     = '?';
            }

            $structure[$info->name] = new database_column_info($info);
        }

        if ($usecache) {
            if ($this->temptables->is_temptable($table)) {
                $this->get_temp_tables_cache()->set($table, $structure);
            } else {
                $this->get_metacache()->set($table, $structure);
            }
        }

        return $structure;
    }

    
    protected function normalise_value($column, $value) {
        $this->detect_objects($value);

        if (is_bool($value)) {             $value = (int)$value;

        } else if ($column->meta_type == 'B') {             if (!is_null($value)) {                             $value = array('blob' => $value);
            }

        } else if ($column->meta_type == 'X' && strlen($value) > 4000) {             if (!is_null($value)) {                                                      $value = array('clob' => (string)$value);                            }

        } else if ($value === '') {
            if ($column->meta_type == 'I' or $column->meta_type == 'F' or $column->meta_type == 'N') {
                $value = 0;             }
        }
        return $value;
    }

    
    private function get_limit_sql($sql, array $params = null, $limitfrom=0, $limitnum=0) {

        list($limitfrom, $limitnum) = $this->normalise_limit_from_num($limitfrom, $limitnum);
        
        if ($limitfrom and $limitnum) {
            $sql = "SELECT oracle_o.*
                      FROM (SELECT oracle_i.*, rownum AS oracle_rownum
                              FROM ($sql) oracle_i
                             WHERE rownum <= :oracle_num_rows
                            ) oracle_o
                     WHERE oracle_rownum > :oracle_skip_rows";
            $params['oracle_num_rows'] = $limitfrom + $limitnum;
            $params['oracle_skip_rows'] = $limitfrom;

        } else if ($limitfrom and !$limitnum) {
            $sql = "SELECT oracle_o.*
                      FROM (SELECT oracle_i.*, rownum AS oracle_rownum
                              FROM ($sql) oracle_i
                            ) oracle_o
                     WHERE oracle_rownum > :oracle_skip_rows";
            $params['oracle_skip_rows'] = $limitfrom;

        } else if (!$limitfrom and $limitnum) {
            $sql = "SELECT *
                      FROM ($sql)
                     WHERE rownum <= :oracle_num_rows";
            $params['oracle_num_rows'] = $limitnum;
        }

        return array($sql, $params);
    }

    
    private function oracle_dirty_hack ($table, $field, $value) {

                if (!$table) {
            if ($value === '') {
                return ' ';
            } else if (is_bool($value)) {
                return (int)$value;
            } else {
                return $value;
            }
        }

                $columns = $this->get_columns($table);
        if (!isset($columns[$field])) {
            if ($value === '') {
                return ' ';
            } else if (is_bool($value)) {
                return (int)$value;
            } else {
                return $value;
            }
        }
        $column = $columns[$field];

                                                                                                
                                                                                                                                                                                                
                if ($column->meta_type != 'C' and $column->meta_type != 'X') {
            return $value;
        }

                if (!empty($value)) {
            return $value;
        }

                
                if ($value === '0') {
            return $value;
        }

                if (gettype($value) == 'boolean') {
            return '0'; 
        } else if (gettype($value) == 'integer') {
            return '0'; 
        } else if ($value === '') {
            return ' ';                                 }

                return $value;
    }

    
    private function compare_by_length_desc($a, $b) {
        return strlen($b) - strlen($a);
    }

    
    public function setup_is_unicodedb() {
        $sql = "SELECT VALUE
                  FROM NLS_DATABASE_PARAMETERS
                 WHERE PARAMETER = 'NLS_CHARACTERSET'";
        $this->query_start($sql, null, SQL_QUERY_AUX);
        $stmt = $this->parse_query($sql);
        $result = oci_execute($stmt, $this->commit_status);
        $this->query_end($result, $stmt);
        $records = null;
        oci_fetch_all($stmt, $records, 0, -1, OCI_FETCHSTATEMENT_BY_COLUMN);
        oci_free_statement($stmt);

        return (isset($records['VALUE'][0]) and $records['VALUE'][0] === 'AL32UTF8');
    }

    
    public function change_database_structure($sql, $tablenames = null) {
        $this->get_manager();         $sqls = (array)$sql;

        try {
            foreach ($sqls as $sql) {
                $this->query_start($sql, null, SQL_QUERY_STRUCTURE);
                $stmt = $this->parse_query($sql);
                $result = oci_execute($stmt, $this->commit_status);
                $this->query_end($result, $stmt);
                oci_free_statement($stmt);
            }
        } catch (ddl_change_structure_exception $e) {
            $this->reset_caches($tablenames);
            throw $e;
        }

        $this->reset_caches($tablenames);
        return true;
    }

    protected function bind_params($stmt, array &$params=null, $tablename=null, array &$descriptors = null) {
        if ($params) {
            $columns = array();
            if ($tablename) {
                $columns = $this->get_columns($tablename);
            }
            foreach($params as $key => $value) {
                                if ($key == 'o_newfieldtoset') {                     $columnname   = key($value);                        $params[$key] = $value[$columnname];                     $value        = $value[$columnname];                 } else {
                    $columnname = preg_replace('/^o_/', '', $key);                 }
                                                if (is_array($value)) {                     if (isset($value['clob'])) {
                        $lob = oci_new_descriptor($this->oci, OCI_DTYPE_LOB);
                        if ($descriptors === null) {
                            throw new coding_exception('moodle_database::bind_params() $descriptors not specified for clob');
                        }
                        $descriptors[] = $lob;
                        oci_bind_by_name($stmt, $key, $lob, -1, SQLT_CLOB);
                        $lob->writeTemporary($this->oracle_dirty_hack($tablename, $columnname, $params[$key]['clob']), OCI_TEMP_CLOB);
                        continue;                     } else if (isset($value['blob'])) {
                        $lob = oci_new_descriptor($this->oci, OCI_DTYPE_LOB);
                        if ($descriptors === null) {
                            throw new coding_exception('moodle_database::bind_params() $descriptors not specified for clob');
                        }
                        $descriptors[] = $lob;
                        oci_bind_by_name($stmt, $key, $lob, -1, SQLT_BLOB);
                        $lob->writeTemporary($params[$key]['blob'], OCI_TEMP_BLOB);
                        continue;                     }
                } else {
                                                                                                    if (strlen($value) > 4000) {
                        $lob = oci_new_descriptor($this->oci, OCI_DTYPE_LOB);
                        if ($descriptors === null) {
                            throw new coding_exception('moodle_database::bind_params() $descriptors not specified for clob');
                        }
                        $descriptors[] = $lob;
                        oci_bind_by_name($stmt, $key, $lob, -1, SQLT_CLOB);
                        $lob->writeTemporary($this->oracle_dirty_hack($tablename, $columnname, $params[$key]), OCI_TEMP_CLOB);
                        continue;                     }
                }
                                                if (isset($columns[$columnname])) {
                    $type = $columns[$columnname]->meta_type;
                    $maxlength = $columns[$columnname]->max_length;
                } else {
                    $type = '?';
                    $maxlength = -1;
                }
                switch ($type) {
                    case 'I':
                    case 'R':
                                                oci_bind_by_name($stmt, $key, $params[$key]);
                        break;

                    case 'N':
                    case 'F':
                                                oci_bind_by_name($stmt, $key, $params[$key]);
                        break;

                    case 'B':
                                                
                    case 'X':
                                                
                    default:                                                 $params[$key] = $this->oracle_dirty_hack($tablename, $columnname, $params[$key]);
                                                                                                if ($params[$key] === null && version_compare(PHP_VERSION, '7.0.0', '>=')) {
                            $params[$key] = '';
                        }
                        oci_bind_by_name($stmt, $key, $params[$key]);
                }
            }
        }
        return $descriptors;
    }

    protected function free_descriptors($descriptors) {
        foreach ($descriptors as $descriptor) {
                                    $descriptor->close();
                        oci_free_descriptor($descriptor);
        }
    }

    
    public static function onespace2empty(&$item, $key=null) {
        $item = ($item === ' ') ? '' : $item;
        return true;
    }

    
    public function execute($sql, array $params=null) {
        list($sql, $params, $type) = $this->fix_sql_params($sql, $params);

        if (strpos($sql, ';') !== false) {
            throw new coding_exception('moodle_database::execute() Multiple sql statements found or bound parameters not used properly in query!');
        }

        list($sql, $params) = $this->tweak_param_names($sql, $params);
        $this->query_start($sql, $params, SQL_QUERY_UPDATE);
        $stmt = $this->parse_query($sql);
        $descriptors = array();
        $this->bind_params($stmt, $params, null, $descriptors);
        $result = oci_execute($stmt, $this->commit_status);
        $this->free_descriptors($descriptors);
        $this->query_end($result, $stmt);
        oci_free_statement($stmt);

        return true;
    }

    
    public function get_record_sql($sql, array $params=null, $strictness=IGNORE_MISSING) {
        $strictness = (int)$strictness;
        if ($strictness == IGNORE_MULTIPLE) {
                        $rs = $this->get_recordset_sql($sql, $params);
            $result = false;
            foreach ($rs as $rec) {
                $result = $rec;
                break;
            }
            $rs->close();
            return $result;
        }
        return parent::get_record_sql($sql, $params, $strictness);
    }

    
    public function get_recordset_sql($sql, array $params=null, $limitfrom=0, $limitnum=0) {

        list($sql, $params, $type) = $this->fix_sql_params($sql, $params);

        list($rawsql, $params) = $this->get_limit_sql($sql, $params, $limitfrom, $limitnum);

        list($rawsql, $params) = $this->tweak_param_names($rawsql, $params);
        $this->query_start($rawsql, $params, SQL_QUERY_SELECT);
        $stmt = $this->parse_query($rawsql);
        $descriptors = array();
        $this->bind_params($stmt, $params, null, $descriptors);
        $result = oci_execute($stmt, $this->commit_status);
        $this->free_descriptors($descriptors);
        $this->query_end($result, $stmt);

        return $this->create_recordset($stmt);
    }

    protected function create_recordset($stmt) {
        return new oci_native_moodle_recordset($stmt);
    }

    
    public function get_records_sql($sql, array $params=null, $limitfrom=0, $limitnum=0) {

        list($sql, $params, $type) = $this->fix_sql_params($sql, $params);

        list($rawsql, $params) = $this->get_limit_sql($sql, $params, $limitfrom, $limitnum);

        list($rawsql, $params) = $this->tweak_param_names($rawsql, $params);
        $this->query_start($rawsql, $params, SQL_QUERY_SELECT);
        $stmt = $this->parse_query($rawsql);
        $descriptors = array();
        $this->bind_params($stmt, $params, null, $descriptors);
        $result = oci_execute($stmt, $this->commit_status);
        $this->free_descriptors($descriptors);
        $this->query_end($result, $stmt);

        $records = null;
        oci_fetch_all($stmt, $records, 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
        oci_free_statement($stmt);

        $return = array();

        foreach ($records as $row) {
            $row = array_change_key_case($row, CASE_LOWER);
            unset($row['oracle_rownum']);
            array_walk($row, array('oci_native_moodle_database', 'onespace2empty'));
            $id = reset($row);
            if (isset($return[$id])) {
                $colname = key($row);
                debugging("Did you remember to make the first column something unique in your call to get_records? Duplicate value '$id' found in column '$colname'.", DEBUG_DEVELOPER);
            }
            $return[$id] = (object)$row;
        }

        return $return;
    }

    
    public function get_fieldset_sql($sql, array $params=null) {
        list($sql, $params, $type) = $this->fix_sql_params($sql, $params);

        list($sql, $params) = $this->tweak_param_names($sql, $params);
        $this->query_start($sql, $params, SQL_QUERY_SELECT);
        $stmt = $this->parse_query($sql);
        $descriptors = array();
        $this->bind_params($stmt, $params, null, $descriptors);
        $result = oci_execute($stmt, $this->commit_status);
        $this->free_descriptors($descriptors);
        $this->query_end($result, $stmt);

        $records = null;
        oci_fetch_all($stmt, $records, 0, -1, OCI_FETCHSTATEMENT_BY_COLUMN);
        oci_free_statement($stmt);

        $return = reset($records);
        array_walk($return, array('oci_native_moodle_database', 'onespace2empty'));

        return $return;
    }

    
    public function insert_record_raw($table, $params, $returnid=true, $bulk=false, $customsequence=false) {
        if (!is_array($params)) {
            $params = (array)$params;
        }

        $returning = "";

        if ($customsequence) {
            if (!isset($params['id'])) {
                throw new coding_exception('moodle_database::insert_record_raw() id field must be specified if custom sequences used.');
            }
            $returnid = false;
        } else {
            unset($params['id']);
            if ($returnid) {
                $returning = " RETURNING id INTO :oracle_id";             }
        }

        if (empty($params)) {
            throw new coding_exception('moodle_database::insert_record_raw() no fields found.');
        }

        $fields = implode(',', array_keys($params));
        $values = array();
        foreach ($params as $pname => $value) {
            $values[] = ":$pname";
        }
        $values = implode(',', $values);

        $sql = "INSERT INTO {" . $table . "} ($fields) VALUES ($values)";
        list($sql, $params, $type) = $this->fix_sql_params($sql, $params);
        $sql .= $returning;

        $id = 0;

                        $this->query_start($sql, $params, SQL_QUERY_INSERT);
        $stmt = $this->parse_query($sql);
        if ($returning) {
            oci_bind_by_name($stmt, ":oracle_id", $id, 10, SQLT_INT);
        }
        $descriptors = array();
        $this->bind_params($stmt, $params, $table, $descriptors);
        $result = oci_execute($stmt, $this->commit_status);
        $this->free_descriptors($descriptors);
        $this->query_end($result, $stmt);
        oci_free_statement($stmt);

        if (!$returnid) {
            return true;
        }

        if (!$returning) {
            die('TODO - implement oracle 9.2 insert support');         }

        return (int)$id;
    }

    
    public function insert_record($table, $dataobject, $returnid=true, $bulk=false) {
        $dataobject = (array)$dataobject;

        $columns = $this->get_columns($table);
        if (empty($columns)) {
            throw new dml_exception('ddltablenotexist', $table);
        }

        $cleaned = array();

        foreach ($dataobject as $field=>$value) {
            if ($field === 'id') {
                continue;
            }
            if (!isset($columns[$field])) {                 continue;
            }
            $column = $columns[$field];
            $cleaned[$field] = $this->normalise_value($column, $value);
        }

        return $this->insert_record_raw($table, $cleaned, $returnid, $bulk);
    }

    
    public function import_record($table, $dataobject) {
        $dataobject = (array)$dataobject;

        $columns = $this->get_columns($table);
        $cleaned = array();

        foreach ($dataobject as $field=>$value) {
            if (!isset($columns[$field])) {
                continue;
            }
            $column = $columns[$field];
            $cleaned[$field] = $this->normalise_value($column, $value);
        }

        return $this->insert_record_raw($table, $cleaned, false, true, true);
    }

    
    public function update_record_raw($table, $params, $bulk=false) {
        $params = (array)$params;

        if (!isset($params['id'])) {
            throw new coding_exception('moodle_database::update_record_raw() id field must be specified.');
        }

        if (empty($params)) {
            throw new coding_exception('moodle_database::update_record_raw() no fields found.');
        }

        $sets = array();
        foreach ($params as $field=>$value) {
            if ($field == 'id') {
                continue;
            }
            $sets[] = "$field = :$field";
        }

        $sets = implode(',', $sets);
        $sql = "UPDATE {" . $table . "} SET $sets WHERE id=:id";
        list($sql, $params, $type) = $this->fix_sql_params($sql, $params);

                        $this->query_start($sql, $params, SQL_QUERY_UPDATE);
        $stmt = $this->parse_query($sql);
        $descriptors = array();
        $this->bind_params($stmt, $params, $table, $descriptors);
        $result = oci_execute($stmt, $this->commit_status);
        $this->free_descriptors($descriptors);
        $this->query_end($result, $stmt);
        oci_free_statement($stmt);

        return true;
    }

    
    public function update_record($table, $dataobject, $bulk=false) {
        $dataobject = (array)$dataobject;

        $columns = $this->get_columns($table);
        $cleaned = array();

        foreach ($dataobject as $field=>$value) {
            if (!isset($columns[$field])) {
                continue;
            }
            $column = $columns[$field];
            $cleaned[$field] = $this->normalise_value($column, $value);
        }

        $this->update_record_raw($table, $cleaned, $bulk);

        return true;
    }

    
    public function set_field_select($table, $newfield, $newvalue, $select, array $params=null) {

        if ($select) {
            $select = "WHERE $select";
        }
        if (is_null($params)) {
            $params = array();
        }

                $columns = $this->get_columns($table);
        $column = $columns[$newfield];

        $newvalue = $this->normalise_value($column, $newvalue);

        list($select, $params, $type) = $this->fix_sql_params($select, $params);

        if (is_bool($newvalue)) {
            $newvalue = (int)$newvalue;         }
        if (is_null($newvalue)) {
            $newsql = "$newfield = NULL";
        } else {
                                                                                    $params['newfieldtoset'] = array($newfield => $newvalue);
            $newsql = "$newfield = :newfieldtoset";
        }
        $sql = "UPDATE {" . $table . "} SET $newsql $select";
        list($sql, $params, $type) = $this->fix_sql_params($sql, $params);

        list($sql, $params) = $this->tweak_param_names($sql, $params);
        $this->query_start($sql, $params, SQL_QUERY_UPDATE);
        $stmt = $this->parse_query($sql);
        $descriptors = array();
        $this->bind_params($stmt, $params, $table, $descriptors);
        $result = oci_execute($stmt, $this->commit_status);
        $this->free_descriptors($descriptors);
        $this->query_end($result, $stmt);
        oci_free_statement($stmt);

        return true;
    }

    
    public function delete_records_select($table, $select, array $params=null) {

        if ($select) {
            $select = "WHERE $select";
        }

        $sql = "DELETE FROM {" . $table . "} $select";

        list($sql, $params, $type) = $this->fix_sql_params($sql, $params);

        list($sql, $params) = $this->tweak_param_names($sql, $params);
        $this->query_start($sql, $params, SQL_QUERY_UPDATE);
        $stmt = $this->parse_query($sql);
        $descriptors = array();
        $this->bind_params($stmt, $params, null, $descriptors);
        $result = oci_execute($stmt, $this->commit_status);
        $this->free_descriptors($descriptors);
        $this->query_end($result, $stmt);
        oci_free_statement($stmt);

        return true;
    }

    function sql_null_from_clause() {
        return ' FROM dual';
    }

    public function sql_bitand($int1, $int2) {
        return 'bitand((' . $int1 . '), (' . $int2 . '))';
    }

    public function sql_bitnot($int1) {
        return '((0 - (' . $int1 . ')) - 1)';
    }

    public function sql_bitor($int1, $int2) {
        return 'MOODLELIB.BITOR(' . $int1 . ', ' . $int2 . ')';
    }

    public function sql_bitxor($int1, $int2) {
        return 'MOODLELIB.BITXOR(' . $int1 . ', ' . $int2 . ')';
    }

    
    public function sql_modulo($int1, $int2) {
        return 'MOD(' . $int1 . ', ' . $int2 . ')';
    }

    public function sql_cast_char2int($fieldname, $text=false) {
        if (!$text) {
            return ' CAST(' . $fieldname . ' AS INT) ';
        } else {
            return ' CAST(' . $this->sql_compare_text($fieldname) . ' AS INT) ';
        }
    }

    public function sql_cast_char2real($fieldname, $text=false) {
        if (!$text) {
            return ' CAST(' . $fieldname . ' AS FLOAT) ';
        } else {
            return ' CAST(' . $this->sql_compare_text($fieldname) . ' AS FLOAT) ';
        }
    }

    
    public function sql_like($fieldname, $param, $casesensitive = true, $accentsensitive = true, $notlike = false, $escapechar = '\\') {
        if (strpos($param, '%') !== false) {
            debugging('Potential SQL injection detected, sql_like() expects bound parameters (? or :named)');
        }

        $LIKE = $notlike ? 'NOT LIKE' : 'LIKE';

        
        if ($casesensitive) {
            return "$fieldname $LIKE $param ESCAPE '$escapechar'";
        } else {
            return "LOWER($fieldname) $LIKE LOWER($param) ESCAPE '$escapechar'";
        }
    }

    public function sql_concat() {
        $arr = func_get_args();
        if (empty($arr)) {
            return " ' ' ";
        }
        foreach ($arr as $k => $v) {
            if ($v === "' '") {
                $arr[$k] = "'*OCISP*'";             }
        }
        $s = $this->recursive_concat($arr);
        return " MOODLELIB.UNDO_MEGA_HACK($s) ";
    }

    public function sql_concat_join($separator="' '", $elements = array()) {
        if ($separator === "' '") {
            $separator = "'*OCISP*'";         }
        foreach ($elements as $k => $v) {
            if ($v === "' '") {
                $elements[$k] = "'*OCISP*'";             }
        }
        for ($n = count($elements)-1; $n > 0 ; $n--) {
            array_splice($elements, $n, 0, $separator);
        }
        if (empty($elements)) {
            return " ' ' ";
        }
        $s = $this->recursive_concat($elements);
        return " MOODLELIB.UNDO_MEGA_HACK($s) ";
    }

    
    public function get_in_or_equal($items, $type=SQL_PARAMS_QM, $prefix='param', $equal=true, $onemptyitems=false) {
        list($sql, $params) = parent::get_in_or_equal($items, $type, $prefix,  $equal, $onemptyitems);

                if (count($params) < 1000) {
            return array($sql, $params);         }

                if (preg_match('!(^.*IN \()([^\)]*)(.*)$!', $sql, $matches) === false) {
            return array($sql, $params);         }

        $instart = $matches[1];
        $insql = $matches[2];
        $inend = $matches[3];
        $newsql = '';

                $insqlarr = explode(',', $insql);
        if (count($insqlarr) !== count($params)) {
            return array($sql, $params);         }

                $addunionclause = false;
        while ($chunk = array_splice($insqlarr, 0, 125)) {             $chunksize = count($chunk);
            if ($addunionclause) {
                $newsql .= "\n    UNION ALL";
            }
            $newsql .= "\n        SELECT DECODE(pivot";
            $counter = 1;
            foreach ($chunk as $element) {
                $newsql .= ",\n            {$counter}, " . trim($element);
                $counter++;
            }
            $newsql .= ")";
            $newsql .= "\n        FROM dual";
            $newsql .= "\n        CROSS JOIN (SELECT LEVEL AS pivot FROM dual CONNECT BY LEVEL <= {$chunksize})";
            $addunionclause = true;
        }

                return array($instart . $newsql . $inend, $params);
    }

    
    protected function recursive_concat(array $args) {
        $count = count($args);
        if ($count == 1) {
            $arg = reset($args);
            return $arg;
        }
        if ($count == 2) {
            $args[] = "' '";
                    }
        $first = array_shift($args);
        $second = array_shift($args);
        $third = $this->recursive_concat($args);
        return "MOODLELIB.TRICONCAT($first, $second, $third)";
    }

    
    public function sql_position($needle, $haystack) {
        return "INSTR(($haystack), ($needle))";
    }

    
    public function sql_isempty($tablename, $fieldname, $nullablefield, $textfield) {
        if ($textfield) {
            return " (".$this->sql_compare_text($fieldname)." = ' ') ";
        } else {
            return " ($fieldname = ' ') ";
        }
    }

    public function sql_order_by_text($fieldname, $numchars=32) {
        return 'dbms_lob.substr(' . $fieldname . ', ' . $numchars . ',1)';
    }

    
    protected function oci_package_installed() {
        $sql = "SELECT 1
                FROM user_objects
                WHERE object_type = 'PACKAGE BODY'
                  AND object_name = 'MOODLELIB'
                  AND status = 'VALID'";
        $this->query_start($sql, null, SQL_QUERY_AUX);
        $stmt = $this->parse_query($sql);
        $result = oci_execute($stmt, $this->commit_status);
        $this->query_end($result, $stmt);
        $records = null;
        oci_fetch_all($stmt, $records, 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
        oci_free_statement($stmt);
        return isset($records[0]) && reset($records[0]) ? true : false;
    }

    
    protected function attempt_oci_package_install() {
        $sqls = file_get_contents(__DIR__.'/oci_native_moodle_package.sql');
        $sqls = preg_split('/^\/$/sm', $sqls);
        foreach ($sqls as $sql) {
            $sql = trim($sql);
            if ($sql === '' or $sql === 'SHOW ERRORS') {
                continue;
            }
            $this->change_database_structure($sql);
        }
    }

    
    public function replace_all_text_supported() {
        return true;
    }

    public function session_lock_supported() {
        return true;
    }

    
    public function get_session_lock($rowid, $timeout) {
        parent::get_session_lock($rowid, $timeout);

        $fullname = $this->dbname.'-'.$this->prefix.'-session-'.$rowid;
        $sql = 'SELECT MOODLELIB.GET_LOCK(:lockname, :locktimeout) FROM DUAL';
        $params = array('lockname' => $fullname , 'locktimeout' => $timeout);
        $this->query_start($sql, $params, SQL_QUERY_AUX);
        $stmt = $this->parse_query($sql);
        $this->bind_params($stmt, $params);
        $result = oci_execute($stmt, $this->commit_status);
        if ($result === false) {             throw new dml_sessionwait_exception();
        }
        $this->query_end($result, $stmt);
        oci_free_statement($stmt);
    }

    public function release_session_lock($rowid) {
        if (!$this->used_for_db_sessions) {
            return;
        }

        parent::release_session_lock($rowid);

        $fullname = $this->dbname.'-'.$this->prefix.'-session-'.$rowid;
        $params = array('lockname' => $fullname);
        $sql = 'SELECT MOODLELIB.RELEASE_LOCK(:lockname) FROM DUAL';
        $this->query_start($sql, $params, SQL_QUERY_AUX);
        $stmt = $this->parse_query($sql);
        $this->bind_params($stmt, $params);
        $result = oci_execute($stmt, $this->commit_status);
        $this->query_end($result, $stmt);
        oci_free_statement($stmt);
    }

    
    protected function begin_transaction() {
        $this->commit_status = OCI_DEFAULT;     }

    
    protected function commit_transaction() {
        $this->query_start('--oracle_commit', NULL, SQL_QUERY_AUX);
        $result = oci_commit($this->oci);
        $this->commit_status = OCI_COMMIT_ON_SUCCESS;
        $this->query_end($result);
    }

    
    protected function rollback_transaction() {
        $this->query_start('--oracle_rollback', NULL, SQL_QUERY_AUX);
        $result = oci_rollback($this->oci);
        $this->commit_status = OCI_COMMIT_ON_SUCCESS;
        $this->query_end($result);
    }
}
