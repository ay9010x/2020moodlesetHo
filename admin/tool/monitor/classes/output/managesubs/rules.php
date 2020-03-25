<?php



namespace tool_monitor\output\managesubs;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');


class rules extends \table_sql implements \renderable {

    
    public $courseid;

    
    public $totalcount = 0;

    
    protected $context;

    
    public function __construct($uniqueid, \moodle_url $url, $courseid = 0, $perpage = 100) {
        parent::__construct($uniqueid);

        $this->set_attribute('class', 'toolmonitor subscriberules generaltable generalbox');
        $this->define_columns(array('name', 'description', 'course', 'plugin', 'eventname', 'filters', 'select'));
        $this->define_headers(array(
                get_string('rulename', 'tool_monitor'),
                get_string('description'),
                get_string('course'),
                get_string('area', 'tool_monitor'),
                get_string('event', 'tool_monitor'),
                get_string('frequency', 'tool_monitor'),
                ''
            )
        );
        $this->courseid = $courseid;
        $this->pagesize = $perpage;
        $systemcontext = \context_system::instance();
        $this->context = empty($courseid) ? $systemcontext : \context_course::instance($courseid);
        $this->collapsible(false);
        $this->sortable(false);
        $this->pageable(true);
        $this->is_downloadable(false);
        $this->define_baseurl($url);
        $total = \tool_monitor\rule_manager::count_rules_by_courseid($this->courseid);
        $this->totalcount = $total;
    }

    
    public function col_name(\tool_monitor\rule $rule) {
        return $rule->get_name($this->context);
    }

    
    public function col_description(\tool_monitor\rule $rule) {
        return $rule->get_description($this->context);
    }

    
    public function col_course(\tool_monitor\rule $rule) {
        $coursename = $rule->get_course_name($this->context);

        $courseid = $rule->courseid;
        if (empty($courseid)) {
            return $coursename;
        } else {
            return \html_writer::link(new \moodle_url('/course/view.php', array('id' => $this->courseid)), $coursename);
        }
    }

    
    public function col_plugin(\tool_monitor\rule $rule) {
        return $rule->get_plugin_name();
    }

    
    public function col_eventname(\tool_monitor\rule $rule) {
        return $rule->get_event_name();
    }

    
    public function col_filters(\tool_monitor\rule $rule) {
        return $rule->get_filters_description();
    }

    
    public function col_select(\tool_monitor\rule $rule) {
        global $OUTPUT;

        $options = $rule->get_subscribe_options($this->courseid);
        $text = get_string('subscribeto', 'tool_monitor', $rule->get_name($this->context));

        if ($options instanceof \single_select) {
            $options->set_label($text, array('class' => 'accesshide'));
            return $OUTPUT->render($options);
        } else if ($options instanceof \moodle_url) {
                        $icon = $OUTPUT->pix_icon('t/add', $text);
            $link = new \action_link($options, $icon);
            return $OUTPUT->render($link);
        } else {
            return $options;
        }
    }

    
    public function query_db($pagesize, $useinitialsbar = true) {

        $total = \tool_monitor\rule_manager::count_rules_by_courseid($this->courseid);
        $this->pagesize($pagesize, $total);
        $rules = \tool_monitor\rule_manager::get_rules_by_courseid($this->courseid, $this->get_page_start(),
                $this->get_page_size());
        $this->rawdata = $rules;
                if ($useinitialsbar) {
            $this->initialbars($total > $pagesize);
        }
    }

    
    public function get_user_courses_select($choose = false) {
        $options = tool_monitor_get_user_courses();
                if (!$options) {
            return false;
        }
        $selected = $this->courseid;
        $nothing = array();
        if ($choose) {
            $selected = null;
            $nothing = array('choosedots');
        }
        $url = new \moodle_url('/admin/tool/monitor/index.php');
        $select = new \single_select($url, 'courseid', $options, $selected, $nothing);
        $select->set_label(get_string('selectacourse', 'tool_monitor'));
        return $select;
    }
}
