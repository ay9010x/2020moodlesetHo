<?php



defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $category = new admin_category('messageinbound', new lang_string('incomingmailconfiguration', 'tool_messageinbound'));

        $settings = new admin_settingpage('messageinbound_mailsettings', new lang_string('mailsettings', 'tool_messageinbound'));

    $settings->add(new admin_setting_heading('messageinbound_generalconfiguration',
            new lang_string('messageinboundgeneralconfiguration', 'tool_messageinbound'),
            new lang_string('messageinboundgeneralconfiguration_desc', 'tool_messageinbound'), ''));
    $settings->add(new admin_setting_configcheckbox('messageinbound_enabled',
            new lang_string('messageinboundenabled', 'tool_messageinbound'),
            new lang_string('messageinboundenabled_desc', 'tool_messageinbound'), 0));

        $settings->add(new admin_setting_heading('messageinbound_mailboxconfiguration',
            new lang_string('mailboxconfiguration', 'tool_messageinbound'),
            new lang_string('messageinboundmailboxconfiguration_desc', 'tool_messageinbound'), ''));
    $settings->add(new admin_setting_configtext_with_maxlength('messageinbound_mailbox',
            new lang_string('mailbox', 'tool_messageinbound'),
            null, '', PARAM_RAW, null, 15));
    $settings->add(new admin_setting_configtext('messageinbound_domain',
            new lang_string('domain', 'tool_messageinbound'),
            null, '', PARAM_RAW));

        $settings->add(new admin_setting_heading('messageinbound_serversettings',
            new lang_string('incomingmailserversettings', 'tool_messageinbound'),
            new lang_string('incomingmailserversettings_desc', 'tool_messageinbound'), ''));
    $settings->add(new admin_setting_configtext('messageinbound_host',
            new lang_string('messageinboundhost', 'tool_messageinbound'),
            new lang_string('configmessageinboundhost', 'tool_messageinbound'), '', PARAM_RAW));

    $options = array(
        ''          => get_string('noencryption',   'tool_messageinbound'),
        'ssl'       => get_string('ssl',            'tool_messageinbound'),
        'sslv2'     => get_string('sslv2',          'tool_messageinbound'),
        'sslv3'     => get_string('sslv3',          'tool_messageinbound'),
        'tls'       => get_string('tls',            'tool_messageinbound'),
        'tlsv1'     => get_string('tlsv1',          'tool_messageinbound'),
    );
    $settings->add(new admin_setting_configselect('messageinbound_hostssl',
            new lang_string('messageinboundhostssl', 'tool_messageinbound'),
            new lang_string('messageinboundhostssl_desc', 'tool_messageinbound'), 'ssl', $options));

    $settings->add(new admin_setting_configtext('messageinbound_hostuser',
            new lang_string('messageinboundhostuser', 'tool_messageinbound'),
            new lang_string('messageinboundhostuser_desc', 'tool_messageinbound'), '', PARAM_NOTAGS));
    $settings->add(new admin_setting_configpasswordunmask('messageinbound_hostpass',
            new lang_string('messageinboundhostpass', 'tool_messageinbound'),
            new lang_string('messageinboundhostpass_desc', 'tool_messageinbound'), ''));

    $category->add('messageinbound', $settings);

        $category->add('messageinbound', new admin_externalpage('messageinbound_handlers',
            new lang_string('message_handlers', 'tool_messageinbound'),
            "$CFG->wwwroot/$CFG->admin/tool/messageinbound/index.php"));

        $ADMIN->add('server', $category);
}
