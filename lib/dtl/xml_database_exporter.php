<?php



defined('MOODLE_INTERNAL') || die();


abstract class xml_database_exporter extends database_exporter {
    
    protected abstract function output($text);

    
    public function begin_database_export($version, $release, $timestamp, $description) {
        $this->output('<?xml version="1.0" encoding="utf-8"?>');
                $this->output('<moodle_database version="'.$version.'" release="'.$release.'" timestamp="'.$timestamp.'"'.(empty ($description) ? '' : ' comment="'.htmlspecialchars($description, ENT_QUOTES, 'UTF-8').'"').'>');
    }

    
    public function begin_table_export(xmldb_table $table) {
        $this->output('<table name="'.$table->getName().'" schemaHash="'.$table->getHash().'">');
    }

    
    public function finish_table_export(xmldb_table $table) {
        $this->output('</table>');
    }

    
    public function finish_database_export() {
        $this->output('</moodle_database>');
    }

    
    public function export_table_data(xmldb_table $table, $data) {
        $this->output('<record>');
        foreach ($data as $key => $value) {
            if (is_null($value)) {
                $this->output('<field name="'.$key.'" value="null" />');
            } else {
                $this->output('<field name="'.$key.'">'.htmlspecialchars($value, ENT_NOQUOTES, 'UTF-8').'</field>');
            }
        }
        $this->output('</record>');
    }
}
