<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/ddl/sql_generator.php');


class mssql_sql_generator extends sql_generator {

    
    
    public $statement_end = "\ngo";

    
    public $number_type = 'DECIMAL';

    
    public $default_for_char = '';

    
    public $specify_nulls = true;

    
    public $sequence_extra_code = false;

    
    public $sequence_name = 'IDENTITY(1,1)';

    
    public $sequence_only = false;

    
    public $add_table_comments = false;

    
    public $concat_character = '+';

    
    public $rename_table_sql = "sp_rename 'OLDNAME', 'NEWNAME'";

    
    public $rename_column_sql = "sp_rename 'TABLENAME.OLDFIELDNAME', 'NEWFIELDNAME', 'COLUMN'";

    
    public $drop_index_sql = 'DROP INDEX TABLENAME.INDEXNAME';

    
    public $rename_index_sql = "sp_rename 'TABLENAME.OLDINDEXNAME', 'NEWINDEXNAME', 'INDEX'";

    
    public $rename_key_sql = null;

    
    public function getResetSequenceSQL($table) {

        if (is_string($table)) {
            $table = new xmldb_table($table);
        }

        $value = (int)$this->mdb->get_field_sql('SELECT MAX(id) FROM {'. $table->getName() . '}');
        $sqls = array();

                                        if ($value == 0) {
                        $sqls[] = "TRUNCATE TABLE " . $this->getTableName($table);
            $value = 1;
        }

                $sqls[] = "DBCC CHECKIDENT ('" . $this->getTableName($table) . "', RESEED, $value)";
        return $sqls;
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
        return $sqlarr;
    }

    
    public function getDropTableSQL($xmldb_table) {
        $sqlarr = parent::getDropTableSQL($xmldb_table);
        if ($this->temptables->is_temptable($xmldb_table->getName())) {
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
                } else if ($xmldb_length > 4) {
                    $dbtype = 'INTEGER';
                } else {
                    $dbtype = 'SMALLINT';
                }
                break;
            case XMLDB_TYPE_NUMBER:
                $dbtype = $this->number_type;
                if (!empty($xmldb_length)) {
                                        if ($xmldb_length > 38) {
                        $xmldb_length = 38;
                    }
                    $dbtype .= '(' . $xmldb_length;
                    if (!empty($xmldb_decimals)) {
                        $dbtype .= ',' . $xmldb_decimals;
                    }
                    $dbtype .= ')';
                }
                break;
            case XMLDB_TYPE_FLOAT:
                $dbtype = 'FLOAT';
                if (!empty($xmldb_decimals)) {
                    if ($xmldb_decimals < 6) {
                        $dbtype = 'REAL';
                    }
                }
                break;
            case XMLDB_TYPE_CHAR:
                $dbtype = 'NVARCHAR';
                if (empty($xmldb_length)) {
                    $xmldb_length='255';
                }
                $dbtype .= '(' . $xmldb_length . ') COLLATE database_default';
                break;
            case XMLDB_TYPE_TEXT:
                $dbtype = 'NVARCHAR(MAX) COLLATE database_default';
                break;
            case XMLDB_TYPE_BINARY:
                $dbtype = 'VARBINARY(MAX)';
                break;
            case XMLDB_TYPE_DATETIME:
                $dbtype = 'DATETIME';
                break;
        }
        return $dbtype;
    }

    
    public function getDropFieldSQL($xmldb_table, $xmldb_field) {
        $results = array();

                $tablename = $this->getTableName($xmldb_table);
        $fieldname = $this->getEncQuoted($xmldb_field->getName());

                if ($defaultname = $this->getDefaultConstraintName($xmldb_table, $xmldb_field)) {
            $results[] = 'ALTER TABLE ' . $tablename . ' DROP CONSTRAINT ' . $defaultname;
        }

                $results[] = 'ALTER TABLE ' . $tablename . ' DROP COLUMN ' . $fieldname;

        return $results;
    }

    
    public function getRenameFieldSQL($xmldb_table, $xmldb_field, $newname) {

        $results = array();  
                                                                if ($xmldb_field->getName() == 'id') {
            return array();
        }

                $results = array_merge($results, parent::getRenameFieldSQL($xmldb_table, $xmldb_field, $newname));

        return $results;
    }

    
    public function getRenameTableExtraSQL($xmldb_table, $newname) {

        $results = array();

        return $results;
    }

    
    public function getAlterFieldSQL($xmldb_table, $xmldb_field, $skip_type_clause = NULL, $skip_default_clause = NULL, $skip_notnull_clause = NULL) {

        $results = array();     
                $tablename = $xmldb_table->getName();
        $fieldname = $xmldb_field->getName();

                $meta = $this->mdb->get_columns($tablename);
        $metac = $meta[$fieldname];
        $oldmetatype = $metac->meta_type;

        $oldlength = $metac->max_length;
        $olddecimals = empty($metac->scale) ? null : $metac->scale;
        $oldnotnull = empty($metac->not_null) ? false : $metac->not_null;
        
        $typechanged = true;          $lengthchanged = true;  
                if (($xmldb_field->getType() == XMLDB_TYPE_INTEGER && $oldmetatype == 'I') ||
            ($xmldb_field->getType() == XMLDB_TYPE_NUMBER  && $oldmetatype == 'N') ||
            ($xmldb_field->getType() == XMLDB_TYPE_FLOAT   && $oldmetatype == 'F') ||
            ($xmldb_field->getType() == XMLDB_TYPE_CHAR    && $oldmetatype == 'C') ||
            ($xmldb_field->getType() == XMLDB_TYPE_TEXT    && $oldmetatype == 'X') ||
            ($xmldb_field->getType() == XMLDB_TYPE_BINARY  && $oldmetatype == 'B')) {
            $typechanged = false;
        }

                        if ($xmldb_field->getType() == XMLDB_TYPE_INTEGER && $oldmetatype == 'I') {
            if ($xmldb_field->getLength() > 9) {                 $newmssqlinttype = 'I8';
            } else if ($xmldb_field->getLength() > 4) {
                $newmssqlinttype = 'I';
            } else {
                $newmssqlinttype = 'I2';
            }
            if ($metac->type == 'bigint') {                 $oldmssqlinttype = 'I8';
            } else if ($metac->type == 'smallint') {
                $oldmssqlinttype = 'I2';
            } else {
                $oldmssqlinttype = 'I';
            }
            if ($newmssqlinttype != $oldmssqlinttype) {                 $typechanged = true;             }
        }

                        if ($xmldb_field->getLength() == $oldlength) {
            $lengthchanged = false;
        }

                if ($typechanged || $lengthchanged) {
            $results = $this->getDropDefaultSQL($xmldb_table, $xmldb_field);
        }

                                $multiple_alter_stmt = array();
        $targettype = $xmldb_field->getType();

        if ($targettype == XMLDB_TYPE_TEXT && $oldmetatype == 'I') {             $multiple_alter_stmt[0] = new stdClass;                              $multiple_alter_stmt[0]->type = XMLDB_TYPE_CHAR;
            $multiple_alter_stmt[0]->length = 255;

        } else if ($targettype == XMLDB_TYPE_TEXT && $oldmetatype == 'N') {             $multiple_alter_stmt[0] = new stdClass;                                     $multiple_alter_stmt[0]->type = XMLDB_TYPE_CHAR;
            $multiple_alter_stmt[0]->length = 255;

        } else if ($targettype == XMLDB_TYPE_TEXT && $oldmetatype == 'F') {             $multiple_alter_stmt[0] = new stdClass;                                     $multiple_alter_stmt[0]->type = XMLDB_TYPE_CHAR;
            $multiple_alter_stmt[0]->length = 255;

        } else if ($targettype == XMLDB_TYPE_INTEGER && $oldmetatype == 'X') {             $multiple_alter_stmt[0] = new stdClass;                                        $multiple_alter_stmt[0]->type = XMLDB_TYPE_CHAR;
            $multiple_alter_stmt[0]->length = 255;
            $multiple_alter_stmt[1] = new stdClass;                                        $multiple_alter_stmt[1]->type = XMLDB_TYPE_NUMBER;                             $multiple_alter_stmt[1]->length = 10;

        } else if ($targettype == XMLDB_TYPE_NUMBER && $oldmetatype == 'X') {             $multiple_alter_stmt[0] = new stdClass;                                       $multiple_alter_stmt[0]->type = XMLDB_TYPE_CHAR;
            $multiple_alter_stmt[0]->length = 255;

        } else if ($targettype ==  XMLDB_TYPE_FLOAT && $oldmetatype == 'X') {             $multiple_alter_stmt[0] = new stdClass;                                       $multiple_alter_stmt[0]->type = XMLDB_TYPE_CHAR;
            $multiple_alter_stmt[0]->length = 255;
        }

                if (empty($multiple_alter_stmt)) {             $results = array_merge($results, parent::getAlterFieldSQL($xmldb_table, $xmldb_field, NULL, true, NULL));

        } else {             $final_type = $xmldb_field->getType();             $final_length = $xmldb_field->getLength();
            foreach ($multiple_alter_stmt as $alter) {
                $xmldb_field->setType($alter->type);                  $xmldb_field->setLength($alter->length);
                $results = array_merge($results, parent::getAlterFieldSQL($xmldb_table, $xmldb_field, NULL, true, NULL));
            }
            $xmldb_field->setType($final_type);             $xmldb_field->setLength($final_length);
            $results = array_merge($results, parent::getAlterFieldSQL($xmldb_table, $xmldb_field, NULL, true, NULL));
        }

                if ($typechanged || $lengthchanged) {
            $results = array_merge($results, $this->getCreateDefaultSQL($xmldb_table, $xmldb_field));
        }

                return $results;
    }

    
    public function getModifyDefaultSQL($xmldb_table, $xmldb_field) {
                
        $results = array();

                if ($xmldb_field->getDefault() === null) {
            $results = $this->getDropDefaultSQL($xmldb_table, $xmldb_field);             $default_clause = $this->getDefaultClause($xmldb_field);
            if ($default_clause) {                 $results = array_merge($results, $this->getCreateDefaultSQL($xmldb_table, $xmldb_field));             }
        } else {
            $results = $this->getDropDefaultSQL($xmldb_table, $xmldb_field);             $results = array_merge($results, $this->getCreateDefaultSQL($xmldb_table, $xmldb_field));         }

        return $results;
    }

    
    public function getCreateDefaultSQL($xmldb_table, $xmldb_field) {
        
        $results = array();

                $tablename = $this->getTableName($xmldb_table);
        $fieldname = $this->getEncQuoted($xmldb_field->getName());

                $default_clause = $this->getDefaultClause($xmldb_field);
        if ($default_clause) {
                        $sql = 'ALTER TABLE ' . $tablename . ' ADD' . $default_clause . ' FOR ' . $fieldname;
            $results[] = $sql;
        }

        return $results;
    }

    
    public function getDropDefaultSQL($xmldb_table, $xmldb_field) {
        
        $results = array();

                $tablename = $this->getTableName($xmldb_table);
        $fieldname = $this->getEncQuoted($xmldb_field->getName());

                if ($defaultname = $this->getDefaultConstraintName($xmldb_table, $xmldb_field)) {
            $results[] = 'ALTER TABLE ' . $tablename . ' DROP CONSTRAINT ' . $defaultname;
        }

        return $results;
    }

    
    protected function getDefaultConstraintName($xmldb_table, $xmldb_field) {

                $tablename = $this->getTableName($xmldb_table);
        $fieldname = $xmldb_field->getName();

                if ($default = $this->mdb->get_record_sql("SELECT id, object_name(cdefault) AS defaultconstraint
                                                     FROM syscolumns
                                                    WHERE id = object_id(?)
                                                          AND name = ?", array($tablename, $fieldname))) {
            return $default->defaultconstraint;
        } else {
            return false;
        }
    }

    
    public function getNameForObject($tablename, $fields, $suffix='') {
        if ($this->temptables->is_temptable($tablename)) {             $random = strtolower(random_string(12));             $fields = $fields . ', ' . implode(', ', str_split($random, 3));
        }
        return parent::getNameForObject($tablename, $fields, $suffix);     }

    
    public function isNameInUse($object_name, $type, $table_name) {
        switch($type) {
            case 'seq':
            case 'trg':
            case 'pk':
            case 'uk':
            case 'fk':
            case 'ck':
                if ($check = $this->mdb->get_records_sql("SELECT name
                                                            FROM sysobjects
                                                           WHERE lower(name) = ?", array(strtolower($object_name)))) {
                    return true;
                }
                break;
            case 'ix':
            case 'uix':
                if ($check = $this->mdb->get_records_sql("SELECT name
                                                            FROM sysindexes
                                                           WHERE lower(name) = ?", array(strtolower($object_name)))) {
                    return true;
                }
                break;
        }
        return false;     }

    
    public function getCommentSQL($xmldb_table) {
        return array();
    }

    
    public function addslashes($s) {
                $s = str_replace("'",  "''", $s);
        return $s;
    }

    
    public static function getReservedWords() {
                        $reserved_words = array (
            'add', 'all', 'alter', 'and', 'any', 'as', 'asc', 'authorization',
            'avg', 'backup', 'begin', 'between', 'break', 'browse', 'bulk',
            'by', 'cascade', 'case', 'check', 'checkpoint', 'close', 'clustered',
            'coalesce', 'collate', 'column', 'commit', 'committed', 'compute',
            'confirm', 'constraint', 'contains', 'containstable', 'continue',
            'controlrow', 'convert', 'count', 'create', 'cross', 'current',
            'current_date', 'current_time', 'current_timestamp', 'current_user',
            'cursor', 'database', 'dbcc', 'deallocate', 'declare', 'default', 'delete',
            'deny', 'desc', 'disk', 'distinct', 'distributed', 'double', 'drop', 'dummy',
            'dump', 'else', 'end', 'errlvl', 'errorexit', 'escape', 'except', 'exec',
            'execute', 'exists', 'exit', 'external', 'fetch', 'file', 'fillfactor', 'floppy',
            'for', 'foreign', 'freetext', 'freetexttable', 'from', 'full', 'function',
            'goto', 'grant', 'group', 'having', 'holdlock', 'identity', 'identitycol',
            'identity_insert', 'if', 'in', 'index', 'inner', 'insert', 'intersect', 'into',
            'is', 'isolation', 'join', 'key', 'kill', 'left', 'level', 'like', 'lineno',
            'load', 'max', 'min', 'mirrorexit', 'national', 'nocheck', 'nonclustered',
            'not', 'null', 'nullif', 'of', 'off', 'offsets', 'on', 'once', 'only', 'open',
            'opendatasource', 'openquery', 'openrowset', 'openxml', 'option', 'or', 'order',
            'outer', 'over', 'percent', 'perm', 'permanent', 'pipe', 'pivot', 'plan', 'precision',
            'prepare', 'primary', 'print', 'privileges', 'proc', 'procedure', 'processexit',
            'public', 'raiserror', 'read', 'readtext', 'reconfigure', 'references',
            'repeatable', 'replication', 'restore', 'restrict', 'return', 'revoke',
            'right', 'rollback', 'rowcount', 'rowguidcol', 'rule', 'save', 'schema',
            'select', 'serializable', 'session_user', 'set', 'setuser', 'shutdown', 'some',
            'statistics', 'sum', 'system_user', 'table', 'tape', 'temp', 'temporary',
            'textsize', 'then', 'to', 'top', 'tran', 'transaction', 'trigger', 'truncate',
            'tsequal', 'uncommitted', 'union', 'unique', 'update', 'updatetext', 'use',
            'user', 'values', 'varying', 'view', 'waitfor', 'when', 'where', 'while',
            'with', 'work', 'writetext'
        );
        return $reserved_words;
    }
}
