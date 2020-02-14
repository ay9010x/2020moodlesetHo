<?php



namespace mod_forum\output\email;

defined('MOODLE_INTERNAL') || die();


class renderer_textemail extends renderer {

    
    public function forum_post_template() {
        return 'forum_post_email_textemail';
    }

    
    public function format_message_text($cm, $post) {
        $message = file_rewrite_pluginfile_urls($post->message, 'pluginfile.php',
            \context_module::instance($cm->id)->id,
            'mod_forum', 'post', $post->id);
        return format_text_email($message, $post->messageformat);
    }

    
    public function format_message_attachments($cm, $post) {
        return forum_print_attachments($post, $cm, "text");
    }
}
