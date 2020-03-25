<?php
// Reset the 'url' information in the field "plugin=block_course_menu" in the table "mdl_config_plugins" of database

define('CLI_SCRIPT', true);
require_once('config.php');
require_once($CFG->libdir.'/clilib.php');

global $DB;
$dbman = $DB->get_manager();

// Add table mdl_grade_categories_setuppage
$table = new xmldb_table('grade_categories_setuppage');
 
$table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
$table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
$table->add_field('setuppage', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);

$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);

$status = $dbman->create_table($table, $continue=true, $feedback=true);
if($dbman->table_exists($table)) {
	echo "OK for Table mdl_grade_categories_setuppage\n";
} else {
	echo "Fail for Table mdl_grade_categories_setuppage\n";
}


// Add fields 'bible', 'qna' into table mdl_course
$table = new xmldb_table('course');

$field = new xmldb_field('bible');
$field->set_attributes(XMLDB_TYPE_TEXT, 'big', null, null, null, null, null, null);

$status = $dbman->add_field($table, $field, $continue=true, $feedback=true);
if($dbman->field_exists($table, $field)) {
	echo "OK for field 'bible' in Table mdl_course\n";
} else {
	echo "Fail for field 'bible' in Table mdl_course\n";
}

$field = new xmldb_field('qna');
$field->set_attributes(XMLDB_TYPE_TEXT, 'big', null, null, null, null, null, null);

$status = $dbman->add_field($table, $field, $continue=true, $feedback=true);
if($dbman->field_exists($table, $field)) {
	echo "OK for field 'qna' in Table mdl_course\n";
} else {
	echo "Fail for field 'qna' in Table mdl_course\n";
}


// UPDATE mdl_config_plugins
set_config('maximumgrade', '100', 'quiz');

exit(0);