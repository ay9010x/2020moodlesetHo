<?php



defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->dirroot . '/course/lib.php');


class tool_uploadcourse_course {

    
    const DO_CREATE = 1;

    
    const DO_UPDATE = 2;

    
    const DO_DELETE = 3;

    
    protected $data = array();

    
    protected $defaults = array();

    
    protected $enrolmentdata;

    
    protected $errors = array();

    
    protected $id;

    
    protected $importoptions = array();

    
    protected $mode;

    
    protected $options = array();

    
    protected $do;

    
    protected $prepared = false;

    
    protected $processstarted = false;

    
    protected $rawdata = array();

    
    protected $restoredata;

    
    protected $shortname;

    
    protected $statuses = array();

    
    protected $updatemode;

    
    static protected $validfields = array('fullname', 'shortname', 'idnumber', 'category', 'visible', 'startdate',
        'summary', 'format', 'theme', 'lang', 'newsitems', 'showgrades', 'showreports', 'legacyfiles', 'maxbytes',
        'groupmode', 'groupmodeforce', 'groupmodeforce', 'enablecompletion');

    
    static protected $mandatoryfields = array('fullname', 'category');

    
    static protected $optionfields = array('delete' => false, 'rename' => null, 'backupfile' => null,
        'templatecourse' => null, 'reset' => false);

    
    static protected $importoptionsdefaults = array('canrename' => false, 'candelete' => false, 'canreset' => false,
        'reset' => false, 'restoredir' => null, 'shortnametemplate' => null);

    
    public function __construct($mode, $updatemode, $rawdata, $defaults = array(), $importoptions = array()) {

        if ($mode !== tool_uploadcourse_processor::MODE_CREATE_NEW &&
                $mode !== tool_uploadcourse_processor::MODE_CREATE_ALL &&
                $mode !== tool_uploadcourse_processor::MODE_CREATE_OR_UPDATE &&
                $mode !== tool_uploadcourse_processor::MODE_UPDATE_ONLY) {
            throw new coding_exception('Incorrect mode.');
        } else if ($updatemode !== tool_uploadcourse_processor::UPDATE_NOTHING &&
                $updatemode !== tool_uploadcourse_processor::UPDATE_ALL_WITH_DATA_ONLY &&
                $updatemode !== tool_uploadcourse_processor::UPDATE_ALL_WITH_DATA_OR_DEFAUTLS &&
                $updatemode !== tool_uploadcourse_processor::UPDATE_MISSING_WITH_DATA_OR_DEFAUTLS) {
            throw new coding_exception('Incorrect update mode.');
        }

        $this->mode = $mode;
        $this->updatemode = $updatemode;

        if (isset($rawdata['shortname'])) {
            $this->shortname = $rawdata['shortname'];
        }
        $this->rawdata = $rawdata;
        $this->defaults = $defaults;

                foreach (self::$optionfields as $option => $default) {
            $this->options[$option] = isset($rawdata[$option]) ? $rawdata[$option] : $default;
        }

                foreach (self::$importoptionsdefaults as $option => $default) {
            $this->importoptions[$option] = isset($importoptions[$option]) ? $importoptions[$option] : $default;
        }
    }

    
    public function can_create() {
        return in_array($this->mode, array(tool_uploadcourse_processor::MODE_CREATE_ALL,
            tool_uploadcourse_processor::MODE_CREATE_NEW,
            tool_uploadcourse_processor::MODE_CREATE_OR_UPDATE)
        );
    }

    
    public function can_delete() {
        return $this->importoptions['candelete'];
    }

    
    public function can_only_create() {
        return in_array($this->mode, array(tool_uploadcourse_processor::MODE_CREATE_ALL,
            tool_uploadcourse_processor::MODE_CREATE_NEW));
    }

    
    public function can_rename() {
        return $this->importoptions['canrename'];
    }

    
    public function can_reset() {
        return $this->importoptions['canreset'];
    }

    
    public function can_update() {
        return in_array($this->mode,
                array(
                    tool_uploadcourse_processor::MODE_UPDATE_ONLY,
                    tool_uploadcourse_processor::MODE_CREATE_OR_UPDATE)
                ) && $this->updatemode != tool_uploadcourse_processor::UPDATE_NOTHING;
    }

    
    public function can_use_defaults() {
        return in_array($this->updatemode, array(tool_uploadcourse_processor::UPDATE_MISSING_WITH_DATA_OR_DEFAUTLS,
            tool_uploadcourse_processor::UPDATE_ALL_WITH_DATA_OR_DEFAUTLS));
    }

    
    protected function delete() {
        global $DB;
        $this->id = $DB->get_field_select('course', 'id', 'shortname = :shortname',
            array('shortname' => $this->shortname), MUST_EXIST);
        return delete_course($this->id, false);
    }

    
    protected function error($code, lang_string $message) {
        if (array_key_exists($code, $this->errors)) {
            throw new coding_exception('Error code already defined');
        }
        $this->errors[$code] = $message;
    }

    
    protected function exists($shortname = null) {
        global $DB;
        if (is_null($shortname)) {
            $shortname = $this->shortname;
        }
        if (!empty($shortname) || is_numeric($shortname)) {
            return $DB->record_exists('course', array('shortname' => $shortname));
        }
        return false;
    }

    
    public function get_data() {
        return $this->data;
    }

    
    public function get_errors() {
        return $this->errors;
    }

    
    protected function get_final_create_data($data) {
        foreach (self::$validfields as $field) {
            if (!isset($data[$field]) && isset($this->defaults[$field])) {
                $data[$field] = $this->defaults[$field];
            }
        }
        $data['shortname'] = $this->shortname;
        return $data;
    }

    
    protected function get_final_update_data($data, $usedefaults = false, $missingonly = false) {
        global $DB;
        $newdata = array();
        $existingdata = $DB->get_record('course', array('shortname' => $this->shortname));
        foreach (self::$validfields as $field) {
            if ($missingonly) {
                if (!is_null($existingdata->$field) and $existingdata->$field !== '') {
                    continue;
                }
            }
            if (isset($data[$field])) {
                $newdata[$field] = $data[$field];
            } else if ($usedefaults && isset($this->defaults[$field])) {
                $newdata[$field] = $this->defaults[$field];
            }
        }
        $newdata['id'] =  $existingdata->id;
        return $newdata;
    }

    
    public function get_id() {
        if (!$this->processstarted) {
            throw new coding_exception('The course has not been processed yet!');
        }
        return $this->id;
    }

    
    protected function get_restore_content_dir() {
        $backupfile = null;
        $shortname = null;

        if (!empty($this->options['backupfile'])) {
            $backupfile = $this->options['backupfile'];
        } else if (!empty($this->options['templatecourse']) || is_numeric($this->options['templatecourse'])) {
            $shortname = $this->options['templatecourse'];
        }

        $errors = array();
        $dir = tool_uploadcourse_helper::get_restore_content_dir($backupfile, $shortname, $errors);
        if (!empty($errors)) {
            foreach ($errors as $key => $message) {
                $this->error($key, $message);
            }
            return false;
        } else if ($dir === false) {
                        $dir = null;
        }

        if (empty($dir) && !empty($this->importoptions['restoredir'])) {
            $dir = $this->importoptions['restoredir'];
        }

        return $dir;
    }

    
    public function get_statuses() {
        return $this->statuses;
    }

    
    public function has_errors() {
        return !empty($this->errors);
    }

    
    public function prepare() {
        global $DB, $SITE;
        $this->prepared = true;

                if (!empty($this->shortname) || is_numeric($this->shortname)) {
            if ($this->shortname !== clean_param($this->shortname, PARAM_TEXT)) {
                $this->error('invalidshortname', new lang_string('invalidshortname', 'tool_uploadcourse'));
                return false;
            }
        }

        $exists = $this->exists();

                if ($this->options['delete']) {
            if (!$exists) {
                $this->error('cannotdeletecoursenotexist', new lang_string('cannotdeletecoursenotexist', 'tool_uploadcourse'));
                return false;
            } else if (!$this->can_delete()) {
                $this->error('coursedeletionnotallowed', new lang_string('coursedeletionnotallowed', 'tool_uploadcourse'));
                return false;
            }

            $this->do = self::DO_DELETE;
            return true;
        }

                if ($exists) {
            if ($this->mode === tool_uploadcourse_processor::MODE_CREATE_NEW) {
                $this->error('courseexistsanduploadnotallowed',
                    new lang_string('courseexistsanduploadnotallowed', 'tool_uploadcourse'));
                return false;
            } else if ($this->can_update()) {
                                if ($this->shortname == $SITE->shortname) {
                    $this->error('cannotupdatefrontpage', new lang_string('cannotupdatefrontpage', 'tool_uploadcourse'));
                    return false;
                }
            }
        } else {
            if (!$this->can_create()) {
                $this->error('coursedoesnotexistandcreatenotallowed',
                    new lang_string('coursedoesnotexistandcreatenotallowed', 'tool_uploadcourse'));
                return false;
            }
        }

                $coursedata = array();
        foreach ($this->rawdata as $field => $value) {
            if (!in_array($field, self::$validfields)) {
                continue;
            } else if ($field == 'shortname') {
                                continue;
            }
            $coursedata[$field] = $value;
        }

        $mode = $this->mode;
        $updatemode = $this->updatemode;
        $usedefaults = $this->can_use_defaults();

                $errors = array();
        $catid = tool_uploadcourse_helper::resolve_category($this->rawdata, $errors);
        if (empty($errors)) {
            $coursedata['category'] = $catid;
        } else {
            foreach ($errors as $key => $message) {
                $this->error($key, $message);
            }
            return false;
        }

                if (!$exists || $mode === tool_uploadcourse_processor::MODE_CREATE_ALL) {

                        $errors = array();
            foreach (self::$mandatoryfields as $field) {
                if ((!isset($coursedata[$field]) || $coursedata[$field] === '') &&
                        (!isset($this->defaults[$field]) || $this->defaults[$field] === '')) {
                    $errors[] = $field;
                }
            }
            if (!empty($errors)) {
                $this->error('missingmandatoryfields', new lang_string('missingmandatoryfields', 'tool_uploadcourse',
                    implode(', ', $errors)));
                return false;
            }
        }

                if (!empty($this->options['rename']) || is_numeric($this->options['rename'])) {
            if (!$this->can_update()) {
                $this->error('canonlyrenameinupdatemode', new lang_string('canonlyrenameinupdatemode', 'tool_uploadcourse'));
                return false;
            } else if (!$exists) {
                $this->error('cannotrenamecoursenotexist', new lang_string('cannotrenamecoursenotexist', 'tool_uploadcourse'));
                return false;
            } else if (!$this->can_rename()) {
                $this->error('courserenamingnotallowed', new lang_string('courserenamingnotallowed', 'tool_uploadcourse'));
                return false;
            } else if ($this->options['rename'] !== clean_param($this->options['rename'], PARAM_TEXT)) {
                $this->error('invalidshortname', new lang_string('invalidshortname', 'tool_uploadcourse'));
                return false;
            } else if ($this->exists($this->options['rename'])) {
                $this->error('cannotrenameshortnamealreadyinuse',
                    new lang_string('cannotrenameshortnamealreadyinuse', 'tool_uploadcourse'));
                return false;
            } else if (isset($coursedata['idnumber']) &&
                    $DB->count_records_select('course', 'idnumber = :idn AND shortname != :sn',
                    array('idn' => $coursedata['idnumber'], 'sn' => $this->shortname)) > 0) {
                $this->error('cannotrenameidnumberconflict', new lang_string('cannotrenameidnumberconflict', 'tool_uploadcourse'));
                return false;
            }
            $coursedata['shortname'] = $this->options['rename'];
            $this->status('courserenamed', new lang_string('courserenamed', 'tool_uploadcourse',
                array('from' => $this->shortname, 'to' => $coursedata['shortname'])));
        }

                if (empty($this->shortname) && !is_numeric($this->shortname)) {
            if (empty($this->importoptions['shortnametemplate'])) {
                $this->error('missingshortnamenotemplate', new lang_string('missingshortnamenotemplate', 'tool_uploadcourse'));
                return false;
            } else if (!$this->can_only_create()) {
                $this->error('cannotgenerateshortnameupdatemode',
                    new lang_string('cannotgenerateshortnameupdatemode', 'tool_uploadcourse'));
                return false;
            } else {
                $newshortname = tool_uploadcourse_helper::generate_shortname($coursedata,
                    $this->importoptions['shortnametemplate']);
                if (is_null($newshortname)) {
                    $this->error('generatedshortnameinvalid', new lang_string('generatedshortnameinvalid', 'tool_uploadcourse'));
                    return false;
                } else if ($this->exists($newshortname)) {
                    if ($mode === tool_uploadcourse_processor::MODE_CREATE_NEW) {
                        $this->error('generatedshortnamealreadyinuse',
                            new lang_string('generatedshortnamealreadyinuse', 'tool_uploadcourse'));
                        return false;
                    }
                    $exists = true;
                }
                $this->status('courseshortnamegenerated', new lang_string('courseshortnamegenerated', 'tool_uploadcourse',
                    $newshortname));
                $this->shortname = $newshortname;
            }
        }

                if ($exists && $mode === tool_uploadcourse_processor::MODE_CREATE_ALL) {
            $original = $this->shortname;
            $this->shortname = tool_uploadcourse_helper::increment_shortname($this->shortname);
            $exists = false;
            if ($this->shortname != $original) {
                $this->status('courseshortnameincremented', new lang_string('courseshortnameincremented', 'tool_uploadcourse',
                    array('from' => $original, 'to' => $this->shortname)));
                if (isset($coursedata['idnumber'])) {
                    $originalidn = $coursedata['idnumber'];
                    $coursedata['idnumber'] = tool_uploadcourse_helper::increment_idnumber($coursedata['idnumber']);
                    if ($originalidn != $coursedata['idnumber']) {
                        $this->status('courseidnumberincremented', new lang_string('courseidnumberincremented', 'tool_uploadcourse',
                            array('from' => $originalidn, 'to' => $coursedata['idnumber'])));
                    }
                }
            }
        }

                if (!$exists && isset($coursedata['idnumber'])) {
            if ($DB->count_records_select('course', 'idnumber = :idn', array('idn' => $coursedata['idnumber'])) > 0) {
                $this->error('idnumberalreadyinuse', new lang_string('idnumberalreadyinuse', 'tool_uploadcourse'));
                return false;
            }
        }

                if (!empty($coursedata['startdate'])) {
            $coursedata['startdate'] = strtotime($coursedata['startdate']);
        }

                switch ($mode) {
            case tool_uploadcourse_processor::MODE_CREATE_NEW:
            case tool_uploadcourse_processor::MODE_CREATE_ALL:
                if ($exists) {
                    $this->error('courseexistsanduploadnotallowed',
                        new lang_string('courseexistsanduploadnotallowed', 'tool_uploadcourse'));
                    return false;
                }
                break;
            case tool_uploadcourse_processor::MODE_UPDATE_ONLY:
                if (!$exists) {
                    $this->error('coursedoesnotexistandcreatenotallowed',
                        new lang_string('coursedoesnotexistandcreatenotallowed', 'tool_uploadcourse'));
                    return false;
                }
                            case tool_uploadcourse_processor::MODE_CREATE_OR_UPDATE:
                if ($exists) {
                    if ($updatemode === tool_uploadcourse_processor::UPDATE_NOTHING) {
                        $this->error('updatemodedoessettonothing',
                            new lang_string('updatemodedoessettonothing', 'tool_uploadcourse'));
                        return false;
                    }
                }
                break;
            default:
                                $this->error('unknownimportmode', new lang_string('unknownimportmode', 'tool_uploadcourse'));
                return false;
        }

                if ($exists) {
            $missingonly = ($updatemode === tool_uploadcourse_processor::UPDATE_MISSING_WITH_DATA_OR_DEFAUTLS);
            $coursedata = $this->get_final_update_data($coursedata, $usedefaults, $missingonly);

                        if ($coursedata['id'] == $SITE->id) {
                $this->error('cannotupdatefrontpage', new lang_string('cannotupdatefrontpage', 'tool_uploadcourse'));
                return false;
            }

            $this->do = self::DO_UPDATE;
        } else {
            $coursedata = $this->get_final_create_data($coursedata);
            $this->do = self::DO_CREATE;
        }

                $errors = array();
        $rolenames = tool_uploadcourse_helper::get_role_names($this->rawdata, $errors);
        if (!empty($errors)) {
            foreach ($errors as $key => $message) {
                $this->error($key, $message);
            }
            return false;
        }
        foreach ($rolenames as $rolekey => $rolename) {
            $coursedata[$rolekey] = $rolename;
        }

                if (!empty($coursedata['format']) && !in_array($coursedata['format'], tool_uploadcourse_helper::get_course_formats())) {
            $this->error('invalidcourseformat', new lang_string('invalidcourseformat', 'tool_uploadcourse'));
            return false;
        }

                $this->data = $coursedata;
        $this->enrolmentdata = tool_uploadcourse_helper::get_enrolment_data($this->rawdata);

        if (isset($this->rawdata['tags']) && strval($this->rawdata['tags']) !== '') {
            $this->data['tags'] = preg_split('/\s*,\s*/', trim($this->rawdata['tags']), -1, PREG_SPLIT_NO_EMPTY);
        }

                                $this->restoredata = $this->get_restore_content_dir();
        if ($this->restoredata === false) {
            return false;
        }

                if ($this->importoptions['reset'] || $this->options['reset']) {
            if ($this->do !== self::DO_UPDATE) {
                $this->error('canonlyresetcourseinupdatemode',
                    new lang_string('canonlyresetcourseinupdatemode', 'tool_uploadcourse'));
                return false;
            } else if (!$this->can_reset()) {
                $this->error('courseresetnotallowed', new lang_string('courseresetnotallowed', 'tool_uploadcourse'));
                return false;
            }
        }

        return true;
    }

    
    public function proceed() {
        global $CFG, $USER;

        if (!$this->prepared) {
            throw new coding_exception('The course has not been prepared.');
        } else if ($this->has_errors()) {
            throw new moodle_exception('Cannot proceed, errors were detected.');
        } else if ($this->processstarted) {
            throw new coding_exception('The process has already been started.');
        }
        $this->processstarted = true;

        if ($this->do === self::DO_DELETE) {
            if ($this->delete()) {
                $this->status('coursedeleted', new lang_string('coursedeleted', 'tool_uploadcourse'));
            } else {
                $this->error('errorwhiledeletingcourse', new lang_string('errorwhiledeletingcourse', 'tool_uploadcourse'));
            }
            return true;
        } else if ($this->do === self::DO_CREATE) {
            $course = create_course((object) $this->data);
            $this->id = $course->id;
            $this->status('coursecreated', new lang_string('coursecreated', 'tool_uploadcourse'));
        } else if ($this->do === self::DO_UPDATE) {
            $course = (object) $this->data;
            update_course($course);
            $this->id = $course->id;
            $this->status('courseupdated', new lang_string('courseupdated', 'tool_uploadcourse'));
        } else {
                        throw new coding_exception('Unknown outcome!');
        }

                if (!empty($this->restoredata)) {
            $rc = new restore_controller($this->restoredata, $course->id, backup::INTERACTIVE_NO,
                backup::MODE_IMPORT, $USER->id, backup::TARGET_CURRENT_ADDING);

                        if ($rc->get_status() == backup::STATUS_REQUIRE_CONV) {
                $rc->convert();
            }
            if ($rc->execute_precheck()) {
                $rc->execute_plan();
                $this->status('courserestored', new lang_string('courserestored', 'tool_uploadcourse'));
            } else {
                $this->error('errorwhilerestoringcourse', new lang_string('errorwhilerestoringthecourse', 'tool_uploadcourse'));
            }
            $rc->destroy();
        }

                $this->process_enrolment_data($course);

                if ($this->importoptions['reset'] || $this->options['reset']) {
            if ($this->do === self::DO_UPDATE && $this->can_reset()) {
                $this->reset($course);
                $this->status('coursereset', new lang_string('coursereset', 'tool_uploadcourse'));
            }
        }

                $context = context_course::instance($course->id);
        $context->mark_dirty();
    }

    
    protected function process_enrolment_data($course) {
        global $DB;

        $enrolmentdata = $this->enrolmentdata;
        if (empty($enrolmentdata)) {
            return;
        }

        $enrolmentplugins = tool_uploadcourse_helper::get_enrolment_plugins();
        $instances = enrol_get_instances($course->id, false);
        foreach ($enrolmentdata as $enrolmethod => $method) {

            $instance = null;
            foreach ($instances as $i) {
                if ($i->enrol == $enrolmethod) {
                    $instance = $i;
                    break;
                }
            }

            $todelete = isset($method['delete']) && $method['delete'];
            $todisable = isset($method['disable']) && $method['disable'];
            unset($method['delete']);
            unset($method['disable']);

            if (!empty($instance) && $todelete) {
                                foreach ($instances as $instance) {
                    if ($instance->enrol == $enrolmethod) {
                        $plugin = $enrolmentplugins[$instance->enrol];
                        $plugin->delete_instance($instance);
                        break;
                    }
                }
            } else if (!empty($instance) && $todisable) {
                                foreach ($instances as $instance) {
                    if ($instance->enrol == $enrolmethod) {
                        $plugin = $enrolmentplugins[$instance->enrol];
                        $plugin->update_status($instance, ENROL_INSTANCE_DISABLED);
                        $enrol_updated = true;
                        break;
                    }
                }
            } else {
                $plugin = null;
                if (empty($instance)) {
                    $plugin = $enrolmentplugins[$enrolmethod];
                    $instance = new stdClass();
                    $instance->id = $plugin->add_default_instance($course);
                    $instance->roleid = $plugin->get_config('roleid');
                    $instance->status = ENROL_INSTANCE_ENABLED;
                } else {
                    $plugin = $enrolmentplugins[$instance->enrol];
                    $plugin->update_status($instance, ENROL_INSTANCE_ENABLED);
                }

                                foreach ($method as $k => $v) {
                    $instance->{$k} = $v;
                }

                                $instance->enrolstartdate = (isset($method['startdate']) ? strtotime($method['startdate']) : 0);
                $instance->enrolenddate = (isset($method['enddate']) ? strtotime($method['enddate']) : 0);

                                if (isset($method['enrolperiod']) && ! empty($method['enrolperiod'])) {
                    if (preg_match('/^\d+$/', $method['enrolperiod'])) {
                        $method['enrolperiod'] = (int) $method['enrolperiod'];
                    } else {
                                                $method['enrolperiod'] = strtotime('1970-01-01 GMT + ' . $method['enrolperiod']);
                    }
                    $instance->enrolperiod = $method['enrolperiod'];
                }
                if ($instance->enrolstartdate > 0 && isset($method['enrolperiod'])) {
                    $instance->enrolenddate = $instance->enrolstartdate + $method['enrolperiod'];
                }
                if ($instance->enrolenddate > 0) {
                    $instance->enrolperiod = $instance->enrolenddate - $instance->enrolstartdate;
                }
                if ($instance->enrolenddate < $instance->enrolstartdate) {
                    $instance->enrolenddate = $instance->enrolstartdate;
                }

                                if (isset($method['role'])) {
                    $roleids = tool_uploadcourse_helper::get_role_ids();
                    if (isset($roleids[$method['role']])) {
                        $instance->roleid = $roleids[$method['role']];
                    }
                }

                $instance->timemodified = time();
                $DB->update_record('enrol', $instance);
            }
        }
    }

    
    protected function reset($course) {
        global $DB;

        $resetdata = new stdClass();
        $resetdata->id = $course->id;
        $resetdata->reset_start_date = time();
        $resetdata->reset_events = true;
        $resetdata->reset_notes = true;
        $resetdata->delete_blog_associations = true;
        $resetdata->reset_completion = true;
        $resetdata->reset_roles_overrides = true;
        $resetdata->reset_roles_local = true;
        $resetdata->reset_groups_members = true;
        $resetdata->reset_groups_remove = true;
        $resetdata->reset_groupings_members = true;
        $resetdata->reset_groupings_remove = true;
        $resetdata->reset_gradebook_items = true;
        $resetdata->reset_gradebook_grades = true;
        $resetdata->reset_comments = true;

        if (empty($course->startdate)) {
            $course->startdate = $DB->get_field_select('course', 'startdate', 'id = :id', array('id' => $course->id));
        }
        $resetdata->reset_start_date_old = $course->startdate;

                $roles = tool_uploadcourse_helper::get_role_ids();
        $resetdata->unenrol_users = array_values($roles);
        $resetdata->unenrol_users[] = 0;    
        return reset_course_userdata($resetdata);
    }

    
    protected function status($code, lang_string $message) {
        if (array_key_exists($code, $this->statuses)) {
            throw new coding_exception('Status code already defined');
        }
        $this->statuses[$code] = $message;
    }

}
