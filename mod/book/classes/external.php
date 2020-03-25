<?php



defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");


class mod_book_external extends external_api {

    
    public static function view_book_parameters() {
        return new external_function_parameters(
            array(
                'bookid' => new external_value(PARAM_INT, 'book instance id'),
                'chapterid' => new external_value(PARAM_INT, 'chapter id', VALUE_DEFAULT, 0)
            )
        );
    }

    
    public static function view_book($bookid, $chapterid = 0) {
        global $DB, $CFG;
        require_once($CFG->dirroot . "/mod/book/lib.php");
        require_once($CFG->dirroot . "/mod/book/locallib.php");

        $params = self::validate_parameters(self::view_book_parameters(),
                                            array(
                                                'bookid' => $bookid,
                                                'chapterid' => $chapterid
                                            ));
        $bookid = $params['bookid'];
        $chapterid = $params['chapterid'];

        $warnings = array();

                $book = $DB->get_record('book', array('id' => $bookid), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($book, 'book');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/book:read', $context);

        $chapters = book_preload_chapters($book);
        $firstchapterid = 0;
        $lastchapterid = 0;

        foreach ($chapters as $ch) {
            if ($ch->hidden) {
                continue;
            }
            if (!$firstchapterid) {
                $firstchapterid = $ch->id;
            }
            $lastchapterid = $ch->id;
        }

        if (!$chapterid) {
                        book_view($book, null, false, $course, $cm, $context);
            $chapterid = $firstchapterid;
        }

                if (!$chapterid) {
            $warnings[] = array(
                'item' => 'book',
                'itemid' => $book->id,
                'warningcode' => '1',
                'message' => get_string('nocontent', 'mod_book')
            );
        } else {
            $chapter = $DB->get_record('book_chapters', array('id' => $chapterid, 'bookid' => $book->id));
            $viewhidden = has_capability('mod/book:viewhiddenchapters', $context);

            if (!$chapter or ($chapter->hidden and !$viewhidden)) {
                throw new moodle_exception('errorchapter', 'mod_book');
            }

                        $islastchapter = ($chapter->id == $lastchapterid) ? true : false;
            book_view($book, $chapter, $islastchapter, $course, $cm, $context);
        }

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function view_book_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings()
            )
        );
    }

    
    public static function get_books_by_courses_parameters() {
        return new external_function_parameters (
            array(
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'course id'), 'Array of course ids', VALUE_DEFAULT, array()
                ),
            )
        );
    }

    
    public static function get_books_by_courses($courseids = array()) {
        global $CFG;

        $returnedbooks = array();
        $warnings = array();

        $params = self::validate_parameters(self::get_books_by_courses_parameters(), array('courseids' => $courseids));

        $courses = array();
        if (empty($params['courseids'])) {
            $courses = enrol_get_my_courses();
            $params['courseids'] = array_keys($courses);
        }

                if (!empty($params['courseids'])) {

            list($courses, $warnings) = external_util::validate_courses($params['courseids'], $courses);

                                    $books = get_all_instances_in_courses("book", $courses);
            foreach ($books as $book) {
                $context = context_module::instance($book->coursemodule);
                                $bookdetails = array();
                                $bookdetails['id'] = $book->id;
                $bookdetails['coursemodule']      = $book->coursemodule;
                $bookdetails['course']            = $book->course;
                $bookdetails['name']              = external_format_string($book->name, $context->id);
                                list($bookdetails['intro'], $bookdetails['introformat']) =
                    external_format_text($book->intro, $book->introformat, $context->id, 'mod_book', 'intro', null);
                $bookdetails['numbering']         = $book->numbering;
                $bookdetails['navstyle']          = $book->navstyle;
                $bookdetails['customtitles']      = $book->customtitles;

                if (has_capability('moodle/course:manageactivities', $context)) {
                    $bookdetails['revision']      = $book->revision;
                    $bookdetails['timecreated']   = $book->timecreated;
                    $bookdetails['timemodified']  = $book->timemodified;
                    $bookdetails['section']       = $book->section;
                    $bookdetails['visible']       = $book->visible;
                    $bookdetails['groupmode']     = $book->groupmode;
                    $bookdetails['groupingid']    = $book->groupingid;
                }
                $returnedbooks[] = $bookdetails;
            }
        }
        $result = array();
        $result['books'] = $returnedbooks;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function get_books_by_courses_returns() {
        return new external_single_structure(
            array(
                'books' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Book id'),
                            'coursemodule' => new external_value(PARAM_INT, 'Course module id'),
                            'course' => new external_value(PARAM_INT, 'Course id'),
                            'name' => new external_value(PARAM_RAW, 'Book name'),
                            'intro' => new external_value(PARAM_RAW, 'The Book intro'),
                            'introformat' => new external_format_value('intro'),
                            'numbering' => new external_value(PARAM_INT, 'Book numbering configuration'),
                            'navstyle' => new external_value(PARAM_INT, 'Book navigation style configuration'),
                            'customtitles' => new external_value(PARAM_INT, 'Book custom titles type'),
                            'revision' => new external_value(PARAM_INT, 'Book revision', VALUE_OPTIONAL),
                            'timecreated' => new external_value(PARAM_INT, 'Time of creation', VALUE_OPTIONAL),
                            'timemodified' => new external_value(PARAM_INT, 'Time of last modification', VALUE_OPTIONAL),
                            'section' => new external_value(PARAM_INT, 'Course section id', VALUE_OPTIONAL),
                            'visible' => new external_value(PARAM_BOOL, 'Visible', VALUE_OPTIONAL),
                            'groupmode' => new external_value(PARAM_INT, 'Group mode', VALUE_OPTIONAL),
                            'groupingid' => new external_value(PARAM_INT, 'Group id', VALUE_OPTIONAL),
                        ), 'Books'
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }

}
