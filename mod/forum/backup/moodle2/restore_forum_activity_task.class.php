<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/forum/backup/moodle2/restore_forum_stepslib.php'); 

class restore_forum_activity_task extends restore_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new restore_forum_activity_structure_step('forum_structure', 'forum.xml'));
    }

    
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('forum', array('intro'), 'forum');
        $contents[] = new restore_decode_content('forum_posts', array('message'), 'forum_post');

        return $contents;
    }

    
    static public function define_decode_rules() {
        $rules = array();

                $rules[] = new restore_decode_rule('FORUMINDEX', '/mod/forum/index.php?id=$1', 'course');
                $rules[] = new restore_decode_rule('FORUMVIEWBYID', '/mod/forum/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('FORUMVIEWBYF', '/mod/forum/view.php?f=$1', 'forum');
                $rules[] = new restore_decode_rule('FORUMDISCUSSIONVIEW', '/mod/forum/discuss.php?d=$1', 'forum_discussion');
                $rules[] = new restore_decode_rule('FORUMDISCUSSIONVIEWPARENT', '/mod/forum/discuss.php?d=$1&parent=$2',
                                           array('forum_discussion', 'forum_post'));
        $rules[] = new restore_decode_rule('FORUMDISCUSSIONVIEWINSIDE', '/mod/forum/discuss.php?d=$1#$2',
                                           array('forum_discussion', 'forum_post'));

        return $rules;
    }

    
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('forum', 'add', 'view.php?id={course_module}', '{forum}');
        $rules[] = new restore_log_rule('forum', 'update', 'view.php?id={course_module}', '{forum}');
        $rules[] = new restore_log_rule('forum', 'view', 'view.php?id={course_module}', '{forum}');
        $rules[] = new restore_log_rule('forum', 'view forum', 'view.php?id={course_module}', '{forum}');
        $rules[] = new restore_log_rule('forum', 'mark read', 'view.php?f={forum}', '{forum}');
        $rules[] = new restore_log_rule('forum', 'start tracking', 'view.php?f={forum}', '{forum}');
        $rules[] = new restore_log_rule('forum', 'stop tracking', 'view.php?f={forum}', '{forum}');
        $rules[] = new restore_log_rule('forum', 'subscribe', 'view.php?f={forum}', '{forum}');
        $rules[] = new restore_log_rule('forum', 'unsubscribe', 'view.php?f={forum}', '{forum}');
        $rules[] = new restore_log_rule('forum', 'subscriber', 'subscribers.php?id={forum}', '{forum}');
        $rules[] = new restore_log_rule('forum', 'subscribers', 'subscribers.php?id={forum}', '{forum}');
        $rules[] = new restore_log_rule('forum', 'view subscribers', 'subscribers.php?id={forum}', '{forum}');
        $rules[] = new restore_log_rule('forum', 'add discussion', 'discuss.php?d={forum_discussion}', '{forum_discussion}');
        $rules[] = new restore_log_rule('forum', 'view discussion', 'discuss.php?d={forum_discussion}', '{forum_discussion}');
        $rules[] = new restore_log_rule('forum', 'move discussion', 'discuss.php?d={forum_discussion}', '{forum_discussion}');
        $rules[] = new restore_log_rule('forum', 'delete discussi', 'view.php?id={course_module}', '{forum}',
                                        null, 'delete discussion');
        $rules[] = new restore_log_rule('forum', 'delete discussion', 'view.php?id={course_module}', '{forum}');
        $rules[] = new restore_log_rule('forum', 'add post', 'discuss.php?d={forum_discussion}&parent={forum_post}', '{forum_post}');
        $rules[] = new restore_log_rule('forum', 'update post', 'discuss.php?d={forum_discussion}#p{forum_post}&parent={forum_post}', '{forum_post}');
        $rules[] = new restore_log_rule('forum', 'update post', 'discuss.php?d={forum_discussion}&parent={forum_post}', '{forum_post}');
        $rules[] = new restore_log_rule('forum', 'prune post', 'discuss.php?d={forum_discussion}', '{forum_post}');
        $rules[] = new restore_log_rule('forum', 'delete post', 'discuss.php?d={forum_discussion}', '[post]');

        return $rules;
    }

    
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('forum', 'view forums', 'index.php?id={course}', null);
        $rules[] = new restore_log_rule('forum', 'subscribeall', 'index.php?id={course}', '{course}');
        $rules[] = new restore_log_rule('forum', 'unsubscribeall', 'index.php?id={course}', '{course}');
        $rules[] = new restore_log_rule('forum', 'user report', 'user.php?course={course}&id={user}&mode=[mode]', '{user}');
        $rules[] = new restore_log_rule('forum', 'search', 'search.php?id={course}&search=[searchenc]', '[search]');

        return $rules;
    }
}
