<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/wiki/backup/moodle2/restore_wiki_stepslib.php'); 

class restore_wiki_activity_task extends restore_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new restore_wiki_activity_structure_step('wiki_structure', 'wiki.xml'));
    }

    
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('wiki', array('intro'), 'wiki');
        $contents[] = new restore_decode_content('wiki_versions', array('content'), 'wiki_version');
        $contents[] = new restore_decode_content('wiki_pages', array('cachedcontent'), 'wiki_page');
        return $contents;
    }

    
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('WIKIINDEX', '/mod/wiki/index.php?id=$1', 'course');
        $rules[] = new restore_decode_rule('WIKIVIEWBYID', '/mod/wiki/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('WIKIPAGEBYID', '/mod/wiki/view.php?pageid=$1', 'wiki_page');

        return $rules;

    }

    
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('wiki', 'add', 'view.php?id={course_module}', '{wiki}');
        $rules[] = new restore_log_rule('wiki', 'update', 'view.php?id={course_module}', '{wiki}');
        $rules[] = new restore_log_rule('wiki', 'view', 'view.php?id={course_module}', '{wiki}');
        $rules[] = new restore_log_rule('wiki', 'comments', 'comments.php?id={course_module}', '{wiki}');
        $rules[] = new restore_log_rule('wiki', 'diff', 'diff.php?id={course_module}', '{wiki}');
        $rules[] = new restore_log_rule('wiki', 'edit', 'edit.php?id={course_module}', '{wiki}');
        $rules[] = new restore_log_rule('wiki', 'history', 'history.php?id={course_module}', '{wiki}');
        $rules[] = new restore_log_rule('wiki', 'map', 'map.php?id={course_module}', '{wiki}');
        $rules[] = new restore_log_rule('wiki', 'overridelocks', 'overridelocks.php?id={course_module}', '{wiki}');
                $rules[] = new restore_log_rule('restore', 'restore', 'view.php?id={course_module}', '{wiki}');
        $rules[] = new restore_log_rule('createpage', 'createpage', 'view.php?id={course_module}', '{wiki}');

        return $rules;
    }

    
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('wiki', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
