<?php



defined('MOODLE_INTERNAL') || die();


class database_importer {
    
    protected $mdb;
    
    protected $manager;
    
    protected $schema;
    
    protected $check_schema;
    
    protected $transactionmode = 'allinone';
    
    protected $transaction;

    
    public function __construct(moodle_database $mdb, $check_schema=true) {
        $this->mdb          = $mdb;
        $this->manager      = $mdb->get_manager();
        $this->schema       = $this->manager->get_install_xml_schema();
        $this->check_schema = $check_schema;
    }

    
    public function set_transaction_mode($mode) {
        if (!in_array($mode, array('pertable', 'allinone', 'none'))) {
            throw new coding_exception('Unknown transaction mode', $mode);
        }
        $this->transactionmode = $mode;
    }

    
    public function begin_database_import($version, $timestamp) {
        global $CFG;

        if (!$this->mdb->get_tables()) {
                        $this->manager->install_from_xmldb_structure($this->schema);
        }

        if (round($version, 2) !== round($CFG->version, 2)) {             $a = (object)array('schemaver'=>$version, 'currentver'=>$CFG->version);
            throw new dbtransfer_exception('importversionmismatchexception', $a);
        }

        $options = array('changedcolumns' => false);         if ($this->check_schema and $errors = $this->manager->check_database_schema($this->schema, $options)) {
            $details = '';
            foreach ($errors as $table=>$items) {
                $details .= '<div>'.get_string('table').' '.$table.':';
                $details .= '<ul>';
                foreach ($items as $item) {
                    $details .= '<li>'.$item.'</li>';
                }
                $details .= '</ul></div>';
            }
            throw new dbtransfer_exception('importschemaexception', $details);
        }
        if ($this->transactionmode == 'allinone') {
            $this->transaction = $this->mdb->start_delegated_transaction();
        }
    }

    
    public function begin_table_import($tablename, $schemaHash) {
        if ($this->transactionmode == 'pertable') {
            $this->transaction = $this->mdb->start_delegated_transaction();
        }
        if (!$table = $this->schema->getTable($tablename)) {
            throw new dbtransfer_exception('unknowntableexception', $tablename);
        }
        if ($schemaHash != $table->getHash()) {
            throw new dbtransfer_exception('differenttableexception', $tablename);
        }
                if (!$this->manager->table_exists($table)) {
            throw new ddl_table_missing_exception($tablename);
        }
        $this->mdb->delete_records($tablename);
    }

    
    public function finish_table_import($tablename) {
        $table  = $this->schema->getTable($tablename);
        $fields = $table->getFields();
        foreach ($fields as $field) {
            if ($field->getSequence()) {
                $this->manager->reset_sequence($tablename);
                return;
            }
        }
        if ($this->transactionmode == 'pertable') {
            $this->transaction->allow_commit();
        }
    }

    
    public function finish_database_import() {
        if ($this->transactionmode == 'allinone') {
            $this->transaction->allow_commit();
        }
    }

    
    public function import_table_data($tablename, $data) {
        $this->mdb->import_record($tablename, $data);
    }

    
    public function import_database() {
            }
}
