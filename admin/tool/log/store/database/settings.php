<?php



defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $testurl = new moodle_url('/admin/tool/log/store/database/test_settings.php', array('sesskey' => sesskey()));
    $test = new admin_externalpage('logstoredbtestsettings', get_string('testsettings', 'logstore_database'),
        $testurl, 'moodle/site:config', true);
    $ADMIN->add('logging', $test);

    $drivers = \logstore_database\helper::get_drivers();
        $link = html_writer::link($testurl, get_string('testsettings', 'logstore_database'), array('target' => '_blank'));
    $settings->add(new admin_setting_heading('dbsettings', get_string('databasesettings', 'logstore_database'),
        get_string('databasesettings_help', 'logstore_database', $link)));
    $settings->add(new admin_setting_configselect('logstore_database/dbdriver', get_string('databasetypehead', 'install'), '',
        '', $drivers));

    $settings->add(new admin_setting_configtext('logstore_database/dbhost', get_string('databasehost', 'install'), '', ''));
    $settings->add(new admin_setting_configtext('logstore_database/dbuser', get_string('databaseuser', 'install'), '', ''));
    $settings->add(new admin_setting_configpasswordunmask('logstore_database/dbpass', get_string('databasepass', 'install'), '', ''));
    $settings->add(new admin_setting_configtext('logstore_database/dbname', get_string('databasename', 'install'), '', ''));
    $settings->add(new admin_setting_configtext('logstore_database/dbtable', get_string('databasetable', 'logstore_database'),
        get_string('databasetable_help', 'logstore_database'), ''));

    $settings->add(new admin_setting_configcheckbox('logstore_database/dbpersist', get_string('databasepersist',
        'logstore_database'), '', '0'));
    $settings->add(new admin_setting_configtext('logstore_database/dbsocket', get_string('databasesocket', 'install'), '',
        ''));
    $settings->add(new admin_setting_configtext('logstore_database/dbport', get_string('databaseport', 'install'), '', ''));
    $settings->add(new admin_setting_configtext('logstore_database/dbschema', get_string('databaseschema',
        'logstore_database'), '', ''));
    $settings->add(new admin_setting_configtext('logstore_database/dbcollation', get_string('databasecollation',
        'logstore_database'), '', ''));
    $settings->add(new admin_setting_configtext('logstore_database/buffersize', get_string('buffersize',
        'logstore_database'), get_string('buffersize_help', 'logstore_database'), 50));

        $settings->add(new admin_setting_heading('filters', get_string('filters', 'logstore_database'), get_string('filters_help',
        'logstore_database')));
    $settings->add(new admin_setting_configcheckbox('logstore_database/logguests', get_string('logguests',
        'logstore_database'), '', '0'));
    $levels = \logstore_database\helper::get_level_options();
    $settings->add(new admin_setting_configmulticheckbox('logstore_database/includelevels', get_string('includelevels',
        'logstore_database'), '', $levels, $levels));
    $actions = \logstore_database\helper::get_action_options();
    $settings->add(new admin_setting_configmulticheckbox('logstore_database/includeactions', get_string('includeactions',
        'logstore_database'), '', $actions, $actions));
}
