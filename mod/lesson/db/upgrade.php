<?php



defined('MOODLE_INTERNAL') || die();


function xmldb_lesson_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014091001) {
        $table = new xmldb_table('lesson');
        $field = new xmldb_field('intro', XMLDB_TYPE_TEXT, null, null, null, null, null, 'name');
                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('introformat', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'intro');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2014091001, 'lesson');
    }

    if ($oldversion < 2014100600) {
                        set_config('requiremodintro', 0, 'lesson');
        upgrade_mod_savepoint(true, 2014100600, 'lesson');
    }

        
    if ($oldversion < 2014112300) {

                $table = new xmldb_table('lesson');
        $field = new xmldb_field('completionendreached', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'timemodified');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                $table = new xmldb_table('lesson_timer');
        $field = new xmldb_field('completed', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'lessontime');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
                upgrade_mod_savepoint(true, 2014112300, 'lesson');
    }

    if ($oldversion < 2014122900) {

                $table = new xmldb_table('lesson');
        $field = new xmldb_field('grade', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'conditions');

                $dbman->change_field_precision($table, $field);

                upgrade_mod_savepoint(true, 2014122900, 'lesson');
    }

    if ($oldversion < 2015030300) {

                $table = new xmldb_table('lesson_branch');
        $field = new xmldb_field('nextpageid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timeseen');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_mod_savepoint(true, 2015030300, 'lesson');
    }

    if ($oldversion < 2015030301) {

                        
        $sql = 'SELECT a.*
                  FROM {lesson_answers} a
                  JOIN {lesson_pages} p ON p.id = a.pageid
                 WHERE a.answerformat <> :format
                   AND p.qtype IN (1, 8, 20)';
        $badanswers = $DB->get_recordset_sql($sql, array('format' => FORMAT_MOODLE));

        foreach ($badanswers as $badanswer) {
                        $badanswer->answer = strip_tags($badanswer->answer);
            $badanswer->answerformat = FORMAT_MOODLE;
            $DB->update_record('lesson_answers', $badanswer);
        }
        $badanswers->close();

                upgrade_mod_savepoint(true, 2015030301, 'lesson');
    }

    if ($oldversion < 2015030400) {

                $table = new xmldb_table('lesson');
        $field = new xmldb_field('timelimit', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'maxpages');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_mod_savepoint(true, 2015030400, 'lesson');
    }

    if ($oldversion < 2015030401) {

                $table = new xmldb_table('lesson');
        $oldfield = new xmldb_field('maxtime');
        $newfield = new xmldb_field('timelimit');
        if ($dbman->field_exists($table, $oldfield) && $dbman->field_exists($table, $newfield)) {
            $sql = 'UPDATE {lesson} SET timelimit = 60 * maxtime';
            $DB->execute($sql);
                        $dbman->drop_field($table, $oldfield);
        }

        $oldfield = new xmldb_field('timed');
        if ($dbman->field_exists($table, $oldfield) && $dbman->field_exists($table, $newfield)) {
                        $DB->set_field_select('lesson', 'timelimit', 0, 'timed = 0');
                        $dbman->drop_field($table, $oldfield);
        }
                upgrade_mod_savepoint(true, 2015030401, 'lesson');
    }

    if ($oldversion < 2015031500) {

                $table = new xmldb_table('lesson');
        $field = new xmldb_field('completiontimespent', XMLDB_TYPE_INTEGER, '11', null, null, null, '0', 'completionendreached');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_mod_savepoint(true, 2015031500, 'lesson');
    }

    if ($oldversion < 2015032600) {

                        $DB->set_field('lesson', 'retake', 1, array('practice' => 1));

                upgrade_mod_savepoint(true, 2015032600, 'lesson');
    }

    if ($oldversion < 2015032700) {
                if ($DB->get_dbfamily() === 'mysql') {
            $sql = "DELETE {lesson_branch}
                      FROM {lesson_branch}
                 LEFT JOIN {lesson_pages}
                        ON {lesson_branch}.pageid = {lesson_pages}.id
                     WHERE {lesson_pages}.id IS NULL";
        } else {
            $sql = "DELETE FROM {lesson_branch}
               WHERE NOT EXISTS (
                         SELECT 'x' FROM {lesson_pages}
                          WHERE {lesson_branch}.pageid = {lesson_pages}.id)";
        }

        $DB->execute($sql);

                upgrade_mod_savepoint(true, 2015032700, 'lesson');
    }

    if ($oldversion < 2015033100) {

                $table = new xmldb_table('lesson_overrides');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('lessonid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('groupid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('available', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('deadline', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timelimit', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('review', XMLDB_TYPE_INTEGER, '3', null, null, null, null);
        $table->add_field('maxattempts', XMLDB_TYPE_INTEGER, '3', null, null, null, null);
        $table->add_field('retake', XMLDB_TYPE_INTEGER, '3', null, null, null, null);
        $table->add_field('password', XMLDB_TYPE_CHAR, '32', null, null, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('lessonid', XMLDB_KEY_FOREIGN, array('lessonid'), 'lesson', array('id'));
        $table->add_key('groupid', XMLDB_KEY_FOREIGN, array('groupid'), 'groups', array('id'));
        $table->add_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                upgrade_mod_savepoint(true, 2015033100, 'lesson');
    }

        
    if ($oldversion < 2015071800) {

                $table = new xmldb_table('lesson_high_scores');

                if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

                upgrade_mod_savepoint(true, 2015071800, 'lesson');
    }

    if ($oldversion < 2015071801) {

                $table = new xmldb_table('lesson');
        $field = new xmldb_field('highscores');

                if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

                upgrade_mod_savepoint(true, 2015071801, 'lesson');
    }

    if ($oldversion < 2015071802) {

                $table = new xmldb_table('lesson');
        $field = new xmldb_field('maxhighscores');

                if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

                upgrade_mod_savepoint(true, 2015071802, 'lesson');
    }

    if ($oldversion < 2015071803) {
        unset_config('lesson_maxhighscores');

                upgrade_mod_savepoint(true, 2015071803, 'lesson');
    }

        
    if ($oldversion < 2016012800) {
                        if (isset($CFG->lesson_maxanswers)) {
            set_config('maxanswers', $CFG->lesson_maxanswers, 'mod_lesson');
            set_config('maxanswers_adv', '1', 'mod_lesson');
            unset_config('lesson_maxanswers');
        }

                if (isset($CFG->lesson_slideshowwidth)) {
            set_config('slideshowwidth', $CFG->lesson_slideshowwidth, 'mod_lesson');
            unset_config('lesson_slideshowwidth');
        }

                if (isset($CFG->lesson_slideshowheight)) {
            set_config('slideshowheight', $CFG->lesson_slideshowheight, 'mod_lesson');
            unset_config('lesson_slideshowheight');
        }

                if (isset($CFG->lesson_slideshowbgcolor)) {
            set_config('slideshowbgcolor', $CFG->lesson_slideshowbgcolor, 'mod_lesson');
            unset_config('lesson_slideshowbgcolor');
        }

                if (isset($CFG->lesson_defaultnextpage)) {
            set_config('defaultnextpage', $CFG->lesson_defaultnextpage, 'mod_lesson');
            set_config('defaultnextpage_adv', '1', 'mod_lesson');
            unset_config('lesson_defaultnextpage');
        }

                if (isset($CFG->lesson_mediawidth)) {
            set_config('mediawidth', $CFG->lesson_mediawidth, 'mod_lesson');
            unset_config('lesson_mediawidth');
        }

                if (isset($CFG->lesson_mediaheight)) {
            set_config('mediaheight', $CFG->lesson_mediaheight, 'mod_lesson');
            unset_config('lesson_mediaheight');
        }

                if (isset($CFG->lesson_mediaclose)) {
            set_config('mediaclose', $CFG->lesson_mediaclose, 'mod_lesson');
            unset_config('lesson_mediaclose');
        }

                upgrade_mod_savepoint(true, 2016012800, 'lesson');
    }
        
    return true;
}
