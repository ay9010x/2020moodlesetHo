<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/moodle_database.php');
require_once(__DIR__.'/sqlsrv_native_moodle_recordset.php');
require_once(__DIR__.'/sqlsrv_native_moodle_temptables.php');


class sqlsrv_native_moodle_database extends moodle_database {

    protected $sqlsrv = null;
    protected $last_error_reporting;     protected $temptables;     protected $collation;      
    protected $supportsoffsetfetch;

    
    protected $recordsets = array();

    
    public function __construct($external=false) {
        parent::__construct($external);
    }

    
    public function driver_installed() {
                                if (!function_exists('sqlsrv_num_rows')) {
            if (stripos(PHP_OS, 'win') === 0) {
                return get_string('nativesqlsrvnodriver', 'install');
            } else {
                return get_string('nativesqlsrvnonwindows', 'install');
            }
        }
        return true;
    }

    
    public function get_dbfamily() {
        return 'mssql';
    }

   
    protected function get_dbtype() {
        return 'sqlsrv';
    }

   
    protected function get_dblibrary() {
        return 'native';
    }

    
    public function get_name() {
        return get_string('nativesqlsrv', 'install');
    }

    
    public function get_configuration_help() {
        return get_string('nativesqlsrvhelp', 'install');
    }

    
    public function diagnose() {
                        $correctrcsmode = false;
        $sql = "SELECT is_read_committed_snapshot_on
                  FROM sys.databases
                 WHERE name = '{$this->dbname}'";
        $this->query_start($sql, null, SQL_QUERY_AUX);
        $result = sqlsrv_query($this->sqlsrv, $sql);
        $this->query_end($result);
        if ($result) {
            if ($row = sqlsrv_fetch_array($result)) {
                $correctrcsmode = (bool)reset($row);
            }
        }
        $this->free_result($result);

        if (!$correctrcsmode) {
            return get_string('mssqlrcsmodemissing', 'error');
        }

                return null;
    }

    
    public function connect($dbhost, $dbuser, $dbpass, $dbname, $prefix, array $dboptions=null) {
        if ($prefix == '' and !$this->external) {
                        throw new dml_exception('prefixcannotbeempty', $this->get_dbfamily());
        }

        $driverstatus = $this->driver_installed();

        if ($driverstatus !== true) {
            throw new dml_exception('dbdriverproblem', $driverstatus);
        }

        
        sqlsrv_configure("WarningsReturnAsErrors", FALSE);
        sqlsrv_configure("LogSubsystems", SQLSRV_LOG_SYSTEM_OFF);
        sqlsrv_configure("LogSeverity", SQLSRV_LOG_SEVERITY_ERROR);

        $this->store_settings($dbhost, $dbuser, $dbpass, $dbname, $prefix, $dboptions);
        $this->sqlsrv = sqlsrv_connect($this->dbhost, array
         (
          'UID' => $this->dbuser,
          'PWD' => $this->dbpass,
          'Database' => $this->dbname,
          'CharacterSet' => 'UTF-8',
          'MultipleActiveResultSets' => true,
          'ConnectionPooling' => !empty($this->dboptions['dbpersist']),
          'ReturnDatesAsStrings' => true,
         ));

        if ($this->sqlsrv === false) {
            $this->sqlsrv = null;
            $dberr = $this->get_last_error();

            throw new dml_connection_exception($dberr);
        }

                $this->query_log_prevent();

                $sql = "SET QUOTED_IDENTIFIER ON";
        $this->query_start($sql, null, SQL_QUERY_AUX);
        $result = sqlsrv_query($this->sqlsrv, $sql);
        $this->query_end($result);

        $this->free_result($result);

                        $sql = "SET ANSI_NULLS ON";
        $this->query_start($sql, null, SQL_QUERY_AUX);
        $result = sqlsrv_query($this->sqlsrv, $sql);
        $this->query_end($result);

        $this->free_result($result);

                        $sql = "SET ANSI_WARNINGS ON";
        $this->query_start($sql, null, SQL_QUERY_AUX);
        $result = sqlsrv_query($this->sqlsrv, $sql);
        $this->query_end($result);

                $sql = "SET CONCAT_NULL_YIELDS_NULL  ON";
        $this->query_start($sql, null, SQL_QUERY_AUX);
        $result = sqlsrv_query($this->sqlsrv, $sql);
        $this->query_end($result);

        $this->free_result($result);

                                $sql = "SET TRANSACTION ISOLATION LEVEL READ COMMITTED";
        $this->query_start($sql, NULL, SQL_QUERY_AUX);
        $result = sqlsrv_query($this->sqlsrv, $sql);
        $this->query_end($result);

        $this->free_result($result);

        $serverinfo = $this->get_server_info();
                $this->supportsoffsetfetch = $serverinfo['version'] > '11';

                $this->query_log_allow();

                $this->temptables = new sqlsrv_native_moodle_temptables($this);

        return true;
    }

    
    public function dispose() {
        parent::dispose(); 
        if ($this->sqlsrv) {
            sqlsrv_close($this->sqlsrv);
            $this->sqlsrv = null;
        }
    }

    
    protected function query_start($sql, array $params = null, $type, $extrainfo = null) {
        parent::query_start($sql, $params, $type, $extrainfo);
    }

    
    protected function query_end($result) {
        parent::query_end($result);
    }

    
    public function get_server_info() {
        static $info;

        if (!$info) {
            $server_info = sqlsrv_server_info($this->sqlsrv);

            if ($server_info) {
                $info['description'] = $server_info['SQLServerName'];
                $info['version'] = $server_info['SQLServerVersion'];
                $info['database'] = $server_info['CurrentDatabase'];
            }
        }
        return $info;
    }

    
    protected function fix_table_names($sql) {
        if (preg_match_all('/\{([a-z][a-z0-9_]*)\}/i', $sql, $matches)) {
            foreach ($matches[0] as $key => $match) {
                $name = $matches[1][$key];

                if ($this->temptables->is_temptable($name)) {
                    $sql = str_replace($match, $this->temptables->get_correct_name($name), $sql);
                } else {
                    $sql = str_replace($match, $this->prefix.$name, $sql);
                }
            }
        }
        return $sql;
    }

    
    protected function allowed_param_types() {
        return SQL_PARAMS_QM;      }

    
    public function get_last_error() {
        $retErrors = sqlsrv_errors(SQLSRV_ERR_ALL);
        $errorMessage = 'No errors found';

        if ($retErrors != null) {
            $errorMessage = '';

            foreach ($retErrors as $arrError) {
                $errorMessage .= "SQLState: ".$arrError['SQLSTATE']."<br>\n";
                $errorMessage .= "Error Code: ".$arrError['code']."<br>\n";
                $errorMessage .= "Message: ".$arrError['message']."<br>\n";
            }
        }

        return $errorMessage;
    }

    
    private function do_query($sql, $params, $sql_query_type, $free_result = true, $scrollable = false) {
        list($sql, $params, $type) = $this->fix_sql_params($sql, $params);

        

        $sql = $this->emulate_bound_params($sql, $params);
        $this->query_start($sql, $params, $sql_query_type);
        if (!$scrollable) {             $result = sqlsrv_query($this->sqlsrv, $sql);
        } else {             $result = sqlsrv_query($this->sqlsrv, $sql, array(), array('Scrollable' => SQLSRV_CURSOR_STATIC));
        }

        if ($result === false) {
                        $dberr = $this->get_last_error();
        }

        $this->query_end($result);

        if ($free_result) {
            $this->free_result($result);
            return true;
        }
        return $result;
    }

    
    public function get_tables($usecache = true) {
        if ($usecache and count($this->tables) > 0) {
            return $this->tables;
        }
        $this->tables = array ();
        $prefix = str_replace('_', '\\_', $this->prefix);
        $sql = "SELECT table_name
                  FROM INFORMATION_SCHEMA.TABLES
                 WHERE table_name LIKE '$prefix%' ESCAPE '\\' AND table_type = 'BASE TABLE'";

        $this->query_start($sql, null, SQL_QUERY_AUX);
        $result = sqlsrv_query($this->sqlsrv, $sql);
        $this->query_end($result);

        if ($result) {
            while ($row = sqlsrv_fetch_array($result)) {
                $tablename = reset($row);
                if ($this->prefix !== false && $this->prefix !== '') {
                    if (strpos($tablename, $this->prefix) !== 0) {
                        continue;
                    }
                    $tablename = substr($tablename, strlen($this->prefix));
                }
                $this->tables[$tablename] = $tablename;
            }
            $this->free_result($result);
        }

                $this->tables = array_merge($this->tables, $this->temptables->get_temptables());
        return $this->tables;
    }

    
    public function get_indexes($table) {
        $indexes = array ();
        $tablename = $this->prefix.$table;

                        $sql = "SELECT i.name AS index_name, i.is_unique, ic.index_column_id, c.name AS column_name
                  FROM sys.indexes i
                  JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
                  JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
                  JOIN sys.tables t ON i.object_id = t.object_id
                 WHERE t.name = '$tablename' AND i.is_primary_key = 0
              ORDER BY i.name, i.index_id, ic.index_column_id";

        $this->query_start($sql, null, SQL_QUERY_AUX);
        $result = sqlsrv_query($this->sqlsrv, $sql);
        $this->query_end($result);

        if ($result) {
            $lastindex = '';
            $unique = false;
            $columns = array ();

            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                if ($lastindex and $lastindex != $row['index_name'])
                    {                     $indexes[$lastindex] = array
                     (
                      'unique' => $unique,
                      'columns' => $columns
                     );

                    $unique = false;
                    $columns = array ();
                }
                $lastindex = $row['index_name'];
                $unique = empty($row['is_unique']) ? false : true;
                $columns[] = $row['column_name'];
            }

            if ($lastindex) {                 $indexes[$lastindex] = array
                 (
                  'unique' => $unique,
                  'columns' => $columns
                 );
            }

            $this->free_result($result);
        }
        return $indexes;
    }

    
    public function get_columns($table, $usecache = true) {
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

        $structure = array();

        if (!$this->temptables->is_temptable($table)) {             $sql = "SELECT column_name AS name,
                           data_type AS type,
                           numeric_precision AS max_length,
                           character_maximum_length AS char_max_length,
                           numeric_scale AS scale,
                           is_nullable AS is_nullable,
                           columnproperty(object_id(quotename(table_schema) + '.' + quotename(table_name)), column_name, 'IsIdentity') AS auto_increment,
                           column_default AS default_value
                      FROM INFORMATION_SCHEMA.COLUMNS
                     WHERE table_name = '{".$table."}'
                  ORDER BY ordinal_position";
        } else {             $sql = "SELECT column_name AS name,
                           data_type AS type,
                           numeric_precision AS max_length,
                           character_maximum_length AS char_max_length,
                           numeric_scale AS scale,
                           is_nullable AS is_nullable,
                           columnproperty(object_id(quotename(table_schema) + '.' + quotename(table_name)), column_name, 'IsIdentity') AS auto_increment,
                           column_default AS default_value
                      FROM tempdb.INFORMATION_SCHEMA.COLUMNS ".
                                                        "WHERE table_name LIKE '{".$table."}__________%'
                  ORDER BY ordinal_position";
        }

        list($sql, $params, $type) = $this->fix_sql_params($sql, null);

        $this->query_start($sql, null, SQL_QUERY_AUX);
        $result = sqlsrv_query($this->sqlsrv, $sql);
        $this->query_end($result);

        if (!$result) {
            return array ();
        }

        while ($rawcolumn = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {

            $rawcolumn = (object)$rawcolumn;

            $info = new stdClass();
            $info->name = $rawcolumn->name;
            $info->type = $rawcolumn->type;
            $info->meta_type = $this->sqlsrvtype2moodletype($info->type);

                        $info->auto_increment = $rawcolumn->auto_increment ? true : false;

                        $info->meta_type = ($info->auto_increment && $info->meta_type == 'I') ? 'R' : $info->meta_type;

                        $info->primary_key = ($info->name == 'id' && $info->meta_type == 'R' && $info->auto_increment);

            if ($info->meta_type === 'C' and $rawcolumn->char_max_length == -1) {
                                $info->max_length = -1;
                $info->meta_type = 'X';
            } else {
                                $info->max_length = $info->meta_type == 'C' ? $rawcolumn->char_max_length : $rawcolumn->max_length;
                $info->max_length = ($info->meta_type == 'X' || $info->meta_type == 'B') ? -1 : $info->max_length;
            }

                        $info->scale = $rawcolumn->scale;

                        $info->not_null = $rawcolumn->is_nullable == 'NO' ? true : false;

                        $info->has_default = !empty($rawcolumn->default_value);
            if ($rawcolumn->default_value === NULL) {
                $info->default_value = NULL;
            } else {
                $info->default_value = preg_replace("/^[\(N]+[']?(.*?)[']?[\)]+$/", '\\1', $rawcolumn->default_value);
            }

                        $info->binary = $info->meta_type == 'B' ? true : false;

            $structure[$info->name] = new database_column_info($info);
        }
        $this->free_result($result);

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

        if (is_bool($value)) {                                           $value = (int)$value;
        }                                                    
        if ($column->meta_type == 'B')
            {             if (!is_null($value)) {                               $value = unpack('H*hex', $value);             }                                                
        } else if ($column->meta_type == 'X') {                          if (is_numeric($value)) {                 $value = array('numstr' => (string)$value);              }                                                        } else if ($value === '') {

            if ($column->meta_type == 'I' or $column->meta_type == 'F' or $column->meta_type == 'N') {
                $value = 0;             }
        }
        return $value;
    }

    
    private function free_result($resource) {
        if (!is_bool($resource)) {             return sqlsrv_free_stmt($resource);
        }
    }

    
    private function sqlsrvtype2moodletype($sqlsrv_type) {
        $type = null;

        switch (strtoupper($sqlsrv_type)) {
          case 'BIT':
           $type = 'L';
           break;

          case 'INT':
          case 'SMALLINT':
          case 'INTEGER':
          case 'BIGINT':
           $type = 'I';
           break;

          case 'DECIMAL':
          case 'REAL':
          case 'FLOAT':
           $type = 'N';
           break;

          case 'VARCHAR':
          case 'NVARCHAR':
           $type = 'C';
           break;

          case 'TEXT':
          case 'NTEXT':
          case 'VARCHAR(MAX)':
          case 'NVARCHAR(MAX)':
           $type = 'X';
           break;

          case 'IMAGE':
          case 'VARBINARY':
          case 'VARBINARY(MAX)':
           $type = 'B';
           break;

          case 'DATETIME':
           $type = 'D';
           break;
         }

        if (!$type) {
            throw new dml_exception('invalidsqlsrvnativetype', $sqlsrv_type);
        }
        return $type;
    }

    
    public function change_database_structure($sql, $tablenames = null) {
        $this->get_manager();         $sqls = (array)$sql;

        try {
            foreach ($sqls as $sql) {
                $this->query_start($sql, null, SQL_QUERY_STRUCTURE);
                $result = sqlsrv_query($this->sqlsrv, $sql);
                $this->query_end($result);
            }
        } catch (ddl_change_structure_exception $e) {
            $this->reset_caches($tablenames);
            throw $e;
        }

        $this->reset_caches($tablenames);
        return true;
    }

    
    protected function build_native_bound_params(array $params = null) {

        return null;
    }

    
    protected function emulate_bound_params($sql, array $params = null) {

        if (empty($params)) {
            return $sql;
        }
                $parts = array_reverse(explode('?', $sql));
        $return = array_pop($parts);
        foreach ($params as $param) {
            if (is_bool($param)) {
                $return .= (int)$param;
            } else if (is_array($param) && isset($param['hex'])) {                 $return .= '0x'.$param['hex'];
            } else if (is_array($param) && isset($param['numstr'])) {                 $return .= "N'{$param['numstr']}'";                               } else if (is_null($param)) {
                $return .= 'NULL';

            } else if (is_number($param)) {                 $return .= "'$param'";             } else if (is_float($param)) {
                $return .= $param;
            } else {
                $param = str_replace("'", "''", $param);
                $param = str_replace("\0", "", $param);
                $return .= "N'$param'";
            }

            $return .= array_pop($parts);
        }
        return $return;
    }

    
    public function execute($sql, array $params = null) {
        if (strpos($sql, ';') !== false) {
            throw new coding_exception('moodle_database::execute() Multiple sql statements found or bound parameters not used properly in query!');
        }
        $this->do_query($sql, $params, SQL_QUERY_UPDATE);
        return true;
    }

    
    public function get_recordset_sql($sql, array $params = null, $limitfrom = 0, $limitnum = 0) {

        list($limitfrom, $limitnum) = $this->normalise_limit_from_num($limitfrom, $limitnum);
        $needscrollable = (bool)$limitfrom; 
        if ($limitfrom or $limitnum) {
            if (!$this->supportsoffsetfetch) {
                if ($limitnum >= 1) {                     $fetch = $limitfrom + $limitnum;
                    if (PHP_INT_MAX - $limitnum < $limitfrom) {                         $fetch = PHP_INT_MAX;
                    }
                    $sql = preg_replace('/^([\s(])*SELECT([\s]+(DISTINCT|ALL))?(?!\s*TOP\s*\()/i',
                                        "\\1SELECT\\2 TOP $fetch", $sql);
                }
            } else {
                $needscrollable = false;                 $sql = (substr($sql, -1) === ';') ? substr($sql, 0, -1) : $sql;
                                                if (!strpos(strtoupper($sql), "ORDER BY")) {
                    $sql .= " ORDER BY 1";
                }

                $sql .= " OFFSET ".$limitfrom." ROWS ";

                if ($limitnum > 0) {
                    $sql .= " FETCH NEXT ".$limitnum." ROWS ONLY";
                }
            }
        }
        $result = $this->do_query($sql, $params, SQL_QUERY_SELECT, false, $needscrollable);

        if ($needscrollable) {             sqlsrv_fetch($result, SQLSRV_SCROLL_ABSOLUTE, $limitfrom - 1);
        }
        return $this->create_recordset($result);
    }

    
    protected function create_recordset($result) {
        $rs = new sqlsrv_native_moodle_recordset($result, $this);
        $this->recordsets[] = $rs;
        return $rs;
    }

    
    public function recordset_closed(sqlsrv_native_moodle_recordset $rs) {
        if ($key = array_search($rs, $this->recordsets, true)) {
            unset($this->recordsets[$key]);
        }
    }

    
    public function get_records_sql($sql, array $params = null, $limitfrom = 0, $limitnum = 0) {

        $rs = $this->get_recordset_sql($sql, $params, $limitfrom, $limitnum);

        $results = array();

        foreach ($rs as $row) {
            $id = reset($row);

            if (isset($results[$id])) {
                $colname = key($row);
                debugging("Did you remember to make the first column something unique in your call to get_records? Duplicate value '$id' found in column '$colname'.", DEBUG_DEVELOPER);
            }
            $results[$id] = (object)$row;
        }
        $rs->close();

        return $results;
    }

    
    public function get_fieldset_sql($sql, array $params = null) {

        $rs = $this->get_recordset_sql($sql, $params);

        $results = array ();

        foreach ($rs as $row) {
            $results[] = reset($row);
        }
        $rs->close();

        return $results;
    }

    
    public function insert_record_raw($table, $params, $returnid=true, $bulk=false, $customsequence=false) {
        if (!is_array($params)) {
            $params = (array)$params;
        }

        $isidentity = false;

        if ($customsequence) {
            if (!isset($params['id'])) {
                throw new coding_exception('moodle_database::insert_record_raw() id field must be specified if custom sequences used.');
            }

            $returnid = false;
            $columns = $this->get_columns($table);
            if (isset($columns['id']) and $columns['id']->auto_increment) {
                $isidentity = true;
            }

                                    if ($isidentity) {
                $sql = 'SET IDENTITY_INSERT {'.$table.'} ON';                 $this->do_query($sql, null, SQL_QUERY_AUX);
            }

        } else {
            unset($params['id']);
        }

        if (empty($params)) {
            throw new coding_exception('moodle_database::insert_record_raw() no fields found.');
        }
        $fields = implode(',', array_keys($params));
        $qms = array_fill(0, count($params), '?');
        $qms = implode(',', $qms);
        $sql = "INSERT INTO {" . $table . "} ($fields) VALUES($qms)";
        $query_id = $this->do_query($sql, $params, SQL_QUERY_INSERT);

        if ($customsequence) {
                                    if ($isidentity) {
                $sql = 'SET IDENTITY_INSERT {'.$table.'} OFF';                 $this->do_query($sql, null, SQL_QUERY_AUX);
            }
        }

        if ($returnid) {
            $id = $this->sqlsrv_fetch_id();
            return $id;
        } else {
            return true;
        }
    }

    
    private function sqlsrv_fetch_id() {
        $query_id = sqlsrv_query($this->sqlsrv, 'SELECT SCOPE_IDENTITY()');
        if ($query_id === false) {
            $dberr = $this->get_last_error();
            return false;
        }
        $row = $this->sqlsrv_fetchrow($query_id);
        return (int)$row[0];
    }

    
    private function sqlsrv_fetchrow($query_id) {
        $row = sqlsrv_fetch_array($query_id, SQLSRV_FETCH_NUMERIC);
        if ($row === false) {
            $dberr = $this->get_last_error();
            return false;
        }

        foreach ($row as $key => $value) {
            $row[$key] = ($value === ' ' || $value === NULL) ? '' : $value;
        }
        return $row;
    }

    
    public function insert_record($table, $dataobject, $returnid = true, $bulk = false) {
        $dataobject = (array)$dataobject;

        $columns = $this->get_columns($table);
        if (empty($columns)) {
            throw new dml_exception('ddltablenotexist', $table);
        }

        $cleaned = array ();

        foreach ($dataobject as $field => $value) {
            if ($field === 'id') {
                continue;
            }
            if (!isset($columns[$field])) {
                continue;
            }
            $column = $columns[$field];
            $cleaned[$field] = $this->normalise_value($column, $value);
        }

        return $this->insert_record_raw($table, $cleaned, $returnid, $bulk);
    }

    
    public function import_record($table, $dataobject) {
        if (!is_object($dataobject)) {
            $dataobject = (object)$dataobject;
        }

        $columns = $this->get_columns($table);
        $cleaned = array ();

        foreach ($dataobject as $field => $value) {
            if (!isset($columns[$field])) {
                continue;
            }
            $column = $columns[$field];
            $cleaned[$field] = $this->normalise_value($column, $value);
        }

        $this->insert_record_raw($table, $cleaned, false, false, true);

        return true;
    }

    
    public function update_record_raw($table, $params, $bulk = false) {
        $params = (array)$params;

        if (!isset($params['id'])) {
            throw new coding_exception('moodle_database::update_record_raw() id field must be specified.');
        }
        $id = $params['id'];
        unset($params['id']);

        if (empty($params)) {
            throw new coding_exception('moodle_database::update_record_raw() no fields found.');
        }

        $sets = array ();

        foreach ($params as $field => $value) {
            $sets[] = "$field = ?";
        }

        $params[] = $id; 
        $sets = implode(',', $sets);
        $sql = "UPDATE {".$table."} SET $sets WHERE id = ?";

        $this->do_query($sql, $params, SQL_QUERY_UPDATE);

        return true;
    }

    
    public function update_record($table, $dataobject, $bulk = false) {
        $dataobject = (array)$dataobject;

        $columns = $this->get_columns($table);
        $cleaned = array ();

        foreach ($dataobject as $field => $value) {
            if (!isset($columns[$field])) {
                continue;
            }
            $column = $columns[$field];
            $cleaned[$field] = $this->normalise_value($column, $value);
        }

        return $this->update_record_raw($table, $cleaned, $bulk);
    }

    
    public function set_field_select($table, $newfield, $newvalue, $select, array $params = null) {
        if ($select) {
            $select = "WHERE $select";
        }

        if (is_null($params)) {
            $params = array ();
        }

                list($select, $params, $type) = $this->fix_sql_params($select, $params);

                $columns = $this->get_columns($table);
        $column = $columns[$newfield];

        $newvalue = $this->normalise_value($column, $newvalue);

        if (is_null($newvalue)) {
            $newfield = "$newfield = NULL";
        } else {
            $newfield = "$newfield = ?";
            array_unshift($params, $newvalue);
        }
        $sql = "UPDATE {".$table."} SET $newfield $select";

        $this->do_query($sql, $params, SQL_QUERY_UPDATE);

        return true;
    }

    
    public function delete_records_select($table, $select, array $params = null) {
        if ($select) {
            $select = "WHERE $select";
        }

        $sql = "DELETE FROM {".$table."} $select";

                $this->do_query($sql, $params, SQL_QUERY_UPDATE);

        return true;
    }


    public function sql_cast_char2int($fieldname, $text = false) {
        if (!$text) {
            return ' CAST(' . $fieldname . ' AS INT) ';
        } else {
            return ' CAST(' . $this->sql_compare_text($fieldname) . ' AS INT) ';
        }
    }

    public function sql_cast_char2real($fieldname, $text=false) {
        if (!$text) {
            return ' CAST(' . $fieldname . ' AS REAL) ';
        } else {
            return ' CAST(' . $this->sql_compare_text($fieldname) . ' AS REAL) ';
        }
    }

    public function sql_ceil($fieldname) {
        return ' CEILING('.$fieldname.')';
    }

    protected function get_collation() {
        if (isset($this->collation)) {
            return $this->collation;
        }
        if (!empty($this->dboptions['dbcollation'])) {
                        $this->collation = $this->dboptions['dbcollation'];
            return $this->collation;
        }

                $this->collation = 'Latin1_General_CI_AI';

        $sql = "SELECT CAST(DATABASEPROPERTYEX('$this->dbname', 'Collation') AS varchar(255)) AS SQLCollation";
        $this->query_start($sql, null, SQL_QUERY_AUX);
        $result = sqlsrv_query($this->sqlsrv, $sql);
        $this->query_end($result);

        if ($result) {
            if ($rawcolumn = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                $this->collation = reset($rawcolumn);
            }
            $this->free_result($result);
        }

        return $this->collation;
    }

    
    public function sql_like($fieldname, $param, $casesensitive = true, $accentsensitive = true, $notlike = false, $escapechar = '\\') {
        if (strpos($param, '%') !== false) {
            debugging('Potential SQL injection detected, sql_like() expects bound parameters (? or :named)');
        }

        $collation = $this->get_collation();
        $LIKE = $notlike ? 'NOT LIKE' : 'LIKE';

        if ($casesensitive) {
            $collation = str_replace('_CI', '_CS', $collation);
        } else {
            $collation = str_replace('_CS', '_CI', $collation);
        }
        if ($accentsensitive) {
            $collation = str_replace('_AI', '_AS', $collation);
        } else {
            $collation = str_replace('_AS', '_AI', $collation);
        }

        return "$fieldname COLLATE $collation $LIKE $param ESCAPE '$escapechar'";
    }

    public function sql_concat() {
        $arr = func_get_args();

        foreach ($arr as $key => $ele) {
            $arr[$key] = ' CAST('.$ele.' AS NVARCHAR(255)) ';
        }
        $s = implode(' + ', $arr);

        if ($s === '') {
            return " '' ";
        }
        return " $s ";
    }

    public function sql_concat_join($separator = "' '", $elements = array ()) {
        for ($n = count($elements) - 1; $n > 0; $n--) {
            array_splice($elements, $n, 0, $separator);
        }
        return call_user_func_array(array($this, 'sql_concat'), $elements);
    }

    public function sql_isempty($tablename, $fieldname, $nullablefield, $textfield) {
        if ($textfield) {
            return ' ('.$this->sql_compare_text($fieldname)." = '') ";
        } else {
            return " ($fieldname = '') ";
        }
    }

    
    public function sql_length($fieldname) {
        return ' LEN('.$fieldname.')';
    }

    public function sql_order_by_text($fieldname, $numchars = 32) {
        return " CONVERT(varchar({$numchars}), {$fieldname})";
    }

    
    public function sql_position($needle, $haystack) {
        return "CHARINDEX(($needle), ($haystack))";
    }

    
    public function sql_substr($expr, $start, $length = false) {
        if (count(func_get_args()) < 2) {
            throw new coding_exception('moodle_database::sql_substr() requires at least two parameters',
                'Originally this function was only returning name of SQL substring function, it now requires all parameters.');
        }

        if ($length === false) {
            return "SUBSTRING($expr, " . $this->sql_cast_char2int($start) . ", 2^31-1)";
        } else {
            return "SUBSTRING($expr, " . $this->sql_cast_char2int($start) . ", " . $this->sql_cast_char2int($length) . ")";
        }
    }

    
    public function replace_all_text_supported() {
        return true;
    }

    public function session_lock_supported() {
        return true;
    }

    
    public function get_session_lock($rowid, $timeout) {
        if (!$this->session_lock_supported()) {
            return;
        }
        parent::get_session_lock($rowid, $timeout);

        $timeoutmilli = $timeout * 1000;

        $fullname = $this->dbname.'-'.$this->prefix.'-session-'.$rowid;
                                        $sql = "BEGIN
                    DECLARE @result INT
                    EXECUTE @result = sp_getapplock @Resource='$fullname',
                                                    @LockMode='Exclusive',
                                                    @LockOwner='Session',
                                                    @LockTimeout='$timeoutmilli'
                    SELECT @result
                END";
        $this->query_start($sql, null, SQL_QUERY_AUX);
        $result = sqlsrv_query($this->sqlsrv, $sql);
        $this->query_end($result);

        if ($result) {
            $row = sqlsrv_fetch_array($result);
            if ($row[0] < 0) {
                throw new dml_sessionwait_exception();
            }
        }

        $this->free_result($result);
    }

    public function release_session_lock($rowid) {
        if (!$this->session_lock_supported()) {
            return;
        }
        if (!$this->used_for_db_sessions) {
            return;
        }

        parent::release_session_lock($rowid);

        $fullname = $this->dbname.'-'.$this->prefix.'-session-'.$rowid;
        $sql = "sp_releaseapplock '$fullname', 'Session'";
        $this->query_start($sql, null, SQL_QUERY_AUX);
        $result = sqlsrv_query($this->sqlsrv, $sql);
        $this->query_end($result);
        $this->free_result($result);
    }

    
    protected function begin_transaction() {
                        foreach ($this->recordsets as $rs) {
            $rs->transaction_starts();
        }

        $this->query_start('native sqlsrv_begin_transaction', NULL, SQL_QUERY_AUX);
        $result = sqlsrv_begin_transaction($this->sqlsrv);
        $this->query_end($result);
    }

    
    protected function commit_transaction() {
        $this->query_start('native sqlsrv_commit', NULL, SQL_QUERY_AUX);
        $result = sqlsrv_commit($this->sqlsrv);
        $this->query_end($result);
    }

    
    protected function rollback_transaction() {
        $this->query_start('native sqlsrv_rollback', NULL, SQL_QUERY_AUX);
        $result = sqlsrv_rollback($this->sqlsrv);
        $this->query_end($result);
    }
}
