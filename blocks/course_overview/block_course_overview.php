<?php


require_once($CFG->dirroot.'/blocks/course_overview/locallib.php');


class block_course_overview extends block_base {
    
    const SHOW_ALL_COURSES = -2;

    
    public function init() {
        $this->title   = get_string('pluginname', 'block_course_overview');
    }

    
    public function get_content() {
        global $USER, $CFG, $DB;
        require_once($CFG->dirroot.'/user/profile/lib.php');

        if($this->content !== NULL) {
            return $this->content;
        }

        $config = get_config('block_course_overview');

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        $content = array();

        $updatemynumber = optional_param('mynumber', -1, PARAM_INT);
        if ($updatemynumber >= 0) {
            block_course_overview_update_mynumber($updatemynumber);
        }

        profile_load_custom_fields($USER);

        $showallcourses = ($updatemynumber === self::SHOW_ALL_COURSES);
        list($sortedcourses, $sitecourses, $totalcourses) = block_course_overview_get_sorted_courses($showallcourses);
        $overviews = block_course_overview_get_overviews($sitecourses);

        $renderer = $this->page->get_renderer('block_course_overview');
        if (!empty($config->showwelcomearea)) {
            require_once($CFG->dirroot.'/message/lib.php');
            $msgcount = message_count_unread_messages();
            $this->content->text = $renderer->welcome_area($msgcount);
        }

                if ($this->page->user_is_editing() && empty($config->forcedefaultmaxcourses)) {
            $this->content->text .= $renderer->editing_bar_head($totalcourses);
        }

        if (empty($sortedcourses)) {
            $this->content->text .= get_string('nocourses','my');
        } else {
                        $this->content->text .= $renderer->course_overview($sortedcourses, $overviews);
            $this->content->text .= $renderer->hidden_courses($totalcourses - count($sortedcourses));
        }

        return $this->content;
    }

    
    public function has_config() {
        return true;
    }

    
    public function applicable_formats() {
        return array('my' => true);
    }

    
    public function hide_header() {
                $config = get_config('block_course_overview');
        return !empty($config->showwelcomearea);
    }
}
