<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/group/lib.php');



class enrol_imsenterprise_plugin extends enrol_plugin {

    
    protected $logfp;

    
    protected $continueprocessing;

    
    protected $xmlcache;

    
    protected $coursemappings;

    
    protected $rolemappings;

    
    public function cron() {
        global $CFG;

                $imsfilelocation = $this->get_config('imsfilelocation');
        $logtolocation = $this->get_config('logtolocation');
        $mailadmins = $this->get_config('mailadmins');
        $prevtime = $this->get_config('prev_time');
        $prevmd5 = $this->get_config('prev_md5');
        $prevpath = $this->get_config('prev_path');

        if (empty($imsfilelocation)) {
            $filename = "$CFG->dataroot/1/imsenterprise-enrol.xml";          } else {
            $filename = $imsfilelocation;
        }

        $this->logfp = false;
        if (!empty($logtolocation)) {
            $this->logfp = fopen($logtolocation, 'a');
        }

        $fileisnew = false;
        if ( file_exists($filename) ) {
            core_php_time_limit::raise();
            $starttime = time();

            $this->log_line('----------------------------------------------------------------------');
            $this->log_line("IMS Enterprise enrol cron process launched at " . userdate(time()));
            $this->log_line('Found file '.$filename);
            $this->xmlcache = '';

                        $this->load_role_mappings();
                        $this->load_course_mappings();

            $md5 = md5_file($filename);             $filemtime = filemtime($filename);

                                    if (empty($prevpath)  || ($filename != $prevpath)) {
                $fileisnew = true;
            } else if (isset($prevtime) && ($filemtime <= $prevtime)) {
                $this->log_line('File modification time is not more recent than last update - skipping processing.');
            } else if (isset($prevmd5) && ($md5 == $prevmd5)) {
                $this->log_line('File MD5 hash is same as on last update - skipping processing.');
            } else {
                $fileisnew = true;             }

            if ($fileisnew) {

                                $this->continueprocessing = true;

                                if (($fh = fopen($filename, "r")) != false) {

                    $line = 0;
                    while ((!feof($fh)) && $this->continueprocessing) {

                        $line++;
                        $curline = fgets($fh);
                        $this->xmlcache .= $curline; 
                        while (true) {
                                                                                    if ($tagcontents = $this->full_tag_found_in_cache('group', $curline)) {
                                $this->process_group_tag($tagcontents);
                                $this->remove_tag_from_cache('group');
                            } else if ($tagcontents = $this->full_tag_found_in_cache('person', $curline)) {
                                $this->process_person_tag($tagcontents);
                                $this->remove_tag_from_cache('person');
                            } else if ($tagcontents = $this->full_tag_found_in_cache('membership', $curline)) {
                                $this->process_membership_tag($tagcontents);
                                $this->remove_tag_from_cache('membership');
                            } else if ($tagcontents = $this->full_tag_found_in_cache('comments', $curline)) {
                                $this->remove_tag_from_cache('comments');
                            } else if ($tagcontents = $this->full_tag_found_in_cache('properties', $curline)) {
                                $this->process_properties_tag($tagcontents);
                                $this->remove_tag_from_cache('properties');
                            } else {
                                break;
                            }
                        }
                    }
                    fclose($fh);
                    fix_course_sortorder();
                }

                $timeelapsed = time() - $starttime;
                $this->log_line('Process has completed. Time taken: '.$timeelapsed.' seconds.');

            }

                        $this->set_config('prev_time', $filemtime);
            $this->set_config('prev_md5',  $md5);
            $this->set_config('prev_path', $filename);

        } else {
            $this->log_line('File not found: '.$filename);
        }

        if (!empty($mailadmins) && $fileisnew) {
            $timeelapsed = isset($timeelapsed) ? $timeelapsed : 0;
            $msg = "An IMS enrolment has been carried out within Moodle.\nTime taken: $timeelapsed seconds.\n\n";
            if (!empty($logtolocation)) {
                if ($this->logfp) {
                    $msg .= "Log data has been written to:\n";
                    $msg .= "$logtolocation\n";
                    $msg .= "(Log file size: ".ceil(filesize($logtolocation) / 1024)."Kb)\n\n";
                } else {
                    $msg .= "The log file appears not to have been successfully written.\n";
                    $msg .= "Check that the file is writeable by the server:\n";
                    $msg .= "$logtolocation\n\n";
                }
            } else {
                $msg .= "Logging is currently not active.";
            }

            $eventdata = new stdClass();
            $eventdata->modulename        = 'moodle';
            $eventdata->component         = 'enrol_imsenterprise';
            $eventdata->name              = 'imsenterprise_enrolment';
            $eventdata->userfrom          = get_admin();
            $eventdata->userto            = get_admin();
            $eventdata->subject           = "Moodle IMS Enterprise enrolment notification";
            $eventdata->fullmessage       = $msg;
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml   = '';
            $eventdata->smallmessage      = '';
            message_send($eventdata);

            $this->log_line('Notification email sent to administrator.');

        }

        if ($this->logfp) {
            fclose($this->logfp);
        }

    }

    
    protected function full_tag_found_in_cache($tagname, $latestline) {
                if (strpos(strtolower($latestline), '</'.strtolower($tagname).'>') === false) {
            return false;
        } else if (preg_match('{(<'.$tagname.'\b.*?>.*?</'.$tagname.'>)}is', $this->xmlcache, $matches)) {
            return $matches[1];
        } else {
            return false;
        }
    }

    
    protected function remove_tag_from_cache($tagname) {
                        $this->xmlcache = trim(preg_replace('{<'.$tagname.'\b.*?>.*?</'.$tagname.'>}is', '', $this->xmlcache, 1));
    }

    
    protected static function get_recstatus($tagdata, $tagname) {
        if (preg_match('{<'.$tagname.'\b[^>]*recstatus\s*=\s*["\'](\d)["\']}is', $tagdata, $matches)) {
            return intval($matches[1]);
        } else {
            return 0;         }
    }

    
    protected function process_group_tag($tagcontents) {
        global $DB, $CFG;

                $truncatecoursecodes    = $this->get_config('truncatecoursecodes');
        $createnewcourses       = $this->get_config('createnewcourses');
        $createnewcategories    = $this->get_config('createnewcategories');

        if ($createnewcourses) {
            require_once("$CFG->dirroot/course/lib.php");
        }

                $group = new stdClass();
        if (preg_match('{<sourcedid>.*?<id>(.+?)</id>.*?</sourcedid>}is', $tagcontents, $matches)) {
            $group->coursecode = trim($matches[1]);
        }

        if (preg_match('{<description>.*?<long>(.*?)</long>.*?</description>}is', $tagcontents, $matches)) {
            $group->long = trim($matches[1]);
        }
        if (preg_match('{<description>.*?<short>(.*?)</short>.*?</description>}is', $tagcontents, $matches)) {
            $group->short = trim($matches[1]);
        }
        if (preg_match('{<description>.*?<full>(.*?)</full>.*?</description>}is', $tagcontents, $matches)) {
            $group->full = trim($matches[1]);
        }

        if (preg_match('{<org>.*?<orgunit>(.*?)</orgunit>.*?</org>}is', $tagcontents, $matches)) {
            $group->category = trim($matches[1]);
        }

        $recstatus = ($this->get_recstatus($tagcontents, 'group'));

        if (empty($group->coursecode)) {
            $this->log_line('Error: Unable to find course code in \'group\' element.');
        } else {
                        if (intval($truncatecoursecodes) > 0) {
                $group->coursecode = ($truncatecoursecodes > 0)
                    ? substr($group->coursecode, 0, intval($truncatecoursecodes))
                    : $group->coursecode;
            }

                        $group->coursecode = array($group->coursecode);

                        foreach ($group->coursecode as $coursecode) {
                $coursecode = trim($coursecode);
                if (!$DB->get_field('course', 'id', array('idnumber' => $coursecode))) {
                    if (!$createnewcourses) {
                        $this->log_line("Course $coursecode not found in Moodle's course idnumbers.");
                    } else {

                                                $courseconfig = get_config('moodlecourse'); 
                                                $course = new stdClass();
                        foreach ($this->coursemappings as $courseattr => $imsname) {

                            if ($imsname == 'ignore') {
                                continue;
                            }

                                                        if ($imsname == 'coursecode') {
                                $course->{$courseattr} = $coursecode;
                            } else if (!empty($group->{$imsname})) {
                                $course->{$courseattr} = $group->{$imsname};
                            } else {
                                $this->log_line('No ' . $imsname . ' description tag found for '
                                    .$coursecode . ' coursecode, using ' . $coursecode . ' instead');
                                $course->{$courseattr} = $coursecode;
                            }
                        }

                        $course->idnumber = $coursecode;
                        $course->format = $courseconfig->format;
                        $course->visible = $courseconfig->visible;
                        $course->newsitems = $courseconfig->newsitems;
                        $course->showgrades = $courseconfig->showgrades;
                        $course->showreports = $courseconfig->showreports;
                        $course->maxbytes = $courseconfig->maxbytes;
                        $course->groupmode = $courseconfig->groupmode;
                        $course->groupmodeforce = $courseconfig->groupmodeforce;
                        $course->enablecompletion = $courseconfig->enablecompletion;
                        
                                                if (!empty($group->category)) {
                                                        if ($catid = $DB->get_field('course_categories', 'id', array('name' => $group->category))) {
                                $course->category = $catid;
                            } else if ($createnewcategories) {
                                                                $newcat = new stdClass();
                                $newcat->name = $group->category;
                                $newcat->visible = 0;
                                $catid = $DB->insert_record('course_categories', $newcat);
                                $course->category = $catid;
                                $this->log_line("Created new (hidden) category, #$catid: $newcat->name");
                            } else {
                                                                $this->log_line('Category '.$group->category.' not found in Moodle database, so using '.
                                    'default category instead.');
                                $course->category = $this->get_default_category_id();
                            }
                        } else {
                            $course->category = $this->get_default_category_id();
                        }
                        $course->startdate = time();
                                                $course->sortorder = 0;

                        $course = create_course($course);

                        $this->log_line("Created course $coursecode in Moodle (Moodle ID is $course->id)");
                    }
                } else if ($recstatus == 3 && ($courseid = $DB->get_field('course', 'id', array('idnumber' => $coursecode)))) {
                                        $DB->set_field('course', 'visible', '0', array('id' => $courseid));
                }
            }
        }
    }

    
    protected function process_person_tag($tagcontents) {
        global $CFG, $DB;

                $imssourcedidfallback   = $this->get_config('imssourcedidfallback');
        $fixcaseusernames       = $this->get_config('fixcaseusernames');
        $fixcasepersonalnames   = $this->get_config('fixcasepersonalnames');
        $imsdeleteusers         = $this->get_config('imsdeleteusers');
        $createnewusers         = $this->get_config('createnewusers');

        $person = new stdClass();
        if (preg_match('{<sourcedid>.*?<id>(.+?)</id>.*?</sourcedid>}is', $tagcontents, $matches)) {
            $person->idnumber = trim($matches[1]);
        }
        if (preg_match('{<name>.*?<n>.*?<given>(.+?)</given>.*?</n>.*?</name>}is', $tagcontents, $matches)) {
            $person->firstname = trim($matches[1]);
        }
        if (preg_match('{<name>.*?<n>.*?<family>(.+?)</family>.*?</n>.*?</name>}is', $tagcontents, $matches)) {
            $person->lastname = trim($matches[1]);
        }
        if (preg_match('{<userid>(.*?)</userid>}is', $tagcontents, $matches)) {
            $person->username = trim($matches[1]);
        }
        if ($imssourcedidfallback && trim($person->username) == '') {
                                    $person->username = $person->idnumber;
        }
        if (preg_match('{<email>(.*?)</email>}is', $tagcontents, $matches)) {
            $person->email = trim($matches[1]);
        }
        if (preg_match('{<url>(.*?)</url>}is', $tagcontents, $matches)) {
            $person->url = trim($matches[1]);
        }
        if (preg_match('{<adr>.*?<locality>(.+?)</locality>.*?</adr>}is', $tagcontents, $matches)) {
            $person->city = trim($matches[1]);
        }
        if (preg_match('{<adr>.*?<country>(.+?)</country>.*?</adr>}is', $tagcontents, $matches)) {
            $person->country = trim($matches[1]);
        }

                if ($fixcaseusernames && isset($person->username)) {
            $person->username = strtolower($person->username);
        }
        if ($fixcasepersonalnames) {
            if (isset($person->firstname)) {
                $person->firstname = ucwords(strtolower($person->firstname));
            }
            if (isset($person->lastname)) {
                $person->lastname = ucwords(strtolower($person->lastname));
            }
        }

        $recstatus = ($this->get_recstatus($tagcontents, 'person'));

                if ($recstatus == 3) {

            if ($imsdeleteusers) {                                 $params = array('username' => $person->username, 'mnethostid' => $CFG->mnet_localhost_id, 'deleted' => 0);
                if ($user = $DB->get_record('user', $params)) {
                    if (delete_user($user)) {
                        $this->log_line("Deleted user '$person->username' (ID number $person->idnumber).");
                    } else {
                        $this->log_line("Error deleting '$person->username' (ID number $person->idnumber).");
                    }
                } else {
                    $this->log_line("Can not delete user '$person->username' (ID number $person->idnumber) - user does not exist.");
                }
            } else {
                $this->log_line("Ignoring deletion request for user '$person->username' (ID number $person->idnumber).");
            }

        } else { 
                        if (!$DB->get_field('user', 'id', array('idnumber' => $person->idnumber)) && $createnewusers) {
                                if ((!isset($person->username)) || (strlen($person->username) == 0)) {
                    $this->log_line("Cannot create new user for ID # $person->idnumber".
                        "- no username listed in IMS data for this person.");
                } else if ($DB->get_field('user', 'id', array('username' => $person->username))) {
                                        $DB->set_field('user', 'idnumber', $person->idnumber, array('username' => $person->username));
                } else {

                                        $person->lang = $CFG->lang;
                                        $auth = explode(',', $CFG->auth);
                    $auth = reset($auth);
                    $person->auth = $auth;
                    $person->confirmed = 1;
                    $person->timemodified = time();
                    $person->mnethostid = $CFG->mnet_localhost_id;
                    $id = $DB->insert_record('user', $person);
                    $this->log_line("Created user record ('.$id.') for user '$person->username' (ID number $person->idnumber).");
                }
            } else if ($createnewusers) {
                $this->log_line("User record already exists for user '$person->username' (ID number $person->idnumber).");

                                            } else {
                $this->log_line("No user record found for '$person->username' (ID number $person->idnumber).");
            }

        }

    }

    
    protected function process_membership_tag($tagcontents) {
        global $DB;

                $truncatecoursecodes = $this->get_config('truncatecoursecodes');
        $imscapitafix = $this->get_config('imscapitafix');

        $memberstally = 0;
        $membersuntally = 0;

                $groupids = array();

        $ship = new stdClass();

        if (preg_match('{<sourcedid>.*?<id>(.+?)</id>.*?</sourcedid>}is', $tagcontents, $matches)) {
            $ship->coursecode = ($truncatecoursecodes > 0)
                ? substr(trim($matches[1]), 0, intval($truncatecoursecodes))
                : trim($matches[1]);
            $ship->courseid = $DB->get_field('course', 'id', array('idnumber' => $ship->coursecode));
        }
        if ($ship->courseid && preg_match_all('{<member>(.*?)</member>}is', $tagcontents, $membermatches, PREG_SET_ORDER)) {
            $courseobj = new stdClass();
            $courseobj->id = $ship->courseid;

            foreach ($membermatches as $mmatch) {
                $member = new stdClass();
                $memberstoreobj = new stdClass();
                if (preg_match('{<sourcedid>.*?<id>(.+?)</id>.*?</sourcedid>}is', $mmatch[1], $matches)) {
                    $member->idnumber = trim($matches[1]);
                }
                if (preg_match('{<role\s+roletype=["\'](.+?)["\'].*?>}is', $mmatch[1], $matches)) {
                                        $member->roletype = trim($matches[1]);
                } else if ($imscapitafix && preg_match('{<roletype>(.+?)</roletype>}is', $mmatch[1], $matches)) {
                                                                                $member->roletype = trim($matches[1]);
                }
                if (preg_match('{<role\b.*?<status>(.+?)</status>.*?</role>}is', $mmatch[1], $matches)) {
                                        $member->status = trim($matches[1]);
                }

                $recstatus = ($this->get_recstatus($mmatch[1], 'role'));
                if ($recstatus == 3) {
                                        $member->status = 0;
                }

                $timeframe = new stdClass();
                $timeframe->begin = 0;
                $timeframe->end = 0;
                if (preg_match('{<role\b.*?<timeframe>(.+?)</timeframe>.*?</role>}is', $mmatch[1], $matches)) {
                    $timeframe = $this->decode_timeframe($matches[1]);
                }
                if (preg_match('{<role\b.*?<extension>.*?<cohort>(.+?)</cohort>.*?</extension>.*?</role>}is',
                        $mmatch[1], $matches)) {
                    $member->groupname = trim($matches[1]);
                                    }

                                $memberstoreobj->userid = $DB->get_field('user', 'id', array('idnumber' => $member->idnumber));
                $memberstoreobj->enrol = 'imsenterprise';
                $memberstoreobj->course = $ship->courseid;
                $memberstoreobj->time = time();
                $memberstoreobj->timemodified = time();
                if ($memberstoreobj->userid) {

                                                            $moodleroleid = $this->rolemappings[$member->roletype];
                    if (!$moodleroleid) {
                        $this->log_line("SKIPPING role $member->roletype for $memberstoreobj->userid "
                            ."($member->idnumber) in course $memberstoreobj->course");
                        continue;
                    }

                    if (intval($member->status) == 1) {
                        
                        $einstance = $DB->get_record('enrol',
                            array('courseid' => $courseobj->id, 'enrol' => $memberstoreobj->enrol));
                        if (empty($einstance)) {
                                                        $enrolid = $this->add_instance($courseobj);
                            $einstance = $DB->get_record('enrol', array('id' => $enrolid));
                        }

                        $this->enrol_user($einstance, $memberstoreobj->userid, $moodleroleid, $timeframe->begin, $timeframe->end);

                        $this->log_line("Enrolled user #$memberstoreobj->userid ($member->idnumber) "
                            ."to role $member->roletype in course $memberstoreobj->course");
                        $memberstally++;

                                                if (isset($member->groupname)) {
                                                        if (isset($groupids[$member->groupname])) {
                                $member->groupid = $groupids[$member->groupname];                             } else {
                                $params = array('courseid' => $ship->courseid, 'name' => $member->groupname);
                                if ($groupid = $DB->get_field('groups', 'id', $params)) {
                                    $member->groupid = $groupid;
                                    $groupids[$member->groupname] = $groupid;                                 } else {
                                                                        $group = new stdClass();
                                    $group->name = $member->groupname;
                                    $group->courseid = $ship->courseid;
                                    $group->timecreated = time();
                                    $group->timemodified = time();
                                    $groupid = $DB->insert_record('groups', $group);
                                    $this->log_line('Added a new group for this course: '.$group->name);
                                    $groupids[$member->groupname] = $groupid;                                     $member->groupid = $groupid;
                                                                        cache_helper::invalidate_by_definition('core', 'groupdata', array(), array($ship->courseid));
                                }
                            }
                                                        if ($member->groupid) {
                                groups_add_member($member->groupid, $memberstoreobj->userid,
                                    'enrol_imsenterprise', $einstance->id);
                            }
                        }

                    } else if ($this->get_config('imsunenrol')) {
                        
                        $einstances = $DB->get_records('enrol',
                            array('enrol' => $memberstoreobj->enrol, 'courseid' => $courseobj->id));
                        foreach ($einstances as $einstance) {
                                                        $this->unenrol_user($einstance, $memberstoreobj->userid);
                        }

                        $membersuntally++;
                        $this->log_line("Unenrolled $member->idnumber from role $moodleroleid in course");
                    }

                }
            }
            $this->log_line("Added $memberstally users to course $ship->coursecode");
            if ($membersuntally > 0) {
                $this->log_line("Removed $membersuntally users from course $ship->coursecode");
            }
        }
    } 
    
    protected function process_properties_tag($tagcontents) {
        $imsrestricttarget = $this->get_config('imsrestricttarget');

        if ($imsrestricttarget) {
            if (!(preg_match('{<target>'.preg_quote($imsrestricttarget).'</target>}is', $tagcontents, $matches))) {
                $this->log_line("Skipping processing: required target \"$imsrestricttarget\" not specified in this data.");
                $this->continueprocessing = false;
            }
        }
    }

    
    protected function log_line($string) {

        if (!PHPUNIT_TEST) {
            mtrace($string);
        }
        if ($this->logfp) {
            fwrite($this->logfp, $string . "\n");
        }
    }

    
    protected static function decode_timeframe($string) {
        $ret = new stdClass();
        $ret->begin = $ret->end = 0;
                                if (preg_match('{<begin\s+restrict="1">(\d\d\d\d)-(\d\d)-(\d\d)</begin>}is', $string, $matches)) {
            $ret->begin = mktime(0, 0, 0, $matches[2], $matches[3], $matches[1]);
        }
        if (preg_match('{<end\s+restrict="1">(\d\d\d\d)-(\d\d)-(\d\d)</end>}is', $string, $matches)) {
            $ret->end = mktime(0, 0, 0, $matches[2], $matches[3], $matches[1]);
        }
        return $ret;
    }

    
    protected function load_role_mappings() {
        require_once('locallib.php');

        $imsroles = new imsenterprise_roles();
        $imsroles = $imsroles->get_imsroles();

        $this->rolemappings = array();
        foreach ($imsroles as $imsrolenum => $imsrolename) {
            $this->rolemappings[$imsrolenum] = $this->rolemappings[$imsrolename] = $this->get_config('imsrolemap' . $imsrolenum);
        }
    }

    
    protected function load_course_mappings() {
        require_once('locallib.php');

        $imsnames = new imsenterprise_courses();
        $courseattrs = $imsnames->get_courseattrs();

        $this->coursemappings = array();
        foreach ($courseattrs as $courseattr) {
            $this->coursemappings[$courseattr] = $this->get_config('imscoursemap' . $courseattr);
        }
    }

    
    public function enrol_imsenterprise_allow_group_member_remove($itemid, $groupid, $userid) {
        return false;
    }


    
    private function get_default_category_id() {
        global $CFG;
        require_once($CFG->libdir.'/coursecatlib.php');

        static $defaultcategoryid = null;

        if ($defaultcategoryid === null) {
            $category = coursecat::get_default();
            $defaultcategoryid = $category->id;
        }

        return $defaultcategoryid;
    }

    
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/imsenterprise:config', $context);
    }

    
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/imsenterprise:config', $context);
    }
}
