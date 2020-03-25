<?php



defined('MOODLE_INTERNAL') || die();
require_once('grade_object.php');


class grade_item extends grade_object {
    
    public $table = 'grade_items';

    
    public $required_fields = array('id', 'courseid', 'categoryid', 'itemname', 'itemtype', 'itemmodule', 'iteminstance',
                                 'itemnumber', 'iteminfo', 'idnumber', 'calculation', 'gradetype', 'grademax', 'grademin',
                                 'scaleid', 'outcomeid', 'gradepass', 'multfactor', 'plusfactor', 'aggregationcoef',
                                 'aggregationcoef2', 'sortorder', 'display', 'decimals', 'hidden', 'locked', 'locktime',
                                 'needsupdate', 'weightoverride', 'timecreated', 'timemodified');

    
    public $courseid;

    
    public $categoryid;

    
    public $item_category;

    
    public $parent_category;


    
    public $itemname;

    
    public $itemtype;

    
    public $itemmodule;

    
    public $iteminstance;

    
    public $itemnumber;

    
    public $iteminfo;

    
    public $idnumber;

    
    public $calculation;

    
    public $calculation_normalized;
    
    public $formula;

    
    public $gradetype = GRADE_TYPE_VALUE;

    
    public $grademax = 100;

    
    public $grademin = 0;

    
    public $scaleid;

    
    public $scale;

    
    public $outcomeid;

    
    public $outcome;

    
    public $gradepass = 0;

    
    public $multfactor = 1.0;

    
    public $plusfactor = 0;

    
    public $aggregationcoef = 0;

    
    public $aggregationcoef2 = 0;

    
    public $sortorder = 0;

    
    public $display = GRADE_DISPLAY_TYPE_DEFAULT;

    
    public $decimals = null;

    
    public $locked = 0;

    
    public $locktime = 0;

    
    public $needsupdate = 1;

    
    public $weightoverride = 0;

    
    public $dependson_cache = null;

    
    public function __construct($params = null, $fetch = true) {
        global $CFG;
                self::set_properties($this, array('grademax' => $CFG->gradepointdefault));
        parent::__construct($params, $fetch);
    }

    
    public function update($source=null) {
                $this->dependson_cache = null;

                $this->load_scale();

                if (empty($this->outcomeid)) {
            $this->outcomeid = null;
        }

        if ($this->qualifies_for_regrading()) {
            $this->force_regrading();
        }

        $this->timemodified = time();

        $this->grademin        = grade_floatval($this->grademin);
        $this->grademax        = grade_floatval($this->grademax);
        $this->multfactor      = grade_floatval($this->multfactor);
        $this->plusfactor      = grade_floatval($this->plusfactor);
        $this->aggregationcoef = grade_floatval($this->aggregationcoef);
        $this->aggregationcoef2 = grade_floatval($this->aggregationcoef2);

        return parent::update($source);
    }

    
    public function qualifies_for_regrading() {
        if (empty($this->id)) {
            return false;
        }

        $db_item = new grade_item(array('id' => $this->id));

        $calculationdiff = $db_item->calculation != $this->calculation;
        $categorydiff    = $db_item->categoryid  != $this->categoryid;
        $gradetypediff   = $db_item->gradetype   != $this->gradetype;
        $scaleiddiff     = $db_item->scaleid     != $this->scaleid;
        $outcomeiddiff   = $db_item->outcomeid   != $this->outcomeid;
        $locktimediff    = $db_item->locktime    != $this->locktime;
        $grademindiff    = grade_floats_different($db_item->grademin,        $this->grademin);
        $grademaxdiff    = grade_floats_different($db_item->grademax,        $this->grademax);
        $multfactordiff  = grade_floats_different($db_item->multfactor,      $this->multfactor);
        $plusfactordiff  = grade_floats_different($db_item->plusfactor,      $this->plusfactor);
        $acoefdiff       = grade_floats_different($db_item->aggregationcoef, $this->aggregationcoef);
        $acoefdiff2      = grade_floats_different($db_item->aggregationcoef2, $this->aggregationcoef2);
        $weightoverride  = grade_floats_different($db_item->weightoverride, $this->weightoverride);

        $needsupdatediff = !$db_item->needsupdate &&  $this->needsupdate;            $lockeddiff      = !empty($db_item->locked) && empty($this->locked); 
        return ($calculationdiff || $categorydiff || $gradetypediff || $grademaxdiff || $grademindiff || $scaleiddiff
             || $outcomeiddiff || $multfactordiff || $plusfactordiff || $needsupdatediff
             || $lockeddiff || $acoefdiff || $acoefdiff2 || $weightoverride || $locktimediff);
    }

    
    public static function fetch($params) {
        return grade_object::fetch_helper('grade_items', 'grade_item', $params);
    }

    
    public function has_grades() {
        global $DB;

        $count = $DB->count_records_select('grade_grades',
                                           'itemid = :gradeitemid AND finalgrade IS NOT NULL',
                                           array('gradeitemid' => $this->id));
        return $count > 0;
    }

    
    public function has_overridden_grades() {
        global $DB;

        $count = $DB->count_records_select('grade_grades',
                                           'itemid = :gradeitemid AND finalgrade IS NOT NULL AND overridden > 0',
                                           array('gradeitemid' => $this->id));
        return $count > 0;
    }

    
    public static function fetch_all($params) {
        return grade_object::fetch_all_helper('grade_items', 'grade_item', $params);
    }

    
    public function delete($source=null) {
        $this->delete_all_grades($source);
        return parent::delete($source);
    }

    
    public function delete_all_grades($source=null) {
        if (!$this->is_course_item()) {
            $this->force_regrading();
        }

        if ($grades = grade_grade::fetch_all(array('itemid'=>$this->id))) {
            foreach ($grades as $grade) {
                $grade->delete($source);
            }
        }

        return true;
    }

    
    public function insert($source=null) {
        global $CFG, $DB;

        if (empty($this->courseid)) {
            print_error('cannotinsertgrade');
        }

                $this->load_scale();

                if (empty($this->categoryid) and !$this->is_course_item() and !$this->is_category_item()) {
            $course_category = grade_category::fetch_course_category($this->courseid);
            $this->categoryid = $course_category->id;

        }

                $last_sortorder = $DB->get_field_select('grade_items', 'MAX(sortorder)', "courseid = ?", array($this->courseid));
        if (!empty($last_sortorder)) {
            $this->sortorder = $last_sortorder + 1;
        } else {
            $this->sortorder = 1;
        }

                if ($this->itemtype == 'manual') {
            if (empty($this->itemnumber)) {
                $this->itemnumber = 0;
            }
        }

                if (empty($this->outcomeid)) {
            $this->outcomeid = null;
        }

        $this->timecreated = $this->timemodified = time();

        if (parent::insert($source)) {
                        $this->force_regrading();
            return $this->id;

        } else {
            debugging("Could not insert this grade_item in the database!");
            return false;
        }
    }

    
    public function add_idnumber($idnumber) {
        global $DB;
        if (!empty($this->idnumber)) {
            return false;
        }

        if ($this->itemtype == 'mod' and !$this->is_outcome_item()) {
            if ($this->itemnumber == 0) {
                                if (!$cm = get_coursemodule_from_instance($this->itemmodule, $this->iteminstance, $this->courseid)) {
                    return false;
                }
                if (!empty($cm->idnumber)) {
                    return false;
                }
                $DB->set_field('course_modules', 'idnumber', $idnumber, array('id' => $cm->id));
                $this->idnumber = $idnumber;
                return $this->update();
            } else {
                $this->idnumber = $idnumber;
                return $this->update();
            }

        } else {
            $this->idnumber = $idnumber;
            return $this->update();
        }
    }

    
    public function is_locked($userid=NULL) {
        if (!empty($this->locked)) {
            return true;
        }

        if (!empty($userid)) {
            if ($grade = grade_grade::fetch(array('itemid'=>$this->id, 'userid'=>$userid))) {
                $grade->grade_item =& $this;                 return $grade->is_locked();
            }
        }

        return false;
    }

    
    public function set_locked($lockedstate, $cascade=false, $refresh=true) {
        if ($lockedstate) {
                    if ($this->needsupdate) {
                return false;             }

            $this->locked = time();
            $this->update();

            if ($cascade) {
                $grades = $this->get_final();
                foreach($grades as $g) {
                    $grade = new grade_grade($g, false);
                    $grade->grade_item =& $this;
                    $grade->set_locked(1, null, false);
                }
            }

            return true;

        } else {
                    if (!empty($this->locked) and $this->locktime < time()) {
                                $this->locktime = 0;
            }

            $this->locked = 0;
            $this->update();

            if ($cascade) {
                if ($grades = grade_grade::fetch_all(array('itemid'=>$this->id))) {
                    foreach($grades as $grade) {
                        $grade->grade_item =& $this;
                        $grade->set_locked(0, null, false);
                    }
                }
            }

            if ($refresh) {
                                $this->refresh_grades();
            }

            return true;
        }
    }

    
    public function check_locktime() {
        if (!empty($this->locked)) {
            return;         }

        if ($this->locktime and $this->locktime < time()) {
            $this->locked = time();
            $this->update('locktime');
        }
    }

    
    public function set_locktime($locktime) {
        $this->locktime = $locktime;
        $this->update();
    }

    
    public function get_locktime() {
        return $this->locktime;
    }

    
    public function set_hidden($hidden, $cascade=false) {
        parent::set_hidden($hidden, $cascade);

        if ($cascade) {
            if ($grades = grade_grade::fetch_all(array('itemid'=>$this->id))) {
                foreach($grades as $grade) {
                    $grade->grade_item =& $this;
                    $grade->set_hidden($hidden, $cascade);
                }
            }
        }

                if( !$hidden ) {
            $category_array = grade_category::fetch_all(array('id'=>$this->categoryid));
            if ($category_array && array_key_exists($this->categoryid, $category_array)) {
                $category = $category_array[$this->categoryid];
                                                    $category->set_hidden($hidden, false);
                            }
        }
    }

    
    public function has_hidden_grades($groupsql="", array $params=null, $groupwheresql="") {
        global $DB;
        $params = (array)$params;
        $params['itemid'] = $this->id;

        return $DB->get_field_sql("SELECT COUNT(*) FROM {grade_grades} g LEFT JOIN "
                            ."{user} u ON g.userid = u.id $groupsql WHERE itemid = :itemid AND hidden = 1 $groupwheresql", $params);
    }

    
    public function regrading_finished() {
        global $DB;
        $this->needsupdate = 0;
                $DB->set_field('grade_items', 'needsupdate', 0, array('id' => $this->id));
    }

    
    public function regrade_final_grades($userid=null) {
        global $CFG, $DB;

                if ($this->is_locked()) {
            return true;
        }

                if ($this->is_calculated()) {
            if ($this->compute($userid)) {
                return true;
            } else {
                return "Could not calculate grades for grade item";             }

                } else if ($this->is_outcome_item()) {
            return true;

                } else if ($this->is_category_item() or $this->is_course_item()) {
                        $category = $this->load_item_category();
            $category->grade_item =& $this;
            if ($category->generate_grades($userid)) {
                return true;
            } else {
                return "Could not aggregate final grades for category:".$this->id;             }

        } else if ($this->is_manual_item()) {
                        return true;

        } else if (!$this->is_raw_used()) {
                        return true;
        }

                $result = true;
        $grade_inst = new grade_grade();
        $fields = implode(',', $grade_inst->required_fields);
        if ($userid) {
            $params = array($this->id, $userid);
            $rs = $DB->get_recordset_select('grade_grades', "itemid=? AND userid=?", $params, '', $fields);
        } else {
            $rs = $DB->get_recordset('grade_grades', array('itemid' => $this->id), '', $fields);
        }
        if ($rs) {
            foreach ($rs as $grade_record) {
                $grade = new grade_grade($grade_record, false);

                if (!empty($grade_record->locked) or !empty($grade_record->overridden)) {
                                        continue;
                }

                $grade->finalgrade = $this->adjust_raw_grade($grade->rawgrade, $grade->rawgrademin, $grade->rawgrademax);

                if (grade_floats_different($grade_record->finalgrade, $grade->finalgrade)) {
                    $success = $grade->update('system');

                                        if ($success) {
                        $grade->load_grade_item();
                        \core\event\user_graded::create_from_grade($grade)->trigger();
                    } else {
                        $result = "Internal error updating final grade";
                    }
                }
            }
            $rs->close();
        }

        return $result;
    }

    
    public function adjust_raw_grade($rawgrade, $rawmin, $rawmax) {
        if (is_null($rawgrade)) {
            return null;
        }

        if ($this->gradetype == GRADE_TYPE_VALUE) { 
            if ($this->grademax < $this->grademin) {
                return null;
            }

            if ($this->grademax == $this->grademin) {
                return $this->grademax;             }

                                    $manuallyrescale = (component_callback_exists('mod_' . $this->itemmodule, 'rescale_activity_grades') !== false);
            if (!$manuallyrescale && ($rawmin != $this->grademin or $rawmax != $this->grademax)) {
                $rawgrade = grade_grade::standardise_score($rawgrade, $rawmin, $rawmax, $this->grademin, $this->grademax);
            }

                        $rawgrade *= $this->multfactor;
            $rawgrade += $this->plusfactor;

            return $this->bounded_grade($rawgrade);

        } else if ($this->gradetype == GRADE_TYPE_SCALE) {             if (empty($this->scale)) {
                $this->load_scale();
            }

            if ($this->grademax < 0) {
                return null;             }

            if ($this->grademax == 0) {
                return $this->grademax;             }

                                    $manuallyrescale = (component_callback_exists('mod_' . $this->itemmodule, 'rescale_activity_grades') !== false);
            if (!$manuallyrescale && ($rawmin != $this->grademin or $rawmax != $this->grademax)) {
                                $rawgrade = grade_grade::standardise_score($rawgrade, $rawmin, $rawmax, $this->grademin, $this->grademax);
            }

            return $this->bounded_grade($rawgrade);


        } else if ($this->gradetype == GRADE_TYPE_TEXT or $this->gradetype == GRADE_TYPE_NONE) {                         return null;

        } else {
            debugging("Unknown grade type");
            return null;
        }
    }

    
    public function rescale_grades_keep_percentage($oldgrademin, $oldgrademax, $newgrademin, $newgrademax, $source = null) {
        global $DB;

        if (empty($this->id)) {
            return false;
        }

        if ($oldgrademax <= $oldgrademin) {
                        return false;
        }
        $scale = ($newgrademax - $newgrademin) / ($oldgrademax - $oldgrademin);
        if (($newgrademax - $newgrademin) <= 1) {
                        return false;
        }

        $rs = $DB->get_recordset('grade_grades', array('itemid' => $this->id));

        foreach ($rs as $graderecord) {
                        $grade = new grade_grade($graderecord, false);
                        $grade->grade_item = $this;

            if (!$this->is_category_item() || ($this->is_category_item() && $grade->is_overridden())) {
                                if ($this->is_raw_used()) {
                    $rawgrade = (($grade->rawgrade - $oldgrademin) * $scale) + $newgrademin;
                    $this->update_raw_grade(false, $rawgrade, $source, false, FORMAT_MOODLE, null, null, null, $grade);
                } else {
                    $finalgrade = (($grade->finalgrade - $oldgrademin) * $scale) + $newgrademin;
                    $this->update_final_grade($grade->userid, $finalgrade, $source);
                }
            }
        }
        $rs->close();

                $this->force_regrading();

        return true;
    }

    
    public function force_regrading() {
        global $DB;
        $this->needsupdate = 1;
                $wheresql = "(itemtype='course' OR id=?) AND courseid=?";
        $params   = array($this->id, $this->courseid);
        $DB->set_field_select('grade_items', 'needsupdate', 1, $wheresql, $params);
    }

    
    public function load_scale() {
        if ($this->gradetype != GRADE_TYPE_SCALE) {
            $this->scaleid = null;
        }

        if (!empty($this->scaleid)) {
                        if (empty($this->scale->id) or $this->scale->id != $this->scaleid) {
                $this->scale = grade_scale::fetch(array('id'=>$this->scaleid));
                if (!$this->scale) {
                    debugging('Incorrect scale id: '.$this->scaleid);
                    $this->scale = null;
                    return null;
                }
                $this->scale->load_items();
            }

                                    $this->grademax = count($this->scale->scale_items);
            $this->grademin = 1;

        } else {
            $this->scale = null;
        }

        return $this->scale;
    }

    
    public function load_outcome() {
        if (!empty($this->outcomeid)) {
            $this->outcome = grade_outcome::fetch(array('id'=>$this->outcomeid));
        }
        return $this->outcome;
    }

    
    public function get_parent_category() {
        if ($this->is_category_item() or $this->is_course_item()) {
            return $this->get_item_category();

        } else {
            return grade_category::fetch(array('id'=>$this->categoryid));
        }
    }

    
    public function load_parent_category() {
        if (empty($this->parent_category->id)) {
            $this->parent_category = $this->get_parent_category();
        }
        return $this->parent_category;
    }

    
    public function get_item_category() {
        if (!$this->is_course_item() and !$this->is_category_item()) {
            return false;
        }
        return grade_category::fetch(array('id'=>$this->iteminstance));
    }

    
    public function load_item_category() {
        if (empty($this->item_category->id)) {
            $this->item_category = $this->get_item_category();
        }
        return $this->item_category;
    }

    
    public function is_category_item() {
        return ($this->itemtype == 'category');
    }

    
    public function is_course_item() {
        return ($this->itemtype == 'course');
    }

    
    public function is_manual_item() {
        return ($this->itemtype == 'manual');
    }

    
    public function is_outcome_item() {
        return !empty($this->outcomeid);
    }

    
    public function is_external_item() {
        return ($this->itemtype == 'mod');
    }

    
    public function is_overridable_item() {
        if ($this->is_course_item() or $this->is_category_item()) {
            $overridable = (bool) get_config('moodle', 'grade_overridecat');
        } else {
            $overridable = false;
        }

        return !$this->is_outcome_item() and ($this->is_external_item() or $this->is_calculated() or $overridable);
    }

    
    public function is_overridable_item_feedback() {
        return !$this->is_outcome_item() and $this->is_external_item();
    }

    
    public function is_raw_used() {
        return ($this->is_external_item() and !$this->is_calculated() and !$this->is_outcome_item());
    }

    
    public function is_aggregate_item() {
        return ($this->is_category_item() || $this->is_course_item());
    }

    
    public static function fetch_course_item($courseid) {
        if ($course_item = grade_item::fetch(array('courseid'=>$courseid, 'itemtype'=>'course'))) {
            return $course_item;
        }

                $course_category = grade_category::fetch_course_category($courseid);
        return $course_category->get_grade_item();
    }

    
    public function is_editable() {
        return true;
    }

    
    public function is_calculated() {
        if (empty($this->calculation)) {
            return false;
        }

        

                if (!$this->calculation_normalized and strpos($this->calculation, '[[') !== false) {
            $this->set_calculation($this->calculation);
        }

        return !empty($this->calculation);
    }

    
    public function get_calculation() {
        if ($this->is_calculated()) {
            return grade_item::denormalize_formula($this->calculation, $this->courseid);

        } else {
            return NULL;
        }
    }

    
    public function set_calculation($formula) {
        $this->calculation = grade_item::normalize_formula($formula, $this->courseid);
        $this->calculation_normalized = true;
        return $this->update();
    }

    
    public static function denormalize_formula($formula, $courseid) {
        if (empty($formula)) {
            return '';
        }

                if (preg_match_all('/##gi(\d+)##/', $formula, $matches)) {
            foreach ($matches[1] as $id) {
                if ($grade_item = grade_item::fetch(array('id'=>$id, 'courseid'=>$courseid))) {
                    if (!empty($grade_item->idnumber)) {
                        $formula = str_replace('##gi'.$grade_item->id.'##', '[['.$grade_item->idnumber.']]', $formula);
                    }
                }
            }
        }

        return $formula;

    }

    
    public static function normalize_formula($formula, $courseid) {
        $formula = trim($formula);

        if (empty($formula)) {
            return NULL;

        }

                if ($grade_items = grade_item::fetch_all(array('courseid'=>$courseid))) {
            foreach ($grade_items as $grade_item) {
                $formula = str_replace('[['.$grade_item->idnumber.']]', '##gi'.$grade_item->id.'##', $formula);
            }
        }

        return $formula;
    }

    
    public function get_final($userid=NULL) {
        global $DB;
        if ($userid) {
            if ($user = $DB->get_record('grade_grades', array('itemid' => $this->id, 'userid' => $userid))) {
                return $user;
            }

        } else {
            if ($grades = $DB->get_records('grade_grades', array('itemid' => $this->id))) {
                                $result = array();
                foreach ($grades as $grade) {
                    $result[$grade->userid] = $grade;
                }
                return $result;
            } else {
                return array();
            }
        }
    }

    
    public function get_grade($userid, $create=true) {
        if (empty($this->id)) {
            debugging('Can not use before insert');
            return false;
        }

        $grade = new grade_grade(array('userid'=>$userid, 'itemid'=>$this->id));
        if (empty($grade->id) and $create) {
            $grade->insert();
        }

        return $grade;
    }

    
    public function get_sortorder() {
        return $this->sortorder;
    }

    
    public function get_idnumber() {
        return $this->idnumber;
    }

    
    public function get_grade_item() {
        return $this;
    }

    
    public function set_sortorder($sortorder) {
        if ($this->sortorder == $sortorder) {
            return;
        }
        $this->sortorder = $sortorder;
        $this->update();
    }

    
    public function move_after_sortorder($sortorder) {
        global $CFG, $DB;

                $params = array($sortorder, $this->courseid);
        $sql = "UPDATE {grade_items}
                   SET sortorder = sortorder + 1
                 WHERE sortorder > ? AND courseid = ?";
        $DB->execute($sql, $params);

        $this->set_sortorder($sortorder + 1);
    }

    
    public static function fix_duplicate_sortorder($courseid) {
        global $DB;

        $transaction = $DB->start_delegated_transaction();

        $sql = "SELECT DISTINCT g1.id, g1.courseid, g1.sortorder
                    FROM {grade_items} g1
                    JOIN {grade_items} g2 ON g1.courseid = g2.courseid
                WHERE g1.sortorder = g2.sortorder AND g1.id != g2.id AND g1.courseid = :courseid
                ORDER BY g1.sortorder DESC, g1.id DESC";

                        $rs = $DB->get_recordset_sql($sql, array('courseid' => $courseid));

        foreach($rs as $duplicate) {
            $DB->execute("UPDATE {grade_items}
                            SET sortorder = sortorder + 1
                          WHERE courseid = :courseid AND
                          (sortorder > :sortorder OR (sortorder = :sortorder2 AND id > :id))",
                array('courseid' => $duplicate->courseid,
                    'sortorder' => $duplicate->sortorder,
                    'sortorder2' => $duplicate->sortorder,
                    'id' => $duplicate->id));
        }
        $rs->close();
        $transaction->allow_commit();
    }

    
    public function get_name($fulltotal=false) {
        if (strval($this->itemname) !== '') {
                        return format_string($this->itemname);

        } else if ($this->is_course_item()) {
            return get_string('coursetotal', 'grades');

        } else if ($this->is_category_item()) {
            if ($fulltotal) {
                $category = $this->load_parent_category();
                $a = new stdClass();
                $a->category = $category->get_name();
                return get_string('categorytotalfull', 'grades', $a);
            } else {
            return get_string('categorytotal', 'grades');
            }

        } else {
            return get_string('grade');
        }
    }

    
    public function get_description() {
        if ($this->is_course_item() || $this->is_category_item()) {
            $categoryitem = $this->load_item_category();
            return $categoryitem->get_description();
        }
        return '';
    }

    
    public function set_parent($parentid, $updateaggregationfields = true) {
        if ($this->is_course_item() or $this->is_category_item()) {
            print_error('cannotsetparentforcatoritem');
        }

        if ($this->categoryid == $parentid) {
            return true;
        }

                if (!$parent_category = grade_category::fetch(array('id'=>$parentid, 'courseid'=>$this->courseid))) {
            return false;
        }

        $currentparent = $this->load_parent_category();

        if ($updateaggregationfields) {
            $this->set_aggregation_fields_for_aggregation($currentparent->aggregation, $parent_category->aggregation);
        }

        $this->force_regrading();

                $this->categoryid = $parent_category->id;
        $this->parent_category =& $parent_category;

        return $this->update();
    }

    
    public function set_aggregation_fields_for_aggregation($from, $to) {
        $defaults = grade_category::get_default_aggregation_coefficient_values($to);

        $origaggregationcoef = $this->aggregationcoef;
        $origaggregationcoef2 = $this->aggregationcoef2;
        $origweighoverride = $this->weightoverride;

        if ($from == GRADE_AGGREGATE_SUM && $to == GRADE_AGGREGATE_SUM && $this->weightoverride) {
                        
        } else if ($from == GRADE_AGGREGATE_WEIGHTED_MEAN && $to == GRADE_AGGREGATE_WEIGHTED_MEAN) {
            
        } else if (in_array($from, array(GRADE_AGGREGATE_SUM,  GRADE_AGGREGATE_EXTRACREDIT_MEAN, GRADE_AGGREGATE_WEIGHTED_MEAN2))
                && in_array($to, array(GRADE_AGGREGATE_SUM,  GRADE_AGGREGATE_EXTRACREDIT_MEAN, GRADE_AGGREGATE_WEIGHTED_MEAN2))) {

                        $this->aggregationcoef2 = $defaults['aggregationcoef2'];
            $this->weightoverride = $defaults['weightoverride'];

            if ($to != GRADE_AGGREGATE_EXTRACREDIT_MEAN) {
                                $this->aggregationcoef = min(1, $this->aggregationcoef);
            }
        } else {
                        $this->aggregationcoef = $defaults['aggregationcoef'];
            $this->aggregationcoef2 = $defaults['aggregationcoef2'];
            $this->weightoverride = $defaults['weightoverride'];
        }

        $acoefdiff       = grade_floats_different($origaggregationcoef, $this->aggregationcoef);
        $acoefdiff2      = grade_floats_different($origaggregationcoef2, $this->aggregationcoef2);
        $weightoverride  = grade_floats_different($origweighoverride, $this->weightoverride);

        return $acoefdiff || $acoefdiff2 || $weightoverride;
    }

    
    public function bounded_grade($gradevalue) {
        global $CFG;

        if (is_null($gradevalue)) {
            return null;
        }

        if ($this->gradetype == GRADE_TYPE_SCALE) {
                                    return (int)bounded_number($this->grademin, round($gradevalue+0.00001), $this->grademax);
        }

        $grademax = $this->grademax;

                $maxcoef = isset($CFG->gradeoverhundredprocentmax) ? $CFG->gradeoverhundredprocentmax : 10; 
        if (!empty($CFG->unlimitedgrades)) {
                        $grademax = $grademax * $maxcoef;
        } else if ($this->is_category_item() or $this->is_course_item()) {
            $category = $this->load_item_category();
            if ($category->aggregation >= 100) {
                                $grademax = $grademax * $maxcoef;
            }
        }

        return (float)bounded_number($this->grademin, $gradevalue, $grademax);
    }

    
    public function depends_on($reset_cache=false) {
        global $CFG, $DB;

        if ($reset_cache) {
            $this->dependson_cache = null;
        } else if (isset($this->dependson_cache)) {
            return $this->dependson_cache;
        }

        if ($this->is_locked()) {
                        $this->dependson_cache = array();
            return $this->dependson_cache;
        }

        if ($this->is_calculated()) {
            if (preg_match_all('/##gi(\d+)##/', $this->calculation, $matches)) {
                $this->dependson_cache = array_unique($matches[1]);                 return $this->dependson_cache;
            } else {
                $this->dependson_cache = array();
                return $this->dependson_cache;
            }

        } else if ($grade_category = $this->load_item_category()) {
            $params = array();

                        if ($this->gradetype != GRADE_TYPE_VALUE and $this->gradetype != GRADE_TYPE_SCALE) {
                $this->dependson_cache = array();
                return $this->dependson_cache;
            }

            $grade_category->apply_forced_settings();

            if (empty($CFG->enableoutcomes) or $grade_category->aggregateoutcomes) {
                $outcomes_sql = "";
            } else {
                $outcomes_sql = "AND gi.outcomeid IS NULL";
            }

            if (empty($CFG->grade_includescalesinaggregation)) {
                $gtypes = "gi.gradetype = ?";
                $params[] = GRADE_TYPE_VALUE;
            } else {
                $gtypes = "(gi.gradetype = ? OR gi.gradetype = ?)";
                $params[] = GRADE_TYPE_VALUE;
                $params[] = GRADE_TYPE_SCALE;
            }

            $params[] = $grade_category->id;
            $params[] = $this->courseid;
            $params[] = $grade_category->id;
            $params[] = $this->courseid;
            if (empty($CFG->grade_includescalesinaggregation)) {
                $params[] = GRADE_TYPE_VALUE;
            } else {
                $params[] = GRADE_TYPE_VALUE;
                $params[] = GRADE_TYPE_SCALE;
            }
            $sql = "SELECT gi.id
                      FROM {grade_items} gi
                     WHERE $gtypes
                           AND gi.categoryid = ?
                           AND gi.courseid = ?
                           $outcomes_sql
                    UNION

                    SELECT gi.id
                      FROM {grade_items} gi, {grade_categories} gc
                     WHERE (gi.itemtype = 'category' OR gi.itemtype = 'course') AND gi.iteminstance=gc.id
                           AND gc.parent = ?
                           AND gi.courseid = ?
                           AND $gtypes
                           $outcomes_sql";

            if ($children = $DB->get_records_sql($sql, $params)) {
                $this->dependson_cache = array_keys($children);
                return $this->dependson_cache;
            } else {
                $this->dependson_cache = array();
                return $this->dependson_cache;
            }

        } else {
            $this->dependson_cache = array();
            return $this->dependson_cache;
        }
    }

    
    public function refresh_grades($userid=0) {
        global $DB;
        if ($this->itemtype == 'mod') {
            if ($this->is_outcome_item()) {
                                return true;
            }

            if (!$activity = $DB->get_record($this->itemmodule, array('id' => $this->iteminstance))) {
                debugging("Can not find $this->itemmodule activity with id $this->iteminstance");
                return false;
            }

            if (!$cm = get_coursemodule_from_instance($this->itemmodule, $activity->id, $this->courseid)) {
                debugging('Can not find course module');
                return false;
            }

            $activity->modname    = $this->itemmodule;
            $activity->cmidnumber = $cm->idnumber;

            return grade_update_mod_grades($activity, $userid);
        }

        return true;
    }

    
    public function update_final_grade($userid, $finalgrade=false, $source=NULL, $feedback=false, $feedbackformat=FORMAT_MOODLE, $usermodified=null) {
        global $USER, $CFG;

        $result = true;

                if ($this->gradetype == GRADE_TYPE_NONE or $this->is_locked()) {
            return false;
        }

        $grade = new grade_grade(array('itemid'=>$this->id, 'userid'=>$userid));
        $grade->grade_item =& $this; 
        if (empty($usermodified)) {
            $grade->usermodified = $USER->id;
        } else {
            $grade->usermodified = $usermodified;
        }

        if ($grade->is_locked()) {
                        return false;
        }

        $locktime = $grade->get_locktime();
        if ($locktime and $locktime < time()) {
                        $this->force_regrading();
            return false;
        }

        $oldgrade = new stdClass();
        $oldgrade->finalgrade     = $grade->finalgrade;
        $oldgrade->overridden     = $grade->overridden;
        $oldgrade->feedback       = $grade->feedback;
        $oldgrade->feedbackformat = $grade->feedbackformat;
        $oldgrade->rawgrademin    = $grade->rawgrademin;
        $oldgrade->rawgrademax    = $grade->rawgrademax;

                $grade->rawgrademin = $this->grademin;
        $grade->rawgrademax = $this->grademax;
        $grade->rawscaleid  = $this->scaleid;

                if ($finalgrade !== false) {
            if ($this->is_overridable_item()) {
                $grade->overridden = time();
            }

            $grade->finalgrade = $this->bounded_grade($finalgrade);
        }

                if ($feedback !== false) {
            if ($this->is_overridable_item_feedback()) {
                                $grade->overridden = time();
            }

            $grade->feedback       = $feedback;
            $grade->feedbackformat = $feedbackformat;
        }

        $gradechanged = false;
        if (empty($grade->id)) {
            $grade->timecreated  = null;               $grade->timemodified = time();             $result = (bool)$grade->insert($source);

                        if ($result && !is_null($grade->finalgrade)) {
                \core\event\user_graded::create_from_grade($grade)->trigger();
            }
            $gradechanged = true;
        } else {
            
            if (grade_floats_different($grade->finalgrade, $oldgrade->finalgrade)
                    or grade_floats_different($grade->rawgrademin, $oldgrade->rawgrademin)
                    or grade_floats_different($grade->rawgrademax, $oldgrade->rawgrademax)
                    or ($oldgrade->overridden == 0 and $grade->overridden > 0)) {
                $gradechanged = true;
            }

            if ($grade->feedback === $oldgrade->feedback and $grade->feedbackformat == $oldgrade->feedbackformat and
                    $gradechanged === false) {
                                return $result;
            }

            $grade->timemodified = time();             $result = $grade->update($source);

                        if ($result && grade_floats_different($grade->finalgrade, $oldgrade->finalgrade)) {
                \core\event\user_graded::create_from_grade($grade)->trigger();
            }
        }

        if (!$result) {
                        $this->force_regrading();
            return $result;
        }

                if (!$gradechanged) {
            return $result;
        }

        if ($this->is_course_item() and !$this->needsupdate) {
            if (grade_regrade_final_grades($this->courseid, $userid, $this) !== true) {
                $this->force_regrading();
            }

        } else if (!$this->needsupdate) {

            $course_item = grade_item::fetch_course_item($this->courseid);
            if (!$course_item->needsupdate) {
                if (grade_regrade_final_grades($this->courseid, $userid, $this) !== true) {
                    $this->force_regrading();
                }
            } else {
                $this->force_regrading();
            }
        }

        return $result;
    }


    
    public function update_raw_grade($userid, $rawgrade=false, $source=NULL, $feedback=false, $feedbackformat=FORMAT_MOODLE, $usermodified=null, $dategraded=null, $datesubmitted=null, $grade=null) {
        global $USER;

        $result = true;

                if (!$this->is_raw_used() or $this->gradetype == GRADE_TYPE_NONE or $this->is_locked()) {
            return false;
        }

        if (is_null($grade)) {
                        $grade = new grade_grade(array('itemid'=>$this->id, 'userid'=>$userid));
        }
        $grade->grade_item =& $this; 
        if (empty($usermodified)) {
            $grade->usermodified = $USER->id;
        } else {
            $grade->usermodified = $usermodified;
        }

        if ($grade->is_locked()) {
                        return false;
        }

        $locktime = $grade->get_locktime();
        if ($locktime and $locktime < time()) {
                        $this->force_regrading();
            return false;
        }

        $oldgrade = new stdClass();
        $oldgrade->finalgrade     = $grade->finalgrade;
        $oldgrade->rawgrade       = $grade->rawgrade;
        $oldgrade->rawgrademin    = $grade->rawgrademin;
        $oldgrade->rawgrademax    = $grade->rawgrademax;
        $oldgrade->rawscaleid     = $grade->rawscaleid;
        $oldgrade->feedback       = $grade->feedback;
        $oldgrade->feedbackformat = $grade->feedbackformat;

                $grade->rawgrade    = $grade->rawgrade;
        $grade->rawgrademin = $this->grademin;
        $grade->rawgrademax = $this->grademax;
        $grade->rawscaleid  = $this->scaleid;

                if ($rawgrade !== false) {
            $grade->rawgrade = $rawgrade;
        }

                if ($feedback === '') {
            $feedback = null;
        }

                if ($feedback !== false and !$grade->is_overridden()) {
            $grade->feedback       = $feedback;
            $grade->feedbackformat = $feedbackformat;
        }

                if (!$grade->is_locked() and !$grade->is_overridden()) {
            $grade->finalgrade = $this->adjust_raw_grade($grade->rawgrade, $grade->rawgrademin, $grade->rawgrademax);
        }

                $oldgrade->timecreated  = $grade->timecreated;
        $oldgrade->timemodified = $grade->timemodified;

        $grade->timecreated = $datesubmitted;

        if ($grade->is_overridden()) {
            
        } else if (is_null($grade->rawgrade) and is_null($grade->feedback)) {
                        $grade->timemodified = null;

        } else if (!empty($dategraded)) {
                        $grade->timemodified = $dategraded;

        } else if (grade_floats_different($grade->finalgrade, $oldgrade->finalgrade)
                   or $grade->feedback !== $oldgrade->feedback) {
                        $grade->timemodified = time();

        } else {
                    }
        
        $gradechanged = false;
        if (empty($grade->id)) {
            $result = (bool)$grade->insert($source);

                        if ($result && !is_null($grade->finalgrade)) {
                \core\event\user_graded::create_from_grade($grade)->trigger();
            }
            $gradechanged = true;
        } else {
            
            if (grade_floats_different($grade->finalgrade,  $oldgrade->finalgrade)
                    or grade_floats_different($grade->rawgrade,    $oldgrade->rawgrade)
                    or grade_floats_different($grade->rawgrademin, $oldgrade->rawgrademin)
                    or grade_floats_different($grade->rawgrademax, $oldgrade->rawgrademax)
                    or $grade->rawscaleid != $oldgrade->rawscaleid) {
                $gradechanged = true;
            }

                        if ($gradechanged === false and
                    $grade->feedback === $oldgrade->feedback and
                    $grade->feedbackformat == $oldgrade->feedbackformat and
                    $grade->timecreated == $oldgrade->timecreated and
                    $grade->timemodified == $oldgrade->timemodified) {
                                return $result;
            }
            $result = $grade->update($source);

                        if ($result && grade_floats_different($grade->finalgrade, $oldgrade->finalgrade)) {
                \core\event\user_graded::create_from_grade($grade)->trigger();
            }
        }

        if (!$result) {
                        $this->force_regrading();
            return $result;
        }

                if (!$gradechanged) {
            return $result;
        }

        if (!$this->needsupdate) {
            $course_item = grade_item::fetch_course_item($this->courseid);
            if (!$course_item->needsupdate) {
                if (grade_regrade_final_grades($this->courseid, $userid, $this) !== true) {
                    $this->force_regrading();
                }
            }
        }

        return $result;
    }

    
    public function compute($userid=null) {
        global $CFG, $DB;

        if (!$this->is_calculated()) {
            return false;
        }

        require_once($CFG->libdir.'/mathslib.php');

        if ($this->is_locked()) {
            return true;         }

                if ($userid) {
            $missing = array();
            if (!$DB->record_exists('grade_grades', array('itemid'=>$this->id, 'userid'=>$userid))) {
                $m = new stdClass();
                $m->userid = $userid;
                $missing[] = $m;
            }
        } else {
                        $params = array('gicourseid' => $this->courseid, 'ggitemid' => $this->id);
            $sql = "SELECT gg.userid
                      FROM {grade_grades} gg
                           JOIN {grade_items} gi
                           ON (gi.id = gg.itemid AND gi.courseid = :gicourseid)
                     GROUP BY gg.userid
                     HAVING SUM(CASE WHEN gg.itemid = :ggitemid THEN 1 ELSE 0 END) = 0";
            $missing = $DB->get_records_sql($sql, $params);
        }

        if ($missing) {
            foreach ($missing as $m) {
                $grade = new grade_grade(array('itemid'=>$this->id, 'userid'=>$m->userid), false);
                $grade->grade_item =& $this;
                $grade->insert('system');
            }
        }

                $useditems = $this->depends_on();

                $formula = preg_replace('/##(gi\d+)##/', '\1', $this->calculation);
        if (strpos($formula, '[[') !== false) {
                        return false;
        }
        $this->formula = new calc_formula($formula);

                        $gis = array_merge($useditems, array($this->id));
        list($usql, $params) = $DB->get_in_or_equal($gis);

        if ($userid) {
            $usersql = "AND g.userid=?";
            $params[] = $userid;
        } else {
            $usersql = "";
        }

        $grade_inst = new grade_grade();
        $fields = 'g.'.implode(',g.', $grade_inst->required_fields);

        $params[] = $this->courseid;
        $sql = "SELECT $fields
                  FROM {grade_grades} g, {grade_items} gi
                 WHERE gi.id = g.itemid AND gi.id $usql $usersql AND gi.courseid=?
                 ORDER BY g.userid";

        $return = true;

                $rs = $DB->get_recordset_sql($sql, $params);
        if ($rs->valid()) {
            $prevuser = 0;
            $grade_records   = array();
            $oldgrade    = null;
            foreach ($rs as $used) {
                if ($used->userid != $prevuser) {
                    if (!$this->use_formula($prevuser, $grade_records, $useditems, $oldgrade)) {
                        $return = false;
                    }
                    $prevuser = $used->userid;
                    $grade_records   = array();
                    $oldgrade    = null;
                }
                if ($used->itemid == $this->id) {
                    $oldgrade = $used;
                }
                $grade_records['gi'.$used->itemid] = $used->finalgrade;
            }
            if (!$this->use_formula($prevuser, $grade_records, $useditems, $oldgrade)) {
                $return = false;
            }
        }
        $rs->close();

        return $return;
    }

    
    public function use_formula($userid, $params, $useditems, $oldgrade) {
        if (empty($userid)) {
            return true;
        }

                        $allinputsnull = true;
        foreach($useditems as $gi) {
            if (!array_key_exists('gi'.$gi, $params) || is_null($params['gi'.$gi])) {
                $params['gi'.$gi] = 0;
            } else {
                $params['gi'.$gi] = (float)$params['gi'.$gi];
                if ($gi != $this->id) {
                    $allinputsnull = false;
                }
            }
        }

                unset($params['gi'.$this->id]);

                        $gradebookcalculationsfreeze = get_config('core', 'gradebook_calculations_freeze_' . $this->courseid);

        $rawminandmaxchanged = false;
                if ($oldgrade) {
                        if ($gradebookcalculationsfreeze && (int)$gradebookcalculationsfreeze <= 20150627) {
                            } else {
                                                if ($oldgrade->rawgrademax != $this->grademax || $oldgrade->rawgrademin != $this->grademin) {
                    $rawminandmaxchanged = true;
                    $oldgrade->rawgrademax = $this->grademax;
                    $oldgrade->rawgrademin = $this->grademin;
                }
            }
            $oldfinalgrade = $oldgrade->finalgrade;
            $grade = new grade_grade($oldgrade, false);             $grade->grade_item =& $this;

        } else {
            $grade = new grade_grade(array('itemid'=>$this->id, 'userid'=>$userid), false);
            $grade->grade_item =& $this;
            $rawminandmaxchanged = false;
            if ($gradebookcalculationsfreeze && (int)$gradebookcalculationsfreeze <= 20150627) {
                            } else {
                                                $rawminandmaxchanged = true;
                $grade->rawgrademax = $this->grademax;
                $grade->rawgrademin = $this->grademin;
            }
            $grade->insert('system');
            $oldfinalgrade = null;
        }

                if ($grade->is_locked() or $grade->is_overridden()) {
            return true;
        }

        if ($allinputsnull) {
            $grade->finalgrade = null;
            $result = true;

        } else {

                        $this->formula->set_params($params);
            $result = $this->formula->evaluate();

            if ($result === false) {
                $grade->finalgrade = null;

            } else {
                                $grade->finalgrade = $this->bounded_grade($result);
            }
        }

                if ($gradebookcalculationsfreeze && (int)$gradebookcalculationsfreeze <= 20150627) {
                        if (grade_floats_different($grade->finalgrade, $oldfinalgrade)) {
                $grade->timemodified = time();
                $success = $grade->update('compute');

                                if ($success) {
                    \core\event\user_graded::create_from_grade($grade)->trigger();
                }
            }
        } else {
                        if (grade_floats_different($grade->finalgrade, $oldfinalgrade) || $rawminandmaxchanged) {
                $grade->timemodified = time();
                $success = $grade->update('compute');

                                if ($success) {
                    \core\event\user_graded::create_from_grade($grade)->trigger();
                }
            }
        }

        if ($result !== false) {
                    }

        if ($result === false) {
            return false;
        } else {
            return true;
        }

    }

    
    public function validate_formula($formulastr) {
        global $CFG, $DB;
        require_once($CFG->libdir.'/mathslib.php');

        $formulastr = grade_item::normalize_formula($formulastr, $this->courseid);

        if (empty($formulastr)) {
            return true;
        }

        if (strpos($formulastr, '=') !== 0) {
            return get_string('errorcalculationnoequal', 'grades');
        }

                if (preg_match_all('/##gi(\d+)##/', $formulastr, $matches)) {
            $useditems = array_unique($matches[1]);         } else {
            $useditems = array();
        }

                                if (!empty($this->id)) {
            $useditems = array_diff($useditems, array($this->id));
                    }

                $formula = preg_replace('/##(gi\d+)##/', '\1', $formulastr);
        $formula = new calc_formula($formula);


        if (empty($useditems)) {
            $grade_items = array();

        } else {
            list($usql, $params) = $DB->get_in_or_equal($useditems);
            $params[] = $this->courseid;
            $sql = "SELECT gi.*
                      FROM {grade_items} gi
                     WHERE gi.id $usql and gi.courseid=?"; 
            if (!$grade_items = $DB->get_records_sql($sql, $params)) {
                $grade_items = array();
            }
        }

        $params = array();
        foreach ($useditems as $itemid) {
                        if (!array_key_exists($itemid, $grade_items)) {
                return false;
            }
                                    $params['gi'.$grade_items[$itemid]->id] = $grade_items[$itemid]->grademax;
        }

                $formula->set_params($params);
        $result = $formula->evaluate();

                if ($result === false) {
                        return get_string('errorcalculationunknown', 'grades');
        } else {
            return true;
        }
    }

    
    public function get_displaytype() {
        global $CFG;

        if ($this->display == GRADE_DISPLAY_TYPE_DEFAULT) {
            return grade_get_setting($this->courseid, 'displaytype', $CFG->grade_displaytype);

        } else {
            return $this->display;
        }
    }

    
    public function get_decimals() {
        global $CFG;

        if (is_null($this->decimals)) {
            return grade_get_setting($this->courseid, 'decimalpoints', $CFG->grade_decimalpoints);

        } else {
            return $this->decimals;
        }
    }

    
    function get_formatted_range($rangesdisplaytype=null, $rangesdecimalpoints=null) {

        global $USER;

                if (isset($USER->gradeediting) && array_key_exists($this->courseid, $USER->gradeediting) && $USER->gradeediting[$this->courseid]) {
            $displaytype = GRADE_DISPLAY_TYPE_REAL;

        } else if ($rangesdisplaytype == GRADE_REPORT_PREFERENCE_INHERIT) {             $displaytype = $this->get_displaytype();

        } else {
            $displaytype = $rangesdisplaytype;
        }

                if ($rangesdecimalpoints == GRADE_REPORT_PREFERENCE_INHERIT) {
            $decimalpoints = $this->get_decimals();

        } else {
            $decimalpoints = $rangesdecimalpoints;
        }

        if ($displaytype == GRADE_DISPLAY_TYPE_PERCENTAGE) {
            $grademin = "0 %";
            $grademax = "100 %";

        } else {
            $grademin = grade_format_gradevalue($this->grademin, $this, true, $displaytype, $decimalpoints);
            $grademax = grade_format_gradevalue($this->grademax, $this, true, $displaytype, $decimalpoints);
        }

        return $grademin.'&ndash;'. $grademax;
    }

    
    public function get_coefstring() {
        $parent_category = $this->load_parent_category();
        if ($this->is_category_item()) {
            $parent_category = $parent_category->load_parent_category();
        }

        if ($parent_category->is_aggregationcoef_used()) {
            return $parent_category->get_coefstring();
        } else {
            return false;
        }
    }

    
    public function can_control_visibility() {
        if (core_component::get_plugin_directory($this->itemtype, $this->itemmodule)) {
            return !plugin_supports($this->itemtype, $this->itemmodule, FEATURE_CONTROLS_GRADE_VISIBILITY, false);
        }
        return parent::can_control_visibility();
    }

    
    protected function notify_changed($deleted) {
        global $CFG;

                                if (!empty($CFG->enableavailability) && class_exists('\availability_grade\callbacks')) {
            \availability_grade\callbacks::grade_item_changed($this->courseid);
        }
    }
}
