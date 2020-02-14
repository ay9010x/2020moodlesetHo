<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/grade_object.php');


class grade_category extends grade_object {
    
    public $table = 'grade_categories';

    
    public $required_fields = array('id', 'courseid', 'parent', 'depth', 'path', 'fullname', 'aggregation',
                                 'keephigh', 'droplow', 'aggregateonlygraded', 'aggregateoutcomes',
                                 'timecreated', 'timemodified', 'hidden');

    
    public $courseid;

    
    public $parent;

    
    public $parent_category;

    
    public $depth = 0;

    
    public $path;

    
    public $fullname;

    
    public $aggregation = GRADE_AGGREGATE_SUM;

    
    public $keephigh = 0;

    
    public $droplow = 0;

    
    public $aggregateonlygraded = 0;

    
    public $aggregateoutcomes = 0;

    
    public $children;

    
    public $all_children;

    
    public $grade_item;

    
    public $sortorder;

    
    public $forceable = array('aggregation', 'keephigh', 'droplow', 'aggregateonlygraded', 'aggregateoutcomes');

    
    public $coefstring = null;

    
    protected $canapplylimitrules;

    
    public static function build_path($grade_category) {
        global $DB;

        if (empty($grade_category->parent)) {
            return '/'.$grade_category->id.'/';

        } else {
            $parent = $DB->get_record('grade_categories', array('id' => $grade_category->parent));
            return grade_category::build_path($parent).$grade_category->id.'/';
        }
    }

    
    public static function fetch($params) {
        if ($records = self::retrieve_record_set($params)) {
            return reset($records);
        }

        $record = grade_object::fetch_helper('grade_categories', 'grade_category', $params);

                        $records = false;
        if ($record) {
            $records = array($record->id => $record);
        }

        self::set_record_set($params, $records);

        return $record;
    }

    
    public static function fetch_all($params) {
        if ($records = self::retrieve_record_set($params)) {
            return $records;
        }

        $records = grade_object::fetch_all_helper('grade_categories', 'grade_category', $params);
        self::set_record_set($params, $records);

        return $records;
    }

    
    public function update($source=null) {
                $this->load_grade_item();

                if (empty($this->path)) {
            $this->path  = grade_category::build_path($this);
            $this->depth = substr_count($this->path, '/') - 1;
            $updatechildren = true;

        } else {
            $updatechildren = false;
        }

        $this->apply_forced_settings();

                if ($this->droplow > 0) {
            $this->keephigh = 0;

        } else if ($this->keephigh > 0) {
            $this->droplow = 0;
        }

                if ($this->qualifies_for_regrading()) {
            $this->force_regrading();
        }

        $this->timemodified = time();

        $result = parent::update($source);

                if ($result and $updatechildren) {

            if ($children = grade_category::fetch_all(array('parent'=>$this->id))) {

                foreach ($children as $child) {
                    $child->path  = null;
                    $child->depth = 0;
                    $child->update($source);
                }
            }
        }

        return $result;
    }

    
    public function delete($source=null) {
        $grade_item = $this->load_grade_item();

        if ($this->is_course_category()) {

            if ($categories = grade_category::fetch_all(array('courseid'=>$this->courseid))) {

                foreach ($categories as $category) {

                    if ($category->id == $this->id) {
                        continue;                     }
                    $category->delete($source);
                }
            }

            if ($items = grade_item::fetch_all(array('courseid'=>$this->courseid))) {

                foreach ($items as $item) {

                    if ($item->id == $grade_item->id) {
                        continue;                     }
                    $item->delete($source);
                }
            }

        } else {
            $this->force_regrading();

            $parent = $this->load_parent_category();

                        if ($children = grade_item::fetch_all(array('categoryid'=>$this->id))) {
                foreach ($children as $child) {
                    $child->set_parent($parent->id);
                }
            }

            if ($children = grade_category::fetch_all(array('parent'=>$this->id))) {
                foreach ($children as $child) {
                    $child->set_parent($parent->id);
                }
            }
        }

                $grade_item->delete($source);

                return parent::delete($source);
    }

    
    public function insert($source=null) {

        if (empty($this->courseid)) {
            print_error('cannotinsertgrade');
        }

        if (empty($this->parent)) {
            $course_category = grade_category::fetch_course_category($this->courseid);
            $this->parent = $course_category->id;
        }

        $this->path = null;

        $this->timecreated = $this->timemodified = time();

        if (!parent::insert($source)) {
            debugging("Could not insert this category: " . print_r($this, true));
            return false;
        }

        $this->force_regrading();

                $this->update($source);

        return $this->id;
    }

    
    public function insert_course_category($courseid) {
        $this->courseid    = $courseid;
        $this->fullname    = '?';
        $this->path        = null;
        $this->parent      = null;
        $this->aggregation = GRADE_AGGREGATE_WEIGHTED_MEAN2;

        $this->apply_default_settings();
        $this->apply_forced_settings();

        $this->timecreated = $this->timemodified = time();

        if (!parent::insert('system')) {
            debugging("Could not insert this category: " . print_r($this, true));
            return false;
        }

                $this->update('system');

        return $this->id;
    }

    
    public function qualifies_for_regrading() {
        if (empty($this->id)) {
            debugging("Can not regrade non existing category");
            return false;
        }

        $db_item = grade_category::fetch(array('id'=>$this->id));

        $aggregationdiff = $db_item->aggregation         != $this->aggregation;
        $keephighdiff    = $db_item->keephigh            != $this->keephigh;
        $droplowdiff     = $db_item->droplow             != $this->droplow;
        $aggonlygrddiff  = $db_item->aggregateonlygraded != $this->aggregateonlygraded;
        $aggoutcomesdiff = $db_item->aggregateoutcomes   != $this->aggregateoutcomes;

        return ($aggregationdiff || $keephighdiff || $droplowdiff || $aggonlygrddiff || $aggoutcomesdiff);
    }

    
    public function force_regrading() {
        $grade_item = $this->load_grade_item();
        $grade_item->force_regrading();
    }

    
    public function pre_regrade_final_grades() {
        $this->auto_update_weights();
        $this->auto_update_max();
    }

    
    public function generate_grades($userid=null) {
        global $CFG, $DB;

        $this->load_grade_item();

        if ($this->grade_item->is_locked()) {
            return true;         }

                $depends_on = $this->grade_item->depends_on();

        if (empty($depends_on)) {
            $items = false;

        } else {
            list($usql, $params) = $DB->get_in_or_equal($depends_on);
            $sql = "SELECT *
                      FROM {grade_items}
                     WHERE id $usql";
            $items = $DB->get_records_sql($sql, $params);
            foreach ($items as $id => $item) {
                $items[$id] = new grade_item($item, false);
            }
        }

        $grade_inst = new grade_grade();
        $fields = 'g.'.implode(',g.', $grade_inst->required_fields);

                $gis = array_merge($depends_on, array($this->grade_item->id));
        list($usql, $params) = $DB->get_in_or_equal($gis);

        if ($userid) {
            $usersql = "AND g.userid=?";
            $params[] = $userid;

        } else {
            $usersql = "";
        }

        $sql = "SELECT $fields
                  FROM {grade_grades} g, {grade_items} gi
                 WHERE gi.id = g.itemid AND gi.id $usql $usersql
              ORDER BY g.userid";

                $rs = $DB->get_recordset_sql($sql, $params);
        if ($rs->valid()) {
            $prevuser = 0;
            $grade_values = array();
            $excluded     = array();
            $oldgrade     = null;
            $grademaxoverrides = array();
            $grademinoverrides = array();

            foreach ($rs as $used) {
                $grade = new grade_grade($used, false);
                if (isset($items[$grade->itemid])) {
                                        $grade->grade_item =& $items[$grade->itemid];
                } else if ($grade->itemid == $this->grade_item->id) {
                                        $grade->grade_item =& $this->grade_item;
                }
                if ($grade->userid != $prevuser) {
                    $this->aggregate_grades($prevuser,
                                            $items,
                                            $grade_values,
                                            $oldgrade,
                                            $excluded,
                                            $grademinoverrides,
                                            $grademaxoverrides);
                    $prevuser = $grade->userid;
                    $grade_values = array();
                    $excluded     = array();
                    $oldgrade     = null;
                    $grademaxoverrides = array();
                    $grademinoverrides = array();
                }
                $grade_values[$grade->itemid] = $grade->finalgrade;
                $grademaxoverrides[$grade->itemid] = $grade->get_grade_max();
                $grademinoverrides[$grade->itemid] = $grade->get_grade_min();

                if ($grade->excluded) {
                    $excluded[] = $grade->itemid;
                }

                if ($this->grade_item->id == $grade->itemid) {
                    $oldgrade = $grade;
                }
            }
            $this->aggregate_grades($prevuser,
                                    $items,
                                    $grade_values,
                                    $oldgrade,
                                    $excluded,
                                    $grademinoverrides,
                                    $grademaxoverrides);        }
        $rs->close();

        return true;
    }

    
    private function aggregate_grades($userid,
                                      $items,
                                      $grade_values,
                                      $oldgrade,
                                      $excluded,
                                      $grademinoverrides,
                                      $grademaxoverrides) {
        global $CFG, $DB;

                $novalue = array();
        $dropped = array();
        $extracredit = array();
        $usedweights = array();

        if (empty($userid)) {
                        return;
        }

        if ($oldgrade) {
            $oldfinalgrade = $oldgrade->finalgrade;
            $grade = new grade_grade($oldgrade, false);
            $grade->grade_item =& $this->grade_item;

        } else {
                        $grade = new grade_grade(array('itemid'=>$this->grade_item->id, 'userid'=>$userid), false);
            $grade->grade_item =& $this->grade_item;
            $grade->insert('system');
            $oldfinalgrade = null;
        }

                if ($grade->is_locked() or $grade->is_overridden()) {
            return;
        }

                unset($grade_values[$this->grade_item->id]);

                                if (!empty($items)) {
            list($ggsql, $params) = $DB->get_in_or_equal(array_keys($items), SQL_PARAMS_NAMED, 'g');


            $params['userid'] = $userid;
            $sql = "SELECT itemid
                      FROM {grade_grades}
                     WHERE itemid $ggsql AND userid = :userid";
            $existingitems = $DB->get_records_sql($sql, $params);

            $notexisting = array_diff(array_keys($items), array_keys($existingitems));
            foreach ($notexisting as $itemid) {
                $gradeitem = $items[$itemid];
                $gradegrade = new grade_grade(array('itemid' => $itemid,
                                                    'userid' => $userid,
                                                    'rawgrademin' => $gradeitem->grademin,
                                                    'rawgrademax' => $gradeitem->grademax), false);
                $gradegrade->grade_item = $gradeitem;
                $gradegrade->insert('system');
            }
        }

                if (empty($grade_values) or empty($items) or ($this->grade_item->gradetype != GRADE_TYPE_VALUE and $this->grade_item->gradetype != GRADE_TYPE_SCALE)) {
            $grade->finalgrade = null;

            if (!is_null($oldfinalgrade)) {
                $grade->timemodified = time();
                $success = $grade->update('aggregation');

                                if ($success) {
                    \core\event\user_graded::create_from_grade($grade)->trigger();
                }
            }
            $dropped = $grade_values;
            $this->set_usedinaggregation($userid, $usedweights, $novalue, $dropped, $extracredit);
            return;
        }

                        foreach ($grade_values as $itemid=>$v) {
            if (is_null($v)) {
                                if ($this->aggregateonlygraded) {
                    unset($grade_values[$itemid]);
                                        $novalue[$itemid] = 0;
                    continue;
                }
            }
            if (in_array($itemid, $excluded)) {
                unset($grade_values[$itemid]);
                $dropped[$itemid] = 0;
                continue;
            }
                        $usergrademin = $items[$itemid]->grademin;
            $usergrademax = $items[$itemid]->grademax;
            if (isset($grademinoverrides[$itemid])) {
                $usergrademin = $grademinoverrides[$itemid];
            }
            if (isset($grademaxoverrides[$itemid])) {
                $usergrademax = $grademaxoverrides[$itemid];
            }
            if ($this->aggregation == GRADE_AGGREGATE_SUM) {
                                $grade_values[$itemid] = grade_grade::standardise_score($v, 0, $usergrademax, 0, 1);
            } else {
                $grade_values[$itemid] = grade_grade::standardise_score($v, $usergrademin, $usergrademax, 0, 1);
            }

        }

                foreach ($items as $itemid=>$value) {
            if (!isset($grade_values[$itemid]) and !in_array($itemid, $excluded)) {
                if (!$this->aggregateonlygraded) {
                    $grade_values[$itemid] = 0;
                } else {
                                        $novalue[$itemid] = 0;
                }
            }
        }

                $allvalues = $grade_values;
        if ($this->can_apply_limit_rules()) {
            $this->apply_limit_rules($grade_values, $items);
        }

        $moredropped = array_diff($allvalues, $grade_values);
        foreach ($moredropped as $drop => $unused) {
            $dropped[$drop] = 0;
        }

        foreach ($grade_values as $itemid => $val) {
            if (self::is_extracredit_used() && ($items[$itemid]->aggregationcoef > 0)) {
                $extracredit[$itemid] = 0;
            }
        }

        asort($grade_values, SORT_NUMERIC);

                if (count($grade_values) == 0) {
                        $grade->finalgrade = null;

            if (!is_null($oldfinalgrade)) {
                $grade->timemodified = time();
                $success = $grade->update('aggregation');

                                if ($success) {
                    \core\event\user_graded::create_from_grade($grade)->trigger();
                }
            }
            $this->set_usedinaggregation($userid, $usedweights, $novalue, $dropped, $extracredit);
            return;
        }

                $result = $this->aggregate_values_and_adjust_bounds($grade_values,
                                                            $items,
                                                            $usedweights,
                                                            $grademinoverrides,
                                                            $grademaxoverrides);
        $agg_grade = $result['grade'];

                $this->grade_item->grademin = $result['grademin'];
        $this->grade_item->grademax = $result['grademax'];

        if ($this->aggregation == GRADE_AGGREGATE_SUM) {
                                    $result['grademin'] = 0;
        }

                $finalgrade = grade_grade::standardise_score($agg_grade, 0, 1, $result['grademin'], $result['grademax']);
        $grade->finalgrade = $this->grade_item->bounded_grade($finalgrade);

        $oldrawgrademin = $grade->rawgrademin;
        $oldrawgrademax = $grade->rawgrademax;
        $grade->rawgrademin = $result['grademin'];
        $grade->rawgrademax = $result['grademax'];

                if (grade_floats_different($grade->finalgrade, $oldfinalgrade) ||
                grade_floats_different($grade->rawgrademax, $oldrawgrademax) ||
                grade_floats_different($grade->rawgrademin, $oldrawgrademin)) {
            $grade->timemodified = time();
            $success = $grade->update('aggregation');

                        if ($success) {
                \core\event\user_graded::create_from_grade($grade)->trigger();
            }
        }

        $this->set_usedinaggregation($userid, $usedweights, $novalue, $dropped, $extracredit);

        return;
    }

    
    private function set_usedinaggregation($userid, $usedweights, $novalue, $dropped, $extracredit) {
        global $DB;

                        $sql = "SELECT gi.id, gg.aggregationstatus, gg.aggregationweight FROM {grade_grades} gg
                  JOIN {grade_items} gi ON (gg.itemid = gi.id)
                 WHERE gg.userid = :userid";
        $params = array('categoryid' => $this->id, 'userid' => $userid);

                        $giids = array_keys($usedweights + $novalue + $dropped + $extracredit);

        if ($giids) {
                        list($itemsql, $itemlist) = $DB->get_in_or_equal($giids, SQL_PARAMS_NAMED, 'gg');
            $sql .= ' AND (gi.categoryid = :categoryid OR gi.id ' . $itemsql . ')';
            $params = $params + $itemlist;
        } else {
            $sql .= ' AND gi.categoryid = :categoryid';
        }
        $currentgrades = $DB->get_recordset_sql($sql, $params);

                $toupdate = array();

        if ($currentgrades->valid()) {

                        foreach ($currentgrades as $currentgrade) {

                                if (!empty($usedweights) && isset($usedweights[$currentgrade->id]) && $currentgrade->aggregationstatus === 'used') {
                                        if (grade_floats_equal($currentgrade->aggregationweight, $usedweights[$currentgrade->id])) {
                        unset($usedweights[$currentgrade->id]);
                    }
                                        if (!isset($novalue[$currentgrade->id]) && !isset($dropped[$currentgrade->id]) &&
                            !isset($extracredit[$currentgrade->id])) {
                        continue;
                    }
                }

                                if (!empty($novalue) && isset($novalue[$currentgrade->id])) {
                    if ($currentgrade->aggregationstatus !== 'novalue' ||
                            grade_floats_different($currentgrade->aggregationweight, 0)) {
                        $toupdate['novalue'][] = $currentgrade->id;
                    }
                    continue;
                }

                                if (!empty($dropped) && isset($dropped[$currentgrade->id])) {
                    if ($currentgrade->aggregationstatus !== 'dropped' ||
                            grade_floats_different($currentgrade->aggregationweight, 0)) {
                        $toupdate['dropped'][] = $currentgrade->id;
                    }
                    continue;
                }

                                if (!empty($extracredit) && isset($extracredit[$currentgrade->id])) {

                                                            if (!empty($usedweights) && isset($usedweights[$currentgrade->id]) &&
                            grade_floats_equal($currentgrade->aggregationweight, $usedweights[$currentgrade->id])) {
                        unset($usedweights[$currentgrade->id]);
                    }

                                                            if ($currentgrade->aggregationstatus !== 'extra' ||
                            (!empty($usedweights) && isset($usedweights[$currentgrade->id]))) {
                        $toupdate['extracredit'][] = $currentgrade->id;
                    }
                    continue;
                }

                                                if ($currentgrade->aggregationstatus !== 'unknown' || grade_floats_different($currentgrade->aggregationweight, 0)) {
                    $toupdate['unknown'][] = $currentgrade->id;
                }
            }
            $currentgrades->close();
        }

                if (!empty($toupdate['unknown'])) {
            list($itemsql, $itemlist) = $DB->get_in_or_equal($toupdate['unknown'], SQL_PARAMS_NAMED, 'g');

            $itemlist['userid'] = $userid;

            $sql = "UPDATE {grade_grades}
                       SET aggregationstatus = 'unknown',
                           aggregationweight = 0
                     WHERE itemid $itemsql AND userid = :userid";
            $DB->execute($sql, $itemlist);
        }

                if (!empty($usedweights)) {
                        foreach ($usedweights as $gradeitemid => $contribution) {
                $sql = "UPDATE {grade_grades}
                           SET aggregationstatus = 'used',
                               aggregationweight = :contribution
                         WHERE itemid = :itemid AND userid = :userid";

                $params = array('contribution' => $contribution, 'itemid' => $gradeitemid, 'userid' => $userid);
                $DB->execute($sql, $params);
            }
        }

                if (!empty($toupdate['novalue'])) {
            list($itemsql, $itemlist) = $DB->get_in_or_equal($toupdate['novalue'], SQL_PARAMS_NAMED, 'g');

            $itemlist['userid'] = $userid;

            $sql = "UPDATE {grade_grades}
                       SET aggregationstatus = 'novalue',
                           aggregationweight = 0
                     WHERE itemid $itemsql AND userid = :userid";

            $DB->execute($sql, $itemlist);
        }

                if (!empty($toupdate['dropped'])) {
            list($itemsql, $itemlist) = $DB->get_in_or_equal($toupdate['dropped'], SQL_PARAMS_NAMED, 'g');

            $itemlist['userid'] = $userid;

            $sql = "UPDATE {grade_grades}
                       SET aggregationstatus = 'dropped',
                           aggregationweight = 0
                     WHERE itemid $itemsql AND userid = :userid";

            $DB->execute($sql, $itemlist);
        }

                if (!empty($toupdate['extracredit'])) {
            list($itemsql, $itemlist) = $DB->get_in_or_equal($toupdate['extracredit'], SQL_PARAMS_NAMED, 'g');

            $itemlist['userid'] = $userid;

            $DB->set_field_select('grade_grades',
                                  'aggregationstatus',
                                  'extra',
                                  "itemid $itemsql AND userid = :userid",
                                  $itemlist);
        }
    }

    
    public function aggregate_values_and_adjust_bounds($grade_values,
                                                       $items,
                                                       & $weights = null,
                                                       $grademinoverrides = array(),
                                                       $grademaxoverrides = array()) {
        global $CFG;

        $category_item = $this->load_grade_item();
        $grademin = $category_item->grademin;
        $grademax = $category_item->grademax;

        switch ($this->aggregation) {

            case GRADE_AGGREGATE_MEDIAN:                 $num = count($grade_values);
                $grades = array_values($grade_values);

                                if ($weights !== null && $num > 0) {
                    $count = 0;
                    foreach ($grade_values as $itemid=>$grade_value) {
                        if (($num % 2 == 0) && ($count == intval($num/2)-1 || $count == intval($num/2))) {
                            $weights[$itemid] = 0.5;
                        } else if (($num % 2 != 0) && ($count == intval(($num/2)-0.5))) {
                            $weights[$itemid] = 1.0;
                        } else {
                            $weights[$itemid] = 0;
                        }
                        $count++;
                    }
                }
                if ($num % 2 == 0) {
                    $agg_grade = ($grades[intval($num/2)-1] + $grades[intval($num/2)]) / 2;
                } else {
                    $agg_grade = $grades[intval(($num/2)-0.5)];
                }

                break;

            case GRADE_AGGREGATE_MIN:
                $agg_grade = reset($grade_values);
                                if ($weights !== null) {
                    foreach ($grade_values as $itemid=>$grade_value) {
                        $weights[$itemid] = 0;
                    }
                }
                                $itemids = array_keys($grade_values);
                $weights[reset($itemids)] = 1;
                break;

            case GRADE_AGGREGATE_MAX:
                                if ($weights !== null) {
                    foreach ($grade_values as $itemid=>$grade_value) {
                        $weights[$itemid] = 0;
                    }
                }
                                $itemids = array_keys($grade_values);
                $weights[end($itemids)] = 1;
                $agg_grade = end($grade_values);
                break;

            case GRADE_AGGREGATE_MODE:                                       $converted_grade_values = array();

                foreach ($grade_values as $k => $gv) {

                    if (!is_int($gv) && !is_string($gv)) {
                        $converted_grade_values[$k] = (string) $gv;

                    } else {
                        $converted_grade_values[$k] = $gv;
                    }
                    if ($weights !== null) {
                        $weights[$k] = 0;
                    }
                }

                $freq = array_count_values($converted_grade_values);
                arsort($freq);                                      $top = reset($freq);                               $modes = array_keys($freq, $top);                  rsort($modes, SORT_NUMERIC);                       $agg_grade = reset($modes);
                                if ($weights !== null && $top > 0) {
                    foreach ($grade_values as $k => $gv) {
                        if ($gv == $agg_grade) {
                            $weights[$k] = 1.0 / $top;
                        }
                    }
                }
                break;

            case GRADE_AGGREGATE_WEIGHTED_MEAN:                 $weightsum = 0;
                $sum       = 0;

                foreach ($grade_values as $itemid=>$grade_value) {
                    if ($weights !== null) {
                        $weights[$itemid] = $items[$itemid]->aggregationcoef;
                    }
                    if ($items[$itemid]->aggregationcoef <= 0) {
                        continue;
                    }
                    $weightsum += $items[$itemid]->aggregationcoef;
                    $sum       += $items[$itemid]->aggregationcoef * $grade_value;
                }
                if ($weightsum == 0) {
                    $agg_grade = null;

                } else {
                    $agg_grade = $sum / $weightsum;
                    if ($weights !== null) {
                                                foreach ($weights as $itemid => $weight) {
                            $weights[$itemid] = $weight / $weightsum;
                        }
                    }

                }
                break;

            case GRADE_AGGREGATE_WEIGHTED_MEAN2:
                                                $this->load_grade_item();
                $weightsum = 0;
                $sum       = null;

                foreach ($grade_values as $itemid=>$grade_value) {
                    if ($items[$itemid]->aggregationcoef > 0) {
                        continue;
                    }

                    $weight = $items[$itemid]->grademax - $items[$itemid]->grademin;
                    if ($weight <= 0) {
                        continue;
                    }

                    $weightsum += $weight;
                    $sum += $weight * $grade_value;
                }

                                foreach ($grade_values as $itemid => $grade_value) {
                    if ($items[$itemid]->aggregationcoef <= 0) {
                        continue;
                    }

                    $weight = $items[$itemid]->grademax - $items[$itemid]->grademin;
                    if ($weight <= 0) {
                        $weights[$itemid] = 0;
                        continue;
                    }

                    $oldsum = $sum;
                    $weightedgrade = $weight * $grade_value;
                    $sum += $weightedgrade;

                    if ($weights !== null) {
                        if ($weightsum <= 0) {
                            $weights[$itemid] = 0;
                            continue;
                        }

                        $oldgrade = $oldsum / $weightsum;
                        $grade = $sum / $weightsum;
                        $normoldgrade = grade_grade::standardise_score($oldgrade, 0, 1, $grademin, $grademax);
                        $normgrade = grade_grade::standardise_score($grade, 0, 1, $grademin, $grademax);
                        $boundedoldgrade = $this->grade_item->bounded_grade($normoldgrade);
                        $boundedgrade = $this->grade_item->bounded_grade($normgrade);

                        if ($boundedgrade - $boundedoldgrade <= 0) {
                                                        $weights[$itemid] = 0;
                        } else if ($boundedgrade < $normgrade) {
                                                        $gradediff = $boundedgrade - $normoldgrade;
                            $gradediffnorm = grade_grade::standardise_score($gradediff, $grademin, $grademax, 0, 1);
                            $weights[$itemid] = $gradediffnorm / $grade_value;
                        } else {
                                                        $weights[$itemid] = $weight / $weightsum;
                        }
                    }
                }

                if ($weightsum == 0) {
                    $agg_grade = $sum; 
                } else {
                    $agg_grade = $sum / $weightsum;
                }

                                if ($weights !== null) {
                    foreach ($grade_values as $itemid=>$grade_value) {
                        if ($items[$itemid]->aggregationcoef > 0) {
                                                        continue;
                        }
                        if ($weightsum > 0) {
                            $weight = $items[$itemid]->grademax - $items[$itemid]->grademin;
                            $weights[$itemid] = $weight / $weightsum;
                        } else {
                            $weights[$itemid] = 0;
                        }
                    }
                }
                break;

            case GRADE_AGGREGATE_EXTRACREDIT_MEAN:                 $this->load_grade_item();
                $num = 0;
                $sum = null;

                foreach ($grade_values as $itemid=>$grade_value) {
                    if ($items[$itemid]->aggregationcoef == 0) {
                        $num += 1;
                        $sum += $grade_value;
                        if ($weights !== null) {
                            $weights[$itemid] = 1;
                        }
                    }
                }

                                foreach ($grade_values as $itemid=>$grade_value) {
                    if ($items[$itemid]->aggregationcoef > 0) {
                        $oldsum = $sum;
                        $sum += $items[$itemid]->aggregationcoef * $grade_value;

                        if ($weights !== null) {
                            if ($num <= 0) {
                                                                continue;
                            }

                            $oldgrade = $oldsum / $num;
                            $grade = $sum / $num;
                            $normoldgrade = grade_grade::standardise_score($oldgrade, 0, 1, $grademin, $grademax);
                            $normgrade = grade_grade::standardise_score($grade, 0, 1, $grademin, $grademax);
                            $boundedoldgrade = $this->grade_item->bounded_grade($normoldgrade);
                            $boundedgrade = $this->grade_item->bounded_grade($normgrade);

                            if ($boundedgrade - $boundedoldgrade <= 0) {
                                                                $weights[$itemid] = 0;
                            } else if ($boundedgrade < $normgrade) {
                                                                $gradediff = $boundedgrade - $normoldgrade;
                                $gradediffnorm = grade_grade::standardise_score($gradediff, $grademin, $grademax, 0, 1);
                                $weights[$itemid] = $gradediffnorm / $grade_value;
                            } else {
                                                                $weights[$itemid] = 1.0 / $num;
                            }
                        }
                    }
                }

                if ($weights !== null && $num > 0) {
                    foreach ($grade_values as $itemid=>$grade_value) {
                        if ($items[$itemid]->aggregationcoef > 0) {
                                                        continue;
                        }
                        if ($weights[$itemid]) {
                            $weights[$itemid] = 1.0 / $num;
                        }
                    }
                }

                if ($num == 0) {
                    $agg_grade = $sum; 
                } else {
                    $agg_grade = $sum / $num;
                }

                break;

            case GRADE_AGGREGATE_SUM:                    $this->load_grade_item();
                $num = count($grade_values);
                $sum = 0;

                                                $gradebookcalculationfreeze = 'gradebook_calculations_freeze_' . $this->courseid;
                $oldextracreditcalculation = isset($CFG->$gradebookcalculationfreeze)
                        && ($CFG->$gradebookcalculationfreeze <= 20150619);

                $sumweights = 0;
                $grademin = 0;
                $grademax = 0;
                $extracredititems = array();
                foreach ($grade_values as $itemid => $gradevalue) {
                                        $usergrademin = $items[$itemid]->grademin;
                    $usergrademax = $items[$itemid]->grademax;
                    if (isset($grademinoverrides[$itemid])) {
                        $usergrademin = $grademinoverrides[$itemid];
                    }
                    if (isset($grademaxoverrides[$itemid])) {
                        $usergrademax = $grademaxoverrides[$itemid];
                    }

                                        if ($items[$itemid]->aggregationcoef > 0) {
                        $extracredititems[$itemid] = $items[$itemid];
                    }

                                        if (!isset($extracredititems[$itemid]) && $items[$itemid]->aggregationcoef2 > 0) {
                        $grademin += $usergrademin;
                        $grademax += $usergrademax;
                        $sumweights += $items[$itemid]->aggregationcoef2;
                    }
                }
                $userweights = array();
                $totaloverriddenweight = 0;
                $totaloverriddengrademax = 0;
                                                foreach ($grade_values as $itemid => $gradevalue) {
                    if ($items[$itemid]->weightoverride) {
                        if ($items[$itemid]->aggregationcoef2 <= 0) {
                                                        $userweights[$itemid] = 0;
                            continue;
                        }
                        $userweights[$itemid] = $sumweights ? ($items[$itemid]->aggregationcoef2 / $sumweights) : 0;
                        if (!$oldextracreditcalculation && isset($extracredititems[$itemid])) {
                                                        continue;
                        }
                        $totaloverriddenweight += $userweights[$itemid];
                        $usergrademax = $items[$itemid]->grademax;
                        if (isset($grademaxoverrides[$itemid])) {
                            $usergrademax = $grademaxoverrides[$itemid];
                        }
                        $totaloverriddengrademax += $usergrademax;
                    }
                }
                $nonoverriddenpoints = $grademax - $totaloverriddengrademax;

                                foreach ($grade_values as $itemid => $gradevalue) {
                    if (!$items[$itemid]->weightoverride && ($oldextracreditcalculation || !isset($extracredititems[$itemid]))) {
                        $usergrademax = $items[$itemid]->grademax;
                        if (isset($grademaxoverrides[$itemid])) {
                            $usergrademax = $grademaxoverrides[$itemid];
                        }
                        if ($nonoverriddenpoints > 0) {
                            $userweights[$itemid] = ($usergrademax/$nonoverriddenpoints) * (1 - $totaloverriddenweight);
                        } else {
                            $userweights[$itemid] = 0;
                            if ($items[$itemid]->aggregationcoef2 > 0) {
                                                                                                $grademax -= $usergrademax;
                            }
                        }
                    }
                }

                                if (!$oldextracreditcalculation) {
                    foreach ($grade_values as $itemid => $gradevalue) {
                        if (!$items[$itemid]->weightoverride && isset($extracredititems[$itemid])) {
                            $usergrademax = $items[$itemid]->grademax;
                            if (isset($grademaxoverrides[$itemid])) {
                                $usergrademax = $grademaxoverrides[$itemid];
                            }
                            $userweights[$itemid] = $grademax ? ($usergrademax / $grademax) : 0;
                        }
                    }
                }

                                foreach ($grade_values as $itemid => $gradevalue) {
                    if (isset($extracredititems[$itemid])) {
                                                continue;
                    }
                    $sum += $gradevalue * $userweights[$itemid] * $grademax;
                    if ($weights !== null) {
                        $weights[$itemid] = $userweights[$itemid];
                    }
                }

                                                                                $oldgrademax = $this->grade_item->grademax;
                $oldgrademin = $this->grade_item->grademin;
                foreach ($grade_values as $itemid => $gradevalue) {
                    if (!isset($extracredititems[$itemid])) {
                        continue;
                    }
                    $oldsum = $sum;
                    $weightedgrade = $gradevalue * $userweights[$itemid] * $grademax;
                    $sum += $weightedgrade;

                                        if ($weights !== null) {
                        if ($grademax <= 0) {
                                                                                    $weights[$itemid] = $userweights[$itemid];
                            continue;
                        }

                        $oldfinalgrade = $this->grade_item->bounded_grade($oldsum);
                        $newfinalgrade = $this->grade_item->bounded_grade($sum);
                        $finalgradediff = $newfinalgrade - $oldfinalgrade;
                        if ($finalgradediff <= 0) {
                                                        $weights[$itemid] = 0;
                        } else if ($finalgradediff < $weightedgrade) {
                                                                                    $weights[$itemid] = $finalgradediff / ($gradevalue * $grademax);
                        } else {
                                                        $weights[$itemid] = $userweights[$itemid];
                        }
                    }
                }
                $this->grade_item->grademax = $oldgrademax;
                $this->grade_item->grademin = $oldgrademin;

                if ($grademax > 0) {
                    $agg_grade = $sum / $grademax;                 } else {
                                        $agg_grade = $sum;
                    $grademax = $sum;
                }

                break;

            case GRADE_AGGREGATE_MEAN:                default:
                $num = count($grade_values);
                $sum = array_sum($grade_values);
                $agg_grade = $sum / $num;
                                if ($weights !== null && $num > 0) {
                    foreach ($grade_values as $itemid=>$grade_value) {
                        $weights[$itemid] = 1.0 / $num;
                    }
                }
                break;
        }

        return array('grade' => $agg_grade, 'grademin' => $grademin, 'grademax' => $grademax);
    }

    
    public function aggregate_values($grade_values, $items) {
        debugging('grade_category::aggregate_values() is deprecated.
                   Call grade_category::aggregate_values_and_adjust_bounds() instead.', DEBUG_DEVELOPER);
        $result = $this->aggregate_values_and_adjust_bounds($grade_values, $items);
        return $result['grade'];
    }

    
    private function auto_update_max() {
        global $CFG, $DB;
        if ($this->aggregation != GRADE_AGGREGATE_SUM) {
                        return;
        }

                $this->load_grade_item();
        $depends_on = $this->grade_item->depends_on();

                        $gradebookcalculationfreeze = 'gradebook_calculations_freeze_' . $this->courseid;
        $oldextracreditcalculation = isset($CFG->$gradebookcalculationfreeze) && ($CFG->$gradebookcalculationfreeze <= 20150627);
                if (!$oldextracreditcalculation) {
                        if ($this->grade_item->is_calculated()) {
                return;
            }
        }

        $items = false;
        if (!empty($depends_on)) {
            list($usql, $params) = $DB->get_in_or_equal($depends_on);
            $sql = "SELECT *
                      FROM {grade_items}
                     WHERE id $usql";
            $items = $DB->get_records_sql($sql, $params);
        }

        if (!$items) {

            if ($this->grade_item->grademax != 0 or $this->grade_item->gradetype != GRADE_TYPE_VALUE) {
                $this->grade_item->grademax  = 0;
                $this->grade_item->grademin  = 0;
                $this->grade_item->gradetype = GRADE_TYPE_VALUE;
                $this->grade_item->update('aggregation');
            }
            return;
        }

                $maxes = array();

        foreach ($items as $item) {

            if ($item->aggregationcoef > 0) {
                                continue;
            } else if ($item->aggregationcoef2 <= 0) {
                                continue;
            }

            if ($item->gradetype == GRADE_TYPE_VALUE) {
                $maxes[$item->id] = $item->grademax;

            } else if ($item->gradetype == GRADE_TYPE_SCALE) {
                $maxes[$item->id] = $item->grademax;             }
        }

        if ($this->can_apply_limit_rules()) {
                        $this->apply_limit_rules($maxes, $items);
        }
        $max = array_sum($maxes);

                if ($this->grade_item->grademax != $max or $this->grade_item->grademin != 0 or $this->grade_item->gradetype != GRADE_TYPE_VALUE) {
            $this->grade_item->grademax  = $max;
            $this->grade_item->grademin  = 0;
            $this->grade_item->gradetype = GRADE_TYPE_VALUE;
            $this->grade_item->update('aggregation');
        }
    }

    
    private function auto_update_weights() {
        global $CFG;
        if ($this->aggregation != GRADE_AGGREGATE_SUM) {
                        return;
        }
        $children = $this->get_children();

        $gradeitem = null;

                $totalnonoverriddengrademax = 0;
        $totalgrademax = 0;

                $totaloverriddenweight  = 0;
        $totaloverriddengrademax  = 0;

                $automaticgradeitemspresent = false;
                $requiresnormalising = false;

                $overridearray = array();
        foreach ($children as $sortorder => $child) {
            $gradeitem = null;

            if ($child['type'] == 'item') {
                $gradeitem = $child['object'];
            } else if ($child['type'] == 'category') {
                $gradeitem = $child['object']->load_grade_item();
            }

            if ($gradeitem->gradetype == GRADE_TYPE_NONE || $gradeitem->gradetype == GRADE_TYPE_TEXT) {
                                continue;
            } else if (!$this->aggregateoutcomes && $gradeitem->is_outcome_item()) {
                                continue;
            } else if (empty($CFG->grade_includescalesinaggregation) && $gradeitem->gradetype == GRADE_TYPE_SCALE) {
                                continue;
            }

                        $overridearray[$gradeitem->id] = array();
            $overridearray[$gradeitem->id]['extracredit'] = intval($gradeitem->aggregationcoef);
            $overridearray[$gradeitem->id]['weight'] = $gradeitem->aggregationcoef2;
            $overridearray[$gradeitem->id]['weightoverride'] = intval($gradeitem->weightoverride);
                                                if (!$gradeitem->weightoverride && $gradeitem->aggregationcoef == 0) {
                $automaticgradeitemspresent = true;
            }

            if ($gradeitem->aggregationcoef > 0) {
                                continue;
            } else if ($gradeitem->weightoverride > 0 && $gradeitem->aggregationcoef2 <= 0) {
                                continue;
            }

            $totalgrademax += $gradeitem->grademax;
            if ($gradeitem->weightoverride > 0) {
                $totaloverriddenweight += $gradeitem->aggregationcoef2;
                $totaloverriddengrademax += $gradeitem->grademax;
            }
        }

                $normalisetotal = 0;
                        $overriddentotal = 0;
                $setotherweightstozero = false;
                foreach ($overridearray as $gradeitemdetail) {
                        if (!$gradeitemdetail['extracredit']) {
                $normalisetotal += $gradeitemdetail['weight'];
            }
                                    if ($gradeitemdetail['weightoverride'] && !$gradeitemdetail['extracredit'] && $gradeitemdetail['weight'] > 0) {
                                $overriddentotal += $gradeitemdetail['weight'];
            }
        }
        if ($overriddentotal > 1) {
                        $requiresnormalising = true;
                        $normalisetotal = $overriddentotal;
        }

        $totalnonoverriddengrademax = $totalgrademax - $totaloverriddengrademax;

                        $gradebookcalculationfreeze = (int)get_config('core', 'gradebook_calculations_freeze_' . $this->courseid);
        $oldextracreditcalculation = $gradebookcalculationfreeze && ($gradebookcalculationfreeze <= 20150619);

        reset($children);
        foreach ($children as $sortorder => $child) {
            $gradeitem = null;

            if ($child['type'] == 'item') {
                $gradeitem = $child['object'];
            } else if ($child['type'] == 'category') {
                $gradeitem = $child['object']->load_grade_item();
            }

            if ($gradeitem->gradetype == GRADE_TYPE_NONE || $gradeitem->gradetype == GRADE_TYPE_TEXT) {
                                                continue;
            } else if (!$this->aggregateoutcomes && $gradeitem->is_outcome_item()) {
                                continue;
            } else if (empty($CFG->grade_includescalesinaggregation) && $gradeitem->gradetype == GRADE_TYPE_SCALE) {
                                continue;
            } else if (!$oldextracreditcalculation && $gradeitem->aggregationcoef > 0 && $gradeitem->weightoverride) {
                                                continue;
            }

                        $prevaggregationcoef2 = $gradeitem->aggregationcoef2;

            if (!$oldextracreditcalculation && $gradeitem->aggregationcoef > 0 && !$gradeitem->weightoverride) {
                                $gradeitem->aggregationcoef2 = $totalgrademax ? ($gradeitem->grademax / $totalgrademax) : 0;

            } else if (!$gradeitem->weightoverride) {
                                if ($totaloverriddenweight >= 1 || $totalnonoverriddengrademax == 0 || $gradeitem->grademax == 0) {
                                        $gradeitem->aggregationcoef2 = 0;
                } else {
                                                            $gradeitem->aggregationcoef2 = ($gradeitem->grademax/$totalnonoverriddengrademax) *
                            (1 - $totaloverriddenweight);
                }

            } else if ((!$automaticgradeitemspresent && $normalisetotal != 1) || ($requiresnormalising)
                    || $overridearray[$gradeitem->id]['weight'] < 0) {
                                                if ($normalisetotal == 0 || $overridearray[$gradeitem->id]['weight'] < 0) {
                                                            $gradeitem->aggregationcoef2 = 0;
                } else {
                    $gradeitem->aggregationcoef2 = $overridearray[$gradeitem->id]['weight'] / $normalisetotal;
                }
            }

            if (grade_floatval($prevaggregationcoef2) !== grade_floatval($gradeitem->aggregationcoef2)) {
                                $gradeitem->update();
            }
        }
    }

    
    public function apply_limit_rules(&$grade_values, $items) {
        $extraused = $this->is_extracredit_used();

        if (!empty($this->droplow)) {
            asort($grade_values, SORT_NUMERIC);
            $dropped = 0;

                                    $droppedsomething = true;

            while ($dropped < $this->droplow && $droppedsomething) {
                $droppedsomething = false;

                $grade_keys = array_keys($grade_values);
                $gradekeycount = count($grade_keys);

                if ($gradekeycount === 0) {
                                        break;
                }

                $originalindex = $founditemid = $foundmax = null;

                                foreach ($grade_keys as $gradekeyindex=>$gradekey) {
                    if (!$extraused || $items[$gradekey]->aggregationcoef <= 0) {
                                                $originalindex = $gradekeyindex;
                        $founditemid = $grade_keys[$originalindex];
                        $foundmax = $items[$founditemid]->grademax;
                        break;
                    }
                }

                if (empty($founditemid)) {
                                        break;
                }

                                                $i = 1;
                while ($originalindex + $i < $gradekeycount) {

                    $possibleitemid = $grade_keys[$originalindex+$i];
                    $i++;

                    if ($grade_values[$founditemid] != $grade_values[$possibleitemid]) {
                                                break;
                    }

                    if ($extraused && $items[$possibleitemid]->aggregationcoef > 0) {
                                                continue;
                    }

                    if ($foundmax < $items[$possibleitemid]->grademax) {
                                                $foundmax = $items[$possibleitemid]->grademax;
                        $founditemid = $possibleitemid;
                                            }
                }

                                unset($grade_values[$founditemid]);
                $dropped++;
                $droppedsomething = true;
            }

        } else if (!empty($this->keephigh)) {
            arsort($grade_values, SORT_NUMERIC);
            $kept = 0;

            foreach ($grade_values as $itemid=>$value) {

                if ($extraused and $items[$itemid]->aggregationcoef > 0) {
                    
                } else if ($kept < $this->keephigh) {
                    $kept++;

                } else {
                    unset($grade_values[$itemid]);
                }
            }
        }
    }

    
    public function can_apply_limit_rules() {
        if ($this->canapplylimitrules !== null) {
            return $this->canapplylimitrules;
        }

                $this->canapplylimitrules = true;

                if ($this->aggregation == GRADE_AGGREGATE_SUM) {
            $canapply = true;

                        $gradeitems = $this->get_children();
            $validitems = 0;
            $lastweight = null;
            $lastmaxgrade = null;
            foreach ($gradeitems as $gradeitem) {
                $gi = $gradeitem['object'];

                if ($gradeitem['type'] == 'category') {
                                        $canapply = false;
                    break;
                }

                if ($gi->aggregationcoef > 0) {
                                        $canapply = false;
                    break;
                }

                if ($lastweight !== null && $lastweight != $gi->aggregationcoef2) {
                                        $canapply = false;
                    break;
                }

                if ($lastmaxgrade !== null && $lastmaxgrade != $gi->grademax) {
                                                            $canapply = false;
                    break;
                }

                $lastweight = $gi->aggregationcoef2;
                $lastmaxgrade = $gi->grademax;
            }

            $this->canapplylimitrules = $canapply;
        }

        return $this->canapplylimitrules;
    }

    
    public function is_extracredit_used() {
        return self::aggregation_uses_extracredit($this->aggregation);
    }

    
    public static function aggregation_uses_extracredit($aggregation) {
        return ($aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN2
             or $aggregation == GRADE_AGGREGATE_EXTRACREDIT_MEAN
             or $aggregation == GRADE_AGGREGATE_SUM);
    }

    
    public function is_aggregationcoef_used() {
        return self::aggregation_uses_aggregationcoef($this->aggregation);

    }

    
    public static function aggregation_uses_aggregationcoef($aggregation) {
        return ($aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN
             or $aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN2
             or $aggregation == GRADE_AGGREGATE_EXTRACREDIT_MEAN
             or $aggregation == GRADE_AGGREGATE_SUM);

    }

    
    public function get_coefstring($first=true) {
        if (!is_null($this->coefstring)) {
            return $this->coefstring;
        }

        $overriding_coefstring = null;

                if (!$first) {

            if ($parent_category = $this->load_parent_category()) {
                return $parent_category->get_coefstring(false);

            } else {
                return null;
            }

        } else if ($first) {

            if ($parent_category = $this->load_parent_category()) {
                $overriding_coefstring = $parent_category->get_coefstring(false);
            }
        }

                if (!is_null($overriding_coefstring)) {
            return $overriding_coefstring;
        }

                if ($this->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN) {
            $this->coefstring = 'aggregationcoefweight';

        } else if ($this->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN2) {
            $this->coefstring = 'aggregationcoefextrasum';

        } else if ($this->aggregation == GRADE_AGGREGATE_EXTRACREDIT_MEAN) {
            $this->coefstring = 'aggregationcoefextraweight';

        } else if ($this->aggregation == GRADE_AGGREGATE_SUM) {
            $this->coefstring = 'aggregationcoefextraweightsum';

        } else {
            $this->coefstring = 'aggregationcoef';
        }
        return $this->coefstring;
    }

    
    public static function fetch_course_tree($courseid, $include_category_items=false) {
        $course_category = grade_category::fetch_course_category($courseid);
        $category_array = array('object'=>$course_category, 'type'=>'category', 'depth'=>1,
                                'children'=>$course_category->get_children($include_category_items));

        $course_category->sortorder = $course_category->get_sortorder();
        $sortorder = $course_category->get_sortorder();
        return grade_category::_fetch_course_tree_recursion($category_array, $sortorder);
    }

    
    static private function _fetch_course_tree_recursion($category_array, &$sortorder) {
        if (isset($category_array['object']->gradetype) && $category_array['object']->gradetype==GRADE_TYPE_NONE) {
            return null;
        }

                $result = array('object'=>$category_array['object'], 'type'=>$category_array['type'], 'depth'=>$category_array['depth']);

                if (array_key_exists('finalgrades', $category_array)) {
            $result['finalgrades'] = $category_array['finalgrades'];
        }

                if (!empty($category_array['children'])) {
            $result['children'] = array();
                        $child = null;

            foreach ($category_array['children'] as $oldorder=>$child_array) {

                if ($child_array['type'] == 'courseitem' or $child_array['type'] == 'categoryitem') {
                    $child = grade_category::_fetch_course_tree_recursion($child_array, $sortorder);
                    if (!empty($child)) {
                        $result['children'][$sortorder] = $child;
                    }
                }
            }

            foreach ($category_array['children'] as $oldorder=>$child_array) {

                if ($child_array['type'] != 'courseitem' and $child_array['type'] != 'categoryitem') {
                    $child = grade_category::_fetch_course_tree_recursion($child_array, $sortorder);
                    if (!empty($child)) {
                        $result['children'][++$sortorder] = $child;
                    }
                }
            }
        }

        return $result;
    }

    
    public function get_children($include_category_items=false) {
        global $DB;

                        
        $cats  = $DB->get_records('grade_categories', array('courseid' => $this->courseid));
        $items = $DB->get_records('grade_items', array('courseid' => $this->courseid));

                foreach ($cats as $catid=>$cat) {
            $cats[$catid]->children = array();
        }

                foreach ($items as $item) {

            if ($item->itemtype == 'course' or $item->itemtype == 'category') {
                $cats[$item->iteminstance]->sortorder = $item->sortorder;

                if (!$include_category_items) {
                    continue;
                }
                $categoryid = $item->iteminstance;

            } else {
                $categoryid = $item->categoryid;
                if (empty($categoryid)) {
                    debugging('Found a grade item that isnt in a category');
                }
            }

                        $sortorder = $item->sortorder;

            while (array_key_exists($categoryid, $cats)
                && array_key_exists($sortorder, $cats[$categoryid]->children)) {

                $sortorder++;
            }

            $cats[$categoryid]->children[$sortorder] = $item;

        }

                $category = false;

        foreach ($cats as $catid=>$cat) {

            if (empty($cat->parent)) {

                if ($cat->path !== '/'.$cat->id.'/') {
                    $grade_category = new grade_category($cat, false);
                    $grade_category->path  = '/'.$cat->id.'/';
                    $grade_category->depth = 1;
                    $grade_category->update('system');
                    return $this->get_children($include_category_items);
                }

            } else {

                if (empty($cat->path) or !preg_match('|/'.$cat->parent.'/'.$cat->id.'/$|', $cat->path)) {
                                        static $recursioncounter = 0;                     $recursioncounter++;

                    if ($recursioncounter < 5) {
                                                $grade_category = new grade_category($cat, false);
                        $grade_category->depth = 0;
                        $grade_category->path  = null;
                        $grade_category->update('system');
                        return $this->get_children($include_category_items);
                    }
                }
                                $sortorder = $cat->sortorder;

                while (array_key_exists($sortorder, $cats[$cat->parent]->children)) {
                                        $sortorder++;
                }

                $cats[$cat->parent]->children[$sortorder] = &$cats[$catid];
            }

            if ($catid == $this->id) {
                $category = &$cats[$catid];
            }
        }

        unset($items);         unset($cats); 
        $children_array = array();
        if (is_object($category)) {
            $children_array = grade_category::_get_children_recursion($category);
            ksort($children_array);
        }

        return $children_array;

    }

    
    private static function _get_children_recursion($category) {

        $children_array = array();
        foreach ($category->children as $sortorder=>$child) {

            if (array_key_exists('itemtype', $child)) {
                $grade_item = new grade_item($child, false);

                if (in_array($grade_item->itemtype, array('course', 'category'))) {
                    $type  = $grade_item->itemtype.'item';
                    $depth = $category->depth;

                } else {
                    $type  = 'item';
                    $depth = $category->depth;                 }
                $children_array[$sortorder] = array('object'=>$grade_item, 'type'=>$type, 'depth'=>$depth);

            } else {
                $children = grade_category::_get_children_recursion($child);
                $grade_category = new grade_category($child, false);

                if (empty($children)) {
                    $children = array();
                }
                $children_array[$sortorder] = array('object'=>$grade_category, 'type'=>'category', 'depth'=>$grade_category->depth, 'children'=>$children);
            }
        }

                ksort($children_array);

        return $children_array;
    }

    
    public function load_grade_item() {
        if (empty($this->grade_item)) {
            $this->grade_item = $this->get_grade_item();
        }
        return $this->grade_item;
    }

    
    public function get_grade_item() {
        if (empty($this->id)) {
            debugging("Attempt to obtain a grade_category's associated grade_item without the category's ID being set.");
            return false;
        }

        if (empty($this->parent)) {
            $params = array('courseid'=>$this->courseid, 'itemtype'=>'course', 'iteminstance'=>$this->id);

        } else {
            $params = array('courseid'=>$this->courseid, 'itemtype'=>'category', 'iteminstance'=>$this->id);
        }

        if (!$grade_items = grade_item::fetch_all($params)) {
                        $grade_item = new grade_item($params, false);
            $grade_item->gradetype = GRADE_TYPE_VALUE;
            $grade_item->insert('system');

        } else if (count($grade_items) == 1) {
                        $grade_item = reset($grade_items);

        } else {
            debugging("Found more than one grade_item attached to category id:".$this->id);
                        $grade_item = reset($grade_items);
        }

        return $grade_item;
    }

    
    public function load_parent_category() {
        if (empty($this->parent_category) && !empty($this->parent)) {
            $this->parent_category = $this->get_parent_category();
        }
        return $this->parent_category;
    }

    
    public function get_parent_category() {
        if (!empty($this->parent)) {
            $parent_category = new grade_category(array('id' => $this->parent));
            return $parent_category;
        } else {
            return null;
        }
    }

    
    public function get_name() {
        global $DB;
                if (empty($this->parent) && $this->fullname == '?') {
            $course = $DB->get_record('course', array('id'=> $this->courseid));
            return format_string($course->fullname);

        } else {
            return $this->fullname;
        }
    }

    
    public function get_description() {
        $allhelp = array();
        if ($this->aggregation != GRADE_AGGREGATE_SUM) {
            $aggrstrings = grade_helper::get_aggregation_strings();
            $allhelp[] = $aggrstrings[$this->aggregation];
        }

        if ($this->droplow && $this->can_apply_limit_rules()) {
            $allhelp[] = get_string('droplowestvalues', 'grades', $this->droplow);
        }
        if ($this->keephigh && $this->can_apply_limit_rules()) {
            $allhelp[] = get_string('keephighestvalues', 'grades', $this->keephigh);
        }
        if (!$this->aggregateonlygraded) {
            $allhelp[] = get_string('aggregatenotonlygraded', 'grades');
        }
        if ($allhelp) {
            return implode('. ', $allhelp) . '.';
        }
        return '';
    }

    
    public function set_parent($parentid, $source=null) {
        if ($this->parent == $parentid) {
            return true;
        }

        if ($parentid == $this->id) {
            print_error('cannotassignselfasparent');
        }

        if (empty($this->parent) and $this->is_course_category()) {
            print_error('cannothaveparentcate');
        }

                if (!$parent_category = grade_category::fetch(array('id'=>$parentid, 'courseid'=>$this->courseid))) {
            return false;
        }

        $this->force_regrading();

                $this->parent          = $parent_category->id;
        $this->parent_category =& $parent_category;
        $this->path            = null;               $this->depth           = 0;                  $this->update($source);

        return $this->update($source);
    }

    
    public function get_final($userid=null) {
        $this->load_grade_item();
        return $this->grade_item->get_final($userid);
    }

    
    public function get_sortorder() {
        $this->load_grade_item();
        return $this->grade_item->get_sortorder();
    }

    
    public function get_idnumber() {
        $this->load_grade_item();
        return $this->grade_item->get_idnumber();
    }

    
    public function set_sortorder($sortorder) {
        $this->load_grade_item();
        $this->grade_item->set_sortorder($sortorder);
    }

    
    public function move_after_sortorder($sortorder) {
        $this->load_grade_item();
        $this->grade_item->move_after_sortorder($sortorder);
    }

    
    public function is_course_category() {
        $this->load_grade_item();
        return $this->grade_item->is_course_item();
    }

    
    public static function fetch_course_category($courseid) {
        if (empty($courseid)) {
            debugging('Missing course id!');
            return false;
        }

                if ($course_category = grade_category::fetch(array('courseid'=>$courseid, 'parent'=>null))) {
            return $course_category;
        }

                $course_category = new grade_category();
        $course_category->insert_course_category($courseid);

        return $course_category;
    }

    
    public function is_editable() {
        return true;
    }

    
    public function is_locked() {
        $this->load_grade_item();
        return $this->grade_item->is_locked();
    }

    
    public function set_locked($lockedstate, $cascade=false, $refresh=true) {
        $this->load_grade_item();

        $result = $this->grade_item->set_locked($lockedstate, $cascade, true);

        if ($cascade) {
                        if ($children = grade_item::fetch_all(array('categoryid'=>$this->id))) {

                foreach ($children as $child) {
                    $child->set_locked($lockedstate, true, false);

                    if (empty($lockedstate) and $refresh) {
                                                $child->refresh_grades();
                    }
                }
            }

            if ($children = grade_category::fetch_all(array('parent'=>$this->id))) {

                foreach ($children as $child) {
                    $child->set_locked($lockedstate, true, true);
                }
            }
        }

        return $result;
    }

    
    public static function set_properties(&$instance, $params) {
        global $DB;

        $fromaggregation = $instance->aggregation;

        parent::set_properties($instance, $params);

                if (isset($params->aggregation) && !empty($instance->id)) {
            $achildwasdupdated = false;

                        $children = $instance->get_children();
            foreach ($children as $child) {
                $item = $child['object'];
                if ($child['type'] == 'category') {
                    $item = $item->load_grade_item();
                }

                                if ($item->set_aggregation_fields_for_aggregation($fromaggregation, $params->aggregation)) {
                    $item->update();
                    $achildwasdupdated = true;
                }
            }

                                                if ($achildwasdupdated && !empty($instance->grade_item) && $instance->is_course_category()) {
                $instance->grade_item = null;
                $instance->load_grade_item();
            }
        }
    }

    
    public function set_hidden($hidden, $cascade=false) {
        $this->load_grade_item();
                $this->grade_item->set_hidden($hidden, $cascade);
                parent::set_hidden($hidden, $cascade);

        if ($cascade) {

            if ($children = grade_item::fetch_all(array('categoryid'=>$this->id))) {

                foreach ($children as $child) {
                    if ($child->can_control_visibility()) {
                        $child->set_hidden($hidden, $cascade);
                    }
                }
            }

            if ($children = grade_category::fetch_all(array('parent'=>$this->id))) {

                foreach ($children as $child) {
                    $child->set_hidden($hidden, $cascade);
                }
            }
        }

                if( !$hidden ) {
            $category_array = grade_category::fetch_all(array('id'=>$this->parent));
            if ($category_array && array_key_exists($this->parent, $category_array)) {
                $category = $category_array[$this->parent];
                                                    $category->set_hidden($hidden, false);
                            }
        }
    }

    
    public function apply_default_settings() {
        global $CFG;

        foreach ($this->forceable as $property) {

            if (isset($CFG->{"grade_$property"})) {

                if ($CFG->{"grade_$property"} == -1) {
                    continue;                 }
                $this->$property = $CFG->{"grade_$property"};
            }
        }
    }

    
    public function apply_forced_settings() {
        global $CFG;

        $updated = false;

        foreach ($this->forceable as $property) {

            if (isset($CFG->{"grade_$property"}) and isset($CFG->{"grade_{$property}_flag"}) and
                                                    ((int) $CFG->{"grade_{$property}_flag"} & 1)) {

                if ($CFG->{"grade_$property"} == -1) {
                    continue;                 }
                $this->$property = $CFG->{"grade_$property"};
                $updated = true;
            }
        }

        return $updated;
    }

    
    public static function updated_forced_settings() {
        global $CFG, $DB;
        $params = array(1, 'course', 'category');
        $sql = "UPDATE {grade_items} SET needsupdate=? WHERE itemtype=? or itemtype=?";
        $DB->execute($sql, $params);
    }

    
    public static function get_default_aggregation_coefficient_values($aggregationmethod) {
        $defaultcoefficients = array(
            'aggregationcoef' => 0,
            'aggregationcoef2' => 0,
            'weightoverride' => 0
        );

        switch ($aggregationmethod) {
            case GRADE_AGGREGATE_WEIGHTED_MEAN:
                $defaultcoefficients['aggregationcoef'] = 1;
                break;
            case GRADE_AGGREGATE_SUM:
                $defaultcoefficients['aggregationcoef2'] = 1;
                break;
        }

        return $defaultcoefficients;
    }

    
    protected function notify_changed($deleted) {
        self::clean_record_set();
    }

    
    protected static function generate_record_set_key($params) {
        return sha1(json_encode($params));
    }

    
    protected static function retrieve_record_set($params) {
        $cache = cache::make('core', 'grade_categories');
        return $cache->get(self::generate_record_set_key($params));
    }

    
    protected static function set_record_set($params, $records) {
        $cache = cache::make('core', 'grade_categories');
        return $cache->set(self::generate_record_set_key($params), $records);
    }

    
    public static function clean_record_set() {
        cache_helper::purge_by_event('changesingradecategories');
    }
}
