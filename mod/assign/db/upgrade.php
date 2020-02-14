<?php



defined('MOODLE_INTERNAL') || die();


function xmldb_assign_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014051201) {

        
        $DB->delete_records('assign_user_mapping', array('assignment'=>0));
                upgrade_mod_savepoint(true, 2014051201, 'assign');
    }

    if ($oldversion < 2014072400) {

                $table = new xmldb_table('assign_submission');
        $field = new xmldb_field('latest', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'attemptnumber');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_mod_savepoint(true, 2014072400, 'assign');
    }
    if ($oldversion < 2014072401) {

                 $table = new xmldb_table('assign_submission');
        $index = new xmldb_index('latestattempt', XMLDB_INDEX_NOTUNIQUE, array('assignment', 'userid', 'groupid', 'latest'));

                if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

                upgrade_mod_savepoint(true, 2014072401, 'assign');
    }
    if ($oldversion < 2014072405) {

        
        $countsql = 'SELECT COUNT(id) FROM {assign_submission} WHERE latest = ?';

        $count = $DB->count_records_sql($countsql, array(1));
        if ($count == 0) {

                        $maxattemptsql = 'SELECT assignment, userid, groupid, max(attemptnumber) AS maxattempt
                                FROM {assign_submission}
                            GROUP BY assignment, groupid, userid';

            $maxattemptidssql = 'SELECT souter.id
                                   FROM {assign_submission} souter
                                   JOIN (' . $maxattemptsql . ') sinner
                                     ON souter.assignment = sinner.assignment
                                    AND souter.userid = sinner.userid
                                    AND souter.groupid = sinner.groupid
                                    AND souter.attemptnumber = sinner.maxattempt';

                                    if ($DB->get_dbfamily() === 'mysql') {
                $params = array('latest' => 1);
                $sql = 'UPDATE {assign_submission}
                    INNER JOIN (' . $maxattemptidssql . ') souterouter ON souterouter.id = {assign_submission}.id
                           SET latest = :latest';
                $DB->execute($sql, $params);
            } else {
                $select = 'id IN(' . $maxattemptidssql . ')';
                $DB->set_field_select('assign_submission', 'latest', 1, $select);
            }

                                    $records = $DB->get_records_sql('SELECT g.id, g.assignment, g.userid
                                               FROM {assign_grades} g
                                          LEFT JOIN {assign_submission} s
                                                 ON s.assignment = g.assignment
                                                AND s.userid = g.userid
                                              WHERE s.id IS NULL');
            $submissions = array();
            foreach ($records as $record) {
                $submission = new stdClass();
                $submission->assignment = $record->assignment;
                $submission->userid = $record->userid;
                $submission->status = 'new';
                $submission->groupid = 0;
                $submission->latest = 1;
                $submission->timecreated = time();
                $submission->timemodified = time();
                array_push($submissions, $submission);
            }

            $DB->insert_records('assign_submission', $submissions);
        }

                upgrade_mod_savepoint(true, 2014072405, 'assign');
    }

        
    if ($oldversion < 2014122600) {
                if ($DB->get_dbfamily() === 'mysql') {
            $sql1 = "DELETE {assign_user_flags}
                       FROM {assign_user_flags}
                  LEFT JOIN {assign}
                         ON {assign_user_flags}.assignment = {assign}.id
                      WHERE {assign}.id IS NULL";

            $sql2 = "DELETE {assign_user_mapping}
                       FROM {assign_user_mapping}
                  LEFT JOIN {assign}
                         ON {assign_user_mapping}.assignment = {assign}.id
                      WHERE {assign}.id IS NULL";
        } else {
            $sql1 = "DELETE FROM {assign_user_flags}
                WHERE NOT EXISTS (
                          SELECT 'x' FROM {assign}
                           WHERE {assign_user_flags}.assignment = {assign}.id)";

            $sql2 = "DELETE FROM {assign_user_mapping}
                WHERE NOT EXISTS (
                          SELECT 'x' FROM {assign}
                           WHERE {assign_user_mapping}.assignment = {assign}.id)";
        }

        $DB->execute($sql1);
        $DB->execute($sql2);

        upgrade_mod_savepoint(true, 2014122600, 'assign');
    }

    if ($oldversion < 2015022300) {

                $table = new xmldb_table('assign');
        $field = new xmldb_field('preventsubmissionnotingroup',
            XMLDB_TYPE_INTEGER,
            '2',
            null,
            XMLDB_NOTNULL,
            null,
            '0',
            'sendstudentnotifications');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_mod_savepoint(true, 2015022300, 'assign');
    }

        
        
        
    
    if($oldversion < 2017020101){
        
        $table = new xmldb_table('assign_user_flags');
        $field = new xmldb_field('patternstate', XMLDB_TYPE_INTEGER, '10');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2017020101, 'assign');
    }
    return true;
}
