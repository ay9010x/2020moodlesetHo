<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/ddl/sql_generator.php');


class oracle_sql_generator extends sql_generator {

    
    
    public $statement_end = "\n/";

    
    public $number_type = 'NUMBER';

    
    public $default_for_char = ' ';

    
    public $drop_default_value_required = true;

    
    public $drop_default_value = null;

    
    public $default_after_null = false;

    
    public $sequence_extra_code = true;

    
    public $sequence_name = '';

    
    public $alter_column_sql = 'ALTER TABLE TABLENAME MODIFY (COLUMNSPECS)';

    
    public $sequence_cache_size = 20;

    
    public function getResetSequenceSQL($table) {

        if (is_string($table)) {
            $tablename = $table;
            $xmldb_table = new xmldb_table($tablename);
        } else {
            $tablename = $table->getName();
            $xmldb_table = $table;
        }
                $value = (int)$this->mdb->get_field_sql('SELECT MAX(id) FROM {'.$tablename.'}');
        $value++;

        $seqname = $this->getSequenceFromDB($xmldb_table);

        if (!$seqname) {
                        $seqname = $this->getNameForObject($table, 'id', 'seq');
        }

        return array ("DROP SEQUENCE $seqname",
                      "CREATE SEQUENCE $seqname START WITH $value INCREMENT BY 1 NOMAXVALUE CACHE $this->sequence_cache_size");
    }

    
    public function getTableName(xmldb_table $xmldb_table, $quoted=true) {
                if ($this->temptables->is_temptable($xmldb_table->getName())) {
            $tablename = $this->temptables->get_correct_name($xmldb_table->getName());
        } else {
            $tablename = $this->prefix . $xmldb_table->getName();
        }

                if ($quoted) {
            $tablename = $this->getEncQuoted($tablename);
        }

        return $tablename;
    }

    
    public function getCreateTempTableSQL($xmldb_table) {
        $this->temptables->add_temptable($xmldb_table->getName());
        $sqlarr = $this->getCreateTableSQL($xmldb_table);
        $sqlarr = preg_replace('/^CREATE TABLE (.*)/s', 'CREATE GLOBAL TEMPORARY TABLE $1 ON COMMIT PRESERVE ROWS', $sqlarr);
        return $sqlarr;
    }

    
    public function getDropTableSQL($xmldb_table) {
        $sqlarr = parent::getDropTableSQL($xmldb_table);
        if ($this->temptables->is_temptable($xmldb_table->getName())) {
            array_unshift($sqlarr, "TRUNCATE TABLE ". $this->getTableName($xmldb_table));             $this->temptables->delete_temptable($xmldb_table->getName());
        }
        return $sqlarr;
    }

    
    public function getTypeSQL($xmldb_type, $xmldb_length=null, $xmldb_decimals=null) {

        switch ($xmldb_type) {
            case XMLDB_TYPE_INTEGER:                    if (empty($xmldb_length)) {
                    $xmldb_length = 10;
                }
                $dbtype = 'NUMBER(' .  $xmldb_length . ')';
                break;
            case XMLDB_TYPE_FLOAT:
            case XMLDB_TYPE_NUMBER:
                $dbtype = $this->number_type;
                                if ($xmldb_length > 38) {
                    $xmldb_length = 38;
                }
                if (!empty($xmldb_length)) {
                    $dbtype .= '(' . $xmldb_length;
                    if (!empty($xmldb_decimals)) {
                        $dbtype .= ',' . $xmldb_decimals;
                    }
                    $dbtype .= ')';
                }
                break;
            case XMLDB_TYPE_CHAR:
                                                                $dbtype = 'VARCHAR2';
                if (empty($xmldb_length)) {
                    $xmldb_length='255';
                }
                $dbtype .= '(' . $xmldb_length . ' CHAR)';                 break;
            case XMLDB_TYPE_TEXT:
                $dbtype = 'CLOB';
                break;
            case XMLDB_TYPE_BINARY:
                $dbtype = 'BLOB';
                break;
            case XMLDB_TYPE_DATETIME:
                $dbtype = 'DATE';
                break;
        }
        return $dbtype;
    }

    
    public function getCreateSequenceSQL($xmldb_table, $xmldb_field) {

        $results = array();

        $sequence_name = $this->getNameForObject($xmldb_table->getName(), $xmldb_field->getName(), 'seq');

        $sequence = "CREATE SEQUENCE $sequence_name START WITH 1 INCREMENT BY 1 NOMAXVALUE CACHE $this->sequence_cache_size";

        $results[] = $sequence;

        $results = array_merge($results, $this->getCreateTriggerSQL ($xmldb_table, $xmldb_field, $sequence_name));

        return $results;
    }

    
    public function getCreateTriggerSQL($xmldb_table, $xmldb_field, $sequence_name) {

        $trigger_name = $this->getNameForObject($xmldb_table->getName(), $xmldb_field->getName(), 'trg');

        $trigger = "CREATE TRIGGER " . $trigger_name;
        $trigger.= "\n    BEFORE INSERT";
        $trigger.= "\nON " . $this->getTableName($xmldb_table);
        $trigger.= "\n    FOR EACH ROW";
        $trigger.= "\nBEGIN";
        $trigger.= "\n    IF :new." . $this->getEncQuoted($xmldb_field->getName()) . ' IS NULL THEN';
        $trigger.= "\n        SELECT " . $sequence_name . '.nextval INTO :new.' . $this->getEncQuoted($xmldb_field->getName()) . " FROM dual;";
        $trigger.= "\n    END IF;";
        $trigger.= "\nEND;";

        return array($trigger);
    }

    
    public function getDropSequenceSQL($xmldb_table, $xmldb_field, $include_trigger=false) {

        $result = array();

        if ($sequence_name = $this->getSequenceFromDB($xmldb_table)) {
            $result[] = "DROP SEQUENCE " . $sequence_name;
        }

        if ($trigger_name = $this->getTriggerFromDB($xmldb_table) && $include_trigger) {
            $result[] = "DROP TRIGGER " . $trigger_name;
        }

        return $result;
    }

    
    function getCommentSQL ($xmldb_table) {

        $comment = "COMMENT ON TABLE " . $this->getTableName($xmldb_table);
        $comment.= " IS '" . $this->addslashes(substr($xmldb_table->getComment(), 0, 250)) . "'";

        return array($comment);
    }

    
    public function getDropTableExtraSQL($xmldb_table) {
        $xmldb_field = new xmldb_field('id');         return $this->getDropSequenceSQL($xmldb_table, $xmldb_field, false);
    }

    
    public function getRenameTableExtraSQL($xmldb_table, $newname) {

        $results = array();

        $xmldb_field = new xmldb_field('id'); 
        $oldseqname = $this->getSequenceFromDB($xmldb_table);
        $newseqname = $this->getNameForObject($newname, $xmldb_field->getName(), 'seq');

        $oldtriggername = $this->getTriggerFromDB($xmldb_table);
        $newtriggername = $this->getNameForObject($newname, $xmldb_field->getName(), 'trg');

                $results[] = "DROP TRIGGER " . $oldtriggername;

                        $results[] = 'ALTER SEQUENCE ' . $oldseqname . ' NOCACHE';
        $results[] = 'RENAME ' . $oldseqname . ' TO ' . $newseqname;
        $results[] = 'ALTER SEQUENCE ' . $newseqname . ' CACHE ' . $this->sequence_cache_size;

                $newt = new xmldb_table($newname);             $results = array_merge($results, $this->getCreateTriggerSQL($newt, $xmldb_field, $newseqname));

        return $results;
    }

    
    public function getAlterFieldSQL($xmldb_table, $xmldb_field, $skip_type_clause = NULL, $skip_default_clause = NULL, $skip_notnull_clause = NULL) {

        $skip_type_clause = is_null($skip_type_clause) ? $this->alter_column_skip_type : $skip_type_clause;
        $skip_default_clause = is_null($skip_default_clause) ? $this->alter_column_skip_default : $skip_default_clause;
        $skip_notnull_clause = is_null($skip_notnull_clause) ? $this->alter_column_skip_notnull : $skip_notnull_clause;

        $results = array();     
                $tablename = $this->getTableName($xmldb_table);
        $fieldname = $xmldb_field->getName();

                $meta = $this->mdb->get_columns($xmldb_table->getName());
        $metac = $meta[$fieldname];
        $oldmetatype = $metac->meta_type;

        $oldlength = $metac->max_length;
                        if ($oldmetatype == 'N') {
            $uppertablename = strtoupper($tablename);
            $upperfieldname = strtoupper($fieldname);
            if ($col = $this->mdb->get_record_sql("SELECT cname, precision
                                                     FROM col
                                                     WHERE tname = ? AND cname = ?",
                                                  array($uppertablename, $upperfieldname))) {
                $oldlength = $col->precision;
            }
        }
        $olddecimals = empty($metac->scale) ? null : $metac->scale;
        $oldnotnull = empty($metac->not_null) ? false : $metac->not_null;
        $olddefault = empty($metac->default_value) || strtoupper($metac->default_value) == 'NULL' ? null : $metac->default_value;

        $typechanged = true;          $precisionchanged = true;          $decimalchanged = true;          $defaultchanged = true;          $notnullchanged = true;  
        $from_temp_fields = false; 
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
            ($xmldb_field->getDefault() === $olddefault) ||                         ("'" . $xmldb_field->getDefault() . "'" === $olddefault)) {              $defaultchanged = false;
        }

                if (($xmldb_field->getNotnull() === $oldnotnull)) {
            $notnullchanged = false;
        }

                                                if (($typechanged) || (($oldmetatype == 'N' || $oldmetatype == 'I')  && ($precisionchanged || $decimalchanged))) {
            $tempcolname = $xmldb_field->getName() . '___tmp';             if (strlen($tempcolname) > 30) {                 $tempcolname = 'ongoing_alter_column_tmp';
            }
                        $skip_notnull_clause = true;
            $skip_default_clause = true;
            $xmldb_field->setName($tempcolname);
                                    if (isset($meta[$tempcolname])) {
                $results = array_merge($results, $this->getDropFieldSQL($xmldb_table, $xmldb_field));
            }
                        $results = array_merge($results, $this->getAddFieldSQL($xmldb_table, $xmldb_field, $skip_type_clause, $skip_type_clause, $skip_notnull_clause));
            
                        if ($oldmetatype == 'X' && $xmldb_field->GetType() == XMLDB_TYPE_INTEGER) {
                $results[] = 'UPDATE ' . $tablename . ' SET ' . $tempcolname . ' = CAST(' . $this->mdb->sql_compare_text($fieldname) . ' AS INT)';
            } else if ($oldmetatype == 'X' && $xmldb_field->GetType() == XMLDB_TYPE_NUMBER) {
                $results[] = 'UPDATE ' . $tablename . ' SET ' . $tempcolname . ' = CAST(' . $this->mdb->sql_compare_text($fieldname) . ' AS NUMBER)';

                        } else {
                $results[] = 'UPDATE ' . $tablename . ' SET ' . $tempcolname . ' = ' . $fieldname;
            }
                        $xmldb_field->setName($fieldname);             $results = array_merge($results, $this->getDropFieldSQL($xmldb_table, $xmldb_field));
                        $results[] = 'ALTER TABLE ' . $tablename . ' RENAME COLUMN ' . $tempcolname . ' TO ' . $fieldname;
                        $from_temp_fields = true;
                        $skip_notnull_clause = false;
            $skip_default_clause = false;
                        $skip_type_clause = true;
                        if (!$xmldb_field->getNotnull()) {
                $notnullchanged = false;
            }
                        if ($xmldb_field->getDefault() === null) {
                $defaultchanged = false;
            }
        }

                if (!$typechanged && !$precisionchanged && !$decimalchanged) {
            $skip_type_clause = true;
        }

                        if (!$notnullchanged) {
            $skip_notnull_clause = true;                             if ($from_temp_fields &&  $xmldb_field->getNotnull()) {
                $skip_notnull_clause = false;
            }
        }
                        if (!$defaultchanged) {
            $skip_default_clause = true;                             if ($from_temp_fields) {
                $default_clause = $this->getDefaultClause($xmldb_field);
                if ($default_clause) {
                    $skip_notnull_clause = false;
                }
            }
        }

                if (!$skip_type_clause || !$skip_notnull_clause || !$skip_default_clause) {
            $results = array_merge($results, parent::getAlterFieldSQL($xmldb_table, $xmldb_field, $skip_type_clause, $skip_default_clause, $skip_notnull_clause));
            return $results;
        }

                return $results;
    }

    
    public function getCreateDefaultSQL($xmldb_table, $xmldb_field) {
                        return $this->getAlterFieldSQL($xmldb_table, $xmldb_field);
    }

    
    public function getDropDefaultSQL($xmldb_table, $xmldb_field) {
                        return $this->getAlterFieldSQL($xmldb_table, $xmldb_field);
    }

    
    public function getSequenceFromDB($xmldb_table) {

         $tablename    = strtoupper($this->getTableName($xmldb_table));
         $prefixupper  = strtoupper($this->prefix);
         $sequencename = false;

        if ($trigger = $this->mdb->get_record_sql("SELECT trigger_name, trigger_body
                                                     FROM user_triggers
                                                    WHERE table_name = ? AND trigger_name LIKE ?",
                                                  array($tablename, "{$prefixupper}%_ID%_TRG"))) {
                        preg_match('/.*SELECT (.*)\.nextval/i', $trigger->trigger_body, $matches);
            if (isset($matches[1])) {
                $sequencename = $matches[1];
            }
        }

        return $sequencename;
    }

    
    public function getTriggerFromDB($xmldb_table) {

        $tablename   = strtoupper($this->getTableName($xmldb_table));
        $prefixupper = strtoupper($this->prefix);
        $triggername = false;

        if ($trigger = $this->mdb->get_record_sql("SELECT trigger_name, trigger_body
                                                     FROM user_triggers
                                                    WHERE table_name = ? AND trigger_name LIKE ?",
                                                  array($tablename, "{$prefixupper}%_ID%_TRG"))) {
            $triggername = $trigger->trigger_name;
        }

        return $triggername;
    }

    
    public function isNameInUse($object_name, $type, $table_name) {
        switch($type) {
            case 'ix':
            case 'uix':
            case 'seq':
            case 'trg':
                if ($check = $this->mdb->get_records_sql("SELECT object_name
                                                            FROM user_objects
                                                           WHERE lower(object_name) = ?", array(strtolower($object_name)))) {
                    return true;
                }
                break;
            case 'pk':
            case 'uk':
            case 'fk':
            case 'ck':
                if ($check = $this->mdb->get_records_sql("SELECT constraint_name
                                                            FROM user_constraints
                                                           WHERE lower(constraint_name) = ?", array(strtolower($object_name)))) {
                    return true;
                }
                break;
        }
        return false;     }

    
    public function addslashes($s) {
                $s = str_replace("'",  "''", $s);
        return $s;
    }

    
    public static function getReservedWords() {
                        $reserved_words = array (
            'access', 'add', 'all', 'alter', 'and', 'any',
            'as', 'asc', 'audit', 'between', 'by', 'char',
            'check', 'cluster', 'column', 'comment',
            'compress', 'connect', 'create', 'current',
            'date', 'decimal', 'default', 'delete', 'desc',
            'distinct', 'drop', 'else', 'exclusive', 'exists',
            'file', 'float', 'for', 'from', 'grant', 'group',
            'having', 'identified', 'immediate', 'in',
            'increment', 'index', 'initial', 'insert',
            'integer', 'intersect', 'into', 'is', 'level',
            'like', 'lock', 'long', 'maxextents', 'minus',
            'mlslabel', 'mode', 'modify', 'nchar', 'nclob', 'noaudit',
            'nocompress', 'not', 'nowait', 'null', 'number', 'nvarchar2',
            'of', 'offline', 'on', 'online', 'option', 'or',
            'order', 'pctfree', 'prior', 'privileges',
            'public', 'raw', 'rename', 'resource', 'revoke',
            'row', 'rowid', 'rownum', 'rows', 'select',
            'session', 'set', 'share', 'size', 'smallint',
            'start', 'successful', 'synonym', 'sysdate',
            'table', 'then', 'to', 'trigger', 'uid', 'union',
            'unique', 'update', 'user', 'validate', 'values',
            'varchar', 'varchar2', 'view', 'whenever',
            'where', 'with'
        );
        return $reserved_words;
    }
}
