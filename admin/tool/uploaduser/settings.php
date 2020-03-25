<?php



defined('MOODLE_INTERNAL') || die;

$ADMIN->add('accounts', new admin_externalpage('tooluploaduser', get_string('uploadusers', 'tool_uploaduser'), "$CFG->wwwroot/$CFG->admin/tool/uploaduser/index.php", 'moodle/site:uploadusers'));
$ADMIN->add('accounts', new admin_externalpage('tooluploaduserpictures', get_string('uploadpictures','tool_uploaduser'), "$CFG->wwwroot/$CFG->admin/tool/uploaduser/picture.php", 'tool/uploaduser:uploaduserpictures'));
