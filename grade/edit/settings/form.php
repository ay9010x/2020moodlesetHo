<?php



if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    }

require_once($CFG->libdir.'/formslib.php');


class course_settings_form extends moodleform {

    function definition() {
        global $USER, $CFG;

        $mform =& $this->_form;

        $systemcontext = context_system::instance();
        $can_view_admin_links = false;
        if (has_capability('moodle/grade:manage', $systemcontext)) {
            $can_view_admin_links = true;
        }

                $strchangedefaults = get_string('changedefaults', 'grades');
        $mform->addElement('header', 'general', get_string('generalsettings', 'grades'));
        if ($can_view_admin_links) {
            $link = '<a href="' . $CFG->wwwroot.'/'.$CFG->admin.'/settings.php?section=gradessettings">' . $strchangedefaults . '</a>';
            $mform->addElement('static', 'generalsettingslink', null, $link);
        }
        $options = array(-1                                      => get_string('default', 'grades'),
                         GRADE_REPORT_AGGREGATION_POSITION_FIRST => get_string('positionfirst', 'grades'),
                         GRADE_REPORT_AGGREGATION_POSITION_LAST  => get_string('positionlast', 'grades'));
        $default_gradedisplaytype = $CFG->grade_aggregationposition;
        foreach ($options as $key=>$option) {
            if ($key == $default_gradedisplaytype) {
                $options[-1] = get_string('defaultprev', 'grades', $option);
                break;
            }
        }
        $mform->addElement('select', 'aggregationposition', get_string('aggregationposition', 'grades'), $options);
        $mform->addHelpButton('aggregationposition', 'aggregationposition', 'grades');

        if ($CFG->grade_minmaxtouse == GRADE_MIN_MAX_FROM_GRADE_ITEM) {
            $default = get_string('gradeitemminmax', 'grades');
        } else if ($CFG->grade_minmaxtouse == GRADE_MIN_MAX_FROM_GRADE_GRADE) {
            $default = get_string('gradegrademinmax', 'grades');
        } else {
            throw new coding_exception('Invalid $CFG->grade_minmaxtouse value.');
        }

        $options = array(
            -1 => get_string('defaultprev', 'grades', $default),
            GRADE_MIN_MAX_FROM_GRADE_ITEM => get_string('gradeitemminmax', 'grades'),
            GRADE_MIN_MAX_FROM_GRADE_GRADE => get_string('gradegrademinmax', 'grades')
        );
        $mform->addElement('select', 'minmaxtouse', get_string('minmaxtouse', 'grades'), $options);
        $mform->addHelpButton('minmaxtouse', 'minmaxtouse', 'grades');

                $mform->addElement('header', 'grade_item_settings', get_string('gradeitemsettings', 'grades'));
        $mform->setExpanded('grade_item_settings');
        if ($can_view_admin_links) {
            $link = '<a href="' . $CFG->wwwroot.'/'.$CFG->admin.'/settings.php?section=gradeitemsettings">' . $strchangedefaults . '</a>';
            $mform->addElement('static', 'gradeitemsettingslink', null, $link);
        }

        $options = array(-1                            => get_string('default', 'grades'),
                         GRADE_DISPLAY_TYPE_REAL       => get_string('real', 'grades'),
                         GRADE_DISPLAY_TYPE_REAL_PERCENTAGE => get_string('realpercentage', 'grades'),
                         GRADE_DISPLAY_TYPE_REAL_LETTER => get_string('realletter', 'grades'),
                         GRADE_DISPLAY_TYPE_PERCENTAGE => get_string('percentage', 'grades'),
                         GRADE_DISPLAY_TYPE_PERCENTAGE_REAL => get_string('percentagereal', 'grades'),
                         GRADE_DISPLAY_TYPE_PERCENTAGE_LETTER => get_string('percentageletter', 'grades'),
                         GRADE_DISPLAY_TYPE_LETTER     => get_string('letter', 'grades'),
                         GRADE_DISPLAY_TYPE_LETTER_REAL => get_string('letterreal', 'grades'),
                         GRADE_DISPLAY_TYPE_LETTER_PERCENTAGE => get_string('letterpercentage', 'grades'));

        $default_gradedisplaytype = $CFG->grade_displaytype;
        foreach ($options as $key=>$option) {
            if ($key == $default_gradedisplaytype) {
                $options[-1] = get_string('defaultprev', 'grades', $option);
                break;
            }
        }
        $mform->addElement('select', 'displaytype', get_string('gradedisplaytype', 'grades'), $options);
        $mform->addHelpButton('displaytype', 'gradedisplaytype', 'grades');
        $mform->setDefault('displaytype', -1);

        $options = array(-1=> get_string('defaultprev', 'grades', $CFG->grade_decimalpoints), 0=>0, 1=>1, 2=>2, 3=>3, 4=>4, 5=>5);
        $mform->addElement('select', 'decimalpoints', get_string('decimalpoints', 'grades'), $options);
        $mform->addHelpButton('decimalpoints', 'decimalpoints', 'grades');

        $types = array('report', 'export', 'import');

        foreach($types as $type) {
            foreach (core_component::get_plugin_list('grade'.$type) as $plugin => $plugindir) {
                             if (file_exists($plugindir.'/lib.php')) {
                    require_once($plugindir.'/lib.php');
                    $functionname = 'grade_'.$type.'_'.$plugin.'_settings_definition';
                    if (function_exists($functionname)) {
                        $mform->addElement('header', 'grade_'.$type.$plugin, get_string('pluginname', 'grade'.$type.'_'.$plugin, NULL));
                        $mform->setExpanded('grade_'.$type.$plugin);
                        if ($can_view_admin_links) {
                            $link = '<a href="' . $CFG->wwwroot.'/'.$CFG->admin.'/settings.php?section=gradereport' . $plugin . '">' . $strchangedefaults . '</a>';
                            $mform->addElement('static', 'gradeitemsettingslink', null, $link);
                        }
                        $functionname($mform);
                    }
                }
            }
        }

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons();
    }
}

