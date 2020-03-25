<?php




defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/url/backup/moodle2/backup_url_stepslib.php');


class backup_url_activity_task extends backup_activity_task {

    
    protected function define_my_settings() {
    }

    
    protected function define_my_steps() {
        $this->add_step(new backup_url_activity_structure_step('url_structure', 'url.xml'));
    }

    
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot.'/mod/url','#');

                $pattern = '#('.$base.'/index\.php\?id=)([0-9]+)#';
        $replacement = '$@URLINDEX*$2@$';
        $content = preg_replace($pattern, $replacement, $content);

                $pattern = '#('.$base.'/view\.php\?id=)([0-9]+)#';
        $replacement = '$@URLVIEWBYID*$2@$';
        $content = preg_replace($pattern, $replacement, $content);

                $pattern = '#('.$base.'/view\.php\?u=)([0-9]+)#';
        $replacement = '$@URLVIEWBYU*$2@$';
        $content = preg_replace($pattern, $replacement, $content);

        return $content;
    }
}
