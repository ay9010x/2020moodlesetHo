<?php


defined('MOODLE_INTERNAL') || die();

function xmldb_feedback_upgrade($oldversion) {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/mod/feedback/db/upgradelib.php');

    $dbman = $DB->get_manager(); 
        
        
        
    if ($oldversion < 2016031600) {
                $DB->execute('UPDATE {feedback_item} SET label = ? WHERE typ = ? OR typ = ?',
                array('', 'captcha', 'label'));

                upgrade_mod_savepoint(true, 2016031600, 'feedback');
    }

    if ($oldversion < 2016040100) {

                        
        $sql = "UPDATE {feedback_item} SET options = " . $DB->sql_concat('?', 'options') .
                " WHERE typ = ? AND presentation LIKE ? AND options NOT LIKE ?";
        $params = array('i', 'multichoice', 'c%', '%i%');
        $DB->execute($sql, $params);

                upgrade_mod_savepoint(true, 2016040100, 'feedback');
    }

    if ($oldversion < 2016051103) {

                $table = new xmldb_table('feedback_value');
        $index = new xmldb_index('completed_item', XMLDB_INDEX_UNIQUE, array('completed', 'item', 'course_id'));

                if (!$dbman->index_exists($table, $index)) {
            mod_feedback_upgrade_delete_duplicate_values();
            $dbman->add_index($table, $index);
        }

                upgrade_mod_savepoint(true, 2016051103, 'feedback');
    }

    if ($oldversion < 2016051104) {

                $table = new xmldb_table('feedback_valuetmp');
        $index = new xmldb_index('completed_item', XMLDB_INDEX_UNIQUE, array('completed', 'item', 'course_id'));

                if (!$dbman->index_exists($table, $index)) {
            mod_feedback_upgrade_delete_duplicate_values(true);
            $dbman->add_index($table, $index);
        }

                upgrade_mod_savepoint(true, 2016051104, 'feedback');
    }

    if ($oldversion < 2016051105) {

                $table = new xmldb_table('feedback_completed');
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'anonymous_response');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
                        mod_feedback_upgrade_courseid(false);
        }

                $table = new xmldb_table('feedback_completedtmp');
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'anonymous_response');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
                        mod_feedback_upgrade_courseid(true);
        }

                $table = new xmldb_table('feedback_tracking');

                if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

                upgrade_mod_savepoint(true, 2016051105, 'feedback');
    }

        
    return true;
}
