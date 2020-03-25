<?php



defined('MOODLE_INTERNAL') || die();


function xmldb_tool_log_install() {
    global $CFG, $DB;

    $enabled = array();

        if (file_exists("$CFG->dirroot/$CFG->admin/tool/log/store/standard")) {
        $enabled[] = 'logstore_standard';
    }

        if (file_exists("$CFG->dirroot/$CFG->admin/tool/log/store/legacy")) {
        unset_config('loglegacy', 'logstore_legacy');
                        $params = array('yesterday' => time() - 60*60*24);
        if ($DB->record_exists_select('log', "time < :yesterday", $params)) {
            $enabled[] = 'logstore_legacy';
        }
    }

    set_config('enabled_stores', implode(',', $enabled), 'tool_log');
}
