<?php



defined('MOODLE_INTERNAL') || die();

require_once('grade_object.php');


class grade_grade extends grade_object {

    
    public $table = 'grade_grades';

    
    public $required_fields = array('id', 'itemid', 'userid', 'rawgrade', 'rawgrademax', 'rawgrademin',
                                 'rawscaleid', 'usermodified', 'finalgrade', 'hidden', 'locked',
                                 'locktime', 'exported', 'overridden', 'excluded', 'timecreated',
                                 'timemodified', 'aggregationstatus', 'aggregationweight');

    
    public $optional_fields = array('feedback'=>null, 'feedbackformat'=>0, 'information'=>null, 'informationformat'=>0);

    
    public $itemid;

    
    public $grade_item;

    
    public $userid;

    
    public $rawgrade;

    
    public $rawgrademax = 100;

    
    public $rawgrademin = 0;

    
    public $rawscaleid;

    
    public $usermodified;

    
    public $finalgrade;

    
    public $hidden = 0;

    
    public $locked = 0;

    
    public $locktime = 0;

    
    public $exported = 0;

    
    public $overridden = 0;

    
    public $excluded = 0;

    
    public $timecreated = null;

    
    public $timemodified = null;

    
    public $aggregationstatus = 'unknown';

    
    public $aggregationweight = null;

    
    public static function fetch_users_grades($grade_item, $userids, $include_missing=true) {
        global $DB;

                        $limit = 2000;
        $count = count($userids);
        if ($count > $limit) {
            $half = (int)($count/2);
            $first  = array_slice($userids, 0, $half);
            $second = array_slice($userids, $half);
            return grade_grade::fetch_users_grades($grade_item, $first, $include_missing) + grade_grade::fetch_users_grades($grade_item, $second, $include_missing);
        }

        list($user_ids_cvs, $params) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'uid0');
        $params['giid'] = $grade_item->id;
        $result = array();
        if ($grade_records = $DB->get_records_select('grade_grades', "itemid=:giid AND userid $user_ids_cvs", $params)) {
            foreach ($grade_records as $record) {
                $result[$record->userid] = new grade_grade($record, false);
            }
        }
        if ($include_missing) {
            foreach ($userids as $userid) {
                if (!array_key_exists($userid, $result)) {
                    $grade_grade = new grade_grade();
                    $grade_grade->userid = $userid;
                    $grade_grade->itemid = $grade_item->id;
                    $result[$userid] = $grade_grade;
                }
            }
        }

        return $result;
    }

    
    public function load_grade_item() {
        if (empty($this->itemid)) {
            debugging('Missing itemid');
            $this->grade_item = null;
            return null;
        }

        if (empty($this->grade_item)) {
            $this->grade_item = grade_item::fetch(array('id'=>$this->itemid));

        } else if ($this->grade_item->id != $this->itemid) {
            debugging('Itemid mismatch');
            $this->grade_item = grade_item::fetch(array('id'=>$this->itemid));
        }

        return $this->grade_item;
    }

    
    public function is_editable() {
        if ($this->is_locked()) {
            return false;
        }

        $grade_item = $this->load_grade_item();

        if ($grade_item->gradetype == GRADE_TYPE_NONE) {
            return false;
        }

        if ($grade_item->is_course_item() or $grade_item->is_category_item()) {
            return (bool)get_config('moodle', 'grade_overridecat');
        }

        return true;
    }

    
    public function is_locked() {
        $this->load_grade_item();
        if (empty($this->grade_item)) {
            return !empty($this->locked);
        } else {
            return !empty($this->locked) or $this->grade_item->is_locked();
        }
    }

    
    public function is_overridden() {
        return !empty($this->overridden);
    }

    
    public function get_datesubmitted() {
                return $this->timecreated;
    }

    
    public function get_aggregationweight() {
        return $this->aggregationweight;
    }

    
    public function set_aggregationweight($aggregationweight) {
        $this->aggregationweight = $aggregationweight;
        $this->update();
    }

    
    public function get_aggregationstatus() {
        return $this->aggregationstatus;
    }

    
    public function set_aggregationstatus($aggregationstatus) {
        $this->aggregationstatus = $aggregationstatus;
        $this->update();
    }

    
    protected function get_grade_min_and_max() {
        global $CFG;
        $this->load_grade_item();

                $minmaxtouse = grade_get_setting($this->grade_item->courseid, 'minmaxtouse', $CFG->grade_minmaxtouse);

                        $gradebookcalculationsfreeze = 'gradebook_calculations_freeze_' . $this->grade_item->courseid;
                if (isset($CFG->$gradebookcalculationsfreeze) && (int)$CFG->$gradebookcalculationsfreeze <= 20150627) {
                        if ($minmaxtouse == GRADE_MIN_MAX_FROM_GRADE_GRADE || $this->grade_item->is_aggregate_item()) {
                return array($this->rawgrademin, $this->rawgrademax);
            } else {
                return array($this->grade_item->grademin, $this->grade_item->grademax);
            }
        } else {
                        if (($this->grade_item->is_aggregate_item() && !$this->grade_item->is_calculated())
                    || $minmaxtouse == GRADE_MIN_MAX_FROM_GRADE_GRADE) {
                return array($this->rawgrademin, $this->rawgrademax);
            } else {
                return array($this->grade_item->grademin, $this->grade_item->grademax);
            }
        }
    }

    
    public function get_grade_min() {
        list($min, $max) = $this->get_grade_min_and_max();

        return $min;
    }

    
    public function get_grade_max() {
        list($min, $max) = $this->get_grade_min_and_max();

        return $max;
    }

    
    public function get_dategraded() {
                if (is_null($this->finalgrade) and is_null($this->feedback)) {
            return null;         } else if ($this->overridden) {
            return $this->overridden;
        } else {
            return $this->timemodified;
        }
    }

    
    public function set_overridden($state, $refresh = true) {
        if (empty($this->overridden) and $state) {
            $this->overridden = time();
            $this->update();
            return true;

        } else if (!empty($this->overridden) and !$state) {
            $this->overridden = 0;
            $this->update();

            if ($refresh) {
                                $this->grade_item->refresh_grades($this->userid);
            }

            return true;
        }
        return false;
    }

    
    public function is_excluded() {
        return !empty($this->excluded);
    }

    
    public function set_excluded($state) {
        if (empty($this->excluded) and $state) {
            $this->excluded = time();
            $this->update();
            return true;

        } else if (!empty($this->excluded) and !$state) {
            $this->excluded = 0;
            $this->update();
            return true;
        }
        return false;
    }

    
    public function set_locked($lockedstate, $cascade=false, $refresh=true) {
        $this->load_grade_item();

        if ($lockedstate) {
            if ($this->grade_item->needsupdate) {
                                return false;
            }

            $this->locked = time();
            $this->update();

            return true;

        } else {
            if (!empty($this->locked) and $this->locktime < time()) {
                                $this->locktime = 0;
            }

                        $this->locked = 0;
            $this->update();

            if ($refresh and !$this->is_overridden()) {
                                $this->grade_item->refresh_grades($this->userid);
            }

            return true;
        }
    }

    
    public static function check_locktime_all($items) {
        global $CFG, $DB;

        $now = time();         list($usql, $params) = $DB->get_in_or_equal($items);
        $params[] = $now;
        $rs = $DB->get_recordset_select('grade_grades', "itemid $usql AND locked = 0 AND locktime > 0 AND locktime < ?", $params);
        foreach ($rs as $grade) {
            $grade_grade = new grade_grade($grade, false);
            $grade_grade->locked = time();
            $grade_grade->update('locktime');
        }
        $rs->close();
    }

    
    public function set_locktime($locktime) {
        $this->locktime = $locktime;
        $this->update();
    }

    
    public function get_locktime() {
        $this->load_grade_item();

        $item_locktime = $this->grade_item->get_locktime();

        if (empty($this->locktime) or ($item_locktime and $item_locktime < $this->locktime)) {
            return $item_locktime;

        } else {
            return $this->locktime;
        }
    }

    
    public function is_hidden() {
        $this->load_grade_item();
        if (empty($this->grade_item)) {
            return $this->hidden == 1 or ($this->hidden != 0 and $this->hidden > time());
        } else {
            return $this->hidden == 1 or ($this->hidden != 0 and $this->hidden > time()) or $this->grade_item->is_hidden();
        }
    }

    
    public function is_hiddenuntil() {
        $this->load_grade_item();

        if ($this->hidden == 1 or $this->grade_item->hidden == 1) {
            return false;         }

        if ($this->hidden > 1 or $this->grade_item->hidden > 1) {
            return true;
        }

        return false;
    }

    
    public function get_hidden() {
        $this->load_grade_item();

        $item_hidden = $this->grade_item->get_hidden();

        if ($item_hidden == 1) {
            return 1;

        } else if ($item_hidden == 0) {
            return $this->hidden;

        } else {
            if ($this->hidden == 0) {
                return $item_hidden;
            } else if ($this->hidden == 1) {
                return 1;
            } else if ($this->hidden > $item_hidden) {
                return $this->hidden;
            } else {
                return $item_hidden;
            }
        }
    }

    
    public function set_hidden($hidden, $cascade=false) {
       $this->hidden = $hidden;
       $this->update();
    }

    
    public static function fetch($params) {
        return grade_object::fetch_helper('grade_grades', 'grade_grade', $params);
    }

    
    public static function fetch_all($params) {
        return grade_object::fetch_all_helper('grade_grades', 'grade_grade', $params);
    }

    
    public static function standardise_score($rawgrade, $source_min, $source_max, $target_min, $target_max) {
        if (is_null($rawgrade)) {
          return null;
        }

        if ($source_max == $source_min or $target_min == $target_max) {
                        return $target_max;
        }

        $factor = ($rawgrade - $source_min) / ($source_max - $source_min);
        $diff = $target_max - $target_min;
        $standardised_value = $factor * $diff + $target_min;
        return $standardised_value;
    }

    
    protected static function flatten_dependencies_array(&$dependson, &$dependencydepth) {
                $somethingchanged = true;
        while ($somethingchanged) {
            $somethingchanged = false;

            foreach ($dependson as $itemid => $depends) {
                                $before = $dependson[$itemid];
                foreach ($depends as $subitemid => $subdepends) {
                    $dependson[$itemid] = array_unique(array_merge($depends, $dependson[$subdepends]));
                    sort($dependson[$itemid], SORT_NUMERIC);
                }
                if ($before != $dependson[$itemid]) {
                    $somethingchanged = true;
                    if (!isset($dependencydepth[$itemid])) {
                        $dependencydepth[$itemid] = 1;
                    } else {
                        $dependencydepth[$itemid]++;
                    }
                }
            }
        }
    }

    
    public static function get_hiding_affected(&$grade_grades, &$grade_items) {
        global $CFG;

        if (count($grade_grades) !== count($grade_items)) {
            print_error('invalidarraysize', 'debug', '', 'grade_grade::get_hiding_affected()!');
        }

        $dependson = array();
        $todo = array();
        $unknown = array();          $altered = array();          $alteredgrademax = array();          $alteredgrademin = array();          $alteredaggregationstatus = array();          $alteredaggregationweight = array();          $dependencydepth = array();

        $hiddenfound = false;
        foreach($grade_grades as $itemid=>$unused) {
            $grade_grade =& $grade_grades[$itemid];
                        $dependson[$grade_grade->itemid] = $grade_items[$grade_grade->itemid]->depends_on();
            if ($grade_grade->is_excluded()) {
                            } else if ($grade_grade->is_hidden()) {
                $hiddenfound = true;
                $altered[$grade_grade->itemid] = null;
                $alteredaggregationstatus[$grade_grade->itemid] = 'dropped';
                $alteredaggregationweight[$grade_grade->itemid] = 0;
            } else if ($grade_grade->is_locked() or $grade_grade->is_overridden()) {
                            } else {
                if (!empty($dependson[$grade_grade->itemid])) {
                    $dependencydepth[$grade_grade->itemid] = 1;
                    $todo[] = $grade_grade->itemid;
                }
            }
        }

                self::flatten_dependencies_array($dependson, $dependencydepth);

        if (!$hiddenfound) {
            return array('unknown' => array(),
                         'altered' => array(),
                         'alteredgrademax' => array(),
                         'alteredgrademin' => array(),
                         'alteredaggregationstatus' => array(),
                         'alteredaggregationweight' => array());
        }
                $dependencydepth = array_intersect_key($dependencydepth, array_flip($todo));
                array_multisort($dependencydepth, $todo);

        $max = count($todo);
        $hidden_precursors = null;
        for($i=0; $i<$max; $i++) {
            $found = false;
            foreach($todo as $key=>$do) {
                $hidden_precursors = array_intersect($dependson[$do], $unknown);
                if ($hidden_precursors) {
                                        $unknown[$do] = $do;
                    unset($todo[$key]);
                    $found = true;
                    continue;

                } else if (!array_intersect($dependson[$do], $todo)) {
                    $hidden_precursors = array_intersect($dependson[$do], array_keys($altered));
                                                                                                                                                                $issumaggregate = false;
                    if ($grade_items[$do]->itemtype == 'category') {
                        $issumaggregate = $grade_items[$do]->load_item_category()->aggregation == GRADE_AGGREGATE_SUM;
                    }
                    if (!$hidden_precursors && !$issumaggregate) {
                        unset($todo[$key]);
                        $found = true;
                        continue;

                    } else {
                                                if ($grade_items[$do]->is_calculated() or
                            (!$grade_items[$do]->is_category_item() and !$grade_items[$do]->is_course_item())
                        ) {
                                                                                    $unknown[$do] = $do;
                            unset($todo[$key]);
                            $found = true;
                            continue;

                        } else {
                                                        $grade_category = $grade_items[$do]->load_item_category();

                                                        $values = array();
                            $immediatedepends = $grade_items[$do]->depends_on();
                            foreach ($immediatedepends as $itemid) {
                                if (array_key_exists($itemid, $altered)) {
                                                                        $values[$itemid] = $altered[$itemid];
                                    if (is_null($values[$itemid])) {
                                                                                unset($values[$itemid]);
                                    }
                                } elseif (empty($values[$itemid])) {
                                    $values[$itemid] = $grade_grades[$itemid]->finalgrade;
                                }
                            }

                            foreach ($values as $itemid=>$value) {
                                if ($grade_grades[$itemid]->is_excluded()) {
                                    unset($values[$itemid]);
                                    $alteredaggregationstatus[$itemid] = 'excluded';
                                    $alteredaggregationweight[$itemid] = null;
                                    continue;
                                }
                                                                $grademin = $grade_items[$itemid]->grademin;
                                if (isset($alteredgrademin[$itemid])) {
                                    $grademin = $alteredgrademin[$itemid];
                                }
                                $grademax = $grade_items[$itemid]->grademax;
                                if (isset($alteredgrademax[$itemid])) {
                                    $grademax = $alteredgrademax[$itemid];
                                }
                                $values[$itemid] = grade_grade::standardise_score($value, $grademin, $grademax, 0, 1);
                            }

                            if ($grade_category->aggregateonlygraded) {
                                foreach ($values as $itemid=>$value) {
                                    if (is_null($value)) {
                                        unset($values[$itemid]);
                                        $alteredaggregationstatus[$itemid] = 'novalue';
                                        $alteredaggregationweight[$itemid] = null;
                                    }
                                }
                            } else {
                                foreach ($values as $itemid=>$value) {
                                    if (is_null($value)) {
                                        $values[$itemid] = 0;
                                    }
                                }
                            }

                                                        $allvalues = $values;
                            $grade_category->apply_limit_rules($values, $grade_items);

                            $moredropped = array_diff($allvalues, $values);
                            foreach ($moredropped as $drop => $unused) {
                                $alteredaggregationstatus[$drop] = 'dropped';
                                $alteredaggregationweight[$drop] = null;
                            }

                            foreach ($values as $itemid => $val) {
                                if ($grade_category->is_extracredit_used() && ($grade_items[$itemid]->aggregationcoef > 0)) {
                                    $alteredaggregationstatus[$itemid] = 'extra';
                                }
                            }

                            asort($values, SORT_NUMERIC);

                                                        if (count($values) == 0) {
                                                                $altered[$do] = null;
                                unset($todo[$key]);
                                $found = true;
                                continue;
                            }

                            $usedweights = array();
                            $adjustedgrade = $grade_category->aggregate_values_and_adjust_bounds($values, $grade_items, $usedweights);

                                                        $finalgrade = grade_grade::standardise_score($adjustedgrade['grade'],
                                                                         0,
                                                                         1,
                                                                         $adjustedgrade['grademin'],
                                                                         $adjustedgrade['grademax']);

                            foreach ($usedweights as $itemid => $weight) {
                                if (!isset($alteredaggregationstatus[$itemid])) {
                                    $alteredaggregationstatus[$itemid] = 'used';
                                }
                                $alteredaggregationweight[$itemid] = $weight;
                            }

                            $finalgrade = $grade_items[$do]->bounded_grade($finalgrade);
                            $alteredgrademin[$do] = $adjustedgrade['grademin'];
                            $alteredgrademax[$do] = $adjustedgrade['grademax'];
                                                                                    $grade_items[$do]->grademin = $adjustedgrade['grademin'];
                            $grade_items[$do]->grademax = $adjustedgrade['grademax'];

                            $altered[$do] = $finalgrade;
                            unset($todo[$key]);
                            $found = true;
                            continue;
                        }
                    }
                }
            }
            if (!$found) {
                break;
            }
        }

        return array('unknown' => $unknown,
                     'altered' => $altered,
                     'alteredgrademax' => $alteredgrademax,
                     'alteredgrademin' => $alteredgrademin,
                     'alteredaggregationstatus' => $alteredaggregationstatus,
                     'alteredaggregationweight' => $alteredaggregationweight);
    }

    
    public function is_passed($grade_item = null) {
        if (empty($grade_item)) {
            if (!isset($this->grade_item)) {
                $this->load_grade_item();
            }
        } else {
            $this->grade_item = $grade_item;
            $this->itemid = $grade_item->id;
        }

                if (is_null($this->finalgrade)) {
            return null;
        }

                if (is_null($this->grade_item->gradepass)) {
            return null;
        } else if ($this->grade_item->gradepass == $this->grade_item->grademin) {
            return null;
        } else if ($this->grade_item->gradetype == GRADE_TYPE_SCALE && !grade_floats_different($this->grade_item->gradepass, 0.0)) {
            return null;
        }

        return $this->finalgrade >= $this->grade_item->gradepass;
    }

    
    public function insert($source=null) {
                        return parent::insert($source);
    }

    
    public function update($source=null) {
        $this->rawgrade    = grade_floatval($this->rawgrade);
        $this->finalgrade  = grade_floatval($this->finalgrade);
        $this->rawgrademin = grade_floatval($this->rawgrademin);
        $this->rawgrademax = grade_floatval($this->rawgrademax);
        return parent::update($source);
    }

    
    public function delete($source = null) {
        $success = parent::delete($source);

                if ($success) {
            $this->load_grade_item();
            \core\event\grade_deleted::create_from_grade($this)->trigger();
        }

        return $success;
    }

    
    protected function notify_changed($deleted) {
        global $CFG;

                                if (!empty($CFG->enableavailability) && class_exists('\availability_grade\callbacks')) {
            \availability_grade\callbacks::grade_changed($this->userid);
        }

        require_once($CFG->libdir.'/completionlib.php');

                        if (!completion_info::is_enabled_for_site()) {
            return;
        }

                                if (class_exists('restore_controller', false) && restore_controller::is_executing()) {
            return;
        }

                $this->load_grade_item();

                if ($this->grade_item->itemtype!='mod') {
            return;
        }

                $course = get_course($this->grade_item->courseid, false);

                $completion = new completion_info($course);
        if (!$completion->is_enabled()) {
            return;
        }

                $cm = get_coursemodule_from_instance($this->grade_item->itemmodule,
              $this->grade_item->iteminstance, $this->grade_item->courseid);
                if (!$cm) {
                                    if (!$deleted) {
                debugging("Couldn't find course-module for module '" .
                        $this->grade_item->itemmodule . "', instance '" .
                        $this->grade_item->iteminstance . "', course '" .
                        $this->grade_item->courseid . "'");
            }
            return;
        }

                $completion->inform_grade_changed($cm, $this->grade_item, $this, $deleted);
    }

    
    function get_aggregation_hint() {
        return array('status' => $this->get_aggregationstatus(),
                     'weight' => $this->get_aggregationweight());
    }
}
