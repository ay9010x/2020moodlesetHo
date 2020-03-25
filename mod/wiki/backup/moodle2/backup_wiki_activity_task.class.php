<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/wiki/backup/moodle2/backup_wiki_stepslib.php');
require_once($CFG->dirroot . '/mod/wiki/backup/moodle2/backup_wiki_settingslib.php');


class backup_wiki_activity_task extends backup_activity_task {

    
    protected function define_my_settings() {
    }

    
    protected function define_my_steps() {
        $this->add_step(new backup_wiki_activity_structure_step('wiki_structure', 'wiki.xml'));
    }

    
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

                $search = "/(" . $base . "\/mod\/wiki\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@WIKIINDEX*$2@$', $content);

                $search = "/(" . $base . "\/mod\/wiki\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@WIKIVIEWBYID*$2@$', $content);

                $search = "/(" . $base . "\/mod\/wiki\/view.php\?pageid\=)([0-9]+)/";
        $content = preg_replace($search, '$@WIKIPAGEBYID*$2@$', $content);

        return $content;
    }
}
