<?php




function xmldb_block_quiz_results_upgrade($oldversion, $block) {
    global $DB, $CFG;

    if ($oldversion < 2015022200) {
                if (is_dir($CFG->dirroot . '/blocks/activity_results')) {

                        $records = $DB->get_records('block_instances', array('blockname' => 'quiz_results'));
            foreach ($records as $record) {
                $configdata = '';

                                if (!empty($record->configdata)) {

                    $config = unserialize(base64_decode($record->configdata));
                    $config->activityparent = 'quiz';
                    $config->activityparentid = isset($config->quizid) ? $config->quizid : 0;
                    $config->gradeformat = isset($config->gradeformat) ? $config->gradeformat : 1;

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
                    $configdata = base64_encode(serialize($config));
                }

                                $record->configdata = $configdata;
                $record->blockname = 'activity_results';
                $DB->update_record('block_instances', $record);
            }

                        if ($block = $DB->get_record("block", array("name" => "quiz_results"))) {
                $DB->set_field("block", "visible", "0", array("id" => $block->id));
            }

        }
        upgrade_block_savepoint(true, 2015022200, 'quiz_results');
    }

        
        
        
        
    return true;
}