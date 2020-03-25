<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/grade/grading/form/lib.php');


class gradingform_rubric_controller extends gradingform_controller {
        
    const DISPLAY_EDIT_FULL     = 1;
    
    const DISPLAY_EDIT_FROZEN   = 2;
    
    const DISPLAY_PREVIEW       = 3;
    
    const DISPLAY_PREVIEW_GRADED= 8;
    
    const DISPLAY_EVAL          = 4;
    
    const DISPLAY_EVAL_FROZEN   = 5;
    
    const DISPLAY_REVIEW        = 6;
    
    const DISPLAY_VIEW          = 7;

    
    public function extend_settings_navigation(settings_navigation $settingsnav, navigation_node $node=null) {
        $node->add(get_string('definerubric', 'gradingform_rubric'),
            $this->get_editor_url(), settings_navigation::TYPE_CUSTOM,
            null, null, new pix_icon('icon', '', 'gradingform_rubric'));
    }

    
    public function extend_navigation(global_navigation $navigation, navigation_node $node=null) {
        if (has_capability('moodle/grade:managegradingforms', $this->get_context())) {
                        return;
        }
        if ($this->is_form_defined() && ($options = $this->get_options()) && !empty($options['alwaysshowdefinition'])) {
            $node->add(get_string('gradingof', 'gradingform_rubric', get_grading_manager($this->get_areaid())->get_area_title()),
                    new moodle_url('/grade/grading/form/'.$this->get_method_name().'/preview.php', array('areaid' => $this->get_areaid())),
                    settings_navigation::TYPE_CUSTOM);
        }
    }

    
    public function update_definition(stdClass $newdefinition, $usermodified = null) {
        $this->update_or_check_rubric($newdefinition, $usermodified, true);
        if (isset($newdefinition->rubric['regrade']) && $newdefinition->rubric['regrade']) {
            $this->mark_for_regrade();
        }
    }

    
    public function update_or_check_rubric(stdClass $newdefinition, $usermodified = null, $doupdate = false) {
        global $DB;

                if ($this->definition === false) {
            if (!$doupdate) {
                                return 5;
            }
                                    parent::update_definition(new stdClass(), $usermodified);
            parent::load_definition();
        }
        if (!isset($newdefinition->rubric['options'])) {
            $newdefinition->rubric['options'] = self::get_default_options();
        }
        $newdefinition->options = json_encode($newdefinition->rubric['options']);
        $editoroptions = self::description_form_field_options($this->get_context());
        $newdefinition = file_postupdate_standard_editor($newdefinition, 'description', $editoroptions, $this->get_context(),
            'grading', 'description', $this->definition->id);

                $currentdefinition = $this->get_definition(true);

                $haschanges = array();
        if (empty($newdefinition->rubric['criteria'])) {
            $newcriteria = array();
        } else {
            $newcriteria = $newdefinition->rubric['criteria'];         }
        $currentcriteria = $currentdefinition->rubric_criteria;
        $criteriafields = array('sortorder', 'description', 'descriptionformat');
        $levelfields = array('score', 'definition', 'definitionformat');
        foreach ($newcriteria as $id => $criterion) {
                        $levelsdata = array();
            if (array_key_exists('levels', $criterion)) {
                $levelsdata = $criterion['levels'];
            }
            $criterionmaxscore = null;
            if (preg_match('/^NEWID\d+$/', $id)) {
                                $data = array('definitionid' => $this->definition->id, 'descriptionformat' => FORMAT_MOODLE);                 foreach ($criteriafields as $key) {
                    if (array_key_exists($key, $criterion)) {
                        $data[$key] = $criterion[$key];
                    }
                }
                if ($doupdate) {
                    $id = $DB->insert_record('gradingform_rubric_criteria', $data);
                }
                $haschanges[5] = true;
            } else {
                                $data = array();
                foreach ($criteriafields as $key) {
                    if (array_key_exists($key, $criterion) && $criterion[$key] != $currentcriteria[$id][$key]) {
                        $data[$key] = $criterion[$key];
                    }
                }
                if (!empty($data)) {
                                        $data['id'] = $id;
                    if ($doupdate) {
                        $DB->update_record('gradingform_rubric_criteria', $data);
                    }
                    $haschanges[1] = true;
                }
                                foreach ($currentcriteria[$id]['levels'] as $levelid => $currentlevel) {
                    if ($criterionmaxscore === null || $criterionmaxscore < $currentlevel['score']) {
                        $criterionmaxscore = $currentlevel['score'];
                    }
                    if (!array_key_exists($levelid, $levelsdata)) {
                        if ($doupdate) {
                            $DB->delete_records('gradingform_rubric_levels', array('id' => $levelid));
                        }
                        $haschanges[4] = true;
                    }
                }
            }
            foreach ($levelsdata as $levelid => $level) {
                if (isset($level['score'])) {
                    $level['score'] = (float)$level['score'];
                    if ($level['score']<0) {
                        $level['score'] = 0;
                    }
                }
                if (preg_match('/^NEWID\d+$/', $levelid)) {
                                        $data = array('criterionid' => $id, 'definitionformat' => FORMAT_MOODLE);                     foreach ($levelfields as $key) {
                        if (array_key_exists($key, $level)) {
                            $data[$key] = $level[$key];
                        }
                    }
                    if ($doupdate) {
                        $levelid = $DB->insert_record('gradingform_rubric_levels', $data);
                    }
                    if ($criterionmaxscore !== null && $criterionmaxscore >= $level['score']) {
                                                $haschanges[2] = true;
                    } else {
                        $haschanges[3] = true;
                    }
                } else {
                                        $data = array();
                    foreach ($levelfields as $key) {
                        if (array_key_exists($key, $level) && $level[$key] != $currentcriteria[$id]['levels'][$levelid][$key]) {
                            $data[$key] = $level[$key];
                        }
                    }
                    if (!empty($data)) {
                                                $data['id'] = $levelid;
                        if ($doupdate) {
                            $DB->update_record('gradingform_rubric_levels', $data);
                        }
                        if (isset($data['score'])) {
                            $haschanges[3] = true;
                        }
                        $haschanges[1] = true;
                    }
                }
            }
        }
                foreach (array_keys($currentcriteria) as $id) {
            if (!array_key_exists($id, $newcriteria)) {
                if ($doupdate) {
                    $DB->delete_records('gradingform_rubric_criteria', array('id' => $id));
                    $DB->delete_records('gradingform_rubric_levels', array('criterionid' => $id));
                }
                $haschanges[3] = true;
            }
        }
        foreach (array('status', 'description', 'descriptionformat', 'name', 'options') as $key) {
            if (isset($newdefinition->$key) && $newdefinition->$key != $this->definition->$key) {
                $haschanges[1] = true;
            }
        }
        if ($usermodified && $usermodified != $this->definition->usermodified) {
            $haschanges[1] = true;
        }
        if (!count($haschanges)) {
            return 0;
        }
        if ($doupdate) {
            parent::update_definition($newdefinition, $usermodified);
            $this->load_definition();
        }
                $changelevels = array_keys($haschanges);
        sort($changelevels);
        return array_pop($changelevels);
    }

    
    public function mark_for_regrade() {
        global $DB;
        if ($this->has_active_instances()) {
            $conditions = array('definitionid'  => $this->definition->id,
                        'status'  => gradingform_instance::INSTANCE_STATUS_ACTIVE);
            $DB->set_field('grading_instances', 'status', gradingform_instance::INSTANCE_STATUS_NEEDUPDATE, $conditions);
        }
    }

    
    protected function load_definition() {
        global $DB;
        $sql = "SELECT gd.*,
                       rc.id AS rcid, rc.sortorder AS rcsortorder, rc.description AS rcdescription, rc.descriptionformat AS rcdescriptionformat,
                       rl.id AS rlid, rl.score AS rlscore, rl.definition AS rldefinition, rl.definitionformat AS rldefinitionformat
                  FROM {grading_definitions} gd
             LEFT JOIN {gradingform_rubric_criteria} rc ON (rc.definitionid = gd.id)
             LEFT JOIN {gradingform_rubric_levels} rl ON (rl.criterionid = rc.id)
                 WHERE gd.areaid = :areaid AND gd.method = :method
              ORDER BY rc.sortorder,rl.score";
        $params = array('areaid' => $this->areaid, 'method' => $this->get_method_name());

        $rs = $DB->get_recordset_sql($sql, $params);
        $this->definition = false;
        foreach ($rs as $record) {
                        if ($this->definition === false) {
                $this->definition = new stdClass();
                foreach (array('id', 'name', 'description', 'descriptionformat', 'status', 'copiedfromid',
                        'timecreated', 'usercreated', 'timemodified', 'usermodified', 'timecopied', 'options') as $fieldname) {
                    $this->definition->$fieldname = $record->$fieldname;
                }
                $this->definition->rubric_criteria = array();
            }
                        if (!empty($record->rcid) and empty($this->definition->rubric_criteria[$record->rcid])) {
                foreach (array('id', 'sortorder', 'description', 'descriptionformat') as $fieldname) {
                    $this->definition->rubric_criteria[$record->rcid][$fieldname] = $record->{'rc'.$fieldname};
                }
                $this->definition->rubric_criteria[$record->rcid]['levels'] = array();
            }
                        if (!empty($record->rlid)) {
                foreach (array('id', 'score', 'definition', 'definitionformat') as $fieldname) {
                    $value = $record->{'rl'.$fieldname};
                    if ($fieldname == 'score') {
                        $value = (float)$value;                     }
                    $this->definition->rubric_criteria[$record->rcid]['levels'][$record->rlid][$fieldname] = $value;
                }
            }
        }
        $rs->close();
        $options = $this->get_options();
        if (!$options['sortlevelsasc']) {
            foreach (array_keys($this->definition->rubric_criteria) as $rcid) {
                $this->definition->rubric_criteria[$rcid]['levels'] = array_reverse($this->definition->rubric_criteria[$rcid]['levels'], true);
            }
        }
    }

    
    public static function get_default_options() {
        $options = array(
            'sortlevelsasc' => 1,
            'alwaysshowdefinition' => 1,
            'showdescriptionteacher' => 1,
            'showdescriptionstudent' => 1,
            'showscoreteacher' => 1,
            'showscorestudent' => 1,
            'enableremarks' => 1,
            'showremarksstudent' => 1
        );
        return $options;
    }

    
    public function get_options() {
        $options = self::get_default_options();
        if (!empty($this->definition->options)) {
            $thisoptions = json_decode($this->definition->options);
            foreach ($thisoptions as $option => $value) {
                $options[$option] = $value;
            }
        }
        return $options;
    }

    
    public function get_definition_for_editing($addemptycriterion = false) {

        $definition = $this->get_definition();
        $properties = new stdClass();
        $properties->areaid = $this->areaid;
        if ($definition) {
            foreach (array('id', 'name', 'description', 'descriptionformat', 'status') as $key) {
                $properties->$key = $definition->$key;
            }
            $options = self::description_form_field_options($this->get_context());
            $properties = file_prepare_standard_editor($properties, 'description', $options, $this->get_context(),
                'grading', 'description', $definition->id);
        }
        $properties->rubric = array('criteria' => array(), 'options' => $this->get_options());
        if (!empty($definition->rubric_criteria)) {
            $properties->rubric['criteria'] = $definition->rubric_criteria;
        } else if (!$definition && $addemptycriterion) {
            $properties->rubric['criteria'] = array('addcriterion' => 1);
        }

        return $properties;
    }

    
    public function get_definition_copy(gradingform_controller $target) {

        $new = parent::get_definition_copy($target);
        $old = $this->get_definition_for_editing();
        $new->description_editor = $old->description_editor;
        $new->rubric = array('criteria' => array(), 'options' => $old->rubric['options']);
        $newcritid = 1;
        $newlevid = 1;
        foreach ($old->rubric['criteria'] as $oldcritid => $oldcrit) {
            unset($oldcrit['id']);
            if (isset($oldcrit['levels'])) {
                foreach ($oldcrit['levels'] as $oldlevid => $oldlev) {
                    unset($oldlev['id']);
                    $oldcrit['levels']['NEWID'.$newlevid] = $oldlev;
                    unset($oldcrit['levels'][$oldlevid]);
                    $newlevid++;
                }
            } else {
                $oldcrit['levels'] = array();
            }
            $new->rubric['criteria']['NEWID'.$newcritid] = $oldcrit;
            $newcritid++;
        }

        return $new;
    }

    
    public static function description_form_field_options($context) {
        global $CFG;
        return array(
            'maxfiles' => -1,
            'maxbytes' => get_user_max_upload_file_size($context, $CFG->maxbytes),
            'context'  => $context,
        );
    }

    
    public function get_formatted_description() {
        if (!isset($this->definition->description)) {
            return '';
        }
        $context = $this->get_context();

        $options = self::description_form_field_options($this->get_context());
        $description = file_rewrite_pluginfile_urls($this->definition->description, 'pluginfile.php', $context->id,
            'grading', 'description', $this->definition->id, $options);

        $formatoptions = array(
            'noclean' => false,
            'trusted' => false,
            'filter' => true,
            'context' => $context
        );
        return format_text($description, $this->definition->descriptionformat, $formatoptions);
    }

    
    public function get_renderer(moodle_page $page) {
        return $page->get_renderer('gradingform_'. $this->get_method_name());
    }

    
    public function render_preview(moodle_page $page) {

        if (!$this->is_form_defined()) {
            throw new coding_exception('It is the caller\'s responsibility to make sure that the form is actually defined');
        }

        $criteria = $this->definition->rubric_criteria;
        $options = $this->get_options();
        $rubric = '';
        if (has_capability('moodle/grade:managegradingforms', $page->context)) {
            $showdescription = true;
        } else {
            if (empty($options['alwaysshowdefinition']))  {
                                return '';
            }
            $showdescription = $options['showdescriptionstudent'];
        }
        $output = $this->get_renderer($page);
        if ($showdescription) {
            $rubric .= $output->box($this->get_formatted_description(), 'gradingform_rubric-description');
        }
        if (has_capability('moodle/grade:managegradingforms', $page->context)) {
            $rubric .= $output->display_rubric_mapping_explained($this->get_min_max_score());
            $rubric .= $output->display_rubric($criteria, $options, self::DISPLAY_PREVIEW, 'rubric');
        } else {
            $rubric .= $output->display_rubric($criteria, $options, self::DISPLAY_PREVIEW_GRADED, 'rubric');
        }

        return $rubric;
    }

    
    protected function delete_plugin_definition() {
        global $DB;

                $instances = array_keys($DB->get_records('grading_instances', array('definitionid' => $this->definition->id), '', 'id'));
                $DB->delete_records_list('gradingform_rubric_fillings', 'instanceid', $instances);
                $DB->delete_records_list('grading_instances', 'id', $instances);
                $criteria = array_keys($DB->get_records('gradingform_rubric_criteria', array('definitionid' => $this->definition->id), '', 'id'));
                $DB->delete_records_list('gradingform_rubric_levels', 'criterionid', $criteria);
                $DB->delete_records_list('gradingform_rubric_criteria', 'id', $criteria);
    }

    
    public function get_or_create_instance($instanceid, $raterid, $itemid) {
        global $DB;
        if ($instanceid &&
                $instance = $DB->get_record('grading_instances', array('id'  => $instanceid, 'raterid' => $raterid, 'itemid' => $itemid), '*', IGNORE_MISSING)) {
            return $this->get_instance($instance);
        }
        if ($itemid && $raterid) {
            $params = array('definitionid' => $this->definition->id, 'raterid' => $raterid, 'itemid' => $itemid);
            if ($rs = $DB->get_records('grading_instances', $params, 'timemodified DESC', '*', 0, 1)) {
                $record = reset($rs);
                $currentinstance = $this->get_current_instance($raterid, $itemid);
                if ($record->status == gradingform_rubric_instance::INSTANCE_STATUS_INCOMPLETE &&
                        (!$currentinstance || $record->timemodified > $currentinstance->get_data('timemodified'))) {
                    $record->isrestored = true;
                    return $this->get_instance($record);
                }
            }
        }
        return $this->create_instance($raterid, $itemid);
    }

    
    public function render_grade($page, $itemid, $gradinginfo, $defaultcontent, $cangrade) {
        return $this->get_renderer($page)->display_instances($this->get_active_instances($itemid), $defaultcontent, $cangrade);
    }

    
    
    public static function sql_search_from_tables($gdid) {
        return " LEFT JOIN {gradingform_rubric_criteria} rc ON (rc.definitionid = $gdid)
                 LEFT JOIN {gradingform_rubric_levels} rl ON (rl.criterionid = rc.id)";
    }

    
    public static function sql_search_where($token) {
        global $DB;

        $subsql = array();
        $params = array();

                $subsql[] = $DB->sql_like('rc.description', '?', false, false);
        $params[] = '%'.$DB->sql_like_escape($token).'%';

                $subsql[] = $DB->sql_like('rl.definition', '?', false, false);
        $params[] = '%'.$DB->sql_like_escape($token).'%';

        return array($subsql, $params);
    }

    
    public function get_min_max_score() {
        if (!$this->is_form_available()) {
            return null;
        }
        $returnvalue = array('minscore' => 0, 'maxscore' => 0);
        foreach ($this->get_definition()->rubric_criteria as $id => $criterion) {
            $scores = array();
            foreach ($criterion['levels'] as $level) {
                $scores[] = $level['score'];
            }
            sort($scores);
            $returnvalue['minscore'] += $scores[0];
            $returnvalue['maxscore'] += $scores[sizeof($scores)-1];
        }
        return $returnvalue;
    }

    
    public static function get_external_definition_details() {
        $rubric_criteria = new external_multiple_structure(
            new external_single_structure(
                array(
                   'id'   => new external_value(PARAM_INT, 'criterion id', VALUE_OPTIONAL),
                   'sortorder' => new external_value(PARAM_INT, 'sortorder', VALUE_OPTIONAL),
                   'description' => new external_value(PARAM_RAW, 'description', VALUE_OPTIONAL),
                   'descriptionformat' => new external_format_value('description', VALUE_OPTIONAL),
                   'levels' => new external_multiple_structure(
                                   new external_single_structure(
                                       array(
                                        'id' => new external_value(PARAM_INT, 'level id', VALUE_OPTIONAL),
                                        'score' => new external_value(PARAM_FLOAT, 'score', VALUE_OPTIONAL),
                                        'definition' => new external_value(PARAM_RAW, 'definition', VALUE_OPTIONAL),
                                        'definitionformat' => new external_format_value('definition', VALUE_OPTIONAL)
                                       )
                                  ), 'levels', VALUE_OPTIONAL
                              )
                   )
              ), 'definition details', VALUE_OPTIONAL
        );
        return array('rubric_criteria' => $rubric_criteria);
    }

    
    public static function get_external_instance_filling_details() {
        $criteria = new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'filling id'),
                    'criterionid' => new external_value(PARAM_INT, 'criterion id'),
                    'levelid' => new external_value(PARAM_INT, 'level id', VALUE_OPTIONAL),
                    'remark' => new external_value(PARAM_RAW, 'remark', VALUE_OPTIONAL),
                    'remarkformat' => new external_format_value('remark', VALUE_OPTIONAL)
                )
            ), 'filling', VALUE_OPTIONAL
        );
        return array ('criteria' => $criteria);
    }

}


class gradingform_rubric_instance extends gradingform_instance {

    
    protected $rubric;

    
    public function cancel() {
        global $DB;
        parent::cancel();
        $DB->delete_records('gradingform_rubric_fillings', array('instanceid' => $this->get_id()));
    }

    
    public function copy($raterid, $itemid) {
        global $DB;
        $instanceid = parent::copy($raterid, $itemid);
        $currentgrade = $this->get_rubric_filling();
        foreach ($currentgrade['criteria'] as $criterionid => $record) {
            $params = array('instanceid' => $instanceid, 'criterionid' => $criterionid,
                'levelid' => $record['levelid'], 'remark' => $record['remark'], 'remarkformat' => $record['remarkformat']);
            $DB->insert_record('gradingform_rubric_fillings', $params);
        }
        return $instanceid;
    }

    
    public function is_empty_form($elementvalue) {
        $criteria = $this->get_controller()->get_definition()->rubric_criteria;

        foreach ($criteria as $id => $criterion) {
            if (isset($elementvalue['criteria'][$id]['levelid'])
                    || !empty($elementvalue['criteria'][$id]['remark'])) {
                return false;
            }
        }
        return true;
    }

    
    public function clear_attempt($data) {
        global $DB;

        foreach ($data['criteria'] as $criterionid => $record) {
            $DB->delete_records('gradingform_rubric_fillings',
                array('criterionid' => $criterionid, 'instanceid' => $this->get_id()));
        }
    }

    
    public function validate_grading_element($elementvalue) {
        $criteria = $this->get_controller()->get_definition()->rubric_criteria;
        if (!isset($elementvalue['criteria']) || !is_array($elementvalue['criteria']) || sizeof($elementvalue['criteria']) < sizeof($criteria)) {
            return false;
        }
        foreach ($criteria as $id => $criterion) {
            if (!isset($elementvalue['criteria'][$id]['levelid'])
                    || !array_key_exists($elementvalue['criteria'][$id]['levelid'], $criterion['levels'])) {
                return false;
            }
        }
        return true;
    }

    
    public function get_rubric_filling($force = false) {
        global $DB;
        if ($this->rubric === null || $force) {
            $records = $DB->get_records('gradingform_rubric_fillings', array('instanceid' => $this->get_id()));
            $this->rubric = array('criteria' => array());
            foreach ($records as $record) {
                $this->rubric['criteria'][$record->criterionid] = (array)$record;
            }
        }
        return $this->rubric;
    }

    
    public function update($data) {
        global $DB;
        $currentgrade = $this->get_rubric_filling();
        parent::update($data);
        foreach ($data['criteria'] as $criterionid => $record) {
            if (!array_key_exists($criterionid, $currentgrade['criteria'])) {
                $newrecord = array('instanceid' => $this->get_id(), 'criterionid' => $criterionid,
                    'levelid' => $record['levelid'], 'remarkformat' => FORMAT_MOODLE);
                if (isset($record['remark'])) {
                    $newrecord['remark'] = $record['remark'];
                }
                $DB->insert_record('gradingform_rubric_fillings', $newrecord);
            } else {
                $newrecord = array('id' => $currentgrade['criteria'][$criterionid]['id']);
                foreach (array('levelid', 'remark') as $key) {
                                        if (isset($record[$key]) && $currentgrade['criteria'][$criterionid][$key] != $record[$key]) {
                        $newrecord[$key] = $record[$key];
                    }
                }
                if (count($newrecord) > 1) {
                    $DB->update_record('gradingform_rubric_fillings', $newrecord);
                }
            }
        }
        foreach ($currentgrade['criteria'] as $criterionid => $record) {
            if (!array_key_exists($criterionid, $data['criteria'])) {
                $DB->delete_records('gradingform_rubric_fillings', array('id' => $record['id']));
            }
        }
        $this->get_rubric_filling(true);
    }

    
    public function get_grade() {
        $grade = $this->get_rubric_filling();

        if (!($scores = $this->get_controller()->get_min_max_score()) || $scores['maxscore'] <= $scores['minscore']) {
            return -1;
        }

        $graderange = array_keys($this->get_controller()->get_grade_range());
        if (empty($graderange)) {
            return -1;
        }
        sort($graderange);
        $mingrade = $graderange[0];
        $maxgrade = $graderange[sizeof($graderange) - 1];

        $curscore = 0;
        foreach ($grade['criteria'] as $id => $record) {
            $curscore += $this->get_controller()->get_definition()->rubric_criteria[$id]['levels'][$record['levelid']]['score'];
        }
        $gradeoffset = ($curscore-$scores['minscore'])/($scores['maxscore']-$scores['minscore'])*($maxgrade-$mingrade);
        if ($this->get_controller()->get_allow_grade_decimals()) {
            return $gradeoffset + $mingrade;
        }
        return round($gradeoffset, 0) + $mingrade;
    }

    
    public function render_grading_element($page, $gradingformelement) {
        global $USER;
        if (!$gradingformelement->_flagFrozen) {
            $module = array('name'=>'gradingform_rubric', 'fullpath'=>'/grade/grading/form/rubric/js/rubric.js');
            $page->requires->js_init_call('M.gradingform_rubric.init', array(array('name' => $gradingformelement->getName())), true, $module);
            $mode = gradingform_rubric_controller::DISPLAY_EVAL;
        } else {
            if ($gradingformelement->_persistantFreeze) {
                $mode = gradingform_rubric_controller::DISPLAY_EVAL_FROZEN;
            } else {
                $mode = gradingform_rubric_controller::DISPLAY_REVIEW;
            }
        }
        $criteria = $this->get_controller()->get_definition()->rubric_criteria;
        $options = $this->get_controller()->get_options();
        $value = $gradingformelement->getValue();
        $html = '';
        if ($value === null) {
            $value = $this->get_rubric_filling();
        } else if (!$this->validate_grading_element($value)) {
            $html .= html_writer::tag('div', get_string('rubricnotcompleted', 'gradingform_rubric'), array('class' => 'gradingform_rubric-error'));
        }
        $currentinstance = $this->get_current_instance();
        if ($currentinstance && $currentinstance->get_status() == gradingform_instance::INSTANCE_STATUS_NEEDUPDATE) {
            $html .= html_writer::div(get_string('needregrademessage', 'gradingform_rubric'), 'gradingform_rubric-regrade',
                                      array('role' => 'alert'));
        }
        $haschanges = false;
        if ($currentinstance) {
            $curfilling = $currentinstance->get_rubric_filling();
            foreach ($curfilling['criteria'] as $criterionid => $curvalues) {
                $value['criteria'][$criterionid]['savedlevelid'] = $curvalues['levelid'];
                $newremark = null;
                $newlevelid = null;
                if (isset($value['criteria'][$criterionid]['remark'])) $newremark = $value['criteria'][$criterionid]['remark'];
                if (isset($value['criteria'][$criterionid]['levelid'])) $newlevelid = $value['criteria'][$criterionid]['levelid'];
                if ($newlevelid != $curvalues['levelid'] || $newremark != $curvalues['remark']) {
                    $haschanges = true;
                }
            }
        }
        if ($this->get_data('isrestored') && $haschanges) {
            $html .= html_writer::tag('div', get_string('restoredfromdraft', 'gradingform_rubric'), array('class' => 'gradingform_rubric-restored'));
        }
        if (!empty($options['showdescriptionteacher'])) {
            $html .= html_writer::tag('div', $this->get_controller()->get_formatted_description(), array('class' => 'gradingform_rubric-description'));
        }
        $html .= $this->get_controller()->get_renderer($page)->display_rubric($criteria, $options, $mode, $gradingformelement->getName(), $value);
        return $html;
    }
}
