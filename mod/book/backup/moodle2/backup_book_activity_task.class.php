<?php



defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/mod/book/backup/moodle2/backup_book_stepslib.php');    require_once($CFG->dirroot.'/mod/book/backup/moodle2/backup_book_settingslib.php'); 
class backup_book_activity_task extends backup_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new backup_book_activity_structure_step('book_structure', 'book.xml'));
    }

    
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

                $search  = "/($base\/mod\/book\/index.php\?id=)([0-9]+)/";
        $content = preg_replace($search, '$@BOOKINDEX*$2@$', $content);

                $search  = "/($base\/mod\/book\/view.php\?id=)([0-9]+)(&|&amp;)chapterid=([0-9]+)/";
        $content = preg_replace($search, '$@BOOKVIEWBYIDCH*$2*$4@$', $content);

        $search  = "/($base\/mod\/book\/view.php\?id=)([0-9]+)/";
        $content = preg_replace($search, '$@BOOKVIEWBYID*$2@$', $content);

                $search  = "/($base\/mod\/book\/view.php\?b=)([0-9]+)(&|&amp;)chapterid=([0-9]+)/";
        $content = preg_replace($search, '$@BOOKVIEWBYBCH*$2*$4@$', $content);

        $search  = "/($base\/mod\/book\/view.php\?b=)([0-9]+)/";
        $content = preg_replace($search, '$@BOOKVIEWBYB*$2@$', $content);

        return $content;
    }
}
