<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/badgeslib.php");


class block_badges extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_badges');
    }

    public function instance_allow_multiple() {
        return true;
    }

    public function has_config() {
        return false;
    }

    public function instance_allow_config() {
        return true;
    }

    public function applicable_formats() {
        return array(
                'admin' => false,
                'site-index' => true,
                'course-view' => true,
                'mod' => false,
                'my' => true
        );
    }

    public function specialization() {
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_badges');
        } else {
            $this->title = $this->config->title;
        }
    }

    public function get_content() {
        global $USER, $PAGE, $CFG;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->config)) {
            $this->config = new stdClass();
        }

                if (!isset($this->config->numberofbadges)) {
            $this->config->numberofbadges = 10;
        }

                $this->content = new stdClass();
        $this->content->text = '';

        if (empty($CFG->enablebadges)) {
            $this->content->text .= get_string('badgesdisabled', 'badges');
            return $this->content;
        }

        $courseid = $this->page->course->id;
        if ($courseid == SITEID) {
            $courseid = null;
        }

        if ($badges = badges_get_user_badges($USER->id, $courseid, 0, $this->config->numberofbadges)) {
            $output = $this->page->get_renderer('core', 'badges');
            $this->content->text = $output->print_badges_list($badges, $USER->id, true);
        } else {
            $this->content->text .= get_string('nothingtodisplay', 'block_badges');
        }

        return $this->content;
    }
}