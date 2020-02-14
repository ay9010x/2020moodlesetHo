<?php



defined('MOODLE_INTERNAL') || die();


abstract class xml_database_importer extends database_importer {
    protected $current_table;
    protected $current_row;
    protected $current_field;
    protected $current_data;
    protected $current_data_is_null;

    
    protected function get_parser() {
        $parser = xml_parser_create();
        xml_set_object($parser, $this);
        xml_set_element_handler($parser, 'tag_open', 'tag_close');
        xml_set_character_data_handler($parser, 'cdata');
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
        return $parser;
    }

    
    protected function tag_open($parser, $tag, $attributes) {
        switch ($tag) {
            case 'moodle_database' :
                if (empty($attributes['version']) || empty($attributes['timestamp'])) {
                    throw new dbtransfer_exception('malformedxmlexception');
                }
                $this->begin_database_import($attributes['version'], $attributes['timestamp']);
                break;
            case 'table' :
                if (isset($this->current_table)) {
                    throw new dbtransfer_exception('malformedxmlexception');
                }
                if (empty($attributes['name']) || empty($attributes['schemaHash'])) {
                    throw new dbtransfer_exception('malformedxmlexception');
                }
                $this->current_table = $attributes['name'];
                $this->begin_table_import($this->current_table, $attributes['schemaHash']);
                break;
            case 'record' :
                if (isset($this->current_row) || !isset($this->current_table)) {
                    throw new dbtransfer_exception('malformedxmlexception');
                }
                $this->current_row = new stdClass();
                break;
            case 'field' :
                if (isset($this->current_field) || !isset($this->current_row)) {
                    throw new dbtransfer_exception('malformedxmlexception');
                }
                $this->current_field = $attributes['name'];
                $this->current_data = '';
                if (isset($attributes['value']) and $attributes['value'] === 'null') {
                    $this->current_data_is_null = true;
                } else {
                    $this->current_data_is_null = false;
                }
                break;
            default :
                throw new dbtransfer_exception('malformedxmlexception');
        }
    }

    
    protected function tag_close($parser, $tag) {
        switch ($tag) {
            case 'moodle_database' :
                $this->finish_database_import();
                break;

            case 'table' :
                $this->finish_table_import($this->current_table);
                $this->current_table = null;
                break;

            case 'record' :
                $this->import_table_data($this->current_table, $this->current_row);
                $this->current_row = null;
                break;

            case 'field' :
                $field = $this->current_field;
                if ($this->current_data_is_null) {
                    $this->current_row->$field = null;
                } else {
                    $this->current_row->$field = $this->current_data;
                }
                $this->current_field        = null;
                $this->current_data         = null;
                $this->current_data_is_null = null;
                break;

            default :
                throw new dbtransfer_exception('malformedxmlexception');
        }
    }

    
    protected function cdata($parser, $data) {
        if (isset($this->current_field)) {
            $this->current_data .= $data;
        }
    }
}
