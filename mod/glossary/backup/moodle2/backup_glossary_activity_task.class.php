<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/glossary/backup/moodle2/backup_glossary_stepslib.php');


class backup_glossary_activity_task extends backup_activity_task {

    
    protected function define_my_settings() {
    }

    
    protected function define_my_steps() {
        $this->add_step(new backup_glossary_activity_structure_step('glossary_structure', 'glossary.xml'));
    }

    
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

                $search="/(".$base."\/mod\/glossary\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@GLOSSARYINDEX*$2@$', $content);

                $search="/(".$base."\/mod\/glossary\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@GLOSSARYVIEWBYID*$2@$', $content);

                $search="/(".$base."\/mod\/glossary\/showentry.php\?courseid=)([0-9]+)(&|&amp;)eid=([0-9]+)/";
        $content = preg_replace($search, '$@GLOSSARYSHOWENTRY*$2*$4@$', $content);

        return $content;
    }
}
