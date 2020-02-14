<?php



defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');


class assignfeedback_offline_grade_importer {

    
    public $importid;

    
    private $csvreader;

    
    private $assignment;

    
    private $gradeindex = -1;

    
    private $idindex = -1;

    
    private $modifiedindex = -1;

    
    private $validusers;

    
    private $feedbackcolumnindexes = array();

    
    private $encoding;

    
    private $separator;

    
    public function __construct($importid, assign $assignment, $encoding = 'utf-8', $separator = 'comma') {
        $this->importid = $importid;
        $this->assignment = $assignment;
        $this->encoding = $encoding;
        $this->separator = $separator;
    }

    
    public function parsecsv($csvdata) {
        $this->csvreader = new csv_import_reader($this->importid, 'assignfeedback_offline');
        $this->csvreader->load_csv_content($csvdata, $this->encoding, $this->separator);
    }

    
    public function init() {
        if ($this->csvreader == null) {
            $this->csvreader = new csv_import_reader($this->importid, 'assignfeedback_offline');
        }
        $this->csvreader->init();

        $columns = $this->csvreader->get_columns();

        $strgrade = get_string('grade');
        $strid = get_string('recordid', 'assign');
        $strmodified = get_string('lastmodifiedgrade', 'assign');

        foreach ($this->assignment->get_feedback_plugins() as $plugin) {
            if ($plugin->is_enabled() && $plugin->is_visible()) {
                foreach ($plugin->get_editor_fields() as $field => $description) {
                    $this->feedbackcolumnindexes[$description] = array('plugin'=>$plugin,
                                                                       'field'=>$field,
                                                                       'description'=>$description);
                }
            }
        }

        if ($columns) {
            foreach ($columns as $index => $column) {
                if (isset($this->feedbackcolumnindexes[$column])) {
                    $this->feedbackcolumnindexes[$column]['index'] = $index;
                }
                if ($column == $strgrade) {
                    $this->gradeindex = $index;
                }
                if ($column == $strid) {
                    $this->idindex = $index;
                }
                if ($column == $strmodified) {
                    $this->modifiedindex = $index;
                }
            }
        }

        if ($this->idindex < 0 || $this->gradeindex < 0 || $this->modifiedindex < 0) {
            return false;
        }

        $groupmode = groups_get_activity_groupmode($this->assignment->get_course_module());
                $groupid = 0;
        $groupname = '';
        if ($groupmode) {
            $groupid = groups_get_activity_group($this->assignment->get_course_module(), true);
            $groupname = groups_get_group_name($groupid).'-';
        }
        $this->validusers = $this->assignment->list_participants($groupid, false);
        return true;
    }

    
    public function get_encoding() {
        return $this->encoding;
    }

    
    public function get_separator() {
        return $this->separator;
    }

    
    public function next() {
        global $DB;
        $result = new stdClass();

        while ($record = $this->csvreader->next()) {
            $idstr = $record[$this->idindex];
                        $id = substr($idstr, strlen(get_string('hiddenuser', 'assign')));
            if ($userid = $this->assignment->get_user_id_for_uniqueid($id)) {
                if (array_key_exists($userid, $this->validusers)) {
                    $result->grade = $record[$this->gradeindex];
                    $result->modified = strtotime($record[$this->modifiedindex]);
                    $result->user = $this->validusers[$userid];
                    $result->feedback = array();
                    foreach ($this->feedbackcolumnindexes as $description => $details) {
                        if (!empty($details['index'])) {
                            $details['value'] = $record[$details['index']];
                            $result->feedback[] = $details;
                        }
                    }

                    return $result;
                }
            }
        }

                return false;
    }

    
    public function close($delete) {
        $this->csvreader->close();
        if ($delete) {
            $this->csvreader->cleanup();
        }
    }
}

