<?php



namespace mod_forum\output\emaildigestfull;

defined('MOODLE_INTERNAL') || die();


class renderer extends \mod_forum\output\email\renderer {

    
    public function forum_post_template() {
        return 'forum_post_emaildigestfull_htmlemail';
    }
}
