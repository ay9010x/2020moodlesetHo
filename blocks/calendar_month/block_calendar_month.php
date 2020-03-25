<?php


class block_calendar_month extends block_base {

    
    public function init() {
        $this->title = get_string('pluginname', 'block_calendar_month');
    }

    
    public function get_content() {
        global $CFG;

        $calm = optional_param('cal_m', 0, PARAM_INT);
        $caly = optional_param('cal_y', 0, PARAM_INT);
        $time = optional_param('time', 0, PARAM_INT);

        require_once($CFG->dirroot.'/calendar/lib.php');

        if ($this->content !== null) {
            return $this->content;
        }

                                if (!empty($calm) && (!empty($caly))) {
            $time = make_timestamp($caly, $calm, 1);
        } else if (empty($time)) {
            $time = time();
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

                                $courseid = $this->page->course->id;
        $issite = ($courseid == SITEID);

        if ($issite) {
                                    $filtercourse = calendar_get_default_courses();
        } else {
                        $filtercourse = array($courseid => $this->page->course);
        }

        list($courses, $group, $user) = calendar_set_filters($filtercourse);
        if ($issite) {
                        $this->content->text .= calendar_get_mini($courses, $group, $user, false, false, 'frontpage', $courseid, $time);
                    } else {
                        $this->content->text .= calendar_get_mini($courses, $group, $user, false, false, 'course', $courseid, $time);
            $this->content->text .= '<h3 class="eventskey">'.get_string('eventskey', 'calendar').'</h3>';
            $this->content->text .= '<div class="filters calendar_filters">'.calendar_filter_controls($this->page->url).'</div>';
        }

        return $this->content;
    }
}


