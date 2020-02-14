<?php



defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/enrol/imsenterprise/locallib.php');

    $settings->add(new admin_setting_heading('enrol_imsenterprise_settings', '',
        get_string('pluginname_desc', 'enrol_imsenterprise')));

        $settings->add(new admin_setting_heading('enrol_imsenterprise_basicsettings',
        get_string('basicsettings', 'enrol_imsenterprise'), ''));

    $settings->add(new admin_setting_configtext('enrol_imsenterprise/imsfilelocation',
        get_string('location', 'enrol_imsenterprise'), '', ''));

    $settings->add(new admin_setting_configtext('enrol_imsenterprise/logtolocation',
        get_string('logtolocation', 'enrol_imsenterprise'), '', ''));

    $settings->add(new admin_setting_configcheckbox('enrol_imsenterprise/mailadmins',
        get_string('mailadmins', 'enrol_imsenterprise'), '', 0));

        $settings->add(new admin_setting_heading('enrol_imsenterprise_usersettings',
        get_string('usersettings', 'enrol_imsenterprise'), ''));

    $settings->add(new admin_setting_configcheckbox('enrol_imsenterprise/createnewusers',
        get_string('createnewusers', 'enrol_imsenterprise'), get_string('createnewusers_desc', 'enrol_imsenterprise'), 0));

    $settings->add(new admin_setting_configcheckbox('enrol_imsenterprise/imsdeleteusers',
        get_string('deleteusers', 'enrol_imsenterprise'), get_string('deleteusers_desc', 'enrol_imsenterprise'), 0));

    $settings->add(new admin_setting_configcheckbox('enrol_imsenterprise/fixcaseusernames',
        get_string('fixcaseusernames', 'enrol_imsenterprise'), '', 0));

    $settings->add(new admin_setting_configcheckbox('enrol_imsenterprise/fixcasepersonalnames',
        get_string('fixcasepersonalnames', 'enrol_imsenterprise'), '', 0));

    $settings->add(new admin_setting_configcheckbox('enrol_imsenterprise/imssourcedidfallback',
        get_string('sourcedidfallback', 'enrol_imsenterprise'), get_string('sourcedidfallback_desc', 'enrol_imsenterprise'), 0));

    $settings->add(new admin_setting_heading('enrol_imsenterprise_usersettings_roles',
        get_string('roles', 'enrol_imsenterprise'), get_string('imsrolesdescription', 'enrol_imsenterprise')));

    if (!during_initial_install()) {
        $coursecontext = context_course::instance(SITEID);
        $assignableroles = get_assignable_roles($coursecontext);
        $assignableroles = array('0' => get_string('ignore', 'enrol_imsenterprise')) + $assignableroles;
        $imsroles = new imsenterprise_roles();
        foreach ($imsroles->get_imsroles() as $imsrolenum => $imsrolename) {
            $settings->add(new admin_setting_configselect('enrol_imsenterprise/imsrolemap'.$imsrolenum,
                format_string('"'.$imsrolename.'" ('.$imsrolenum.')'), '',
                (int)$imsroles->determine_default_rolemapping($imsrolenum), $assignableroles));
        }
    }

        $settings->add(new admin_setting_heading('enrol_imsenterprise_coursesettings',
        get_string('coursesettings', 'enrol_imsenterprise'), ''));

    $settings->add(new admin_setting_configtext('enrol_imsenterprise/truncatecoursecodes',
        get_string('truncatecoursecodes', 'enrol_imsenterprise'), get_string('truncatecoursecodes_desc', 'enrol_imsenterprise'),
        0, PARAM_INT, 2));

    $settings->add(new admin_setting_configcheckbox('enrol_imsenterprise/createnewcourses',
        get_string('createnewcourses', 'enrol_imsenterprise'), get_string('createnewcourses_desc', 'enrol_imsenterprise'), 0));

    $settings->add(new admin_setting_configcheckbox('enrol_imsenterprise/createnewcategories',
        get_string('createnewcategories', 'enrol_imsenterprise'), get_string('createnewcategories_desc', 'enrol_imsenterprise'),
        0));

    $settings->add(new admin_setting_configcheckbox('enrol_imsenterprise/imsunenrol',
        get_string('allowunenrol', 'enrol_imsenterprise'), get_string('allowunenrol_desc', 'enrol_imsenterprise'), 0));

    if (!during_initial_install()) {
        $imscourses = new imsenterprise_courses();
        foreach ($imscourses->get_courseattrs() as $courseattr) {

                        $assignablevalues = $imscourses->get_imsnames($courseattr);
            $name = get_string('setting' . $courseattr, 'enrol_imsenterprise');
            $description = get_string('setting' . $courseattr . 'description', 'enrol_imsenterprise');
            $defaultvalue = (string) $imscourses->determine_default_coursemapping($courseattr);
            $settings->add(new admin_setting_configselect('enrol_imsenterprise/imscoursemap' . $courseattr, $name,
                $description, $defaultvalue, $assignablevalues));
        }
    }

        $settings->add(new admin_setting_heading('enrol_imsenterprise_miscsettings',
        get_string('miscsettings', 'enrol_imsenterprise'), ''));

    $settings->add(new admin_setting_configtext('enrol_imsenterprise/imsrestricttarget',
        get_string('restricttarget', 'enrol_imsenterprise'), get_string('restricttarget_desc', 'enrol_imsenterprise'), ''));

    $settings->add(new admin_setting_configcheckbox('enrol_imsenterprise/imscapitafix',
        get_string('usecapitafix', 'enrol_imsenterprise'), get_string('usecapitafix_desc', 'enrol_imsenterprise'), 0));

    $importurl = new moodle_url('/enrol/imsenterprise/importnow.php', array('sesskey' => sesskey()));
    $importnowstring = get_string('aftersaving...', 'enrol_imsenterprise').' ';
    $importnowstring .= html_writer::link($importurl, get_string('doitnow', 'enrol_imsenterprise'));
    $settings->add(new admin_setting_heading('enrol_imsenterprise_doitnowmessage', '', $importnowstring));
}