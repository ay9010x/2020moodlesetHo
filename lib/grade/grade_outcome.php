<?php



defined('MOODLE_INTERNAL') || die();

require_once('grade_object.php');


class grade_outcome extends grade_object {
    
    public $table = 'grade_outcomes';

    
    public $required_fields = array('id', 'courseid', 'shortname', 'fullname', 'scaleid','description',
                                 'descriptionformat', 'timecreated', 'timemodified', 'usermodified');

    
    public $courseid;

    
    public $shortname;

    
    public $fullname;

    
    public $scale;

    
    public $scaleid;

    
    public $description;

    
    public $usermodified;

    
    public function delete($source=null) {
        global $DB;
        if (!empty($this->courseid)) {
            $DB->delete_records('grade_outcomes_courses', array('outcomeid' => $this->id, 'courseid' => $this->courseid));
        }
        if (parent::delete($source)) {
            $context = context_system::instance();
            $fs = get_file_storage();
            $files = $fs->get_area_files($context->id, 'grade', 'outcome', $this->id);
            foreach ($files as $file) {
                $file->delete();
            }
            return true;
        }
        return false;
    }

    
    public function insert($source=null) {
        global $DB;

        $this->timecreated = $this->timemodified = time();

        if ($result = parent::insert($source)) {
            if (!empty($this->courseid)) {
                $goc = new stdClass();
                $goc->courseid = $this->courseid;
                $goc->outcomeid = $this->id;
                $DB->insert_record('grade_outcomes_courses', $goc);
            }
        }
        return $result;
    }

    
    public function update($source=null) {
        $this->timemodified = time();

        if ($result = parent::update($source)) {
            if (!empty($this->courseid)) {
                $this->use_in($this->courseid);
            }
        }
        return $result;
    }

    
    public function use_in($courseid) {
        global $DB;
        if (!empty($this->courseid) and $courseid != $this->courseid) {
            return false;
        }

        if (!$DB->record_exists('grade_outcomes_courses', array('courseid' => $courseid, 'outcomeid' => $this->id))) {
            $goc = new stdClass();
            $goc->courseid  = $courseid;
            $goc->outcomeid = $this->id;
            $DB->insert_record('grade_outcomes_courses', $goc);
        }
        return true;
    }

    
    public static function fetch($params) {
        return grade_object::fetch_helper('grade_outcomes', 'grade_outcome', $params);
    }

    
    public static function fetch_all($params) {
        return grade_object::fetch_all_helper('grade_outcomes', 'grade_outcome', $params);
    }

    
    public function load_scale() {
        if (empty($this->scale->id) or $this->scale->id != $this->scaleid) {
            $this->scale = grade_scale::fetch(array('id'=>$this->scaleid));
            $this->scale->load_items();
        }
        return $this->scale;
    }

    
    public static function fetch_all_global() {
        if (!$outcomes = grade_outcome::fetch_all(array('courseid'=>null))) {
            $outcomes = array();
        }
        return $outcomes;
    }

    
    public static function fetch_all_local($courseid) {
        if (!$outcomes =grade_outcome::fetch_all(array('courseid'=>$courseid))) {
            $outcomes = array();
        }
        return $outcomes;
    }

    
    public static function fetch_all_available($courseid) {
        global $CFG, $DB;

        $result = array();
        $params = array($courseid);
        $sql = "SELECT go.*
                  FROM {grade_outcomes} go, {grade_outcomes_courses} goc
                 WHERE go.id = goc.outcomeid AND goc.courseid = ?
              ORDER BY go.id ASC";

        if ($datas = $DB->get_records_sql($sql, $params)) {
            foreach($datas as $data) {
                $instance = new grade_outcome();
                grade_object::set_properties($instance, $data);
                $result[$instance->id] = $instance;
            }
        }
        return $result;
    }


    
    public function get_name() {
        return format_string($this->fullname);
    }

    
    public function get_shortname() {
        return $this->shortname;
    }

    
    public function get_description() {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $options = new stdClass;
        $options->noclean = true;
        $systemcontext = context_system::instance();
        $description = file_rewrite_pluginfile_urls($this->description, 'pluginfile.php', $systemcontext->id, 'grade', 'outcome', $this->id);
        return format_text($description, $this->descriptionformat, $options);
    }

    
    public function can_delete() {
        if ($this->get_item_uses_count()) {
            return false;
        }
        if (empty($this->courseid)) {
            if ($this->get_course_uses_count()) {
                return false;
            }
        }
        return true;
    }

    
    public function get_course_uses_count() {
        global $DB;

        if (!empty($this->courseid)) {
            return 1;
        }

        return $DB->count_records('grade_outcomes_courses', array('outcomeid' => $this->id));
    }

    
    public function get_item_uses_count() {
        global $DB;
        return $DB->count_records('grade_items', array('outcomeid' => $this->id));
    }

    
    public function get_grade_info($courseid=null, $average=true, $items=false) {
        global $CFG, $DB;

        if (!isset($this->id)) {
            debugging("You must setup the outcome's id before calling its get_grade_info() method!");
            return false;         }

        if ($average === false && $items === false) {
            debugging('Either the 1st or 2nd param of grade_outcome::get_grade_info() must be true, or both, but not both false!');
            return false;
        }

        $params = array($this->id);

        $wheresql = '';
        if (!is_null($courseid)) {
            $wheresql = " AND {grade_items}.courseid = ? ";
            $params[] = $courseid;
        }

        $selectadd = '';
        if ($items !== false) {
            $selectadd = ", {grade_items}.* ";
        }

        $sql = "SELECT finalgrade $selectadd
                  FROM {grade_grades}, {grade_items}, {grade_outcomes}
                 WHERE {grade_outcomes}.id = {grade_items}.outcomeid
                   AND {grade_items}.id = {grade_grades}.itemid
                   AND {grade_outcomes}.id = ?
                   $wheresql";

        $grades = $DB->get_records_sql($sql, $params);
        $retval = array();

        if ($average !== false && count($grades) > 0) {
            $count = 0;
            $total = 0;

            foreach ($grades as $k => $grade) {
                                if (!is_null($grade->finalgrade)) {
                    $total += $grade->finalgrade;
                    $count++;
                }
                unset($grades[$k]->finalgrade);
            }

            $retval['avg'] = $total / $count;
        }

        if ($items !== false) {
            foreach ($grades as $grade) {
                $retval['items'][$grade->id] = new grade_item($grade);
            }
        }

        return $retval;
    }
}
