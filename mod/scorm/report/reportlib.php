<?php



defined('MOODLE_INTERNAL') || die();


function scorm_report_list($context) {
    global $CFG;
    static $reportlist;
    if (!empty($reportlist)) {
        return $reportlist;
    }
    $installed = core_component::get_plugin_list('scormreport');
    foreach ($installed as $reportname => $notused) {

                $classname = "scormreport_$reportname\\report";
        if (class_exists($classname)) {
            $report = new $classname();

            if ($report->canview($context)) {
                $reportlist[] = $reportname;
            }
            continue;
        }

                $pluginfile = $CFG->dirroot.'/mod/scorm/report/'.$reportname.'/report.php';
        if (is_readable($pluginfile)) {
            debugging("Please use autoloaded classnames for your plugin. Refer MDL-46469 for details", DEBUG_DEVELOPER);
            include_once($pluginfile);
            $reportclassname = "scorm_{$reportname}_report";
            if (class_exists($reportclassname)) {
                $report = new $reportclassname();

                if ($report->canview($context)) {
                    $reportlist[] = $reportname;
                }
            }
        }
    }
    return $reportlist;
}

function get_scorm_question_count($scormid) {
    global $DB;
    $count = 0;
    $params = array();
    $select = "scormid = ? AND ";
    $select .= $DB->sql_like("element", "?", false);
    $params[] = $scormid;
    $params[] = "cmi.interactions_%.id";
    $rs = $DB->get_recordset_select("scorm_scoes_track", $select, $params, 'element');
    $keywords = array("cmi.interactions_", ".id");
    if ($rs->valid()) {
        foreach ($rs as $record) {
            $num = trim(str_ireplace($keywords, '', $record->element));
            if (is_numeric($num) && $num > $count) {
                $count = $num;
            }
        }
                $count++;
    }
    $rs->close();     return $count;
}
