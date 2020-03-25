<?php



namespace tool_cohortroles\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;
use renderable;


class renderer extends plugin_renderer_base {

    
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
