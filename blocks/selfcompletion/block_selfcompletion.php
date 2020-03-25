<?php



require_once($CFG->libdir.'/completionlib.php');


class block_selfcompletion extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_selfcompletion');
    }

    function applicable_formats() {
        return array('all' => true, 'mod' => false, 'tag' => false, 'my' => false);
    }

    public function get_content() {
        global $CFG, $USER;

                if ($this->content !== NULL) {
          return $this->content;
        }

                $this->content = new stdClass;

                $can_edit = has_capability('moodle/course:update', context_course::instance($this->page->course->id));

                $info = new completion_info($this->page->course);

                if (!completion_info::is_enabled_for_site()) {
            if ($can_edit) {
                $this->content->text = get_string('completionnotenabledforsite', 'completion');
            }
            return $this->content;

        } else if (!$info->is_enabled()) {
            if ($can_edit) {
                $this->content->text = get_string('completionnotenabledforcourse', 'completion');
            }
            return $this->content;
        }

                $completion = $info->get_completion($USER->id, COMPLETION_CRITERIA_TYPE_SELF);

                if (empty($completion)) {
            if ($can_edit) {
                $this->content->text = get_string('selfcompletionnotenabled', 'block_selfcompletion');
            }
            return $this->content;
        }

                if (!$info->is_tracked_user($USER->id)) {
            $this->content->text = get_string('nottracked', 'completion');
            return $this->content;
        }

                if ($info->is_course_complete($USER->id)) {
            $this->content->text = get_string('coursealreadycompleted', 'completion');
            return $this->content;

                } else if ($completion->is_complete()) {
            $this->content->text = get_string('alreadyselfcompleted', 'block_selfcompletion');
            return $this->content;

                } else {
            $this->content->text = '';
            $this->content->footer = '<br /><a href="'.$CFG->wwwroot.'/course/togglecompletion.php?course='.$this->page->course->id.'">';
            $this->content->footer .= get_string('completecourse', 'block_selfcompletion').'</a>...';
        }

        return $this->content;
    }
}
