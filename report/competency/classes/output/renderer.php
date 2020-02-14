<?php



namespace report_competency\output;

defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;
use renderable;


class renderer extends plugin_renderer_base {

    
    public function render_report(report $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('report_competency/report', $data);
    }

    
    public function render_user_course_navigation(user_course_navigation $nav) {
        $data = $nav->export_for_template($this);
        return parent::render_from_template('report_competency/user_course_navigation', $data);
    }

    
    public function notify_message($message) {
        $n = new \core\output\notification($message, \core\output\notification::NOTIFY_INFO);
        return $this->render($n);
    }

    
    public function notify_problem($message) {
        $n = new \core\output\notification($message, \core\output\notification::NOTIFY_ERROR);
        return $this->render($n);
    }

    
    public function notify_success($message) {
        $n = new \core\output\notification($message, \core\output\notification::NOTIFY_SUCCESS);
        return $this->render($n);
    }
}
