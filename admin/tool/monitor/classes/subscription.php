<?php



namespace tool_monitor;

defined('MOODLE_INTERNAL') || die();


class subscription {
    
    protected $subscription;

    
    public function __construct($subscription) {
        $this->subscription = $subscription;
    }

    
    public function __get($prop) {
        if (isset($this->subscription->$prop)) {
            return $this->subscription->$prop;
        }
        throw new \coding_exception('Property "' . $prop . '" doesn\'t exist');
    }

    
    public function __isset($prop) {
        return property_exists($this->subscription, $prop);
    }

    
    public function get_instance_name() {
        if ($this->plugin === 'core') {
            $string = get_string('allevents', 'tool_monitor');
        } else {
            if ($this->cmid == 0) {
                $string = get_string('allmodules', 'tool_monitor');
            } else {
                $cms = get_fast_modinfo($this->courseid);
                $cms = $cms->get_cms();
                if (isset($cms[$this->cmid])) {
                    $string = $cms[$this->cmid]->get_formatted_name();                 } else {
                                        $string = get_string('invalidmodule', 'tool_monitor');
                }
            }
        }

        return $string;
    }

    
    public function get_event_name() {
        $eventclass = $this->eventname;
        if (class_exists($eventclass)) {
            return $eventclass::get_name();
        }
        return get_string('eventnotfound', 'tool_monitor');
    }

    
    public function get_filters_description() {
        $a = new \stdClass();
        $a->freq = $this->frequency;
        $mins = $this->timewindow / MINSECS;         $a->mins = $mins;
        return get_string('freqdesc', 'tool_monitor', $a);
    }

    
    public function get_name(\context $context) {
        return format_text($this->name, FORMAT_HTML, array('context' => $context));
    }

    
    public function get_description(\context $context) {
        return format_text($this->description, $this->descriptionformat, array('context' => $context));
    }

    
    public function get_plugin_name() {
        if ($this->plugin === 'core') {
            $string = get_string('core', 'tool_monitor');
        } else if (get_string_manager()->string_exists('pluginname', $this->plugin)) {
            $string = get_string('pluginname', $this->plugin);
        } else {
            $string = $this->plugin;
        }
        return $string;
    }

    
    public function get_course_name(\context $context) {
        $courseid = $this->courseid;
        if (empty($courseid)) {
            return get_string('site');
        } else {
            $course = get_course($courseid);
            return format_string($course->fullname, true, array('context' => $context));
        }
    }

    
    public function can_manage_rule() {
        $courseid = $this->rulecourseid;
        $context = empty($courseid) ? \context_system::instance() : \context_course::instance($courseid);
        return has_capability('tool/monitor:managerules', $context);
    }
}
