<?php



define('NO_OUTPUT_BUFFERING', true);

require('../../../config.php');
require_once('locallib.php');
require_once('database_export_form.php');

require_login();
admin_externalpage_setup('tooldbexport');

$form = new database_export_form();

if ($data = $form->get_data()) {
    tool_dbtransfer_export_xml_database($data->description, $DB);
    die;
}

echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();
