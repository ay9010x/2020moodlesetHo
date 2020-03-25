<?php



defined('MOODLE_INTERNAL') || die();


abstract class sql_generator {

                
    
    public $quote_string = '"';

    
    public $statement_end = ';';

    
    public $quote_all = false;

    
    public $integer_to_number = false;

    
    public $float_to_number   = false;

    
    public $number_type = 'NUMERIC';

    
    public $default_for_char = null;

    
    public $drop_default_value_required = false;

    
    public $drop_default_value = '';

    
    public $default_after_null = true;

    
    public $specify_nulls = false;

    
    public $primary_key_name = null;

    
    public $primary_keys = true;

    
    public $unique_keys = false;

    
    public $foreign_keys = false;

    
    public $drop_primary_key = 'ALTER TABLE TABLENAME DROP CONSTRAINT KEYNAME';

    
    public $drop_unique_key = 'ALTER TABLE TABLENAME DROP CONSTRAINT KEYNAME';

    
    public $drop_foreign_key = 'ALTER TABLE TABLENAME DROP CONSTRAINT KEYNAME';

    
    public $sequence_extra_code = true;

    
    public $sequence_name = 'auto_increment';

    
    public $sequence_name_small = false;

    
    public $sequence_only = false;

    
    public $add_table_comments  = true;

    
    public $add_after_clause = false;

    
    public $prefix_on_names = true;

    
    public $names_max_length = 30;

    
    public $concat_character = '||';

    
    public $rename_table_sql = 'ALTER TABLE OLDNAME RENAME TO NEWNAME';

    
    public $drop_table_sql = 'DROP TABLE TABLENAME';

    
    public $alter_column_sql = 'ALTER TABLE TABLENAME ALTER COLUMN COLUMNSPECS';

    
    public $alter_column_skip_default = false;

    
    public $alter_column_skip_type = false;

    
    public $alter_column_skip_notnull = false;

    
    public $rename_column_sql = 'ALTER TABLE TABLENAME RENAME COLUMN OLDFIELDNAME TO NEWFIELDNAME';

    
    public $drop_index_sql = 'DROP INDEX INDEXNAME';

    
    public $rename_index_sql = 'ALTER INDEX OLDINDEXNAME RENAME TO NEWINDEXNAME';

    
    public $rename_key_sql = 'ALTER TABLE TABLENAME CONSTRAINT OLDKEYNAME RENAME TO NEWKEYNAME';

    
    public $prefix;

    
    public $reserved_words;

    
    public $mdb;

    
    protected $temptables;

    
    public function __construct($mdb, $temptables = null) {
        $this->prefix         = $mdb->get_prefix();
        $this->reserved_words = $this->getReservedWords();
        $this->mdb            = $mdb;         $this->temptables     = $temptables;
    }

    
    public function dispose() {
        $this->mdb = null;
    }

    
    public function getEndedStatements($input) {

        if (is_array($input)) {
            foreach ($input as $key=>$content) {
                $input[$key] = $this->getEndedStatements($content);
            }
            return $input;
        } else {
            $input = trim($input).$this->statement_end;
            return $input;
        }
    }

    
    public function table_exists($table) {
        if (is_string($table)) {
            $tablename = $table;
        } else {
                        $tablename = $table->getName();
        }

        if ($this->temptables->is_temptable($tablename)) {
            return true;
        }

                $tables = $this->mdb->get_tables();
        return isset($tables[$tablename]);
    }

    
    public function getCreateStructureSQL($xmldb_structure) {
        $results = array();

        if ($tables = $xmldb_structure->getTables()) {
            foreach ($tables as $table) {
                $results = array_merge($results, $this->getCreateTableSQL($table));
            }
        }

        return $results;
    }

    
    public function getTableName(xmldb_table $xmldb_table, $quoted=true) {
                $tablename = $this->prefix.$xmldb_table->getName();

                if ($quoted) {
            $tablename = $this->getEncQuoted($tablename);
        }

        return $tablename;
    }

    
    public function getCreateTableSQL($xmldb_table) {
        if ($error = $xmldb_table->validateDefinition()) {
            throw new coding_exception($error);
        }

        $results = array();  
                $table = 'CREATE TABLE ' . $this->getTableName($xmldb_table) . ' (';

        if (!$xmldb_fields = $xmldb_table->getFields()) {
            return $results;
        }

        $sequencefield = null;

                foreach ($xmldb_fields as $xmldb_field) {
            if ($xmldb_field->getSequence()) {
                $sequencefield = $xmldb_field->getName();
            }
            $table .= "\n    " . $this->getFieldSQL($xmldb_table, $xmldb_field);
            $table .= ',';
        }
                if ($xmldb_keys = $xmldb_table->getKeys()) {
            foreach ($xmldb_keys as $xmldb_key) {
                if ($keytext = $this->getKeySQL($xmldb_table, $xmldb_key)) {
                    $table .= "\nCONSTRAINT " . $keytext . ',';
                }
                                if ($xmldb_key->getType() == XMLDB_KEY_FOREIGN_UNIQUE) {
                                        $xmldb_key->setType(XMLDB_KEY_UNIQUE);
                    if ($keytext = $this->getKeySQL($xmldb_table, $xmldb_key)) {
                        $table .= "\nCONSTRAINT " . $keytext . ',';
                    }
                }
                                if ($sequencefield and $xmldb_key->getType() == XMLDB_KEY_PRIMARY) {
                    $fields = $xmldb_key->getFields();
                    $field = reset($fields);
                    if ($sequencefield === $field) {
                        $sequencefield = null;
                    }
                }
            }
        }
                if ($sequencefield) {
            throw new ddl_exception('ddsequenceerror', $xmldb_table->getName());
        }

                $table = trim($table,',');
        $table .= "\n)";

                $results[] = $table;

                if ($this->add_table_comments && $xmldb_table->getComment()) {
            $comment = $this->getCommentSQL($xmldb_table);
                        $results = array_merge($results, $comment);
        }

                if ($xmldb_indexes = $xmldb_table->getIndexes()) {
            foreach ($xmldb_indexes as $xmldb_index) {
                                if ($indextext = $this->getCreateIndexSQL($xmldb_table, $xmldb_index)) {
                    $results = array_merge($results, $indextext);
                }
            }
        }

                if ($xmldb_keys = $xmldb_table->getKeys()) {
            foreach ($xmldb_keys as $xmldb_key) {
                                                if (!$this->getKeySQL($xmldb_table, $xmldb_key) || $xmldb_key->getType() == XMLDB_KEY_FOREIGN) {
                                        $index = new xmldb_index('anyname');
                    $index->setFields($xmldb_key->getFields());
                                        $createindex = false;                     switch ($xmldb_key->getType()) {
                        case XMLDB_KEY_UNIQUE:
                        case XMLDB_KEY_FOREIGN_UNIQUE:
                            $index->setUnique(true);
                            $createindex = true;
                            break;
                        case XMLDB_KEY_FOREIGN:
                            $index->setUnique(false);
                            $createindex = true;
                            break;
                    }
                    if ($createindex) {
                        if ($indextext = $this->getCreateIndexSQL($xmldb_table, $index)) {
                                                        $results = array_merge($results, $indextext);
                        }
                    }
                }
            }
        }

                if ($this->sequence_extra_code) {
                        foreach ($xmldb_fields as $xmldb_field) {
                if ($xmldb_field->getSequence()) {
                                        $sequence_sentences = $this->getCreateSequenceSQL($xmldb_table, $xmldb_field);
                                        $results = array_merge($results, $sequence_sentences);
                }
            }
        }

        return $results;
    }

    
    public function getCreateIndexSQL($xmldb_table, $xmldb_index) {
        if ($error = $xmldb_index->validateDefinition($xmldb_table)) {
            throw new coding_exception($error);
        }

        $unique = '';
        $suffix = 'ix';
        if ($xmldb_index->getUnique()) {
            $unique = ' UNIQUE';
            $suffix = 'uix';
        }

        $index = 'CREATE' . $unique . ' INDEX ';
        $index .= $this->getNameForObject($xmldb_table->getName(), implode(', ', $xmldb_index->getFields()), $suffix);
        $index .= ' ON ' . $this->getTableName($xmldb_table);
        $index .= ' (' . implode(', ', $this->getEncQuoted($xmldb_index->getFields())) . ')';

        return array($index);
    }

    
    public function getFieldSQL($xmldb_table, $xmldb_field, $skip_type_clause = NULL, $skip_default_clause = NULL, $skip_notnull_clause = NULL, $specify_nulls_clause = NULL, $specify_field_name = true)  {
        if ($error = $xmldb_field->validateDefinition($xmldb_table)) {
            throw new coding_exception($error);
        }

        $skip_type_clause = is_null($skip_type_clause) ? $this->alter_column_skip_type : $skip_type_clause;
        $skip_default_clause = is_null($skip_default_clause) ? $this->alter_column_skip_default : $skip_default_clause;
        $skip_notnull_clause = is_null($skip_notnull_clause) ? $this->alter_column_skip_notnull : $skip_notnull_clause;
        $specify_nulls_clause = is_null($specify_nulls_clause) ? $this->specify_nulls : $specify_nulls_clause;

                if ($this->integer_to_number) {
            if ($xmldb_field->getType() == XMLDB_TYPE_INTEGER) {
                $xmldb_field->setType(XMLDB_TYPE_NUMBER);
            }
        }
                if ($this->float_to_number) {
            if ($xmldb_field->getType() == XMLDB_TYPE_FLOAT) {
                $xmldb_field->setType(XMLDB_TYPE_NUMBER);
            }
        }

        $field = '';                 if ($specify_field_name) {
            $field .= $this->getEncQuoted($xmldb_field->getName());
        }
                if (!$skip_type_clause) {
                        $field .= ' ' . $this->getTypeSQL($xmldb_field->getType(), $xmldb_field->getLength(), $xmldb_field->getDecimals());
        }
                        $notnull = '';
                if (!$skip_notnull_clause) {
            if ($xmldb_field->getNotNull()) {
                $notnull = ' NOT NULL';
            } else {
                if ($specify_nulls_clause) {
                    $notnull = ' NULL';
                }
            }
        }
                $default_clause = '';
        if (!$skip_default_clause) {             $default_clause = $this->getDefaultClause($xmldb_field);
        }
                if ($this->default_after_null) {
            $field .= $notnull . $default_clause;
        } else {
            $field .= $default_clause . $notnull;
        }
                if ($xmldb_field->getSequence()) {
            if($xmldb_field->getLength()<=9 && $this->sequence_name_small) {
                $sequencename=$this->sequence_name_small;
            } else {
                $sequencename=$this->sequence_name;
            }
            $field .= ' ' . $sequencename;
            if ($this->sequence_only) {
                                                $sql = $this->getEncQuoted($xmldb_field->getName()) . ' ' . $sequencename;
                return $sql;
            }
        }
        return $field;
    }

    
    public function getKeySQL($xmldb_table, $xmldb_key) {

        $key = '';

        switch ($xmldb_key->getType()) {
            case XMLDB_KEY_PRIMARY:
                if ($this->primary_keys) {
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

    
    public function getDefaultValue($xmldb_field) {

        $default = null;

        if ($xmldb_field->getDefault() !== NULL) {
            if ($xmldb_field->getType() == XMLDB_TYPE_CHAR ||
                $xmldb_field->getType() == XMLDB_TYPE_TEXT) {
                    if ($xmldb_field->getDefault() === '') {                         $default = "'" . $this->default_for_char . "'";
                    } else {
                        $default = "'" . $this->addslashes($xmldb_field->getDefault()) . "'";
                    }
            } else {
                $default = $xmldb_field->getDefault();
            }
        } else {
                                    if ($this->default_for_char !== NULL &&
                $xmldb_field->getType() == XMLDB_TYPE_CHAR &&
                $xmldb_field->getNotNull()) {
                $default = "'" . $this->default_for_char . "'";
            } else {
                                                if ($this->drop_default_value_required &&
                    $xmldb_field->getType() != XMLDB_TYPE_TEXT &&
                    $xmldb_field->getType() != XMLDB_TYPE_BINARY && !$xmldb_field->getNotNull()) {
                    $default = $this->drop_default_value;
                }
            }
        }
        return $default;
    }

    
    public function getDefaultClause($xmldb_field) {

        $defaultvalue = $this->getDefaultValue ($xmldb_field);

        if ($defaultvalue !== null) {
            return ' DEFAULT ' . $defaultvalue;
        } else {
            return null;
        }
    }

    
    public function getRenameTableSQL($xmldb_table, $newname) {

        $results = array();  
        $newt = new xmldb_table($newname); 
        $rename = str_replace('OLDNAME', $this->getTableName($xmldb_table), $this->rename_table_sql);
        $rename = str_replace('NEWNAME', $this->getTableName($newt), $rename);

        $results[] = $rename;

                $extra_sentences = $this->getRenameTableExtraSQL($xmldb_table, $newname);
        $results = array_merge($results, $extra_sentences);

        return $results;
    }

    
    public function getDropTableSQL($xmldb_table) {

        $results = array();  
        $drop = str_replace('TABLENAME', $this->getTableName($xmldb_table), $this->drop_table_sql);

        $results[] = $drop;

                $extra_sentences = $this->getDropTableExtraSQL($xmldb_table);
        $results = array_merge($results, $extra_sentences);

        return $results;
    }

    
    public function getAddFieldSQL($xmldb_table, $xmldb_field, $skip_type_clause = NULL, $skip_default_clause = NULL, $skip_notnull_clause = NULL) {

        $skip_type_clause = is_null($skip_type_clause) ? $this->alter_column_skip_type : $skip_type_clause;
        $skip_default_clause = is_null($skip_default_clause) ? $this->alter_column_skip_default : $skip_default_clause;
        $skip_notnull_clause = is_null($skip_notnull_clause) ? $this->alter_column_skip_notnull : $skip_notnull_clause;

        $results = array();

                $tablename = $this->getTableName($xmldb_table);

                $sql = $this->getFieldSQL($xmldb_table, $xmldb_field, $skip_type_clause,
                                  $skip_default_clause,
                                  $skip_notnull_clause);
        $altertable = 'ALTER TABLE ' . $tablename . ' ADD ' . $sql;
                if ($this->add_after_clause && $xmldb_field->getPrevious()) {
            $altertable .= ' AFTER ' . $this->getEncQuoted($xmldb_field->getPrevious());
        }
        $results[] = $altertable;

        return $results;
    }

    
    public function getDropFieldSQL($xmldb_table, $xmldb_field) {

        $results = array();

                $tablename = $this->getTableName($xmldb_table);
        $fieldname = $this->getEncQuoted($xmldb_field->getName());

                $results[] = 'ALTER TABLE ' . $tablename . ' DROP COLUMN ' . $fieldname;

        return $results;
    }

    
    public function getAlterFieldSQL($xmldb_table, $xmldb_field, $skip_type_clause = NULL, $skip_default_clause = NULL, $skip_notnull_clause = NULL) {

        $skip_type_clause = is_null($skip_type_clause) ? $this->alter_column_skip_type : $skip_type_clause;
        $skip_default_clause = is_null($skip_default_clause) ? $this->alter_column_skip_default : $skip_default_clause;
        $skip_notnull_clause = is_null($skip_notnull_clause) ? $this->alter_column_skip_notnull : $skip_notnull_clause;

        $results = array();

                $tablename = $this->getTableName($xmldb_table);
        $fieldname = $this->getEncQuoted($xmldb_field->getName());

                $alter = str_replace('TABLENAME', $this->getTableName($xmldb_table), $this->alter_column_sql);
        $colspec = $this->getFieldSQL($xmldb_table, $xmldb_field, $skip_type_clause,
                                      $skip_default_clause,
                                      $skip_notnull_clause,
                                      true);
        $alter = str_replace('COLUMNSPECS', $colspec, $alter);

                if ($this->add_after_clause && $xmldb_field->getPrevious()) {
            $alter .= ' after ' . $this->getEncQuoted($xmldb_field->getPrevious());
        }

                $results[] = $alter;

        return $results;
    }

    
    public function getModifyDefaultSQL($xmldb_table, $xmldb_field) {

        $results = array();

                $tablename = $this->getTableName($xmldb_table);
        $fieldname = $this->getEncQuoted($xmldb_field->getName());

                if ($xmldb_field->getDefault() === null) {
            $results = $this->getDropDefaultSQL($xmldb_table, $xmldb_field);         } else {
            $results = $this->getCreateDefaultSQL($xmldb_table, $xmldb_field);         }

        return $results;
    }

    
    public function getRenameFieldSQL($xmldb_table, $xmldb_field, $newname) {

        $results = array();  
                                                                if ($xmldb_field->getName() == 'id') {
            return array();
        }

        $rename = str_replace('TABLENAME', $this->getTableName($xmldb_table), $this->rename_column_sql);
        $rename = str_replace('OLDFIELDNAME', $this->getEncQuoted($xmldb_field->getName()), $rename);
        $rename = str_replace('NEWFIELDNAME', $this->getEncQuoted($newname), $rename);

        $results[] = $rename;

                $extra_sentences = $this->getRenameFieldExtraSQL($xmldb_table, $xmldb_field, $newname);
        $results = array_merge($results, $extra_sentences);

        return $results;
    }

    
    public function getAddKeySQL($xmldb_table, $xmldb_key) {

        $results = array();

                if ($keyclause = $this->getKeySQL($xmldb_table, $xmldb_key)) {
            $key = 'ALTER TABLE ' . $this->getTableName($xmldb_table) .
               ' ADD CONSTRAINT ' . $keyclause;
            $results[] = $key;
        }

                        if (!$keyclause || $xmldb_key->getType() == XMLDB_KEY_FOREIGN) {
                        if ($xmldb_key->getType() == XMLDB_KEY_FOREIGN) {                      $indextype = XMLDB_INDEX_NOTUNIQUE;
            } else {
                $indextype = XMLDB_INDEX_UNIQUE;
            }
            $xmldb_index = new xmldb_index('anyname', $indextype, $xmldb_key->getFields());
            if (!$this->mdb->get_manager()->index_exists($xmldb_table, $xmldb_index)) {
                $results = array_merge($results, $this->getAddIndexSQL($xmldb_table, $xmldb_index));
            }
        }

                if ($xmldb_key->getType() == XMLDB_KEY_FOREIGN_UNIQUE && $this->unique_keys) {
                        $xmldb_key->setType(XMLDB_KEY_UNIQUE);
            $results = array_merge($results, $this->getAddKeySQL($xmldb_table, $xmldb_key));
        }

                return $results;
    }

    
    public function getDropKeySQL($xmldb_table, $xmldb_key) {

        $results = array();

                                                $dbkeyname = $this->mdb->get_manager()->find_key_name($xmldb_table, $xmldb_key);

                $dropkey = false;
        switch ($xmldb_key->getType()) {
            case XMLDB_KEY_PRIMARY:
                if ($this->primary_keys) {
                    $template = $this->drop_primary_key;
                    $dropkey = true;
                }
                break;
            case XMLDB_KEY_UNIQUE:
                if ($this->unique_keys) {
                    $template = $this->drop_unique_key;
                    $dropkey = true;
                }
                break;
            case XMLDB_KEY_FOREIGN_UNIQUE:
            case XMLDB_KEY_FOREIGN:
                if ($this->foreign_keys) {
                    $template = $this->drop_foreign_key;
                    $dropkey = true;
                }
                break;
        }
                if ($dropkey) {
                        $dropsql = str_replace('TABLENAME', $this->getTableName($xmldb_table), $template);
            $dropsql = str_replace('KEYNAME', $dbkeyname, $dropsql);

            $results[] = $dropsql;
        }

                        if (!$dropkey || $xmldb_key->getType() == XMLDB_KEY_FOREIGN) {
                        $xmldb_index = new xmldb_index('anyname', XMLDB_INDEX_UNIQUE, $xmldb_key->getFields());
            if ($this->mdb->get_manager()->index_exists($xmldb_table, $xmldb_index)) {
                $results = array_merge($results, $this->getDropIndexSQL($xmldb_table, $xmldb_index));
            }
        }

                if ($xmldb_key->getType() == XMLDB_KEY_FOREIGN_UNIQUE && $this->unique_keys) {
                        $xmldb_key->setType(XMLDB_KEY_UNIQUE);
            $results = array_merge($results, $this->getDropKeySQL($xmldb_table, $xmldb_key));
        }

                return $results;
    }

    
    public function getRenameKeySQL($xmldb_table, $xmldb_key, $newname) {

        $results = array();

                $dbkeyname = $this->mdb->get_manager()->find_key_name($xmldb_table, $xmldb_key);

                if (($xmldb_key->getType() == XMLDB_KEY_PRIMARY && !$this->primary_keys) ||
            ($xmldb_key->getType() == XMLDB_KEY_UNIQUE && !$this->unique_keys) ||
            ($xmldb_key->getType() == XMLDB_KEY_FOREIGN && !$this->foreign_keys) ||
            ($xmldb_key->getType() == XMLDB_KEY_FOREIGN_UNIQUE && !$this->unique_keys && !$this->foreign_keys)) {
                        $xmldb_index = new xmldb_index($xmldb_key->getName());
            $xmldb_index->setFields($xmldb_key->getFields());
            return $this->getRenameIndexSQL($xmldb_table, $xmldb_index, $newname);
        }

                        $renamesql = str_replace('TABLENAME', $this->getTableName($xmldb_table), $this->rename_key_sql);
        $renamesql = str_replace('OLDKEYNAME', $dbkeyname, $renamesql);
        $renamesql = str_replace('NEWKEYNAME', $newname, $renamesql);

                if ($renamesql) {
            $results[] = $renamesql;
        }

        return $results;
    }

    
    public function getAddIndexSQL($xmldb_table, $xmldb_index) {

                return $this->getCreateIndexSQL($xmldb_table, $xmldb_index);
    }

    
    public function getDropIndexSQL($xmldb_table, $xmldb_index) {

        $results = array();

                $dbindexnames = $this->mdb->get_manager()->find_index_name($xmldb_table, $xmldb_index, true);

                if ($dbindexnames) {
            foreach ($dbindexnames as $dbindexname) {
                $dropsql = str_replace('TABLENAME', $this->getTableName($xmldb_table), $this->drop_index_sql);
                $dropsql = str_replace('INDEXNAME', $this->getEncQuoted($dbindexname), $dropsql);
                $results[] = $dropsql;
            }
        }

        return $results;
    }

    
    function getRenameIndexSQL($xmldb_table, $xmldb_index, $newname) {
                if (empty($this->rename_index_sql)) {
            return array();
        }

                $dbindexname = $this->mdb->get_manager()->find_index_name($xmldb_table, $xmldb_index);
                $renamesql = str_replace('TABLENAME', $this->getTableName($xmldb_table), $this->rename_index_sql);
        $renamesql = str_replace('OLDINDEXNAME', $this->getEncQuoted($dbindexname), $renamesql);
        $renamesql = str_replace('NEWINDEXNAME', $this->getEncQuoted($newname), $renamesql);

        return array($renamesql);
    }

    
    public function getNameForObject($tablename, $fields, $suffix='') {

        $name = '';

                                                static $used_names = array();

                $tablearr = explode ('_', $tablename);
        foreach ($tablearr as $table) {
            $name .= substr(trim($table),0,4);
        }
        $name .= '_';
        $fieldsarr = explode (',', $fields);
        foreach ($fieldsarr as $field) {
            $name .= substr(trim($field),0,3);
        }
                $name = trim($this->prefix . $name);

                $maxlengthwithoutsuffix = $this->names_max_length - strlen($suffix) - ($suffix ? 1 : 0);
        $namewithsuffix = substr($name, 0, $maxlengthwithoutsuffix) . ($suffix ? ('_' . $suffix) : '');

                $counter = 1;
        while (in_array($namewithsuffix, $used_names) || $this->isNameInUse($namewithsuffix, $suffix, $tablename)) {
                        $counter++;
            $namewithsuffix = substr($name, 0, $maxlengthwithoutsuffix - strlen($counter)) .
                    $counter . ($suffix ? ('_' . $suffix) : '');
        }

                $used_names[] = $namewithsuffix;

                $namewithsuffix = $this->getEncQuoted($namewithsuffix);

        return $namewithsuffix;
    }

    
    public function getEncQuoted($input) {

        if (is_array($input)) {
            foreach ($input as $key=>$content) {
                $input[$key] = $this->getEncQuoted($content);
            }
            return $input;
        } else {
                        $input = strtolower($input);
                        if ($this->quote_all || in_array($input, $this->reserved_words) || strpos($input, '-') !== false) {
                $input = $this->quote_string . $input . $this->quote_string;
            }
            return $input;
        }
    }

    
    function getExecuteInsertSQL($statement) {

         $results = array();  
         if ($sentences = $statement->getSentences()) {
             foreach ($sentences as $sentence) {
                                  $fields = $statement->getFieldsFromInsertSentence($sentence);
                                  $values = $statement->getValuesFromInsertSentence($sentence);
                                  foreach($values as $key => $value) {
                                          $value = trim($value,"'");
                     if (stristr($value, 'CONCAT') !== false){
                                                  preg_match("/CONCAT\s*\((.*)\)$/is", trim($value), $matches);
                         if (isset($matches[1])) {
                             $part = $matches[1];
                                                          $arr = xmldb_object::comma2array($part);
                             if ($arr) {
                                 $value = $this->getConcatSQL($arr);
                             }
                         }
                     }
                                          $value = $this->addslashes($value);
                                          $value = "'" . $value . "'";
                                          $values[$key] = $value;
                 }

                                  foreach($fields as $key => $field) {
                     $fields[$key] = $this->getEncQuoted($field);
                 }
                              $sql = 'INSERT INTO ' . $this->getEncQuoted($this->prefix . $statement->getTable()) .
                         '(' . implode(', ', $fields) . ') ' .
                         'VALUES (' . implode(', ', $values) . ')';
                 $results[] = $sql;
             }

         }
         return $results;
    }

    
    public function getConcatSQL($elements) {

                foreach($elements as $key => $element) {
            $element = trim($element);
            if (substr($element, 0, 1) == '"' &&
                substr($element, -1, 1) == '"') {
                    $elements[$key] = "'" . trim($element, '"') . "'";
            }
        }

                return call_user_func_array(array($this->mdb, 'sql_concat'), $elements);
    }

    
    public function getSequenceFromDB($xmldb_table) {
        return false;
    }

    
    public function isNameInUse($object_name, $type, $table_name) {
        return false;                           }



    
    public abstract function getResetSequenceSQL($table);

    
    abstract public function getCreateTempTableSQL($xmldb_table);

    
    public abstract function getTypeSQL($xmldb_type, $xmldb_length=null, $xmldb_decimals=null);

    
    public function getRenameFieldExtraSQL($xmldb_table, $xmldb_field) {
        return array();
    }

    
    public function getCreateSequenceSQL($xmldb_table, $xmldb_field) {
        return array();
    }

    
    public abstract function getCommentSQL($xmldb_table);

    
    public function getRenameTableExtraSQL($xmldb_table, $newname) {
        return array();
    }

    
    public function getDropTableExtraSQL($xmldb_table) {
        return array();
    }

    
    public abstract function getDropDefaultSQL($xmldb_table, $xmldb_field);

    
    public abstract function getCreateDefaultSQL($xmldb_table, $xmldb_field);

    
    public static function getReservedWords() {
        throw new coding_exception('getReservedWords() method needs to be overridden in each subclass of sql_generator');
    }

    
    public static function getAllReservedWords() {
        global $CFG;

        $generators = array('mysql', 'postgres', 'oracle', 'mssql');
        $reserved_words = array();

        foreach($generators as $generator) {
            $class = $generator . '_sql_generator';
            require_once("$CFG->libdir/ddl/$class.php");
            foreach (call_user_func(array($class, 'getReservedWords')) as $word) {
                $reserved_words[$word][] = $generator;
            }
        }
        ksort($reserved_words);
        return $reserved_words;
    }

    
    public function addslashes($s) {
                $s = str_replace('\\','\\\\',$s);
        $s = str_replace("\0","\\\0", $s);
        $s = str_replace("'",  "\\'", $s);
        return $s;
    }
}
