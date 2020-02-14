<?php

function scheduler_migrate_config_setting($name) {
    $oldval = get_config('core', 'scheduler_'.$name);
    set_config($name, $oldval, 'mod_scheduler');
    unset_config('scheduler_'.$name);
}

function scheduler_migrate_groupmode($sid) {
    global $DB;
    $globalenable = (bool) get_config('mod_scheduler', 'groupscheduling');
    $cm = get_coursemodule_from_instance('scheduler', $sid, 0, false, IGNORE_MISSING);
    if ($cm) {
        if ((groups_get_activity_groupmode($cm) > 0) && $globalenable) {
            $g = $cm->groupingid;
        } else {
            $g = -1;
        }
        $DB->set_field('scheduler', 'bookingrouping', $g, array('id' => $sid));
        $DB->set_field('course_modules', 'groupmode', 0, array('id' => $cm->id));
        $DB->set_field('course_modules', 'groupingid', 0, array('id' => $cm->id));
    }
}

function xmldb_scheduler_upgrade($oldversion=0) {
    
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    $result = true;

    

    if ($oldversion < 2011081302) {

                $table = new xmldb_table('scheduler');
        $introfield = new xmldb_field('description', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null, 'name');
        $dbman->rename_field($table, $introfield, 'intro', false);

        $formatfield = new xmldb_field('introformat', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, '0', 'intro');

        if (!$dbman->field_exists($table, $formatfield)) {
            $dbman->add_field($table, $formatfield);
        }

                if ($CFG->texteditors !== 'textarea') {
            $rs = $DB->get_recordset('scheduler', array('introformat' => FORMAT_MOODLE),
                '', 'id, intro, introformat');
            foreach ($rs as $q) {
                $q->intro       = text_to_html($q->intro, false, false, true);
                $q->introformat = FORMAT_HTML;
                $DB->update_record('scheduler', $q);
                upgrade_set_timeout();
            }
            $rs->close();
        }

                upgrade_mod_savepoint(true, 2011081302, 'scheduler');
    }

    

    if ($oldversion < 2012102903) {

                $table = new xmldb_table('scheduler_slots');
        $formatfield = new xmldb_field('notesformat', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, '0', 'notes');
        if (!$dbman->field_exists($table, $formatfield)) {
            $dbman->add_field($table, $formatfield);
        }

        $table = new xmldb_table('scheduler_appointment');
        $formatfield = new xmldb_field('appointmentnoteformat', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, '0', 'appointmentnote');
        if (!$dbman->field_exists($table, $formatfield)) {
            $dbman->add_field($table, $formatfield);
        }

                if ($CFG->texteditors !== 'textarea') {
            upgrade_set_timeout();
            $DB->set_field('scheduler_slots', 'notesformat', FORMAT_HTML);
            $DB->set_field('scheduler_appointment', 'appointmentnoteformat', FORMAT_HTML);
        }

                upgrade_mod_savepoint(true, 2012102903, 'scheduler');
    }

    

    if ($oldversion < 2014071300) {

                $table = new xmldb_table('scheduler');
        $field = new xmldb_field('teacher');

                if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

                $table = new xmldb_table('scheduler');
        $field = new xmldb_field('maxbookings', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '1', 'schedulermode');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                $table = new xmldb_table('scheduler');
        $field = new xmldb_field('guardtime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'maxbookings');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                $table = new xmldb_table('scheduler');
        $field = new xmldb_field('staffrolename', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'allownotifications');

                $dbman->change_field_precision($table, $field);

                $table = new xmldb_table('scheduler_slots');
        $field = new xmldb_field('appointmentlocation', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'teacherid');

                $dbman->change_field_precision($table, $field);

                $table = new xmldb_table('scheduler_slots');
        $index = new xmldb_index('schedulerid-teacherid', XMLDB_INDEX_NOTUNIQUE, array('schedulerid', 'teacherid'));

                if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

                $table = new xmldb_table('scheduler_appointment');
        $index = new xmldb_index('slotid', XMLDB_INDEX_NOTUNIQUE, array('slotid'));

                if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

                $table = new xmldb_table('scheduler_appointment');
        $index = new xmldb_index('studentid', XMLDB_INDEX_NOTUNIQUE, array('studentid'));

                if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

                $sql = 'UPDATE {event} SET modulename = ? WHERE eventtype LIKE ? OR eventtype LIKE ?';
        $DB->execute($sql, array('scheduler', 'SSsup:%', 'SSstu:%'));

                upgrade_mod_savepoint(true, 2014071300, 'scheduler');
    }

    

    if ($oldversion < 2015050400) {

                scheduler_migrate_config_setting('allteachersgrading');
        scheduler_migrate_config_setting('showemailplain');
        scheduler_migrate_config_setting('groupscheduling');
        scheduler_migrate_config_setting('maxstudentlistsize');

                upgrade_mod_savepoint(true, 2015050400, 'scheduler');
    }

    if ($oldversion < 2015062601) {

                $table = new xmldb_table('scheduler');
        $field = new xmldb_field('bookingrouping', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '-1', 'gradingstrategy');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                $sids = $DB->get_fieldset_select('scheduler', 'id', '');
        foreach ($sids as $sid) {
            scheduler_migrate_groupmode($sid);
        }

                upgrade_mod_savepoint(true, 2015062601, 'scheduler');
    }

    

    if ($oldversion < 2016051700) {

                $table = new xmldb_table('scheduler');
        $field = new xmldb_field('usenotes', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1', 'bookingrouping');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                $table = new xmldb_table('scheduler_appointment');
        $field1 = new xmldb_field('teachernote', XMLDB_TYPE_TEXT, null, null, null, null, null, 'appointmentnoteformat');
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        $field2 = new xmldb_field('teachernoteformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'teachernote');
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

                $table = new xmldb_table('scheduler_slots');
        $field = new xmldb_field('appointmentnote');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

                upgrade_mod_savepoint(true, 2016051700, 'scheduler');
    }
    return true;
}