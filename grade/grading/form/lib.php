<?php



defined('MOODLE_INTERNAL') || die();


abstract class gradingform_controller {

    
    const DEFINITION_STATUS_NULL = 0;
    
    const DEFINITION_STATUS_DRAFT = 10;
    
    const DEFINITION_STATUS_READY = 20;

    
    protected $context;

    
    protected $component;

    
    protected $area;

    
    protected $areaid;

    
    protected $definition = false;

    
    private $graderange = null;

    
    private $allowgradedecimals = false;

    
    protected $hasactiveinstances = null;

    
    public function __construct(stdClass $context, $component, $area, $areaid) {
        global $DB;

        $this->context      = $context;
        list($type, $name)  = core_component::normalize_component($component);
        $this->component    = $type.'_'.$name;
        $this->area         = $area;
        $this->areaid       = $areaid;

        $this->load_definition();
    }

    
    public function get_context() {
        return $this->context;
    }

    
    public function get_component() {
        return $this->component;
    }

    
    public function get_area() {
        return $this->area;
    }

    
    public function get_areaid() {
        return $this->areaid;
    }

    
    public function is_form_defined() {
        return ($this->definition !== false);
    }

    
    public function is_form_available() {
        return ($this->is_form_defined() && $this->definition->status == self::DEFINITION_STATUS_READY);
    }

    
    public function is_shared_template() {
        return ($this->get_context()->id == context_system::instance()->id
            and $this->get_component() == 'core_grading');
    }

    
    public function is_own_form($userid = null) {
        global $USER;

        if (!$this->is_form_defined()) {
            return null;
        }
        if (is_null($userid)) {
            $userid = $USER->id;
        }
        return ($this->definition->usercreated == $userid);
    }

    
    public function form_unavailable_notification() {
        if ($this->is_form_available()) {
            return null;
        }
        return get_string('gradingformunavailable', 'grading');
    }

    
    public function get_editor_url(moodle_url $returnurl = null) {

        $params = array('areaid' => $this->areaid);

        if (!is_null($returnurl)) {
            $params['returnurl'] = $returnurl->out(false);
        }

        return new moodle_url('/grade/grading/form/'.$this->get_method_name().'/edit.php', $params);
    }

    
    public function extend_settings_navigation(settings_navigation $settingsnav, navigation_node $node=null) {
            }

    
    public function extend_navigation(global_navigation $navigation, navigation_node $node=null) {
            }

    
    public function get_definition($force = false) {
        if ($this->definition === false || $force) {
            $this->load_definition();
        }
        return $this->definition;
    }

    
    public function get_definition_copy(gradingform_controller $target) {

        if (get_class($this) != get_class($target)) {
            throw new coding_exception('The source and copy controller mismatch');
        }

        if ($target->is_form_defined()) {
            throw new coding_exception('The target controller already contains a form definition');
        }

        $old = $this->get_definition();
                $new = new stdClass();
        $new->copiedfromid = $old->id;
        $new->name = $old->name;
                        $new->description = $old->description;
        $new->descriptionformat = $old->descriptionformat;
        $new->options = $old->options;
        $new->status = $old->status;

        return $new;
    }

    
    public function update_definition(stdClass $definition, $usermodified = null) {
        global $DB, $USER;

        if (is_null($usermodified)) {
            $usermodified = $USER->id;
        }

        if (!empty($this->definition->id)) {
                        $record = new stdClass();
                        foreach ($definition as $prop => $val) {
                if (is_array($val) or is_object($val)) {
                                        continue;
                }
                $record->{$prop} = $val;
            }
                        if (!empty($record->id) and $record->id != $this->definition->id) {
                throw new coding_exception('Attempting to update other definition record.');
            }
            $record->id = $this->definition->id;
            unset($record->areaid);
            unset($record->method);
            unset($record->timecreated);
                        $record->timemodified = time();
            $record->usermodified = $usermodified;

            $DB->update_record('grading_definitions', $record);

        } else if ($this->definition === false) {
                        $record = new stdClass();
                        foreach ($definition as $prop => $val) {
                if (is_array($val) or is_object($val)) {
                                        continue;
                }
                $record->{$prop} = $val;
            }
                        if (!empty($record->id)) {
                throw new coding_exception('Attempting to create a new record while there is already one existing.');
            }
            unset($record->id);
            $record->areaid       = $this->areaid;
            $record->method       = $this->get_method_name();
            $record->timecreated  = time();
            $record->usercreated  = $usermodified;
            $record->timemodified = $record->timecreated;
            $record->usermodified = $record->usercreated;
            if (empty($record->status)) {
                $record->status = self::DEFINITION_STATUS_DRAFT;
            }
            if (empty($record->descriptionformat)) {
                $record->descriptionformat = FORMAT_MOODLE;             }

            $DB->insert_record('grading_definitions', $record);

        } else {
            throw new coding_exception('Unknown status of the cached definition record.');
        }
    }

    
    public function get_formatted_description() {
        if (!isset($this->definition->description)) {
            return '';
        }
        return format_text($this->definition->description, $this->definition->descriptionformat);
    }

    
    public function get_current_instance($raterid, $itemid, $idonly = false) {
        global $DB;
        $params = array(
                'definitionid'  => $this->definition->id,
                'itemid' => $itemid,
                'status1'  => gradingform_instance::INSTANCE_STATUS_ACTIVE,
                'status2'  => gradingform_instance::INSTANCE_STATUS_NEEDUPDATE);
        $select = 'definitionid=:definitionid and itemid=:itemid and (status=:status1 or status=:status2)';
        if (false) {
                        $select .= ' and raterid=:raterid';
            $params['raterid'] = $raterid;
        }
        if ($idonly) {
            if ($current = $DB->get_record_select('grading_instances', $select, $params, 'id', IGNORE_MISSING)) {
                return $current->id;
            }
        } else {
            if ($current = $DB->get_record_select('grading_instances', $select, $params, '*', IGNORE_MISSING)) {
                return $this->get_instance($current);
            }
        }
        return null;
    }

    
    public function get_active_instances($itemid) {
        global $DB;
        $conditions = array('definitionid'  => $this->definition->id,
                    'itemid' => $itemid,
                    'status'  => gradingform_instance::INSTANCE_STATUS_ACTIVE);
        $records = $DB->get_recordset('grading_instances', $conditions);
        $rv = array();
        foreach ($records as $record) {
            $rv[] = $this->get_instance($record);
        }
        return $rv;
    }

    
    public function get_all_active_instances($since = 0) {
        global $DB;
        $conditions = array ($this->definition->id,
                             gradingform_instance::INSTANCE_STATUS_ACTIVE,
                             $since);
        $where = "definitionid = ? AND status = ? AND timemodified >= ?";
        $records = $DB->get_records_select('grading_instances', $where, $conditions);
        $rv = array();
        foreach ($records as $record) {
            $rv[] = $this->get_instance($record);
        }
        return $rv;
    }

    
    public function has_active_instances() {
        global $DB;
        if (empty($this->definition->id)) {
            return false;
        }
        if ($this->hasactiveinstances === null) {
            $conditions = array('definitionid'  => $this->definition->id,
                        'status'  => gradingform_instance::INSTANCE_STATUS_ACTIVE);
            $this->hasactiveinstances = $DB->record_exists('grading_instances', $conditions);
        }
        return $this->hasactiveinstances;
    }

    
    protected function get_instance($instance) {
        global $DB;
        if (is_scalar($instance)) {
                        $instance = $DB->get_record('grading_instances', array('id'  => $instance), '*', MUST_EXIST);
        }
        if ($instance) {
            $class = 'gradingform_'. $this->get_method_name(). '_instance';
            return new $class($this, $instance);
        }
        return null;
    }

    
    public function create_instance($raterid, $itemid = null) {

                if ($itemid && $current = $this->get_current_instance($raterid, $itemid)) {
            return $this->get_instance($current->copy($raterid, $itemid));
        } else {
            $class = 'gradingform_'. $this->get_method_name(). '_instance';
            return $this->get_instance($class::create_new($this->definition->id, $raterid, $itemid));
        }
    }

    
    public function get_or_create_instance($instanceid, $raterid, $itemid) {
        global $DB;
        if ($instanceid &&
                $instance = $DB->get_record('grading_instances', array('id'  => $instanceid, 'raterid' => $raterid, 'itemid' => $itemid), '*', IGNORE_MISSING)) {
            return $this->get_instance($instance);
        }
        return $this->create_instance($raterid, $itemid);
    }

    
    abstract public function render_preview(moodle_page $page);

    
    public function delete_definition() {
        global $DB;

        if (!$this->is_form_defined()) {
                        return;
        }

                $this->delete_plugin_definition();
                $DB->delete_records('grading_instances', array('definitionid' => $this->definition->id));
                $DB->delete_records('grading_definitions', array('id' => $this->definition->id));

        $this->definition = false;
    }

    
    public static function sql_search_from_tables($gdid) {
        return '';
    }

    
    public static function sql_search_where($token) {

        $subsql = array();
        $params = array();

        return array($subsql, $params);
    }

    
    
    protected function load_definition() {
        global $DB;
        $this->definition = $DB->get_record('grading_definitions', array(
            'areaid' => $this->areaid,
            'method' => $this->get_method_name()), '*', IGNORE_MISSING);
    }

    
    abstract protected function delete_plugin_definition();

    
    protected function get_method_name() {
        if (preg_match('/^gradingform_([a-z][a-z0-9_]*[a-z0-9])_controller$/', get_class($this), $matches)) {
            return $matches[1];
        } else {
            throw new coding_exception('Invalid class name');
        }
    }

    
    public function render_grade($page, $itemid, $gradinginfo, $defaultcontent, $cangrade) {
        return $defaultcontent;
    }

    
    public final function set_grade_range(array $graderange, $allowgradedecimals = false) {
        $this->graderange = $graderange;
        $this->allowgradedecimals = $allowgradedecimals;
    }

    
    public final function get_grade_range() {
        if (empty($this->graderange)) {
            return array();
        }
        return $this->graderange;
    }

    
    public final function get_allow_grade_decimals() {
        return $this->allowgradedecimals;
    }

    
    public static function get_external_definition_details() {
        return null;
    }

    
    public static function get_external_instance_filling_details() {
        return null;
    }
}


abstract class gradingform_instance {
    
    const INSTANCE_STATUS_ACTIVE = 1;
    
    const INSTANCE_STATUS_NEEDUPDATE = 2;
    
    const INSTANCE_STATUS_INCOMPLETE = 0;
    
    const INSTANCE_STATUS_ARCHIVE = 3;

    
    protected $data;
    
    protected $controller;

    
    public function __construct($controller, $data) {
        $this->data = (object)$data;
        $this->controller = $controller;
    }

    
    public static function create_new($definitionid, $raterid, $itemid) {
        global $DB;
        $instance = new stdClass();
        $instance->definitionid = $definitionid;
        $instance->raterid = $raterid;
        $instance->itemid = $itemid;
        $instance->status = self::INSTANCE_STATUS_INCOMPLETE;
        $instance->timemodified = time();
        $instance->feedbackformat = FORMAT_MOODLE;
        $instanceid = $DB->insert_record('grading_instances', $instance);
        return $instanceid;
    }

    
    public function copy($raterid, $itemid) {
        global $DB;
        $data = (array)$this->data;         unset($data['id']);
        $data['raterid'] = $raterid;
        $data['itemid'] = $itemid;
        $data['timemodified'] = time();
        $data['status'] = self::INSTANCE_STATUS_INCOMPLETE;
        $instanceid = $DB->insert_record('grading_instances', $data);
        return $instanceid;
    }

    
    public function get_current_instance() {
        if ($this->get_status() == self::INSTANCE_STATUS_ACTIVE || $this->get_status() == self::INSTANCE_STATUS_NEEDUPDATE) {
            return $this;
        }
        return $this->get_controller()->get_current_instance($this->data->raterid, $this->data->itemid);
    }

    
    public function get_controller() {
        return $this->controller;
    }

    
    public function get_data($key) {
        if (isset($this->data->$key)) {
            return $this->data->$key;
        }
        return null;
    }

    
    public function get_id() {
        return $this->get_data('id');
    }

    
    public function get_status() {
        return $this->get_data('status');
    }

    
    protected function make_active() {
        global $DB;
        if ($this->data->status == self::INSTANCE_STATUS_ACTIVE) {
                        return;
        }
        if (empty($this->data->itemid)) {
            throw new coding_exception('You cannot mark active the grading instance without itemid');
        }
        $currentid = $this->get_controller()->get_current_instance($this->data->raterid, $this->data->itemid, true);
        if ($currentid && $currentid != $this->get_id()) {
            $DB->update_record('grading_instances', array('id' => $currentid, 'status' => self::INSTANCE_STATUS_ARCHIVE));
        }
        $DB->update_record('grading_instances', array('id' => $this->get_id(), 'status' => self::INSTANCE_STATUS_ACTIVE));
        $this->data->status = self::INSTANCE_STATUS_ACTIVE;
    }

    
    public function cancel() {
        global $DB;
                $DB->delete_records('grading_instances', array('id' => $this->get_id()));
    }

    
    public function update($elementvalue) {
        global $DB;
        $newdata = new stdClass();
        $newdata->id = $this->get_id();
        $newdata->timemodified = time();
        if (isset($elementvalue['itemid']) && $elementvalue['itemid'] != $this->data->itemid) {
            $newdata->itemid = $elementvalue['itemid'];
        }
                $DB->update_record('grading_instances', $newdata);
        foreach ($newdata as $key => $value) {
            $this->data->$key = $value;
        }
    }

    
    abstract public function get_grade();

    
    public function is_empty_form($elementvalue) {
        return false;
    }

    
    public function clear_attempt($data) {
                        return;
    }

    
    public function submit_and_get_grade($elementvalue, $itemid) {
        $elementvalue['itemid'] = $itemid;
        if ($this->is_empty_form($elementvalue)) {
            $this->clear_attempt($elementvalue);
            $this->make_active();
            return -1;
        }
        $this->update($elementvalue);
        $this->make_active();
        return $this->get_grade();
    }

    
    abstract function render_grading_element($page, $gradingformelement);

    
    public function validate_grading_element($elementvalue) {
        return true;
    }

    
    public function default_validation_error_message() {
        return '';
    }
}
