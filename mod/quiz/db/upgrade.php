<?php



defined('MOODLE_INTERNAL') || die();


function xmldb_quiz_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014052800) {

                $table = new xmldb_table('quiz');
        $field = new xmldb_field('completionattemptsexhausted', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'showblocks');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
                upgrade_mod_savepoint(true, 2014052800, 'quiz');
    }

    if ($oldversion < 2014052801) {
                $table = new xmldb_table('quiz');
        $field = new xmldb_field('completionpass', XMLDB_TYPE_INTEGER, '1', null, null, null, 0, 'completionattemptsexhausted');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_mod_savepoint(true, 2014052801, 'quiz');
    }

        
    if ($oldversion < 2015030500) {
                $table = new xmldb_table('quiz_slots');
        $field = new xmldb_field('requireprevious', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 0, 'page');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_mod_savepoint(true, 2015030500, 'quiz');
    }

    if ($oldversion < 2015030900) {
                $table = new xmldb_table('quiz');
        $field = new xmldb_field('canredoquestions', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 0, 'preferredbehaviour');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_mod_savepoint(true, 2015030900, 'quiz');
    }

    if ($oldversion < 2015032300) {

                $table = new xmldb_table('quiz_sections');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('quizid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('firstslot', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('heading', XMLDB_TYPE_CHAR, '1333', null, null, null, null);
        $table->add_field('shufflequestions', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('quizid', XMLDB_KEY_FOREIGN, array('quizid'), 'quiz', array('id'));

                $table->add_index('quizid-firstslot', XMLDB_INDEX_UNIQUE, array('quizid', 'firstslot'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                upgrade_mod_savepoint(true, 2015032300, 'quiz');
    }

    if ($oldversion < 2015032301) {

                $DB->execute("
                INSERT INTO {quiz_sections}
                            (quizid, firstslot, heading, shufflequestions)
                     SELECT  id,     1,         ?,       shufflequestions
                       FROM {quiz}
                ", array(''));

                upgrade_mod_savepoint(true, 2015032301, 'quiz');
    }

    if ($oldversion < 2015032302) {

                $table = new xmldb_table('quiz');
        $field = new xmldb_field('shufflequestions');

                if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

                upgrade_mod_savepoint(true, 2015032302, 'quiz');
    }

    if ($oldversion < 2015032303) {

                unset_config('shufflequestions', 'quiz');
        unset_config('shufflequestions_adv', 'quiz');

                upgrade_mod_savepoint(true, 2015032303, 'quiz');
    }

        
        
    if ($oldversion < 2016032600) {
                $problemquizzes = $DB->get_records_sql("
                SELECT quizid, MIN(firstslot) AS firstsectionfirstslot
                FROM {quiz_sections}
                GROUP BY quizid
                HAVING MIN(firstslot) > 1");

        if ($problemquizzes) {
            $pbar = new progress_bar('upgradequizfirstsection', 500, true);
            $total = count($problemquizzes);
            $done = 0;
            foreach ($problemquizzes as $problemquiz) {
                $DB->set_field('quiz_sections', 'firstslot', 1,
                        array('quizid' => $problemquiz->quizid,
                        'firstslot' => $problemquiz->firstsectionfirstslot));
                $done += 1;
                $pbar->update($done, $total, "Fixing quiz layouts - {$done}/{$total}.");
            }
        }

                upgrade_mod_savepoint(true, 2016032600, 'quiz');
    }

        
    if ($oldversion < 2016052301) {
                $gradeitems = $DB->get_records_sql("
            SELECT gi.id, gi.itemnumber, cm.id AS cmid
              FROM {quiz} q
        INNER JOIN {course_modules} cm ON q.id = cm.instance
        INNER JOIN {grade_items} gi ON q.id = gi.iteminstance
        INNER JOIN {modules} m ON m.id = cm.module
             WHERE q.completionpass = 1
               AND gi.gradepass = 0
               AND cm.completiongradeitemnumber IS NULL
               AND gi.itemmodule = m.name
               AND gi.itemtype = ?
               AND m.name = ?", array('mod', 'quiz'));

        foreach ($gradeitems as $gradeitem) {
            $DB->execute("UPDATE {course_modules}
                             SET completiongradeitemnumber = :itemnumber
                           WHERE id = :cmid",
                array('itemnumber' => $gradeitem->itemnumber, 'cmid' => $gradeitem->cmid));
        }
                upgrade_mod_savepoint(true, 2016052301, 'quiz');
    }

    return true;
}
