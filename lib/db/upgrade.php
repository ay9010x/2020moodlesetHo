<?php



defined('MOODLE_INTERNAL') || die();


function xmldb_main_upgrade($oldversion) {
    global $CFG, $DB;

    require_once($CFG->libdir.'/db/upgradelib.php'); 
    $dbman = $DB->get_manager(); 
            if ($oldversion < 2014051200) {
                echo("You need to upgrade to 2.7.x or higher first!\n");
        exit(1);
                upgrade_main_savepoint(true, 2014051200);
    }

        if ($oldversion < 2014051200.02) {

        $table = new xmldb_table('log');

        $columns = $DB->get_columns('log');
        if ($columns['action']->max_length < 40) {
            $index1 = new xmldb_index('course-module-action', XMLDB_INDEX_NOTUNIQUE, array('course', 'module', 'action'));
            if ($dbman->index_exists($table, $index1)) {
                $dbman->drop_index($table, $index1);
            }
            $index2 = new xmldb_index('action', XMLDB_INDEX_NOTUNIQUE, array('action'));
            if ($dbman->index_exists($table, $index2)) {
                $dbman->drop_index($table, $index2);
            }
            $field = new xmldb_field('action', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL, null, null, 'cmid');
            $dbman->change_field_precision($table, $field);
            $dbman->add_index($table, $index1);
            $dbman->add_index($table, $index2);
        }

        if ($columns['url']->max_length < 100) {
            $field = new xmldb_field('url', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null, 'action');
            $dbman->change_field_precision($table, $field);
        }

        upgrade_main_savepoint(true, 2014051200.02);
    }

    if ($oldversion < 2014060300.00) {
        $gspath = get_config('assignfeedback_editpdf', 'gspath');
        if ($gspath !== false) {
            set_config('pathtogs', $gspath);
            unset_config('gspath', 'assignfeedback_editpdf');
        }
        upgrade_main_savepoint(true, 2014060300.00);
    }

    if ($oldversion < 2014061000.00) {
                $filetypes = array('%.pub'=>'application/x-mspublisher');
        upgrade_mimetypes($filetypes);
        upgrade_main_savepoint(true, 2014061000.00);
    }

    if ($oldversion < 2014062600.01) {
                        if (!check_dir_exists($CFG->libdir . '/editor/tinymce/plugins/dragmath', false)) {
                        unset_all_config_for_plugin('tinymce_dragmath');
        }

                upgrade_main_savepoint(true, 2014062600.01);
    }

        if ($oldversion < 2014070100.00) {
        $table = new xmldb_table('files_reference');
        $index = new xmldb_index('uq_external_file', XMLDB_INDEX_UNIQUE, array('repositoryid', 'referencehash'));
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }
        upgrade_main_savepoint(true, 2014070100.00);
    }

    if ($oldversion < 2014070101.00) {
        $table = new xmldb_table('files_reference');
        $index = new xmldb_index('uq_external_file', XMLDB_INDEX_UNIQUE, array('referencehash', 'repositoryid'));
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        upgrade_main_savepoint(true, 2014070101.00);
    }

    if ($oldversion < 2014072400.01) {
        $table = new xmldb_table('user_devices');
        $oldindex = new xmldb_index('pushid-platform', XMLDB_KEY_UNIQUE, array('pushid', 'platform'));
        if ($dbman->index_exists($table, $oldindex)) {
            $key = new xmldb_key('pushid-platform', XMLDB_KEY_UNIQUE, array('pushid', 'platform'));
            $dbman->drop_key($table, $key);
        }
        upgrade_main_savepoint(true, 2014072400.01);
    }

    if ($oldversion < 2014080801.00) {

                $table = new xmldb_table('question_attempts');
        $index = new xmldb_index('behaviour', XMLDB_INDEX_NOTUNIQUE, array('behaviour'));

                if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

                upgrade_main_savepoint(true, 2014080801.00);
    }

    if ($oldversion < 2014082900.01) {
                $filetypes = array(
                '%.7z' => 'application/x-7z-compressed',
                '%.rar' => 'application/x-rar-compressed');
        upgrade_mimetypes($filetypes);
        upgrade_main_savepoint(true, 2014082900.01);
    }

    if ($oldversion < 2014082900.02) {
                $transaction = $DB->start_delegated_transaction();
        if ($CFG->enablegroupmembersonly) {
                        if (!$CFG->enableavailability) {
                set_config('enableavailability', 1);
            }

                                    $total = $DB->count_records('course_modules', array('groupmembersonly' => 1));
            $pbar = new progress_bar('upgradegroupmembersonly', 500, true);

                        $rs = $DB->get_recordset('course_modules', array('groupmembersonly' => 1),
                    'course, id');
            $i = 0;
            foreach ($rs as $cm) {
                                $availability = upgrade_group_members_only($cm->groupingid, $cm->availability);
                $DB->set_field('course_modules', 'availability', $availability,
                        array('id' => $cm->id));

                                $i++;
                $pbar->update($i, $total, "Upgrading groupmembersonly settings - $i/$total.");
            }
            $rs->close();
        }

                $table = new xmldb_table('course_modules');
        $field = new xmldb_field('groupmembersonly');

                if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

                unset_config('enablegroupmembersonly');
        $transaction->allow_commit();

        upgrade_main_savepoint(true, 2014082900.02);
    }

    if ($oldversion < 2014100100.00) {

                $table = new xmldb_table('messageinbound_handlers');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('component', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('classname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('defaultexpiration', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '86400');
        $table->add_field('validateaddress', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('classname', XMLDB_KEY_UNIQUE, array('classname'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                $table = new xmldb_table('messageinbound_datakeys');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('handler', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('datavalue', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('datakey', XMLDB_TYPE_CHAR, '64', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('expires', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('handler_datavalue', XMLDB_KEY_UNIQUE, array('handler', 'datavalue'));
        $table->add_key('handler', XMLDB_KEY_FOREIGN, array('handler'), 'messageinbound_handlers', array('id'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                upgrade_main_savepoint(true, 2014100100.00);
    }

    if ($oldversion < 2014100600.01) {
                $table = new xmldb_table('grade_grades');
        $field = new xmldb_field('aggregationstatus', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, 'unknown', 'timemodified');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('aggregationweight', XMLDB_TYPE_NUMBER, '10, 5', null, null, null, null, 'aggregationstatus');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                $table = new xmldb_table('grade_items');
        $field = new xmldb_field('aggregationcoef2', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, '0', 'aggregationcoef');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('weightoverride', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'needsupdate');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_main_savepoint(true, 2014100600.01);
    }

    if ($oldversion < 2014100600.02) {

                $table = new xmldb_table('grade_items_history');
        $field = new xmldb_field('aggregationcoef2', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, '0', 'aggregationcoef');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_main_savepoint(true, 2014100600.02);
    }

    if ($oldversion < 2014100600.03) {

                $table = new xmldb_table('grade_items_history');
        $field = new xmldb_field('weightoverride', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'decimals');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_main_savepoint(true, 2014100600.03);
    }
    if ($oldversion < 2014100600.04) {
                        if (!get_config('grades_sumofgrades_upgrade_flagged', 'core')) {
                        $sql = 'SELECT DISTINCT courseid
                      FROM {grade_categories}
                     WHERE aggregation = ?';
            $courses = $DB->get_records_sql($sql, array(13));

            foreach ($courses as $course) {
                set_config('show_sumofgrades_upgrade_' . $course->courseid, 1);
                                                $DB->set_field('grade_items', 'needsupdate', 1, array('courseid' => $course->courseid));
            }

            set_config('grades_sumofgrades_upgrade_flagged', 1);
        }

                upgrade_main_savepoint(true, 2014100600.04);
    }

    if ($oldversion < 2014100700.00) {

                $table = new xmldb_table('messageinbound_messagelist');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('messageid', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('address', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                upgrade_main_savepoint(true, 2014100700.00);
    }

    if ($oldversion < 2014100700.01) {

                $table = new xmldb_table('cohort');
        $field = new xmldb_field('visible', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'descriptionformat');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_main_savepoint(true, 2014100700.01);
    }

    if ($oldversion < 2014100800.00) {
                if (!file_exists($CFG->dirroot . '/question/format/learnwise/format.php')) {
            unset_all_config_for_plugin('qformat_learnwise');
        }

                upgrade_main_savepoint(true, 2014100800.00);
    }

    if ($oldversion < 2014101001.00) {
                        
                        if ($systempage = $DB->get_record('my_pages', array('userid' => null, 'private' => 1))) {

                                                $blocks = array('course_overview', 'private_files', 'online_users', 'badges', 'calendar_month', 'calendar_upcoming');
            list($blocksql, $blockparams) = $DB->get_in_or_equal($blocks, SQL_PARAMS_NAMED);
            $select = "parentcontextid = :contextid
                    AND pagetypepattern = :page
                    AND showinsubcontexts = 0
                    AND subpagepattern IS NULL
                    AND blockname $blocksql";
            $params = array(
                'contextid' => context_system::instance()->id,
                'page' => 'my-index'
            );
            $params = array_merge($params, $blockparams);

            $DB->set_field_select(
                'block_instances',
                'subpagepattern',
                $systempage->id,
                $select,
                $params
            );
        }

                upgrade_main_savepoint(true, 2014101001.00);
    }

    if ($oldversion < 2014102000.00) {

                $table = new xmldb_table('grade_categories');
        $field = new xmldb_field('aggregatesubcats');

                if ($dbman->field_exists($table, $field)) {

            $sql = 'SELECT DISTINCT courseid
                      FROM {grade_categories}
                     WHERE aggregatesubcats = ?';
            $courses = $DB->get_records_sql($sql, array(1));

            foreach ($courses as $course) {
                set_config('show_aggregatesubcats_upgrade_' . $course->courseid, 1);
                                                $DB->set_field('grade_items', 'needsupdate', 1, array('courseid' => $course->courseid));
            }


            $dbman->drop_field($table, $field);
        }

                upgrade_main_savepoint(true, 2014102000.00);
    }

    if ($oldversion < 2014110300.00) {
                upgrade_fix_missing_root_folders_draft();

                upgrade_main_savepoint(true, 2014110300.00);
    }

        
    if ($oldversion < 2014111000.00) {
                set_config('upgrade_minmaxgradestepignored', 1);
                set_config('upgrade_calculatedgradeitemsonlyregrade', 1);

                upgrade_main_savepoint(true, 2014111000.00);
    }

    if ($oldversion < 2014120100.00) {

                $table = new xmldb_table('mnet_host');
        $field = new xmldb_field('sslverification', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'applicationid');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_main_savepoint(true, 2014120100.00);
    }

    if ($oldversion < 2014120101.00) {

                $table = new xmldb_table('comments');
        $field = new xmldb_field('component', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'contextid');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_main_savepoint(true, 2014120101.00);
    }

    if ($oldversion < 2014120102.00) {

                $table = new xmldb_table('user_password_history');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('hash', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                upgrade_main_savepoint(true, 2014120102.00);
    }

    if ($oldversion < 2015010800.01) {

                $DB->set_field('messageinbound_handlers', 'defaultexpiration', 0,
                array('classname' => '\core\message\inbound\private_files_handler'));

                upgrade_main_savepoint(true, 2015010800.01);

    }

    if ($oldversion < 2015012600.00) {

                                        $storage = (int) get_config('backup', 'backup_auto_storage');
        $folder = get_config('backup', 'backup_auto_destination');
        if ($storage !== 0 && empty($folder)) {
            set_config('backup_auto_storage', 0, 'backup');
        }

                upgrade_main_savepoint(true, 2015012600.00);
    }

    if ($oldversion < 2015012600.01) {

                $value = $DB->get_field('config', 'value', array('name' => 'calendar_lookahead'));
        if ($value > 90) {
            set_config('calendar_lookahead', '120');
        } else if ($value > 60 and $value < 90) {
            set_config('calendar_lookahead', '90');
        } else if ($value > 30 and $value < 60) {
            set_config('calendar_lookahead', '60');
        } else if ($value > 21 and $value < 30) {
            set_config('calendar_lookahead', '30');
        } else if ($value > 14 and $value < 21) {
            set_config('calendar_lookahead', '21');
        } else if ($value > 7 and $value < 14) {
            set_config('calendar_lookahead', '14');
        }

                upgrade_main_savepoint(true, 2015012600.01);
    }

    if ($oldversion < 2015021100.00) {

                $table = new xmldb_table('registration_hubs');
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'secret');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_main_savepoint(true, 2015021100.00);
    }

    if ($oldversion < 2015022401.00) {

                $table = new xmldb_table('message');
        $index = new xmldb_index('useridfromto', XMLDB_INDEX_NOTUNIQUE, array('useridfrom', 'useridto'));

                if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

                $table = new xmldb_table('message_read');
        $index = new xmldb_index('useridfromto', XMLDB_INDEX_NOTUNIQUE, array('useridfrom', 'useridto'));

                if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

                upgrade_main_savepoint(true, 2015022401.00);
    }

    if ($oldversion < 2015022500.00) {
        $table = new xmldb_table('user_devices');
        $index = new xmldb_index('uuid-userid', XMLDB_INDEX_NOTUNIQUE, array('uuid', 'userid'));
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        upgrade_main_savepoint(true, 2015022500.00);
    }

    if ($oldversion < 2015030400.00) {
                unset_config('registered');
        upgrade_main_savepoint(true, 2015030400.00);
    }

    if ($oldversion < 2015031100.00) {
                unset_config('enabletgzbackups');

        upgrade_main_savepoint(true, 2015031100.00);
    }

    if ($oldversion < 2015031400.00) {

                $table = new xmldb_table('message');
        $index = new xmldb_index('useridfrom', XMLDB_INDEX_NOTUNIQUE, array('useridfrom'));

                if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

                $table = new xmldb_table('message_read');
        $index = new xmldb_index('useridfrom', XMLDB_INDEX_NOTUNIQUE, array('useridfrom'));

                if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

                upgrade_main_savepoint(true, 2015031400.00);
    }

    if ($oldversion < 2015031900.01) {
        unset_config('crontime', 'registration');
        upgrade_main_savepoint(true, 2015031900.01);
    }

    if ($oldversion < 2015032000.00) {
        $table = new xmldb_table('badge_criteria');

        $field = new xmldb_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('descriptionformat', XMLDB_TYPE_INTEGER, 2, null, XMLDB_NOTNULL, null, 0);
                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_main_savepoint(true, 2015032000.00);
    }

    if ($oldversion < 2015040200.01) {
                if (!file_exists("$CFG->dirroot/$CFG->admin/tool/timezoneimport")) {
                        capabilities_cleanup('tool_timezoneimport');
                        unset_all_config_for_plugin('tool_timezoneimport');
        }
        upgrade_main_savepoint(true, 2015040200.01);
    }

    if ($oldversion < 2015040200.02) {
                $table = new xmldb_table('timezone');
                if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        upgrade_main_savepoint(true, 2015040200.02);
    }

    if ($oldversion < 2015040200.03) {
        if (isset($CFG->timezone) and $CFG->timezone == 99) {
                        unset_config('timezone');
        }
        upgrade_main_savepoint(true, 2015040200.03);
    }

    if ($oldversion < 2015040700.01) {
        $DB->delete_records('config_plugins', array('name' => 'requiremodintro'));
        upgrade_main_savepoint(true, 2015040700.01);
    }

    if ($oldversion < 2015040900.01) {
                $oldconfig = get_config('core', 'customusermenuitems');
        if (strpos("mygrades,grades|/grade/report/mygrades.php|grades", $oldconfig) === false) {
            $newconfig = "mygrades,grades|/grade/report/mygrades.php|grades\n" . $oldconfig;
            set_config('customusermenuitems', $newconfig);
        }

        upgrade_main_savepoint(true, 2015040900.01);
    }

    if ($oldversion < 2015040900.02) {
                $oldconfig = get_config('core', 'customusermenuitems');

                if (strpos($oldconfig, "mypreferences,moodle|/user/preference.php|preferences") === false) {
            $newconfig = $oldconfig . "\nmypreferences,moodle|/user/preferences.php|preferences";
        } else {
            $newconfig = $oldconfig;
        }
                $newconfig = str_replace("myfiles,moodle|/user/files.php|download", "", $newconfig);
                $newconfig = str_replace("mybadges,badges|/badges/mybadges.php|award", "", $newconfig);
                $newconfig = preg_replace('/\n+/', "\n", $newconfig);
        $newconfig = preg_replace('/(\r\n)+/', "\n", $newconfig);
        set_config('customusermenuitems', $newconfig);

        upgrade_main_savepoint(true, 2015040900.02);
    }

    if ($oldversion < 2015050400.00) {
        $config = get_config('core', 'customusermenuitems');

                $config = str_replace("mypreferences,moodle|/user/preferences.php|preferences",
            "preferences,moodle|/user/preferences.php|preferences", $config);

                $config = str_replace("mygrades,grades|/grade/report/mygrades.php|grades",
            "grades,grades|/grade/report/mygrades.php|grades", $config);

        set_config('customusermenuitems', $config);

        upgrade_main_savepoint(true, 2015050400.00);
    }

    if ($oldversion < 2015050401.00) {
                $oldconfig = get_config('core', 'customusermenuitems');
        $messagesconfig = "messages,message|/message/index.php|message";
        $preferencesconfig = "preferences,moodle|/user/preferences.php|preferences";

                if (strpos($oldconfig, $messagesconfig) === false) {
                        if (strpos($oldconfig, "preferences,moodle|/user/preferences.php|preferences") !== false) {
                                $newconfig = str_replace($preferencesconfig, $messagesconfig . "\n" . $preferencesconfig, $oldconfig);
            } else {
                                $newconfig = $oldconfig . "\n" . $messagesconfig;
            }
            set_config('customusermenuitems', $newconfig);
        }

        upgrade_main_savepoint(true, 2015050401.00);
    }

        
    if ($oldversion < 2015060400.02) {

                if (empty($CFG->upgrade_minmaxgradestepignored)) {

            upgrade_minmaxgrade();

                        set_config('upgrade_minmaxgradestepignored', 1);
        }

        upgrade_main_savepoint(true, 2015060400.02);
    }

    if ($oldversion < 2015061900.00) {
        
                                
                if (empty($CFG->upgrade_extracreditweightsstepignored)) {
            upgrade_extra_credit_weightoverride();

                        set_config('upgrade_extracreditweightsstepignored', 1);
        }

                upgrade_main_savepoint(true, 2015061900.00);
    }

    if ($oldversion < 2015062500.01) {
        
                
                if (empty($CFG->upgrade_calculatedgradeitemsignored)) {
            upgrade_calculated_grade_items();

                        set_config('upgrade_calculatedgradeitemsignored', 1);
                        unset_config('upgrade_calculatedgradeitemsonlyregrade');
        }

                upgrade_main_savepoint(true, 2015062500.01);
    }

    if ($oldversion < 2015081300.01) {

                $table = new xmldb_table('grade_import_values');
        $field = new xmldb_field('importonlyfeedback', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'importer');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_main_savepoint(true, 2015081300.01);
    }

    if ($oldversion < 2015082400.00) {

                $table = new xmldb_table('webdav_locks');

                if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

                upgrade_main_savepoint(true, 2015082400.00);
    }

    if ($oldversion < 2015090200.00) {
        $table = new xmldb_table('message');

                $field1 = new xmldb_field('timeuserfromdeleted', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0',
            'timecreated');
        $field2 = new xmldb_field('timeusertodeleted', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0',
            'timecreated');
        $oldindex = new xmldb_index('useridfromto', XMLDB_INDEX_NOTUNIQUE,
            array('useridfrom', 'useridto'));
        $newindex = new xmldb_index('useridfromtodeleted', XMLDB_INDEX_NOTUNIQUE,
            array('useridfrom', 'useridto', 'timeuserfromdeleted', 'timeusertodeleted'));

                if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }

                if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

                if ($dbman->index_exists($table, $oldindex)) {
            $dbman->drop_index($table, $oldindex);
        }

                if (!$dbman->index_exists($table, $newindex)) {
            $dbman->add_index($table, $newindex);
        }

                $table = new xmldb_table('message_read');

                if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }

                if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

                if ($dbman->index_exists($table, $oldindex)) {
            $dbman->drop_index($table, $oldindex);
        }

                if (!$dbman->index_exists($table, $newindex)) {
            $dbman->add_index($table, $newindex);
        }

                upgrade_main_savepoint(true, 2015090200.00);
    }

    if ($oldversion < 2015090801.00) {
                        upgrade_course_tags();

                        if (!empty($CFG->block_tags_showcoursetags)) {
            if ($record = $DB->get_record('block', array('name' => 'tags'), 'id, visible')) {
                if ($record->visible) {
                    $DB->update_record('block', array('id' => $record->id, 'visible' => 0));
                }
            }
        }

                $table = new xmldb_table('tag');
        $index = new xmldb_index('idname', XMLDB_INDEX_UNIQUE, array('id', 'name'));

                if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

                upgrade_main_savepoint(true, 2015090801.00);
    }

    if ($oldversion < 2015092200.00) {
                $table = new xmldb_table('question');
        $index = new xmldb_index('qtype', XMLDB_INDEX_NOTUNIQUE, array('qtype'));

                if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

                upgrade_main_savepoint(true, 2015092200.00);
    }

    if ($oldversion < 2015092900.00) {
                $keep = get_config('backup', 'backup_auto_keep');
        if ($keep !== false) {
            set_config('backup_auto_max_kept', $keep, 'backup');
            unset_config('backup_auto_keep', 'backup');
        }

                upgrade_main_savepoint(true, 2015092900.00);
    }

    if ($oldversion < 2015100600.00) {

                $table = new xmldb_table('message_read');
        $index = new xmldb_index('notificationtimeread', XMLDB_INDEX_NOTUNIQUE, array('notification', 'timeread'));

                if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

                upgrade_main_savepoint(true, 2015100600.00);
    }

    if ($oldversion < 2015100800.01) {
                        unset_config('updateautodeploy');
        upgrade_main_savepoint(true, 2015100800.01);
    }

        
    if ($oldversion < 2016011300.01) {

                
                $table = new xmldb_table('tag_coll');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('isdefault', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('component', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('searchable', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('customurl', XMLDB_TYPE_CHAR, '255', null, null, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                        $table = new xmldb_table('tag');
        $index = new xmldb_index('name', XMLDB_INDEX_UNIQUE, array('name'));

                if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

                $table = new xmldb_table('tag');
        $field = new xmldb_field('tagcollid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'userid');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                upgrade_main_savepoint(true, 2016011300.01);
    }

    if ($oldversion < 2016011300.02) {
                if (!$tcid = $DB->get_field_sql('SELECT id FROM {tag_coll} ORDER BY isdefault DESC, sortorder, id', null,
                IGNORE_MULTIPLE)) {
            $tcid = $DB->insert_record('tag_coll', array('isdefault' => 1, 'sortorder' => 0));
        }
        $DB->execute('UPDATE {tag} SET tagcollid = ? WHERE tagcollid IS NULL', array($tcid));

                $table = new xmldb_table('tag');
        $index = new xmldb_index('tagcollname', XMLDB_INDEX_UNIQUE, array('tagcollid', 'name'));
        $field = new xmldb_field('tagcollid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'userid');

                if (!$dbman->index_exists($table, $index)) {
                        $dbman->change_field_notnull($table, $field);
            $dbman->add_index($table, $index);
        }

                $table = new xmldb_table('tag');
        $key = new xmldb_key('tagcollid', XMLDB_KEY_FOREIGN, array('tagcollid'), 'tag_coll', array('id'));

                $dbman->add_key($table, $key);

                upgrade_main_savepoint(true, 2016011300.02);
    }

    if ($oldversion < 2016011300.03) {

                $table = new xmldb_table('tag_area');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('component', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('itemtype', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('enabled', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('tagcollid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('callback', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('callbackfile', XMLDB_TYPE_CHAR, '100', null, null, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('tagcollid', XMLDB_KEY_FOREIGN, array('tagcollid'), 'tag_coll', array('id'));

                $table->add_index('compitemtype', XMLDB_INDEX_UNIQUE, array('component', 'itemtype'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                upgrade_main_savepoint(true, 2016011300.03);
    }

    if ($oldversion < 2016011300.04) {

                $table = new xmldb_table('tag_instance');
        $index = new xmldb_index('itemtype-itemid-tagid-tiuserid', XMLDB_INDEX_UNIQUE,
                array('itemtype', 'itemid', 'tagid', 'tiuserid'));

                if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

                upgrade_main_savepoint(true, 2016011300.04);
    }

    if ($oldversion < 2016011300.05) {

        $DB->execute("UPDATE {tag_instance} SET component = ? WHERE component IS NULL", array(''));

                $table = new xmldb_table('tag_instance');
        $field = new xmldb_field('component', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null, 'tagid');

                $dbman->change_field_notnull($table, $field);

                $table = new xmldb_table('tag_instance');
        $field = new xmldb_field('itemtype', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null, 'component');

                $dbman->change_field_type($table, $field);

                upgrade_main_savepoint(true, 2016011300.05);
    }

    if ($oldversion < 2016011300.06) {

                $table = new xmldb_table('tag_instance');
        $index = new xmldb_index('taggeditem', XMLDB_INDEX_UNIQUE, array('component', 'itemtype', 'itemid', 'tiuserid', 'tagid'));

                if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

                upgrade_main_savepoint(true, 2016011300.06);
    }

    if ($oldversion < 2016011300.07) {

                $table = new xmldb_table('tag_instance');
        $index = new xmldb_index('taglookup', XMLDB_INDEX_NOTUNIQUE, array('itemtype', 'component', 'tagid', 'contextid'));

                if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

                upgrade_main_savepoint(true, 2016011300.07);
    }

    if ($oldversion < 2016011301.00) {

                if (!file_exists("$CFG->dirroot/webservice/amf")) {
                        capabilities_cleanup('webservice_amf');
                        unset_all_config_for_plugin('webservice_amf');
        }
        upgrade_main_savepoint(true, 2016011301.00);
    }

    if ($oldversion < 2016011901.00) {

                $transaction = $DB->start_delegated_transaction();

                $total = $DB->count_records_select('user_preferences', "name = 'calendar_lookahead' AND value != '0'");
        $pbar = new progress_bar('upgradecalendarlookahead', 500, true);

                $rs = $DB->get_recordset_select('user_preferences', "name = 'calendar_lookahead' AND value != '0'");
        $i = 0;
        foreach ($rs as $userpref) {

                        if ($userpref->value > 90) {
                $newvalue = 120;
            } else if ($userpref->value > 60 and $userpref->value < 90) {
                $newvalue = 90;
            } else if ($userpref->value > 30 and $userpref->value < 60) {
                $newvalue = 60;
            } else if ($userpref->value > 21 and $userpref->value < 30) {
                $newvalue = 30;
            } else if ($userpref->value > 14 and $userpref->value < 21) {
                $newvalue = 21;
            } else if ($userpref->value > 7 and $userpref->value < 14) {
                $newvalue = 14;
            } else {
                $newvalue = $userpref->value;
            }

            $DB->set_field('user_preferences', 'value', $newvalue, array('id' => $userpref->id));

                        $i++;
            $pbar->update($i, $total, "Upgrading user preference settings - $i/$total.");
        }
        $rs->close();
        $transaction->allow_commit();

        upgrade_main_savepoint(true, 2016011901.00);
    }

    if ($oldversion < 2016020200.00) {

                $table = new xmldb_table('tag');
        $field = new xmldb_field('isstandard', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'rawname');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                        $index = new xmldb_index('tagcolltype', XMLDB_INDEX_NOTUNIQUE, array('tagcollid', 'tagtype'));

                if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

                $index = new xmldb_index('tagcolltype', XMLDB_INDEX_NOTUNIQUE, array('tagcollid', 'isstandard'));

                if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

                $field = new xmldb_field('tagtype');

                if ($dbman->field_exists($table, $field)) {
            $DB->execute("UPDATE {tag} SET isstandard=(CASE WHEN (tagtype = ?) THEN 1 ELSE 0 END)", array('official'));
            $dbman->drop_field($table, $field);
        }

                upgrade_main_savepoint(true, 2016020200.00);
    }

    if ($oldversion < 2016020201.00) {

                $table = new xmldb_table('tag_area');
        $field = new xmldb_field('showstandard', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'callbackfile');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                $DB->execute("UPDATE {tag_area} SET showstandard = ? WHERE itemtype = ? AND component = ?",
            array(2, 'user', 'core'));

                $table = new xmldb_table('tag_area');
        $field = new xmldb_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'itemtype');

                $dbman->change_field_precision($table, $field);

                upgrade_main_savepoint(true, 2016020201.00);
    }

    if ($oldversion < 2016021500.00) {
        $root = $CFG->tempdir . '/download';
        if (is_dir($root)) {
                        $repositories = $DB->get_records('repository', array(), '', 'type');

            foreach ($repositories as $id => $repository) {
                $directory = $root . '/repository_' . $repository->type;
                if (is_dir($directory)) {
                    fulldelete($directory);
                }
            }
        }

                upgrade_main_savepoint(true, 2016021500.00);
    }

    if ($oldversion < 2016021501.00) {
                upgrade_set_timeout(3600);

                $table = new xmldb_table('grade_grades_history');
        $index = new xmldb_index('userid-itemid-timemodified', XMLDB_INDEX_NOTUNIQUE, array('userid', 'itemid', 'timemodified'));

                if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

                upgrade_main_savepoint(true, 2016021501.00);
    }

    if ($oldversion < 2016030103.00) {

                
                if (!empty($CFG->runclamonupload) && !empty($CFG->pathtoclam)) {
            set_config('antiviruses', 'clamav');
        } else {
            set_config('antiviruses', '');
        }

        if (isset($CFG->runclamonupload)) {
                                    unset_config('runclamonupload');
        }
                if (isset($CFG->pathtoclam)) {
            set_config('pathtoclam', $CFG->pathtoclam, 'antivirus_clamav');
            unset_config('pathtoclam');
        }
        if (isset($CFG->quarantinedir)) {
            set_config('quarantinedir', $CFG->quarantinedir, 'antivirus_clamav');
            unset_config('quarantinedir');
        }
        if (isset($CFG->clamfailureonupload)) {
            set_config('clamfailureonupload', $CFG->clamfailureonupload, 'antivirus_clamav');
            unset_config('clamfailureonupload');
        }

                upgrade_main_savepoint(true, 2016030103.00);
    }

    if ($oldversion < 2016030400.01) {
                $table = new xmldb_table('external_functions');
        $field = new xmldb_field('services', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'capabilities');

                if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
                upgrade_main_savepoint(true, 2016030400.01);
    }

    if ($oldversion < 2016041500.50) {

                $table = new xmldb_table('competency');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('shortname', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('descriptionformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('idnumber', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('competencyframeworkid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('parentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('path', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('ruletype', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('ruleoutcome', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('ruleconfig', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('scaleid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('scaleconfiguration', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

                $table->add_index('idnumberframework', XMLDB_INDEX_UNIQUE, array('competencyframeworkid', 'idnumber'));
        $table->add_index('ruleoutcome', XMLDB_INDEX_NOTUNIQUE, array('ruleoutcome'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                upgrade_main_savepoint(true, 2016041500.50);
    }

    if ($oldversion < 2016041500.51) {

                $table = new xmldb_table('competency_coursecompsetting');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('pushratingstouserplans', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('courseidlink', XMLDB_KEY_FOREIGN_UNIQUE, array('courseid'), 'course', array('id'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                upgrade_main_savepoint(true, 2016041500.51);
    }

    if ($oldversion < 2016041500.52) {

                $table = new xmldb_table('competency_framework');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('shortname', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('contextid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('idnumber', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('descriptionformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('scaleid', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('scaleconfiguration', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('visible', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('taxonomies', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

                $table->add_index('idnumber', XMLDB_INDEX_UNIQUE, array('idnumber'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                upgrade_main_savepoint(true, 2016041500.52);
    }

    if ($oldversion < 2016041500.53) {

                $table = new xmldb_table('competency_coursecomp');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('competencyid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('ruleoutcome', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('courseidlink', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));
        $table->add_key('competencyid', XMLDB_KEY_FOREIGN, array('competencyid'), 'competency_competency', array('id'));

                $table->add_index('courseidruleoutcome', XMLDB_INDEX_NOTUNIQUE, array('courseid', 'ruleoutcome'));
        $table->add_index('courseidcompetencyid', XMLDB_INDEX_UNIQUE, array('courseid', 'competencyid'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                upgrade_main_savepoint(true, 2016041500.53);
    }

    if ($oldversion < 2016041500.54) {

                $table = new xmldb_table('competency_plan');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('descriptionformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('templateid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('origtemplateid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('duedate', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('reviewerid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

                $table->add_index('useridstatus', XMLDB_INDEX_NOTUNIQUE, array('userid', 'status'));
        $table->add_index('templateid', XMLDB_INDEX_NOTUNIQUE, array('templateid'));
        $table->add_index('statusduedate', XMLDB_INDEX_NOTUNIQUE, array('status', 'duedate'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                upgrade_main_savepoint(true, 2016041500.54);
    }

    if ($oldversion < 2016041500.55) {

                $table = new xmldb_table('competency_template');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('shortname', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('contextid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('descriptionformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('visible', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('duedate', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                upgrade_main_savepoint(true, 2016041500.55);
    }

    if ($oldversion < 2016041500.56) {

                $table = new xmldb_table('competency_templatecomp');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('templateid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('competencyid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('templateidlink', XMLDB_KEY_FOREIGN, array('templateid'), 'competency_template', array('id'));
        $table->add_key('competencyid', XMLDB_KEY_FOREIGN, array('competencyid'), 'competency_competency', array('id'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                upgrade_main_savepoint(true, 2016041500.56);
    }

    if ($oldversion < 2016041500.57) {

                $table = new xmldb_table('competency_templatecohort');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('templateid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('cohortid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

                $table->add_index('templateid', XMLDB_INDEX_NOTUNIQUE, array('templateid'));
        $table->add_index('templatecohortids', XMLDB_INDEX_UNIQUE, array('templateid', 'cohortid'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                upgrade_main_savepoint(true, 2016041500.57);
    }

    if ($oldversion < 2016041500.58) {

                $table = new xmldb_table('competency_relatedcomp');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('competencyid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('relatedcompetencyid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                upgrade_main_savepoint(true, 2016041500.58);
    }

    if ($oldversion < 2016041500.59) {

                $table = new xmldb_table('competency_usercomp');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('competencyid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('reviewerid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('proficiency', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('grade', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

                $table->add_index('useridcompetency', XMLDB_INDEX_UNIQUE, array('userid', 'competencyid'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                upgrade_main_savepoint(true, 2016041500.59);
    }

    if ($oldversion < 2016041500.60) {

                $table = new xmldb_table('competency_usercompcourse');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('competencyid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('proficiency', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('grade', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

                $table->add_index('useridcoursecomp', XMLDB_INDEX_UNIQUE, array('userid', 'courseid', 'competencyid'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                upgrade_main_savepoint(true, 2016041500.60);
    }

    if ($oldversion < 2016041500.61) {

                $table = new xmldb_table('competency_usercompplan');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('competencyid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('planid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('proficiency', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('grade', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

                $table->add_index('usercompetencyplan', XMLDB_INDEX_UNIQUE, array('userid', 'competencyid', 'planid'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                upgrade_main_savepoint(true, 2016041500.61);
    }

    if ($oldversion < 2016041500.62) {

                $table = new xmldb_table('competency_plancomp');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('planid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('competencyid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

                $table->add_index('planidcompetencyid', XMLDB_INDEX_UNIQUE, array('planid', 'competencyid'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                upgrade_main_savepoint(true, 2016041500.62);
    }

    if ($oldversion < 2016041500.63) {

                $table = new xmldb_table('competency_evidence');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('usercompetencyid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('contextid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('action', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, null);
        $table->add_field('actionuserid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('descidentifier', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('desccomponent', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('desca', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('url', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('grade', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('note', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

                $table->add_index('usercompetencyid', XMLDB_INDEX_NOTUNIQUE, array('usercompetencyid'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                upgrade_main_savepoint(true, 2016041500.63);
    }

    if ($oldversion < 2016041500.64) {

                $table = new xmldb_table('competency_userevidence');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('descriptionformat', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('url', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

                $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                upgrade_main_savepoint(true, 2016041500.64);
    }

    if ($oldversion < 2016041500.65) {

                $table = new xmldb_table('competency_userevidencecomp');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userevidenceid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('competencyid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

                $table->add_index('userevidenceid', XMLDB_INDEX_NOTUNIQUE, array('userevidenceid'));
        $table->add_index('userevidencecompids', XMLDB_INDEX_UNIQUE, array('userevidenceid', 'competencyid'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                upgrade_main_savepoint(true, 2016041500.65);
    }

    if ($oldversion < 2016041500.66) {

                $table = new xmldb_table('competency_modulecomp');

                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('cmid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('competencyid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('ruleoutcome', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, null);

                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('cmidkey', XMLDB_KEY_FOREIGN, array('cmid'), 'course_modules', array('id'));
        $table->add_key('competencyidkey', XMLDB_KEY_FOREIGN, array('competencyid'), 'competency_competency', array('id'));

                $table->add_index('cmidruleoutcome', XMLDB_INDEX_NOTUNIQUE, array('cmid', 'ruleoutcome'));
        $table->add_index('cmidcompetencyid', XMLDB_INDEX_UNIQUE, array('cmid', 'competencyid'));

                if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

                upgrade_main_savepoint(true, 2016041500.66);
    }

    if ($oldversion < 2016042100.00) {
                $DB->execute("UPDATE {user} SET country = UPPER(country)");
                upgrade_main_savepoint(true, 2016042100.00);
    }

    if ($oldversion < 2016042600.01) {
        $deprecatedwebservices = [
            'moodle_course_create_courses',
            'moodle_course_get_courses',
            'moodle_enrol_get_enrolled_users',
            'moodle_enrol_get_users_courses',
            'moodle_enrol_manual_enrol_users',
            'moodle_file_get_files',
            'moodle_file_upload',
            'moodle_group_add_groupmembers',
            'moodle_group_create_groups',
            'moodle_group_delete_groupmembers',
            'moodle_group_delete_groups',
            'moodle_group_get_course_groups',
            'moodle_group_get_groupmembers',
            'moodle_group_get_groups',
            'moodle_message_send_instantmessages',
            'moodle_notes_create_notes',
            'moodle_role_assign',
            'moodle_role_unassign',
            'moodle_user_create_users',
            'moodle_user_delete_users',
            'moodle_user_get_course_participants_by_id',
            'moodle_user_get_users_by_courseid',
            'moodle_user_get_users_by_id',
            'moodle_user_update_users',
            'core_grade_get_definitions',
            'core_user_get_users_by_id',
            'moodle_webservice_get_siteinfo',
            'mod_forum_get_forum_discussions'
        ];

        list($insql, $params) = $DB->get_in_or_equal($deprecatedwebservices);
        $DB->delete_records_select('external_functions', "name $insql", $params);
        $DB->delete_records_select('external_services_functions', "functionname $insql", $params);
                upgrade_main_savepoint(true, 2016042600.01);
    }

    if ($oldversion < 2016051300.00) {
                make_competence_scale();

                upgrade_main_savepoint(true, 2016051300.00);
    }

    if ($oldversion < 2016051700.01) {
                if (empty($CFG->upgrade_letterboundarycourses)) {
                                                            upgrade_course_letter_boundary();

                        set_config('upgrade_letterboundarycourses', 1);
        }
                upgrade_main_savepoint(true, 2016051700.01);
    }

        
    if ($oldversion < 2016052301.08) {
                $hour = 0;
        $minute = 0;

                if (isset($CFG->statsruntimestarthour)) {
            $hour = $CFG->statsruntimestarthour;
        }
        if (isset($CFG->statsruntimestartminute)) {
            $minute = $CFG->statsruntimestartminute;
        }

                $stattask = $DB->get_record('task_scheduled', array('component' => 'moodle', 'classname' => '\core\task\stats_cron_task'));

                if ($stattask && !$stattask->customised) {

            $nextruntime = mktime($hour, $minute, 0, date('m'), date('d'), date('Y'));
            if ($nextruntime < $stattask->lastruntime) {
                                $newtime = new DateTime();
                $newtime->setTimestamp($nextruntime);
                $newtime->add(new DateInterval('P1D'));
                $nextruntime = $newtime->getTimestamp();
            }
            $stattask->nextruntime = $nextruntime;
            $stattask->minute = $minute;
            $stattask->hour = $hour;
            $stattask->customised = 1;
            $DB->update_record('task_scheduled', $stattask);
        }
                unset_config('statsruntimestarthour');
        unset_config('statsruntimestartminute');
        unset_config('statslastexecution');

        upgrade_main_savepoint(true, 2016052301.08);
    }

    if ($oldversion < 2016052302.02) {

                $table = new xmldb_table('question_attempt_step_data');
        $index = new xmldb_index('attemptstepid-name', XMLDB_INDEX_UNIQUE, array('attemptstepid', 'name'));

                if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

                upgrade_main_savepoint(true, 2016052302.02);
    }

    return true;
}
