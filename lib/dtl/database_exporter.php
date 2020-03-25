<?php



defined('MOODLE_INTERNAL') || die();


abstract class database_exporter {
    
    protected $mdb;
    
    protected $manager;
    
    protected $schema;
    
    protected $check_schema;

    
    public function __construct(moodle_database $mdb, $check_schema=true) {
        $this->mdb          = $mdb;
        $this->manager      = $mdb->get_manager();
        $this->schema       = $this->manager->get_install_xml_schema();
        $this->check_schema = $check_schema;
    }

    
    public abstract function begin_database_export($version, $release, $timestamp, $description);

    
    public abstract function begin_table_export(xmldb_table $table);

    
    public abstract function finish_table_export(xmldb_table $table);

    
    public abstract function finish_database_export();

    
    public abstract function export_table_data(xmldb_table $table, $data);

    
    public function export_database($description=null) {
        global $CFG;

        $options = array('changedcolumns' => false);         if ($this->check_schema and $errors = $this->manager->check_database_schema($this->schema, $options)) {
            $details = '';
            foreach ($errors as $table=>$items) {
                $details .= '<div>'.get_string('tablex', 'dbtransfer', $table);
                $details .= '<ul>';
                foreach ($items as $item) {
                    $details .= '<li>'.$item.'</li>';
                }
                $details .= '</ul></div>';
            }
            throw new dbtransfer_exception('exportschemaexception', $details);
        }
        $tables = $this->schema->getTables();
        $this->begin_database_export($CFG->version, $CFG->release, date('c'), $description);
        foreach ($tables as $table) {
            $rs = $this->mdb->export_table_recordset($table->getName());
            if (!$rs) {
                throw new ddl_table_missing_exception($table->getName());
            }
            $this->begin_table_export($table);
            foreach ($rs as $row) {
                $this->export_table_data($table, $row);
            }
            $this->finish_table_export($table);
            $rs->close();
        }
        $this->finish_database_export();
    }
}
