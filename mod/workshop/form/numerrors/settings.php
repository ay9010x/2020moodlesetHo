<?php




defined('MOODLE_INTERNAL') || die();

$settings->add(new admin_setting_configtext('workshopform_numerrors/grade0', get_string('grade0', 'workshopform_numerrors'),
                    get_string('configgrade0', 'workshopform_numerrors'),
                    get_string('grade0default', 'workshopform_numerrors'), $paramtype=PARAM_TEXT, $size=15));

$settings->add(new admin_setting_configtext('workshopform_numerrors/grade1', get_string('grade1', 'workshopform_numerrors'),
                    get_string('configgrade1', 'workshopform_numerrors'),
                    get_string('grade1default', 'workshopform_numerrors'), $paramtype=PARAM_TEXT, $size=15));
