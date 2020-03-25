<?php



defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot.'/course/lib.php');

function report_outline_print_row($mod, $instance, $result) {
    global $OUTPUT, $CFG;

    $image = "<img src=\"" . $OUTPUT->pix_url('icon', $mod->modname) . "\" class=\"icon\" alt=\"$mod->modfullname\" />";

    echo "<tr>";
    echo "<td valign=\"top\">$image</td>";
    echo "<td valign=\"top\" style=\"width:300\">";
    echo "   <a title=\"$mod->modfullname\"";
    echo "   href=\"$CFG->wwwroot/mod/$mod->modname/view.php?id=$mod->id\">".format_string($instance->name,true)."</a></td>";
    echo "<td>&nbsp;&nbsp;&nbsp;</td>";
    echo "<td valign=\"top\">";
    if (isset($result->info)) {
        echo "$result->info";
    } else {
        echo "<p style=\"text-align:center\">-</p>";
    }
    echo "</td>";
    echo "<td>&nbsp;&nbsp;&nbsp;</td>";
    if (!empty($result->time)) {
        $timeago = format_time(time() - $result->time);
        echo "<td valign=\"top\" style=\"white-space: nowrap\">".userdate($result->time)." ($timeago)</td>";
    }
    echo "</tr>";
}


function report_outline_get_common_log_variables() {
    global $DB;

    static $uselegacyreader;
    static $useinternalreader;
    static $minloginternalreader;
    static $logtable = null;

    if (isset($uselegacyreader) && isset($useinternalreader) && isset($minloginternalreader)) {
        return array($uselegacyreader, $useinternalreader, $minloginternalreader, $logtable);
    }

    $uselegacyreader = false;     $useinternalreader = false;     $minloginternalreader = 0; 
        $logmanager = get_log_manager();
    $readers = $logmanager->get_readers();

        if (!empty($readers)) {
        foreach ($readers as $readerpluginname => $reader) {
                        if ($readerpluginname == 'logstore_legacy') {
                $uselegacyreader = true;
            }

                        if ($reader instanceof \core\log\sql_internal_table_reader) {
                $useinternalreader = true;
                $logtable = $reader->get_internal_log_table_name();
                $minloginternalreader = $DB->get_field_sql('SELECT min(timecreated) FROM {' . $logtable . '}');
            }
        }
    }

    return array($uselegacyreader, $useinternalreader, $minloginternalreader, $logtable);
}


function report_outline_user_outline($userid, $cmid, $module, $instanceid) {
    global $DB;

    list($uselegacyreader, $useinternalreader, $minloginternalreader, $logtable) = report_outline_get_common_log_variables();

        if ($uselegacyreader) {
                $params = array('userid' => $userid, 'module' => $module, 'action' => 'view', 'info' => $instanceid);
                        $limittime = '';
        if (!empty($minloginternalreader)) {
            $limittime = ' AND time < :timeto ';
            $params['timeto'] = $minloginternalreader;
        }
        $select = "SELECT COUNT(id) ";
        $from = "FROM {log} ";
        $where = "WHERE userid = :userid
                    AND module = :module
                    AND action = :action
                    AND info = :info ";
        if ($legacylogcount = $DB->count_records_sql($select . $from . $where . $limittime, $params)) {
            $numviews = $legacylogcount;

                        $select = "SELECT MAX(time) ";
            $lastlogtime = $DB->get_field_sql($select . $from . $where, $params);

            $result = new stdClass();
            $result->info = get_string('numviews', '', $numviews);
            $result->time = $lastlogtime;
        }
    }

        if ($useinternalreader) {
        $params = array('userid' => $userid, 'contextlevel' => CONTEXT_MODULE, 'contextinstanceid' => $cmid, 'crud' => 'r',
            'edulevel1' => core\event\base::LEVEL_PARTICIPATING, 'edulevel2' => core\event\base::LEVEL_TEACHING,
            'edulevel3' => core\event\base::LEVEL_OTHER, 'anonymous' => 0);
        $select = "SELECT COUNT(*) as count ";
        $from = "FROM {" . $logtable . "} ";
        $where = "WHERE userid = :userid
                    AND contextlevel = :contextlevel
                    AND contextinstanceid = :contextinstanceid
                    AND crud = :crud
                    AND edulevel IN (:edulevel1, :edulevel2, :edulevel3)
                    AND anonymous = :anonymous";
        if ($internalreadercount = $DB->count_records_sql($select . $from . $where, $params)) {
            if (!empty($numviews)) {
                $numviews = $numviews + $internalreadercount;
            } else {
                $numviews = $internalreadercount;
            }

                        $select = "SELECT MAX(timecreated) ";
            $lastlogtime = $DB->get_field_sql($select . $from . $where, $params);

            $result = new stdClass();
            $result->info = get_string('numviews', '', $numviews);
            $result->time = $lastlogtime;
        }
    }

    if (!empty($result)) {
        return $result;
    }

    return null;
}


function report_outline_user_complete($userid, $cmid, $module, $instanceid) {
    global $DB;

    list($uselegacyreader, $useinternalreader, $minloginternalreader, $logtable) = report_outline_get_common_log_variables();

        if ($uselegacyreader) {
                $params = array('userid' => $userid, 'module' => $module, 'action' => 'view', 'info' => $instanceid);
                        $limittime = '';
        if (!empty($minloginternalreader)) {
            $limittime = ' AND time < :timeto ';
            $params['timeto'] = $minloginternalreader;
        }
        $select = "SELECT COUNT(id) ";
        $from = "FROM {log} ";
        $where = "WHERE userid = :userid
                    AND module = :module
                    AND action = :action
                    AND info = :info ";
        if ($legacylogcount = $DB->count_records_sql($select . $from . $where . $limittime, $params)) {
            $numviews = $legacylogcount;

                        $select = "SELECT MAX(time) ";
            $lastlogtime = $DB->get_field_sql($select . $from . $where, $params);

            $strnumviews = get_string('numviews', '', $numviews);
        }
    }

        if ($useinternalreader) {
        $params = array('userid' => $userid, 'contextlevel' => CONTEXT_MODULE, 'contextinstanceid' => $cmid, 'crud' => 'r',
            'edulevel1' => core\event\base::LEVEL_PARTICIPATING, 'edulevel2' => core\event\base::LEVEL_TEACHING,
            'edulevel3' => core\event\base::LEVEL_OTHER, 'anonymous' => 0);
        $select = "SELECT COUNT(*) as count ";
        $from = "FROM {" . $logtable . "} ";
        $where = "WHERE userid = :userid
                    AND contextlevel = :contextlevel
                    AND contextinstanceid = :contextinstanceid
                    AND crud = :crud
                    AND edulevel IN (:edulevel1, :edulevel2, :edulevel3)
                    AND anonymous = :anonymous";
        if ($internalreadercount = $DB->count_records_sql($select . $from . $where, $params)) {
            if (!empty($numviews)) {
                $numviews = $numviews + $internalreadercount;
            } else {
                $numviews = $internalreadercount;
            }

                        $select = "SELECT MAX(timecreated) ";
            $lastlogtime = $DB->get_field_sql($select . $from . $where, $params);

            $strnumviews = get_string('numviews', '', $numviews);
        }
    }

    if (!empty($strnumviews) && (!empty($lastlogtime))) {
        return $strnumviews . ' - ' . get_string('mostrecently') . ' ' . userdate($lastlogtime);
    } else {
        return get_string('neverseen', 'report_outline');
    }
}
