<?php



defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox('tinymce_moodleemoticon/requireemoticon',
        get_string('requireemoticon', 'tinymce_moodleemoticon'), get_string('requireemoticon_desc', 'tinymce_moodleemoticon'), 1));
}
