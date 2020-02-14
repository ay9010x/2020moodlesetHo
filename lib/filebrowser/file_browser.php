<?php




defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/filebrowser/file_info.php");

require_once("$CFG->libdir/filebrowser/file_info_stored.php");
require_once("$CFG->libdir/filebrowser/virtual_root_file.php");

require_once("$CFG->libdir/filebrowser/file_info_context_system.php");
require_once("$CFG->libdir/filebrowser/file_info_context_user.php");
require_once("$CFG->libdir/filebrowser/file_info_context_coursecat.php");
require_once("$CFG->libdir/filebrowser/file_info_context_course.php");
require_once("$CFG->libdir/filebrowser/file_info_context_module.php");


class file_browser {

    
    public function get_file_info($context = NULL, $component = NULL, $filearea = NULL, $itemid = NULL, $filepath = NULL, $filename = NULL) {
        if (!$context) {
            $context = context_system::instance();
        }
        switch ($context->contextlevel) {
            case CONTEXT_SYSTEM:
                return $this->get_file_info_context_system($context, $component, $filearea, $itemid, $filepath, $filename);
            case CONTEXT_USER:
                return $this->get_file_info_context_user($context, $component, $filearea, $itemid, $filepath, $filename);
            case CONTEXT_COURSECAT:
                return $this->get_file_info_context_coursecat($context, $component, $filearea, $itemid, $filepath, $filename);
            case CONTEXT_COURSE:
                return $this->get_file_info_context_course($context, $component, $filearea, $itemid, $filepath, $filename);
            case CONTEXT_MODULE:
                return $this->get_file_info_context_module($context, $component, $filearea, $itemid, $filepath, $filename);
        }

        return null;
    }

    
    private function get_file_info_context_system($context, $component, $filearea, $itemid, $filepath, $filename) {
        $level = new file_info_context_system($this, $context);
        return $level->get_file_info($component, $filearea, $itemid, $filepath, $filename);
            }

    
    private function get_file_info_context_user($context, $component, $filearea, $itemid, $filepath, $filename) {
        global $DB, $USER;
        if ($context->instanceid == $USER->id) {
            $user = $USER;
        } else {
            $user = $DB->get_record('user', array('id'=>$context->instanceid));
        }

        if (isguestuser($user)) {
                        return null;
        }

        if ($user->deleted) {
            return null;
        }

        $level = new file_info_context_user($this, $context, $user);
        return $level->get_file_info($component, $filearea, $itemid, $filepath, $filename);
    }

    
    private function get_file_info_context_coursecat($context, $component, $filearea, $itemid, $filepath, $filename) {
        global $DB;

        if (!$category = $DB->get_record('course_categories', array('id'=>$context->instanceid))) {
            return null;
        }

        $level = new file_info_context_coursecat($this, $context, $category);
        return $level->get_file_info($component, $filearea, $itemid, $filepath, $filename);
    }

    
    private function get_file_info_context_course($context, $component, $filearea, $itemid, $filepath, $filename) {
        global $DB, $COURSE;

        if ($context->instanceid == $COURSE->id) {
            $course = $COURSE;
        } else if (!$course = $DB->get_record('course', array('id'=>$context->instanceid))) {
            return null;
        }

        $level = new file_info_context_course($this, $context, $course);
        return $level->get_file_info($component, $filearea, $itemid, $filepath, $filename);
    }

    
    private function get_file_info_context_module($context, $component, $filearea, $itemid, $filepath, $filename) {
        global $COURSE, $DB, $CFG;

        static $cachedmodules = array();

        if (!array_key_exists($context->instanceid, $cachedmodules)) {
            $cachedmodules[$context->instanceid] = get_coursemodule_from_id('', $context->instanceid);
        }

        if (!($cm = $cachedmodules[$context->instanceid])) {
            return null;
        }

        if ($cm->course == $COURSE->id) {
            $course = $COURSE;
        } else if (!$course = $DB->get_record('course', array('id'=>$cm->course))) {
            return null;
        }

        $modinfo = get_fast_modinfo($course);
        if (empty($modinfo->cms[$cm->id]->uservisible)) {
            return null;
        }

        $modname = $modinfo->cms[$cm->id]->modname;

        if (!file_exists("$CFG->dirroot/mod/$modname/lib.php")) {
            return null;
        }

        
        $level = new file_info_context_module($this, $context, $course, $cm, $modname);
        return $level->get_file_info($component, $filearea, $itemid, $filepath, $filename);
    }

}
