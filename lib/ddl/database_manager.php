<?php



defined('MOODLE_INTERNAL') || die();


class database_manager {

    
    protected $mdb;

    
    public $generator;

    
    public function __construct($mdb, $generator) {
        $this->mdb       = $mdb;
        $this->generator = $generator;
    }

    
    public function dispose() {
        if ($this->generator) {
            $this->generator->dispose();
            $this->generator = null;
        }
        $this->mdb = null;
    }

    
    protected function execute_sql_arr(array $sqlarr, $tablenames = null) {
        $this->mdb->change_database_structure($sqlarr, $tablenames);
    }

    
    protected function execute_sql($sql) {
        $this->mdb->change_database_structure($sql);
    }

    
    public function table_exists($table) {
        if (!is_string($table) and !($table instanceof xmldb_table)) {
            throw new ddl_exception('ddlunknownerror', NULL, 'incorrect table parameter!');
        }
        return $this->generator->table_exists($table);
    }

    
    public function reset_sequence($table) {
        if (!is_string($table) and !($table instanceof xmldb_table)) {
            throw new ddl_exception('ddlunknownerror', NULL, 'incorrect table parameter!');
        } else {
            if ($table instanceof xmldb_table) {
                $tablename = $table->getName();
            } else {
                $tablename = $table;
            }
        }

        
        if (!$sqlarr = $this->generator->getResetSequenceSQL($table)) {
            throw new ddl_exception('ddlunknownerror', null, 'table reset sequence sql not generated');
        }

        $this->execute_sql_arr($sqlarr, array($tablename));
    }

    
    public function field_exists($table, $field) {
                if (is_string($table)) {
            $tablename = $table;
        } else {
            $tablename = $table->getName();
        }

                if (!$this->table_exists($table)) {
            throw new ddl_table_missing_exception($tablename);
        }

        if (is_string($field)) {
            $fieldname = $field;
        } else {
                        $fieldname = $field->getName();
        }

                $columns = $this->mdb->get_columns($tablename);

        $exists = array_key_exists($fieldname,  $columns);

        return $exists;
    }

    
    public function find_index_name(xmldb_table $xmldb_table, xmldb_index $xmldb_index, $returnall = false) {
                $tablename = $xmldb_table->getName();

                if (!$this->table_exists($xmldb_table)) {
            throw new ddl_table_missing_exception($tablename);
        }

                $indcolumns = $xmldb_index->getFields();

                $indexes = $this->mdb->get_indexes($tablename);

        $return = array();

                foreach ($indexes as $indexname => $index) {
            $columns = $index['columns'];
                        $diferences = array_merge(array_diff($columns, $indcolumns), array_diff($indcolumns, $columns));
                        if (empty($diferences)) {
                if ($returnall) {
                    $return[] = $indexname;
                } else {
                    return $indexname;
                }
            }
        }

        if ($return and $returnall) {
            return $return;
        }

                return false;
    }

    
    public function index_exists(xmldb_table $xmldb_table, xmldb_index $xmldb_index) {
        if (!$this->table_exists($xmldb_table)) {
            return false;
        }
        return ($this->find_index_name($xmldb_table, $xmldb_index) !== false);
    }

    
    public function find_key_name(xmldb_table $xmldb_table, xmldb_key $xmldb_key) {

        $keycolumns = $xmldb_key->getFields();

                                                                                                
                        if ($this->generator->primary_key_name && $xmldb_key->getType() == XMLDB_KEY_PRIMARY) {
            return $this->generator->primary_key_name;
        } else {
                        switch ($xmldb_key->getType()) {
                case XMLDB_KEY_PRIMARY:
                    $suffix = 'pk';
                    break;
                case XMLDB_KEY_UNIQUE:
                    $suffix = 'uk';
                    break;
                case XMLDB_KEY_FOREIGN_UNIQUE:
                case XMLDB_KEY_FOREIGN:
                    $suffix = 'fk';
                    break;
            }
                        return $this->generator->getNameForObject($xmldb_table->getName(), implode(', ', $xmldb_key->getFields()), $suffix);
        }
    }

    
    public function delete_tables_from_xmldb_file($file) {

        $xmldb_file = new xmldb_file($file);

        if (!$xmldb_file->fileExists()) {
            throw new ddl_exception('ddlxmlfileerror', null, 'File does not exist');
        }

        $loaded    = $xmldb_file->loadXMLStructure();
        $structure = $xmldb_file->getStructure();

        if (!$loaded || !$xmldb_file->isLoaded()) {
                        if ($structure) {
                if ($errors = $structure->getAllErrors()) {
                    throw new ddl_exception('ddlxmlfileerror', null, 'Errors found in XMLDB file: '. implode (', ', $errors));
                }
            }
            throw new ddl_exception('ddlxmlfileerror', null, 'not loaded??');
        }

        if ($xmldb_tables = $structure->getTables()) {
                        $xmldb_tables = array_reverse($xmldb_tables);
            foreach($xmldb_tables as $table) {
                if ($this->table_exists($table)) {
                    $this->drop_table($table);
                }
            }
        }
    }

    
    public function drop_table(xmldb_table $xmldb_table) {
                if (!$this->table_exists($xmldb_table)) {
            throw new ddl_table_missing_exception($xmldb_table->getName());
        }

        if (!$sqlarr = $this->generator->getDropTableSQL($xmldb_table)) {
            throw new ddl_exception('ddlunknownerror', null, 'table drop sql not generated');
        }
        $this->execute_sql_arr($sqlarr, array($xmldb_table->getName()));
    }

    
    private function load_xmldb_file($file) {
        $xmldb_file = new xmldb_file($file);

        if (!$xmldb_file->fileExists()) {
            throw new ddl_exception('ddlxmlfileerror', null, 'File does not exist');
        }

        $loaded = $xmldb_file->loadXMLStructure();
        if (!$loaded || !$xmldb_file->isLoaded()) {
                        if ($structure = $xmldb_file->getStructure()) {
                if ($errors = $structure->getAllErrors()) {
                    throw new ddl_exception('ddlxmlfileerror', null, 'Errors found in XMLDB file: '. implode (', ', $errors));
                }
            }
            throw new ddl_exception('ddlxmlfileerror', null, 'not loaded??');
        }

        return $xmldb_file;
    }

    
    public function install_from_xmldb_file($file) {
        $xmldb_file = $this->load_xmldb_file($file);
        $xmldb_structure = $xmldb_file->getStructure();
        $this->install_from_xmldb_structure($xmldb_structure);
    }

    
    public function install_one_table_from_xmldb_file($file, $tablename, $cachestructures = false) {

        static $xmldbstructurecache = array();         if (!empty($xmldbstructurecache) && array_key_exists($file, $xmldbstructurecache)) {
            $xmldb_structure = $xmldbstructurecache[$file];
        } else {
            $xmldb_file = $this->load_xmldb_file($file);
            $xmldb_structure = $xmldb_file->getStructure();
            if ($cachestructures) {
                $xmldbstructurecache[$file] = $xmldb_structure;
            }
        }

        $targettable = $xmldb_structure->getTable($tablename);
        if (is_null($targettable)) {
            throw new ddl_exception('ddlunknowntable', null, 'The table ' . $tablename . ' is not defined in file ' . $file);
        }
        $targettable->setNext(NULL);
        $targettable->setPrevious(NULL);

        $tempstructure = new xmldb_structure('temp');
        $tempstructure->addTable($targettable);
        $this->install_from_xmldb_structure($tempstructure);
    }

    
    public function install_from_xmldb_structure($xmldb_structure) {

        if (!$sqlarr = $this->generator->getCreateStructureSQL($xmldb_structure)) {
            return;         }

        $tablenames = array();
        foreach ($xmldb_structure as $xmldb_table) {
            if ($xmldb_table instanceof xmldb_table) {
                $tablenames[] = $xmldb_table->getName();
            }
        }
        $this->execute_sql_arr($sqlarr, $tablenames);
    }

    
    public function create_table(xmldb_table $xmldb_table) {
                if ($this->table_exists($xmldb_table)) {
            throw new ddl_exception('ddltablealreadyexists', $xmldb_table->getName());
        }

        if (!$sqlarr = $this->generator->getCreateTableSQL($xmldb_table)) {
            throw new ddl_exception('ddlunknownerror', null, 'table create sql not generated');
        }
        $this->execute_sql_arr($sqlarr, array($xmldb_table->getName()));
    }

    
    public function create_temp_table(xmldb_table $xmldb_table) {

                if ($this->table_exists($xmldb_table)) {
            throw new ddl_exception('ddltablealreadyexists', $xmldb_table->getName());
        }

        if (!$sqlarr = $this->generator->getCreateTempTableSQL($xmldb_table)) {
            throw new ddl_exception('ddlunknownerror', null, 'temp table create sql not generated');
        }
        $this->execute_sql_arr($sqlarr, array($xmldb_table->getName()));
    }

    
    public function drop_temp_table(xmldb_table $xmldb_table) {
        debugging('database_manager::drop_temp_table() is deprecated, use database_manager::drop_table() instead');
        $this->drop_table($xmldb_table);
    }

    
    public function rename_table(xmldb_table $xmldb_table, $newname) {
                if (!$newname) {
            throw new ddl_exception('ddlunknownerror', null, 'newname can not be empty');
        }

        $check = new xmldb_table($newname);

                if (!$this->table_exists($xmldb_table)) {
            if ($this->table_exists($check)) {
                throw new ddl_exception('ddlunknownerror', null, 'table probably already renamed');
            } else {
                throw new ddl_table_missing_exception($xmldb_table->getName());
            }
        }

                if ($this->table_exists($check)) {
            throw new ddl_exception('ddltablealreadyexists', $check->getName(), 'can not rename table');
        }

        if (!$sqlarr = $this->generator->getRenameTableSQL($xmldb_table, $newname)) {
            throw new ddl_exception('ddlunknownerror', null, 'table rename sql not generated');
        }

        $this->execute_sql_arr($sqlarr);
    }

    
    public function add_field(xmldb_table $xmldb_table, xmldb_field $xmldb_field) {
                 if ($this->field_exists($xmldb_table, $xmldb_field)) {
            throw new ddl_exception('ddlfieldalreadyexists', $xmldb_field->getName());
        }

                        if ($xmldb_field->getNotNull() && $this->generator->getDefaultValue($xmldb_field) === NULL && $this->mdb->count_records($xmldb_table->getName())) {
            throw new ddl_exception('ddlunknownerror', null, 'Field ' . $xmldb_table->getName() . '->' . $xmldb_field->getName() .
                      ' cannot be added. Not null fields added to non empty tables require default value. Create skipped');
        }

        if (!$sqlarr = $this->generator->getAddFieldSQL($xmldb_table, $xmldb_field)) {
            throw new ddl_exception('ddlunknownerror', null, 'addfield sql not generated');
        }
        $this->execute_sql_arr($sqlarr, array($xmldb_table->getName()));
    }

    
    public function drop_field(xmldb_table $xmldb_table, xmldb_field $xmldb_field) {
        if (!$this->table_exists($xmldb_table)) {
            throw new ddl_table_missing_exception($xmldb_table->getName());
        }
                if (!$this->field_exists($xmldb_table, $xmldb_field)) {
            throw new ddl_field_missing_exception($xmldb_field->getName(), $xmldb_table->getName());
        }
                $this->check_field_dependencies($xmldb_table, $xmldb_field);

        if (!$sqlarr = $this->generator->getDropFieldSQL($xmldb_table, $xmldb_field)) {
            throw new ddl_exception('ddlunknownerror', null, 'drop_field sql not generated');
        }

        $this->execute_sql_arr($sqlarr, array($xmldb_table->getName()));
    }

    
    public function change_field_type(xmldb_table $xmldb_table, xmldb_field $xmldb_field) {
        if (!$this->table_exists($xmldb_table)) {
            throw new ddl_table_missing_exception($xmldb_table->getName());
        }
                if (!$this->field_exists($xmldb_table, $xmldb_field)) {
            throw new ddl_field_missing_exception($xmldb_field->getName(), $xmldb_table->getName());
        }
                $this->check_field_dependencies($xmldb_table, $xmldb_field);

        if (!$sqlarr = $this->generator->getAlterFieldSQL($xmldb_table, $xmldb_field)) {
            return;         }

        $this->execute_sql_arr($sqlarr, array($xmldb_table->getName()));
    }

    
    public function change_field_precision(xmldb_table $xmldb_table, xmldb_field $xmldb_field) {
                $this->change_field_type($xmldb_table, $xmldb_field);
    }

    
    public function change_field_unsigned(xmldb_table $xmldb_table, xmldb_field $xmldb_field) {
        debugging('All unsigned numbers are converted to signed automatically during Moodle upgrade.');
        $this->change_field_type($xmldb_table, $xmldb_field);
    }

    
    public function change_field_notnull(xmldb_table $xmldb_table, xmldb_field $xmldb_field) {
                $this->change_field_type($xmldb_table, $xmldb_field);
    }

    
    public function change_field_default(xmldb_table $xmldb_table, xmldb_field $xmldb_field) {
        if (!$this->table_exists($xmldb_table)) {
            throw new ddl_table_missing_exception($xmldb_table->getName());
        }
                if (!$this->field_exists($xmldb_table, $xmldb_field)) {
            throw new ddl_field_missing_exception($xmldb_field->getName(), $xmldb_table->getName());
        }
                $this->check_field_dependencies($xmldb_table, $xmldb_field);

        if (!$sqlarr = $this->generator->getModifyDefaultSQL($xmldb_table, $xmldb_field)) {
            return;         }

        $this->execute_sql_arr($sqlarr, array($xmldb_table->getName()));
    }

    
    public function rename_field(xmldb_table $xmldb_table, xmldb_field $xmldb_field, $newname) {
        if (empty($newname)) {
            throw new ddl_exception('ddlunknownerror', null, 'newname can not be empty');
        }

        if (!$this->table_exists($xmldb_table)) {
            throw new ddl_table_missing_exception($xmldb_table->getName());
        }

                if (!$this->field_exists($xmldb_table, $xmldb_field)) {
            throw new ddl_field_missing_exception($xmldb_field->getName(), $xmldb_table->getName());
        }

                if (!$xmldb_field->getType()) {
            throw new ddl_exception('ddlunknownerror', null,
                      'Field ' . $xmldb_table->getName() . '->' . $xmldb_field->getName() .
                      ' must contain full specs. Rename skipped');
        }

                if ($xmldb_field->getName() == 'id') {
            throw new ddl_exception('ddlunknownerror', null,
                      'Field ' . $xmldb_table->getName() . '->' . $xmldb_field->getName() .
                      ' cannot be renamed. Rename skipped');
        }

        if (!$sqlarr = $this->generator->getRenameFieldSQL($xmldb_table, $xmldb_field, $newname)) {
            return;         }

        $this->execute_sql_arr($sqlarr, array($xmldb_table->getName()));
    }

    
    private function check_field_dependencies(xmldb_table $xmldb_table, xmldb_field $xmldb_field) {

                if (!$this->table_exists($xmldb_table)) {
            throw new ddl_table_missing_exception($xmldb_table->getName());
        }

                if (!$this->field_exists($xmldb_table, $xmldb_field)) {
            throw new ddl_field_missing_exception($xmldb_field->getName(), $xmldb_table->getName());
        }

                if ($indexes = $this->mdb->get_indexes($xmldb_table->getName(), false)) {
            foreach ($indexes as $indexname => $index) {
                $columns = $index['columns'];
                if (in_array($xmldb_field->getName(), $columns)) {
                    throw new ddl_dependency_exception('column', $xmldb_table->getName() . '->' . $xmldb_field->getName(),
                                                       'index', $indexname . ' (' . implode(', ', $columns)  . ')');
                }
            }
        }
    }

    
    public function add_key(xmldb_table $xmldb_table, xmldb_key $xmldb_key) {

        if ($xmldb_key->getType() == XMLDB_KEY_PRIMARY) {             throw new ddl_exception('ddlunknownerror', null, 'Primary Keys can be added at table create time only');
        }

        if (!$sqlarr = $this->generator->getAddKeySQL($xmldb_table, $xmldb_key)) {
            return;         }

        $this->execute_sql_arr($sqlarr, array($xmldb_table->getName()));
    }

    
    public function drop_key(xmldb_table $xmldb_table, xmldb_key $xmldb_key) {
        if ($xmldb_key->getType() == XMLDB_KEY_PRIMARY) {             throw new ddl_exception('ddlunknownerror', null, 'Primary Keys can be deleted at table drop time only');
        }

        if (!$sqlarr = $this->generator->getDropKeySQL($xmldb_table, $xmldb_key)) {
            return;         }

        $this->execute_sql_arr($sqlarr, array($xmldb_table->getName()));
    }

    
    public function rename_key(xmldb_table $xmldb_table, xmldb_key $xmldb_key, $newname) {
        debugging('rename_key() is one experimental feature. You must not use it in production!', DEBUG_DEVELOPER);

                if (!$newname) {
            throw new ddl_exception('ddlunknownerror', null, 'newname can not be empty');
        }

        if (!$sqlarr = $this->generator->getRenameKeySQL($xmldb_table, $xmldb_key, $newname)) {
            throw new ddl_exception('ddlunknownerror', null, 'Some DBs do not support key renaming (MySQL, PostgreSQL, MsSQL). Rename skipped');
        }

        $this->execute_sql_arr($sqlarr, array($xmldb_table->getName()));
    }

    
    public function add_index($xmldb_table, $xmldb_intex) {
        if (!$this->table_exists($xmldb_table)) {
            throw new ddl_table_missing_exception($xmldb_table->getName());
        }

                if ($this->index_exists($xmldb_table, $xmldb_intex)) {
            throw new ddl_exception('ddlunknownerror', null,
                      'Index ' . $xmldb_table->getName() . '->' . $xmldb_intex->getName() .
                      ' already exists. Create skipped');
        }

        if (!$sqlarr = $this->generator->getAddIndexSQL($xmldb_table, $xmldb_intex)) {
            throw new ddl_exception('ddlunknownerror', null, 'add_index sql not generated');
        }

        $this->execute_sql_arr($sqlarr, array($xmldb_table->getName()));
    }

    
    public function drop_index($xmldb_table, $xmldb_intex) {
        if (!$this->table_exists($xmldb_table)) {
            throw new ddl_table_missing_exception($xmldb_table->getName());
        }

                if (!$this->index_exists($xmldb_table, $xmldb_intex)) {
            throw new ddl_exception('ddlunknownerror', null,
                      'Index ' . $xmldb_table->getName() . '->' . $xmldb_intex->getName() .
                      ' does not exist. Drop skipped');
        }

        if (!$sqlarr = $this->generator->getDropIndexSQL($xmldb_table, $xmldb_intex)) {
            throw new ddl_exception('ddlunknownerror', null, 'drop_index sql not generated');
        }

        $this->execute_sql_arr($sqlarr, array($xmldb_table->getName()));
    }

    
    public function rename_index($xmldb_table, $xmldb_intex, $newname) {
        debugging('rename_index() is one experimental feature. You must not use it in production!', DEBUG_DEVELOPER);

                if (!$newname) {
            throw new ddl_exception('ddlunknownerror', null, 'newname can not be empty');
        }

                if (!$this->index_exists($xmldb_table, $xmldb_intex)) {
            throw new ddl_exception('ddlunknownerror', null,
                      'Index ' . $xmldb_table->getName() . '->' . $xmldb_intex->getName() .
                      ' does not exist. Rename skipped');
        }

        if (!$sqlarr = $this->generator->getRenameIndexSQL($xmldb_table, $xmldb_intex, $newname)) {
            throw new ddl_exception('ddlunknownerror', null, 'Some DBs do not support index renaming (MySQL). Rename skipped');
        }

        $this->execute_sql_arr($sqlarr, array($xmldb_table->getName()));
    }

    
    public function get_install_xml_schema() {
        global $CFG;
        require_once($CFG->libdir.'/adminlib.php');

        $schema = new xmldb_structure('export');
        $schema->setVersion($CFG->version);
        $dbdirs = get_db_directories();
        foreach ($dbdirs as $dbdir) {
            $xmldb_file = new xmldb_file($dbdir.'/install.xml');
            if (!$xmldb_file->fileExists() or !$xmldb_file->loadXMLStructure()) {
                continue;
            }
            $structure = $xmldb_file->getStructure();
            $tables = $structure->getTables();
            foreach ($tables as $table) {
                $table->setPrevious(null);
                $table->setNext(null);
                $schema->addTable($table);
            }
        }
        return $schema;
    }

    
    public function check_database_schema(xmldb_structure $schema, array $options = null) {
        $alloptions = array(
            'extratables' => true,
            'missingtables' => true,
            'extracolumns' => true,
            'missingcolumns' => true,
            'changedcolumns' => true,
        );

        $typesmap = array(
            'I' => XMLDB_TYPE_INTEGER,
            'R' => XMLDB_TYPE_INTEGER,
            'N' => XMLDB_TYPE_NUMBER,
            'F' => XMLDB_TYPE_NUMBER,             'C' => XMLDB_TYPE_CHAR,
            'X' => XMLDB_TYPE_TEXT,
            'B' => XMLDB_TYPE_BINARY,
            'T' => XMLDB_TYPE_TIMESTAMP,
            'D' => XMLDB_TYPE_DATETIME,
        );

        $options = (array)$options;
        $options = array_merge($alloptions, $options);

                        $errors = array();

        
        $dbtables = $this->mdb->get_tables(false);
        
        $tables = $schema->getTables();

        foreach ($tables as $table) {
            $tablename = $table->getName();

            if ($options['missingtables']) {
                                if (empty($dbtables[$tablename])) {
                    $errors[$tablename][] = "table is missing";
                    continue;
                }
            }

            
            $dbfields = $this->mdb->get_columns($tablename, false);
            
            $fields = $table->getFields();

            foreach ($fields as $field) {
                $fieldname = $field->getName();
                if (empty($dbfields[$fieldname])) {
                    if ($options['missingcolumns']) {
                                                $errors[$tablename][] = "column '$fieldname' is missing";
                    }
                } else if ($options['changedcolumns']) {
                    $dbfield = $dbfields[$fieldname];

                    if (!isset($typesmap[$dbfield->meta_type])) {
                        $errors[$tablename][] = "column '$fieldname' has unsupported type '$dbfield->meta_type'";
                    } else {
                        $dbtype = $typesmap[$dbfield->meta_type];
                        $type = $field->getType();
                        if ($type == XMLDB_TYPE_FLOAT) {
                            $type = XMLDB_TYPE_NUMBER;
                        }
                        if ($type != $dbtype) {
                            if ($expected = array_search($type, $typesmap)) {
                                $errors[$tablename][] = "column '$fieldname' has incorrect type '$dbfield->meta_type', expected '$expected'";
                            } else {
                                $errors[$tablename][] = "column '$fieldname' has incorrect type '$dbfield->meta_type'";
                            }
                        } else {
                            if ($field->getNotNull() != $dbfield->not_null) {
                                if ($field->getNotNull()) {
                                    $errors[$tablename][] = "column '$fieldname' should be NOT NULL ($dbfield->meta_type)";
                                } else {
                                    $errors[$tablename][] = "column '$fieldname' should allow NULL ($dbfield->meta_type)";
                                }
                            }
                            if ($dbtype == XMLDB_TYPE_TEXT) {
                                
                            } else if ($dbtype == XMLDB_TYPE_NUMBER) {
                                if ($field->getType() == XMLDB_TYPE_FLOAT) {
                                    
                                } else if ($field->getLength() != $dbfield->max_length or $field->getDecimals() != $dbfield->scale) {
                                    $size = "({$field->getLength()},{$field->getDecimals()})";
                                    $dbsize = "($dbfield->max_length,$dbfield->scale)";
                                    $errors[$tablename][] = "column '$fieldname' size is $dbsize, expected $size ($dbfield->meta_type)";
                                }

                            } else if ($dbtype == XMLDB_TYPE_CHAR) {
                                                                if ($field->getLength() != $dbfield->max_length) {
                                    $errors[$tablename][] = "column '$fieldname' length is $dbfield->max_length, expected {$field->getLength()} ($dbfield->meta_type)";
                                }

                            } else if ($dbtype == XMLDB_TYPE_INTEGER) {
                                                                $length = $field->getLength();
                                if ($length > 18) {
                                                                        $length = 18;
                                }
                                if ($length > $dbfield->max_length) {
                                    $errors[$tablename][] = "column '$fieldname' length is $dbfield->max_length, expected at least {$field->getLength()} ($dbfield->meta_type)";
                                }

                            } else if ($dbtype == XMLDB_TYPE_BINARY) {
                                                                continue;

                            } else if ($dbtype == XMLDB_TYPE_TIMESTAMP) {
                                $errors[$tablename][] = "column '$fieldname' is a timestamp, this type is not supported ($dbfield->meta_type)";
                                continue;

                            } else if ($dbtype == XMLDB_TYPE_DATETIME) {
                                $errors[$tablename][] = "column '$fieldname' is a datetime, this type is not supported ($dbfield->meta_type)";
                                continue;

                            } else {
                                                                $errors[$tablename][] = "column '$fieldname' has unknown type ($dbfield->meta_type)";
                                continue;
                            }

                                                        if ($field->getDefault() != $dbfield->default_value) {
                                $default = is_null($field->getDefault()) ? 'NULL' : $field->getDefault();
                                $dbdefault = is_null($dbfield->default_value) ? 'NULL' : $dbfield->default_value;
                                $errors[$tablename][] = "column '$fieldname' has default '$dbdefault', expected '$default' ($dbfield->meta_type)";
                            }
                        }
                    }
                }
                unset($dbfields[$fieldname]);
            }

                        foreach ($dbfields as $fieldname => $dbfield) {
                if ($options['extracolumns']) {
                    $errors[$tablename][] = "column '$fieldname' is not expected ($dbfield->meta_type)";
                }
            }
            unset($dbtables[$tablename]);
        }

        if ($options['extratables']) {
                                    if ($this->generator->prefix !== '') {
                foreach ($dbtables as $tablename => $unused) {
                    if (strpos($tablename, 'pma_') === 0) {
                                                continue;
                    }
                    if (strpos($tablename, 'test') === 0) {
                                                                        $errors[$tablename][] = "table is not expected (it may be a leftover after Simpletest unit tests)";
                    } else {
                        $errors[$tablename][] = "table is not expected";
                    }
                }
            }
        }

        return $errors;
    }
}
