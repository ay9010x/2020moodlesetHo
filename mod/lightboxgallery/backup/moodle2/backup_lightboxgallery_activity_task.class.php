<?php



require_once($CFG->dirroot . '/mod/lightboxgallery/backup/moodle2/backup_lightboxgallery_stepslib.php');


class backup_lightboxgallery_activity_task extends backup_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new backup_lightboxgallery_activity_structure_step('lightboxgallery_structure', 'lightboxgallery.xml'));
    }

    
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

                $search = "/(".$base."\/mod\/lightboxgallery\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@LIGHTBOXGALLERYINDEX*$2@$', $content);

                $search = "/(".$base."\/mod\/lightboxgallery\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@LIGHTBOXGALLERYVIEWBYID*$2@$', $content);

        return $content;
    }
}
