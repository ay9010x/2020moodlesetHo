<?php



defined('MOODLE_INTERNAL') || die;


function booktool_importhtml_extend_settings_navigation(settings_navigation $settings, navigation_node $node) {
    global $PAGE;

    if (has_capability('booktool/importhtml:import', $PAGE->cm->context)) {
        $url = new moodle_url('/mod/book/tool/importhtml/index.php', array('id'=>$PAGE->cm->id));
        $node->add(get_string('import', 'booktool_importhtml'), $url, navigation_node::TYPE_SETTING, null, null, null);
    }
}
