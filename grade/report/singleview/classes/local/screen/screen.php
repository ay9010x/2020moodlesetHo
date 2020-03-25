<?php



namespace gradereport_singleview\local\screen;

use context_course;
use moodle_url;
use html_writer;
use grade_structure;
use grade_grade;
use grade_item;
use stdClass;

defined('MOODLE_INTERNAL') || die;


abstract class screen {

    
    protected $courseid;

    
    protected $itemid;

    
    protected $groupid;

    
    protected $context;

    
    protected $page;

    
    protected $perpage;

    
    protected $items;

    
    public function __construct($courseid, $itemid, $groupid = null) {
        global $DB;

        $this->courseid = $courseid;
        $this->itemid = $itemid;
        $this->groupid = $groupid;

        $this->context = context_course::instance($this->courseid);
        $this->course = $DB->get_record('course', array('id' => $courseid));

        $this->page = optional_param('page', 0, PARAM_INT);
        $this->perpage = optional_param('perpage', 100, PARAM_INT);
        if ($this->perpage > 100) {
            $this->perpage = 100;
        }

        $this->init(empty($itemid));
    }

    
    public function setup_structure() {
        $this->structure = new grade_structure();
        $this->structure->modinfo = get_fast_modinfo($this->course);
    }

    
    public function format_link($screen, $itemid, $display = null) {
        $url = new moodle_url('/grade/report/singleview/index.php', array(
            'id' => $this->courseid,
            'item' => $screen,
            'itemid' => $itemid,
            'group' => $this->groupid,
        ));

        if ($display) {
            return html_writer::link($url, $display);
        } else {
            return $url;
        }
    }

    
    public function fetch_grade_or_default($item, $userid) {
        $grade = grade_grade::fetch(array(
            'itemid' => $item->id, 'userid' => $userid
        ));

        if (!$grade) {
            $default = new stdClass;

            $default->userid = $userid;
            $default->itemid = $item->id;
            $default->feedback = '';

            $grade = new grade_grade($default, false);
        }

        $grade->grade_item = $item;

        return $grade;
    }

    
    public function make_toggle($key) {
        $attrs = array('href' => '#');

                $strmanager = \get_string_manager();
        $titleall = get_string('all');
        $titlenone = get_string('none');
        if ($strmanager->string_exists(strtolower($key) . 'all', 'gradereport_singleview')) {
            $titleall = get_string(strtolower($key) . 'all', 'gradereport_singleview');
        }
        if ($strmanager->string_exists(strtolower($key) . 'none', 'gradereport_singleview')) {
            $titlenone = get_string(strtolower($key) . 'none', 'gradereport_singleview');
        }

        $all = html_writer::tag('a', get_string('all'), $attrs + array(
            'class' => 'include all ' . $key,
            'title' => $titleall
        ));

        $none = html_writer::tag('a', get_string('none'), $attrs + array(
            'class' => 'include none ' . $key,
            'title' => $titlenone
        ));

        return html_writer::tag('span', "$all / $none", array(
            'class' => 'inclusion_links'
        ));
    }

    
    public function make_toggle_links($key) {
        return get_string($key, 'gradereport_singleview') . ' ' .
            $this->make_toggle($key);
    }

    
    public function heading() {
        return get_string('entrypage', 'gradereport_singleview');
    }

    
    public abstract function init($selfitemisempty = false);

    
    public abstract function item_type();

    
    public abstract function html();

    
    public function supports_paging() {
        return true;
    }

    
    public function pager() {
        return '';
    }

    
    public function js() {
        global $PAGE;

        $module = array(
            'name' => 'gradereport_singleview',
            'fullpath' => '/grade/report/singleview/js/singleview.js',
            'requires' => array('base', 'dom', 'event', 'event-simulate', 'io-base')
        );

        $PAGE->requires->js_init_call('M.gradereport_singleview.init', array(), false, $module);
    }

    
    public function process($data) {
        $warnings = array();

        $fields = $this->definition();

                        $progress = 0;
        $progressbar = new \core\progress\display_if_slow();
        $progressbar->start_html();
        $progressbar->start_progress(get_string('savegrades', 'gradereport_singleview'), count((array) $data) - 1);
        $changecount = array();

        foreach ($data as $varname => $throw) {
            $progressbar->progress($progress);
            $progress++;
            if (preg_match("/(\w+)_(\d+)_(\d+)/", $varname, $matches)) {
                $itemid = $matches[2];
                $userid = $matches[3];
            } else {
                continue;
            }

            $gradeitem = grade_item::fetch(array(
                'id' => $itemid, 'courseid' => $this->courseid
            ));

            if (preg_match('/^old[oe]{1}/', $varname)) {
                $elementname = preg_replace('/^old/', '', $varname);
                if (!isset($data->$elementname)) {
                                                            $progress--;
                    $data->$elementname = false;
                }
            }

            if (!in_array($matches[1], $fields)) {
                continue;
            }

            if (!$gradeitem) {
                continue;
            }

            $grade = $this->fetch_grade_or_default($gradeitem, $userid);

            $classname = '\\gradereport_singleview\\local\\ui\\' . $matches[1];
            $element = new $classname($grade);

            $name = $element->get_name();
            $oldname = "old$name";

            $posted = $data->$name;

            $format = $element->determine_format();

            if ($format->is_textbox() and trim($data->$name) === '') {
                $data->$name = null;
            }

                        if (isset($data->$oldname) && $data->$oldname == $posted) {
                continue;
            }

                                    if ($matches[1] === 'exclude' && !has_capability('moodle/grade:manage', $this->context)){
                $warnings[] = get_string('nopermissions', 'error', get_string('grade:manage', 'role'));
                continue;
            }

            $msg = $element->set($posted);

                        if (!empty($msg)) {
                $warnings[] = $msg;
            }
            if (preg_match('/_(\d+)_(\d+)/', $varname, $matchelement)) {
                $changecount[$matchelement[0]] = 1;
            }
        }

                $eventdata = new stdClass;
        $eventdata->warnings = $warnings;
        $eventdata->post_data = $data;
        $eventdata->instance = $this;
        $eventdata->changecount = $changecount;

        $progressbar->end_html();

        return $eventdata;
    }

    
    public function options() {
        return array();
    }

    
    public function display_group_selector() {
        return true;
    }

    
    public function supports_next_prev() {
        return true;
    }

    
    protected function load_users() {
        global $CFG;

                $defaultgradeshowactiveenrol = !empty($CFG->grade_report_showonlyactiveenrol);
        $showonlyactiveenrol = get_user_preferences('grade_report_showonlyactiveenrol', $defaultgradeshowactiveenrol);
        $showonlyactiveenrol = $showonlyactiveenrol || !has_capability('moodle/course:viewsuspendedusers', $this->context);

        require_once($CFG->dirroot.'/grade/lib.php');
        $gui = new \graded_users_iterator($this->course, null, $this->groupid);
        $gui->require_active_enrolment($showonlyactiveenrol);
        $gui->init();

                $users = array();
        while ($user = $gui->next_user()) {
            $users[$user->user->id] = $user->user;
        }
        return $users;
    }
}
