<?php



defined('MOODLE_INTERNAL') || die();


class restore_activity_results_block_task extends restore_block_task {

    
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
            if (!empty($config->activityparentid)) {
                                if ($mapping = restore_dbops::get_backup_ids_record($this->get_restoreid(),
                    $config->activityparent, $config->activityparentid)) {

                                        $config->activityparentid = $mapping->newitemid;

                                        $info = $DB->get_record('grade_items',
                            array('iteminstance' => $config->activityparentid, 'itemmodule' => $config->activityparent));

                                        $config->activitygradeitemid = $info->id;

                                        $configdata = base64_encode(serialize($config));
                    $DB->set_field('block_instances', 'configdata', $configdata, array('id' => $blockid));
                }
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
