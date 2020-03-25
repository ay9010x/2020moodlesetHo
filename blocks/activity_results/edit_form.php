<?php



defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/lib/grade/constants.php');


class block_activity_results_edit_form extends block_edit_form {
    
    protected function specific_definition($mform) {
        global $DB;

                $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

                $sql = 'SELECT id, itemname FROM {grade_items} WHERE courseid = ? and itemtype = ? and (gradetype = ? or gradetype = ?)';
        $params = array($this->page->course->id, 'mod', GRADE_TYPE_VALUE, GRADE_TYPE_SCALE);
        $activities = $DB->get_records_sql_menu($sql, $params);

        if (empty($activities)) {
            $mform->addElement('static', 'noactivitieswarning', get_string('config_select_activity', 'block_activity_results'),
                    get_string('config_no_activities_in_course', 'block_activity_results'));
        } else {
            foreach ($activities as $id => $name) {
                $activities[$id] = strip_tags(format_string($name));
            }
            $mform->addElement('select', 'config_activitygradeitemid',
                    get_string('config_select_activity', 'block_activity_results'), $activities);
            $mform->setDefault('config_activitygradeitemid', $this->block->get_owning_activity()->id);
        }

        $mform->addElement('text', 'config_showbest',
                get_string('config_show_best', 'block_activity_results'), array('size' => 3));
        $mform->setDefault('config_showbest', 3);
        $mform->setType('config_showbest', PARAM_INT);

        $mform->addElement('text', 'config_showworst',
                get_string('config_show_worst', 'block_activity_results'), array('size' => 3));
        $mform->setDefault('config_showworst', 0);
        $mform->setType('config_showworst', PARAM_INT);

        $mform->addElement('selectyesno', 'config_usegroups', get_string('config_use_groups', 'block_activity_results'));

        $nameoptions = array(
            B_ACTIVITYRESULTS_NAME_FORMAT_FULL => get_string('config_names_full', 'block_activity_results'),
            B_ACTIVITYRESULTS_NAME_FORMAT_ID => get_string('config_names_id', 'block_activity_results'),
            B_ACTIVITYRESULTS_NAME_FORMAT_ANON => get_string('config_names_anon', 'block_activity_results')
        );
        $mform->addElement('select', 'config_nameformat',
                get_string('config_name_format', 'block_activity_results'), $nameoptions);
        $mform->setDefault('config_nameformat', B_ACTIVITYRESULTS_NAME_FORMAT_FULL);

        $gradeeoptions = array(
            B_ACTIVITYRESULTS_GRADE_FORMAT_PCT => get_string('config_format_percentage', 'block_activity_results'),
            B_ACTIVITYRESULTS_GRADE_FORMAT_FRA => get_string('config_format_fraction', 'block_activity_results'),
            B_ACTIVITYRESULTS_GRADE_FORMAT_ABS => get_string('config_format_absolute', 'block_activity_results')
        );
        $mform->addElement('select', 'config_gradeformat',
                get_string('config_grade_format', 'block_activity_results'), $gradeeoptions);
        $mform->setDefault('config_gradeformat', B_ACTIVITYRESULTS_GRADE_FORMAT_PCT);

        $options = array();
        for ($i = 0; $i <= 5; $i++) {
            $options[$i] = $i;
        }
        $mform->addElement('select', 'config_decimalpoints', get_string('config_decimalplaces', 'block_activity_results'),
                $options);
        $mform->setDefault('config_decimalpoints', 2);
        $mform->setType('config_decimalpoints', PARAM_INT);
    }
}