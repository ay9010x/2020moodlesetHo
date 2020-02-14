<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/chat/backup/moodle2/backup_chat_stepslib.php');


class backup_chat_activity_task extends backup_activity_task {

    
    protected function define_my_settings() {
    }

    
    protected function define_my_steps() {
        $this->add_step(new backup_chat_activity_structure_step('chat_structure', 'chat.xml'));
    }

    
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot . '/mod/chat', '#');

                $pattern = "#(".$base."\/index.php\?id\=)([0-9]+)#";
        $content = preg_replace($pattern, '$@CHATINDEX*$2@$', $content);

                $pattern = "#(".$base."\/view.php\?id\=)([0-9]+)#";
        $content = preg_replace($pattern, '$@CHATVIEWBYID*$2@$', $content);

        return $content;
    }
}
