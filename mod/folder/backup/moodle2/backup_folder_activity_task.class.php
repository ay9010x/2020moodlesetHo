<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/folder/backup/moodle2/backup_folder_stepslib.php');


class backup_folder_activity_task extends backup_activity_task {

    
    protected function define_my_settings() {
    }

    
    protected function define_my_steps() {
        $this->add_step(new backup_folder_activity_structure_step('folder_structure', 'folder.xml'));
    }

    
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

                $search="/(".$base."\/mod\/folder\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@FOLDERINDEX*$2@$', $content);

                $search="/(".$base."\/mod\/folder\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@FOLDERVIEWBYID*$2@$', $content);

        return $content;
    }
}
