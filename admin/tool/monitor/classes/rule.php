<?php



namespace tool_monitor;

defined('MOODLE_INTERNAL') || die();


class rule {

    
    protected $rule;

    
    public function __construct($rule) {
        $this->rule = $rule;
    }

    
    public function can_manage_rule() {
        $courseid = $this->courseid;
        $context = empty($courseid) ? \context_system::instance() : \context_course::instance($this->courseid);
        return has_capability('tool/monitor:managerules', $context);
    }

    
    public function duplicate_rule($finalcourseid) {
        $rule = fullclone($this->rule);
        unset($rule->id);
        $rule->courseid = $finalcourseid;
        $time = time();
        $rule->timecreated = $time;
        $rule->timemodified = $time;
        rule_manager::add_rule($rule);
    }

    
    public function delete_rule() {
        rule_manager::delete_rule($this->id);
    }

    
    public function get_subscribe_options($courseid) {
        global $CFG;

        $url = new \moodle_url($CFG->wwwroot. '/admin/tool/monitor/index.php', array(
            'courseid' => $courseid,
            'ruleid' => $this->id,
            'action' => 'subscribe',
            'sesskey' => sesskey()
        ));

        if (strpos($this->plugin, 'mod_') !== 0) {
            return $url;

        } else {
                        $options = array();
            $options[0] = get_string('allmodules', 'tool_monitor');

            if ($courseid == 0) {
                                return get_string('selectcourse', 'tool_monitor');
            }

                        $cms = get_fast_modinfo($courseid);
            $instances = $cms->get_instances_of(str_replace('mod_', '',  $this->plugin));
            foreach ($instances as $cminfo) {
                                if ($cminfo->uservisible && $cminfo->available) {
                    $options[$cminfo->id] = $cminfo->get_formatted_name();
                }
            }

            return new \single_select($url, 'cmid', $options);
        }
    }

    
    public function subscribe_user($courseid, $cmid, $userid = 0) {
        global $USER;

        if ($this->courseid != $courseid && $this->courseid != 0) {
                        throw new \coding_exception('Can not subscribe to rules from a different course');
        }
        if ($cmid !== 0) {
            $cms = get_fast_modinfo($courseid);
            $cminfo = $cms->get_cm($cmid);
            if (!$cminfo->uservisible || !$cminfo->available) {
                                throw new \coding_exception('You cannot do that');
            }
        }
        $userid = empty($userid) ? $USER->id : $userid;

        subscription_manager::create_subscription($this->id, $courseid, $cmid, $userid);
    }

    
    public function __get($prop) {
        if (property_exists($this->rule, $prop)) {
            return $this->rule->$prop;
        }
        throw new \coding_exception('Property "' . $prop . '" doesn\'t exist');
    }

    
    public function get_mform_set_data() {
        if (!empty($this->rule)) {
            $rule = fullclone($this->rule);
            $rule->description = array('text' => $rule->description, 'format' => $rule->descriptionformat);
            $rule->template = array('text' => $rule->template, 'format' => $rule->templateformat);
            return $rule;
        }
        throw new \coding_exception('Invalid call to get_mform_set_data.');
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

    
    public function get_course_name($context) {
        $courseid = $this->courseid;
        if (empty($courseid)) {
            return get_string('site');
        } else {
            $course = get_course($courseid);
            return format_string($course->fullname, true, array('context' => $context));
        }
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
}
