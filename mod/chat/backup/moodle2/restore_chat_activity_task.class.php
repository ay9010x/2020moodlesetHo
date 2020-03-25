<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/chat/backup/moodle2/restore_chat_stepslib.php');


class restore_chat_activity_task extends restore_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new restore_chat_activity_structure_step('chat_structure', 'chat.xml'));
    }

    
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('chat', array('intro'), 'chat');
        $contents[] = new restore_decode_content('chat_messages', array('message'), 'chat_message');

        return $contents;
    }

    
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('CHATVIEWBYID', '/mod/chat/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('CHATINDEX', '/mod/chat/index.php?id=$1', 'course');

        return $rules;

    }

    
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('chat', 'add', 'view.php?id={course_module}', '{chat}');
        $rules[] = new restore_log_rule('chat', 'update', 'view.php?id={course_module}', '{chat}');
        $rules[] = new restore_log_rule('chat', 'view', 'view.php?id={course_module}', '{chat}');
        $rules[] = new restore_log_rule('chat', 'talk', 'view.php?id={course_module}', '{chat}');
        $rules[] = new restore_log_rule('chat', 'report', 'report.php?id={course_module}', '{chat}');

        return $rules;
    }

    
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('chat', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
