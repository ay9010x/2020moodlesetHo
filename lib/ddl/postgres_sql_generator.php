<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/ddl/sql_generator.php');



class postgres_sql_generator extends sql_generator {

    
    
    public $number_type = 'NUMERIC';

    
    public $default_for_char = '';

    
    public $sequence_extra_code = false;

    
    public $sequence_name = 'BIGSERIAL';

    
    public $sequence_name_small = 'SERIAL';

    
    public $sequence_only = true;

    
    public $rename_index_sql = 'ALTER TABLE OLDINDEXNAME RENAME TO NEWINDEXNAME';

    
    public $rename_key_sql = null;

    
    protected $std_strings = null;

    
    public function getResetSequenceSQL($table) {

        if ($table instanceof xmldb_table) {
            $tablename = $table->getName();
        } else {
            $tablename = $table;
        }

                $value = (int)$this->mdb->get_field_sql('SELECT MAX(id) FROM {'.$tablename.'}');
        $value++;
        return array("ALTER SEQUENCE $this->prefix{$tablename}_id_seq RESTART WITH $value");
    }

    
    public function getCreateTempTableSQL($xmldb_table) {
        $this->temptables->add_temptable($xmldb_table->getName());
        $sqlarr = $this->getCreateTableSQL($xmldb_table);
        $sqlarr = preg_replace('/^CREATE TABLE/', "CREATE TEMPORARY TABLE", $sqlarr);
        return $sqlarr;
    }

    
    public function getDropTableSQL($xmldb_table) {
        $sqlarr = parent::getDropTableSQL($xmldb_table);
        if ($this->temptables->is_temptable($xmldb_table->getName())) {
            $this->temptables->delete_temptable($xmldb_table->getName());
        }
        return $sqlarr;
    }

    
    public function getCreateIndexSQL($xmldb_table, $xmldb_index) {
        $sqls = parent::getCreateIndexSQL($xmldb_table, $xmldb_index);

        $hints = $xmldb_index->getHints();
        $fields = $xmldb_index->getFields();
        if (in_array('varchar_pattern_ops', $hints) and count($fields) == 1) {
                        foreach ($sqls as $sql) {
                $field = reset($fields);
                $count = 0;
                $newindex = preg_replace("/^CREATE( UNIQUE)? INDEX ([a-z0-9_]+) ON ([a-z0-9_]+) \($field\)$/", "CREATE INDEX \\2_pattern ON \\3 USING btree ($field varchar_pattern_ops)", $sql, -1, $count);
                if ($count != 1) {
                    debugging('Unexpected getCreateIndexSQL() structure.');
                    continue;
                }
                $sqls[] = $newindex;
            }
        }

        return $sqls;
    }

    
    public function getTypeSQL($xmldb_type, $xmldb_length=null, $xmldb_decimals=null) {

        switch ($xmldb_type) {
            case XMLDB_TYPE_INTEGER:                    if (empty($xmldb_length)) {
                    $xmldb_length = 10;
                }
                if ($xmldb_length > 9) {
                    $dbtype = 'BIGINT';
                } else if ($xmldb_length > 4) {
                    $dbtype = 'INTEGER';
                } else {
                    $dbtype = 'SMALLINT';
                }
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
                $dbtype = 'DOUBLE PRECISION';
                if (!empty($xmldb_decimals)) {
                    if ($xmldb_decimals < 6) {
                        $dbtype = 'REAL';
                    }
                }
                break;
            case XMLDB_TYPE_CHAR:
                $dbtype = 'VARCHAR';
                if (empty($xmldb_length)) {
                    $xmldb_length='255';
                }
                $dbtype .= '(' . $xmldb_length . ')';
                break;
            case XMLDB_TYPE_TEXT:
                $dbtype = 'TEXT';
                break;
            case XMLDB_TYPE_BINARY:
                $dbtype = 'BYTEA';
                break;
            case XMLDB_TYPE_DATETIME:
                $dbtype = 'TIMESTAMP';
                break;
        }
        return $dbtype;
    }

    
    function getCommentSQL ($xmldb_table) {

        $comment = "COMMENT ON TABLE " . $this->getTableName($xmldb_table);
        $comment.= " IS '" . $this->addslashes(substr($xmldb_table->getComment(), 0, 250)) . "'";

        return array($comment);
    }

    
    public function getRenameTableExtraSQL($xmldb_table, $newname) {

        $results = array();

        $newt = new xmldb_table($newname);

        $xmldb_field = new xmldb_field('id'); 
        $oldseqname = $this->getTableName($xmldb_table) . '_' . $xmldb_field->getName() . '_seq';
        $newseqname = $this->getTableName($newt) . '_' . $xmldb_field->getName() . '_seq';

                $results[] = 'ALTER TABLE ' . $oldseqname . ' RENAME TO ' . $newseqname;

        return $results;
    }

    
    public function getAlterFieldSQL($xmldb_table, $xmldb_field, $skip_type_clause = NULL, $skip_default_clause = NULL, $skip_notnull_clause = NULL) {
        $results = array();     
                $tablename = $xmldb_table->getName();
        $fieldname = $xmldb_field->getName();

                $meta = $this->mdb->get_columns($tablename);
        $metac = $meta[$xmldb_field->getName()];
        $oldmetatype = $metac->meta_type;
        $oldlength = $metac->max_length;
        $olddecimals = empty($metac->scale) ? null : $metac->scale;
        $oldnotnull = empty($metac->not_null) ? false : $metac->not_null;
        $olddefault = empty($metac->has_default) ? null : $metac->default_value;

        $typechanged = true;          $precisionchanged = true;          $decimalchanged = true;          $defaultchanged = true;          $notnullchanged = true;  
                if (($xmldb_field->getType() == XMLDB_TYPE_INTEGER && $oldmetatype == 'I') ||
            ($xmldb_field->getType() == XMLDB_TYPE_NUMBER  && $oldmetatype == 'N') ||
            ($xmldb_field->getType() == XMLDB_TYPE_FLOAT   && $oldmetatype == 'F') ||
            ($xmldb_field->getType() == XMLDB_TYPE_CHAR    && $oldmetatype == 'C') ||
            ($xmldb_field->getType() == XMLDB_TYPE_TEXT    && $oldmetatype == 'X') ||
            ($xmldb_field->getType() == XMLDB_TYPE_BINARY  && $oldmetatype == 'B')) {
            $typechanged = false;
        }
                if (($xmldb_field->getType() == XMLDB_TYPE_TEXT) ||
            ($xmldb_field->getType() == XMLDB_TYPE_BINARY) ||
            ($oldlength == -1) ||
            ($xmldb_field->getLength() == $oldlength)) {
            $precisionchanged = false;
        }
                if (($xmldb_field->getType() == XMLDB_TYPE_INTEGER) ||
            ($xmldb_field->getType() == XMLDB_TYPE_CHAR) ||
            ($xmldb_field->getType() == XMLDB_TYPE_TEXT) ||
            ($xmldb_field->getType() == XMLDB_TYPE_BINARY) ||
            (!$xmldb_field->getDecimals()) ||
            (!$olddecimals) ||
            ($xmldb_field->getDecimals() == $olddecimals)) {
            $decimalchanged = false;
        }
                if (($xmldb_field->getDefault() === null && $olddefault === null) ||
            ($xmldb_field->getDefault() === $olddefault)) {
            $defaultchanged = false;
        }
                if (($xmldb_field->getNotnull() === $oldnotnull)) {
            $notnullchanged = false;
        }

                $tablename = $this->getTableName($xmldb_table);
        $fieldname = $this->getEncQuoted($xmldb_field->getName());

                $specschanged = $typechanged || $precisionchanged || $decimalchanged;

                if ($specschanged) {
                        if ($olddefault !== null) {
                $results[] = 'ALTER TABLE ' . $tablename . ' ALTER COLUMN ' . $fieldname . ' DROP DEFAULT';                 }
            $alterstmt = 'ALTER TABLE ' . $tablename . ' ALTER COLUMN ' . $this->getEncQuoted($xmldb_field->getName()) .
                ' TYPE' . $this->getFieldSQL($xmldb_table, $xmldb_field, null, true, true, null, false);
                        if (($oldmetatype == 'C' || $oldmetatype == 'X') &&
                ($xmldb_field->getType() == XMLDB_TYPE_NUMBER || $xmldb_field->getType() == XMLDB_TYPE_FLOAT)) {
                $alterstmt .= ' USING CAST('.$fieldname.' AS NUMERIC)';             } else if (($oldmetatype == 'C' || $oldmetatype == 'X') &&
                $xmldb_field->getType() == XMLDB_TYPE_INTEGER) {
                $alterstmt .= ' USING CAST(CAST('.$fieldname.' AS NUMERIC) AS INTEGER)';             }
            $results[] = $alterstmt;
        }

                if ($defaultchanged || $specschanged) {
            $default_clause = $this->getDefaultClause($xmldb_field);
            if ($default_clause) {
                $sql = 'ALTER TABLE ' . $tablename . ' ALTER COLUMN ' . $fieldname . ' SET' . $default_clause;                     $results[] = $sql;
            } else {
                if (!$specschanged) {                         $results[] = 'ALTER TABLE ' . $tablename . ' ALTER COLUMN ' . $fieldname . ' DROP DEFAULT';                     }
            }
        }

                if ($notnullchanged) {
            if ($xmldb_field->getNotnull()) {
                $results[] = 'ALTER TABLE ' . $tablename . ' ALTER COLUMN ' . $fieldname . ' SET NOT NULL';
            } else {
                $results[] = 'ALTER TABLE ' . $tablename . ' ALTER COLUMN ' . $fieldname . ' DROP NOT NULL';
            }
        }

                return $results;
    }

    
    public function getCreateDefaultSQL($xmldb_table, $xmldb_field) {
                        return $this->getAlterFieldSQL($xmldb_table, $xmldb_field);
    }

    
    public function getDropDefaultSQL($xmldb_table, $xmldb_field) {
                        return $this->getAlterFieldSQL($xmldb_table, $xmldb_field);
    }

    
    public function addslashes($s) {
                if (!isset($this->std_strings)) {
            $this->std_strings = ($this->mdb->get_field_sql("select setting from pg_settings where name = 'standard_conforming_strings'") === 'on');
        }

        if ($this->std_strings) {
            $s = str_replace("'",  "''", $s);
        } else {
                        $s = str_replace('\\','\\\\',$s);
            $s = str_replace("\0","\\\0", $s);
            $s = str_replace("'",  "\\'", $s);
        }

        return $s;
    }

    
    function getSequenceFromDB($xmldb_table) {

        $tablename = $this->getTableName($xmldb_table);
        $sequencename = $tablename . '_id_seq';

        if (!$this->mdb->get_record_sql("SELECT c.*
                                           FROM pg_catalog.pg_class c
                                           JOIN pg_catalog.pg_namespace as ns ON ns.oid = c.relnamespace
                                          WHERE c.relname = ? AND c.relkind = 'S'
                                                AND (ns.nspname = current_schema() OR ns.oid = pg_my_temp_schema())",
            array($sequencename))) {
            $sequencename = false;
        }

        return $sequencename;
    }

    
    public function isNameInUse($object_name, $type, $table_name) {
        switch($type) {
            case 'ix':
            case 'uix':
            case 'seq':
                if ($check = $this->mdb->get_records_sql("SELECT c.relname
                                                            FROM pg_class c
                                                            JOIN pg_catalog.pg_namespace as ns ON ns.oid = c.relnamespace
                                                           WHERE lower(c.relname) = ?
                                                                 AND (ns.nspname = current_schema() OR ns.oid = pg_my_temp_schema())", array(strtolower($object_name)))) {
                    return true;
                }
                break;
            case 'pk':
            case 'uk':
            case 'fk':
            case 'ck':
                if ($check = $this->mdb->get_records_sql("SELECT c.conname
                                                            FROM pg_constraint c
                                                            JOIN pg_catalog.pg_namespace as ns ON ns.oid = c.connamespace
                                                           WHERE lower(c.conname) = ?
                                                                 AND (ns.nspname = current_schema() OR ns.oid = pg_my_temp_schema())", array(strtolower($object_name)))) {
                    return true;
                }
                break;
            case 'trg':
                if ($check = $this->mdb->get_records_sql("SELECT tgname
                                                            FROM pg_trigger
                                                           WHERE lower(tgname) = ?", array(strtolower($object_name)))) {
                    return true;
                }
                break;
        }
        return false;     }

    
    public static function getReservedWords() {
                                        $reserved_words = array (
            'all', 'analyse', 'analyze', 'and', 'any', 'array', 'as', 'asc',
            'asymmetric', 'authorization', 'between', 'binary', 'both', 'case',
            'cast', 'check', 'collate', 'column', 'constraint', 'create', 'cross',
            'current_date', 'current_role', 'current_time', 'current_timestamp',
            'current_user', 'default', 'deferrable', 'desc', 'distinct', 'do',
            'else', 'end', 'except', 'false', 'for', 'foreign', 'freeze', 'from',
            'full', 'grant', 'group', 'having', 'ilike', 'in', 'initially', 'inner',
            'intersect', 'into', 'is', 'isnull', 'join', 'leading', 'left', 'like',
            'limit', 'localtime', 'localtimestamp', 'natural', 'new', 'not',
            'notnull', 'null', 'off', 'offset', 'old', 'on', 'only', 'or', 'order',
            'outer', 'overlaps', 'placing', 'primary', 'references', 'returning', 'right', 'select',
            'session_user', 'similar', 'some', 'symmetric', 'table', 'then', 'to',
            'trailing', 'true', 'union', 'unique', 'user', 'using', 'verbose',
            'when', 'where', 'with'
        );
        return $reserved_words;
    }
}
