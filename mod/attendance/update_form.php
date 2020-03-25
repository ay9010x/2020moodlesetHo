<?php




require_once($CFG->libdir.'/formslib.php');


class mod_attendance_update_form extends moodleform {

    
    public function definition() {

        global $DB;
        $mform    =& $this->_form;

        $modcontext    = $this->_customdata['modcontext'];
        $sessionid     = $this->_customdata['sessionid'];

        if (!$sess = $DB->get_record('attendance_sessions', array('id' => $sessionid) )) {
            error('No such session in this course');
        }
        $defopts = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $modcontext);
        $sess = file_prepare_standard_editor($sess, 'description', $defopts, $modcontext, 'mod_attendance', 'session', $sess->id);

        $starttime = $sess->sessdate - usergetmidnight($sess->sessdate);
        $starthour = floor($starttime / HOURSECS);
        $startminute = floor(($starttime - $starthour * HOURSECS) / MINSECS);

        $enddate = $sess->sessdate + $sess->duration;
        $endtime = $enddate - usergetmidnight($enddate);
        $endhour = floor($endtime / HOURSECS);
        $endminute = floor(($endtime - $endhour * HOURSECS) / MINSECS);

        $sesendtime = $sess->sessdate + $sess->duration;
        $data = array('sessiondate' => $sess->sessdate,
                'sestime' => array('starthour' => $starthour, 'startminute' => $startminute,
                                   'endhour' => $endhour, 'endminute' => $endminute),
                'sdescription' => $sess->description_editor);

        $mform->addElement('header', 'general', get_string('changesession', 'attendance'));

        if ($sess->groupid == 0) {
            $strtype = get_string('commonsession', 'attendance');
        } else {
            $groupname = $DB->get_field('groups', 'name', array('id' => $sess->groupid));
            $strtype = get_string('group') . ': ' . $groupname;
        }
        $mform->addElement('static', 'sessiontypedescription', get_string('sessiontype', 'attendance'), $strtype);

        $olddate = construct_session_full_date_time($sess->sessdate, $sess->duration);
        $mform->addElement('static', 'olddate', get_string('olddate', 'attendance'), $olddate);

        attendance_form_sessiondate_selector($mform);

                $maxstatusset = attendance_get_max_statusset($this->_customdata['att']->id);
        if ($maxstatusset > 0) {
            $mform->addElement('static', 'statusset', get_string('usestatusset', 'mod_attendance'),
                attendance_get_setname($this->_customdata['att']->id, $sess->statusset));
        }

        $mform->addElement('editor', 'sdescription', get_string('description', 'attendance'),
                           array('rows' => 1, 'columns' => 80), $defopts);
        $mform->setType('sdescription', PARAM_RAW);

        $mform->setDefaults($data);

        $this->add_action_buttons(true);
    }

    
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $sesstarttime = $data['sestime']['starthour'] * HOURSECS + $data['sestime']['startminute'] * MINSECS;
        $sesendtime = $data['sestime']['endhour'] * HOURSECS + $data['sestime']['endminute'] * MINSECS;
        if ($sesendtime < $sesstarttime) {
            $errors['sestime'] = get_string('invalidsessionendtime', 'attendance');
        }

        return $errors;
    }
}
