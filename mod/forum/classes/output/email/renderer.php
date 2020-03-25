<?php



namespace mod_forum\output\email;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../../renderer.php');


class renderer extends \mod_forum_renderer {

    
    public function forum_post_template() {
        return 'forum_post_email_htmlemail';
    }

    
    public function format_message_text($cm, $post) {
        $message = file_rewrite_pluginfile_urls($post->message, 'pluginfile.php',
            \context_module::instance($cm->id)->id,
            'mod_forum', 'post', $post->id);
        $options = new \stdClass();
        $options->para = true;
        return format_text($message, $post->messageformat, $options);
    }

    
    public function format_message_attachments($cm, $post) {
        return forum_print_attachments($post, $cm, "html");
    }
}
