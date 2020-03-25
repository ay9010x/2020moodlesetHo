<?php



defined('MOODLE_INTERNAL') || die;


function booktool_exportimscp_extend_settings_navigation(settings_navigation $settings, navigation_node $node) {
    global $PAGE;

    if (has_capability('booktool/exportimscp:export', $PAGE->cm->context)) {
        $url = new moodle_url('/mod/book/tool/exportimscp/index.php', array('id'=>$PAGE->cm->id));
        $icon = new pix_icon('generate', '', 'booktool_exportimscp', array('class'=>'icon'));
        $node->add(get_string('generateimscp', 'booktool_exportimscp'), $url, navigation_node::TYPE_SETTING, null, null, $icon);
    }
}
