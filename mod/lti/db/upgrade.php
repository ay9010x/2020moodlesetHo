<?php



 defined('MOODLE_INTERNAL') || die;


function xmldb_lti_upgrade($oldversion) {
    global $CFG, $DB;

    require_once(__DIR__ . '/upgradelib.php');

    $dbman = $DB->get_manager();

    if ($oldversion < 2014060201) {

                $table = new xmldb_table('lti');
        $field = new xmldb_field('grade', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '100',
                'instructorchoiceacceptgrades');

                $dbman->change_field_type($table, $field);

                upgrade_mod_savepoint(true, 2014060201, 'lti');
    }

    if ($oldversion < 2014061200) {

                $table = new xmldb_table('lti_tool_proxies');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, 'Tool Provider');
        $table->add_field('regurl', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('state', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('guid', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('secret', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('vendorcode', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('capabilityoffered', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('serviceoffered', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('toolproxy', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('createdby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

                $table->add_index('guid', XMLDB_INDEX_UNIQUE, array('guid'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                $table = new xmldb_table('lti_tool_settings');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('toolproxyid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('coursemoduleid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('settings', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('toolproxy', XMLDB_KEY_FOREIGN, array('toolproxyid'), 'lti_tool_proxies', array('id'));
        $table->add_key('course', XMLDB_KEY_FOREIGN, array('course'), 'course', array('id'));
        $table->add_key('coursemodule', XMLDB_KEY_FOREIGN, array('coursemoduleid'), 'lti', array('id'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                $table = new xmldb_table('lti_types');

                $field = new xmldb_field('toolproxyid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('enabledcapability', XMLDB_TYPE_TEXT, null, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('parameter', XMLDB_TYPE_TEXT, null, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('icon', XMLDB_TYPE_TEXT, null, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('secureicon', XMLDB_TYPE_TEXT, null, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_mod_savepoint(true, 2014061200, 'lti');
    }

    if ($oldversion < 2014100300) {

        mod_lti_upgrade_custom_separator();

                upgrade_mod_savepoint(true, 2014100300, 'lti');
    }

        
        
        
    if ($oldversion < 2016041800) {

                $table = new xmldb_table('lti_types');
        $field = new xmldb_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null, 'timemodified');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_mod_savepoint(true, 2016041800, 'lti');
    }

        
    return true;
}
