<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/moodle_database.php');
require_once(__DIR__.'/mysqli_native_moodle_recordset.php');
require_once(__DIR__.'/mysqli_native_moodle_temptables.php');


class mysqli_native_moodle_database extends moodle_database {

    
    protected $mysqli = null;
    
    protected $compressedrowformatsupported = null;

    private $transactions_supported = null;

    
    public function create_database($dbhost, $dbuser, $dbpass, $dbname, array $dboptions=null) {
        $driverstatus = $this->driver_installed();

        if ($driverstatus !== true) {
            throw new dml_exception('dbdriverproblem', $driverstatus);
        }

        if (!empty($dboptions['dbsocket'])
                and (strpos($dboptions['dbsocket'], '/') !== false or strpos($dboptions['dbsocket'], '\\') !== false)) {
            $dbsocket = $dboptions['dbsocket'];
        } else {
            $dbsocket = ini_get('mysqli.default_socket');
        }
        if (empty($dboptions['dbport'])) {
            $dbport = (int)ini_get('mysqli.default_port');
        } else {
            $dbport = (int)$dboptions['dbport'];
        }
                if (empty($dbport)) {
            $dbport = 3306;
        }
        ob_start();
        $conn = new mysqli($dbhost, $dbuser, $dbpass, '', $dbport, $dbsocket);         $dberr = ob_get_contents();
        ob_end_clean();
        $errorno = @$conn->connect_errno;

        if ($errorno !== 0) {
            throw new dml_connection_exception($dberr);
        }

        if (isset($dboptions['dbcollation']) and strpos($dboptions['dbcollation'], 'utf8_') === 0) {
            $collation = $dboptions['dbcollation'];
        } else {
            $collation = 'utf8_unicode_ci';
        }

        $result = $conn->query("CREATE DATABASE $dbname DEFAULT CHARACTER SET utf8 DEFAULT COLLATE ".$collation);

        $conn->close();

        if (!$result) {
            throw new dml_exception('cannotcreatedb');
        }

        return true;
    }

    
    public function driver_installed() {
        if (!extension_loaded('mysqli')) {
            return get_string('mysqliextensionisnotpresentinphp', 'install');
        }
        return true;
    }

    
    public function get_dbfamily() {
        return 'mysql';
    }

    
    protected function get_dbtype() {
        return 'mysqli';
    }

    
    protected function get_dblibrary() {
        return 'native';
    }

    
    public function get_dbengine() {
        if (isset($this->dboptions['dbengine'])) {
            return $this->dboptions['dbengine'];
        }

        if ($this->external) {
            return null;
        }

        $engine = null;

                        $sql = "SELECT engine
                  FROM INFORMATION_SCHEMA.TABLES
                 WHERE table_schema = DATABASE() AND table_name = '{$this->prefix}config'";
        $this->query_start($sql, NULL, SQL_QUERY_AUX);
        $result = $this->mysqli->query($sql);
        $this->query_end($result);
        if ($rec = $result->fetch_assoc()) {
            $engine = $rec['engine'];
        }
        $result->close();

        if ($engine) {
                        $this->dboptions['dbengine'] = $engine;
            return $engine;
        }

                $sql = "SELECT @@default_storage_engine engine";
        $this->query_start($sql, NULL, SQL_QUERY_AUX);
        $result = $this->mysqli->query($sql);
        $this->query_end($result);
        if ($rec = $result->fetch_assoc()) {
            $engine = $rec['engine'];
        }
        $result->close();

        if ($engine === 'MyISAM') {
                        $sql = "SHOW STORAGE ENGINES";
            $this->query_start($sql, NULL, SQL_QUERY_AUX);
            $result = $this->mysqli->query($sql);
            $this->query_end($result);
            $engines = array();
            while ($res = $result->fetch_assoc()) {
                if ($res['Support'] === 'YES' or $res['Support'] === 'DEFAULT') {
                    $engines[$res['Engine']] = true;
                }
            }
            $result->close();
            if (isset($engines['InnoDB'])) {
                $engine = 'InnoDB';
            }
            if (isset($engines['XtraDB'])) {
                $engine = 'XtraDB';
            }
        }

                $this->dboptions['dbengine'] = $engine;
        return $engine;
    }

    
    public function get_dbcollation() {
        if (isset($this->dboptions['dbcollation'])) {
            return $this->dboptions['dbcollation'];
        }
        if ($this->external) {
            return null;
        }

        $collation = null;

                        $sql = "SELECT collation_name
                  FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE table_schema = DATABASE() AND table_name = '{$this->prefix}config' AND column_name = 'value'";
        $this->query_start($sql, NULL, SQL_QUERY_AUX);
        $result = $this->mysqli->query($sql);
        $this->query_end($result);
        if ($rec = $result->fetch_assoc()) {
            $collation = $rec['collation_name'];
        }
        $result->close();

        if (!$collation) {
                        $sql = "SELECT @@collation_database";
            $this->query_start($sql, NULL, SQL_QUERY_AUX);
            $result = $this->mysqli->query($sql);
            $this->query_end($result);
            if ($rec = $result->fetch_assoc()) {
                if (strpos($rec['@@collation_database'], 'utf8_') === 0) {
                    $collation = $rec['@@collation_database'];
                }
            }
            $result->close();
        }

        if (!$collation) {
                        $collation = null;
            $sql = "SHOW COLLATION WHERE Collation LIKE 'utf8\_%' AND Charset = 'utf8'";
            $this->query_start($sql, NULL, SQL_QUERY_AUX);
            $result = $this->mysqli->query($sql);
            $this->query_end($result);
            while ($res = $result->fetch_assoc()) {
                $collation = $res['Collation'];
                if (strtoupper($res['Default']) === 'YES') {
                    $collation = $res['Collation'];
                    break;
                }
            }
            $result->close();
        }

                $this->dboptions['dbcollation'] = $collation;
        return $collation;
    }

    
    public function get_row_format($table) {
        $rowformat = null;
        $table = $this->mysqli->real_escape_string($table);
        $sql = "SELECT row_format
                  FROM INFORMATION_SCHEMA.TABLES
                 WHERE table_schema = DATABASE() AND table_name = '{$this->prefix}$table'";
        $this->query_start($sql, NULL, SQL_QUERY_AUX);
        $result = $this->mysqli->query($sql);
        $this->query_end($result);
        if ($rec = $result->fetch_assoc()) {
            $rowformat = $rec['row_format'];
        }
        $result->close();

        return $rowformat;
    }

    
    public function is_compressed_row_format_supported($cached = true) {
        if ($cached and isset($this->compressedrowformatsupported)) {
            return($this->compressedrowformatsupported);
        }

        $engine = strtolower($this->get_dbengine());
        $info = $this->get_server_info();

        if (version_compare($info['version'], '5.5.0') < 0) {
                        $this->compressedrowformatsupported = false;

        } else if ($engine !== 'innodb' and $engine !== 'xtradb') {
                        $this->compressedrowformatsupported = false;

        } else if (!$filepertable = $this->get_record_sql("SHOW VARIABLES LIKE 'innodb_file_per_table'")) {
            $this->compressedrowformatsupported = false;

        } else if ($filepertable->value !== 'ON') {
            $this->compressedrowformatsupported = false;

        } else if (!$fileformat = $this->get_record_sql("SHOW VARIABLES LIKE 'innodb_file_format'")) {
            $this->compressedrowformatsupported = false;

        } else  if ($fileformat->value !== 'Barracuda') {
            $this->compressedrowformatsupported = false;

        } else {
                        $this->compressedrowformatsupported = true;
        }

        return $this->compressedrowformatsupported;
    }

    
    public function get_name() {
        return get_string('nativemysqli', 'install');
    }

    
    public function get_configuration_help() {
        return get_string('nativemysqlihelp', 'install');
    }

    
    public function diagnose() {
        $sloppymyisamfound = false;
        $prefix = str_replace('_', '\\_', $this->prefix);
        $sql = "SELECT COUNT('x')
                  FROM INFORMATION_SCHEMA.TABLES
                 WHERE table_schema = DATABASE()
                       AND table_name LIKE BINARY '$prefix%'
                       AND Engine = 'MyISAM'";
        $this->query_start($sql, null, SQL_QUERY_AUX);
        $result = $this->mysqli->query($sql);
        $this->query_end($result);
        if ($result) {
            if ($arr = $result->fetch_assoc()) {
                $count = reset($arr);
                if ($count) {
                    $sloppymyisamfound = true;
                }
            }
            $result->close();
        }

        if ($sloppymyisamfound) {
            return get_string('myisamproblem', 'error');
        } else {
            return null;
        }
    }

    
    public function connect($dbhost, $dbuser, $dbpass, $dbname, $prefix, array $dboptions=null) {
        $driverstatus = $this->driver_installed();

        if ($driverstatus !== true) {
            throw new dml_exception('dbdriverproblem', $driverstatus);
        }

        $this->store_settings($dbhost, $dbuser, $dbpass, $dbname, $prefix, $dboptions);

                        if (!empty($this->dboptions['dbsocket'])
                and (strpos($this->dboptions['dbsocket'], '/') !== false or strpos($this->dboptions['dbsocket'], '\\') !== false)) {
            $dbsocket = $this->dboptions['dbsocket'];
        } else {
            $dbsocket = ini_get('mysqli.default_socket');
        }
        if (empty($this->dboptions['dbport'])) {
            $dbport = (int)ini_get('mysqli.default_port');
        } else {
            $dbport = (int)$this->dboptions['dbport'];
        }
                if (empty($dbport)) {
            $dbport = 3306;
        }
        if ($dbhost and !empty($this->dboptions['dbpersist'])) {
            $dbhost = "p:$dbhost";
        }
        $this->mysqli = @new mysqli($dbhost, $dbuser, $dbpass, $dbname, $dbport, $dbsocket);

        if ($this->mysqli->connect_errno !== 0) {
            $dberr = $this->mysqli->connect_error;
            $this->mysqli = null;
            throw new dml_connection_exception($dberr);
        }

                $this->query_log_prevent();

        $this->query_start("--set_charset()", null, SQL_QUERY_AUX);
        $this->mysqli->set_charset('utf8');
        $this->query_end(true);

                                                $si = $this->get_server_info();
        if (version_compare($si['version'], '5.0.2', '>=')) {
            $sql = "SET SESSION sql_mode = 'STRICT_ALL_TABLES'";
            $this->query_start($sql, null, SQL_QUERY_AUX);
            $result = $this->mysqli->query($sql);
            $this->query_end($result);
        }

                $this->query_log_allow();

                $this->temptables = new mysqli_native_moodle_temptables($this);

        return true;
    }

    
    public function dispose() {
        parent::dispose();         if ($this->mysqli) {
            $this->mysqli->close();
            $this->mysqli = null;
        }
    }

    
    public function get_server_info() {
        return array('description'=>$this->mysqli->server_info, 'version'=>$this->mysqli->server_info);
    }

    
    protected function allowed_param_types() {
        return SQL_PARAMS_QM;
    }

    
    public function get_last_error() {
        return $this->mysqli->error;
    }

    
    public function get_tables($usecache=true) {
        if ($usecache and $this->tables !== null) {
            return $this->tables;
        }
        $this->tables = array();
        $prefix = str_replace('_', '\\_', $this->prefix);
        $sql = "SHOW TABLES LIKE '$prefix%'";
        $this->query_start($sql, null, SQL_QUERY_AUX);
        $result = $this->mysqli->query($sql);
        $this->query_end($result);
        $len = strlen($this->prefix);
        if ($result) {
            while ($arr = $result->fetch_assoc()) {
                $tablename = reset($arr);
                $tablename = substr($tablename, $len);
                $this->tables[$tablename] = $tablename;
            }
            $result->close();
        }

                $this->tables = array_merge($this->tables, $this->temptables->get_temptables());
        return $this->tables;
    }

    
    public function get_indexes($table) {
        $indexes = array();
        $sql = "SHOW INDEXES FROM {$this->prefix}$table";
        $this->query_start($sql, null, SQL_QUERY_AUX);
        $result = $this->mysqli->query($sql);
        try {
            $this->query_end($result);
        } catch (dml_read_exception $e) {
            return $indexes;         }
        if ($result) {
            while ($res = $result->fetch_object()) {
                if ($res->Key_name === 'PRIMARY') {
                    continue;
                }
                if (!isset($indexes[$res->Key_name])) {
                    $indexes[$res->Key_name] = array('unique'=>empty($res->Non_unique), 'columns'=>array());
                }
                $indexes[$res->Key_name]['columns'][$res->Seq_in_index-1] = $res->Column_name;
            }
            $result->close();
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

        $structure = array();

        $sql = "SELECT column_name, data_type, character_maximum_length, numeric_precision,
                       numeric_scale, is_nullable, column_type, column_default, column_key, extra
                  FROM information_schema.columns
                 WHERE table_name = '" . $this->prefix.$table . "'
                       AND table_schema = '" . $this->dbname . "'
              ORDER BY ordinal_position";
        $this->query_start($sql, null, SQL_QUERY_AUX);
        $result = $this->mysqli->query($sql);
        $this->query_end(true); 
        if ($result === false) {
            return array();
        }

        if ($result->num_rows > 0) {
                        while ($rawcolumn = $result->fetch_assoc()) {
                $info = (object)$this->get_column_info((object)$rawcolumn);
                $structure[$info->name] = new database_column_info($info);
            }
            $result->close();

        } else {
                        $result->close();
            $sql = "SHOW COLUMNS FROM {$this->prefix}$table";
            $this->query_start($sql, null, SQL_QUERY_AUX);
            $result = $this->mysqli->query($sql);
            $this->query_end(true);
            if ($result === false) {
                return array();
            }
            while ($rawcolumn = $result->fetch_assoc()) {
                $rawcolumn = (object)array_change_key_case($rawcolumn, CASE_LOWER);
                $rawcolumn->column_name              = $rawcolumn->field; unset($rawcolumn->field);
                $rawcolumn->column_type              = $rawcolumn->type; unset($rawcolumn->type);
                $rawcolumn->character_maximum_length = null;
                $rawcolumn->numeric_precision        = null;
                $rawcolumn->numeric_scale            = null;
                $rawcolumn->is_nullable              = $rawcolumn->null; unset($rawcolumn->null);
                $rawcolumn->column_default           = $rawcolumn->default; unset($rawcolumn->default);
                $rawcolumn->column_key               = $rawcolumn->key; unset($rawcolumn->default);

                if (preg_match('/(enum|varchar)\((\d+)\)/i', $rawcolumn->column_type, $matches)) {
                    $rawcolumn->data_type = $matches[1];
                    $rawcolumn->character_maximum_length = $matches[2];

                } else if (preg_match('/([a-z]*int[a-z]*)\((\d+)\)/i', $rawcolumn->column_type, $matches)) {
                    $rawcolumn->data_type = $matches[1];
                    $rawcolumn->numeric_precision = $matches[2];
                    $rawcolumn->max_length = $rawcolumn->numeric_precision;

                    $type = strtoupper($matches[1]);
                    if ($type === 'BIGINT') {
                        $maxlength = 18;
                    } else if ($type === 'INT' or $type === 'INTEGER') {
                        $maxlength = 9;
                    } else if ($type === 'MEDIUMINT') {
                        $maxlength = 6;
                    } else if ($type === 'SMALLINT') {
                        $maxlength = 4;
                    } else if ($type === 'TINYINT') {
                        $maxlength = 2;
                    } else {
                                                $maxlength = 0;
                    }
                    if ($maxlength < $rawcolumn->max_length) {
                        $rawcolumn->max_length = $maxlength;
                    }

                } else if (preg_match('/(decimal)\((\d+),(\d+)\)/i', $rawcolumn->column_type, $matches)) {
                    $rawcolumn->data_type = $matches[1];
                    $rawcolumn->numeric_precision = $matches[2];
                    $rawcolumn->numeric_scale = $matches[3];

                } else if (preg_match('/(double|float)(\((\d+),(\d+)\))?/i', $rawcolumn->column_type, $matches)) {
                    $rawcolumn->data_type = $matches[1];
                    $rawcolumn->numeric_precision = isset($matches[3]) ? $matches[3] : null;
                    $rawcolumn->numeric_scale = isset($matches[4]) ? $matches[4] : null;

                } else if (preg_match('/([a-z]*text)/i', $rawcolumn->column_type, $matches)) {
                    $rawcolumn->data_type = $matches[1];
                    $rawcolumn->character_maximum_length = -1; 
                } else if (preg_match('/([a-z]*blob)/i', $rawcolumn->column_type, $matches)) {
                    $rawcolumn->data_type = $matches[1];

                } else {
                    $rawcolumn->data_type = $rawcolumn->column_type;
                }

                $info = $this->get_column_info($rawcolumn);
                $structure[$info->name] = new database_column_info($info);
            }
            $result->close();
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

    
    private function get_column_info(stdClass $rawcolumn) {
        $rawcolumn = (object)$rawcolumn;
        $info = new stdClass();
        $info->name           = $rawcolumn->column_name;
        $info->type           = $rawcolumn->data_type;
        $info->meta_type      = $this->mysqltype2moodletype($rawcolumn->data_type);
        $info->default_value  = $rawcolumn->column_default;
        $info->has_default    = !is_null($rawcolumn->column_default);
        $info->not_null       = ($rawcolumn->is_nullable === 'NO');
        $info->primary_key    = ($rawcolumn->column_key === 'PRI');
        $info->binary         = false;
        $info->unsigned       = null;
        $info->auto_increment = false;
        $info->unique         = null;
        $info->scale          = null;

        if ($info->meta_type === 'C') {
            $info->max_length = $rawcolumn->character_maximum_length;

        } else if ($info->meta_type === 'I') {
            if ($info->primary_key) {
                $info->meta_type = 'R';
                $info->unique    = true;
            }
                        $info->max_length    = $rawcolumn->numeric_precision;
            if (preg_match('/([a-z]*int[a-z]*)\((\d+)\)/i', $rawcolumn->column_type, $matches)) {
                $type = strtoupper($matches[1]);
                if ($type === 'BIGINT') {
                    $maxlength = 18;
                } else if ($type === 'INT' or $type === 'INTEGER') {
                    $maxlength = 9;
                } else if ($type === 'MEDIUMINT') {
                    $maxlength = 6;
                } else if ($type === 'SMALLINT') {
                    $maxlength = 4;
                } else if ($type === 'TINYINT') {
                    $maxlength = 2;
                } else {
                                        $maxlength = 0;
                }
                                                if ($maxlength < $info->max_length) {
                    $info->max_length = $maxlength;
                }
            }
            $info->unsigned      = (stripos($rawcolumn->column_type, 'unsigned') !== false);
            $info->auto_increment= (strpos($rawcolumn->extra, 'auto_increment') !== false);

        } else if ($info->meta_type === 'N') {
            $info->max_length    = $rawcolumn->numeric_precision;
            $info->scale         = $rawcolumn->numeric_scale;
            $info->unsigned      = (stripos($rawcolumn->column_type, 'unsigned') !== false);

        } else if ($info->meta_type === 'X') {
            if ("$rawcolumn->character_maximum_length" === '4294967295') {                                 $info->max_length = -1;
            } else {
                $info->max_length = $rawcolumn->character_maximum_length;
            }
            $info->primary_key   = false;

        } else if ($info->meta_type === 'B') {
            $info->max_length    = -1;
            $info->primary_key   = false;
            $info->binary        = true;
        }

        return $info;
    }

    
    private function mysqltype2moodletype($mysql_type) {
        $type = null;

        switch(strtoupper($mysql_type)) {
            case 'BIT':
                $type = 'L';
                break;

            case 'TINYINT':
            case 'SMALLINT':
            case 'MEDIUMINT':
            case 'INT':
            case 'INTEGER':
            case 'BIGINT':
                $type = 'I';
                break;

            case 'FLOAT':
            case 'DOUBLE':
            case 'DECIMAL':
                $type = 'N';
                break;

            case 'CHAR':
            case 'ENUM':
            case 'SET':
            case 'VARCHAR':
                $type = 'C';
                break;

            case 'TINYTEXT':
            case 'TEXT':
            case 'MEDIUMTEXT':
            case 'LONGTEXT':
                $type = 'X';
                break;

            case 'BINARY':
            case 'VARBINARY':
            case 'BLOB':
            case 'TINYBLOB':
            case 'MEDIUMBLOB':
            case 'LONGBLOB':
                $type = 'B';
                break;

            case 'DATE':
            case 'TIME':
            case 'DATETIME':
            case 'TIMESTAMP':
            case 'YEAR':
                $type = 'D';
                break;
        }

        if (!$type) {
            throw new dml_exception('invalidmysqlnativetype', $mysql_type);
        }
        return $type;
    }

    
    protected function normalise_value($column, $value) {
        $this->detect_objects($value);

        if (is_bool($value)) {             $value = (int)$value;

        } else if ($value === '') {
            if ($column->meta_type == 'I' or $column->meta_type == 'F' or $column->meta_type == 'N') {
                $value = 0;             }
                        } else if (is_float($value) and ($column->meta_type == 'C' or $column->meta_type == 'X')) {
            $value = "$value";
        }
        return $value;
    }

    
    public function setup_is_unicodedb() {
                        $collation = $this->get_dbcollation();

        $sql = "SHOW COLLATION WHERE Collation ='$collation' AND Charset = 'utf8'";
        $this->query_start($sql, NULL, SQL_QUERY_AUX);
        $result = $this->mysqli->query($sql);
        $this->query_end($result);
        if ($result->fetch_assoc()) {
            $return = true;
        } else {
            $return = false;
        }
        $result->close();

        return $return;
    }

    
    public function change_database_structure($sql, $tablenames = null) {
        $this->get_manager();         if (is_array($sql)) {
            $sql = implode("\n;\n", $sql);
        }

        try {
            $this->query_start($sql, null, SQL_QUERY_STRUCTURE);
            $result = $this->mysqli->multi_query($sql);
            if ($result === false) {
                $this->query_end(false);
            }
            while ($this->mysqli->more_results()) {
                $result = $this->mysqli->next_result();
                if ($result === false) {
                    $this->query_end(false);
                }
            }
            $this->query_end(true);
        } catch (ddl_change_structure_exception $e) {
            while (@$this->mysqli->more_results()) {
                @$this->mysqli->next_result();
            }
            $this->reset_caches($tablenames);
            throw $e;
        }

        $this->reset_caches($tablenames);
        return true;
    }

    
    protected function emulate_bound_params($sql, array $params=null) {
        if (empty($params)) {
            return $sql;
        }
                $parts = array_reverse(explode('?', $sql));
        $return = array_pop($parts);
        foreach ($params as $param) {
            if (is_bool($param)) {
                $return .= (int)$param;
            } else if (is_null($param)) {
                $return .= 'NULL';
            } else if (is_number($param)) {
                $return .= "'".$param."'";             } else if (is_float($param)) {
                $return .= $param;
            } else {
                $param = $this->mysqli->real_escape_string($param);
                $return .= "'$param'";
            }
            $return .= array_pop($parts);
        }
        return $return;
    }

    
    public function execute($sql, array $params=null) {
        list($sql, $params, $type) = $this->fix_sql_params($sql, $params);

        if (strpos($sql, ';') !== false) {
            throw new coding_exception('moodle_database::execute() Multiple sql statements found or bound parameters not used properly in query!');
        }

        $rawsql = $this->emulate_bound_params($sql, $params);

        $this->query_start($sql, $params, SQL_QUERY_UPDATE);
        $result = $this->mysqli->query($rawsql);
        $this->query_end($result);

        if ($result === true) {
            return true;

        } else {
            $result->close();
            return true;
        }
    }

    
    public function get_recordset_sql($sql, array $params=null, $limitfrom=0, $limitnum=0) {

        list($limitfrom, $limitnum) = $this->normalise_limit_from_num($limitfrom, $limitnum);

        if ($limitfrom or $limitnum) {
            if ($limitnum < 1) {
                $limitnum = "18446744073709551615";
            }
            $sql .= " LIMIT $limitfrom, $limitnum";
        }

        list($sql, $params, $type) = $this->fix_sql_params($sql, $params);
        $rawsql = $this->emulate_bound_params($sql, $params);

        $this->query_start($sql, $params, SQL_QUERY_SELECT);
                $result = $this->mysqli->query($rawsql, MYSQLI_STORE_RESULT);
        $this->query_end($result);

        return $this->create_recordset($result);
    }

    
    public function export_table_recordset($table) {
        $sql = $this->fix_table_names("SELECT * FROM {{$table}}");

        $this->query_start($sql, array(), SQL_QUERY_SELECT);
                $result = $this->mysqli->query($sql, MYSQLI_USE_RESULT);
        $this->query_end($result);

        return $this->create_recordset($result);
    }

    protected function create_recordset($result) {
        return new mysqli_native_moodle_recordset($result);
    }

    
    public function get_records_sql($sql, array $params=null, $limitfrom=0, $limitnum=0) {

        list($limitfrom, $limitnum) = $this->normalise_limit_from_num($limitfrom, $limitnum);

        if ($limitfrom or $limitnum) {
            if ($limitnum < 1) {
                $limitnum = "18446744073709551615";
            }
            $sql .= " LIMIT $limitfrom, $limitnum";
        }

        list($sql, $params, $type) = $this->fix_sql_params($sql, $params);
        $rawsql = $this->emulate_bound_params($sql, $params);

        $this->query_start($sql, $params, SQL_QUERY_SELECT);
        $result = $this->mysqli->query($rawsql, MYSQLI_STORE_RESULT);
        $this->query_end($result);

        $return = array();

        while($row = $result->fetch_assoc()) {
            $row = array_change_key_case($row, CASE_LOWER);
            $id  = reset($row);
            if (isset($return[$id])) {
                $colname = key($row);
                debugging("Did you remember to make the first column something unique in your call to get_records? Duplicate value '$id' found in column '$colname'.", DEBUG_DEVELOPER);
            }
            $return[$id] = (object)$row;
        }
        $result->close();

        return $return;
    }

    
    public function get_fieldset_sql($sql, array $params=null) {
        list($sql, $params, $type) = $this->fix_sql_params($sql, $params);
        $rawsql = $this->emulate_bound_params($sql, $params);

        $this->query_start($sql, $params, SQL_QUERY_SELECT);
        $result = $this->mysqli->query($rawsql, MYSQLI_STORE_RESULT);
        $this->query_end($result);

        $return = array();

        while($row = $result->fetch_assoc()) {
            $return[] = reset($row);
        }
        $result->close();

        return $return;
    }

    
    public function insert_record_raw($table, $params, $returnid=true, $bulk=false, $customsequence=false) {
        if (!is_array($params)) {
            $params = (array)$params;
        }

        if ($customsequence) {
            if (!isset($params['id'])) {
                throw new coding_exception('moodle_database::insert_record_raw() id field must be specified if custom sequences used.');
            }
            $returnid = false;
        } else {
            unset($params['id']);
        }

        if (empty($params)) {
            throw new coding_exception('moodle_database::insert_record_raw() no fields found.');
        }

        $fields = implode(',', array_keys($params));
        $qms    = array_fill(0, count($params), '?');
        $qms    = implode(',', $qms);

        $sql = "INSERT INTO {$this->prefix}$table ($fields) VALUES($qms)";

        list($sql, $params, $type) = $this->fix_sql_params($sql, $params);
        $rawsql = $this->emulate_bound_params($sql, $params);

        $this->query_start($sql, $params, SQL_QUERY_INSERT);
        $result = $this->mysqli->query($rawsql);
        $id = @$this->mysqli->insert_id;         $this->query_end($result);

        if (!$customsequence and !$id) {
            throw new dml_write_exception('unknown error fetching inserted id');
        }

        if (!$returnid) {
            return true;
        } else {
            return (int)$id;
        }
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
            if (!isset($columns[$field])) {
                continue;
            }
            $column = $columns[$field];
            $cleaned[$field] = $this->normalise_value($column, $value);
        }

        return $this->insert_record_raw($table, $cleaned, $returnid, $bulk);
    }

    
    public function insert_records($table, $dataobjects) {
        if (!is_array($dataobjects) and !$dataobjects instanceof Traversable) {
            throw new coding_exception('insert_records() passed non-traversable object');
        }

                                static $chunksize = null;
        if ($chunksize === null) {
            if (!empty($this->dboptions['bulkinsertsize'])) {
                $chunksize = (int)$this->dboptions['bulkinsertsize'];

            } else {
                if (PHP_INT_SIZE === 4) {
                                        $chunksize = 5;
                } else {
                    $sql = "SHOW VARIABLES LIKE 'max_allowed_packet'";
                    $this->query_start($sql, null, SQL_QUERY_AUX);
                    $result = $this->mysqli->query($sql);
                    $this->query_end($result);
                    $size = 0;
                    if ($rec = $result->fetch_assoc()) {
                        $size = $rec['Value'];
                    }
                    $result->close();
                                        $chunksize = (int)($size / 200000);
                    if ($chunksize > 50) {
                        $chunksize = 50;
                    }
                }
            }
        }

        $columns = $this->get_columns($table, true);
        $fields = null;
        $count = 0;
        $chunk = array();
        foreach ($dataobjects as $dataobject) {
            if (!is_array($dataobject) and !is_object($dataobject)) {
                throw new coding_exception('insert_records() passed invalid record object');
            }
            $dataobject = (array)$dataobject;
            if ($fields === null) {
                $fields = array_keys($dataobject);
                $columns = array_intersect_key($columns, $dataobject);
                unset($columns['id']);
            } else if ($fields !== array_keys($dataobject)) {
                throw new coding_exception('All dataobjects in insert_records() must have the same structure!');
            }

            $count++;
            $chunk[] = $dataobject;

            if ($count === $chunksize) {
                $this->insert_chunk($table, $chunk, $columns);
                $chunk = array();
                $count = 0;
            }
        }

        if ($count) {
            $this->insert_chunk($table, $chunk, $columns);
        }
    }

    
    protected function insert_chunk($table, array $chunk, array $columns) {
        $fieldssql = '('.implode(',', array_keys($columns)).')';

        $valuessql = '('.implode(',', array_fill(0, count($columns), '?')).')';
        $valuessql = implode(',', array_fill(0, count($chunk), $valuessql));

        $params = array();
        foreach ($chunk as $dataobject) {
            foreach ($columns as $field => $column) {
                $params[] = $this->normalise_value($column, $dataobject[$field]);
            }
        }

        $sql = "INSERT INTO {$this->prefix}$table $fieldssql VALUES $valuessql";

        list($sql, $params, $type) = $this->fix_sql_params($sql, $params);
        $rawsql = $this->emulate_bound_params($sql, $params);

        $this->query_start($sql, $params, SQL_QUERY_INSERT);
        $result = $this->mysqli->query($rawsql);
        $this->query_end($result);
    }

    
    public function import_record($table, $dataobject) {
        $dataobject = (array)$dataobject;

        $columns = $this->get_columns($table);
        $cleaned = array();

        foreach ($dataobject as $field=>$value) {
            if (!isset($columns[$field])) {
                continue;
            }
            $cleaned[$field] = $value;
        }

        return $this->insert_record_raw($table, $cleaned, false, true, true);
    }

    
    public function update_record_raw($table, $params, $bulk=false) {
        $params = (array)$params;

        if (!isset($params['id'])) {
            throw new coding_exception('moodle_database::update_record_raw() id field must be specified.');
        }
        $id = $params['id'];
        unset($params['id']);

        if (empty($params)) {
            throw new coding_exception('moodle_database::update_record_raw() no fields found.');
        }

        $sets = array();
        foreach ($params as $field=>$value) {
            $sets[] = "$field = ?";
        }

        $params[] = $id; 
        $sets = implode(',', $sets);
        $sql = "UPDATE {$this->prefix}$table SET $sets WHERE id=?";

        list($sql, $params, $type) = $this->fix_sql_params($sql, $params);
        $rawsql = $this->emulate_bound_params($sql, $params);

        $this->query_start($sql, $params, SQL_QUERY_UPDATE);
        $result = $this->mysqli->query($rawsql);
        $this->query_end($result);

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

        return $this->update_record_raw($table, $cleaned, $bulk);
    }

    
    public function set_field_select($table, $newfield, $newvalue, $select, array $params=null) {
        if ($select) {
            $select = "WHERE $select";
        }
        if (is_null($params)) {
            $params = array();
        }
        list($select, $params, $type) = $this->fix_sql_params($select, $params);

                $columns = $this->get_columns($table);
        $column = $columns[$newfield];

        $normalised_value = $this->normalise_value($column, $newvalue);

        if (is_null($normalised_value)) {
            $newfield = "$newfield = NULL";
        } else {
            $newfield = "$newfield = ?";
            array_unshift($params, $normalised_value);
        }
        $sql = "UPDATE {$this->prefix}$table SET $newfield $select";
        $rawsql = $this->emulate_bound_params($sql, $params);

        $this->query_start($sql, $params, SQL_QUERY_UPDATE);
        $result = $this->mysqli->query($rawsql);
        $this->query_end($result);

        return true;
    }

    
    public function delete_records_select($table, $select, array $params=null) {
        if ($select) {
            $select = "WHERE $select";
        }
        $sql = "DELETE FROM {$this->prefix}$table $select";

        list($sql, $params, $type) = $this->fix_sql_params($sql, $params);
        $rawsql = $this->emulate_bound_params($sql, $params);

        $this->query_start($sql, $params, SQL_QUERY_UPDATE);
        $result = $this->mysqli->query($rawsql);
        $this->query_end($result);

        return true;
    }

    public function sql_cast_char2int($fieldname, $text=false) {
        return ' CAST(' . $fieldname . ' AS SIGNED) ';
    }

    public function sql_cast_char2real($fieldname, $text=false) {
                                        return ' CAST(' . $fieldname . ' AS DECIMAL(65,7)) ';
    }

    
    public function sql_like($fieldname, $param, $casesensitive = true, $accentsensitive = true, $notlike = false, $escapechar = '\\') {
        if (strpos($param, '%') !== false) {
            debugging('Potential SQL injection detected, sql_like() expects bound parameters (? or :named)');
        }
        $escapechar = $this->mysqli->real_escape_string($escapechar); 
        $LIKE = $notlike ? 'NOT LIKE' : 'LIKE';

        if ($casesensitive) {
                        return "$fieldname $LIKE $param COLLATE utf8_bin ESCAPE '$escapechar'";

        } else if ($accentsensitive) {
                        return "LOWER($fieldname) $LIKE LOWER($param) COLLATE utf8_bin ESCAPE '$escapechar'";

        } else {
                        $collation = '';
            if ($this->get_dbcollation() == 'utf8_bin') {
                                $collation = 'COLLATE utf8_unicode_ci';
            }

            return "$fieldname $LIKE $param $collation ESCAPE '$escapechar'";
        }
    }

    
    public function sql_concat() {
        $arr = func_get_args();
        $s = implode(', ', $arr);
        if ($s === '') {
            return "''";
        }
        return "CONCAT($s)";
    }

    
    public function sql_concat_join($separator="' '", $elements=array()) {
        $s = implode(', ', $elements);

        if ($s === '') {
            return "''";
        }
        return "CONCAT_WS($separator, $s)";
    }

    
    public function sql_length($fieldname) {
        return ' CHAR_LENGTH(' . $fieldname . ')';
    }

    
    public function sql_regex_supported() {
        return true;
    }

    
    public function sql_regex($positivematch=true) {
        return $positivematch ? 'REGEXP' : 'NOT REGEXP';
    }

    
    public function sql_cast_2signed($fieldname) {
        return ' CAST(' . $fieldname . ' AS SIGNED) ';
    }

    
    public function sql_intersect($selects, $fields) {
        if (count($selects) <= 1) {
            return parent::sql_intersect($selects, $fields);
        }
        $fields = preg_replace('/\s/', '', $fields);
        static $aliascnt = 0;
        $falias = 'intsctal'.($aliascnt++);
        $rv = "SELECT $falias.".
            preg_replace('/,/', ','.$falias.'.', $fields).
            " FROM ($selects[0]) $falias";
        for ($i = 1; $i < count($selects); $i++) {
            $alias = 'intsctal'.($aliascnt++);
            $rv .= " JOIN (".$selects[$i].") $alias ON ".
                join(' AND ',
                    array_map(
                        create_function('$a', 'return "'.$falias.'.$a = '.$alias.'.$a";'),
                        preg_split('/,/', $fields))
                );
        }
        return $rv;
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
        $sql = "SELECT GET_LOCK('$fullname', $timeout)";
        $this->query_start($sql, null, SQL_QUERY_AUX);
        $result = $this->mysqli->query($sql);
        $this->query_end($result);

        if ($result) {
            $arr = $result->fetch_assoc();
            $result->close();

            if (reset($arr) == 1) {
                return;
            } else {
                throw new dml_sessionwait_exception();
            }
        }
    }

    public function release_session_lock($rowid) {
        if (!$this->used_for_db_sessions) {
            return;
        }

        parent::release_session_lock($rowid);
        $fullname = $this->dbname.'-'.$this->prefix.'-session-'.$rowid;
        $sql = "SELECT RELEASE_LOCK('$fullname')";
        $this->query_start($sql, null, SQL_QUERY_AUX);
        $result = $this->mysqli->query($sql);
        $this->query_end($result);

        if ($result) {
            $result->close();
        }
    }

    
    protected function transactions_supported() {
        if (!is_null($this->transactions_supported)) {
            return $this->transactions_supported;
        }

                if (isset($this->dboptions['dbtransactions'])) {
            $this->transactions_supported = $this->dboptions['dbtransactions'];
            return $this->transactions_supported;
        }

        $this->transactions_supported = false;

        $engine = $this->get_dbengine();

                if (in_array($engine, array('InnoDB', 'INNOBASE', 'BDB', 'XtraDB', 'Aria', 'Falcon'))) {
            $this->transactions_supported = true;
        }

        return $this->transactions_supported;
    }

    
    protected function begin_transaction() {
        if (!$this->transactions_supported()) {
            return;
        }

        $sql = "SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED";
        $this->query_start($sql, NULL, SQL_QUERY_AUX);
        $result = $this->mysqli->query($sql);
        $this->query_end($result);

        $sql = "START TRANSACTION";
        $this->query_start($sql, NULL, SQL_QUERY_AUX);
        $result = $this->mysqli->query($sql);
        $this->query_end($result);
    }

    
    protected function commit_transaction() {
        if (!$this->transactions_supported()) {
            return;
        }

        $sql = "COMMIT";
        $this->query_start($sql, NULL, SQL_QUERY_AUX);
        $result = $this->mysqli->query($sql);
        $this->query_end($result);
    }

    
    protected function rollback_transaction() {
        if (!$this->transactions_supported()) {
            return;
        }

        $sql = "ROLLBACK";
        $this->query_start($sql, NULL, SQL_QUERY_AUX);
        $result = $this->mysqli->query($sql);
        $this->query_end($result);

        return true;
    }
}
