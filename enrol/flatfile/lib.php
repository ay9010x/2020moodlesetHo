<?php



defined('MOODLE_INTERNAL') || die();



class enrol_flatfile_plugin extends enrol_plugin {
    protected $lasternoller = null;
    protected $lasternollercourseid = 0;

    
    public function roles_protected() {
        return false;
    }

    
    public function allow_unenrol(stdClass $instance) {
        return true;
    }

    
    public function allow_unenrol_user(stdClass $instance, stdClass $ue) {
        return true;
    }

    
    public function allow_manage(stdClass $instance) {
        return true;
    }

    
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/flatfile:manage', $context);
    }

    
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/flatfile:manage', $context);
    }

    
    public function get_user_enrolment_actions(course_enrolment_manager $manager, $ue) {
        $actions = array();
        $context = $manager->get_context();
        $instance = $ue->enrolmentinstance;
        $params = $manager->get_moodlepage()->url->params();
        $params['ue'] = $ue->id;
        if ($this->allow_unenrol_user($instance, $ue) && has_capability("enrol/flatfile:unenrol", $context)) {
            $url = new moodle_url('/enrol/unenroluser.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/delete', ''), get_string('unenrol', 'enrol'), $url, array('class'=>'unenrollink', 'rel'=>$ue->id));
        }
        if ($this->allow_manage($instance) && has_capability("enrol/flatfile:manage", $context)) {
            $url = new moodle_url('/enrol/editenrolment.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/edit', ''), get_string('edit'), $url, array('class'=>'editenrollink', 'rel'=>$ue->id));
        }
        return $actions;
    }

    
    public function enrol_user(stdClass $instance, $userid, $roleid = null, $timestart = 0, $timeend = 0, $status = null, $recovergrades = null) {
        parent::enrol_user($instance, $userid, null, $timestart, $timeend, $status, $recovergrades);
        if ($roleid) {
            $context = context_course::instance($instance->courseid, MUST_EXIST);
            role_assign($roleid, $userid, $context->id, 'enrol_'.$this->get_name(), $instance->id);
        }
    }

    
    public function sync(progress_trace $trace) {
        if (!enrol_is_enabled('flatfile')) {
            return 2;
        }

        $mailadmins = $this->get_config('mailadmins', 0);

        if ($mailadmins) {
            $buffer = new progress_trace_buffer(new text_progress_trace(), false);
            $trace = new combined_progress_trace(array($trace, $buffer));
        }

        $processed = false;

        $processed = $this->process_file($trace) || $processed;
        $processed = $this->process_buffer($trace) || $processed;
        $processed = $this->process_expirations($trace) || $processed;

        if ($processed and $mailadmins) {
            if ($log = $buffer->get_buffer()) {
                $eventdata = new stdClass();
                $eventdata->modulename        = 'moodle';
                $eventdata->component         = 'enrol_flatfile';
                $eventdata->name              = 'flatfile_enrolment';
                $eventdata->userfrom          = get_admin();
                $eventdata->userto            = get_admin();
                $eventdata->subject           = 'Flatfile Enrolment Log';
                $eventdata->fullmessage       = $log;
                $eventdata->fullmessageformat = FORMAT_PLAIN;
                $eventdata->fullmessagehtml   = '';
                $eventdata->smallmessage      = '';
                message_send($eventdata);
            }
            $buffer->reset_buffer();
        }

        return 0;
    }

    
    protected function obfuscate_filepath($filepath) {
        global $CFG;

        if (strpos($filepath, $CFG->dataroot.'/') === 0 or strpos($filepath, $CFG->dataroot.'\\') === 0) {
            $disclosefile = '$CFG->dataroot'.substr($filepath, strlen($CFG->dataroot));

        } else if (strpos($filepath, $CFG->dirroot.'/') === 0 or strpos($filepath, $CFG->dirroot.'\\') === 0) {
            $disclosefile = '$CFG->dirroot'.substr($filepath, strlen($CFG->dirroot));

        } else {
            $disclosefile = basename($filepath);
        }

        return $disclosefile;
    }

    
    protected function process_file(progress_trace $trace) {
        global $CFG, $DB;

                core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        $filelocation = $this->get_config('location');
        if (empty($filelocation)) {
                        $filelocation = "$CFG->dataroot/1/enrolments.txt";
        }
        $disclosefile = $this->obfuscate_filepath($filelocation);

        if (!file_exists($filelocation)) {
            $trace->output("Flatfile enrolments file not found: $disclosefile");
            $trace->finished();
            return false;
        }
        $trace->output("Processing flat file enrolments from: $disclosefile ...");

        $content = file_get_contents($filelocation);

        if ($content !== false) {

            $rolemap = $this->get_role_map($trace);

            $content = core_text::convert($content, $this->get_config('encoding', 'utf-8'), 'utf-8');
            $content = str_replace("\r", '', $content);
            $content = explode("\n", $content);

            $line = 0;
            foreach($content as $fields) {
                $line++;

                if (trim($fields) === '') {
                                        continue;
                }

                                if (strpos($fields, ',') !== false) {
                    $fields = explode(',', $fields);
                } else {
                    $fields = explode(';', $fields);
                }

                                if (count($fields) < 4 or count($fields) > 6) {
                    $trace->output("Line incorrectly formatted - ignoring $line", 1);
                    continue;
                }

                $fields[0] = trim(core_text::strtolower($fields[0]));
                $fields[1] = trim(core_text::strtolower($fields[1]));
                $fields[2] = trim($fields[2]);
                $fields[3] = trim($fields[3]);
                $fields[4] = isset($fields[4]) ? (int)trim($fields[4]) : 0;
                $fields[5] = isset($fields[5]) ? (int)trim($fields[5]) : 0;

                                if (strpos($fields[0], "'") === 0) {
                    foreach ($fields as $k=>$v) {
                        $fields[$k] = trim($v, "'");
                    }
                } else if (strpos($fields[0], '"') === 0) {
                    foreach ($fields as $k=>$v) {
                        $fields[$k] = trim($v, '"');
                    }
                }

                $trace->output("$line: $fields[0], $fields[1], $fields[2], $fields[3], $fields[4], $fields[5]", 1);

                                if ($fields[0] !== "add" and $fields[0] !== "del") {
                    $trace->output("Unknown operation in field 1 - ignoring line $line", 1);
                    continue;
                }

                                if (!isset($rolemap[$fields[1]])) {
                    $trace->output("Unknown role in field2 - ignoring line $line", 1);
                    continue;
                }
                $roleid = $rolemap[$fields[1]];

                if (empty($fields[2]) or !$user = $DB->get_record("user", array("idnumber"=>$fields[2], 'deleted'=>0))) {
                    $trace->output("Unknown user idnumber or deleted user in field 3 - ignoring line $line", 1);
                    continue;
                }

                if (!$course = $DB->get_record("course", array("idnumber"=>$fields[3]))) {
                    $trace->output("Unknown course idnumber in field 4 - ignoring line $line", 1);
                    continue;
                }

                if ($fields[4] > $fields[5] and $fields[5] != 0) {
                    $trace->output("Start time was later than end time - ignoring line $line", 1);
                    continue;
                }

                $this->process_records($trace, $fields[0], $roleid, $user, $course, $fields[4], $fields[5]);
            }

            unset($content);
        }

        if (!unlink($filelocation)) {
            $eventdata = new stdClass();
            $eventdata->modulename        = 'moodle';
            $eventdata->component         = 'enrol_flatfile';
            $eventdata->name              = 'flatfile_enrolment';
            $eventdata->userfrom          = get_admin();
            $eventdata->userto            = get_admin();
            $eventdata->subject           = get_string('filelockedmailsubject', 'enrol_flatfile');
            $eventdata->fullmessage       = get_string('filelockedmail', 'enrol_flatfile', $filelocation);
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml   = '';
            $eventdata->smallmessage      = '';
            message_send($eventdata);
            $trace->output("Error deleting enrolment file: $disclosefile", 1);
        } else {
            $trace->output("Deleted enrolment file", 1);
        }

        $trace->output("...finished enrolment file processing.");
        $trace->finished();

        return true;
    }

    
    protected function process_buffer(progress_trace $trace) {
        global $DB;

        if (!$future_enrols = $DB->get_records_select('enrol_flatfile', "timestart < ?", array(time()))) {
            $trace->output("No enrolments to be processed in flatfile buffer");
            $trace->finished();
            return false;
        }

        $trace->output("Starting processing of flatfile buffer");
        foreach($future_enrols as $en) {
            $user = $DB->get_record('user', array('id'=>$en->userid));
            $course = $DB->get_record('course', array('id'=>$en->courseid));
            if ($user and $course) {
                $trace->output("buffer: $en->action $en->roleid $user->id $course->id $en->timestart $en->timeend", 1);
                $this->process_records($trace, $en->action, $en->roleid, $user, $course, $en->timestart, $en->timeend, false);
            }
            $DB->delete_records('enrol_flatfile', array('id'=>$en->id));
        }
        $trace->output("Finished processing of flatfile buffer");
        $trace->finished();

        return true;
    }

    
    protected function process_records(progress_trace $trace, $action, $roleid, $user, $course, $timestart, $timeend, $buffer_if_future = true) {
        global $CFG, $DB;

                if ($timestart > time() and $buffer_if_future) {
                                    $future_en = new stdClass();
            $future_en->action       = $action;
            $future_en->roleid       = $roleid;
            $future_en->userid       = $user->id;
            $future_en->courseid     = $course->id;
            $future_en->timestart    = $timestart;
            $future_en->timeend      = $timeend;
            $future_en->timemodified = time();
            $DB->insert_record('enrol_flatfile', $future_en);
            $trace->output("User $user->id will be enrolled later into course $course->id using role $roleid ($timestart, $timeend)", 1);
            return;
        }

        $context = context_course::instance($course->id);

        if ($action === 'add') {
                        $DB->delete_records('enrol_flatfile', array('userid'=>$user->id, 'courseid'=>$course->id, 'roleid'=>$roleid));

            $instance = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'flatfile'));
            if (empty($instance)) {
                                $enrolid = $this->add_instance($course);
                $instance = $DB->get_record('enrol', array('id' => $enrolid));
            }

            $notify = false;
            if ($ue = $DB->get_record('user_enrolments', array('enrolid'=>$instance->id, 'userid'=>$user->id))) {
                                $this->update_user_enrol($instance, $user->id, ENROL_USER_ACTIVE, $timestart, $timeend);
                if (!$DB->record_exists('role_assignments', array('contextid'=>$context->id, 'roleid'=>$roleid, 'userid'=>$user->id, 'component'=>'enrol_flatfile', 'itemid'=>$instance->id))) {
                    role_assign($roleid, $user->id, $context->id, 'enrol_flatfile', $instance->id);
                }
                $trace->output("User $user->id enrolment updated in course $course->id using role $roleid ($timestart, $timeend)", 1);

            } else {
                                $this->enrol_user($instance, $user->id, $roleid, $timestart, $timeend);
                $trace->output("User $user->id enrolled in course $course->id using role $roleid ($timestart, $timeend)", 1);
                $notify = true;
            }

            if ($notify and $this->get_config('mailstudents')) {
                $oldforcelang = force_current_language($user->lang);

                                $a = new stdClass();
                $a->coursename = format_string($course->fullname, true, array('context' => $context));
                $a->profileurl = "$CFG->wwwroot/user/view.php?id=$user->id&amp;course=$course->id";
                $subject = get_string('enrolmentnew', 'enrol', format_string($course->shortname, true, array('context' => $context)));

                $eventdata = new stdClass();
                $eventdata->modulename        = 'moodle';
                $eventdata->component         = 'enrol_flatfile';
                $eventdata->name              = 'flatfile_enrolment';
                $eventdata->userfrom          = $this->get_enroller($course->id);
                $eventdata->userto            = $user;
                $eventdata->subject           = $subject;
                $eventdata->fullmessage       = get_string('welcometocoursetext', '', $a);
                $eventdata->fullmessageformat = FORMAT_PLAIN;
                $eventdata->fullmessagehtml   = '';
                $eventdata->smallmessage      = '';
                if (message_send($eventdata)) {
                    $trace->output("Notified enrolled user", 1);
                } else {
                    $trace->output("Failed to notify enrolled user", 1);
                }

                force_current_language($oldforcelang);
            }

            if ($notify and $this->get_config('mailteachers', 0)) {
                                $enroller = $this->get_enroller($course->id);

                $oldforcelang = force_current_language($enroller->lang);

                $a = new stdClass();
                $a->course = format_string($course->fullname, true, array('context' => $context));
                $a->user = fullname($user);
                $subject = get_string('enrolmentnew', 'enrol', format_string($course->shortname, true, array('context' => $context)));

                $eventdata = new stdClass();
                $eventdata->modulename        = 'moodle';
                $eventdata->component         = 'enrol_flatfile';
                $eventdata->name              = 'flatfile_enrolment';
                $eventdata->userfrom          = get_admin();
                $eventdata->userto            = $enroller;
                $eventdata->subject           = $subject;
                $eventdata->fullmessage       = get_string('enrolmentnewuser', 'enrol', $a);
                $eventdata->fullmessageformat = FORMAT_PLAIN;
                $eventdata->fullmessagehtml   = '';
                $eventdata->smallmessage      = '';
                if (message_send($eventdata)) {
                    $trace->output("Notified enroller {$eventdata->userto->id}", 1);
                } else {
                    $trace->output("Failed to notify enroller {$eventdata->userto->id}", 1);
                }

                force_current_language($oldforcelang);
            }
            return;

        } else if ($action === 'del') {
                        $DB->delete_records('enrol_flatfile', array('userid'=>$user->id, 'courseid'=>$course->id, 'roleid'=>$roleid));

            $action = $this->get_config('unenrolaction');
            if ($action == ENROL_EXT_REMOVED_KEEP) {
                $trace->output("del action is ignored", 1);
                return;
            }

                        $instances = $DB->get_records('enrol', array('courseid' => $course->id));
            $unenrolled = false;
            foreach ($instances as $instance) {
                if (!$ue = $DB->get_record('user_enrolments', array('enrolid'=>$instance->id, 'userid'=>$user->id))) {
                    continue;
                }
                if ($instance->enrol === 'flatfile') {
                    $plugin = $this;
                } else {
                    if (!enrol_is_enabled($instance->enrol)) {
                        continue;
                    }
                    if (!$plugin = enrol_get_plugin($instance->enrol)) {
                        continue;
                    }
                    if (!$plugin->allow_unenrol_user($instance, $ue)) {
                        continue;
                    }
                }

                                $componentroles = array();
                $manualroles = array();
                $ras = $DB->get_records('role_assignments', array('userid'=>$user->id, 'contextid'=>$context->id));
                foreach ($ras as $ra) {
                    if ($ra->component === '') {
                        $manualroles[$ra->roleid] = $ra->roleid;
                    } else if ($ra->component === 'enrol_'.$instance->enrol and $ra->itemid == $instance->id) {
                        $componentroles[$ra->roleid] = $ra->roleid;
                    }
                }

                if ($componentroles and !isset($componentroles[$roleid])) {
                                        continue;

                } else if (empty($ras)) {
                    
                } else if (!$plugin->roles_protected()) {
                    if (!$componentroles and $manualroles and !isset($manualroles[$roleid])) {
                                                continue;
                    }
                }

                if ($action == ENROL_EXT_REMOVED_UNENROL) {
                    $unenrolled = true;
                    if (!$plugin->roles_protected()) {
                        role_unassign_all(array('contextid'=>$context->id, 'userid'=>$user->id, 'roleid'=>$roleid, 'component'=>'', 'itemid'=>0), true);
                    }
                    $plugin->unenrol_user($instance, $user->id);
                    $trace->output("User $user->id was unenrolled from course $course->id (enrol_$instance->enrol)", 1);

                } else if ($action == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                    if ($plugin->allow_manage($instance)) {
                        if ($ue->status == ENROL_USER_ACTIVE) {
                            $unenrolled = true;
                            $plugin->update_user_enrol($instance, $user->id, ENROL_USER_SUSPENDED);
                            if (!$plugin->roles_protected()) {
                                role_unassign_all(array('contextid'=>$context->id, 'userid'=>$user->id, 'component'=>'enrol_'.$instance->enrol, 'itemid'=>$instance->id), true);
                                role_unassign_all(array('contextid'=>$context->id, 'userid'=>$user->id, 'roleid'=>$roleid, 'component'=>'', 'itemid'=>0), true);
                            }
                            $trace->output("User $user->id enrolment was suspended in course $course->id (enrol_$instance->enrol)", 1);
                        }
                    }
                }
            }

            if (!$unenrolled) {
                if (0 == $DB->count_records('role_assignments', array('userid'=>$user->id, 'contextid'=>$context->id))) {
                    role_unassign_all(array('contextid'=>$context->id, 'userid'=>$user->id, 'component'=>'', 'itemid'=>0), true);
                }
                $trace->output("User $user->id (with role $roleid) not unenrolled from course $course->id", 1);
            }

            return;
        }
    }

    
    protected function get_enroller($courseid) {
        if ($this->lasternollercourseid == $courseid and $this->lasternoller) {
            return $this->lasternoller;
        }

        $context = context_course::instance($courseid);

        $users = get_enrolled_users($context, 'enrol/flatfile:manage');
        if (!$users) {
            $users = get_enrolled_users($context, 'moodle/role:assign');
        }

        if ($users) {
            $users = sort_by_roleassignment_authority($users, $context);
            $this->lasternoller = reset($users);
            unset($users);
        } else {
            $this->lasternoller = get_admin();
        }

        $this->lasternollercourseid == $courseid;

        return $this->lasternoller;
    }

    
    protected function get_role_map(progress_trace $trace) {
        global $DB;

                $rolemap = array();
        $roles = $DB->get_records('role', null, '', 'id, name, shortname');
        foreach ($roles as $id=>$role) {
            $alias = $this->get_config('map_'.$id, $role->shortname, '');
            $alias = trim(core_text::strtolower($alias));
            if ($alias === '') {
                                continue;
            }
            if (isset($rolemap[$alias])) {
                $trace->output("Duplicate role alias $alias detected!");
            } else {
                $rolemap[$alias] = $id;
            }
        }

        return $rolemap;
    }

    
    public function restore_instance(restore_enrolments_structure_step $step, stdClass $data, $course, $oldid) {
        global $DB;

        if ($instance = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>$this->get_name()))) {
            $instanceid = $instance->id;
        } else {
            $instanceid = $this->add_instance($course);
        }
        $step->set_mapping('enrol', $oldid, $instanceid);
    }

    
    public function restore_user_enrolment(restore_enrolments_structure_step $step, $data, $instance, $userid, $oldinstancestatus) {
        $this->enrol_user($instance, $userid, null, $data->timestart, $data->timeend, $data->status);
    }

    
    public function restore_role_assignment($instance, $roleid, $userid, $contextid) {
        role_assign($roleid, $userid, $contextid, 'enrol_'.$instance->enrol, $instance->id);
    }
}
