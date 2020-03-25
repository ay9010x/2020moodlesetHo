<?php



defined('MOODLE_INTERNAL') || die();

class database_mover extends database_exporter {
    
    protected $importer;
    
    protected $feedback;

    
    public function __construct(moodle_database $mdb_source, moodle_database $mdb_target,
            $check_schema = true, progress_trace $feedback = null) {
        if (empty($feedback)) {
            $this->feedback = new null_progress_trace();
        } else {
            $this->feedback = $feedback;
        }
        if ($check_schema) {
            $this->feedback->output(get_string('checkingsourcetables', 'core_dbtransfer'));
        }
        parent::__construct($mdb_source, $check_schema);
        $this->feedback->output(get_string('creatingtargettables', 'core_dbtransfer'));
        $this->importer = new database_importer($mdb_target, $check_schema);
    }

    
    public function set_transaction_mode($mode) {
        $this->importer->set_transaction_mode($mode);
    }

    
    public function begin_database_export($version, $release, $timestamp, $description) {
        $this->feedback->output(get_string('copyingtables', 'core_dbtransfer'));
        $this->importer->begin_database_import($version, $timestamp, $description);
    }

    
    public function begin_table_export(xmldb_table $table) {
        $this->feedback->output(get_string('copyingtable', 'core_dbtransfer', $table->getName()), 1);
        $this->importer->begin_table_import($table->getName(), $table->getHash());
    }

    
    public function export_table_data(xmldb_table $table, $data) {
        $this->importer->import_table_data($table->getName(), $data);
    }

    
    public function finish_table_export(xmldb_table $table) {
        $this->feedback->output(get_string('done', 'core_dbtransfer', $table->getName()), 2);
        $this->importer->finish_table_import($table->getName());
    }

    
    public function finish_database_export() {
        $this->importer->finish_database_import();
    }
}
