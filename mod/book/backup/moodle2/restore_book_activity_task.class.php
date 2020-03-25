<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/book/backup/moodle2/restore_book_stepslib.php'); 
class restore_book_activity_task extends restore_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new restore_book_activity_structure_step('book_structure', 'book.xml'));
    }

    
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('book', array('intro'), 'book');
        $contents[] = new restore_decode_content('book_chapters', array('content'), 'book_chapter');

        return $contents;
    }

    
    static public function define_decode_rules() {
        $rules = array();

                $rules[] = new restore_decode_rule('BOOKINDEX', '/mod/book/index.php?id=$1', 'course');

                $rules[] = new restore_decode_rule('BOOKVIEWBYID', '/mod/book/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('BOOKVIEWBYIDCH', '/mod/book/view.php?id=$1&amp;chapterid=$2', array('course_module', 'book_chapter'));

                $rules[] = new restore_decode_rule('BOOKVIEWBYB', '/mod/book/view.php?b=$1', 'book');
        $rules[] = new restore_decode_rule('BOOKVIEWBYBCH', '/mod/book/view.php?b=$1&amp;chapterid=$2', array('book', 'book_chapter'));

                $rules[] = new restore_decode_rule('BOOKSTART', '/mod/book/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('BOOKCHAPTER', '/mod/book/view.php?id=$1&amp;chapterid=$2', array('course_module', 'book_chapter'));

        return $rules;
    }

    
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('book', 'add', 'view.php?id={course_module}', '{book}');
        $rules[] = new restore_log_rule('book', 'update', 'view.php?id={course_module}&chapterid={book_chapter}', '{book}');
        $rules[] = new restore_log_rule('book', 'update', 'view.php?id={course_module}', '{book}');
        $rules[] = new restore_log_rule('book', 'view', 'view.php?id={course_module}&chapterid={book_chapter}', '{book}');
        $rules[] = new restore_log_rule('book', 'view', 'view.php?id={course_module}', '{book}');
        $rules[] = new restore_log_rule('book', 'print', 'tool/print/index.php?id={course_module}&chapterid={book_chapter}', '{book}');
        $rules[] = new restore_log_rule('book', 'print', 'tool/print/index.php?id={course_module}', '{book}');
        $rules[] = new restore_log_rule('book', 'exportimscp', 'tool/exportimscp/index.php?id={course_module}', '{book}');
                $rules[] = new restore_log_rule('book', 'generateimscp', 'tool/generateimscp/index.php?id={course_module}', '{book}',
                'book', 'exportimscp', 'tool/exportimscp/index.php?id={course_module}', '{book}');
        $rules[] = new restore_log_rule('book', 'print chapter', 'tool/print/index.php?id={course_module}&chapterid={book_chapter}', '{book_chapter}');
        $rules[] = new restore_log_rule('book', 'update chapter', 'view.php?id={course_module}&chapterid={book_chapter}', '{book_chapter}');
        $rules[] = new restore_log_rule('book', 'add chapter', 'view.php?id={course_module}&chapterid={book_chapter}', '{book_chapter}');
        $rules[] = new restore_log_rule('book', 'view chapter', 'view.php?id={course_module}&chapterid={book_chapter}', '{book_chapter}');

        return $rules;
    }

    
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('book', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
