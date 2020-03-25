<?php




defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
        $settings->add(new admin_setting_configcheckbox('folder/showexpanded',
        get_string('showexpanded', 'folder'),
        get_string('showexpanded_help', 'folder'), 1));

    $settings->add(new admin_setting_configtext('folder/maxsizetodownload',
        get_string('maxsizetodownload', 'folder'),
        get_string('maxsizetodownload_help', 'folder'), '', PARAM_INT));
}
