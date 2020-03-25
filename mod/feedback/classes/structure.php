<?php



defined('MOODLE_INTERNAL') || die();


class mod_feedback_structure {
    
    protected $feedback;
    
    protected $cm;
    
    protected $courseid = 0;
    
    protected $templateid;
    
    protected $allitems;
    
    protected $allcourses;

    
    public function __construct($feedback, $cm, $courseid = 0, $templateid = null) {
        $this->feedback = $feedback;
        $this->cm = $cm;
        $this->courseid = ($feedback->course == SITEID) ? $courseid : 0;
        $this->templateid = $templateid;
    }

    
    public function get_feedback() {
        return $this->feedback;
    }

    
    public function get_cm() {
        return $this->cm;
    }

    
    public function get_courseid() {
        return $this->courseid;
    }

    
    public function get_templateid() {
        return $this->templateid;
    }

    
    public function is_open() {
        $checktime = time();
        return (!$this->feedback->timeopen || $this->feedback->timeopen <= $checktime) &&
            (!$this->feedback->timeclose || $this->feedback->timeclose >= $checktime);
    }

    
    public function get_items($hasvalueonly = false) {
        global $DB;
        if ($this->allitems === null) {
            if ($this->templateid) {
                $this->allitems = $DB->get_records('feedback_item', ['template' => $this->templateid], 'position');
            } else {
                $this->allitems = $DB->get_records('feedback_item', ['feedback' => $this->feedback->id], 'position');
            }
            $idx = 1;
            foreach ($this->allitems as $id => $item) {
                $this->allitems[$id]->itemnr = $item->hasvalue ? ($idx++) : null;
            }
        }
        if ($hasvalueonly && $this->allitems) {
            return array_filter($this->allitems, function($item) {
                return $item->hasvalue;
            });
        }
        return $this->allitems;
    }

    
    public function is_empty() {
        $items = $this->get_items();
        $displayeditems = array_filter($items, function($item) {
            return $item->typ !== 'pagebreak';
        });
        return !$displayeditems;
    }

    
    public function is_anonymous() {
        return $this->feedback->anonymous == FEEDBACK_ANONYMOUS_YES;
    }

    
    public function page_after_submit() {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $pageaftersubmit = $this->get_feedback()->page_after_submit;
        if (empty($pageaftersubmit)) {
            return null;
        }
        $pageaftersubmitformat = $this->get_feedback()->page_after_submitformat;

        $context = context_module::instance($this->get_cm()->id);
        $output = file_rewrite_pluginfile_urls($pageaftersubmit,
                'pluginfile.php', $context->id, 'mod_feedback', 'page_after_submit', 0);

        return format_text($output, $pageaftersubmitformat, array('overflowdiv' => true));
    }

    
    public function can_view_analysis() {
        $context = context_module::instance($this->cm->id);
        if (has_capability('mod/feedback:viewreports', $context)) {
            return true;
        }

        if (intval($this->feedback->publish_stats) != 1 ||
                !has_capability('mod/feedback:viewanalysepage', $context)) {
            return false;
        }

        if (!isloggedin() || isguestuser()) {
                        return $this->feedback->course == SITEID;
        }

        return $this->is_already_submitted(true);
    }

    
    public function is_already_submitted($anycourseid = false) {
        global $USER, $DB;

        if (!isloggedin() || isguestuser()) {
            return false;
        }

        $params = array('userid' => $USER->id, 'feedback' => $this->feedback->id);
        if (!$anycourseid && $this->courseid) {
            $params['courseid'] = $this->courseid;
        }
        return $DB->record_exists('feedback_completed', $params);
    }

    
    public function check_course_is_mapped() {
        global $DB;
        if ($this->feedback->course != SITEID) {
            return true;
        }
        if ($DB->get_records('feedback_sitecourse_map', array('feedbackid' => $this->feedback->id))) {
            $params = array('feedbackid' => $this->feedback->id, 'courseid' => $this->courseid);
            if (!$DB->get_record('feedback_sitecourse_map', $params)) {
                return false;
            }
        }
                return true;
    }

    
    public function shuffle_anonym_responses() {
        global $DB;
        $params = array('feedback' => $this->feedback->id,
            'random_response' => 0,
            'anonymous_response' => FEEDBACK_ANONYMOUS_YES);

        if ($DB->count_records('feedback_completed', $params, 'random_response')) {
                        unset($params['random_response']);
            $feedbackcompleteds = $DB->get_records('feedback_completed', $params, 'id');
            shuffle($feedbackcompleteds);
            $num = 1;
            foreach ($feedbackcompleteds as $compl) {
                $compl->random_response = $num++;
                $DB->update_record('feedback_completed', $compl);
            }
        }
    }

    
    public function count_completed_responses($groupid = 0) {
        global $DB;
        if (intval($groupid) > 0) {
            $query = "SELECT COUNT(DISTINCT fbc.id)
                        FROM {feedback_completed} fbc, {groups_members} gm
                        WHERE fbc.feedback = :feedback
                            AND gm.groupid = :groupid
                            AND fbc.userid = gm.userid";
        } else if ($this->courseid) {
            $query = "SELECT COUNT(fbc.id)
                        FROM {feedback_completed} fbc
                        WHERE fbc.feedback = :feedback
                            AND fbc.courseid = :courseid";
        } else {
            $query = "SELECT COUNT(fbc.id) FROM {feedback_completed} fbc WHERE fbc.feedback = :feedback";
        }
        $params = ['feedback' => $this->feedback->id, 'groupid' => $groupid, 'courseid' => $this->courseid];
        return $DB->get_field_sql($query, $params);
    }

    
    public function get_completed_courses() {
        global $DB;

        if ($this->get_feedback()->course != SITEID) {
            return [];
        }

        if ($this->allcourses !== null) {
            return $this->allcourses;
        }

        $courseselect = "SELECT fbc.courseid
            FROM {feedback_completed} fbc
            WHERE fbc.feedback = :feedbackid";

        $ctxselect = context_helper::get_preload_record_columns_sql('ctx');

        $sql = 'SELECT c.id, c.shortname, c.fullname, c.idnumber, c.visible, '. $ctxselect. '
                FROM {course} c
                JOIN {context} ctx ON c.id = ctx.instanceid AND ctx.contextlevel = :contextcourse
                WHERE c.id IN ('. $courseselect.') ORDER BY c.sortorder';
        $list = $DB->get_records_sql($sql, ['contextcourse' => CONTEXT_COURSE, 'feedbackid' => $this->get_feedback()->id]);

        $this->allcourses = array();
        foreach ($list as $course) {
            context_helper::preload_from_record($course);
            if (!$course->visible && !has_capability('moodle/course:viewhiddencourses', context_course::instance($course->id))) {
                                continue;
            }
            $label = get_course_display_name_for_list($course);
            $this->allcourses[$course->id] = $label;
        }
        return $this->allcourses;
    }
}