<?php




function xmldb_attendance_upgrade($oldversion=0) {

    global $DB;
    $dbman = $DB->get_manager(); 
    $result = true;

    if ($oldversion < 2014112000) {
        $table = new xmldb_table('attendance_sessions');

        $field = new xmldb_field('studentscanmark');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint($result, 2014112000, 'attendance');
    }

    if ($oldversion < 2014112001) {
                $sql = "UPDATE {grade_items}
                   SET itemmodule = 'attendance'
                 WHERE itemmodule = 'attforblock'";

        $DB->execute($sql);

        $sql = "UPDATE {grade_items_history}
                   SET itemmodule = 'attendance'
                 WHERE itemmodule = 'attforblock'";

        $DB->execute($sql);

        
        $sql = $DB->sql_like('capability', '?').' AND modifierid <> 0';
        $rs = $DB->get_recordset_select('role_capabilities', $sql, array('%mod/attforblock%'));
        foreach ($rs as $cap) {
            $renamedcapability = str_replace('mod/attforblock', 'mod/attendance', $cap->capability);
            $exists = $DB->record_exists('role_capabilities', array('roleid' => $cap->roleid, 'capability' => $renamedcapability));
            if (!$exists) {
                $DB->update_record('role_capabilities', array('id' => $cap->id, 'capability' => $renamedcapability));
            }
        }

                $sql = $DB->sql_like('capability', '?');
        $DB->delete_records_select('role_capabilities', $sql, array('%mod/attforblock%'));

                $DB->delete_records_select('capabilities', 'component = ?', array('mod_attforblock'));

        upgrade_plugin_savepoint($result, 2014112001, 'mod', 'attendance');
    }

    if ($oldversion < 2015040501) {
                $table = new xmldb_table('attendance_tempusers');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('studentid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('fullname', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('email', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('created', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                $index = new xmldb_index('courseid', XMLDB_INDEX_NOTUNIQUE, array('courseid'));
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

                $index = new xmldb_index('studentid', XMLDB_INDEX_UNIQUE, array('studentid'));
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

                upgrade_mod_savepoint(true, 2015040501, 'attendance');
    }

    if ($oldversion < 2015040502) {

                $table = new xmldb_table('attendance_statuses');
        $field = new xmldb_field('setnumber', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, '0', 'deleted');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                $table = new xmldb_table('attendance_sessions');
        $field = new xmldb_field('statusset', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, '0', 'descriptionformat');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_mod_savepoint(true, 2015040502, 'attendance');
    }

    if ($oldversion < 2015040503) {

                $table = new xmldb_table('attendance_statuses');
        $field = new xmldb_field('grade', XMLDB_TYPE_NUMBER, '5, 2', null, XMLDB_NOTNULL, null, '0', 'description');

                $dbman->change_field_type($table, $field);

                upgrade_mod_savepoint(true, 2015040503, 'attendance');
    }

    return $result;
}
