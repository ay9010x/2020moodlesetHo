<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/grade/grading/form/lib.php');


class gradingform_guide_controller extends gradingform_controller {
        
    const DISPLAY_EDIT_FULL     = 1;
    
    const DISPLAY_EDIT_FROZEN   = 2;
    
    const DISPLAY_PREVIEW       = 3;
    
    const DISPLAY_PREVIEW_GRADED= 8;
    
    const DISPLAY_EVAL          = 4;
    
    const DISPLAY_EVAL_FROZEN   = 5;
    
    const DISPLAY_REVIEW        = 6;
    
    const DISPLAY_VIEW          = 7;

    
    protected $moduleinstance = false;

    
    public function extend_settings_navigation(settings_navigation $settingsnav, navigation_node $node=null) {
        $node->add(get_string('definemarkingguide', 'gradingform_guide'),
            $this->get_editor_url(), settings_navigation::TYPE_CUSTOM,
            null, null, new pix_icon('icon', '', 'gradingform_guide'));
    }

    
    public function extend_navigation(global_navigation $navigation, navigation_node $node=null) {
        if (has_capability('moodle/grade:managegradingforms', $this->get_context())) {
                        return;
        }
        if ($this->is_form_defined() && ($options = $this->get_options()) && !empty($options['alwaysshowdefinition'])) {
            $node->add(get_string('gradingof', 'gradingform_guide', get_grading_manager($this->get_areaid())->get_area_title()),
                    new moodle_url('/grade/grading/form/'.$this->get_method_name().'/preview.php',
                        array('areaid' => $this->get_areaid())), settings_navigation::TYPE_CUSTOM);
        }
    }

    
    public function update_definition(stdClass $newdefinition, $usermodified = null) {
        $this->update_or_check_guide($newdefinition, $usermodified, true);
        if (isset($newdefinition->guide['regrade']) && $newdefinition->guide['regrade']) {
            $this->mark_for_regrade();
        }
    }

    
    public function update_or_check_guide(stdClass $newdefinition, $usermodified = null, $doupdate = false) {
        global $DB;

                if ($this->definition === false) {
            if (!$doupdate) {
                                return 5;
            }
                                    parent::update_definition(new stdClass(), $usermodified);
            parent::load_definition();
        }
        if (!isset($newdefinition->guide['options'])) {
            $newdefinition->guide['options'] = self::get_default_options();
        }
        $newdefinition->options = json_encode($newdefinition->guide['options']);
        $editoroptions = self::description_form_field_options($this->get_context());
        $newdefinition = file_postupdate_standard_editor($newdefinition, 'description', $editoroptions, $this->get_context(),
            'grading', 'description', $this->definition->id);

                $currentdefinition = $this->get_definition(true);

                $haschanges = array();
        if (empty($newdefinition->guide['criteria'])) {
            $newcriteria = array();
        } else {
            $newcriteria = $newdefinition->guide['criteria'];         }
        $currentcriteria = $currentdefinition->guide_criteria;
        $criteriafields = array('sortorder', 'description', 'descriptionformat', 'descriptionmarkers',
            'descriptionmarkersformat', 'shortname', 'maxscore');
        foreach ($newcriteria as $id => $criterion) {
            if (preg_match('/^NEWID\d+$/', $id)) {
                                $data = array('definitionid' => $this->definition->id, 'descriptionformat' => FORMAT_MOODLE,
                    'descriptionmarkersformat' => FORMAT_MOODLE);                 foreach ($criteriafields as $key) {
                    if (array_key_exists($key, $criterion)) {
                        $data[$key] = $criterion[$key];
                    }
                }
                if ($doupdate) {
                    $id = $DB->insert_record('gradingform_guide_criteria', $data);
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
                        $DB->update_record('gradingform_guide_criteria', $data);
                    }
                    $haschanges[1] = true;
                }
            }
        }
                foreach (array_keys($currentcriteria) as $id) {
            if (!array_key_exists($id, $newcriteria)) {
                if ($doupdate) {
                    $DB->delete_records('gradingform_guide_criteria', array('id' => $id));
                }
                $haschanges[3] = true;
            }
        }
                if (empty($newdefinition->guide['comments'])) {
            $newcomment = array();
        } else {
            $newcomment = $newdefinition->guide['comments'];         }
        $currentcomments = $currentdefinition->guide_comments;
        $commentfields = array('sortorder', 'description');
        foreach ($newcomment as $id => $comment) {
            if (preg_match('/^NEWID\d+$/', $id)) {
                                $data = array('definitionid' => $this->definition->id, 'descriptionformat' => FORMAT_MOODLE);
                foreach ($commentfields as $key) {
                    if (array_key_exists($key, $comment)) {
                                                if ($key === 'description') {
                                                        $description = trim($comment[$key]);
                                                        if (empty($description)) {
                                                                continue 2;
                            }
                        }
                        $data[$key] = $comment[$key];
                    }
                }
                if ($doupdate) {
                    $id = $DB->insert_record('gradingform_guide_comments', $data);
                }
            } else {
                                $data = array();
                foreach ($commentfields as $key) {
                    if (array_key_exists($key, $comment) && $comment[$key] != $currentcomments[$id][$key]) {
                        $data[$key] = $comment[$key];
                    }
                }
                if (!empty($data)) {
                                        $data['id'] = $id;
                    if ($doupdate) {
                        $DB->update_record('gradingform_guide_comments', $data);
                    }
                }
            }
        }
                foreach (array_keys($currentcomments) as $id) {
            if (!array_key_exists($id, $newcomment)) {
                if ($doupdate) {
                    $DB->delete_records('gradingform_guide_comments', array('id' => $id));
                }
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

                        $showdesc = optional_param('showmarkerdesc', null, PARAM_BOOL);         $showdescstudent = optional_param('showstudentdesc', null, PARAM_BOOL);         if ($showdesc !== null) {
            set_user_preference('gradingform_guide-showmarkerdesc', $showdesc);
        }
        if ($showdescstudent !== null) {
            set_user_preference('gradingform_guide-showstudentdesc', $showdescstudent);
        }

                $definition = $DB->get_record('grading_definitions', array('areaid' => $this->areaid,
            'method' => $this->get_method_name()), '*');
        if (!$definition) {
                        $this->definition = false;
            return false;
        }

        $this->definition = $definition;
                $this->definition->guide_criteria = array();
        $this->definition->guide_comments = array();
        $criteria = $DB->get_recordset('gradingform_guide_criteria', array('definitionid' => $this->definition->id), 'sortorder');
        foreach ($criteria as $criterion) {
            foreach (array('id', 'sortorder', 'description', 'descriptionformat',
                           'maxscore', 'descriptionmarkers', 'descriptionmarkersformat', 'shortname') as $fieldname) {
                if ($fieldname == 'maxscore') {                      $this->definition->guide_criteria[$criterion->id][$fieldname] = (float)$criterion->{$fieldname};
                } else {
                    $this->definition->guide_criteria[$criterion->id][$fieldname] = $criterion->{$fieldname};
                }
            }
        }
        $criteria->close();

                $comments = $DB->get_recordset('gradingform_guide_comments', array('definitionid' => $this->definition->id), 'sortorder');
        foreach ($comments as $comment) {
            foreach (array('id', 'sortorder', 'description', 'descriptionformat') as $fieldname) {
                $this->definition->guide_comments[$comment->id][$fieldname] = $comment->{$fieldname};
            }
        }
        $comments->close();
        if (empty($this->moduleinstance)) {             $modulename = $this->get_component();
            $context = $this->get_context();
            if (strpos($modulename, 'mod_') === 0) {
                $dbman = $DB->get_manager();
                $modulename = substr($modulename, 4);
                if ($dbman->table_exists($modulename)) {
                    $cm = get_coursemodule_from_id($modulename, $context->instanceid);
                    if (!empty($cm)) {                         $this->moduleinstance = $DB->get_record($modulename, array("id"=>$cm->instance));
                    }
                }
            }
        }
    }

    
    public static function get_default_options() {
        $options = array(
            'alwaysshowdefinition' => 1,
            'showmarkspercriterionstudents' => 1,
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
        if (isset($this->moduleinstance->grade)) {
            $properties->modulegrade = $this->moduleinstance->grade;
        }
        if ($definition) {
            foreach (array('id', 'name', 'description', 'descriptionformat', 'status') as $key) {
                $properties->$key = $definition->$key;
            }
            $options = self::description_form_field_options($this->get_context());
            $properties = file_prepare_standard_editor($properties, 'description', $options, $this->get_context(),
                'grading', 'description', $definition->id);
        }
        $properties->guide = array('criteria' => array(), 'options' => $this->get_options(), 'comments' => array());
        if (!empty($definition->guide_criteria)) {
            $properties->guide['criteria'] = $definition->guide_criteria;
        } else if (!$definition && $addemptycriterion) {
            $properties->guide['criteria'] = array('addcriterion' => 1);
        }
        if (!empty($definition->guide_comments)) {
            $properties->guide['comments'] = $definition->guide_comments;
        } else if (!$definition && $addemptycriterion) {
            $properties->guide['comments'] = array('addcomment' => 1);
        }
        return $properties;
    }

    
    public function get_definition_copy(gradingform_controller $target) {

        $new = parent::get_definition_copy($target);
        $old = $this->get_definition_for_editing();
        $new->description_editor = $old->description_editor;
        $new->guide = array('criteria' => array(), 'options' => $old->guide['options'], 'comments' => array());
        $newcritid = 1;
        foreach ($old->guide['criteria'] as $oldcritid => $oldcrit) {
            unset($oldcrit['id']);
            $new->guide['criteria']['NEWID'.$newcritid] = $oldcrit;
            $newcritid++;
        }
        $newcomid = 1;
        foreach ($old->guide['comments'] as $oldcritid => $oldcom) {
            unset($oldcom['id']);
            $new->guide['comments']['NEWID'.$newcomid] = $oldcom;
            $newcomid++;
        }
        return $new;
    }

    
    public static function description_form_field_options($context) {
        global $CFG;
        return array(
            'maxfiles' => -1,
            'maxbytes' => get_max_upload_file_size($CFG->maxbytes),
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

                $options = $this->get_options();
        if (empty($options['alwaysshowdefinition']) && !has_capability('moodle/grade:managegradingforms', $page->context))  {
            return '';
        }

        $criteria = $this->definition->guide_criteria;
        $comments = $this->definition->guide_comments;
        $output = $this->get_renderer($page);

        $guide = '';
        $guide .= $output->box($this->get_formatted_description(), 'gradingform_guide-description');
        if (has_capability('moodle/grade:managegradingforms', $page->context)) {
            $guide .= $output->display_guide_mapping_explained($this->get_min_max_score());
            $guide .= $output->display_guide($criteria, $comments, $options, self::DISPLAY_PREVIEW, 'guide');
        } else {
            $guide .= $output->display_guide($criteria, $comments, $options, self::DISPLAY_PREVIEW_GRADED, 'guide');
        }

        return $guide;
    }

    
    protected function delete_plugin_definition() {
        global $DB;

                $instances = array_keys($DB->get_records('grading_instances', array('definitionid' => $this->definition->id), '', 'id'));
                $DB->delete_records_list('gradingform_guide_fillings', 'instanceid', $instances);
                $DB->delete_records_list('grading_instances', 'id', $instances);
                $criteria = array_keys($DB->get_records('gradingform_guide_criteria',
            array('definitionid' => $this->definition->id), '', 'id'));
                $DB->delete_records_list('gradingform_guide_criteria', 'id', $criteria);
                $DB->delete_records('gradingform_guide_comments', array('definitionid' => $this->definition->id));
    }

    
    public function get_or_create_instance($instanceid, $raterid, $itemid) {
        global $DB;
        if ($instanceid &&
                $instance = $DB->get_record('grading_instances',
                    array('id'  => $instanceid, 'raterid' => $raterid, 'itemid' => $itemid), '*', IGNORE_MISSING)) {
            return $this->get_instance($instance);
        }
        if ($itemid && $raterid) {
            $params = array('definitionid' => $this->definition->id, 'raterid' => $raterid, 'itemid' => $itemid);
            if ($rs = $DB->get_records('grading_instances', $params, 'timemodified DESC', '*', 0, 1)) {
                $record = reset($rs);
                $currentinstance = $this->get_current_instance($raterid, $itemid);
                if ($record->status == gradingform_guide_instance::INSTANCE_STATUS_INCOMPLETE &&
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
        return " LEFT JOIN {gradingform_guide_criteria} gc ON (gc.definitionid = $gdid)";
    }

    
    public static function sql_search_where($token) {
        global $DB;

        $subsql = array();
        $params = array();

                $subsql[] = $DB->sql_like('gc.description', '?', false, false);
        $params[] = '%'.$DB->sql_like_escape($token).'%';

        return array($subsql, $params);
    }

    
    public function get_min_max_score() {
        if (!$this->is_form_available()) {
            return null;
        }
        $returnvalue = array('minscore' => 0, 'maxscore' => 0);
        $maxscore = 0;
        foreach ($this->get_definition()->guide_criteria as $id => $criterion) {
            $maxscore += $criterion['maxscore'];
        }
        $returnvalue['maxscore'] = $maxscore;
        $returnvalue['minscore'] = 0;
        if (!empty($this->moduleinstance->grade)) {
            $graderange = make_grades_menu($this->moduleinstance->grade);
            $returnvalue['modulegrade'] = count($graderange) - 1;
        }
        return $returnvalue;
    }

    
    public static function get_external_definition_details() {
        $guide_criteria = new external_multiple_structure(
                              new external_single_structure(
                                  array(
                                      'id'   => new external_value(PARAM_INT, 'criterion id', VALUE_OPTIONAL),
                                      'sortorder' => new external_value(PARAM_INT, 'sortorder', VALUE_OPTIONAL),
                                      'description' => new external_value(PARAM_RAW, 'description', VALUE_OPTIONAL),
                                      'descriptionformat' => new external_format_value('description', VALUE_OPTIONAL),
                                      'shortname' => new external_value(PARAM_TEXT, 'description'),
                                      'descriptionmarkers' => new external_value(PARAM_RAW, 'markers description', VALUE_OPTIONAL),
                                      'descriptionmarkersformat' => new external_format_value('descriptionmarkers', VALUE_OPTIONAL),
                                      'maxscore' => new external_value(PARAM_FLOAT, 'maximum score')
                                      )
                                  )
        );
        $guide_comments = new external_multiple_structure(
                              new external_single_structure(
                                  array(
                                      'id'   => new external_value(PARAM_INT, 'criterion id', VALUE_OPTIONAL),
                                      'sortorder' => new external_value(PARAM_INT, 'sortorder', VALUE_OPTIONAL),
                                      'description' => new external_value(PARAM_RAW, 'description', VALUE_OPTIONAL),
                                      'descriptionformat' => new external_format_value('description', VALUE_OPTIONAL)
                                   )
                              ), 'comments', VALUE_OPTIONAL
        );
        return array('guide_criteria' => $guide_criteria, 'guide_comments' => $guide_comments);
    }

    
    public static function get_external_instance_filling_details() {
        $criteria = new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'filling id'),
                    'criterionid' => new external_value(PARAM_INT, 'criterion id'),
                    'levelid' => new external_value(PARAM_INT, 'level id', VALUE_OPTIONAL),
                    'remark' => new external_value(PARAM_RAW, 'remark', VALUE_OPTIONAL),
                    'remarkformat' => new external_format_value('remark', VALUE_OPTIONAL),
                    'score' => new external_value(PARAM_FLOAT, 'maximum score')
                )
            ), 'filling', VALUE_OPTIONAL
        );
        return array ('criteria' => $criteria);
    }

}


class gradingform_guide_instance extends gradingform_instance {

    
    protected $guide;

    
    protected $validationerrors = array();

    
    public function cancel() {
        global $DB;
        parent::cancel();
        $DB->delete_records('gradingform_guide_fillings', array('instanceid' => $this->get_id()));
    }

    
    public function copy($raterid, $itemid) {
        global $DB;
        $instanceid = parent::copy($raterid, $itemid);
        $currentgrade = $this->get_guide_filling();
        foreach ($currentgrade['criteria'] as $criterionid => $record) {
            $params = array('instanceid' => $instanceid, 'criterionid' => $criterionid,
                'score' => $record['score'], 'remark' => $record['remark'], 'remarkformat' => $record['remarkformat']);
            $DB->insert_record('gradingform_guide_fillings', $params);
        }
        return $instanceid;
    }

    
    public function is_empty_form($elementvalue) {
        $criteria = $this->get_controller()->get_definition()->guide_criteria;
        foreach ($criteria as $id => $criterion) {
            $score = $elementvalue['criteria'][$id]['score'];
            $remark = $elementvalue['criteria'][$id]['remark'];

            if ((isset($score) && $score !== '')
                    || ((isset($remark) && $remark !== ''))) {
                return false;
            }
        }
        return true;
    }

    
    public function validate_grading_element($elementvalue) {
        $criteria = $this->get_controller()->get_definition()->guide_criteria;
        if (!isset($elementvalue['criteria']) || !is_array($elementvalue['criteria']) ||
            count($elementvalue['criteria']) < count($criteria)) {
            return false;
        }
                $this->validationerrors = null;
        foreach ($criteria as $id => $criterion) {
            if (!isset($elementvalue['criteria'][$id]['score'])
                    || $criterion['maxscore'] < $elementvalue['criteria'][$id]['score']
                    || !is_numeric($elementvalue['criteria'][$id]['score'])
                    || $elementvalue['criteria'][$id]['score'] < 0) {
                $this->validationerrors[$id]['score'] = $elementvalue['criteria'][$id]['score'];
            }
        }
        if (!empty($this->validationerrors)) {
            return false;
        }
        return true;
    }

    
    public function get_guide_filling($force = false) {
        global $DB;
        if ($this->guide === null || $force) {
            $records = $DB->get_records('gradingform_guide_fillings', array('instanceid' => $this->get_id()));
            $this->guide = array('criteria' => array());
            foreach ($records as $record) {
                $record->score = (float)$record->score;                 $this->guide['criteria'][$record->criterionid] = (array)$record;
            }
        }
        return $this->guide;
    }

    
    public function update($data) {
        global $DB;
        $currentgrade = $this->get_guide_filling();
        parent::update($data);

        foreach ($data['criteria'] as $criterionid => $record) {
            if (!array_key_exists($criterionid, $currentgrade['criteria'])) {
                $newrecord = array('instanceid' => $this->get_id(), 'criterionid' => $criterionid,
                    'score' => $record['score'], 'remarkformat' => FORMAT_MOODLE);
                if (isset($record['remark'])) {
                    $newrecord['remark'] = $record['remark'];
                }
                $DB->insert_record('gradingform_guide_fillings', $newrecord);
            } else {
                $newrecord = array('id' => $currentgrade['criteria'][$criterionid]['id']);
                foreach (array('score', 'remark') as $key) {
                    if (isset($record[$key]) && $currentgrade['criteria'][$criterionid][$key] != $record[$key]) {
                        $newrecord[$key] = $record[$key];
                    }
                }
                if (count($newrecord) > 1) {
                    $DB->update_record('gradingform_guide_fillings', $newrecord);
                }
            }
        }
        foreach ($currentgrade['criteria'] as $criterionid => $record) {
            if (!array_key_exists($criterionid, $data['criteria'])) {
                $DB->delete_records('gradingform_guide_fillings', array('id' => $record['id']));
            }
        }
        $this->get_guide_filling(true);
    }

    
    public function clear_attempt($data) {
        global $DB;

        foreach ($data['criteria'] as $criterionid => $record) {
            $DB->delete_records('gradingform_guide_fillings',
                array('criterionid' => $criterionid, 'instanceid' => $this->get_id()));
        }
    }

    
    public function get_grade() {
        $grade = $this->get_guide_filling();

        if (!($scores = $this->get_controller()->get_min_max_score()) || $scores['maxscore'] <= $scores['minscore']) {
            return -1;
        }

        $graderange = array_keys($this->get_controller()->get_grade_range());
        if (empty($graderange)) {
            return -1;
        }
        sort($graderange);
        $mingrade = $graderange[0];
        $maxgrade = $graderange[count($graderange) - 1];

        $curscore = 0;
        foreach ($grade['criteria'] as $record) {
            $curscore += $record['score'];
        }
        $gradeoffset = ($curscore-$scores['minscore'])/($scores['maxscore']-$scores['minscore'])*
            ($maxgrade-$mingrade);
        if ($this->get_controller()->get_allow_grade_decimals()) {
            return $gradeoffset + $mingrade;
        }
        return round($gradeoffset, 0) + $mingrade;
    }

    
    public function render_grading_element($page, $gradingformelement) {
        if (!$gradingformelement->_flagFrozen) {
            $module = array('name'=>'gradingform_guide', 'fullpath'=>'/grade/grading/form/guide/js/guide.js');
            $page->requires->js_init_call('M.gradingform_guide.init', array(
                array('name' => $gradingformelement->getName())), true, $module);
            $mode = gradingform_guide_controller::DISPLAY_EVAL;
        } else {
            if ($gradingformelement->_persistantFreeze) {
                $mode = gradingform_guide_controller::DISPLAY_EVAL_FROZEN;
            } else {
                $mode = gradingform_guide_controller::DISPLAY_REVIEW;
            }
        }
        $criteria = $this->get_controller()->get_definition()->guide_criteria;
        $comments = $this->get_controller()->get_definition()->guide_comments;
        $options = $this->get_controller()->get_options();
        $value = $gradingformelement->getValue();
        $html = '';
        if ($value === null) {
            $value = $this->get_guide_filling();
        } else if (!$this->validate_grading_element($value)) {
            $html .= html_writer::tag('div', get_string('guidenotcompleted', 'gradingform_guide'),
                array('class' => 'gradingform_guide-error'));
            if (!empty($this->validationerrors)) {
                foreach ($this->validationerrors as $id => $err) {
                    $a = new stdClass();
                    $a->criterianame = s($criteria[$id]['shortname']);
                    $a->maxscore = $criteria[$id]['maxscore'];
                    if ($this->validationerrors[$id]['score'] < 0) {
                        $html .= html_writer::tag('div', get_string('err_scoreisnegative', 'gradingform_guide', $a),
                        array('class' => 'gradingform_guide-error'));
                    } else {
                        $html .= html_writer::tag('div', get_string('err_scoreinvalid', 'gradingform_guide', $a),
                        array('class' => 'gradingform_guide-error'));
                    }
                }
            }
        }
        $currentinstance = $this->get_current_instance();
        if ($currentinstance && $currentinstance->get_status() == gradingform_instance::INSTANCE_STATUS_NEEDUPDATE) {
            $html .= html_writer::tag('div', get_string('needregrademessage', 'gradingform_guide'),
                array('class' => 'gradingform_guide-regrade', 'role' => 'alert'));
        }
        $haschanges = false;
        if ($currentinstance) {
            $curfilling = $currentinstance->get_guide_filling();
            foreach ($curfilling['criteria'] as $criterionid => $curvalues) {
                $value['criteria'][$criterionid]['score'] = $curvalues['score'];
                $newremark = null;
                $newscore = null;
                if (isset($value['criteria'][$criterionid]['remark'])) {
                    $newremark = $value['criteria'][$criterionid]['remark'];
                }
                if (isset($value['criteria'][$criterionid]['score'])) {
                    $newscore = $value['criteria'][$criterionid]['score'];
                }
                if ($newscore != $curvalues['score'] || $newremark != $curvalues['remark']) {
                    $haschanges = true;
                }
            }
        }
        if ($this->get_data('isrestored') && $haschanges) {
            $html .= html_writer::tag('div', get_string('restoredfromdraft', 'gradingform_guide'),
                array('class' => 'gradingform_guide-restored'));
        }
        $html .= html_writer::tag('div', $this->get_controller()->get_formatted_description(),
            array('class' => 'gradingform_guide-description'));
        $html .= $this->get_controller()->get_renderer($page)->display_guide($criteria, $comments, $options, $mode,
            $gradingformelement->getName(), $value, $this->validationerrors);
        return $html;
    }
}
