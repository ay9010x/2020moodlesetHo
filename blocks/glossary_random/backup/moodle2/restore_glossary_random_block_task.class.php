<?php




class restore_glossary_random_block_task extends restore_block_task {

    protected function define_my_settings() {
    }

    protected function define_my_steps() {
    }

    public function get_fileareas() {
        return array();     }

    public function get_configdata_encoded_attributes() {
        return array();     }

    
    public function after_restore() {
        global $DB;

                $blockid = $this->get_blockid();

                if ($configdata = $DB->get_field('block_instances', 'configdata', array('id' => $blockid))) {
            $config = unserialize(base64_decode($configdata));
            if (!empty($config->glossary)) {
                if ($glossarymap = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'glossary', $config->glossary)) {
                                        $config->glossary = $glossarymap->newitemid;
                } else if ($this->is_samesite()) {
                                        $glossaryid = $DB->get_field_sql("SELECT id FROM {glossary} " .
                        "WHERE id = ? AND (course = ? OR globalglossary = 1)",
                        [$config->glossary, $this->get_courseid()]);
                    if (!$glossaryid) {
                        unset($config->glossary);
                    }
                } else {
                                        unset($config->glossary);
                }
                                unset($config->globalglossary);
                unset($config->courseid);
                                $configdata = base64_encode(serialize($config));
                $DB->set_field('block_instances', 'configdata', $configdata, array('id' => $blockid));
            }
        }
    }

    static public function define_decode_contents() {
        return array();
    }

    static public function define_decode_rules() {
        return array();
    }
}
