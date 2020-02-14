<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/ddl/sql_generator.php');


class mysql_sql_generator extends sql_generator {

    
    
    public $quote_string = '`';

    
    public $default_for_char = '';

    
    public $drop_default_value_required = true;

    
    public $drop_default_value = null;

    
    public $primary_key_name = '';

    
    public $drop_primary_key = 'ALTER TABLE TABLENAME DROP PRIMARY KEY';

    
    public $drop_unique_key = 'ALTER TABLE TABLENAME DROP KEY KEYNAME';

    
    public $drop_foreign_key = 'ALTER TABLE TABLENAME DROP FOREIGN KEY KEYNAME';

    
    public $sequence_extra_code = false;

    
    public $sequence_name = 'auto_increment';

    public $add_after_clause = true; 
    
    public $concat_character = null;

    
    public $alter_column_sql = 'ALTER TABLE TABLENAME MODIFY COLUMN COLUMNSPECS';

    
    public $drop_index_sql = 'ALTER TABLE TABLENAME DROP INDEX INDEXNAME';

    
    public $rename_index_sql = null;

    
    public $rename_key_sql = null;

    
    const ANTELOPE_MAX_ROW_SIZE = 8126;

    
    public function getResetSequenceSQL($table) {

        if ($table instanceof xmldb_table) {
            $tablename = $table->getName();
        } else {
            $tablename = $table;
        }

                $value = (int)$this->mdb->get_field_sql('SELECT MAX(id) FROM {'.$tablename.'}');
        $value++;
        return array("ALTER TABLE $this->prefix$tablename AUTO_INCREMENT = $value");
    }

    
    public function guess_antolope_row_size(array $columns) {
        throw new coding_exception('guess_antolope_row_size() can not be used any more, please use guess_antelope_row_size() instead.');
    }

    
    public function guess_antelope_row_size(array $columns) {

        if (empty($columns)) {
            return 0;
        }

        $size = 0;
        $first = reset($columns);

        if (count($columns) > 1) {
                                    $size += 1000;
        }

        if ($first instanceof xmldb_field) {
            foreach ($columns as $field) {
                switch ($field->getType()) {
                    case XMLDB_TYPE_TEXT:
                        $size += 768;
                        break;
                    case XMLDB_TYPE_BINARY:
                        $size += 768;
                        break;
                    case XMLDB_TYPE_CHAR:
                        $bytes = $field->getLength() * 3;
                        if ($bytes > 768) {
                            $bytes = 768;
                        }
                        $size += $bytes;
                        break;
                    default:
                                                $size += 8;
                }
            }

        } else if ($first instanceof database_column_info) {
            foreach ($columns as $column) {
                switch ($column->meta_type) {
                    case 'X':
                        $size += 768;
                        break;
                    case 'B':
                        $size += 768;
                        break;
                    case 'C':
                        $bytes = $column->max_length * 3;
                        if ($bytes > 768) {
                            $bytes = 768;
                        }
                        $size += $bytes;
                        break;
                    default:
                                                $size += 8;
                }
            }
        }

        return $size;
    }

    
    public function getCreateTableSQL($xmldb_table) {
                $engine = $this->mdb->get_dbengine();
                $collation = $this->mdb->get_dbcollation();

                $rowformat = "";
        $size = $this->guess_antelope_row_size($xmldb_table->getFields());
        if ($size > self::ANTELOPE_MAX_ROW_SIZE) {
            if ($this->mdb->is_compressed_row_format_supported()) {
                $rowformat = "\n ROW_FORMAT=Compressed";
            }
        }

        $sqlarr = parent::getCreateTableSQL($xmldb_table);

                                $sqls = array();
        $prevcreate = null;
        $matches = null;
        foreach ($sqlarr as $sql) {
            if (preg_match('/^CREATE TABLE ([^ ]+)/', $sql, $matches)) {
                $prevcreate = $matches[1];
                $sql = preg_replace('/\s*\)\s*$/s', '/*keyblock*/)', $sql);
                                if ($engine) {
                    $sql .= "\n ENGINE = $engine";
                }
                if ($collation) {
                    if (strpos($collation, 'utf8_') === 0) {
                        $sql .= "\n DEFAULT CHARACTER SET utf8";
                    }
                    $sql .= "\n DEFAULT COLLATE = $collation";
                }
                if ($rowformat) {
                    $sql .= $rowformat;
                }
                $sqls[] = $sql;
                continue;
            }
            if ($prevcreate) {
                if (preg_match('/^ALTER TABLE '.$prevcreate.' COMMENT=(.*)$/s', $sql, $matches)) {
                    $prev = array_pop($sqls);
                    $prev .= "\n COMMENT=$matches[1]";
                    $sqls[] = $prev;
                    continue;
                }
                if (preg_match('/^CREATE INDEX ([^ ]+) ON '.$prevcreate.' (.*)$/s', $sql, $matches)) {
                    $prev = array_pop($sqls);
                    if (strpos($prev, '/*keyblock*/')) {
                        $prev = str_replace('/*keyblock*/', "\n, KEY $matches[1] $matches[2]/*keyblock*/", $prev);
                        $sqls[] = $prev;
                        continue;
                    } else {
                        $sqls[] = $prev;
                    }
                }
                if (preg_match('/^CREATE UNIQUE INDEX ([^ ]+) ON '.$prevcreate.' (.*)$/s', $sql, $matches)) {
                    $prev = array_pop($sqls);
                    if (strpos($prev, '/*keyblock*/')) {
                        $prev = str_replace('/*keyblock*/', "\n, UNIQUE KEY $matches[1] $matches[2]/*keyblock*/", $prev);
                        $sqls[] = $prev;
                        continue;
                    } else {
                        $sqls[] = $prev;
                    }
                }
            }
            $prevcreate = null;
            $sqls[] = $sql;
        }

        foreach ($sqls as $key => $sql) {
            $sqls[$key] = str_replace('/*keyblock*/', "\n", $sql);
        }

        return $sqls;
    }

    
    public function getAddFieldSQL($xmldb_table, $xmldb_field, $skip_type_clause = NULL, $skip_default_clause = NULL, $skip_notnull_clause = NULL) {
        $sqls = parent::getAddFieldSQL($xmldb_table, $xmldb_field, $skip_type_clause, $skip_default_clause, $skip_notnull_clause);

        if ($this->table_exists($xmldb_table)) {
            $tablename = $xmldb_table->getName();

            $size = $this->guess_antelope_row_size($this->mdb->get_columns($tablename));
            $size += $this->guess_antelope_row_size(array($xmldb_field));

            if ($size > self::ANTELOPE_MAX_ROW_SIZE) {
                if ($this->mdb->is_compressed_row_format_supported()) {
                    $format = strtolower($this->mdb->get_row_format($tablename));
                    if ($format === 'compact' or $format === 'redundant') {
                                                array_unshift($sqls, "ALTER TABLE {$this->prefix}$tablename ROW_FORMAT=Compressed");
                    }
                }
            }
        }

        return $sqls;
    }

    
    public function getCreateTempTableSQL($xmldb_table) {
                $collation = $this->mdb->get_dbcollation();
        $this->temptables->add_temptable($xmldb_table->getName());

        $sqlarr = parent::getCreateTableSQL($xmldb_table);

                foreach ($sqlarr as $i=>$sql) {
            if (strpos($sql, 'CREATE TABLE ') === 0) {
                                $sqlarr[$i] = preg_replace('/^CREATE TABLE (.*)/s', 'CREATE TEMPORARY TABLE $1', $sql);
                if ($collation) {
                    if (strpos($collation, 'utf8_') === 0) {
                        $sqlarr[$i] .= " DEFAULT CHARACTER SET utf8";
                    }
                    $sqlarr[$i] .= " DEFAULT COLLATE $collation";
                }
            }
        }

        return $sqlarr;
    }

    
    public function getDropTableSQL($xmldb_table) {
        $sqlarr = parent::getDropTableSQL($xmldb_table);
        if ($this->temptables->is_temptable($xmldb_table->getName())) {
            $sqlarr = preg_replace('/^DROP TABLE/', "DROP TEMPORARY TABLE", $sqlarr);
            $this->temptables->delete_temptable($xmldb_table->getName());
        }
        return $sqlarr;
    }

    
    public function getTypeSQL($xmldb_type, $xmldb_length=null, $xmldb_decimals=null) {

        switch ($xmldb_type) {
            case XMLDB_TYPE_INTEGER:                    if (empty($xmldb_length)) {
                    $xmldb_length = 10;
                }
                if ($xmldb_length > 9) {
                    $dbtype = 'BIGINT';
                } else if ($xmldb_length > 6) {
                    $dbtype = 'INT';
                } else if ($xmldb_length > 4) {
                    $dbtype = 'MEDIUMINT';
                } else if ($xmldb_length > 2) {
                    $dbtype = 'SMALLINT';
                } else {
                    $dbtype = 'TINYINT';
                }
                $dbtype .= '(' . $xmldb_length . ')';
                break;
            case XMLDB_TYPE_NUMBER:
                $dbtype = $this->number_type;
                if (!empty($xmldb_length)) {
                    $dbtype .= '(' . $xmldb_length;
                    if (!empty($xmldb_decimals)) {
                        $dbtype .= ',' . $xmldb_decimals;
                    }
                    $dbtype .= ')';
                }
                break;
            case XMLDB_TYPE_FLOAT:
                $dbtype = 'DOUBLE';
                if (!empty($xmldb_decimals)) {
                    if ($xmldb_decimals < 6) {
                        $dbtype = 'FLOAT';
                    }
                }
                if (!empty($xmldb_length)) {
                    $dbtype .= '(' . $xmldb_length;
                    if (!empty($xmldb_decimals)) {
                        $dbtype .= ',' . $xmldb_decimals;
                    } else {
                        $dbtype .= ', 0';                     }
                    $dbtype .= ')';
                }
                break;
            case XMLDB_TYPE_CHAR:
                $dbtype = 'VARCHAR';
                if (empty($xmldb_length)) {
                    $xmldb_length='255';
                }
                $dbtype .= '(' . $xmldb_length . ')';
                if ($collation = $this->mdb->get_dbcollation()) {
                    if (strpos($collation, 'utf8_') === 0) {
                        $dbtype .= " CHARACTER SET utf8";
                    }
                    $dbtype .= " COLLATE $collation";
                }
                break;
            case XMLDB_TYPE_TEXT:
                $dbtype = 'LONGTEXT';
                if ($collation = $this->mdb->get_dbcollation()) {
                    if (strpos($collation, 'utf8_') === 0) {
                        $dbtype .= " CHARACTER SET utf8";
                    }
                    $dbtype .= " COLLATE $collation";
                }
                break;
            case XMLDB_TYPE_BINARY:
                $dbtype = 'LONGBLOB';
                break;
            case XMLDB_TYPE_DATETIME:
                $dbtype = 'DATETIME';
        }
        return $dbtype;
    }

    
    public function getCreateDefaultSQL($xmldb_table, $xmldb_field) {
                        return $this->getAlterFieldSQL($xmldb_table, $xmldb_field);
    }

    
    public function getRenameFieldSQL($xmldb_table, $xmldb_field, $newname) {
        
                $xmldb_field_clone = clone($xmldb_field);

                $xmldb_field_clone->setName($newname);

        $fieldsql = $this->getFieldSQL($xmldb_table, $xmldb_field_clone);

        $sql = 'ALTER TABLE ' . $this->getTableName($xmldb_table) . ' CHANGE ' .
               $xmldb_field->getName() . ' ' . $fieldsql;

        return array($sql);
    }

    
    public function getDropDefaultSQL($xmldb_table, $xmldb_field) {
                        return $this->getAlterFieldSQL($xmldb_table, $xmldb_field);
    }

    
    function getCommentSQL ($xmldb_table) {
        $comment = '';

        if ($xmldb_table->getComment()) {
            $comment .= 'ALTER TABLE ' . $this->getTableName($xmldb_table);
            $comment .= " COMMENT='" . $this->addslashes(substr($xmldb_table->getComment(), 0, 60)) . "'";
        }
        return array($comment);
    }

    
    public function isNameInUse($object_name, $type, $table_name) {

        switch($type) {
            case 'ix':
            case 'uix':
                                $metatables = $this->mdb->get_tables();
                if (isset($metatables[$table_name])) {
                                        if ($indexes = $this->mdb->get_indexes($table_name)) {
                                                if (isset($indexes[$object_name])) {
                            return true;
                        }
                    }
                }
                break;
        }
        return false;     }


    
    public static function getReservedWords() {
                        $reserved_words = array (
            'accessible', 'add', 'all', 'alter', 'analyze', 'and', 'as', 'asc',
            'asensitive', 'before', 'between', 'bigint', 'binary',
            'blob', 'both', 'by', 'call', 'cascade', 'case', 'change',
            'char', 'character', 'check', 'collate', 'column',
            'condition', 'connection', 'constraint', 'continue',
            'convert', 'create', 'cross', 'current_date', 'current_time',
            'current_timestamp', 'current_user', 'cursor', 'database',
            'databases', 'day_hour', 'day_microsecond',
            'day_minute', 'day_second', 'dec', 'decimal', 'declare',
            'default', 'delayed', 'delete', 'desc', 'describe',
            'deterministic', 'distinct', 'distinctrow', 'div', 'double',
            'drop', 'dual', 'each', 'else', 'elseif', 'enclosed', 'escaped',
            'exists', 'exit', 'explain', 'false', 'fetch', 'float', 'float4',
            'float8', 'for', 'force', 'foreign', 'from', 'fulltext', 'grant',
            'group', 'having', 'high_priority', 'hour_microsecond',
            'hour_minute', 'hour_second', 'if', 'ignore', 'in', 'index',
            'infile', 'inner', 'inout', 'insensitive', 'insert', 'int', 'int1',
            'int2', 'int3', 'int4', 'int8', 'integer', 'interval', 'into', 'is',
            'iterate', 'join', 'key', 'keys', 'kill', 'leading', 'leave', 'left',
            'like', 'limit', 'linear', 'lines', 'load', 'localtime', 'localtimestamp',
            'lock', 'long', 'longblob', 'longtext', 'loop', 'low_priority', 'master_heartbeat_period',
            'master_ssl_verify_server_cert', 'match', 'mediumblob', 'mediumint', 'mediumtext',
            'middleint', 'minute_microsecond', 'minute_second',
            'mod', 'modifies', 'natural', 'not', 'no_write_to_binlog',
            'null', 'numeric', 'on', 'optimize', 'option', 'optionally',
            'or', 'order', 'out', 'outer', 'outfile', 'overwrite', 'precision', 'primary',
            'procedure', 'purge', 'raid0', 'range', 'read', 'read_only', 'read_write', 'reads', 'real',
            'references', 'regexp', 'release', 'rename', 'repeat', 'replace',
            'require', 'restrict', 'return', 'revoke', 'right', 'rlike', 'schema',
            'schemas', 'second_microsecond', 'select', 'sensitive',
            'separator', 'set', 'show', 'smallint', 'soname', 'spatial',
            'specific', 'sql', 'sqlexception', 'sqlstate', 'sqlwarning',
            'sql_big_result', 'sql_calc_found_rows', 'sql_small_result',
            'ssl', 'starting', 'straight_join', 'table', 'terminated', 'then',
            'tinyblob', 'tinyint', 'tinytext', 'to', 'trailing', 'trigger', 'true',
            'undo', 'union', 'unique', 'unlock', 'unsigned', 'update',
            'upgrade', 'usage', 'use', 'using', 'utc_date', 'utc_time',
            'utc_timestamp', 'values', 'varbinary', 'varchar', 'varcharacter',
            'varying', 'when', 'where', 'while', 'with', 'write', 'x509',
            'xor', 'year_month', 'zerofill'
        );
        return $reserved_words;
    }
}
