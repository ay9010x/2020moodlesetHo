<?php



namespace tool_monitor\output\managesubs;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');


class subs extends \table_sql implements \renderable {

    
    public $courseid;

    
    protected $context;

    
    public function __construct($uniqueid, \moodle_url $url, $courseid = 0, $perpage = 100) {
        parent::__construct($uniqueid);

        $this->set_attribute('class', 'toolmonitor subscriptions generaltable generalbox');
        $this->define_columns(array('name', 'description', 'course', 'plugin', 'instance', 'eventname',
            'filters', 'unsubscribe'));
        $this->define_headers(array(
                get_string('rulename', 'tool_monitor'),
                get_string('description'),
                get_string('course'),
                get_string('area', 'tool_monitor'),
                get_string('moduleinstance', 'tool_monitor'),
                get_string('event', 'tool_monitor'),
                get_string('frequency', 'tool_monitor'),
                get_string('unsubscribe', 'tool_monitor')
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
    }

    
    public function col_name(\tool_monitor\subscription $sub) {
        return $sub->get_name($this->context);
    }

    
    public function col_description(\tool_monitor\subscription $sub) {
        return $sub->get_description($this->context);
    }

    
    public function col_course(\tool_monitor\subscription $sub) {
        $coursename = $sub->get_course_name($this->context);

        $courseid = $sub->courseid;
        if (empty($courseid)) {
            return $coursename;
        } else {
            return \html_writer::link(new \moodle_url('/course/view.php', array('id' => $courseid)), $coursename);
        }
    }

    
    public function col_plugin(\tool_monitor\subscription $sub) {
        return $sub->get_plugin_name();
    }

    
    public function col_instance(\tool_monitor\subscription $sub) {
        return $sub->get_instance_name();
    }

    
    public function col_eventname(\tool_monitor\subscription $sub) {
        return $sub->get_event_name();
    }

    
    public function col_filters(\tool_monitor\subscription $sub) {
        return $sub->get_filters_description();
    }

    
    public function col_unsubscribe(\tool_monitor\subscription $sub) {
        global $OUTPUT, $CFG;

        $deleteurl = new \moodle_url($CFG->wwwroot. '/admin/tool/monitor/index.php', array('subscriptionid' => $sub->id,
                'action' => 'unsubscribe', 'courseid' => $this->courseid, 'sesskey' => sesskey()));
        $icon = $OUTPUT->render(new \pix_icon('t/delete', get_string('deletesubscription', 'tool_monitor')));

        return \html_writer::link($deleteurl, $icon, array('class' => 'action-icon'));
    }

    
    public function query_db($pagesize, $useinitialsbar = true) {

        $total = \tool_monitor\subscription_manager::count_user_subscriptions();
        $this->pagesize($pagesize, $total);
        $subs = \tool_monitor\subscription_manager::get_user_subscriptions($this->get_page_start(), $this->get_page_size());
        $this->rawdata = $subs;
                if ($useinitialsbar) {
            $this->initialbars($total > $pagesize);
        }
    }
}
