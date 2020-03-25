<?php



defined('MOODLE_INTERNAL') || die();


class gradeimport_csv_load_data {

    
    protected $error;
    
    protected $iid;
    
    protected $headers;
    
    protected $previewdata;

        
    protected $newgrades;
    
    protected $newfeedbacks;
    
    protected $studentid;

        
    protected $status;
    
    protected $importcode;
    
    protected $gradebookerrors;
    
    protected $newgradeitems;

    
    public function load_csv_content($text, $encoding, $separator, $previewrows) {
        $this->raise_limits();

        $this->iid = csv_import_reader::get_new_iid('grade');
        $csvimport = new csv_import_reader($this->iid, 'grade');

        $csvimport->load_csv_content($text, $encoding, $separator);
        $this->error = $csvimport->get_error();

                if (empty($this->error)) {

                        $this->headers = $csvimport->get_columns();
            $this->trim_headers();

            $csvimport->init();
            $this->previewdata = array();

            for ($numlines = 0; $numlines <= $previewrows; $numlines++) {
                $lines = $csvimport->next();
                if ($lines) {
                    $this->previewdata[] = $lines;
                }
            }
        }
    }

    
    public static function fetch_grade_items($courseid) {
        $gradeitems = null;
        if ($allgradeitems = grade_item::fetch_all(array('courseid' => $courseid))) {
            foreach ($allgradeitems as $gradeitem) {
                                if ($gradeitem->itemtype == 'course' || $gradeitem->itemtype == 'category') {
                    continue;
                }

                $displaystring = null;
                if (!empty($gradeitem->itemmodule)) {
                    $displaystring = get_string('modulename', $gradeitem->itemmodule).get_string('labelsep', 'langconfig')
                            .$gradeitem->get_name();
                } else {
                    $displaystring = $gradeitem->get_name();
                }
                $gradeitems[$gradeitem->id] = $displaystring;
            }
        }
        return $gradeitems;
    }

    
    protected function trim_headers() {
        foreach ($this->headers as $i => $h) {
            $h = trim($h);             $h = clean_param($h, PARAM_RAW);             $this->headers[$i] = $h;
        }
    }

    
    protected function raise_limits() {
                                core_php_time_limit::raise();
        raise_memory_limit(MEMORY_EXTRA);
    }

    
    protected function insert_grade_record($record, $studentid) {
        global $DB, $USER, $CFG;
        $record->importcode = $this->importcode;
        $record->userid     = $studentid;
        $record->importer   = $USER->id;
                $gradepointmaximum = 100;
                if ($CFG->unlimitedgrades) {
            $gradepointmaximum = $CFG->gradepointmax;
        }
                        if (!isset($record->finalgrade) || $record->finalgrade <= $gradepointmaximum) {
            return $DB->insert_record('grade_import_values', $record);
        } else {
            $this->cleanup_import(get_string('gradevaluetoobig', 'grades', $gradepointmaximum));
            return null;
        }
    }

    
    protected function import_new_grade_item($header, $key, $value) {
        global $DB, $USER;

                if (empty($this->newgradeitems[$key])) {

            $newgradeitem = new stdClass();
            $newgradeitem->itemname = $header[$key];
            $newgradeitem->importcode = $this->importcode;
            $newgradeitem->importer = $USER->id;

                        $this->newgradeitems[$key] = $DB->insert_record('grade_import_newitem', $newgradeitem);
        }
        $newgrade = new stdClass();
        $newgrade->newgradeitem = $this->newgradeitems[$key];

        $trimmed = trim($value);
        if ($trimmed === '' or $trimmed == '-') {
                        $newgrade->finalgrade = null;
        } else {
                        $newgrade->finalgrade = $value;
        }
        $this->newgrades[] = $newgrade;
        return $newgrade;
    }

    
    protected function check_user_exists($value, $userfields) {
        global $DB;

        $usercheckproblem = false;
        $user = null;
                try {
            $user = $DB->get_record('user', array($userfields['field'] => $value));
        } catch (Exception $e) {
            $usercheckproblem = true;
        }
                if (!$user || $usercheckproblem) {
            $usermappingerrorobj = new stdClass();
            $usermappingerrorobj->field = $userfields['label'];
            $usermappingerrorobj->value = $value;
            $this->cleanup_import(get_string('usermappingerror', 'grades', $usermappingerrorobj));
            unset($usermappingerrorobj);
            return null;
        }
        return $user->id;
    }

    
    protected function create_feedback($courseid, $itemid, $value) {
                        if (!new grade_item(array('id' => $itemid, 'courseid' => $courseid))) {
                                    $this->cleanup_import(get_string('importfailed', 'grades'));
            return null;
        }

                $feedback = new stdClass();
        $feedback->itemid   = $itemid;
        $feedback->feedback = $value;
        return $feedback;
    }

    
    protected function update_grade_item($courseid, $map, $key, $verbosescales, $value) {
                        if (!$gradeitem = new grade_item(array('id' => $map[$key], 'courseid' => $courseid))) {
                                    $this->cleanup_import(get_string('importfailed', 'grades'));
            return null;
        }

                if ($gradeitem->is_locked()) {
            $this->cleanup_import(get_string('gradeitemlocked', 'grades'));
            return null;
        }

        $newgrade = new stdClass();
        $newgrade->itemid = $gradeitem->id;
        if ($gradeitem->gradetype == GRADE_TYPE_SCALE and $verbosescales) {
            if ($value === '' or $value == '-') {
                $value = null;             } else {
                $scale = $gradeitem->load_scale();
                $scales = explode(',', $scale->scale);
                $scales = array_map('trim', $scales);                 array_unshift($scales, '-');                 $key = array_search($value, $scales);
                if ($key === false) {
                    $this->cleanup_import(get_string('badgrade', 'grades'));
                    return null;
                }
                $value = $key;
            }
            $newgrade->finalgrade = $value;
        } else {
            if ($value === '' or $value == '-') {
                $value = null;             } else {
                                $validvalue = unformat_float($value, true);
                if ($validvalue !== false) {
                    $value = $validvalue;
                } else {
                                        $this->cleanup_import(get_string('badgrade', 'grades'));
                    return null;
                }
            }
            $newgrade->finalgrade = $value;
        }
        $this->newgrades[] = $newgrade;
        return $this->newgrades;
    }

    
    protected function cleanup_import($notification) {
        $this->status = false;
        import_cleanup($this->importcode);
        $this->gradebookerrors[] = $notification;
    }

    
    protected function map_user_data_with_value($mappingidentifier, $value, $header, $map, $key, $courseid, $feedbackgradeid,
            $verbosescales) {

                $userfields = array(
            'userid' => array(
                'field' => 'id',
                'label' => 'id',
            ),
            'useridnumber' => array(
                'field' => 'idnumber',
                'label' => 'idnumber',
            ),
            'useremail' => array(
                'field' => 'email',
                'label' => 'email address',
            ),
            'username' => array(
                'field' => 'username',
                'label' => 'username',
            ),
        );

        switch ($mappingidentifier) {
            case 'userid':
            case 'useridnumber':
            case 'useremail':
            case 'username':
                                if (!empty($value)) {
                    $this->studentid = $this->check_user_exists($value, $userfields[$mappingidentifier]);
                }
            break;
            case 'new':
                $this->import_new_grade_item($header, $key, $value);
            break;
            case 'feedback':
                if ($feedbackgradeid) {
                    $feedback = $this->create_feedback($courseid, $feedbackgradeid, $value);
                    if (isset($feedback)) {
                        $this->newfeedbacks[] = $feedback;
                    }
                }
            break;
            default:
                                if (!empty($map[$key])) {
                    $this->newgrades = $this->update_grade_item($courseid, $map, $key, $verbosescales, $value,
                            $mappingidentifier);
                }
                                            break;
        }
    }

    
    public function prepare_import_grade_data($header, $formdata, $csvimport, $courseid, $separatemode, $currentgroup,
            $verbosescales) {
        global $DB, $USER;

                $this->importcode = $formdata->importcode;
        $this->status = true;
        $this->headers = $header;
        $this->studentid = null;
        $this->gradebookerrors = null;
        $forceimport = $formdata->forceimport;
                $this->newgradeitems = array();
        $this->trim_headers();
        $timeexportkey = null;
        $map = array();
                foreach ($header as $i => $head) {
            if (isset($formdata->{'mapping_'.$i})) {
                $map[$i] = $formdata->{'mapping_'.$i};
            }
            if ($head == get_string('timeexported', 'gradeexport_txt')) {
                $timeexportkey = $i;
            }
        }

                $map[clean_param($formdata->mapfrom, PARAM_RAW)] = clean_param($formdata->mapto, PARAM_RAW);

                $maperrors = array();
        foreach ($map as $i => $j) {
            if ($j == 0) {
                                continue;
            } else {
                if (!isset($maperrors[$j])) {
                    $maperrors[$j] = true;
                } else {
                                        print_error('cannotmapfield', '', '', $j);
                }
            }
        }

        $this->raise_limits();

        $csvimport->init();

        while ($line = $csvimport->next()) {
            if (count($line) <= 1) {
                                continue;
            }

                        $this->newgrades = array();
                        $this->newfeedbacks = array();
                        foreach ($line as $key => $value) {

                $value = clean_param($value, PARAM_RAW);
                $value = trim($value);

                

                                $mappingbase = explode("_", $map[$key]);
                $mappingidentifier = $mappingbase[0];
                                if (isset($mappingbase[1])) {
                    $feedbackgradeid = (int)$mappingbase[1];
                } else {
                    $feedbackgradeid = '';
                }

                $this->map_user_data_with_value($mappingidentifier, $value, $header, $map, $key, $courseid, $feedbackgradeid,
                        $verbosescales);
                if ($this->status === false) {
                    return $this->status;
                }
            }

                        if (empty($this->studentid) || !is_numeric($this->studentid)) {
                                $this->cleanup_import(get_string('usermappingerrorusernotfound', 'grades'));
                break;
            }

            if ($separatemode and !groups_is_member($currentgroup, $this->studentid)) {
                                $this->cleanup_import(get_string('usermappingerrorcurrentgroup', 'grades'));
                break;
            }

                        if ($this->status and !empty($this->newgrades)) {

                foreach ($this->newgrades as $newgrade) {

                                        if (!empty($newgrade->itemid) and $gradegrade = new grade_grade(array('itemid' => $newgrade->itemid,
                            'userid' => $this->studentid))) {
                        if ($gradegrade->is_locked()) {
                                                        $this->cleanup_import(get_string('gradelocked', 'grades'));
                            return $this->status;
                        }
                                                if (!$forceimport && !empty($timeexportkey)) {
                            $exportedtime = $line[$timeexportkey];
                            if (clean_param($exportedtime, PARAM_INT) != $exportedtime || $exportedtime > time() ||
                                    $exportedtime < strtotime("-1 year", time())) {
                                                                $this->cleanup_import(get_string('invalidgradeexporteddate', 'grades'));
                                return $this->status;

                            }
                            $timemodified = $gradegrade->get_dategraded();
                            if (!empty($timemodified) && ($exportedtime < $timemodified)) {
                                                                $user = core_user::get_user($this->studentid);
                                $this->cleanup_import(get_string('gradealreadyupdated', 'grades', fullname($user)));
                                return $this->status;
                            }
                        }
                    }
                    $insertid = self::insert_grade_record($newgrade, $this->studentid);
                                        if (empty($insertid)) {
                        return null;
                    }
                }
            }

                        if ($this->status and !empty($this->newfeedbacks)) {
                foreach ($this->newfeedbacks as $newfeedback) {
                    $sql = "SELECT *
                              FROM {grade_import_values}
                             WHERE importcode=? AND userid=? AND itemid=? AND importer=?";
                    if ($feedback = $DB->get_record_sql($sql, array($this->importcode, $this->studentid, $newfeedback->itemid,
                            $USER->id))) {
                        $newfeedback->id = $feedback->id;
                        $DB->update_record('grade_import_values', $newfeedback);

                    } else {
                                                $newfeedback->importonlyfeedback = true;
                        $insertid = self::insert_grade_record($newfeedback, $this->studentid);
                                                if (empty($insertid)) {
                            return null;
                        }
                    }
                }
            }
        }
        return $this->status;
    }

    
    public function get_headers() {
        return $this->headers;
    }

    
    public function get_error() {
        return $this->error;
    }

    
    public function get_iid() {
        return $this->iid;
    }

    
    public function get_previewdata() {
        return $this->previewdata;
    }

    
    public function get_gradebookerrors() {
        return $this->gradebookerrors;
    }
}
