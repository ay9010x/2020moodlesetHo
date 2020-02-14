<?php



defined('MOODLE_INTERNAL') || die();


class testing_repository_generator extends component_generator_base {

    
    protected $instancecount = 0;

    
    public function reset() {
        $this->instancecount = 0;
    }

    
    public function get_typename() {
        $matches = null;
        if (!preg_match('/^repository_([a-z0-9_]+)_generator$/', get_class($this), $matches)) {
            throw new coding_exception('Invalid repository generator class name: '.get_class($this));
        }
        if (empty($matches[1])) {
            throw new coding_exception('Invalid repository generator class name: '.get_class($this));
        }
        return $matches[1];
    }

    
    protected function prepare_record(array $record) {
        if (!isset($record['name'])) {
            $record['name'] = $this->get_typename() . ' ' . $this->instancecount;
        }
        if (!isset($record['contextid'])) {
            $record['contextid'] = context_system::instance()->id;
        }
        return $record;
    }

    
    protected function prepare_type_record(array $record) {
        if (!isset($record['pluginname'])) {
            $record['pluginname'] = '';
        }
        if (!isset($record['enableuserinstances'])) {
            $record['enableuserinstances'] = 1;
        }
        if (!isset($record['enablecourseinstances'])) {
            $record['enablecourseinstances'] = 1;
        }
        return $record;
    }

    
    public function create_instance($record = null, array $options = null) {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/repository/lib.php');

        $this->instancecount++;
        $record = (array) $record;

        $typeid = $DB->get_field('repository', 'id', array('type' => $this->get_typename()), MUST_EXIST);
        $instanceoptions = repository::static_function($this->get_typename(), 'get_instance_option_names');

        if (empty($instanceoptions)) {
                        $id = $DB->get_field('repository_instances', 'id', array('typeid' => $typeid), MUST_EXIST);
        } else {
                        $record = $this->prepare_record($record);

            if (empty($record['contextid'])) {
                throw new coding_exception('contextid must be present in testing_repository_generator::create_instance() $record');
            }

            foreach ($instanceoptions as $option) {
                if (!isset($record[$option])) {
                    throw new coding_exception("$option must be present in testing_repository_generator::create_instance() \$record");
                }
            }

            $context = context::instance_by_id($record['contextid']);
            unset($record['contextid']);
            if (!in_array($context->contextlevel, array(CONTEXT_SYSTEM, CONTEXT_COURSE, CONTEXT_USER))) {
                throw new coding_exception('Wrong contextid passed in testing_repository_generator::create_instance() $record');
            }

            $id = repository::static_function($this->get_typename(), 'create', $this->get_typename(), 0, $context, $record);
        }

        return $DB->get_record('repository_instances', array('id' => $id), '*', MUST_EXIST);
    }

    
    public function create_type($record = null, array $options = null) {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/repository/lib.php');

        $record = (array) $record;
        $type = $this->get_typename();

        $typeoptions = repository::static_function($type, 'get_type_option_names');
        $instanceoptions = repository::static_function($type, 'get_instance_option_names');

                if (!empty($instanceoptions)) {
            $typeoptions[] = 'enableuserinstances';
            $typeoptions[] = 'enablecourseinstances';
        }

                $record = $this->prepare_type_record($record);
        foreach ($typeoptions as $option) {
            if (!isset($record[$option])) {
                throw new coding_exception("$option must be present in testing::create_repository_type() for $type");
            }
        }

                $record = array_intersect_key($record, array_flip($typeoptions));

                $plugintype = new repository_type($type, $record);
        $plugintype->create(false);

        return $DB->get_record('repository', array('type' => $type));
    }
}
