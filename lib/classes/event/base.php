<?php

namespace core\event;

defined('MOODLE_INTERNAL') || die();




abstract class base implements \IteratorAggregate {

    
    const LEVEL_OTHER = 0;

    
    const LEVEL_TEACHING = 1;

    
    const LEVEL_PARTICIPATING = 2;

    
    const NOT_MAPPED = -31337;

    
    const NOT_FOUND = -31338;

    
    protected $data;

    
    protected $logextra;

    
    protected $context;

    
    private $triggered;

    
    private $dispatched;

    
    private $restored;

    
    private static $fields = array(
        'eventname', 'component', 'action', 'target', 'objecttable', 'objectid', 'crud', 'edulevel', 'contextid',
        'contextlevel', 'contextinstanceid', 'userid', 'courseid', 'relateduserid', 'anonymous', 'other',
        'timecreated');

    
    private $recordsnapshots = array();

    
    private final function __construct() {
        $this->data = array_fill_keys(self::$fields, null);

                $classname = get_called_class();
        $parts = explode('\\', $classname);
        if (count($parts) !== 3 or $parts[1] !== 'event') {
            throw new \coding_exception("Invalid event class name '$classname', it must be defined in component\\event\\
                    namespace");
        }
        $this->data['eventname'] = '\\'.$classname;
        $this->data['component'] = $parts[0];

        $pos = strrpos($parts[2], '_');
        if ($pos === false) {
            throw new \coding_exception("Invalid event class name '$classname', there must be at least one underscore separating
                    object and action words");
        }
        $this->data['target'] = substr($parts[2], 0, $pos);
        $this->data['action'] = substr($parts[2], $pos + 1);
    }

    
    public static final function create(array $data = null) {
        global $USER, $CFG;

        $data = (array)$data;

        
        $event = new static();
        $event->triggered = false;
        $event->restored = false;
        $event->dispatched = false;

                $event->data['anonymous'] = 0;

                $event->init();

        if (isset($event->data['level'])) {
            if (!isset($event->data['edulevel'])) {
                debugging('level property is deprecated, use edulevel property instead', DEBUG_DEVELOPER);
                $event->data['edulevel'] = $event->data['level'];
            }
            unset($event->data['level']);
        }

                $event->data['timecreated'] = time();

                $event->data['objectid'] = isset($data['objectid']) ? $data['objectid'] : null;
        $event->data['courseid'] = isset($data['courseid']) ? $data['courseid'] : null;
        $event->data['userid'] = isset($data['userid']) ? $data['userid'] : $USER->id;
        $event->data['other'] = isset($data['other']) ? $data['other'] : null;
        $event->data['relateduserid'] = isset($data['relateduserid']) ? $data['relateduserid'] : null;
        if (isset($data['anonymous'])) {
            $event->data['anonymous'] = $data['anonymous'];
        }
        $event->data['anonymous'] = (int)(bool)$event->data['anonymous'];

        if (isset($event->context)) {
            if (isset($data['context'])) {
                debugging('Context was already set in init() method, ignoring context parameter', DEBUG_DEVELOPER);
            }

        } else if (!empty($data['context'])) {
            $event->context = $data['context'];

        } else if (!empty($data['contextid'])) {
            $event->context = \context::instance_by_id($data['contextid'], MUST_EXIST);

        } else {
            throw new \coding_exception('context (or contextid) is a required event property, system context may be hardcoded in init() method.');
        }

        $event->data['contextid'] = $event->context->id;
        $event->data['contextlevel'] = $event->context->contextlevel;
        $event->data['contextinstanceid'] = $event->context->instanceid;

        if (!isset($event->data['courseid'])) {
            if ($coursecontext = $event->context->get_course_context(false)) {
                $event->data['courseid'] = $coursecontext->instanceid;
            } else {
                $event->data['courseid'] = 0;
            }
        }

        if (!array_key_exists('relateduserid', $data) and $event->context->contextlevel == CONTEXT_USER) {
            $event->data['relateduserid'] = $event->context->instanceid;
        }

                if ($CFG->debugdeveloper) {
            static $automatickeys = array('eventname', 'component', 'action', 'target', 'contextlevel', 'contextinstanceid', 'timecreated');
            static $initkeys = array('crud', 'level', 'objecttable', 'edulevel');

            foreach ($data as $key => $ignored) {
                if ($key === 'context') {
                    continue;

                } else if (in_array($key, $automatickeys)) {
                    debugging("Data key '$key' is not allowed in \\core\\event\\base::create() method, it is set automatically", DEBUG_DEVELOPER);

                } else if (in_array($key, $initkeys)) {
                    debugging("Data key '$key' is not allowed in \\core\\event\\base::create() method, you need to set it in init() method", DEBUG_DEVELOPER);

                } else if (!in_array($key, self::$fields)) {
                    debugging("Data key '$key' does not exist in \\core\\event\\base");
                }
            }
            $expectedcourseid = 0;
            if ($coursecontext = $event->context->get_course_context(false)) {
                $expectedcourseid = $coursecontext->instanceid;
            }
            if ($expectedcourseid != $event->data['courseid']) {
                debugging("Inconsistent courseid - context combination detected.", DEBUG_DEVELOPER);
            }
        }

                $event->validate_data();

        return $event;
    }

    
    protected abstract function init();

    
    protected function validate_data() {
                    }

    
    public static function get_name() {
                $parts = explode('\\', get_called_class());
        if (count($parts) !== 3) {
            return get_string('unknownevent', 'error');
        }
        return $parts[0].': '.str_replace('_', ' ', $parts[2]);
    }

    
    public function get_description() {
        return null;
    }

    
    public function can_view($user_or_id = null) {
        debugging('can_view() method is deprecated, use anonymous flag instead if necessary.', DEBUG_DEVELOPER);
        return is_siteadmin($user_or_id);
    }

    
    public static final function restore(array $data, array $logextra) {
        $classname = $data['eventname'];
        $component = $data['component'];
        $action = $data['action'];
        $target = $data['target'];

                if ($classname !== "\\{$component}\\event\\{$target}_{$action}") {
            return false;
        }

        if (!class_exists($classname)) {
            return self::restore_unknown($data, $logextra);
        }
        $event = new $classname();
        if (!($event instanceof \core\event\base)) {
            return false;
        }

        $event->init();         $event->restored = true;
        $event->triggered = true;
        $event->dispatched = true;
        $event->logextra = $logextra;

        foreach (self::$fields as $key) {
            if (!array_key_exists($key, $data)) {
                debugging("Event restore data must contain key $key");
                $data[$key] = null;
            }
        }
        if (count($data) != count(self::$fields)) {
            foreach ($data as $key => $value) {
                if (!in_array($key, self::$fields)) {
                    debugging("Event restore data cannot contain key $key");
                    unset($data[$key]);
                }
            }
        }
        $event->data = $data;

        return $event;
    }

    
    protected static final function restore_unknown(array $data, array $logextra) {
        $classname = '\core\event\unknown_logged';

        
        $event = new $classname();
        $event->restored = true;
        $event->triggered = true;
        $event->dispatched = true;
        $event->data = $data;
        $event->logextra = $logextra;

        return $event;
    }

    
    public static final function restore_legacy($legacy) {
        $classname = get_called_class();
        
        $event = new $classname();
        $event->restored = true;
        $event->triggered = true;
        $event->dispatched = true;

        $context = false;
        $component = 'legacy';
        if ($legacy->cmid) {
            $context = \context_module::instance($legacy->cmid, IGNORE_MISSING);
            $component = 'mod_'.$legacy->module;
        } else if ($legacy->course) {
            $context = \context_course::instance($legacy->course, IGNORE_MISSING);
        }
        if (!$context) {
            $context = \context_system::instance();
        }

        $event->data = array();

        $event->data['eventname'] = $legacy->module.'_'.$legacy->action;
        $event->data['component'] = $component;
        $event->data['action'] = $legacy->action;
        $event->data['target'] = null;
        $event->data['objecttable'] = null;
        $event->data['objectid'] = null;
        if (strpos($legacy->action, 'view') !== false) {
            $event->data['crud'] = 'r';
        } else if (strpos($legacy->action, 'print') !== false) {
            $event->data['crud'] = 'r';
        } else if (strpos($legacy->action, 'update') !== false) {
            $event->data['crud'] = 'u';
        } else if (strpos($legacy->action, 'hide') !== false) {
            $event->data['crud'] = 'u';
        } else if (strpos($legacy->action, 'move') !== false) {
            $event->data['crud'] = 'u';
        } else if (strpos($legacy->action, 'write') !== false) {
            $event->data['crud'] = 'u';
        } else if (strpos($legacy->action, 'tag') !== false) {
            $event->data['crud'] = 'u';
        } else if (strpos($legacy->action, 'remove') !== false) {
            $event->data['crud'] = 'u';
        } else if (strpos($legacy->action, 'delete') !== false) {
            $event->data['crud'] = 'p';
        } else if (strpos($legacy->action, 'create') !== false) {
            $event->data['crud'] = 'c';
        } else if (strpos($legacy->action, 'post') !== false) {
            $event->data['crud'] = 'c';
        } else if (strpos($legacy->action, 'add') !== false) {
            $event->data['crud'] = 'c';
        } else {
                        $event->data['crud'] = 'r';
        }
        $event->data['edulevel'] = $event::LEVEL_OTHER;
        $event->data['contextid'] = $context->id;
        $event->data['contextlevel'] = $context->contextlevel;
        $event->data['contextinstanceid'] = $context->instanceid;
        $event->data['userid'] = ($legacy->userid ? $legacy->userid : null);
        $event->data['courseid'] = ($legacy->course ? $legacy->course : null);
        $event->data['relateduserid'] = ($legacy->userid ? $legacy->userid : null);
        $event->data['timecreated'] = $legacy->time;

        $event->logextra = array();
        if ($legacy->ip) {
            $event->logextra['origin'] = 'web';
            $event->logextra['ip'] = $legacy->ip;
        } else {
            $event->logextra['origin'] = 'cli';
            $event->logextra['ip'] = null;
        }
        $event->logextra['realuserid'] = null;

        $event->data['other'] = (array)$legacy;

        return $event;
    }

    
    public static function get_objectid_mapping() {
        debugging('In order to restore course logs accurately the event "' . get_called_class() . '" must define the
            function get_objectid_mapping().', DEBUG_DEVELOPER);

        return false;
    }

    
    public static function get_other_mapping() {
        debugging('In order to restore course logs accurately the event "' . get_called_class() . '" must define the
            function get_other_mapping().', DEBUG_DEVELOPER);
    }

    
    public static final function get_static_info() {
        
        $event = new static();
                $event->init();
        return array(
            'eventname' => $event->data['eventname'],
            'component' => $event->data['component'],
            'target' => $event->data['target'],
            'action' => $event->data['action'],
            'crud' => $event->data['crud'],
            'edulevel' => $event->data['edulevel'],
            'objecttable' => $event->data['objecttable'],
        );
    }

    
    public static function get_explanation() {
        $ref = new \ReflectionClass(get_called_class());
        $docblock = $ref->getDocComment();

                if (empty($docblock)) {
            return null;
        }

        $docblocklines = explode("\n", $docblock);
                $pattern = "/(^\s*\/\*\*|^\s+\*\s|^\s+\*)/";
        $cleanline = array();
        foreach ($docblocklines as $line) {
            $templine = preg_replace($pattern, '', $line);
                        if (!empty($templine)) {
                $cleanline[] = rtrim($templine);
            }
                        if (preg_match("/^@|\//", $templine)) {
                                array_pop($cleanline);
                                break;
            }
        }
                $explanation = implode("\n", $cleanline);

        return $explanation;
    }

    
    public function get_context() {
        if (isset($this->context)) {
            return $this->context;
        }
        $this->context = \context::instance_by_id($this->data['contextid'], IGNORE_MISSING);
        return $this->context;
    }

    
    public function get_url() {
        return null;
    }

    
    public function get_data() {
        return $this->data;
    }

    
    public function get_logextra() {
        return $this->logextra;
    }

    
    public static function get_legacy_eventname() {
        return null;
    }

    
    protected function get_legacy_eventdata() {
        return null;
    }

    
    protected function get_legacy_logdata() {
        return null;
    }

    
    protected final function validate_before_trigger() {
        global $DB, $CFG;

        if (empty($this->data['crud'])) {
            throw new \coding_exception('crud must be specified in init() method of each method');
        }
        if (!isset($this->data['edulevel'])) {
            throw new \coding_exception('edulevel must be specified in init() method of each method');
        }
        if (!empty($this->data['objectid']) and empty($this->data['objecttable'])) {
            throw new \coding_exception('objecttable must be specified in init() method if objectid present');
        }

        if ($CFG->debugdeveloper) {
                        
            if (!in_array($this->data['crud'], array('c', 'r', 'u', 'd'), true)) {
                debugging("Invalid event crud value specified.", DEBUG_DEVELOPER);
            }
            if (!in_array($this->data['edulevel'], array(self::LEVEL_OTHER, self::LEVEL_TEACHING, self::LEVEL_PARTICIPATING))) {
                                debugging('Event property edulevel must a constant value, see event_base::LEVEL_*', DEBUG_DEVELOPER);
            }
            if (self::$fields !== array_keys($this->data)) {
                debugging('Number of event data fields must not be changed in event classes', DEBUG_DEVELOPER);
            }
            $encoded = json_encode($this->data['other']);
                                    if ($encoded === false or $this->data['other'] != json_decode($encoded, true)) {
                debugging('other event data must be compatible with json encoding', DEBUG_DEVELOPER);
            }
            if ($this->data['userid'] and !is_number($this->data['userid'])) {
                debugging('Event property userid must be a number', DEBUG_DEVELOPER);
            }
            if ($this->data['courseid'] and !is_number($this->data['courseid'])) {
                debugging('Event property courseid must be a number', DEBUG_DEVELOPER);
            }
            if ($this->data['objectid'] and !is_number($this->data['objectid'])) {
                debugging('Event property objectid must be a number', DEBUG_DEVELOPER);
            }
            if ($this->data['relateduserid'] and !is_number($this->data['relateduserid'])) {
                debugging('Event property relateduserid must be a number', DEBUG_DEVELOPER);
            }
            if ($this->data['objecttable']) {
                if (!$DB->get_manager()->table_exists($this->data['objecttable'])) {
                    debugging('Unknown table specified in objecttable field', DEBUG_DEVELOPER);
                }
                if (!isset($this->data['objectid'])) {
                    debugging('Event property objectid must be set when objecttable is defined', DEBUG_DEVELOPER);
                }
            }
        }
    }

    
    public final function trigger() {
        global $CFG;

        if ($this->restored) {
            throw new \coding_exception('Can not trigger restored event');
        }
        if ($this->triggered or $this->dispatched) {
            throw new \coding_exception('Can not trigger event twice');
        }

        $this->validate_before_trigger();

        $this->triggered = true;

        if (isset($CFG->loglifetime) and $CFG->loglifetime != -1) {
            if ($data = $this->get_legacy_logdata()) {
                $manager = get_log_manager();
                if (method_exists($manager, 'legacy_add_to_log')) {
                    if (is_array($data[0])) {
                                                foreach ($data as $d) {
                            call_user_func_array(array($manager, 'legacy_add_to_log'), $d);
                        }
                    } else {
                        call_user_func_array(array($manager, 'legacy_add_to_log'), $data);
                    }
                }
            }
        }

        if (PHPUNIT_TEST and \phpunit_util::is_redirecting_events()) {
            $this->dispatched = true;
            \phpunit_util::event_triggered($this);
            return;
        }

        \core\event\manager::dispatch($this);

        $this->dispatched = true;

        if ($legacyeventname = static::get_legacy_eventname()) {
            events_trigger_legacy($legacyeventname, $this->get_legacy_eventdata());
        }
    }

    
    public final function is_triggered() {
        return $this->triggered;
    }

    
    public final function is_dispatched() {
        return $this->dispatched;
    }

    
    public final function is_restored() {
        return $this->restored;
    }

    
    public final function add_record_snapshot($tablename, $record) {
        global $DB, $CFG;

        if ($this->triggered) {
            throw new \coding_exception('It is not possible to add snapshots after triggering of events');
        }

                if ($tablename === 'course_modules' && $record instanceof \cm_info) {
            $record = $record->get_course_module_record();
        }

                        if ($CFG->debugdeveloper) {
            if (!($record instanceof \stdClass)) {
                debugging('Argument $record must be an instance of stdClass.', DEBUG_DEVELOPER);
            }
            if (!$DB->get_manager()->table_exists($tablename)) {
                debugging("Invalid table name '$tablename' specified, database table does not exist.", DEBUG_DEVELOPER);
            } else {
                $columns = $DB->get_columns($tablename);
                $missingfields = array_diff(array_keys($columns), array_keys((array)$record));
                if (!empty($missingfields)) {
                    debugging("Fields list in snapshot record does not match fields list in '$tablename'. Record is missing fields: ".
                            join(', ', $missingfields), DEBUG_DEVELOPER);
                }
            }
        }
        $this->recordsnapshots[$tablename][$record->id] = $record;
    }

    
    public final function get_record_snapshot($tablename, $id) {
        global $DB;

        if ($this->restored) {
            throw new \coding_exception('It is not possible to get snapshots from restored events');
        }

        if (isset($this->recordsnapshots[$tablename][$id])) {
            return clone($this->recordsnapshots[$tablename][$id]);
        }

        $record = $DB->get_record($tablename, array('id'=>$id));
        $this->recordsnapshots[$tablename][$id] = $record;

        return $record;
    }

    
    public function __get($name) {
        if ($name === 'level') {
            debugging('level property is deprecated, use edulevel property instead', DEBUG_DEVELOPER);
            return $this->data['edulevel'];
        }
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        debugging("Accessing non-existent event property '$name'");
    }

    
    public function __set($name, $value) {
        throw new \coding_exception('Event properties must not be modified.');
    }

    
    public function __isset($name) {
        if ($name === 'level') {
            debugging('level property is deprecated, use edulevel property instead', DEBUG_DEVELOPER);
            return isset($this->data['edulevel']);
        }
        return isset($this->data[$name]);
    }

    
    public function getIterator() {
        return new \ArrayIterator($this->data);
    }
}
