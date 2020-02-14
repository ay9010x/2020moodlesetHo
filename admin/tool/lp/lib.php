<?php



defined('MOODLE_INTERNAL') || die();


function tool_lp_extend_navigation_course($navigation, $course, $coursecontext) {
    if (!get_config('core_competency', 'enabled')) {
        return;
    }

        $capabilities = array('moodle/competency:coursecompetencyview', 'moodle/competency:coursecompetencymanage');
    $context = context_course::instance($course->id);
    if (!has_any_capability($capabilities, $context) || !can_access_course($course)) {
        return;
    }

        $title = get_string('competencies', 'core_competency');
    $path = new moodle_url("/admin/tool/lp/coursecompetencies.php", array('courseid' => $course->id));
    $settingsnode = navigation_node::create($title,
                                            $path,
                                            navigation_node::TYPE_SETTING,
                                            null,
                                            null,
                                            new pix_icon('i/competencies', ''));
    if (isset($settingsnode)) {
        $navigation->add_node($settingsnode);
    }
}



function tool_lp_extend_navigation_user($navigation, $user, $usercontext, $course, $coursecontext) {
    if (!get_config('core_competency', 'enabled')) {
        return;
    }

    if (\core_competency\plan::can_read_user($user->id)) {
        $node = $navigation->add(get_string('learningplans', 'tool_lp'),
            new moodle_url('/admin/tool/lp/plans.php', array('userid' => $user->id)));

        if (\core_competency\user_evidence::can_read_user($user->id)) {
            $node->add(get_string('userevidence', 'tool_lp'),
                new moodle_url('/admin/tool/lp/user_evidence_list.php', array('userid' => $user->id)));
        }
    }

}


function tool_lp_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    if (!get_config('core_competency', 'enabled')) {
        return false;
    } else if (!\core_competency\plan::can_read_user($user->id)) {
        return false;
    }

    $url = new moodle_url('/admin/tool/lp/plans.php', array('userid' => $user->id));
    $node = new core_user\output\myprofile\node('miscellaneous', 'learningplans',
                                                get_string('learningplans', 'tool_lp'), null, $url);
    $tree->add_node($node);

    return true;
}


function tool_lp_extend_navigation_category_settings($navigation, $coursecategorycontext) {
    if (!get_config('core_competency', 'enabled')) {
        return false;
    }

        $templatereadcapability = \core_competency\template::can_read_context($coursecategorycontext);
    $competencyreadcapability = \core_competency\competency_framework::can_read_context($coursecategorycontext);
    if (!$templatereadcapability && !$competencyreadcapability) {
        return false;
    }

        if ($templatereadcapability) {
        $title = get_string('templates', 'tool_lp');
        $path = new moodle_url("/admin/tool/lp/learningplans.php", array('pagecontextid' => $coursecategorycontext->id));
        $settingsnode = navigation_node::create($title,
                                                $path,
                                                navigation_node::TYPE_SETTING,
                                                null,
                                                null,
                                                new pix_icon('i/competencies', ''));
        if (isset($settingsnode)) {
            $navigation->add_node($settingsnode);
        }
    }

        if ($competencyreadcapability) {
        $title = get_string('competencyframeworks', 'tool_lp');
        $path = new moodle_url("/admin/tool/lp/competencyframeworks.php", array('pagecontextid' => $coursecategorycontext->id));
        $settingsnode = navigation_node::create($title,
                                                $path,
                                                navigation_node::TYPE_SETTING,
                                                null,
                                                null,
                                                new pix_icon('i/competencies', ''));
        if (isset($settingsnode)) {
            $navigation->add_node($settingsnode);
        }
    }
}


function tool_lp_coursemodule_standard_elements($formwrapper, $mform) {
    global $CFG, $COURSE;

    if (!get_config('core_competency', 'enabled')) {
        return;
    } else if (!has_capability('moodle/competency:coursecompetencymanage', $formwrapper->get_context())) {
        return;
    }

    $mform->addElement('header', 'competenciessection', get_string('competencies', 'core_competency'));

    MoodleQuickForm::registerElementType('course_competencies',
                                         "$CFG->dirroot/$CFG->admin/tool/lp/classes/course_competencies_form_element.php",
                                         'tool_lp_course_competencies_form_element');
    $cmid = null;
    if ($cm = $formwrapper->get_coursemodule()) {
        $cmid = $cm->id;
    }
    $options = array(
        'courseid' => $COURSE->id,
        'cmid' => $cmid
    );
    $mform->addElement('course_competencies', 'competencies', get_string('modcompetencies', 'tool_lp'), $options);
    $mform->addHelpButton('competencies', 'modcompetencies', 'tool_lp');
    MoodleQuickForm::registerElementType('course_competency_rule',
                                         "$CFG->dirroot/$CFG->admin/tool/lp/classes/course_competency_rule_form_element.php",
                                         'tool_lp_course_competency_rule_form_element');
        $mform->addElement('course_competency_rule', 'competency_rule', get_string('uponcoursemodulecompletion', 'tool_lp'), $options);
}


function tool_lp_coursemodule_edit_post_actions($data, $course) {
    if (!get_config('core_competency', 'enabled')) {
        return $data;
    }

        if (!isset($data->competency_rule) && !isset($data->competencies)) {
        return $data;
    }

            $existing = \core_competency\course_module_competency::list_course_module_competencies($data->coursemodule);

    $existingids = array();
    foreach ($existing as $cmc) {
        array_push($existingids, $cmc->get_competencyid());
    }

    $newids = isset($data->competencies) ? $data->competencies : array();

    $removed = array_diff($existingids, $newids);
    $added = array_diff($newids, $existingids);

    foreach ($removed as $removedid) {
        \core_competency\api::remove_competency_from_course_module($data->coursemodule, $removedid);
    }
    foreach ($added as $addedid) {
        \core_competency\api::add_competency_to_course_module($data->coursemodule, $addedid);
    }

    if (isset($data->competency_rule)) {
                $current = \core_competency\api::list_course_module_competencies_in_course_module($data->coursemodule);
        foreach ($current as $coursemodulecompetency) {
            \core_competency\api::set_course_module_competency_ruleoutcome($coursemodulecompetency, $data->competency_rule);
        }
    }

    return $data;
}
