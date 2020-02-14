<?php



if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    }

require_once($CFG->libdir.'/formslib.php');


class grader_report_preferences_form extends moodleform {

    function definition() {
        global $USER, $CFG;

        $mform    =& $this->_form;
        $course   = $this->_customdata['course'];

        $context = context_course::instance($course->id);

        $canviewhidden = has_capability('moodle/grade:viewhidden', $context);

        $checkbox_default = array(GRADE_REPORT_PREFERENCE_DEFAULT => '*default*', 0 => get_string('no'), 1 => get_string('yes'));

        $advanced = array();
        $preferences = array();

                if (has_capability('moodle/grade:manage', $context)) {

            $preferences['prefshow'] = array();

            $preferences['prefshow']['showcalculations'] = $checkbox_default;

            $preferences['prefshow']['showeyecons']       = $checkbox_default;
            if ($canviewhidden) {
                $preferences['prefshow']['showaverages']  = $checkbox_default;
            }
            $preferences['prefshow']['showlocks']         = $checkbox_default;

            $preferences['prefrows'] = array(
                        'rangesdisplaytype'      => array(GRADE_REPORT_PREFERENCE_DEFAULT => '*default*',
                                                          GRADE_REPORT_PREFERENCE_INHERIT => get_string('inherit', 'grades'),
                                                          GRADE_DISPLAY_TYPE_REAL => get_string('real', 'grades'),
                                                          GRADE_DISPLAY_TYPE_PERCENTAGE => get_string('percentage', 'grades'),
                                                          GRADE_DISPLAY_TYPE_LETTER => get_string('letter', 'grades')),
                        'rangesdecimalpoints'    => array(GRADE_REPORT_PREFERENCE_DEFAULT => '*default*',
                                                          GRADE_REPORT_PREFERENCE_INHERIT => get_string('inherit', 'grades'),
                                                          0=>0, 1=>1, 2=>2, 3=>3, 4=>4, 5=>5));
            $advanced = array_merge($advanced, array('rangesdisplaytype', 'rangesdecimalpoints'));

            if ($canviewhidden) {
                $preferences['prefrows']['averagesdisplaytype'] = array(GRADE_REPORT_PREFERENCE_DEFAULT => '*default*',
                                                                        GRADE_REPORT_PREFERENCE_INHERIT => get_string('inherit', 'grades'),
                                                                        GRADE_DISPLAY_TYPE_REAL => get_string('real', 'grades'),
                                                                        GRADE_DISPLAY_TYPE_PERCENTAGE => get_string('percentage', 'grades'),
                                                                        GRADE_DISPLAY_TYPE_LETTER => get_string('letter', 'grades'));
                $preferences['prefrows']['averagesdecimalpoints'] = array(GRADE_REPORT_PREFERENCE_DEFAULT => '*default*',
                                                                          GRADE_REPORT_PREFERENCE_INHERIT => get_string('inherit', 'grades'),
                                                                          0=>0, 1=>1, 2=>2, 3=>3, 4=>4, 5=>5);
                $preferences['prefrows']['meanselection']  = array(GRADE_REPORT_PREFERENCE_DEFAULT => '*default*',
                                                                   GRADE_REPORT_MEAN_ALL => get_string('meanall', 'grades'),
                                                                   GRADE_REPORT_MEAN_GRADED => get_string('meangraded', 'grades'));

                $advanced = array_merge($advanced, array('averagesdisplaytype', 'averagesdecimalpoints'));
            }
        }

                if (has_capability('moodle/grade:edit', $context)) {
            $preferences['prefgeneral']['quickgrading'] = $checkbox_default;
            $preferences['prefgeneral']['showquickfeedback'] = $checkbox_default;
        }

                if (has_capability('gradereport/grader:view', $context)) {
            $preferences['prefgeneral']['studentsperpage'] = 'text';
            if (has_capability('moodle/course:viewsuspendedusers', $context)) {
                $preferences['prefgeneral']['showonlyactiveenrol'] = $checkbox_default;
            }
            $preferences['prefgeneral']['aggregationposition'] = array(GRADE_REPORT_PREFERENCE_DEFAULT => '*default*',
                                                                       GRADE_REPORT_AGGREGATION_POSITION_FIRST => get_string('positionfirst', 'grades'),
                                                                       GRADE_REPORT_AGGREGATION_POSITION_LAST => get_string('positionlast', 'grades'));
            $preferences['prefgeneral']['enableajax'] = $checkbox_default;

            $preferences['prefshow']['showuserimage'] = $checkbox_default;
            $preferences['prefshow']['showactivityicons'] = $checkbox_default;
            $preferences['prefshow']['showranges'] = $checkbox_default;
            $preferences['prefshow']['showanalysisicon'] = $checkbox_default;

            if ($canviewhidden) {
                $preferences['prefrows']['shownumberofgrades'] = $checkbox_default;
            }

            $advanced = array_merge($advanced, array('aggregationposition'));
        }


        foreach ($preferences as $group => $prefs) {
            $mform->addElement('header', $group, get_string($group, 'grades'));
            $mform->setExpanded($group);

            foreach ($prefs as $pref => $type) {
                                if (preg_match('/([^[0-9]+)([0-9]+)/', $pref, $matches)) {
                    $lang_string = $matches[1];
                    $number = ' ' . $matches[2];
                } else {
                    $lang_string = $pref;
                    $number = null;
                }

                $full_pref  = 'grade_report_' . $pref;

                $pref_value = get_user_preferences($full_pref);

                $options = null;
                if (is_array($type)) {
                    $options = $type;
                    $type = 'select';
                                                            $course_value = null;
                    if (!empty($CFG->{$full_pref})) {
                        $course_value = grade_get_setting($course->id, $pref, $CFG->{$full_pref});
                    }

                    if ($pref == 'aggregationposition') {
                        if (!empty($options[$course_value])) {
                            $default = $options[$course_value];
                        } else {
                            $default = $options[$CFG->grade_aggregationposition];
                        }
                    } elseif (isset($options[$CFG->{$full_pref}])) {
                        $default = $options[$CFG->{$full_pref}];
                    } else {
                        $default = '';
                    }
                } else {
                    $default = $CFG->$full_pref;
                }

                                if (!is_null($options) AND isset($options[GRADE_REPORT_PREFERENCE_DEFAULT]) && $options[GRADE_REPORT_PREFERENCE_DEFAULT] == '*default*') {
                    $options[GRADE_REPORT_PREFERENCE_DEFAULT] = get_string('reportdefault', 'grades', $default);
                }

                $label = get_string($lang_string, 'grades') . $number;

                $mform->addElement($type, $full_pref, $label, $options);
                if ($lang_string != 'showuserimage') {
                    $mform->addHelpButton($full_pref, $lang_string, 'grades');
                }
                $mform->setDefault($full_pref, $pref_value);
                $mform->setType($full_pref, PARAM_ALPHANUM);
            }
        }

        foreach($advanced as $name) {
            $mform->setAdvanced('grade_report_'.$name);
        }

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $course->id);

        $this->add_action_buttons();
    }

    function validation($data, $files) {
        return parent::validation($data, $files);
    }
}

