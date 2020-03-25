<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/pdo_moodle_database.php');


class sqlite3_pdo_moodle_database extends pdo_moodle_database {
    protected $database_file_extension = '.sq3.php';
    
    public function driver_installed() {
        if (!extension_loaded('pdo_sqlite') || !extension_loaded('pdo')){
            return get_string('sqliteextensionisnotpresentinphp', 'install');
        }
        return true;
    }

    
    public function get_dbfamily() {
        return 'sqlite';
    }

    
    protected function get_dbtype() {
        return 'sqlite3';
    }

    protected function configure_dbconnection() {
                                $this->pdb->exec('CREATE TABLE IF NOT EXISTS "<?php die?>" (id int)');
        $this->pdb->exec('PRAGMA synchronous=OFF');
        $this->pdb->exec('PRAGMA short_column_names=1');
        $this->pdb->exec('PRAGMA encoding="UTF-8"');
        $this->pdb->exec('PRAGMA case_sensitive_like=0');
        $this->pdb->exec('PRAGMA locking_mode=NORMAL');
    }

    
    public function create_database($dbhost, $dbuser, $dbpass, $dbname, array $dboptions=null) {
        global $CFG;

        $this->dbhost = $dbhost;
        $this->dbuser = $dbuser;
        $this->dbpass = $dbpass;
        $this->dbname = $dbname;
        $filepath = $this->get_dbfilepath();
        $dirpath = dirname($filepath);
        @mkdir($dirpath, $CFG->directorypermissions, true);
        return touch($filepath);
    }

    
    protected function get_dsn() {
        return 'sqlite:'.$this->get_dbfilepath();
    }

    
    public function get_dbfilepath() {
        global $CFG;
        if (!empty($this->dboptions['file'])) {
            return $this->dboptions['file'];
        }
        if ($this->dbhost && $this->dbhost != 'localhost') {
            $path = $this->dbhost;
        } else {
            $path = $CFG->dataroot;
        }
        $path = rtrim($path, '\\/').'/';
        if (!empty($this->dbuser)) {
            $path .= $this->dbuser.'_';
        }
        $path .= $this->dbname.'_'.md5($this->dbpass).$this->database_file_extension;
        return $path;
    }

    
    public function get_tables($usecache=true) {
        $tables = array();

        $sql = 'SELECT name FROM sqlite_master WHERE type="table" UNION ALL SELECT name FROM sqlite_temp_master WHERE type="table" ORDER BY name';
        if ($this->debug) {
            $this->debug_query($sql);
        }
        $rstables = $this->pdb->query($sql);
        foreach ($rstables as $table) {
            $table = $table['name'];
            $table = strtolower($table);
            if ($this->prefix !== false && $this->prefix !== '') {
                if (strpos($table, $this->prefix) !== 0) {
                    continue;
                }
                $table = substr($table, strlen($this->prefix));
            }
            $tables[$table] = $table;
        }
        return $tables;
    }

    
    public function get_indexes($table) {
        $indexes = array();
        $sql = 'PRAGMA index_list('.$this->prefix.$table.')';
        if ($this->debug) {
            $this->debug_query($sql);
        }
        $rsindexes = $this->pdb->query($sql);
        foreach($rsindexes as $index) {
            $unique = (boolean)$index['unique'];
            $index = $index['name'];
            $sql = 'PRAGMA index_info("'.$index.'")';
            if ($this->debug) {
                $this->debug_query($sql);
            }
            $rscolumns = $this->pdb->query($sql);
            $columns = array();
            foreach($rscolumns as $row) {
                $columns[] = strtolower($row['name']);
            }
            $index = strtolower($index);
            $indexes[$index]['unique'] = $unique;
            $indexes[$index]['columns'] = $columns;
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

                $sql = 'SELECT sql FROM sqlite_master WHERE type="table" AND tbl_name="'.$this->prefix.$table.'"';
        if ($this->debug) {
            $this->debug_query($sql);
        }
        $createsql = $this->pdb->query($sql)->fetch();
        if (!$createsql) {
            return false;
        }
        $createsql = $createsql['sql'];

        $sql = 'PRAGMA table_info("'. $this->prefix.$table.'")';
        if ($this->debug) {
            $this->debug_query($sql);
        }
        $rscolumns = $this->pdb->query($sql);
        foreach ($rscolumns as $row) {
            $columninfo = array(
                'name' => strtolower($row['name']),                 'not_null' =>(boolean)$row['notnull'],
                'primary_key' => (boolean)$row['pk'],
                'has_default' => !is_null($row['dflt_value']),
                'default_value' => $row['dflt_value'],
                'auto_increment' => false,
                'binary' => false,
                            );
            $type = explode('(', $row['type']);
            $columninfo['type'] = strtolower($type[0]);
            if (count($type) > 1) {
                $size = explode(',', trim($type[1], ')'));
                $columninfo['max_length'] = $size[0];
                if (count($size) > 1) {
                    $columninfo['scale'] = $size[1];
                }
            }
                                    switch(substr($columninfo['type'], 0, 3)) {
                case 'int':                     if ($columninfo['primary_key'] && preg_match('/'.$columninfo['name'].'\W+integer\W+primary\W+key\W+autoincrement/im', $createsql)) {
                        $columninfo['meta_type'] = 'R';
                        $columninfo['auto_increment'] = true;
                    } else {
                        $columninfo['meta_type'] = 'I';
                    }
                    break;
                case 'num':                 case 'rea':                 case 'dou':                 case 'flo':                     $columninfo['meta_type'] = 'N';
                    break;
                case 'var':                 case 'cha':                     $columninfo['meta_type'] = 'C';
                    break;
                case 'enu':                     $columninfo['meta_type'] = 'C';
                    break;
                case 'tex':                 case 'clo':                     $columninfo['meta_type'] = 'X';
                    break;
                case 'blo':                 case 'non':                     $columninfo['meta_type'] = 'B';
                    $columninfo['binary'] = true;
                    break;
                case 'boo':                 case 'bit':                 case 'log':                     $columninfo['meta_type'] = 'L';
                    $columninfo['max_length'] = 1;
                    break;
                case 'tim':                     $columninfo['meta_type'] = 'T';
                    break;
                case 'dat':                     $columninfo['meta_type'] = 'D';
                    break;
            }
            if ($columninfo['has_default'] && ($columninfo['meta_type'] == 'X' || $columninfo['meta_type']== 'C')) {
                                $columninfo['default_value'] = substr($columninfo['default_value'], 1, -1);
            }
            $structure[$columninfo['name']] = new database_column_info($columninfo);
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
        return $value;
    }

    
    protected function get_limit_clauses($sql, $limitfrom=0, $limitnum=0) {
        if ($limitnum) {
            $sql .= ' LIMIT '.$limitnum;
            if ($limitfrom) {
                $sql .= ' OFFSET '.$limitfrom;
            }
        }
        return $sql;
    }

    
    public function delete_records($table, array $conditions=null) {
        if (is_null($conditions)) {
            return $this->execute("DELETE FROM {{$table}}");
        }
        list($select, $params) = $this->where_clause($table, $conditions);
        return $this->delete_records_select($table, $select, $params);
    }

    
    public function sql_concat() {
        $elements = func_get_args();
        return implode('||', $elements);
    }

    
    public function sql_concat_join($separator="' '", $elements=array()) {
                                        for ($n=count($elements)-1; $n > 0; $n--) {
            array_splice($elements, $n, 0, $separator);
        }
        return implode('||', $elements);
    }

    
    public function sql_bitxor($int1, $int2) {
        return '( ~' . $this->sql_bitand($int1, $int2) . ' & ' . $this->sql_bitor($int1, $int2) . ')';
    }
}
