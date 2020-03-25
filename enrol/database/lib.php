<?php



defined('MOODLE_INTERNAL') || die();


class enrol_database_plugin extends enrol_plugin {
    
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        if (!has_capability('enrol/database:config', $context)) {
            return false;
        }
        if (!enrol_is_enabled('database')) {
            return true;
        }
        if (!$this->get_config('dbtype') or !$this->get_config('remoteenroltable') or !$this->get_config('remotecoursefield') or !$this->get_config('remoteuserfield')) {
            return true;
        }

                return false;
    }

    
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/database:config', $context);
    }

    
    public function allow_unenrol_user(stdClass $instance, stdClass $ue) {
        if ($ue->status == ENROL_USER_SUSPENDED) {
            return true;
        }

        return false;
    }

    
    public function get_user_enrolment_actions(course_enrolment_manager $manager, $ue) {
        $actions = array();
        $context = $manager->get_context();
        $instance = $ue->enrolmentinstance;
        $params = $manager->get_moodlepage()->url->params();
        $params['ue'] = $ue->id;
        if ($this->allow_unenrol_user($instance, $ue) && has_capability('enrol/database:unenrol', $context)) {
            $url = new moodle_url('/enrol/unenroluser.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/delete', ''), get_string('unenrol', 'enrol'), $url, array('class'=>'unenrollink', 'rel'=>$ue->id));
        }
        return $actions;
    }

    
    public function sync_user_enrolments($user) {
        global $CFG, $DB;

                if (!$this->get_config('dbtype') or !$this->get_config('remoteenroltable') or !$this->get_config('remotecoursefield') or !$this->get_config('remoteuserfield')) {
            return;
        }

        $table            = $this->get_config('remoteenroltable');
        $coursefield      = trim($this->get_config('remotecoursefield'));
        $userfield        = trim($this->get_config('remoteuserfield'));
        $rolefield        = trim($this->get_config('remoterolefield'));
        $otheruserfield   = trim($this->get_config('remoteotheruserfield'));

                $coursefield_l    = strtolower($coursefield);
        $userfield_l      = strtolower($userfield);
        $rolefield_l      = strtolower($rolefield);
        $otheruserfieldlower = strtolower($otheruserfield);

        $localrolefield   = $this->get_config('localrolefield');
        $localuserfield   = $this->get_config('localuserfield');
        $localcoursefield = $this->get_config('localcoursefield');

        $unenrolaction    = $this->get_config('unenrolaction');
        $defaultrole      = $this->get_config('defaultrole');

        $ignorehidden     = $this->get_config('ignorehiddencourses');

        if (!is_object($user) or !property_exists($user, 'id')) {
            throw new coding_exception('Invalid $user parameter in sync_user_enrolments()');
        }

        if (!property_exists($user, $localuserfield)) {
            debugging('Invalid $user parameter in sync_user_enrolments(), missing '.$localuserfield);
            $user = $DB->get_record('user', array('id'=>$user->id));
        }

                $allroles = get_all_roles();
        if (!isset($allroles[$defaultrole])) {
            $defaultrole = 0;
        }
        $roles = array();
        foreach ($allroles as $role) {
            $roles[$role->$localrolefield] = $role->id;
        }

        $roleassigns = array();
        $enrols = array();
        $instances = array();

        if (!$extdb = $this->db_init()) {
                        return;
        }

                $sql = $this->db_get_sql($table, array($userfield=>$user->$localuserfield), array(), false);

        if ($rs = $extdb->Execute($sql)) {
            if (!$rs->EOF) {
                while ($fields = $rs->FetchRow()) {
                    $fields = array_change_key_case($fields, CASE_LOWER);
                    $fields = $this->db_decode($fields);

                    if (empty($fields[$coursefield_l])) {
                                                continue;
                    }
                    if (!$course = $DB->get_record('course', array($localcoursefield=>$fields[$coursefield_l]), 'id,visible')) {
                        continue;
                    }
                    if (!$course->visible and $ignorehidden) {
                        continue;
                    }

                    if (empty($fields[$rolefield_l]) or !isset($roles[$fields[$rolefield_l]])) {
                        if (!$defaultrole) {
                                                        continue;
                        }
                        $roleid = $defaultrole;
                    } else {
                        $roleid = $roles[$fields[$rolefield_l]];
                    }

                    $roleassigns[$course->id][$roleid] = $roleid;
                    if (empty($fields[$otheruserfieldlower])) {
                        $enrols[$course->id][$roleid] = $roleid;
                    }

                    if ($instance = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>'database'), '*', IGNORE_MULTIPLE)) {
                        $instances[$course->id] = $instance;
                        continue;
                    }

                    $enrolid = $this->add_instance($course);
                    $instances[$course->id] = $DB->get_record('enrol', array('id'=>$enrolid));
                }
            }
            $rs->Close();
            $extdb->Close();
        } else {
                        $extdb->Close();
            return;
        }

                foreach ($roleassigns as $courseid => $roles) {
            if (!isset($instances[$courseid])) {
                                continue;
            }
            $instance = $instances[$courseid];

            if (isset($enrols[$courseid])) {
                if ($e = $DB->get_record('user_enrolments', array('userid' => $user->id, 'enrolid' => $instance->id))) {
                                        if ($e->status == ENROL_USER_SUSPENDED) {
                        $this->update_user_enrol($instance, $user->id, ENROL_USER_ACTIVE);
                    }
                } else {
                    $roleid = reset($enrols[$courseid]);
                    $this->enrol_user($instance, $user->id, $roleid, 0, 0, ENROL_USER_ACTIVE);
                }
            }

            if (!$context = context_course::instance($instance->courseid, IGNORE_MISSING)) {
                                continue;
            }
            $current = $DB->get_records('role_assignments', array('contextid'=>$context->id, 'userid'=>$user->id, 'component'=>'enrol_database', 'itemid'=>$instance->id), '', 'id, roleid');

            $existing = array();
            foreach ($current as $r) {
                if (isset($roles[$r->roleid])) {
                    $existing[$r->roleid] = $r->roleid;
                } else {
                    role_unassign($r->roleid, $user->id, $context->id, 'enrol_database', $instance->id);
                }
            }
            foreach ($roles as $rid) {
                if (!isset($existing[$rid])) {
                    role_assign($rid, $user->id, $context->id, 'enrol_database', $instance->id);
                }
            }
        }

                $sql = "SELECT e.*, c.visible AS cvisible, ue.status AS ustatus
                  FROM {enrol} e
                  JOIN {course} c ON c.id = e.courseid
                  JOIN {role_assignments} ra ON ra.itemid = e.id
             LEFT JOIN {user_enrolments} ue ON ue.enrolid = e.id AND ue.userid = ra.userid
                 WHERE ra.userid = :userid AND e.enrol = 'database'";
        $rs = $DB->get_recordset_sql($sql, array('userid' => $user->id));
        foreach ($rs as $instance) {
            if (!$instance->cvisible and $ignorehidden) {
                continue;
            }

            if (!$context = context_course::instance($instance->courseid, IGNORE_MISSING)) {
                                continue;
            }

            if (!empty($enrols[$instance->courseid])) {
                                continue;
            }

                        if ($unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
                $this->unenrol_user($instance, $user->id);

            } else if ($unenrolaction == ENROL_EXT_REMOVED_KEEP) {
                
            } else if ($unenrolaction == ENROL_EXT_REMOVED_SUSPEND or $unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                                if ($instance->ustatus != ENROL_USER_SUSPENDED) {
                    $this->update_user_enrol($instance, $user->id, ENROL_USER_SUSPENDED);
                }
                if ($unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                    if (!empty($roleassigns[$instance->courseid])) {
                                                continue;
                    }
                    role_unassign_all(array('contextid'=>$context->id, 'userid'=>$user->id, 'component'=>'enrol_database', 'itemid'=>$instance->id));
                }
            }
        }
        $rs->close();
    }

    
    public function sync_enrolments(progress_trace $trace, $onecourse = null) {
        global $CFG, $DB;

                if (!$this->get_config('dbtype') or !$this->get_config('remoteenroltable') or !$this->get_config('remotecoursefield') or !$this->get_config('remoteuserfield')) {
            $trace->output('User enrolment synchronisation skipped.');
            $trace->finished();
            return 0;
        }

        $trace->output('Starting user enrolment synchronisation...');

        if (!$extdb = $this->db_init()) {
            $trace->output('Error while communicating with external enrolment database');
            $trace->finished();
            return 1;
        }

                core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        $table            = $this->get_config('remoteenroltable');
        $coursefield      = trim($this->get_config('remotecoursefield'));
        $userfield        = trim($this->get_config('remoteuserfield'));
        $rolefield        = trim($this->get_config('remoterolefield'));
        $otheruserfield   = trim($this->get_config('remoteotheruserfield'));

                $coursefield_l    = strtolower($coursefield);
        $userfield_l      = strtolower($userfield);
        $rolefield_l      = strtolower($rolefield);
        $otheruserfieldlower = strtolower($otheruserfield);

        $localrolefield   = $this->get_config('localrolefield');
        $localuserfield   = $this->get_config('localuserfield');
        $localcoursefield = $this->get_config('localcoursefield');

        $unenrolaction    = $this->get_config('unenrolaction');
        $defaultrole      = $this->get_config('defaultrole');

                $allroles = get_all_roles();
        if (!isset($allroles[$defaultrole])) {
            $defaultrole = 0;
        }
        $roles = array();
        foreach ($allroles as $role) {
            $roles[$role->$localrolefield] = $role->id;
        }

        if ($onecourse) {
            $sql = "SELECT c.id, c.visible, c.$localcoursefield AS mapping, c.shortname, e.id AS enrolid
                      FROM {course} c
                 LEFT JOIN {enrol} e ON (e.courseid = c.id AND e.enrol = 'database')
                     WHERE c.id = :id";
            if (!$course = $DB->get_record_sql($sql, array('id'=>$onecourse))) {
                                return 0;
            }
            if (empty($course->mapping)) {
                                return 0;
            }
            if (empty($course->enrolid)) {
                $course->enrolid = $this->add_instance($course);
            }
            $existing = array($course->mapping=>$course);

                        $preventfullunenrol = false;
                        $ignorehidden = false;

        } else {
                        $externalcourses = array();
            $sql = $this->db_get_sql($table, array(), array($coursefield), true);
            if ($rs = $extdb->Execute($sql)) {
                if (!$rs->EOF) {
                    while ($mapping = $rs->FetchRow()) {
                        $mapping = reset($mapping);
                        $mapping = $this->db_decode($mapping);
                        if (empty($mapping)) {
                                                        continue;
                        }
                        $externalcourses[$mapping] = true;
                    }
                }
                $rs->Close();
            } else {
                $trace->output('Error reading data from the external enrolment table');
                $extdb->Close();
                return 2;
            }
            $preventfullunenrol = empty($externalcourses);
            if ($preventfullunenrol and $unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
                $trace->output('Preventing unenrolment of all current users, because it might result in major data loss, there has to be at least one record in external enrol table, sorry.', 1);
            }

                        $existing = array();
            $sql = "SELECT c.id, c.visible, c.$localcoursefield AS mapping, e.id AS enrolid, c.shortname
                      FROM {course} c
                      JOIN {enrol} e ON (e.courseid = c.id AND e.enrol = 'database')";
            $rs = $DB->get_recordset_sql($sql);             foreach ($rs as $course) {
                if (empty($course->mapping)) {
                    continue;
                }
                $existing[$course->mapping] = $course;
                unset($externalcourses[$course->mapping]);
            }
            $rs->close();

                        $params = array();
            $localnotempty = "";
            if ($localcoursefield !== 'id') {
                $localnotempty =  "AND c.$localcoursefield <> :lcfe";
                $params['lcfe'] = '';
            }
            $sql = "SELECT c.id, c.visible, c.$localcoursefield AS mapping, c.shortname
                      FROM {course} c
                 LEFT JOIN {enrol} e ON (e.courseid = c.id AND e.enrol = 'database')
                     WHERE e.id IS NULL $localnotempty";
            $rs = $DB->get_recordset_sql($sql, $params);
            foreach ($rs as $course) {
                if (empty($course->mapping)) {
                    continue;
                }
                if (!isset($externalcourses[$course->mapping])) {
                                        continue;
                }
                $course->enrolid = $this->add_instance($course);
                $existing[$course->mapping] = $course;
                unset($externalcourses[$course->mapping]);
            }
            $rs->close();

                        if ($externalcourses) {
                $list = implode(', ', array_keys($externalcourses));
                $trace->output("error: following courses do not exist - $list", 1);
                unset($list);
            }

                        unset($externalcourses);

            $ignorehidden = $this->get_config('ignorehiddencourses');
        }

                $sqlfields = array($userfield);
        if ($rolefield) {
            $sqlfields[] = $rolefield;
        }
        if ($otheruserfield) {
            $sqlfields[] = $otheruserfield;
        }
        foreach ($existing as $course) {
            if ($ignorehidden and !$course->visible) {
                continue;
            }
            if (!$instance = $DB->get_record('enrol', array('id'=>$course->enrolid))) {
                continue;             }
            $context = context_course::instance($course->id);

                        $currentroles  = array();
            $currentenrols = array();
            $currentstatus = array();
            $usermapping   = array();
            $sql = "SELECT u.$localuserfield AS mapping, u.id AS userid, ue.status, ra.roleid
                      FROM {user} u
                      JOIN {role_assignments} ra ON (ra.userid = u.id AND ra.component = 'enrol_database' AND ra.itemid = :enrolid)
                 LEFT JOIN {user_enrolments} ue ON (ue.userid = u.id AND ue.enrolid = ra.itemid)
                     WHERE u.deleted = 0";
            $params = array('enrolid'=>$instance->id);
            if ($localuserfield === 'username') {
                $sql .= " AND u.mnethostid = :mnethostid";
                $params['mnethostid'] = $CFG->mnet_localhost_id;
            }
            $rs = $DB->get_recordset_sql($sql, $params);
            foreach ($rs as $ue) {
                $currentroles[$ue->userid][$ue->roleid] = $ue->roleid;
                $usermapping[$ue->mapping] = $ue->userid;

                if (isset($ue->status)) {
                    $currentenrols[$ue->userid][$ue->roleid] = $ue->roleid;
                    $currentstatus[$ue->userid] = $ue->status;
                }
            }
            $rs->close();

                        $requestedroles  = array();
            $requestedenrols = array();
            $sql = $this->db_get_sql($table, array($coursefield=>$course->mapping), $sqlfields);
            if ($rs = $extdb->Execute($sql)) {
                if (!$rs->EOF) {
                    $usersearch = array('deleted' => 0);
                    if ($localuserfield === 'username') {
                        $usersearch['mnethostid'] = $CFG->mnet_localhost_id;
                    }
                    while ($fields = $rs->FetchRow()) {
                        $fields = array_change_key_case($fields, CASE_LOWER);
                        if (empty($fields[$userfield_l])) {
                            $trace->output("error: skipping user without mandatory $localuserfield in course '$course->mapping'", 1);
                            continue;
                        }
                        $mapping = $fields[$userfield_l];
                        if (!isset($usermapping[$mapping])) {
                            $usersearch[$localuserfield] = $mapping;
                            if (!$user = $DB->get_record('user', $usersearch, 'id', IGNORE_MULTIPLE)) {
                                $trace->output("error: skipping unknown user $localuserfield '$mapping' in course '$course->mapping'", 1);
                                continue;
                            }
                            $usermapping[$mapping] = $user->id;
                            $userid = $user->id;
                        } else {
                            $userid = $usermapping[$mapping];
                        }
                        if (empty($fields[$rolefield_l]) or !isset($roles[$fields[$rolefield_l]])) {
                            if (!$defaultrole) {
                                $trace->output("error: skipping user '$userid' in course '$course->mapping' - missing course and default role", 1);
                                continue;
                            }
                            $roleid = $defaultrole;
                        } else {
                            $roleid = $roles[$fields[$rolefield_l]];
                        }

                        $requestedroles[$userid][$roleid] = $roleid;
                        if (empty($fields[$otheruserfieldlower])) {
                            $requestedenrols[$userid][$roleid] = $roleid;
                        }
                    }
                }
                $rs->Close();
            } else {
                $trace->output("error: skipping course '$course->mapping' - could not match with external database", 1);
                continue;
            }
            unset($usermapping);

                        foreach ($requestedenrols as $userid => $userroles) {
                foreach ($userroles as $roleid) {
                    if (empty($currentenrols[$userid])) {
                        $this->enrol_user($instance, $userid, $roleid, 0, 0, ENROL_USER_ACTIVE);
                        $currentroles[$userid][$roleid] = $roleid;
                        $currentenrols[$userid][$roleid] = $roleid;
                        $currentstatus[$userid] = ENROL_USER_ACTIVE;
                        $trace->output("enrolling: $userid ==> $course->shortname as ".$allroles[$roleid]->shortname, 1);
                    }
                }

                                if ($currentstatus[$userid] == ENROL_USER_SUSPENDED) {
                    $this->update_user_enrol($instance, $userid, ENROL_USER_ACTIVE);
                    $trace->output("unsuspending: $userid ==> $course->shortname", 1);
                }
            }

            foreach ($requestedroles as $userid => $userroles) {
                                foreach ($userroles as $roleid) {
                    if (empty($currentroles[$userid][$roleid])) {
                        role_assign($roleid, $userid, $context->id, 'enrol_database', $instance->id);
                        $currentroles[$userid][$roleid] = $roleid;
                        $trace->output("assigning roles: $userid ==> $course->shortname as ".$allroles[$roleid]->shortname, 1);
                    }
                }

                                foreach ($currentroles[$userid] as $cr) {
                    if (empty($userroles[$cr])) {
                        role_unassign($cr, $userid, $context->id, 'enrol_database', $instance->id);
                        unset($currentroles[$userid][$cr]);
                        $trace->output("unsassigning roles: $userid ==> $course->shortname", 1);
                    }
                }

                unset($currentroles[$userid]);
            }

            foreach ($currentroles as $userid => $userroles) {
                                                $currentstatus += array($userid => ENROL_USER_ACTIVE);
            }

                        if ($unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
                if (!$preventfullunenrol) {
                                        foreach ($currentstatus as $userid => $status) {
                        if (isset($requestedenrols[$userid])) {
                            continue;
                        }
                        $this->unenrol_user($instance, $userid);
                        $trace->output("unenrolling: $userid ==> $course->shortname", 1);
                    }
                }

            } else if ($unenrolaction == ENROL_EXT_REMOVED_KEEP) {
                
            } else if ($unenrolaction == ENROL_EXT_REMOVED_SUSPEND or $unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                                foreach ($currentstatus as $userid => $status) {
                    if (isset($requestedenrols[$userid])) {
                        continue;
                    }
                    if ($status != ENROL_USER_SUSPENDED) {
                        $this->update_user_enrol($instance, $userid, ENROL_USER_SUSPENDED);
                        $trace->output("suspending: $userid ==> $course->shortname", 1);
                    }
                    if ($unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                        if (isset($requestedroles[$userid])) {
                                                        continue;
                        }
                        role_unassign_all(array('contextid'=>$context->id, 'userid'=>$userid, 'component'=>'enrol_database', 'itemid'=>$instance->id));

                        $trace->output("unsassigning all roles: $userid ==> $course->shortname", 1);
                    }
                }
            }
        }

                $extdb->Close();

        $trace->output('...user enrolment synchronisation finished.');
        $trace->finished();

        return 0;
    }

    
    public function sync_courses(progress_trace $trace) {
        global $CFG, $DB;

                if (!$this->get_config('dbtype') or !$this->get_config('newcoursetable') or !$this->get_config('newcoursefullname') or !$this->get_config('newcourseshortname')) {
            $trace->output('Course synchronisation skipped.');
            $trace->finished();
            return 0;
        }

        $trace->output('Starting course synchronisation...');

                core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        if (!$extdb = $this->db_init()) {
            $trace->output('Error while communicating with external enrolment database');
            $trace->finished();
            return 1;
        }

        $table     = $this->get_config('newcoursetable');
        $fullname  = trim($this->get_config('newcoursefullname'));
        $shortname = trim($this->get_config('newcourseshortname'));
        $idnumber  = trim($this->get_config('newcourseidnumber'));
        $category  = trim($this->get_config('newcoursecategory'));

                $fullname_l  = strtolower($fullname);
        $shortname_l = strtolower($shortname);
        $idnumber_l  = strtolower($idnumber);
        $category_l  = strtolower($category);

        $localcategoryfield = $this->get_config('localcategoryfield', 'id');
        $defaultcategory    = $this->get_config('defaultcategory');

        if (!$DB->record_exists('course_categories', array('id'=>$defaultcategory))) {
            $trace->output("default course category does not exist!", 1);
            $categories = $DB->get_records('course_categories', array(), 'sortorder', 'id', 0, 1);
            $first = reset($categories);
            $defaultcategory = $first->id;
        }

        $sqlfields = array($fullname, $shortname);
        if ($category) {
            $sqlfields[] = $category;
        }
        if ($idnumber) {
            $sqlfields[] = $idnumber;
        }
        $sql = $this->db_get_sql($table, array(), $sqlfields, true);
        $createcourses = array();
        if ($rs = $extdb->Execute($sql)) {
            if (!$rs->EOF) {
                while ($fields = $rs->FetchRow()) {
                    $fields = array_change_key_case($fields, CASE_LOWER);
                    $fields = $this->db_decode($fields);
                    if (empty($fields[$shortname_l]) or empty($fields[$fullname_l])) {
                        $trace->output('error: invalid external course record, shortname and fullname are mandatory: ' . json_encode($fields), 1);                         continue;
                    }
                    if ($DB->record_exists('course', array('shortname'=>$fields[$shortname_l]))) {
                                                continue;
                    }
                                        if ($idnumber and $fields[$idnumber_l] !== '' and $fields[$idnumber_l] !== null and $DB->record_exists('course', array('idnumber'=>$fields[$idnumber_l]))) {
                        $trace->output('error: duplicate idnumber, can not create course: '.$fields[$shortname_l].' ['.$fields[$idnumber_l].']', 1);
                        continue;
                    }
                    $course = new stdClass();
                    $course->fullname  = $fields[$fullname_l];
                    $course->shortname = $fields[$shortname_l];
                    $course->idnumber  = $idnumber ? $fields[$idnumber_l] : '';
                    if ($category) {
                        if (empty($fields[$category_l])) {
                                                        $course->category = $defaultcategory;
                        } else if ($coursecategory = $DB->get_record('course_categories', array($localcategoryfield=>$fields[$category_l]), 'id')) {
                                                        $course->category = $coursecategory->id;
                            unset($coursecategory);
                        } else {
                                                        $trace->output('error: invalid category '.$localcategoryfield.', can not create course: '.$fields[$shortname_l], 1);
                            continue;
                        }
                    } else {
                        $course->category = $defaultcategory;
                    }
                    $createcourses[] = $course;
                }
            }
            $rs->Close();
        } else {
            $extdb->Close();
            $trace->output('Error reading data from the external course table');
            $trace->finished();
            return 4;
        }
        if ($createcourses) {
            require_once("$CFG->dirroot/course/lib.php");

            $templatecourse = $this->get_config('templatecourse');

            $template = false;
            if ($templatecourse) {
                if ($template = $DB->get_record('course', array('shortname'=>$templatecourse))) {
                    $template = fullclone(course_get_format($template)->get_course());
                    unset($template->id);
                    unset($template->fullname);
                    unset($template->shortname);
                    unset($template->idnumber);
                } else {
                    $trace->output("can not find template for new course!", 1);
                }
            }
            if (!$template) {
                $courseconfig = get_config('moodlecourse');
                $template = new stdClass();
                $template->summary        = '';
                $template->summaryformat  = FORMAT_HTML;
                $template->format         = $courseconfig->format;
                $template->newsitems      = $courseconfig->newsitems;
                $template->showgrades     = $courseconfig->showgrades;
                $template->showreports    = $courseconfig->showreports;
                $template->maxbytes       = $courseconfig->maxbytes;
                $template->groupmode      = $courseconfig->groupmode;
                $template->groupmodeforce = $courseconfig->groupmodeforce;
                $template->visible        = $courseconfig->visible;
                $template->lang           = $courseconfig->lang;
                $template->groupmodeforce = $courseconfig->groupmodeforce;
            }

            foreach ($createcourses as $fields) {
                $newcourse = clone($template);
                $newcourse->fullname  = $fields->fullname;
                $newcourse->shortname = $fields->shortname;
                $newcourse->idnumber  = $fields->idnumber;
                $newcourse->category  = $fields->category;

                                                if ($DB->record_exists('course', array('shortname' => $newcourse->shortname))) {
                    $trace->output("can not insert new course, duplicate shortname detected: ".$newcourse->shortname, 1);
                    continue;
                } else if (!empty($newcourse->idnumber) and $DB->record_exists('course', array('idnumber' => $newcourse->idnumber))) {
                    $trace->output("can not insert new course, duplicate idnumber detected: ".$newcourse->idnumber, 1);
                    continue;
                }
                $c = create_course($newcourse);
                $trace->output("creating course: $c->id, $c->fullname, $c->shortname, $c->idnumber, $c->category", 1);
            }

            unset($createcourses);
            unset($template);
        }

                $extdb->Close();

        $trace->output('...course synchronisation finished.');
        $trace->finished();

        return 0;
    }

    protected function db_get_sql($table, array $conditions, array $fields, $distinct = false, $sort = "") {
        $fields = $fields ? implode(',', $fields) : "*";
        $where = array();
        if ($conditions) {
            foreach ($conditions as $key=>$value) {
                $value = $this->db_encode($this->db_addslashes($value));

                $where[] = "$key = '$value'";
            }
        }
        $where = $where ? "WHERE ".implode(" AND ", $where) : "";
        $sort = $sort ? "ORDER BY $sort" : "";
        $distinct = $distinct ? "DISTINCT" : "";
        $sql = "SELECT $distinct $fields
                  FROM $table
                 $where
                  $sort";

        return $sql;
    }

    
    protected function db_init() {
        global $CFG;

        require_once($CFG->libdir.'/adodb/adodb.inc.php');

                $extdb = ADONewConnection($this->get_config('dbtype'));
        if ($this->get_config('debugdb')) {
            $extdb->debug = true;
            ob_start();         }

                if (!$extdb->IsConnected()) {
            $result = $extdb->Connect($this->get_config('dbhost'), $this->get_config('dbuser'), $this->get_config('dbpass'), $this->get_config('dbname'), true);
            if (!$result) {
                return null;
            }
        }

        $extdb->SetFetchMode(ADODB_FETCH_ASSOC);
        if ($this->get_config('dbsetupsql')) {
            $extdb->Execute($this->get_config('dbsetupsql'));
        }
        return $extdb;
    }

    protected function db_addslashes($text) {
                if ($this->get_config('dbsybasequoting')) {
            $text = str_replace('\\', '\\\\', $text);
            $text = str_replace(array('\'', '"', "\0"), array('\\\'', '\\"', '\\0'), $text);
        } else {
            $text = str_replace("'", "''", $text);
        }
        return $text;
    }

    protected function db_encode($text) {
        $dbenc = $this->get_config('dbencoding');
        if (empty($dbenc) or $dbenc == 'utf-8') {
            return $text;
        }
        if (is_array($text)) {
            foreach($text as $k=>$value) {
                $text[$k] = $this->db_encode($value);
            }
            return $text;
        } else {
            return core_text::convert($text, 'utf-8', $dbenc);
        }
    }

    protected function db_decode($text) {
        $dbenc = $this->get_config('dbencoding');
        if (empty($dbenc) or $dbenc == 'utf-8') {
            return $text;
        }
        if (is_array($text)) {
            foreach($text as $k=>$value) {
                $text[$k] = $this->db_decode($value);
            }
            return $text;
        } else {
            return core_text::convert($text, $dbenc, 'utf-8');
        }
    }

    
    public function restore_sync_course($course) {
        $trace = new null_progress_trace();
        $this->sync_enrolments($trace, $course->id);
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
        global $DB;

        if ($this->get_config('unenrolaction') == ENROL_EXT_REMOVED_UNENROL) {
                        return;
        }
        if (!$DB->record_exists('user_enrolments', array('enrolid'=>$instance->id, 'userid'=>$userid))) {
            $this->enrol_user($instance, $userid, null, 0, 0, ENROL_USER_SUSPENDED);
        }
    }

    
    public function restore_role_assignment($instance, $roleid, $userid, $contextid) {
        if ($this->get_config('unenrolaction') == ENROL_EXT_REMOVED_UNENROL or $this->get_config('unenrolaction') == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                        return;
        }
        role_assign($roleid, $userid, $contextid, 'enrol_'.$this->get_name(), $instance->id);
    }

    
    public function test_settings() {
        global $CFG, $OUTPUT;

        
        raise_memory_limit(MEMORY_HUGE);

        $this->load_config();

        $enroltable = $this->get_config('remoteenroltable');
        $coursetable = $this->get_config('newcoursetable');

        if (empty($enroltable)) {
            echo $OUTPUT->notification('External enrolment table not specified.', 'notifyproblem');
        }

        if (empty($coursetable)) {
            echo $OUTPUT->notification('External course table not specified.', 'notifyproblem');
        }

        if (empty($coursetable) and empty($enroltable)) {
            return;
        }

        $olddebug = $CFG->debug;
        $olddisplay = ini_get('display_errors');
        ini_set('display_errors', '1');
        $CFG->debug = DEBUG_DEVELOPER;
        $olddebugdb = $this->config->debugdb;
        $this->config->debugdb = 1;
        error_reporting($CFG->debug);

        $adodb = $this->db_init();

        if (!$adodb or !$adodb->IsConnected()) {
            $this->config->debugdb = $olddebugdb;
            $CFG->debug = $olddebug;
            ini_set('display_errors', $olddisplay);
            error_reporting($CFG->debug);
            ob_end_flush();

            echo $OUTPUT->notification('Cannot connect the database.', 'notifyproblem');
            return;
        }

        if (!empty($enroltable)) {
            $rs = $adodb->Execute("SELECT *
                                     FROM $enroltable");
            if (!$rs) {
                echo $OUTPUT->notification('Can not read external enrol table.', 'notifyproblem');

            } else if ($rs->EOF) {
                echo $OUTPUT->notification('External enrol table is empty.', 'notifyproblem');
                $rs->Close();

            } else {
                $fields_obj = $rs->FetchObj();
                $columns = array_keys((array)$fields_obj);

                echo $OUTPUT->notification('External enrolment table contains following columns:<br />'.implode(', ', $columns), 'notifysuccess');
                $rs->Close();
            }
        }

        if (!empty($coursetable)) {
            $rs = $adodb->Execute("SELECT *
                                     FROM $coursetable");
            if (!$rs) {
                echo $OUTPUT->notification('Can not read external course table.', 'notifyproblem');

            } else if ($rs->EOF) {
                echo $OUTPUT->notification('External course table is empty.', 'notifyproblem');
                $rs->Close();

            } else {
                $fields_obj = $rs->FetchObj();
                $columns = array_keys((array)$fields_obj);

                echo $OUTPUT->notification('External course table contains following columns:<br />'.implode(', ', $columns), 'notifysuccess');
                $rs->Close();
            }
        }

        $adodb->Close();

        $this->config->debugdb = $olddebugdb;
        $CFG->debug = $olddebug;
        ini_set('display_errors', $olddisplay);
        error_reporting($CFG->debug);
        ob_end_flush();
    }
}
