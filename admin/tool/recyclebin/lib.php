<?php



defined('MOODLE_INTERNAL') || die;


function tool_recyclebin_extend_navigation_course($navigation, $course, $context) {
    global $PAGE;

        if (!$PAGE->course || $PAGE->course->id == SITEID || !\tool_recyclebin\course_bin::is_enabled()) {
        return null;
    }

    $coursebin = new \tool_recyclebin\course_bin($context->instanceid);

        if (!$coursebin->can_view()) {
        return null;
    }

    $url = null;
    $settingnode = null;

    $url = new moodle_url('/admin/tool/recyclebin/index.php', array(
        'contextid' => $context->id
    ));

        $autohide = get_config('tool_recyclebin', 'autohide');
    if ($autohide) {
        $items = $coursebin->get_items();
        if (empty($items)) {
            return null;
        }
    }

        $pluginname = get_string('pluginname', 'tool_recyclebin');

    $node = navigation_node::create(
        $pluginname,
        $url,
        navigation_node::NODETYPE_LEAF,
        'tool_recyclebin',
        'tool_recyclebin',
        new pix_icon('trash', $pluginname, 'tool_recyclebin')
    );

    if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
        $node->make_active();
    }

    $navigation->add_node($node);
}


function tool_recyclebin_extend_navigation_category_settings($navigation, $context) {
    global $PAGE;

        if (!\tool_recyclebin\category_bin::is_enabled()) {
        return null;
    }

    $categorybin = new \tool_recyclebin\category_bin($context->instanceid);

        if (!$categorybin->can_view()) {
        return null;
    }

    $url = null;
    $settingnode = null;

        $url = new moodle_url('/admin/tool/recyclebin/index.php', array(
        'contextid' => $context->id
    ));

        $autohide = get_config('tool_recyclebin', 'autohide');
    if ($autohide) {
        $items = $categorybin->get_items();
        if (empty($items)) {
            return null;
        }
    }

        $pluginname = get_string('pluginname', 'tool_recyclebin');

    $node = navigation_node::create(
        $pluginname,
        $url,
        navigation_node::NODETYPE_LEAF,
        'tool_recyclebin',
        'tool_recyclebin',
        new pix_icon('trash', $pluginname, 'tool_recyclebin')
    );

    if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
        $node->make_active();
    }

    $navigation->add_node($node);
}


function tool_recyclebin_pre_course_module_delete($cm) {
    if (\tool_recyclebin\course_bin::is_enabled()) {
        $coursebin = new \tool_recyclebin\course_bin($cm->course);
        $coursebin->store_item($cm);
    }
}


function tool_recyclebin_pre_course_delete($course) {
                if (isset($course->deletesource) && $course->deletesource == 'restore') {
        return;
    }
            $coursebin = new \tool_recyclebin\course_bin($course->id);
    $coursebin->delete_all_items();

    if (\tool_recyclebin\category_bin::is_enabled()) {
        $categorybin = new \tool_recyclebin\category_bin($course->category);
        $categorybin->store_item($course);
    }
}


function tool_recyclebin_pre_course_category_delete($category) {
            $categorybin = new \tool_recyclebin\category_bin($category->id);
    $categorybin->delete_all_items();
}
