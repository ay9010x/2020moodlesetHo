<?php




class restore_tags_block_task extends restore_block_task {

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
            $changed = false;
            if (!empty($config->tagcoll) && $config->tagcoll > 1 && !$this->is_samesite()) {
                $config->tagcoll = 0;
                $changed = true;
            }
            if (!empty($config->ctx)) {
                if ($ctxmap = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'context', $config->ctx)) {
                    $config->ctx = $ctxmap->newitemid;
                } else {
                    $config->ctx = 0;
                }
                $changed = true;
            }
            if ($changed) {
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
