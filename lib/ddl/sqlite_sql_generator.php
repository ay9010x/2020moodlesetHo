<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/ddl/sql_generator.php');


class sqlite_sql_generator extends sql_generator {


    
    public $drop_default_value_required = true;

    
    public $drop_default_value = NULL;

    
    public $drop_primary_key = 'ALTER TABLE TABLENAME DROP PRIMARY KEY';

    
    public $drop_unique_key = 'ALTER TABLE TABLENAME DROP KEY KEYNAME';

    
    public $drop_foreign_key = 'ALTER TABLE TABLENAME DROP FOREIGN KEY KEYNAME';

    
    public $default_for_char = '';

    
    public $sequence_only = true;

    
    public $sequence_extra_code = false;

    
    public $sequence_name = 'INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL';

    
    public $drop_index_sql = 'ALTER TABLE TABLENAME DROP INDEX INDEXNAME';

    
    public $rename_index_sql = null;

    
    public $rename_key_sql = null;

    
    public function __construct($mdb) {
        parent::__construct($mdb);
    }

    
    public function getResetSequenceSQL($table) {

        if ($table instanceof xmldb_table) {
            $table = $table->getName();
        }

                $value = (int)$this->mdb->get_field_sql('SELECT MAX(id) FROM {'.$table.'}');
        return array("UPDATE sqlite_sequence SET seq=$value WHERE name='{$this->prefix}{$table}'");
    }

    
    public function getKeySQL($xmldb_table, $xmldb_key) {

        $key = '';

        switch ($xmldb_key->getType()) {
            case XMLDB_KEY_PRIMARY:
                if ($this->primary_keys && count($xmldb_key->getFields())>1) {
                    if ($this->primary_key_name !== null) {
                        $key = $this->getEncQuoted($this->primary_key_name);
                    } else {
                        $key = $this->getNameForObject($xmldb_table->getName(), implode(', ', $xmldb_key->getFields()), 'pk');
                    }
                    $key .= ' PRIMARY KEY (' . implode(', ', $this->getEncQuoted($xmldb_key->getFields())) . ')';
                }
                break;
            case XMLDB_KEY_UNIQUE:
                if ($this->unique_keys) {
                    $key = $this->getNameForObject($xmldb_table->getName(), implode(', ', $xmldb_key->getFields()), 'uk');
                    $key .= ' UNIQUE (' . implode(', ', $this->getEncQuoted($xmldb_key->getFields())) . ')';
                }
                break;
            case XMLDB_KEY_FOREIGN:
            case XMLDB_KEY_FOREIGN_UNIQUE:
                if ($this->foreign_keys) {
                    $key = $this->getNameForObject($xmldb_table->getName(), implode(', ', $xmldb_key->getFields()), 'fk');
                    $key .= ' FOREIGN KEY (' . implode(', ', $this->getEncQuoted($xmldb_key->getFields())) . ')';
                    $key .= ' REFERENCES ' . $this->getEncQuoted($this->prefix . $xmldb_key->getRefTable());
                    $key .= ' (' . implode(', ', $this->getEncQuoted($xmldb_key->getRefFields())) . ')';
                }
                break;
        }

        return $key;
    }

    
    public function getTypeSQL($xmldb_type, $xmldb_length=null, $xmldb_decimals=null) {

        switch ($xmldb_type) {
            case XMLDB_TYPE_INTEGER:                    if (empty($xmldb_length)) {
                    $xmldb_length = 10;
                }
                $dbtype = 'INTEGER(' . $xmldb_length . ')';
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
                $dbtype = 'REAL';
                if (!empty($xmldb_length)) {
                    $dbtype .= '(' . $xmldb_length;
                    if (!empty($xmldb_decimals)) {
                        $dbtype .= ',' . $xmldb_decimals;
                    }
                    $dbtype .= ')';
                }
                break;
            case XMLDB_TYPE_CHAR:
                $dbtype = 'VARCHAR';
                if (empty($xmldb_length)) {
                    $xmldb_length='255';
                }
                $dbtype .= '(' . $xmldb_length . ')';
                break;
            case XMLDB_TYPE_BINARY:
                $dbtype = 'BLOB';
                break;
            case XMLDB_TYPE_DATETIME:
                $dbtype = 'DATETIME';
            default:
            case XMLDB_TYPE_TEXT:
                $dbtype = 'TEXT';
                break;
        }
        return $dbtype;
    }

    
    protected function getAlterTableSchema($xmldb_table, $xmldb_add_field=NULL, $xmldb_delete_field=NULL) {
            $tablename = $this->getTableName($xmldb_table);

        $oldname = $xmldb_delete_field ? $xmldb_delete_field->getName() : NULL;
        $newname = $xmldb_add_field ? $xmldb_add_field->getName() : NULL;
        if($xmldb_delete_field) {
            $xmldb_table->deleteField($oldname);
        }
        if($xmldb_add_field) {
            $xmldb_table->addField($xmldb_add_field);
        }
        if($oldname) {
                        $indexes = $xmldb_table->getIndexes();
            foreach($indexes as $index) {
                $fields = $index->getFields();
                $i = array_search($oldname, $fields);
                if($i!==FALSE) {
                    if($newname) {
                        $fields[$i] = $newname;
                    } else {
                        unset($fields[$i]);
                    }
                    $xmldb_table->deleteIndex($index->getName());
                    if(count($fields)) {
                        $index->setFields($fields);
                        $xmldb_table->addIndex($index);
                    }
                }
            }
                        $keys = $xmldb_table->getKeys();
            foreach($keys as $key) {
                $fields = $key->getFields();
                $reffields = $key->getRefFields();
                $i = array_search($oldname, $fields);
                if($i!==FALSE) {
                    if($newname) {
                        $fields[$i] = $newname;
                    } else {
                        unset($fields[$i]);
                        unset($reffields[$i]);
                    }
                    $xmldb_table->deleteKey($key->getName());
                    if(count($fields)) {
                        $key->setFields($fields);
                        $key->setRefFields($fields);
                        $xmldb_table->addkey($key);
                    }
                }
            }
        }
                $fields = $xmldb_table->getFields();
        foreach ($fields as $key => $field) {
            $fieldname = $field->getName();
            if($fieldname == $newname && $oldname && $oldname != $newname) {
                                $fields[$key] = $this->getEncQuoted($oldname) . ' AS ' . $this->getEncQuoted($newname);
            } else {
                $fields[$key] = $this->getEncQuoted($field->getName());
            }
        }
        $fields = implode(',', $fields);
        $results[] = 'BEGIN TRANSACTION';
        $results[] = 'CREATE TEMPORARY TABLE temp_data AS SELECT * FROM ' . $tablename;
        $results[] = 'DROP TABLE ' . $tablename;
        $results = array_merge($results, $this->getCreateTableSQL($xmldb_table));
        $results[] = 'INSERT INTO ' . $tablename . ' SELECT ' . $fields . ' FROM temp_data';
        $results[] = 'DROP TABLE temp_data';
        $results[] = 'COMMIT';
        return $results;
    }

    
    public function getAlterFieldSQL($xmldb_table, $xmldb_field, $skip_type_clause = NULL, $skip_default_clause = NULL, $skip_notnull_clause = NULL) {
        return $this->getAlterTableSchema($xmldb_table, $xmldb_field, $xmldb_field);
    }

    
    public function getAddKeySQL($xmldb_table, $xmldb_key) {
        $xmldb_table->addKey($xmldb_key);
        return $this->getAlterTableSchema($xmldb_table);
    }

    
    public function getCreateDefaultSQL($xmldb_table, $xmldb_field) {
        return $this->getAlterTableSchema($xmldb_table, $xmldb_field, $xmldb_field);
    }

    
    public function getRenameFieldSQL($xmldb_table, $xmldb_field, $newname) {
        $oldfield = clone($xmldb_field);
        $xmldb_field->setName($newname);
        return $this->getAlterTableSchema($xmldb_table, $xmldb_field, $oldfield);
    }

    
    function getRenameIndexSQL($xmldb_table, $xmldb_index, $newname) {
            $dbindexname = $this->mdb->get_manager()->find_index_name($xmldb_table, $xmldb_index);
        $xmldb_index->setName($newname);
        $results = array('DROP INDEX ' . $dbindexname);
        $results = array_merge($results, $this->getCreateIndexSQL($xmldb_table, $xmldb_index));
        return $results;
    }

    
    public function getRenameKeySQL($xmldb_table, $xmldb_key, $newname) {
        $xmldb_table->deleteKey($xmldb_key->getName());
        $xmldb_key->setName($newname);
        $xmldb_table->addkey($xmldb_key);
        return $this->getAlterTableSchema($xmldb_table);
    }

    
    public function getDropFieldSQL($xmldb_table, $xmldb_field) {
        return $this->getAlterTableSchema($xmldb_table, NULL, $xmldb_field);
    }

    
    public function getDropIndexSQL($xmldb_table, $xmldb_index) {
        $xmldb_table->deleteIndex($xmldb_index->getName());
        return $this->getAlterTableSchema($xmldb_table);
    }

    
    public function getDropKeySQL($xmldb_table, $xmldb_key) {
        $xmldb_table->deleteKey($xmldb_key->getName());
        return $this->getAlterTableSchema($xmldb_table);
    }

    
    public function getDropDefaultSQL($xmldb_table, $xmldb_field) {
        return $this->getAlterTableSchema($xmldb_table, $xmldb_field, $xmldb_field);
    }

    
    function getCommentSQL ($xmldb_table) {
        return array();
    }

    
    public function isNameInUse($object_name, $type, $table_name) {
                return false;     }

    
    public static function getReservedWords() {
            $reserved_words = array (
            'add', 'all', 'alter', 'and', 'as', 'autoincrement',
            'between', 'by',
            'case', 'check', 'collate', 'column', 'commit', 'constraint', 'create', 'cross',
            'default', 'deferrable', 'delete', 'distinct', 'drop',
            'else', 'escape', 'except', 'exists',
            'foreign', 'from', 'full',
            'group',
            'having',
            'in', 'index', 'inner', 'insert', 'intersect', 'into', 'is', 'isnull',
            'join',
            'left', 'limit',
            'natural', 'not', 'notnull', 'null',
            'on', 'or', 'order', 'outer',
            'primary',
            'references', 'regexp', 'right', 'rollback',
            'select', 'set',
            'table', 'then', 'to', 'transaction',
            'union', 'unique', 'update', 'using',
            'values',
            'when', 'where'
        );
        return $reserved_words;
    }

    
    public function addslashes($s) {
                $s = str_replace("'",  "''", $s);
        return $s;
    }
}
