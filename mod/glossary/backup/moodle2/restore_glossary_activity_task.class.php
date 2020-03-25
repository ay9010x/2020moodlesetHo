<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/glossary/backup/moodle2/restore_glossary_stepslib.php'); 

class restore_glossary_activity_task extends restore_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new restore_glossary_activity_structure_step('glossary_structure', 'glossary.xml'));
    }

    
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('glossary', array('intro'), 'glossary');
        $contents[] = new restore_decode_content('glossary_entries', array('definition'), 'glossary_entry');

        return $contents;
    }

    
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('GLOSSARYVIEWBYID', '/mod/glossary/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('GLOSSARYINDEX', '/mod/glossary/index.php?id=$1', 'course');
        $rules[] = new restore_decode_rule('GLOSSARYSHOWENTRY', '/mod/glossary/showentry.php?courseid=$1&eid=$2',
                                           array('course', 'glossary_entry'));

        return $rules;

    }

    
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('glossary', 'add', 'view.php?id={course_module}', '{glossary}');
        $rules[] = new restore_log_rule('glossary', 'update', 'view.php?id={course_module}', '{glossary}');
        $rules[] = new restore_log_rule('glossary', 'view', 'view.php?id={course_module}', '{glossary}');
        $rules[] = new restore_log_rule('glossary', 'add category', 'editcategories.php?id={course_module}', '{glossary_category}');
        $rules[] = new restore_log_rule('glossary', 'edit category', 'editcategories.php?id={course_module}', '{glossary_category}');
        $rules[] = new restore_log_rule('glossary', 'delete category', 'editcategories.php?id={course_module}', '{glossary_category}');
        $rules[] = new restore_log_rule('glossary', 'add entry', 'view.php?id={course_module}&mode=entry&hook={glossary_entry}', '{glossary_entry}');
        $rules[] = new restore_log_rule('glossary', 'update entry', 'view.php?id={course_module}&mode=entry&hook={glossary_entry}', '{glossary_entry}');
        $rules[] = new restore_log_rule('glossary', 'delete entry', 'view.php?id={course_module}&mode=entry&hook={glossary_entry}', '{glossary_entry}');
        $rules[] = new restore_log_rule('glossary', 'approve entry', 'showentry.php?id={course_module}&eid={glossary_entry}', '{glossary_entry}');
        $rules[] = new restore_log_rule('glossary', 'disapprove entry', 'showentry.php?id={course_module}&eid={glossary_entry}', '{glossary_entry}');
        $rules[] = new restore_log_rule('glossary', 'view entry', 'showentry.php?eid={glossary_entry}', '{glossary_entry}');

        return $rules;
    }

    
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('glossary', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
