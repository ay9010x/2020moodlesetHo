<?php



require_once($CFG->libdir.'/formslib.php');


class mod_attendance_export_form extends moodleform {

    
    public function definition() {
        global $USER, $DB, $PAGE;
        $mform    =& $this->_form;
        $course        = $this->_customdata['course'];
        $cm            = $this->_customdata['cm'];
        $modcontext    = $this->_customdata['modcontext'];

        $mform->addElement('header', 'general', get_string('export', 'attendance'));

        $groupmode = groups_get_activity_groupmode($cm, $course);
        $groups = groups_get_activity_allowed_groups($cm, $USER->id);
        if ($groupmode == VISIBLEGROUPS or has_capability('moodle/site:accessallgroups', $modcontext)) {
            $grouplist[0] = get_string('allparticipants');
        }
        if ($groups) {
            foreach ($groups as $group) {
                $grouplist[$group->id] = $group->name;
            }
        }

                $namefields = get_all_user_name_fields(true, 'u');
        $allusers = get_enrolled_users($modcontext, 'mod/attendance:canbelisted', 0, 'u.id,'.$namefields);
        $userlist = array();
        foreach ($allusers as $user) {
            $userlist[$user->id] = fullname($user);
        }
        unset($allusers);
        $tempusers = $DB->get_records('attendance_tempusers', array('courseid' => $course->id), 'studentid, fullname');
        foreach ($tempusers as $user) {
            $userlist[$user->studentid] = $user->fullname;
        }
        if (empty($userlist)) {
            $mform->addElement('static', 'nousers', '', get_string('noattendanceusers', 'attendance'));
            return;
        }

        list($gsql, $gparams) = $DB->get_in_or_equal(array_keys($grouplist), SQL_PARAMS_NAMED);
        list($usql, $uparams) = $DB->get_in_or_equal(array_keys($userlist), SQL_PARAMS_NAMED);
        $params = array_merge($gparams, $uparams);
        $groupmembers = $DB->get_recordset_select('groups_members', "groupid {$gsql} AND userid {$usql}", $params,
                                                  '', 'groupid, userid');
        $groupmappings = array();
        foreach ($groupmembers as $groupmember) {
            if (!isset($groupmappings[$groupmember->groupid])) {
                $groupmappings[$groupmember->groupid] = array();
            }
            $groupmappings[$groupmember->groupid][$groupmember->userid] = $userlist[$groupmember->userid];
        }
        if (isset($grouplist[0])) {
            $groupmappings[0] = $userlist;
        }

        $mform->addElement('select', 'group', get_string('group'), $grouplist);

        $mform->addElement('selectyesno', 'selectedusers', get_string('onlyselectedusers', 'mod_attendance'));
        $sel = $mform->addElement('select', 'users', get_string('users', 'mod_attendance'), $userlist, array('size' => 12));
        $sel->setMultiple(true);
        $mform->disabledIf('users', 'selectedusers', 'eq', 0);

        $opts = array('groupmappings' => $groupmappings);
        $PAGE->requires->yui_module('moodle-mod_attendance-groupfilter', 'M.mod_attendance.groupfilter.init', array($opts));

        $ident = array();
        $ident[] =& $mform->createElement('checkbox', 'id', '', get_string('studentid', 'attendance'));
        $ident[] =& $mform->createElement('checkbox', 'uname', '', get_string('username'));

        $optional = array('idnumber', 'institution', 'department');
        foreach ($optional as $opt) {
            $ident[] =& $mform->createElement('checkbox', $opt, '', get_string($opt));
            $mform->setType($opt, PARAM_NOTAGS);
        }

        $mform->addGroup($ident, 'ident', get_string('identifyby', 'attendance'), array('<br />'), true);
        $mform->setDefaults(array('ident[id]' => true, 'ident[uname]' => true));
        $mform->setType('id', PARAM_INT);
        $mform->setType('uname', PARAM_INT);

        $mform->addElement('checkbox', 'includeallsessions', get_string('includeall', 'attendance'), get_string('yes'));
        $mform->setDefault('includeallsessions', true);
        $mform->addElement('checkbox', 'includenottaken', get_string('includenottaken', 'attendance'), get_string('yes'));
        $mform->addElement('checkbox', 'includeremarks', get_string('includeremarks', 'attendance'), get_string('yes'));
        $mform->addElement('date_selector', 'sessionstartdate', get_string('startofperiod', 'attendance'));
        $mform->setDefault('sessionstartdate', $course->startdate);
        $mform->disabledIf('sessionstartdate', 'includeallsessions', 'checked');
        $mform->addElement('date_selector', 'sessionenddate', get_string('endofperiod', 'attendance'));
        $mform->disabledIf('sessionenddate', 'includeallsessions', 'checked');

        $formatoptions = array('excel' => get_string('downloadexcel', 'attendance'),
                               'ooo' => get_string('downloadooo', 'attendance'),
                               'text' => get_string('downloadtext', 'attendance'));
        $mform->addElement('select', 'format', get_string('format'), $formatoptions);

        $submitstring = get_string('ok');
        $this->add_action_buttons(false, $submitstring);

        $mform->addElement('hidden', 'id', $cm->id);
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

                if ($data['selectedusers'] && empty($data['users'])) {
            $errors['users'] = get_string('mustselectusers', 'mod_attendance');
        }

        return $errors;
    }
}

