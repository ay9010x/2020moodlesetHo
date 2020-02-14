<?php



defined('MOODLE_INTERNAL') || die;


function booktool_print_extend_settings_navigation(settings_navigation $settings, navigation_node $node) {
    global $USER, $PAGE, $CFG, $DB, $OUTPUT;

    $params = $PAGE->url->params();
    if (empty($params['id']) or empty($params['chapterid'])) {
        return;
    }

    if (has_capability('booktool/print:print', $PAGE->cm->context)) {
        $url1 = new moodle_url('/mod/book/tool/print/index.php', array('id'=>$params['id']));
        $url2 = new moodle_url('/mod/book/tool/print/index.php', array('id'=>$params['id'], 'chapterid'=>$params['chapterid']));
        $action = new action_link($url1, get_string('printbook', 'booktool_print'), new popup_action('click', $url1));
        $node->add(get_string('printbook', 'booktool_print'), $action, navigation_node::TYPE_SETTING, null, null,
                new pix_icon('book', '', 'booktool_print', array('class'=>'icon')));
        $action = new action_link($url2, get_string('printchapter', 'booktool_print'), new popup_action('click', $url2));
        $node->add(get_string('printchapter', 'booktool_print'), $action, navigation_node::TYPE_SETTING, null, null,
                new pix_icon('chapter', '', 'booktool_print', array('class'=>'icon')));
    }
}


function booktool_print_get_view_actions() {
    return array('print');
}
