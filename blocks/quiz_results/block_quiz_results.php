<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/lib.php');


class block_quiz_results extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_quiz_results');
    }

    function applicable_formats() {
        return array('mod-quiz' => true);
    }

    function instance_config_save($data, $nolongerused = false) {
        parent::instance_config_save($data);
    }

    function get_content() {
        return $this->content;
    }

    function instance_allow_multiple() {
        return true;
    }
}


