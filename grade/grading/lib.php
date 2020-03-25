<?php



defined('MOODLE_INTERNAL') || die();


function get_grading_manager($context_or_areaid = null, $component = null, $area = null) {
    global $DB;

    $manager = new grading_manager();

    if (is_object($context_or_areaid)) {
        $context = $context_or_areaid;
    } else {
        $context = null;

        if (is_numeric($context_or_areaid)) {
            $manager->load($context_or_areaid);
            return $manager;
        }
    }

    if (!is_null($context)) {
        $manager->set_context($context);
    }

    if (!is_null($component)) {
        $manager->set_component($component);
    }

    if (!is_null($area)) {
        $manager->set_area($area);
    }

    return $manager;
}


class grading_manager {

    
    protected $context;

    
    protected $component;

    
    protected $area;

    
    private $areacache = null;

    
    public function get_context() {
        return $this->context;
    }

    
    public function set_context(stdClass $context) {
        $this->areacache = null;
        $this->context = $context;
    }

    
    public function get_component() {
        return $this->component;
    }

    
    public function set_component($component) {
        $this->areacache = null;
        list($type, $name) = core_component::normalize_component($component);
        $this->component = $type.'_'.$name;
    }

    
    public function get_area() {
        return $this->area;
    }

    
    public function set_area($area) {
        $this->areacache = null;
        $this->area = $area;
    }

    
    public function get_component_title() {

        $this->ensure_isset(array('context', 'component'));

        if ($this->get_context()->contextlevel == CONTEXT_SYSTEM) {
            if ($this->get_component() == 'core_grading') {
                $title = '';             } else {
                throw new coding_exception('Unsupported component at the system context');
            }

        } else if ($this->get_context()->contextlevel >= CONTEXT_COURSE) {
            list($context, $course, $cm) = get_context_info_array($this->get_context()->id);

            if (strval($cm->name) !== '') {
                $title = $cm->name;
            } else {
                debugging('Gradable areas are currently supported at the course module level only', DEBUG_DEVELOPER);
                $title = $this->get_component();
            }

        } else {
            throw new coding_exception('Unsupported gradable area context level');
        }

        return $title;
    }

    
    public function get_area_title() {

        if ($this->get_context()->contextlevel == CONTEXT_SYSTEM) {
            return '';

        } else if ($this->get_context()->contextlevel >= CONTEXT_COURSE) {
            $this->ensure_isset(array('context', 'component', 'area'));
            $areas = $this->get_available_areas();
            if (array_key_exists($this->get_area(), $areas)) {
                return $areas[$this->get_area()];
            } else {
                debugging('Unknown area!');
                return '???';
            }

        } else {
            throw new coding_exception('Unsupported context level');
        }
    }

    
    public function load($areaid) {
        global $DB;

        $this->areacache = $DB->get_record('grading_areas', array('id' => $areaid), '*', MUST_EXIST);
        $this->context = context::instance_by_id($this->areacache->contextid, MUST_EXIST);
        $this->component = $this->areacache->component;
        $this->area = $this->areacache->areaname;
    }

    
    public static function available_methods($includenone = true) {

        if ($includenone) {
            $list = array('' => get_string('gradingmethodnone', 'core_grading'));
        } else {
            $list = array();
        }

        foreach (core_component::get_plugin_list('gradingform') as $name => $location) {
            $list[$name] = get_string('pluginname', 'gradingform_'.$name);
        }

        return $list;
    }

    
    public function get_available_methods($includenone = true) {
        $this->ensure_isset(array('context'));
        return self::available_methods($includenone);
    }

    
    public static function available_areas($component) {
        global $CFG;

        list($plugintype, $pluginname) = core_component::normalize_component($component);

        if ($component === 'core_grading') {
            return array();

        } else if ($plugintype === 'mod') {
            return plugin_callback('mod', $pluginname, 'grading', 'areas_list', null, array());

        } else {
            throw new coding_exception('Unsupported area location');
        }
    }


    
    public function get_available_areas() {
        global $CFG;

        $this->ensure_isset(array('context', 'component'));

        if ($this->get_context()->contextlevel == CONTEXT_SYSTEM) {
            if ($this->get_component() !== 'core_grading') {
                throw new coding_exception('Unsupported component at the system context');
            } else {
                return array();
            }

        } else if ($this->get_context()->contextlevel == CONTEXT_MODULE) {
            list($context, $course, $cm) = get_context_info_array($this->get_context()->id);
            return self::available_areas('mod_'.$cm->modname);

        } else {
            throw new coding_exception('Unsupported gradable area context level');
        }
    }

    
    public function get_active_method() {
        global $DB;

        $this->ensure_isset(array('context', 'component', 'area'));

                if (is_null($this->areacache)) {
            $this->areacache = $DB->get_record('grading_areas', array(
                'contextid' => $this->context->id,
                'component' => $this->component,
                'areaname'  => $this->area),
            '*', IGNORE_MISSING);
        }

        if ($this->areacache === false) {
                        return null;
        }

        return $this->areacache->activemethod;
    }

    
    public function set_active_method($method) {
        global $DB;

        $this->ensure_isset(array('context', 'component', 'area'));

                if (empty($method)) {
            $method = null;
        } else {
            if ('gradingform_'.$method !== clean_param('gradingform_'.$method, PARAM_COMPONENT)) {
                throw new moodle_exception('invalid_method_name', 'core_grading');
            }
            $available = $this->get_available_methods(false);
            if (!array_key_exists($method, $available)) {
                throw new moodle_exception('invalid_method_name', 'core_grading');
            }
        }

                if (is_null($this->areacache)) {
            $this->areacache = $DB->get_record('grading_areas', array(
                'contextid' => $this->context->id,
                'component' => $this->component,
                'areaname'  => $this->area),
            '*', IGNORE_MISSING);
        }

        $methodchanged = false;

        if ($this->areacache === false) {
                        $area = array(
                'contextid'     => $this->context->id,
                'component'     => $this->component,
                'areaname'      => $this->area,
                'activemethod'  => $method);
            $DB->insert_record('grading_areas', $area);
            $methodchanged = true;

        } else {
                        if ($this->areacache->activemethod !== $method) {
                $DB->set_field('grading_areas', 'activemethod', $method, array('id' => $this->areacache->id));
                $methodchanged = true;
            }
        }

        $this->areacache = null;

        return $methodchanged;
    }

    
    public function extend_settings_navigation(settings_navigation $settingsnav, navigation_node $modulenode=null) {

        $this->ensure_isset(array('context', 'component'));

        $areas = $this->get_available_areas();

        if (empty($areas)) {
                        return;

        } else if (count($areas) == 1) {
                        $areatitle = reset($areas);
            $areaname  = key($areas);
            $this->set_area($areaname);
            $method = $this->get_active_method();
            $managementnode = $modulenode->add(get_string('gradingmanagement', 'core_grading'),
                $this->get_management_url(), settings_navigation::TYPE_CUSTOM);
            if ($method) {
                $controller = $this->get_controller($method);
                $controller->extend_settings_navigation($settingsnav, $managementnode);
            }

        } else {
                        $managementnode = $modulenode->add(get_string('gradingmanagement', 'core_grading'),
                null, settings_navigation::TYPE_CUSTOM);
            foreach ($areas as $areaname => $areatitle) {
                $this->set_area($areaname);
                $method = $this->get_active_method();
                $node = $managementnode->add($areatitle,
                    $this->get_management_url(), settings_navigation::TYPE_CUSTOM);
                if ($method) {
                    $controller = $this->get_controller($method);
                    $controller->extend_settings_navigation($settingsnav, $node);
                }
            }
        }
    }

    
    public function extend_navigation(global_navigation $navigation, navigation_node $modulenode=null) {
        $this->ensure_isset(array('context', 'component'));

        $areas = $this->get_available_areas();
        foreach ($areas as $areaname => $areatitle) {
            $this->set_area($areaname);
            if ($controller = $this->get_active_controller()) {
                $controller->extend_navigation($navigation, $modulenode);
            }
        }
    }

    
    public function get_controller($method) {
        global $CFG, $DB;

        $this->ensure_isset(array('context', 'component', 'area'));

                if ('gradingform_'.$method !== clean_param('gradingform_'.$method, PARAM_COMPONENT)) {
            throw new moodle_exception('invalid_method_name', 'core_grading');
        }
        $available = $this->get_available_methods(false);
        if (!array_key_exists($method, $available)) {
            throw new moodle_exception('invalid_method_name', 'core_grading');
        }

                if (is_null($this->areacache)) {
            $this->areacache = $DB->get_record('grading_areas', array(
                'contextid' => $this->context->id,
                'component' => $this->component,
                'areaname'  => $this->area),
            '*', IGNORE_MISSING);
        }

        if ($this->areacache === false) {
                        $area = array(
                'contextid' => $this->context->id,
                'component' => $this->component,
                'areaname'  => $this->area);
            $areaid = $DB->insert_record('grading_areas', $area);
                        $this->areacache = $DB->get_record('grading_areas', array('id' => $areaid), '*', MUST_EXIST);
        }

        require_once($CFG->dirroot.'/grade/grading/form/'.$method.'/lib.php');
        $classname = 'gradingform_'.$method.'_controller';

        return new $classname($this->context, $this->component, $this->area, $this->areacache->id);
    }

    
    public function get_active_controller() {
        if ($gradingmethod = $this->get_active_method()) {
            $controller = $this->get_controller($gradingmethod);
            if ($controller->is_form_available()) {
                return $controller;
            }
        }
        return null;
    }

    
    public function get_management_url(moodle_url $returnurl = null) {

        $this->ensure_isset(array('context', 'component'));

        if ($this->areacache) {
            $params = array('areaid' => $this->areacache->id);
        } else {
            $params = array('contextid' => $this->context->id, 'component' => $this->component);
            if ($this->area) {
                $params['area'] = $this->area;
            }
        }

        if (!is_null($returnurl)) {
            $params['returnurl'] = $returnurl->out(false);
        }

        return new moodle_url('/grade/grading/manage.php', $params);
    }

    
    public function create_shared_area($method) {
        global $DB;

                $name = $method . '_' . sha1(rand().uniqid($method, true));
                $area = array(
            'contextid'     => context_system::instance()->id,
            'component'     => 'core_grading',
            'areaname'      => $name,
            'activemethod'  => $method);
        return $DB->insert_record('grading_areas', $area);
    }

    
    public static function delete_all_for_context($contextid) {
        global $DB;

        $areaids = $DB->get_fieldset_select('grading_areas', 'id', 'contextid = ?', array($contextid));
        $methods = array_keys(self::available_methods(false));

        foreach($areaids as $areaid) {
            $manager = get_grading_manager($areaid);
            foreach ($methods as $method) {
                $controller = $manager->get_controller($method);
                $controller->delete_definition();
            }
        }

        $DB->delete_records_list('grading_areas', 'id', $areaids);
    }

    
    public static function tokenize($needle) {

                if (preg_match('/^[\s]*"[\s]*(.*?)[\s]*"[\s]*$/', $needle, $matches)) {
            $token = $matches[1];
            if ($token === '') {
                return array();
            } else {
                return array($token);
            }
        }

                $tokens = preg_split("/\W/u", $needle);
                $tokens = array_filter($tokens);
                $tokens = array_unique($tokens);
                foreach ($tokens as $ix => $token) {
            if (strlen($token) == 1) {
                unset($tokens[$ix]);
            }
        }

        return array_values($tokens);
    }

    
    
    private function ensure_isset(array $properties) {
        foreach ($properties as $property) {
            if (!isset($this->$property)) {
                throw new coding_exception('The property "'.$property.'" is not set.');
            }
        }
    }
}
