<?php



defined('MOODLE_INTERNAL') || die;


function report_competency_extend_navigation_course($navigation, $course, $context) {
    if (!get_config('core_competency', 'enabled')) {
        return;
    }

    if (has_capability('moodle/competency:coursecompetencyview', $context)) {
        $url = new moodle_url('/report/competency/index.php', array('id' => $course->id));
        $name = get_string('pluginname', 'report_competency');
        $navigation->add($name, $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}
