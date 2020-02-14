<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/moodle_database.php');
require_once(__DIR__.'/pgsql_native_moodle_recordset.php');
require_once(__DIR__.'/pgsql_native_moodle_temptables.php');


class pgsql_native_moodle_database extends moodle_database {

    
    protected $pgsql     = null;
    protected $bytea_oid = null;

    protected $last_error_reporting; 
    
    protected $savepointpresent = false;

    
    public function driver_installed() {
        if (!extension_loaded('pgsql')) {
            return get_string('pgsqlextensionisnotpresentinphp', 'install');
        }
        return true;
    }

    
    public function get_dbfamily() {
        return 'postgres';
    }

    
    protected function get_dbtype() {
        return 'pgsql';
    }

    
    protected function get_dblibrary() {
        return 'native';
    }

    
    public function get_name() {
        return get_string('nativepgsql', 'install');
    }

    
    public function get_configuration_help() {
        return get_string('nativepgsqlhelp', 'install');
    }

    
    public function connect($dbhost, $dbuser, $dbpass, $dbname, $prefix, array $dboptions=null) {
        if ($prefix == '' and !$this->external) {
                        throw new dml_exception('prefixcannotbeempty', $this->get_dbfamily());
        }

        $driverstatus = $this->driver_installed();

        if ($driverstatus !== true) {
            throw new dml_exception('dbdriverproblem', $driverstatus);
        }

        $this->store_settings($dbhost, $dbuser, $dbpass, $dbname, $prefix, $dboptions);

        $pass = addcslashes($this->dbpass, "'\\");

                if (!empty($this->dboptions['dbsocket']) and ($this->dbhost === 'localhost' or $this->dbhost === '127.0.0.1')) {
            $connection = "user='$this->dbuser' password='$pass' dbname='$this->dbname'";
            if (strpos($this->dboptions['dbsocket'], '/') !== false) {
                $connection = $connection." host='".$this->dboptions['dbsocket']."'";
                if (!empty($this->dboptions['dbport'])) {
                                        $connection = $connection." port ='".$this->dboptions['dbport']."'";
                }
            }
        } else {
            $this->dboptions['dbsocket'] = '';
            if (empty($this->dbname)) {
                                $port = "";
            } else if (empty($this->dboptions['dbport'])) {
                $port = "port ='5432'";
            } else {
                $port = "port ='".$this->dboptions['dbport']."'";
            }
            $connection = "host='$this->dbhost' $port user='$this->dbuser' password='$pass' dbname='$this->dbname'";
        }

        ob_start();
        if (empty($this->dboptions['dbpersist'])) {
            $this->pgsql = pg_connect($connection, PGSQL_CONNECT_FORCE_NEW);
        } else {
            $this->pgsql = pg_pconnect($connection, PGSQL_CONNECT_FORCE_NEW);
        }
        $dberr = ob_get_contents();
        ob_end_clean();

        $status = pg_connection_status($this->pgsql);

        if ($status === false or $status === PGSQL_CONNECTION_BAD) {
            $this->pgsql = null;
            throw new dml_connection_exception($dberr);
        }

                $this->query_log_prevent();

        $this->query_start("--pg_set_client_encoding()", null, SQL_QUERY_AUX);
        pg_set_client_encoding($this->pgsql, 'utf8');
        $this->query_end(true);

        $sql = '';
                if ($this->is_min_version('9.0')) {
            $sql = "SET bytea_output = 'escape'; ";
        }

                if (!empty($this->dboptions['dbschema'])) {
            $sql .= "SET search_path = '".$this->dboptions['dbschema']."'; ";
        }

                $sql .= "SELECT oid FROM pg_type WHERE typname = 'bytea'";
        $this->query_start($sql, null, SQL_QUERY_AUX);
        $result = pg_query($this->pgsql, $sql);
        $this->query_end($result);

        $this->bytea_oid = pg_fetch_result($result, 0, 0);
        pg_free_result($result);
        if ($this->bytea_oid === false) {
            $this->pgsql = null;
            throw new dml_connection_exception('Can not read bytea type.');
        }

                $this->query_log_allow();

                $this->temptables = new pgsql_native_moodle_temptables($this);

        return true;
    }

    
    public function dispose() {
        parent::dispose();         if ($this->pgsql) {
            pg_close($this->pgsql);
            $this->pgsql = null;
        }
    }


    
    protected function query_start($sql, array $params=null, $type, $extrainfo=null) {
        parent::query_start($sql, $params, $type, $extrainfo);
                $this->last_error_reporting = error_reporting(0);
    }

    
    protected function query_end($result) {
                error_reporting($this->last_error_reporting);
        try {
            parent::query_end($result);
            if ($this->savepointpresent and $this->last_type != SQL_QUERY_AUX and $this->last_type != SQL_QUERY_SELECT) {
                $res = @pg_query($this->pgsql, "RELEASE SAVEPOINT moodle_pg_savepoint; SAVEPOINT moodle_pg_savepoint");
                if ($res) {
                    pg_free_result($res);
                }
            }
        } catch (Exception $e) {
            if ($this->savepointpresent) {
                $res = @pg_query($this->pgsql, "ROLLBACK TO SAVEPOINT moodle_pg_savepoint; SAVEPOINT moodle_pg_savepoint");
                if ($res) {
                    pg_free_result($res);
                }
            }
            throw $e;
        }
    }

    
    public function get_server_info() {
        static $info;
        if (!$info) {
            $this->query_start("--pg_version()", null, SQL_QUERY_AUX);
            $info = pg_version($this->pgsql);
            $this->query_end(true);
        }
        return array('description'=>$info['server'], 'version'=>$info['server']);
    }

    
    private function is_min_version($version) {
        $server = $this->get_server_info();
        $server = $server['version'];
        return version_compare($server, $version, '>=');
    }

    
    protected function allowed_param_types() {
        return SQL_PARAMS_DOLLAR;
    }

    
    public function get_last_error() {
        return pg_last_error($this->pgsql);
    }

    
    public function get_tables($usecache=true) {
        if ($usecache and $this->tables !== null) {
            return $this->tables;
        }
        $this->tables = array();
        $prefix = str_replace('_', '|_', $this->prefix);
        $sql = "SELECT c.relname
                  FROM pg_catalog.pg_class c
                  JOIN pg_catalog.pg_namespace as ns ON ns.oid = c.relnamespace
                 WHERE c.relname LIKE '$prefix%' ESCAPE '|'
                       AND c.relkind = 'r'
                       AND (ns.nspname = current_schema() OR ns.oid = pg_my_temp_schema())";
        $this->query_start($sql, null, SQL_QUERY_AUX);
        $result = pg_query($this->pgsql, $sql);
        $this->query_end($result);

        if ($result) {
            while ($row = pg_fetch_row($result)) {
                $tablename = reset($row);
                if ($this->prefix !== false && $this->prefix !== '') {
                    if (strpos($tablename, $this->prefix) !== 0) {
                        continue;
                    }
                    $tablename = substr($tablename, strlen($this->prefix));
                }
                $this->tables[$tablename] = $tablename;
            }
            pg_free_result($result);
        }
        return $this->tables;
    }

    
    public function get_indexes($table) {
        $indexes = array();
        $tablename = $this->prefix.$table;

        $sql = "SELECT i.*
                  FROM pg_catalog.pg_indexes i
                  JOIN pg_catalog.pg_namespace as ns ON ns.nspname = i.schemaname
                 WHERE i.tablename = '$tablename'
                       AND (i.schemaname = current_schema() OR ns.oid = pg_my_temp_schema())";

        $this->query_start($sql, null, SQL_QUERY_AUX);
        $result = pg_query($this->pgsql, $sql);
        $this->query_end($result);

        if ($result) {
            while ($row = pg_fetch_assoc($result)) {
                if (!preg_match('/CREATE (|UNIQUE )INDEX ([^\s]+) ON '.$tablename.' USING ([^\s]+) \(([^\)]+)\)/i', $row['indexdef'], $matches)) {
                    continue;
                }
                if ($matches[4] === 'id') {
                    continue;
                }
                $columns = explode(',', $matches[4]);
                foreach ($columns as $k=>$column) {
                    $column = trim($column);
                    if ($pos = strpos($column, ' ')) {
                                                $column = substr($column, 0, $pos);
                    }
                    $columns[$k] = $this->trim_quotes($column);
                }
                $indexes[$row['indexname']] = array('unique'=>!empty($matches[1]),
                                              'columns'=>$columns);
            }
            pg_free_result($result);
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

        $tablename = $this->prefix.$table;

        $sql = "SELECT a.attnum, a.attname AS field, t.typname AS type, a.attlen, a.atttypmod, a.attnotnull, a.atthasdef, d.adsrc
                  FROM pg_catalog.pg_class c
                  JOIN pg_catalog.pg_namespace as ns ON ns.oid = c.relnamespace
                  JOIN pg_catalog.pg_attribute a ON a.attrelid = c.oid
                  JOIN pg_catalog.pg_type t ON t.oid = a.atttypid
             LEFT JOIN pg_catalog.pg_attrdef d ON (d.adrelid = c.oid AND d.adnum = a.attnum)
                 WHERE relkind = 'r' AND c.relname = '$tablename' AND c.reltype > 0 AND a.attnum > 0
                       AND (ns.nspname = current_schema() OR ns.oid = pg_my_temp_schema())
              ORDER BY a.attnum";

        $this->query_start($sql, null, SQL_QUERY_AUX);
        $result = pg_query($this->pgsql, $sql);
        $this->query_end($result);

        if (!$result) {
            return array();
        }
        while ($rawcolumn = pg_fetch_object($result)) {

            $info = new stdClass();
            $info->name = $rawcolumn->field;
            $matches = null;

            if ($rawcolumn->type === 'varchar') {
                $info->type          = 'varchar';
                $info->meta_type     = 'C';
                $info->max_length    = $rawcolumn->atttypmod - 4;
                $info->scale         = null;
                $info->not_null      = ($rawcolumn->attnotnull === 't');
                $info->has_default   = ($rawcolumn->atthasdef === 't');
                if ($info->has_default) {
                    $parts = explode('::', $rawcolumn->adsrc);
                    if (count($parts) > 1) {
                        $info->default_value = reset($parts);
                        $info->default_value = trim($info->default_value, "'");
                    } else {
                        $info->default_value = $rawcolumn->adsrc;
                    }
                } else {
                    $info->default_value = null;
                }
                $info->primary_key   = false;
                $info->binary        = false;
                $info->unsigned      = null;
                $info->auto_increment= false;
                $info->unique        = null;

            } else if (preg_match('/int(\d)/i', $rawcolumn->type, $matches)) {
                $info->type = 'int';
                if (strpos($rawcolumn->adsrc, 'nextval') === 0) {
                    $info->primary_key   = true;
                    $info->meta_type     = 'R';
                    $info->unique        = true;
                    $info->auto_increment= true;
                    $info->has_default   = false;
                } else {
                    $info->primary_key   = false;
                    $info->meta_type     = 'I';
                    $info->unique        = null;
                    $info->auto_increment= false;
                    $info->has_default   = ($rawcolumn->atthasdef === 't');
                }
                                if ($matches[1] >= 8) {
                    $info->max_length = 18;
                } else if ($matches[1] >= 4) {
                    $info->max_length = 9;
                } else if ($matches[1] >= 2) {
                    $info->max_length = 4;
                } else if ($matches[1] >= 1) {
                    $info->max_length = 2;
                } else {
                    $info->max_length = 0;
                }
                $info->scale         = null;
                $info->not_null      = ($rawcolumn->attnotnull === 't');
                if ($info->has_default) {
                                        $parts = explode('::', $rawcolumn->adsrc);
                    if (count($parts) > 1) {
                        $info->default_value = reset($parts);
                    } else {
                        $info->default_value = $rawcolumn->adsrc;
                    }
                    $info->default_value = trim($info->default_value, "()'");
                } else {
                    $info->default_value = null;
                }
                $info->binary        = false;
                $info->unsigned      = false;

            } else if ($rawcolumn->type === 'numeric') {
                $info->type = $rawcolumn->type;
                $info->meta_type     = 'N';
                $info->primary_key   = false;
                $info->binary        = false;
                $info->unsigned      = null;
                $info->auto_increment= false;
                $info->unique        = null;
                $info->not_null      = ($rawcolumn->attnotnull === 't');
                $info->has_default   = ($rawcolumn->atthasdef === 't');
                if ($info->has_default) {
                                        $parts = explode('::', $rawcolumn->adsrc);
                    if (count($parts) > 1) {
                        $info->default_value = reset($parts);
                    } else {
                        $info->default_value = $rawcolumn->adsrc;
                    }
                    $info->default_value = trim($info->default_value, "()'");
                } else {
                    $info->default_value = null;
                }
                $info->max_length    = $rawcolumn->atttypmod >> 16;
                $info->scale         = ($rawcolumn->atttypmod & 0xFFFF) - 4;

            } else if (preg_match('/float(\d)/i', $rawcolumn->type, $matches)) {
                $info->type = 'float';
                $info->meta_type     = 'N';
                $info->primary_key   = false;
                $info->binary        = false;
                $info->unsigned      = null;
                $info->auto_increment= false;
                $info->unique        = null;
                $info->not_null      = ($rawcolumn->attnotnull === 't');
                $info->has_default   = ($rawcolumn->atthasdef === 't');
                if ($info->has_default) {
                                        $parts = explode('::', $rawcolumn->adsrc);
                    if (count($parts) > 1) {
                        $info->default_value = reset($parts);
                    } else {
                        $info->default_value = $rawcolumn->adsrc;
                    }
                    $info->default_value = trim($info->default_value, "()'");
                } else {
                    $info->default_value = null;
                }
                                if ($matches[1] == 8) {
                                        $info->max_length = 8;
                    $info->scale      = 7;
                } else {
                                        $info->max_length = 4;
                    $info->scale      = 2;
                }

            } else if ($rawcolumn->type === 'text') {
                $info->type          = $rawcolumn->type;
                $info->meta_type     = 'X';
                $info->max_length    = -1;
                $info->scale         = null;
                $info->not_null      = ($rawcolumn->attnotnull === 't');
                $info->has_default   = ($rawcolumn->atthasdef === 't');
                if ($info->has_default) {
                    $parts = explode('::', $rawcolumn->adsrc);
                    if (count($parts) > 1) {
                        $info->default_value = reset($parts);
                        $info->default_value = trim($info->default_value, "'");
                    } else {
                        $info->default_value = $rawcolumn->adsrc;
                    }
                } else {
                    $info->default_value = null;
                }
                $info->primary_key   = false;
                $info->binary        = false;
                $info->unsigned      = null;
                $info->auto_increment= false;
                $info->unique        = null;

            } else if ($rawcolumn->type === 'bytea') {
                $info->type          = $rawcolumn->type;
                $info->meta_type     = 'B';
                $info->max_length    = -1;
                $info->scale         = null;
                $info->not_null      = ($rawcolumn->attnotnull === 't');
                $info->has_default   = false;
                $info->default_value = null;
                $info->primary_key   = false;
                $info->binary        = true;
                $info->unsigned      = null;
                $info->auto_increment= false;
                $info->unique        = null;

            }

            $structure[$info->name] = new database_column_info($info);
        }

        pg_free_result($result);

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

        } else if ($column->meta_type === 'B') {             if (!is_null($value)) {                             $value = array('blob' => $value);
            }

        } else if ($value === '') {
            if ($column->meta_type === 'I' or $column->meta_type === 'F' or $column->meta_type === 'N') {
                $value = 0;             }
        }
        return $value;
    }

    
    public function setup_is_unicodedb() {
                $sql = "SHOW server_encoding";
        $this->query_start($sql, null, SQL_QUERY_AUX);
        $result = pg_query($this->pgsql, $sql);
        $this->query_end($result);

        if (!$result) {
            return false;
        }
        $rawcolumn = pg_fetch_object($result);
        $encoding = $rawcolumn->server_encoding;
        pg_free_result($result);

        return (strtoupper($encoding) == 'UNICODE' || strtoupper($encoding) == 'UTF8');
    }

    
    public function change_database_structure($sql, $tablenames = null) {
        $this->get_manager();         if (is_array($sql)) {
            $sql = implode("\n;\n", $sql);
        }
        if (!$this->is_transaction_started()) {
                        $sql = "BEGIN ISOLATION LEVEL SERIALIZABLE;\n$sql\n; COMMIT";
        }

        try {
            $this->query_start($sql, null, SQL_QUERY_STRUCTURE);
            $result = pg_query($this->pgsql, $sql);
            $this->query_end($result);
            pg_free_result($result);
        } catch (ddl_change_structure_exception $e) {
            if (!$this->is_transaction_started()) {
                $result = @pg_query($this->pgsql, "ROLLBACK");
                @pg_free_result($result);
            }
            $this->reset_caches($tablenames);
            throw $e;
        }

        $this->reset_caches($tablenames);
        return true;
    }

    
    public function execute($sql, array $params=null) {
        list($sql, $params, $type) = $this->fix_sql_params($sql, $params);

        if (strpos($sql, ';') !== false) {
            throw new coding_exception('moodle_database::execute() Multiple sql statements found or bound parameters not used properly in query!');
        }

        $this->query_start($sql, $params, SQL_QUERY_UPDATE);
        $result = pg_query_params($this->pgsql, $sql, $params);
        $this->query_end($result);

        pg_free_result($result);
        return true;
    }

    
    public function get_recordset_sql($sql, array $params=null, $limitfrom=0, $limitnum=0) {

        list($limitfrom, $limitnum) = $this->normalise_limit_from_num($limitfrom, $limitnum);

        if ($limitfrom or $limitnum) {
            if ($limitnum < 1) {
                $limitnum = "ALL";
            } else if (PHP_INT_MAX - $limitnum < $limitfrom) {
                                $limitnum = "ALL";
            }
            $sql .= " LIMIT $limitnum OFFSET $limitfrom";
        }

        list($sql, $params, $type) = $this->fix_sql_params($sql, $params);

        $this->query_start($sql, $params, SQL_QUERY_SELECT);
        $result = pg_query_params($this->pgsql, $sql, $params);
        $this->query_end($result);

        return $this->create_recordset($result);
    }

    protected function create_recordset($result) {
        return new pgsql_native_moodle_recordset($result, $this->bytea_oid);
    }

    
    public function get_records_sql($sql, array $params=null, $limitfrom=0, $limitnum=0) {

        list($limitfrom, $limitnum) = $this->normalise_limit_from_num($limitfrom, $limitnum);

        if ($limitfrom or $limitnum) {
            if ($limitnum < 1) {
                $limitnum = "ALL";
            } else if (PHP_INT_MAX - $limitnum < $limitfrom) {
                                $limitnum = "ALL";
            }
            $sql .= " LIMIT $limitnum OFFSET $limitfrom";
        }

        list($sql, $params, $type) = $this->fix_sql_params($sql, $params);
        $this->query_start($sql, $params, SQL_QUERY_SELECT);
        $result = pg_query_params($this->pgsql, $sql, $params);
        $this->query_end($result);

                $numrows = pg_num_fields($result);
        $blobs = array();
        for($i=0; $i<$numrows; $i++) {
            $type_oid = pg_field_type_oid($result, $i);
            if ($type_oid == $this->bytea_oid) {
                $blobs[] = pg_field_name($result, $i);
            }
        }

        $rows = pg_fetch_all($result);
        pg_free_result($result);

        $return = array();
        if ($rows) {
            foreach ($rows as $row) {
                $id = reset($row);
                if ($blobs) {
                    foreach ($blobs as $blob) {
                                                $row[$blob] = $row[$blob] !== null ? pg_unescape_bytea($row[$blob]) : null;
                    }
                }
                if (isset($return[$id])) {
                    $colname = key($row);
                    debugging("Did you remember to make the first column something unique in your call to get_records? Duplicate value '$id' found in column '$colname'.", DEBUG_DEVELOPER);
                }
                $return[$id] = (object)$row;
            }
        }

        return $return;
    }

    
    public function get_fieldset_sql($sql, array $params=null) {
        list($sql, $params, $type) = $this->fix_sql_params($sql, $params);

        $this->query_start($sql, $params, SQL_QUERY_SELECT);
        $result = pg_query_params($this->pgsql, $sql, $params);
        $this->query_end($result);

        $return = pg_fetch_all_columns($result, 0);
        pg_free_result($result);

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
            if ($returnid) {
                $returning = "RETURNING id";
                unset($params['id']);
            } else {
                unset($params['id']);
            }
        }

        if (empty($params)) {
            throw new coding_exception('moodle_database::insert_record_raw() no fields found.');
        }

        $fields = implode(',', array_keys($params));
        $values = array();
        $i = 1;
        foreach ($params as $value) {
            $this->detect_objects($value);
            $values[] = "\$".$i++;
        }
        $values = implode(',', $values);

        $sql = "INSERT INTO {$this->prefix}$table ($fields) VALUES($values) $returning";
        $this->query_start($sql, $params, SQL_QUERY_INSERT);
        $result = pg_query_params($this->pgsql, $sql, $params);
        $this->query_end($result);

        if ($returning !== "") {
            $row = pg_fetch_assoc($result);
            $params['id'] = reset($row);
        }
        pg_free_result($result);

        if (!$returnid) {
            return true;
        }

        return (int)$params['id'];
    }

    
    public function insert_record($table, $dataobject, $returnid=true, $bulk=false) {
        $dataobject = (array)$dataobject;

        $columns = $this->get_columns($table);
        if (empty($columns)) {
            throw new dml_exception('ddltablenotexist', $table);
        }

        $cleaned = array();
        $blobs   = array();

        foreach ($dataobject as $field=>$value) {
            if ($field === 'id') {
                continue;
            }
            if (!isset($columns[$field])) {
                continue;
            }
            $column = $columns[$field];
            $normalised_value = $this->normalise_value($column, $value);
            if (is_array($normalised_value) && array_key_exists('blob', $normalised_value)) {
                $cleaned[$field] = '@#BLOB#@';
                $blobs[$field] = $normalised_value['blob'];
            } else {
                $cleaned[$field] = $normalised_value;
            }
        }

        if (empty($blobs)) {
            return $this->insert_record_raw($table, $cleaned, $returnid, $bulk);
        }

        $id = $this->insert_record_raw($table, $cleaned, true, $bulk);

        foreach ($blobs as $key=>$value) {
            $value = pg_escape_bytea($this->pgsql, $value);
            $sql = "UPDATE {$this->prefix}$table SET $key = '$value'::bytea WHERE id = $id";
            $this->query_start($sql, NULL, SQL_QUERY_UPDATE);
            $result = pg_query($this->pgsql, $sql);
            $this->query_end($result);
            if ($result !== false) {
                pg_free_result($result);
            }
        }

        return ($returnid ? $id : true);

    }

    
    public function insert_records($table, $dataobjects) {
        if (!is_array($dataobjects) and !($dataobjects instanceof Traversable)) {
            throw new coding_exception('insert_records() passed non-traversable object');
        }

                $chunksize = 500;
        if (!empty($this->dboptions['bulkinsertsize'])) {
            $chunksize = (int)$this->dboptions['bulkinsertsize'];
        }

        $columns = $this->get_columns($table, true);

                foreach ($columns as $column) {
            if ($column->binary) {
                parent::insert_records($table, $dataobjects);
                return;
            }
        }

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
        $i = 1;
        $params = array();
        $values = array();
        foreach ($chunk as $dataobject) {
            $vals = array();
            foreach ($columns as $field => $column) {
                $params[] = $this->normalise_value($column, $dataobject[$field]);
                $vals[] = "\$".$i++;
            }
            $values[] = '('.implode(',', $vals).')';
        }

        $fieldssql = '('.implode(',', array_keys($columns)).')';
        $valuessql = implode(',', $values);

        $sql = "INSERT INTO {$this->prefix}$table $fieldssql VALUES $valuessql";
        $this->query_start($sql, $params, SQL_QUERY_INSERT);
        $result = pg_query_params($this->pgsql, $sql, $params);
        $this->query_end($result);
        pg_free_result($result);
    }

    
    public function import_record($table, $dataobject) {
        $dataobject = (array)$dataobject;

        $columns = $this->get_columns($table);
        $cleaned = array();
        $blobs   = array();

        foreach ($dataobject as $field=>$value) {
            $this->detect_objects($value);
            if (!isset($columns[$field])) {
                continue;
            }
            if ($columns[$field]->meta_type === 'B') {
                if (!is_null($value)) {
                    $cleaned[$field] = '@#BLOB#@';
                    $blobs[$field] = $value;
                    continue;
                }
            }

            $cleaned[$field] = $value;
        }

        $this->insert_record_raw($table, $cleaned, false, true, true);
        $id = $dataobject['id'];

        foreach ($blobs as $key=>$value) {
            $value = pg_escape_bytea($this->pgsql, $value);
            $sql = "UPDATE {$this->prefix}$table SET $key = '$value'::bytea WHERE id = $id";
            $this->query_start($sql, NULL, SQL_QUERY_UPDATE);
            $result = pg_query($this->pgsql, $sql);
            $this->query_end($result);
            if ($result !== false) {
                pg_free_result($result);
            }
        }

        return true;
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

        $i = 1;

        $sets = array();
        foreach ($params as $field=>$value) {
            $this->detect_objects($value);
            $sets[] = "$field = \$".$i++;
        }

        $params[] = $id; 
        $sets = implode(',', $sets);
        $sql = "UPDATE {$this->prefix}$table SET $sets WHERE id=\$".$i;

        $this->query_start($sql, $params, SQL_QUERY_UPDATE);
        $result = pg_query_params($this->pgsql, $sql, $params);
        $this->query_end($result);

        pg_free_result($result);
        return true;
    }

    
    public function update_record($table, $dataobject, $bulk=false) {
        $dataobject = (array)$dataobject;

        $columns = $this->get_columns($table);
        $cleaned = array();
        $blobs   = array();

        foreach ($dataobject as $field=>$value) {
            if (!isset($columns[$field])) {
                continue;
            }
            $column = $columns[$field];
            $normalised_value = $this->normalise_value($column, $value);
            if (is_array($normalised_value) && array_key_exists('blob', $normalised_value)) {
                $cleaned[$field] = '@#BLOB#@';
                $blobs[$field] = $normalised_value['blob'];
            } else {
                $cleaned[$field] = $normalised_value;
            }
        }

        $this->update_record_raw($table, $cleaned, $bulk);

        if (empty($blobs)) {
            return true;
        }

        $id = (int)$dataobject['id'];

        foreach ($blobs as $key=>$value) {
            $value = pg_escape_bytea($this->pgsql, $value);
            $sql = "UPDATE {$this->prefix}$table SET $key = '$value'::bytea WHERE id = $id";
            $this->query_start($sql, NULL, SQL_QUERY_UPDATE);
            $result = pg_query($this->pgsql, $sql);
            $this->query_end($result);

            pg_free_result($result);
        }

        return true;
    }

    
    public function set_field_select($table, $newfield, $newvalue, $select, array $params=null) {

        if ($select) {
            $select = "WHERE $select";
        }
        if (is_null($params)) {
            $params = array();
        }
        list($select, $params, $type) = $this->fix_sql_params($select, $params);
        $i = count($params)+1;

                $columns = $this->get_columns($table);
        $column = $columns[$newfield];

        $normalised_value = $this->normalise_value($column, $newvalue);
        if (is_array($normalised_value) && array_key_exists('blob', $normalised_value)) {
                        $normalised_value = pg_escape_bytea($this->pgsql, $normalised_value['blob']);
            $sql = "UPDATE {$this->prefix}$table SET $newfield = '$normalised_value'::bytea $select";
            $this->query_start($sql, NULL, SQL_QUERY_UPDATE);
            $result = pg_query_params($this->pgsql, $sql, $params);
            $this->query_end($result);
            pg_free_result($result);
            return true;
        }

        if (is_null($normalised_value)) {
            $newfield = "$newfield = NULL";
        } else {
            $newfield = "$newfield = \$".$i;
            $params[] = $normalised_value;
        }
        $sql = "UPDATE {$this->prefix}$table SET $newfield $select";

        $this->query_start($sql, $params, SQL_QUERY_UPDATE);
        $result = pg_query_params($this->pgsql, $sql, $params);
        $this->query_end($result);

        pg_free_result($result);

        return true;
    }

    
    public function delete_records_select($table, $select, array $params=null) {
        if ($select) {
            $select = "WHERE $select";
        }
        $sql = "DELETE FROM {$this->prefix}$table $select";

        list($sql, $params, $type) = $this->fix_sql_params($sql, $params);

        $this->query_start($sql, $params, SQL_QUERY_UPDATE);
        $result = pg_query_params($this->pgsql, $sql, $params);
        $this->query_end($result);

        pg_free_result($result);

        return true;
    }

    
    public function sql_like($fieldname, $param, $casesensitive = true, $accentsensitive = true, $notlike = false, $escapechar = '\\') {
        if (strpos($param, '%') !== false) {
            debugging('Potential SQL injection detected, sql_like() expects bound parameters (? or :named)');
        }
        if ($escapechar === '\\') {
                                    $escapechar = '\\\\';
        }

                if ($casesensitive) {
            $LIKE = $notlike ? 'NOT LIKE' : 'LIKE';
        } else {
            $LIKE = $notlike ? 'NOT ILIKE' : 'ILIKE';
        }
        return "$fieldname $LIKE $param ESCAPE E'$escapechar'";
    }

    public function sql_bitxor($int1, $int2) {
        return '((' . $int1 . ') # (' . $int2 . '))';
    }

    public function sql_cast_char2int($fieldname, $text=false) {
        return ' CAST(' . $fieldname . ' AS INT) ';
    }

    public function sql_cast_char2real($fieldname, $text=false) {
        return " $fieldname::real ";
    }

    public function sql_concat() {
        $arr = func_get_args();
        $s = implode(' || ', $arr);
        if ($s === '') {
            return " '' ";
        }
                        return " '' || $s ";
    }

    public function sql_concat_join($separator="' '", $elements=array()) {
        for ($n=count($elements)-1; $n > 0 ; $n--) {
            array_splice($elements, $n, 0, $separator);
        }
        $s = implode(' || ', $elements);
        if ($s === '') {
            return " '' ";
        }
        return " $s ";
    }

    public function sql_regex_supported() {
        return true;
    }

    public function sql_regex($positivematch=true) {
        return $positivematch ? '~*' : '!~*';
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

        $sql = "SET statement_timeout TO $timeoutmilli";
        $this->query_start($sql, null, SQL_QUERY_AUX);
        $result = pg_query($this->pgsql, $sql);
        $this->query_end($result);

        if ($result) {
            pg_free_result($result);
        }

        $sql = "SELECT pg_advisory_lock($rowid)";
        $this->query_start($sql, null, SQL_QUERY_AUX);
        $start = time();
        $result = pg_query($this->pgsql, $sql);
        $end = time();
        try {
            $this->query_end($result);
        } catch (dml_exception $ex) {
            if ($end - $start >= $timeout) {
                throw new dml_sessionwait_exception();
            } else {
                throw $ex;
            }
        }

        if ($result) {
            pg_free_result($result);
        }

        $sql = "SET statement_timeout TO DEFAULT";
        $this->query_start($sql, null, SQL_QUERY_AUX);
        $result = pg_query($this->pgsql, $sql);
        $this->query_end($result);

        if ($result) {
            pg_free_result($result);
        }
    }

    public function release_session_lock($rowid) {
        if (!$this->session_lock_supported()) {
            return;
        }
        if (!$this->used_for_db_sessions) {
            return;
        }

        parent::release_session_lock($rowid);

        $sql = "SELECT pg_advisory_unlock($rowid)";
        $this->query_start($sql, null, SQL_QUERY_AUX);
        $result = pg_query($this->pgsql, $sql);
        $this->query_end($result);

        if ($result) {
            pg_free_result($result);
        }
    }

    
    protected function begin_transaction() {
        $this->savepointpresent = true;
        $sql = "BEGIN ISOLATION LEVEL READ COMMITTED; SAVEPOINT moodle_pg_savepoint";
        $this->query_start($sql, NULL, SQL_QUERY_AUX);
        $result = pg_query($this->pgsql, $sql);
        $this->query_end($result);

        pg_free_result($result);
    }

    
    protected function commit_transaction() {
        $this->savepointpresent = false;
        $sql = "RELEASE SAVEPOINT moodle_pg_savepoint; COMMIT";
        $this->query_start($sql, NULL, SQL_QUERY_AUX);
        $result = pg_query($this->pgsql, $sql);
        $this->query_end($result);

        pg_free_result($result);
    }

    
    protected function rollback_transaction() {
        $this->savepointpresent = false;
        $sql = "RELEASE SAVEPOINT moodle_pg_savepoint; ROLLBACK";
        $this->query_start($sql, NULL, SQL_QUERY_AUX);
        $result = pg_query($this->pgsql, $sql);
        $this->query_end($result);

        pg_free_result($result);
    }

    
    private function trim_quotes($str) {
        return trim(trim($str), "'\"");
    }
}
