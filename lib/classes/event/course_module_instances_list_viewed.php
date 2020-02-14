<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


abstract class course_module_instances_list_viewed extends course_module_instance_list_viewed {
}

debugging('core\\event\\course_module_instances_list_viewed has been deperecated. Please use
        core\\event\\course_module_instance_list_viewed instead', DEBUG_DEVELOPER);
