<?php




defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/page/backup/moodle2/backup_page_stepslib.php');


class backup_page_activity_task extends backup_activity_task {

    
    protected function define_my_settings() {
    }

    
    protected function define_my_steps() {
        $this->add_step(new backup_page_activity_structure_step('page_structure', 'page.xml'));
    }

    
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

                $search="/(".$base."\/mod\/page\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@PAGEINDEX*$2@$', $content);

                $search="/(".$base."\/mod\/page\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@PAGEVIEWBYID*$2@$', $content);

        return $content;
    }
}
