<?php



defined('MOODLE_INTERNAL') || die();


abstract class testing_module_generator extends component_generator_base {

    
    protected $instancecount = 0;

    
    public function reset() {
        $this->instancecount = 0;
    }

    
    public function get_modulename() {
        $matches = null;
        if (!preg_match('/^mod_([a-z0-9]+)_generator$/', get_class($this), $matches)) {
            throw new coding_exception('Invalid module generator class name: '.get_class($this));
        }

        if (empty($matches[1])) {
            throw new coding_exception('Invalid module generator class name: '.get_class($this));
        }
        return $matches[1];
    }

    
    protected function precreate_course_module($courseid, array $options) {
        global $DB, $CFG;
        require_once("$CFG->dirroot/course/lib.php");

        $modulename = $this->get_modulename();
        $sectionnum = isset($options['section']) ? $options['section'] : 0;
        unset($options['section']); 
        $cm = new stdClass();
        $cm->course             = $courseid;
        $cm->module             = $DB->get_field('modules', 'id', array('name'=>$modulename));
        $cm->instance           = 0;
        $cm->section            = 0;
        $cm->idnumber           = isset($options['idnumber']) ? $options['idnumber'] : 0;
        $cm->added              = time();

        $columns = $DB->get_columns('course_modules');
        foreach ($options as $key => $value) {
            if ($key === 'id' or !isset($columns[$key])) {
                continue;
            }
            if (property_exists($cm, $key)) {
                continue;
            }
            $cm->$key = $value;
        }

        $cm->id = $DB->insert_record('course_modules', $cm);

        course_add_cm_to_section($courseid, $cm->id, $sectionnum);

        return $cm->id;
    }

    
    protected function post_add_instance($id, $cmid) {
        global $DB;

        $DB->set_field('course_modules', 'instance', $id, array('id'=>$cmid));

        $instance = $DB->get_record($this->get_modulename(), array('id'=>$id), '*', MUST_EXIST);

        $cm = get_coursemodule_from_id($this->get_modulename(), $cmid, $instance->course, true, MUST_EXIST);
        context_module::instance($cm->id);

        $instance->cmid = $cm->id;

        return $instance;
    }

    
    protected function prepare_moduleinfo_record($record, $options) {
        global $DB;
                $moduleinfo = (object)(array)$record;

        if (empty($moduleinfo->course)) {
            throw new coding_exception('module generator requires $record->course');
        }

        $moduleinfo->modulename = $this->get_modulename();
        $moduleinfo->module = $DB->get_field('modules', 'id', array('name' => $moduleinfo->modulename));

                        if (isset($options['idnumber'])) {
            $moduleinfo->cmidnumber = $options['idnumber'];
        } else if (!isset($moduleinfo->cmidnumber) && isset($moduleinfo->idnumber)) {
            $moduleinfo->cmidnumber = $moduleinfo->idnumber;
        }

                                $easymergefields = array('section', 'added', 'score', 'indent',
            'visible', 'visibleold', 'groupmode', 'groupingid',
            'completion', 'completiongradeitemnumber', 'completionview', 'completionexpected',
            'availability', 'showdescription');
        foreach ($easymergefields as $key) {
            if (isset($options[$key])) {
                $moduleinfo->$key = $options[$key];
            }
        }

                $defaults = array(
            'section' => 0,
            'visible' => 1,
            'cmidnumber' => '',
            'groupmode' => 0,
            'groupingid' => 0,
            'availability' => null,
            'completion' => 0,
            'completionview' => 0,
            'completionexpected' => 0,
            'conditiongradegroup' => array(),
            'conditionfieldgroup' => array(),
            'conditioncompletiongroup' => array()
        );
        foreach ($defaults as $key => $value) {
            if (!isset($moduleinfo->$key)) {
                $moduleinfo->$key = $value;
            }
        }

        return $moduleinfo;
    }

    
    public function create_instance($record = null, array $options = null) {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/course/modlib.php');

        $this->instancecount++;

                $record = $this->prepare_moduleinfo_record($record, $options);

                if (!empty($record->course->id)) {
            $course = $record->course;
            $record->course = $record->course->id;
        } else {
            $course = get_course($record->course);
        }

                if (empty($record->name)) {
            $record->name = get_string('pluginname', $this->get_modulename()).' '.$this->instancecount;
        }
        if (empty($record->introeditor) && empty($record->intro)) {
            $record->intro = 'Test '.$this->get_modulename().' ' . $this->instancecount;
        }
        if (empty($record->introeditor) && empty($record->introformat)) {
            $record->introformat = FORMAT_MOODLE;
        }

        if (isset($record->tags) && !is_array($record->tags)) {
            $record->tags = preg_split('/\s*,\s*/', trim($record->tags), -1, PREG_SPLIT_NO_EMPTY);
        }

                                if ($record->completion && empty($CFG->enablecompletion)) {
            debugging('Did you forget to set $CFG->enablecompletion before generating module with completion tracking?', DEBUG_DEVELOPER);
        }
        if ($record->completion && empty($course->enablecompletion)) {
            debugging('Did you forget to enable completion tracking for the course before generating module with completion tracking?', DEBUG_DEVELOPER);
        }

                $moduleinfo = add_moduleinfo($record, $course, $mform = null);

                $instance = $DB->get_record($this->get_modulename(), array('id' => $moduleinfo->instance), '*', MUST_EXIST);
        $instance->cmid = $moduleinfo->coursemodule;
        return $instance;
    }

    
    public function create_content($instance, $record = array()) {
        throw new coding_exception('Module generator for '.$this->get_modulename().' does not implement method create_content()');
    }
}
