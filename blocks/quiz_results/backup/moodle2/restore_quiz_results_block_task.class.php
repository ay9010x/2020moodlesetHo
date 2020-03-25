<?php



defined('MOODLE_INTERNAL') || die();


class restore_quiz_results_block_task extends restore_block_task {

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

                $configdata = $DB->get_field('block_instances', 'configdata', array('id' => $blockid));
        $newconfigdata = '';

                if (!empty($configdata)) {

            $config = unserialize(base64_decode($configdata));
            $config->activityparent = 'quiz';
            $config->activityparentid = 0;
            $config->gradeformat = isset($config->gradeformat) ? $config->gradeformat : 1;

            if (!empty($config->quizid)
                    && $quizmap = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'quiz', $config->quizid)) {
                $config->activityparentid = $quizmap->newitemid;
            }

                        if ($config->gradeformat == 1) {
                                $config->decimalpoints = 0;
            } else {
                                $config->decimalpoints = $DB->get_field('quiz', 'decimalpoints', array('id' => $config->activityparentid));
            }

                        $info = $DB->get_record('grade_items',
                    array('iteminstance' => $config->activityparentid, 'itemmodule' => $config->activityparent));
            $config->activitygradeitemid = 0;
            if ($info) {
                $config->activitygradeitemid = $info->id;
            }

            unset($config->quizid);
            $newconfigdata = base64_encode(serialize($config));
        }

                $DB->set_field('block_instances', 'configdata', $newconfigdata, array('id' => $blockid));
        $DB->set_field('block_instances', 'blockname', 'activity_results', array('id' => $blockid));
    }

    static public function define_decode_contents() {
        return array();
    }

    static public function define_decode_rules() {
        return array();
    }
}
