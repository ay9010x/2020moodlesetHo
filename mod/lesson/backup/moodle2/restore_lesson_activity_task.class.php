<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/lesson/backup/moodle2/restore_lesson_stepslib.php'); 

class restore_lesson_activity_task extends restore_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new restore_lesson_activity_structure_step('lesson_structure', 'lesson.xml'));
    }

    
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('lesson', array('intro'), 'lesson');
        $contents[] = new restore_decode_content('lesson_pages', array('contents'), 'lesson_page');
        $contents[] = new restore_decode_content('lesson_answers', array('answer', 'response'), 'lesson_answer');

        return $contents;
    }

    
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('LESSONEDIT', '/mod/lesson/edit.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('LESSONESAY', '/mod/lesson/essay.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('LESSONREPORT', '/mod/lesson/report.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('LESSONMEDIAFILE', '/mod/lesson/mediafile.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('LESSONVIEWBYID', '/mod/lesson/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('LESSONINDEX', '/mod/lesson/index.php?id=$1', 'course');
        $rules[] = new restore_decode_rule('LESSONVIEWPAGE', '/mod/lesson/view.php?id=$1&pageid=$2', array('course_module', 'lesson_page'));
        $rules[] = new restore_decode_rule('LESSONEDITPAGE', '/mod/lesson/edit.php?id=$1&pageid=$2', array('course_module', 'lesson_page'));

        return $rules;

    }

    
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('lesson', 'add', 'view.php?id={course_module}', '{lesson}');
        $rules[] = new restore_log_rule('lesson', 'update', 'view.php?id={course_module}', '{lesson}');
        $rules[] = new restore_log_rule('lesson', 'view', 'view.php?id={course_module}', '{lesson}');
        $rules[] = new restore_log_rule('lesson', 'start', 'view.php?id={course_module}', '{lesson}');
        $rules[] = new restore_log_rule('lesson', 'end', 'view.php?id={course_module}', '{lesson}');
        $rules[] = new restore_log_rule('lesson', 'view grade', 'essay.php?id={course_module}', '[name]');
        $rules[] = new restore_log_rule('lesson', 'update grade', 'essay.php?id={course_module}', '[name]');
        $rules[] = new restore_log_rule('lesson', 'update email essay grade', 'essay.php?id={course_module}', '[name]');

        return $rules;
    }

    
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('lesson', 'view all', 'index.php?id={course}', null);

        return $rules;
    }


    
    public function after_restore() {
        global $DB;

        $lesson = $DB->get_record('lesson', array('id' => $this->get_activityid()), 'id, course, dependency, activitylink');
        $updaterequired = false;

        if (!empty($lesson->dependency)) {
            $updaterequired = true;
            if ($newitem = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'lesson', $lesson->dependency)) {
                $lesson->dependency = $newitem->newitemid;
            }
            if (!$DB->record_exists('lesson', array('id' => $lesson->dependency, 'course' => $lesson->course))) {
                $lesson->dependency = 0;
            }
        }

        if (!empty($lesson->activitylink)) {
            $updaterequired = true;
            if ($newitem = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'course_module', $lesson->activitylink)) {
                $lesson->activitylink = $newitem->newitemid;
            }
            if (!$DB->record_exists('course_modules', array('id' => $lesson->activitylink, 'course' => $lesson->course))) {
                $lesson->activitylink = 0;
            }
        }

        if ($updaterequired) {
            $DB->update_record('lesson', $lesson);
        }
    }
}
