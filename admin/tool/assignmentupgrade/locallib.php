<?php



defined('MOODLE_INTERNAL') || die();


function tool_assignmentupgrade_url($script, $params = array()) {
    return new moodle_url('/admin/tool/assignmentupgrade/' . $script . '.php', $params);
}


class tool_assignmentupgrade_batchoperationconfirm implements renderable {
    
    public $continuemessage = '';
    
    public $continueurl;

    
    public function __construct($data) {
        if (isset($data->upgradeselected)) {
            $this->continuemessage = get_string('upgradeselectedcount',
                                                'tool_assignmentupgrade',
                                                count(explode(',', $data->selectedassignments)));
            $urlparams = array('upgradeselected'=>'1',
                               'confirm'=>'1',
                               'sesskey'=>sesskey(),
                               'selected'=>$data->selectedassignments);
            $this->continueurl = new moodle_url('/admin/tool/assignmentupgrade/batchupgrade.php', $urlparams);
        } else if (isset($data->upgradeall)) {
            if (!tool_assignmentupgrade_any_upgradable_assignments()) {
                $this->continuemessage = get_string('noassignmentstoupgrade', 'tool_assignmentupgrade');
                $this->continueurl = '';
            } else {
                $this->continuemessage = get_string('upgradeallconfirm', 'tool_assignmentupgrade');
                $urlparams = array('upgradeall'=>'1', 'confirm'=>'1', 'sesskey'=>sesskey());
                $this->continueurl = new moodle_url('/admin/tool/assignmentupgrade/batchupgrade.php', $urlparams);
            }
        }
    }
}



class tool_assignmentupgrade_action {
    
    public $name;
    
    public $url;
    
    public $description;

    
    protected function __construct($name, moodle_url $url, $description) {
        $this->name = $name;
        $this->url = $url;
        $this->description = $description;
    }

    
    public static function make($shortname, $params = array()) {
        return new self(
                get_string($shortname, 'tool_assignmentupgrade'),
                tool_assignmentupgrade_url($shortname, $params),
                get_string($shortname . '_desc', 'tool_assignmentupgrade'));
    }
}


function tool_assignmentupgrade_any_upgradable_assignments() {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/mod/assign/locallib.php');
        $types = $DB->get_records_sql('SELECT plugin AS assignmenttype,
                                          value AS version
                                   FROM {config_plugins}
                                   WHERE
                                       name = ? AND
                                       plugin LIKE ?', array('version', 'assignment_%'));

    $upgradabletypes = array();

    foreach ($types as $assignment) {
        $shorttype = substr($assignment->assignmenttype, strlen('assignment_'));
        if (assign::can_upgrade_assignment($shorttype, $assignment->version)) {
            $upgradabletypes[] = $shorttype;
        }
    }
    list($sql, $params) = $DB->get_in_or_equal($upgradabletypes);

    $count = $DB->count_records_sql('SELECT COUNT(id) FROM {assignment} WHERE assignmenttype ' . $sql, $params);

    return $count > 0;
}


function tool_assignmentupgrade_load_all_upgradable_assignmentids() {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/mod/assign/locallib.php');
        $types = $DB->get_records_sql('SELECT
                                       plugin AS assignmenttype,
                                       value AS version
                                   FROM {config_plugins}
                                   WHERE
                                       name = ? AND
                                       plugin LIKE ?', array('version', 'assignment_%'));

    $upgradabletypes = array();

    foreach ($types as $assignment) {
        $shorttype = substr($assignment->assignmenttype, strlen('assignment_'));
        if (assign::can_upgrade_assignment($shorttype, $assignment->version)) {
            $upgradabletypes[] = $shorttype;
        }
    }

    list($sql, $params) = $DB->get_in_or_equal($upgradabletypes);

    $records = $DB->get_records_sql('SELECT id from {assignment} where assignmenttype ' . $sql, $params);
    $ids = array();
    foreach ($records as $record) {
        $ids[] = $record->id;
    }

    return $ids;
}



function tool_assignmentupgrade_upgrade_assignment($assignmentid) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/assign/upgradelib.php');

    $assignment_upgrader = new assign_upgrade_manager();
    $info = tool_assignmentupgrade_get_assignment($assignmentid);
    if ($info) {
        $log = '';
        $success = $assignment_upgrader->upgrade_assignment($assignmentid, $log);
    } else {
        $success = false;
        $log = get_string('assignmentnotfound', 'tool_assignmentupgrade', $assignmentid);
        $info = new stdClass();
        $info->name = get_string('unknown', 'tool_assignmentupgrade');
        $info->shortname = get_string('unknown', 'tool_assignmentupgrade');
    }

    return array($info, $success, $log);
}


function tool_assignmentupgrade_get_assignment($assignmentid) {
    global $DB;
    return $DB->get_record_sql("
            SELECT a.id, a.name, c.shortname, c.id AS courseid
            FROM {assignment} a
            JOIN {course} c ON c.id = a.course
            WHERE a.id = ?", array($assignmentid));
}

