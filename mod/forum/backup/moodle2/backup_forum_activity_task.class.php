<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/forum/backup/moodle2/backup_forum_stepslib.php');
require_once($CFG->dirroot . '/mod/forum/backup/moodle2/backup_forum_settingslib.php');


class backup_forum_activity_task extends backup_activity_task {

    
    protected function define_my_settings() {
    }

    
    protected function define_my_steps() {
        $this->add_step(new backup_forum_activity_structure_step('forum structure', 'forum.xml'));
    }

    
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

                $search="/(".$base."\/mod\/forum\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@FORUMINDEX*$2@$', $content);

                $search="/(".$base."\/mod\/forum\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@FORUMVIEWBYID*$2@$', $content);

                $search="/(".$base."\/mod\/forum\/view.php\?f\=)([0-9]+)/";
        $content= preg_replace($search, '$@FORUMVIEWBYF*$2@$', $content);

                $search = "/(".$base."\/mod\/forum\/discuss.php\?d\=)([0-9]+)(?:\&amp;|\&)parent\=([0-9]+)/";
        $content= preg_replace($search, '$@FORUMDISCUSSIONVIEWPARENT*$2*$3@$', $content);

                $search="/(".$base."\/mod\/forum\/discuss.php\?d\=)([0-9]+)\#([0-9]+)/";
        $content= preg_replace($search, '$@FORUMDISCUSSIONVIEWINSIDE*$2*$3@$', $content);

                $search="/(".$base."\/mod\/forum\/discuss.php\?d\=)([0-9]+)/";
        $content= preg_replace($search, '$@FORUMDISCUSSIONVIEW*$2@$', $content);

        return $content;
    }
}
