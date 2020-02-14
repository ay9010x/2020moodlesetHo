<?php



defined('MOODLE_INTERNAL') || die;


class report_loglive_renderable implements renderable {

    
    const CUTOFF = 3600;

    
    protected $logmanager;

    
    public $selectedlogreader = null;

    
    public $page;

    
    public $perpage;

    
    public $course;

    
    public $url;

    
    public $date;

    
    public $order;

    
    public $groupid;

    
    public $tablelog;

    
    protected $refresh  = 60;

    
    public function __construct($logreader = "", $course = 0, $url = "", $date = 0, $page = 0, $perpage = 100,
                                $order = "timecreated DESC") {

        global $PAGE;

                if (empty($logreader)) {
            $readers = $this->get_readers();
            if (!empty($readers)) {
                reset($readers);
                $logreader = key($readers);
            } else {
                $logreader = null;
            }
        }
        $this->selectedlogreader = $logreader;

                if (empty($url)) {
            $url = new moodle_url($PAGE->url);
        } else {
            $url = new moodle_url($url);
        }
        $this->url = $url;

                if (!empty($course) && is_int($course)) {
            $course = get_course($course);
        }
        $this->course = $course;

        if ($date == 0 ) {
            $date = time() - self::CUTOFF;
        }
        $this->date = $date;

        $this->page = $page;
        $this->perpage = $perpage;
        $this->order = $order;
        $this->set_refresh_rate();
    }

    
    public function get_readers($nameonly = false) {
        if (!isset($this->logmanager)) {
            $this->logmanager = get_log_manager();
        }

        $readers = $this->logmanager->get_readers('core\log\sql_reader');
        if ($nameonly) {
            foreach ($readers as $pluginname => $reader) {
                $readers[$pluginname] = $reader->get_name();
            }
        }
        return $readers;
    }

    
    protected function setup_table() {
        $filter = $this->setup_filters();
        $this->tablelog = new report_loglive_table_log('report_loglive', $filter);
        $this->tablelog->define_baseurl($this->url);
    }

    
    protected function setup_table_ajax() {
        $filter = $this->setup_filters();
        $this->tablelog = new report_loglive_table_log_ajax('report_loglive', $filter);
        $this->tablelog->define_baseurl($this->url);
    }

    
    protected function setup_filters() {
        $readers = $this->get_readers();

                $filter = new \stdClass();
        if (!empty($this->course)) {
            $filter->courseid = $this->course->id;
        } else {
            $filter->courseid = 0;
        }
        $filter->logreader = $readers[$this->selectedlogreader];
        $filter->date = $this->date;
        $filter->orderby = $this->order;
        $filter->anonymous = 0;

        return $filter;
    }

    
    protected function set_refresh_rate() {
        if (defined('BEHAT_SITE_RUNNING')) {
                        $this->refresh = 5;
        } else {
            if (defined('REPORT_LOGLIVE_REFRESH')) {
                                $this->refresh = REPORT_LOGLIVE_REFERESH;
            } else {
                                $this->refresh = 60;
            }
        }
    }

    
    public function get_refresh_rate() {
        return $this->refresh;
    }

    
    public function get_table($ajax = false) {
        if (empty($this->tablelog)) {
            if ($ajax) {
                $this->setup_table_ajax();
            } else {
                $this->setup_table();
            }
        }
        return $this->tablelog;
    }
}
