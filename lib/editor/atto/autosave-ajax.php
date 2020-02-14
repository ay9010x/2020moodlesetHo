<?php



define('AJAX_SCRIPT', true);

require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir . '/filestorage/file_storage.php');

$actions = array_map(function($actionparams) {
    $action = isset($actionparams['action']) ? $actionparams['action'] : null;
    $params = [];
    $keys = [
        'action' => PARAM_ALPHA,
        'contextid' => PARAM_INT,
        'elementid' => PARAM_ALPHANUMEXT,
        'pagehash' => PARAM_ALPHANUMEXT,
        'pageinstance' => PARAM_ALPHANUMEXT
    ];

    if ($action == 'save') {
        $keys['drafttext'] = PARAM_RAW;
    } else if ($action == 'resume') {
        $keys['draftid'] = PARAM_INT;
    }

    foreach ($keys as $key => $type) {
                if (!isset($actionparams[$key])) {
            print_error('missingparam', '', '', $key);
        }
        $params[$key] = clean_param($actionparams[$key], $type);
    }

    return $params;
}, isset($_REQUEST['actions']) ? $_REQUEST['actions'] : []);

$now = time();
$before = $now - 60*60*24*4;

$context = context_system::instance();
$PAGE->set_url('/lib/editor/atto/autosave-ajax.php');
$PAGE->set_context($context);

require_login();
if (isguestuser()) {
    print_error('accessdenied', 'admin');
}
require_sesskey();

if (!in_array('atto', explode(',', get_config('core', 'texteditors')))) {
    print_error('accessdenied', 'admin');
}

$responses = array();
foreach ($actions as $actionparams) {

    $action = $actionparams['action'];
    $contextid = $actionparams['contextid'];
    $elementid = $actionparams['elementid'];
    $pagehash = $actionparams['pagehash'];
    $pageinstance = $actionparams['pageinstance'];

    if ($action === 'save') {
        $drafttext = $actionparams['drafttext'];
        $params = array('elementid' => $elementid,
                        'userid' => $USER->id,
                        'pagehash' => $pagehash,
                        'contextid' => $contextid);

        $record = $DB->get_record('editor_atto_autosave', $params);
        if ($record && $record->pageinstance != $pageinstance) {
            print_error('concurrent access from the same user is not supported');
            die();
        }

        if (!$record) {
            $record = new stdClass();
            $record->elementid = $elementid;
            $record->userid = $USER->id;
            $record->pagehash = $pagehash;
            $record->contextid = $contextid;
            $record->drafttext = $drafttext;
            $record->pageinstance = $pageinstance;
            $record->timemodified = $now;

            $DB->insert_record('editor_atto_autosave', $record);

                        $responses[] = null;
            continue;
        } else {
            $record->drafttext = $drafttext;
            $record->timemodified = time();
            $DB->update_record('editor_atto_autosave', $record);

                        $responses[] = null;
            continue;
        }

    } else if ($action == 'resume') {
        $params = array('elementid' => $elementid,
                        'userid' => $USER->id,
                        'pagehash' => $pagehash,
                        'contextid' => $contextid);

        $newdraftid = $actionparams['draftid'];

        $record = $DB->get_record('editor_atto_autosave', $params);

        if (!$record) {
            $record = new stdClass();
            $record->elementid = $elementid;
            $record->userid = $USER->id;
            $record->pagehash = $pagehash;
            $record->contextid = $contextid;
            $record->pageinstance = $pageinstance;
            $record->pagehash = $pagehash;
            $record->draftid = $newdraftid;
            $record->timemodified = time();
            $record->drafttext = '';

            $DB->insert_record('editor_atto_autosave', $record);

                        $responses[] = null;
            continue;

        } else {
                        $usercontext = context_user::instance($USER->id);
            $stale = $record->timemodified < $before;
            require_once($CFG->libdir . '/filelib.php');

            $fs = get_file_storage();
            $files = $fs->get_directory_files($usercontext->id, 'user', 'draft', $newdraftid, '/', true, true);

            $lastfilemodified = 0;
            foreach ($files as $file) {
                $lastfilemodified = max($lastfilemodified, $file->get_timemodified());
            }
            if ($record->timemodified < $lastfilemodified) {
                $stale = true;
            }

            if (!$stale) {
                                                $newdrafttext = file_save_draft_area_files($record->draftid,
                                                           $usercontext->id,
                                                           'user',
                                                           'draft',
                                                           $newdraftid,
                                                           array(),
                                                           $record->drafttext);

                                $newdrafttext = file_rewrite_pluginfile_urls($newdrafttext,
                                                             'draftfile.php',
                                                             $usercontext->id,
                                                             'user',
                                                             'draft',
                                                             $newdraftid);
                $record->drafttext = $newdrafttext;

                $record->pageinstance = $pageinstance;
                $record->draftid = $newdraftid;
                $record->timemodified = time();
                $DB->update_record('editor_atto_autosave', $record);

                                $response = ['result' => $record->drafttext];
                $responses[] = $response;

            } else {
                $DB->delete_records('editor_atto_autosave', array('id' => $record->id));

                                $responses[] = null;
            }
            continue;
        }

    } else if ($action == 'reset') {
        $params = array('elementid' => $elementid,
                        'userid' => $USER->id,
                        'pagehash' => $pagehash,
                        'contextid' => $contextid);

        $DB->delete_records('editor_atto_autosave', $params);
        $responses[] = null;
        continue;
    }
}

echo json_encode($responses);
