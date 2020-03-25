<?php



defined('MOODLE_INTERNAL') || die();


function core_role_get_potential_user_selector(context $context, $name, $options) {
    $blockinsidecourse = false;
    if ($context->contextlevel == CONTEXT_BLOCK) {
        $parentcontext = $context->get_parent_context();
        $blockinsidecourse = in_array($parentcontext->contextlevel, array(CONTEXT_MODULE, CONTEXT_COURSE));
    }

    if (($context->contextlevel == CONTEXT_MODULE || $blockinsidecourse) &&
            !is_inside_frontpage($context)) {
        $potentialuserselector = new core_role_potential_assignees_below_course('addselect', $options);
    } else {
        $potentialuserselector = new core_role_potential_assignees_course_and_above('addselect', $options);
    }

    return $potentialuserselector;
}
