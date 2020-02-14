<?php


define('AJAX_SCRIPT', true);

require_once(dirname(__FILE__) . "/../../config.php");
require_once($CFG->dirroot . '/mod/lti/locallib.php');

$courseid = required_param('course', PARAM_INT);
$context = context_course::instance($courseid);

require_login($courseid, false);

$action = required_param('action', PARAM_TEXT);

$response = new stdClass();

switch ($action) {
    case 'find_tool_config':
        $toolurl = required_param('toolurl', PARAM_RAW);
        $toolid = optional_param('toolid', 0, PARAM_INT);

        require_capability('moodle/course:manageactivities', $context);
        require_capability('mod/lti:addinstance', $context);

        if (!empty($toolurl) && lti_is_cartridge($toolurl)) {
            $response->cartridge = true;
        } else {
            if (empty($toolid) && !empty($toolurl)) {
                $tool = lti_get_tool_by_url_match($toolurl, $courseid);

                if (!empty($tool)) {
                    $toolid = $tool->id;

                    $response->toolid = $tool->id;
                    $response->toolname = s($tool->name);
                    $response->tooldomain = s($tool->tooldomain);
                }
            } else {
                $response->toolid = $toolid;
            }

            if (!empty($toolid)) {
                                $query = '
                    SELECT name, value
                    FROM {lti_types_config}
                    WHERE
                        typeid = :typeid
                    AND name IN (\'sendname\', \'sendemailaddr\', \'acceptgrades\')
                ';

                $privacyconfigs = $DB->get_records_sql($query, array('typeid' => $toolid));
                $success = count($privacyconfigs) > 0;
                foreach ($privacyconfigs as $config) {
                    $configname = $config->name;
                    $response->$configname = $config->value;
                }
                if (!$success) {
                    $response->error = s(get_string('tool_config_not_found', 'mod_lti'));
                }
            }
        }

        break;
}
echo $OUTPUT->header();
echo json_encode($response);

die;
