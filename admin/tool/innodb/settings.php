<?php



defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('unsupported', new admin_externalpage('toolinnodb', 'Convert to InnoDB', $CFG->wwwroot.'/'.$CFG->admin.'/tool/innodb/index.php', 'moodle/site:config', true));
}
