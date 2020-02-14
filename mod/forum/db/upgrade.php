<?php



defined('MOODLE_INTERNAL') || die();

function xmldb_forum_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager(); 
    if ($oldversion < 2014051201) {

                $replacements = array(
            11 => 20,
            12 => 50,
            13 => 100
        );

                foreach ($replacements as $old => $new) {
            $DB->set_field('forum', 'maxattachments', $new, array('maxattachments' => $old));
        }

                upgrade_mod_savepoint(true, 2014051201, 'forum');
    }

    if ($oldversion < 2014081500) {

                $table = new xmldb_table('forum_discussions');
        $index = new xmldb_index('course', XMLDB_INDEX_NOTUNIQUE, array('course'));

                if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

                upgrade_mod_savepoint(true, 2014081500, 'forum');
    }

    if ($oldversion < 2014081900) {

                $table = new xmldb_table('forum_discussion_subs');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('forum', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('discussion', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('preference', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('forum', XMLDB_KEY_FOREIGN, array('forum'), 'forum', array('id'));
        $table->add_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));
        $table->add_key('discussion', XMLDB_KEY_FOREIGN, array('discussion'), 'forum_discussions', array('id'));
        $table->add_key('user_discussions', XMLDB_KEY_UNIQUE, array('userid', 'discussion'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                upgrade_mod_savepoint(true, 2014081900, 'forum');
    }

    if ($oldversion < 2014103000) {
                        $sql = "
            SELECT MIN(id) as lowid, userid, postid
            FROM {forum_read}
            GROUP BY userid, postid
            HAVING COUNT(id) > 1";

        if ($duplicatedrows = $DB->get_recordset_sql($sql)) {
            foreach ($duplicatedrows as $row) {
                $DB->delete_records_select('forum_read', 'userid = ? AND postid = ? AND id <> ?', array(
                    $row->userid,
                    $row->postid,
                    $row->lowid,
                ));
            }
        }
        $duplicatedrows->close();

                upgrade_mod_savepoint(true, 2014103000, 'forum');
    }

    if ($oldversion < 2014110300) {

                $table = new xmldb_table('forum_discussion_subs');
        $field = new xmldb_field('preference', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '1', 'discussion');

                $dbman->change_field_precision($table, $field);

                upgrade_mod_savepoint(true, 2014110300, 'forum');
    }

        
            if ($oldversion < 2015102900) {
                $DB->set_field('forum_discussions', 'groupid', -1, array('groupid' => 0));

                upgrade_mod_savepoint(true, 2015102900, 'forum');
    }

        
    if ($oldversion < 2015120800) {

                $table = new xmldb_table('forum_discussions');
        $field = new xmldb_field('pinned', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'timeend');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_mod_savepoint(true, 2015120800, 'forum');
    }
        
    return true;
}
