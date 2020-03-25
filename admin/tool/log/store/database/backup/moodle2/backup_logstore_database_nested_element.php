<?php



defined('MOODLE_INTERNAL') || die();


class backup_logstore_database_nested_element extends backup_nested_element {

    
    protected $sourcedb;

    
    public function __construct($name, $attributes = null, $finalelements = null) {
        global $DB;

        parent::__construct($name, $attributes, $finalelements);
        $this->sourcedb = $DB;
    }

    
    protected function get_iterator($processor) {
        if ($this->get_source_table() !== null) {             return $this->get_source_db()->get_recordset(
                $this->get_source_table(),
                backup_structure_dbops::convert_params_to_values($this->procparams, $processor),
                $this->get_source_table_sortby()
            );

        } else if ($this->get_source_sql() !== null) {             return $this->get_source_db()->get_recordset_sql(
                $this->get_source_sql(),
                backup_structure_dbops::convert_params_to_values($this->procparams, $processor)
            );
        }

        return parent::get_iterator($processor);
    }

    
    public function set_source_db($db) {
        $this->sourcedb = $db;
    }

    
    public function get_source_db() {
        return $this->sourcedb;
    }

}
