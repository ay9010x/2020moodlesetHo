<?php



require_once($CFG->dirroot . '/grade/report/lib.php');
require_once($CFG->libdir.'/tablelib.php');


class grade_report_overview extends grade_report {

    
    public $user;

    
    public $courses;

    
    public $table;

    
    public $showrank;

    
    var $showtotalsifcontainhidden;

    
    public $studentcourseids;

    
    public $teachercourses;

    
    public function __construct($userid, $gpr, $context) {
        global $CFG, $COURSE, $DB;
        parent::__construct($COURSE->id, $gpr, $context);

                $this->user = $DB->get_record('user', array('id' => $userid));

                $this->courses = enrol_get_users_courses($this->user->id, false, 'id, shortname, showgrades');

        $this->showrank = array();
        $this->showrank['any'] = false;

        $this->showtotalsifcontainhidden = array();

        $this->studentcourseids = array();
        $this->teachercourses = array();
        $roleids = explode(',', get_config('moodle', 'gradebookroles'));

        if ($this->courses) {
            foreach ($this->courses as $course) {
                $this->showrank[$course->id] = grade_get_setting($course->id, 'report_overview_showrank', !empty($CFG->grade_report_overview_showrank));
                if ($this->showrank[$course->id]) {
                    $this->showrank['any'] = true;
                }

                $this->showtotalsifcontainhidden[$course->id] = grade_get_setting($course->id, 'report_overview_showtotalsifcontainhidden', $CFG->grade_report_overview_showtotalsifcontainhidden);

                $coursecontext = context_course::instance($course->id);

                foreach ($roleids as $roleid) {
                    if (user_has_role_assignment($userid, $roleid, $coursecontext->id)) {
                        $this->studentcourseids[$course->id] = $course->id;
                                                break;
                    }
                }

                if (has_capability('moodle/grade:viewall', $coursecontext, $userid)) {
                    $this->teachercourses[$course->id] = $course;
                }
            }
        }


                $this->baseurl = $CFG->wwwroot.'/grade/overview/index.php?id='.$userid;
        $this->pbarurl = $this->baseurl;

        $this->setup_table();
    }

    
    public function setup_table() {
        

                if ($this->showrank['any']) {
            $tablecolumns = array('coursename', 'grade', 'rank');
            $tableheaders = array($this->get_lang_string('coursename', 'grades'),
                                  $this->get_lang_string('grade'),
                                  $this->get_lang_string('rank', 'grades'));
        } else {
            $tablecolumns = array('coursename', 'grade');
            $tableheaders = array($this->get_lang_string('coursename', 'grades'),
                                  $this->get_lang_string('grade'));
        }
        $this->table = new flexible_table('grade-report-overview-'.$this->user->id);

        $this->table->define_columns($tablecolumns);
        $this->table->define_headers($tableheaders);
        $this->table->define_baseurl($this->baseurl);

        $this->table->set_attribute('cellspacing', '0');
        $this->table->set_attribute('id', 'overview-grade');
        $this->table->set_attribute('class', 'boxaligncenter generaltable');

        $this->table->setup();
    }

    
    public function fill_table($activitylink = false, $studentcoursesonly = false) {
        global $CFG, $DB, $OUTPUT, $USER;

        if ($studentcoursesonly && count($this->studentcourseids) == 0) {
            return false;
        }

                if ($this->courses) {
            $numusers = $this->get_numusers(false);

            foreach ($this->courses as $course) {
                if (!$course->showgrades) {
                    continue;
                }

                                if ($studentcoursesonly && !isset($this->studentcourseids[$course->id])) {
                    continue;
                }

                $coursecontext = context_course::instance($course->id);

                if (!$course->visible && !has_capability('moodle/course:viewhiddencourses', $coursecontext)) {
                                        continue;
                }

                if (!has_capability('moodle/user:viewuseractivitiesreport', context_user::instance($this->user->id)) &&
                        ((!has_capability('moodle/grade:view', $coursecontext) || $this->user->id != $USER->id) &&
                        !has_capability('moodle/grade:viewall', $coursecontext))) {
                    continue;
                }

                $coursename = format_string(get_course_display_name_for_list($course), true, array('context' => $coursecontext));
                                if ($activitylink) {
                    $courselink = html_writer::link(new moodle_url('/course/user.php', array('mode' => 'grade', 'id' => $course->id,
                        'user' => $this->user->id)), $coursename);
                } else {
                    $courselink = html_writer::link(new moodle_url('/grade/report/user/index.php', array('id' => $course->id,
                        'userid' => $this->user->id)), $coursename);
                }
                $canviewhidden = has_capability('moodle/grade:viewhidden', $coursecontext);

                                $course_item = grade_item::fetch_course_item($course->id);

                                $course_grade = new grade_grade(array('itemid'=>$course_item->id, 'userid'=>$this->user->id));
                $course_grade->grade_item =& $course_item;
                $finalgrade = $course_grade->finalgrade;

                if (!$canviewhidden and !is_null($finalgrade)) {
                    if ($course_grade->is_hidden()) {
                        $finalgrade = null;
                    } else {
                        $adjustedgrade = $this->blank_hidden_total_and_adjust_bounds($course->id,
                                                                                     $course_item,
                                                                                     $finalgrade);

                                                                        $finalgrade = $adjustedgrade['grade'];
                        $course_item->grademax = $adjustedgrade['grademax'];
                        $course_item->grademin = $adjustedgrade['grademin'];
                    }
                } else {
                                                            if (!is_null($finalgrade)) {
                        $course_item->grademin = $course_grade->get_grade_min();
                        $course_item->grademax = $course_grade->get_grade_max();
                    }
                }

                $data = array($courselink, grade_format_gradevalue($finalgrade, $course_item, true));

                if (!$this->showrank['any']) {
                    
                } else if ($this->showrank[$course->id] && !is_null($finalgrade)) {
                                                            $params = array($finalgrade, $course_item->id);
                    $sql = "SELECT COUNT(DISTINCT(userid))
                              FROM {grade_grades}
                             WHERE finalgrade IS NOT NULL AND finalgrade > ?
                                   AND itemid = ?";
                    $rank = $DB->count_records_sql($sql, $params) + 1;

                    $data[] = "$rank/$numusers";

                } else {
                                                            $data[] = '-';
                }

                $this->table->add_data($data);
            }
            return true;

        } else {
            echo $OUTPUT->notification(get_string('notenrolled', 'grades'), 'notifymessage');
            return false;
        }
    }

    
    public function print_table($return=false) {
        ob_start();
        $this->table->print_html();
        $html = ob_get_clean();
        if ($return) {
            return $html;
        } else {
            echo $html;
        }
    }

    
    public function print_teacher_table() {
        $table = new html_table();
        $table->head = array(get_string('coursename', 'grades'));
        $table->data = null;
        foreach ($this->teachercourses as $courseid => $course) {
            $url = new moodle_url('/grade/report/index.php', array('id' => $courseid));
            $table->data[] = array(html_writer::link($url, $course->fullname));
        }
        echo html_writer::table($table);
    }

    
    function process_data($data) {
    }
    function process_action($target, $action) {
    }

    
    public static function supports_mygrades() {
        return true;
    }
}

function grade_report_overview_settings_definition(&$mform) {
    global $CFG;

        $options = array(-1 => get_string('default', 'grades'),
                      0 => get_string('hide'),
                      1 => get_string('show'));

    if (empty($CFG->grade_report_overview_showrank)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_overview_showrank', get_string('showrank', 'grades'), $options);
    $mform->addHelpButton('report_overview_showrank', 'showrank', 'grades');

        $options = array(-1 => get_string('default', 'grades'),
                      GRADE_REPORT_HIDE_TOTAL_IF_CONTAINS_HIDDEN => get_string('hide'),
                      GRADE_REPORT_SHOW_TOTAL_IF_CONTAINS_HIDDEN => get_string('hidetotalshowexhiddenitems', 'grades'),
                      GRADE_REPORT_SHOW_REAL_TOTAL_IF_CONTAINS_HIDDEN => get_string('hidetotalshowinchiddenitems', 'grades') );

    if (!array_key_exists($CFG->grade_report_overview_showtotalsifcontainhidden, $options)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[$CFG->grade_report_overview_showtotalsifcontainhidden]);
    }

    $mform->addElement('select', 'report_overview_showtotalsifcontainhidden', get_string('hidetotalifhiddenitems', 'grades'), $options);
    $mform->addHelpButton('report_overview_showtotalsifcontainhidden', 'hidetotalifhiddenitems', 'grades');
}


