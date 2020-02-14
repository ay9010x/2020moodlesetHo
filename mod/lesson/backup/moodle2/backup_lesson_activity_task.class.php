<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/lesson/backup/moodle2/backup_lesson_stepslib.php');


class backup_lesson_activity_task extends backup_activity_task {

    
    protected function define_my_settings() {
    }

    
    protected function define_my_steps() {
        $this->add_step(new backup_lesson_activity_structure_step('lesson structure', 'lesson.xml'));
    }

    
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot.'/mod/lesson','#');

                $pattern = '#'.$base.'/edit\.php\?id=([0-9]+)#';
        $replacement = '$@LESSONEDIT*$1@$';
        $content = preg_replace($pattern, $replacement, $content);

                $pattern = '#'.$base.'/editpage\.php\?id=([0-9]+)&(amp;)?pageid=([0-9]+)#';
        $replacement = '$@LESSONEDITPAGE*$1*$3@$';
        $content = preg_replace($pattern, $replacement, $content);

                $pattern = '#'.$base.'/essay\.php\?id=([0-9]+)#';
        $replacement = '$@LESSONESSAY*$1@$';
        $content = preg_replace($pattern, $replacement, $content);

                $pattern = '#'.$base.'/report\.php\?id=([0-9]+)#';
        $replacement = '$@LESSONREPORT*$1@$';
        $content = preg_replace($pattern, $replacement, $content);

                $pattern = '#'.$base.'/mediafile\.php\?id=([0-9]+)#';
        $replacement = '$@LESSONMEDIAFILE*$1@$';
        $content = preg_replace($pattern, $replacement, $content);

                $pattern = '#'.$base.'/index\.php\?id=([0-9]+)#';
        $replacement = '$@LESSONINDEX*$1@$';
        $content = preg_replace($pattern, $replacement, $content);

                $pattern = '#'.$base.'/view\.php\?id=([0-9]+)&(amp;)?pageid=([0-9]+)#';
        $replacement = '$@LESSONVIEWPAGE*$1*$3@$';
        $content = preg_replace($pattern, $replacement, $content);

                $pattern = '#'.$base.'/view\.php\?id=([0-9]+)#';
        $replacement = '$@LESSONVIEWBYID*$1@$';
        $content = preg_replace($pattern, $replacement, $content);

                return $content;
    }
}
