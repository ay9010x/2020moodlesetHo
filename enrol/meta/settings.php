<?php



defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

        $settings->add(new admin_setting_heading('enrol_meta_settings', '', get_string('pluginname_desc', 'enrol_meta')));

    if (!during_initial_install()) {
        $allroles = role_fix_names(get_all_roles(), null, ROLENAME_ORIGINALANDSHORT, true);
        $settings->add(new admin_setting_configmultiselect('enrol_meta/nosyncroleids', get_string('nosyncroleids', 'enrol_meta'), get_string('nosyncroleids_desc', 'enrol_meta'), array(), $allroles));

        $settings->add(new admin_setting_configcheckbox('enrol_meta/syncall', get_string('syncall', 'enrol_meta'), get_string('syncall_desc', 'enrol_meta'), 1));

        $options = array(
            ENROL_EXT_REMOVED_UNENROL        => get_string('extremovedunenrol', 'core_enrol'),
            ENROL_EXT_REMOVED_SUSPEND        => get_string('extremovedsuspend', 'core_enrol'),
            ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'core_enrol'),
        );
        $settings->add(new admin_setting_configselect('enrol_meta/unenrolaction', get_string('extremovedaction', 'enrol'), get_string('extremovedaction_help', 'enrol'), ENROL_EXT_REMOVED_SUSPENDNOROLES, $options));

        $sortoptions = array(
            'sortorder' => new lang_string('sort_sortorder', 'admin'),
            'fullname' => new lang_string('sort_fullname', 'admin'),
            'shortname' => new lang_string('sort_shortname', 'admin'),
            'idnumber' => new lang_string('sort_idnumber', 'admin'),
        );
        $settings->add(new admin_setting_configselect(
            'enrol_meta/coursesort',
            new lang_string('coursesort', 'enrol_meta'),
            new lang_string('coursesort_help', 'enrol_meta'),
            'sortorder',
            $sortoptions
        ));
    }
}
