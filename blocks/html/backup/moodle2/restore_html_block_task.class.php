<?php




class restore_html_block_task extends restore_block_task {

    protected function define_my_settings() {
    }

    protected function define_my_steps() {
    }

    public function get_fileareas() {
        return array('content');
    }

    public function get_configdata_encoded_attributes() {
        return array('text');     }

    static public function define_decode_contents() {

        $contents = array();

        $contents[] = new restore_html_block_decode_content('block_instances', 'configdata', 'block_instance');

        return $contents;
    }

    static public function define_decode_rules() {
        return array();
    }
}


class restore_html_block_decode_content extends restore_decode_content {

    protected $configdata; 
    protected function get_iterator() {
        global $DB;

                $fieldslist = 't.' . implode(', t.', $this->fields);
        $sql = "SELECT t.id, $fieldslist
                  FROM {" . $this->tablename . "} t
                  JOIN {backup_ids_temp} b ON b.newitemid = t.id
                 WHERE b.backupid = ?
                   AND b.itemname = ?
                   AND t.blockname = 'html'";
        $params = array($this->restoreid, $this->mapping);
        return ($DB->get_recordset_sql($sql, $params));
    }

    protected function preprocess_field($field) {
        $this->configdata = unserialize(base64_decode($field));
        return isset($this->configdata->text) ? $this->configdata->text : '';
    }

    protected function postprocess_field($field) {
        $this->configdata->text = $field;
        return base64_encode(serialize($this->configdata));
    }
}
