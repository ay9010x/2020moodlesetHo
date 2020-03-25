<?php



defined('MOODLE_INTERNAL') || die();


class mod_lesson_generator extends testing_module_generator {

    
    protected $pagecount = 0;

    
    public function reset() {
        $this->pagecount = 0;
        parent::reset();
    }

    public function create_instance($record = null, array $options = null) {
        global $CFG;

                $lessonconfig = get_config('mod_lesson');
        $record = (array)$record + array(
            'progressbar' => $lessonconfig->progressbar,
            'ongoing' => $lessonconfig->ongoing,
            'displayleft' => $lessonconfig->displayleftmenu,
            'displayleftif' => $lessonconfig->displayleftif,
            'slideshow' => $lessonconfig->slideshow,
            'maxanswers' => $lessonconfig->maxanswers,
            'feedback' => $lessonconfig->defaultfeedback,
            'activitylink' => 0,
            'available' => 0,
            'deadline' => 0,
            'usepassword' => 0,
            'password' => '',
            'dependency' => 0,
            'timespent' => 0,
            'completed' => 0,
            'gradebetterthan' => 0,
            'modattempts' => $lessonconfig->modattempts,
            'review' => $lessonconfig->displayreview,
            'maxattempts' => $lessonconfig->maximumnumberofattempts,
            'nextpagedefault' => $lessonconfig->defaultnextpage,
            'maxpages' => $lessonconfig->numberofpagestoshow,
            'practice' => $lessonconfig->practice,
            'custom' => $lessonconfig->customscoring,
            'retake' => $lessonconfig->retakesallowed,
            'usemaxgrade' => $lessonconfig->handlingofretakes,
            'minquestions' => $lessonconfig->minimumnumberofquestions,
            'grade' => 100,
        );
        if (!isset($record['mediafile'])) {
            require_once($CFG->libdir.'/filelib.php');
            $record['mediafile'] = file_get_unused_draft_itemid();
        }

        return parent::create_instance($record, (array)$options);
    }

    public function create_content($lesson, $record = array()) {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/mod/lesson/locallib.php');
        $now = time();
        $this->pagecount++;
        $record = (array)$record + array(
            'lessonid' => $lesson->id,
            'title' => 'Lesson page '.$this->pagecount,
            'timecreated' => $now,
            'qtype' => 20,             'pageid' => 0,         );
        if (!isset($record['contents_editor'])) {
            $record['contents_editor'] = array(
                'text' => 'Contents of lesson page '.$this->pagecount,
                'format' => FORMAT_MOODLE,
                'itemid' => 0,
            );
        }
        $context = context_module::instance($lesson->cmid);
        $page = lesson_page::create((object)$record, new lesson($lesson), $context, $CFG->maxbytes);
        return $DB->get_record('lesson_pages', array('id' => $page->id), '*', MUST_EXIST);
    }

    
    public function create_question_truefalse($lesson, $record = array()) {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/mod/lesson/locallib.php');
        $now = time();
        $this->pagecount++;
        $record = (array)$record + array(
            'lessonid' => $lesson->id,
            'title' => 'Lesson TF question '.$this->pagecount,
            'timecreated' => $now,
            'qtype' => 2,              'pageid' => 0,         );
        if (!isset($record['contents_editor'])) {
            $record['contents_editor'] = array(
                'text' => 'The answer is TRUE '.$this->pagecount,
                'format' => FORMAT_HTML,
                'itemid' => 0
            );
        }

                if (!isset($record['answer_editor'][0])) {
            $record['answer_editor'][0] = array(
                'text' => 'TRUE answer for '.$this->pagecount,
                'format' => FORMAT_HTML
            );
        }
        if (!isset($record['jumpto'][0])) {
            $record['jumpto'][0] = LESSON_NEXTPAGE;
        }

                if (!isset($record['answer_editor'][1])) {
            $record['answer_editor'][1] = array(
                'text' => 'FALSE answer for '.$this->pagecount,
                'format' => FORMAT_HTML
            );
        }
        if (!isset($record['jumpto'][1])) {
            $record['jumpto'][1] = LESSON_THISPAGE;
        }

        $context = context_module::instance($lesson->cmid);
        $page = lesson_page::create((object)$record, new lesson($lesson), $context, $CFG->maxbytes);
        return $DB->get_record('lesson_pages', array('id' => $page->id), '*', MUST_EXIST);
    }

    
    public function create_question_multichoice($lesson, $record = array()) {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/mod/lesson/locallib.php');
        $now = time();
        $this->pagecount++;
        $record = (array)$record + array(
            'lessonid' => $lesson->id,
            'title' => 'Lesson multichoice question '.$this->pagecount,
            'timecreated' => $now,
            'qtype' => 3,              'pageid' => 0,         );
        if (!isset($record['contents_editor'])) {
            $record['contents_editor'] = array(
                'text' => 'Pick the correct answer '.$this->pagecount,
                'format' => FORMAT_HTML,
                'itemid' => 0
            );
        }

                if (!isset($record['answer_editor'][0])) {
            $record['answer_editor'][0] = array(
                'text' => 'correct answer for '.$this->pagecount,
                'format' => FORMAT_HTML
            );
        }
        if (!isset($record['jumpto'][0])) {
            $record['jumpto'][0] = LESSON_NEXTPAGE;
        }

                if (!isset($record['answer_editor'][1])) {
            $record['answer_editor'][1] = array(
                'text' => 'correct answer for '.$this->pagecount,
                'format' => FORMAT_HTML
            );
        }
        if (!isset($record['jumpto'][1])) {
            $record['jumpto'][1] = LESSON_THISPAGE;
        }

        $context = context_module::instance($lesson->cmid);
        $page = lesson_page::create((object)$record, new lesson($lesson), $context, $CFG->maxbytes);
        return $DB->get_record('lesson_pages', array('id' => $page->id), '*', MUST_EXIST);
    }

    
    public function create_question_essay($lesson, $record = array()) {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/mod/lesson/locallib.php');
        $now = time();
        $this->pagecount++;
        $record = (array)$record + array(
            'lessonid' => $lesson->id,
            'title' => 'Lesson Essay question '.$this->pagecount,
            'timecreated' => $now,
            'qtype' => 10,             'pageid' => 0,         );
        if (!isset($record['contents_editor'])) {
            $record['contents_editor'] = array(
                'text' => 'Write an Essay '.$this->pagecount,
                'format' => FORMAT_HTML,
                'itemid' => 0
            );
        }

                if (!isset($record['answer_editor'][0])) {
            $record['answer_editor'][0] = array(
                'text' => null,
                'format' => FORMAT_MOODLE
            );
        }
        if (!isset($record['jumpto'][0])) {
            $record['jumpto'][0] = LESSON_NEXTPAGE;
        }

        $context = context_module::instance($lesson->cmid);
        $page = lesson_page::create((object)$record, new lesson($lesson), $context, $CFG->maxbytes);
        return $DB->get_record('lesson_pages', array('id' => $page->id), '*', MUST_EXIST);
    }

    
    public function create_question_matching($lesson, $record = array()) {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/mod/lesson/locallib.php');
        $now = time();
        $this->pagecount++;
        $record = (array)$record + array(
            'lessonid' => $lesson->id,
            'title' => 'Lesson Matching question '.$this->pagecount,
            'timecreated' => $now,
            'qtype' => 5,              'pageid' => 0,         );
        if (!isset($record['contents_editor'])) {
            $record['contents_editor'] = array(
                'text' => 'Match the values '.$this->pagecount,
                'format' => FORMAT_HTML,
                'itemid' => 0
            );
        }
                if (!isset($record['answer_editor'][0])) {
            $record['answer_editor'][0] = array(
                'text' => '',
                'format' => FORMAT_HTML
            );
        }
                if (!isset($record['answer_editor'][1])) {
            $record['answer_editor'][1] = array(
                'text' => '',
                'format' => FORMAT_HTML
            );
        }
                if (!isset($record['answer_editor'][2])) {
            $record['answer_editor'][2] = array(
                'text' => 'Match value 1',
                'format' => FORMAT_HTML
            );
        }
                if (!isset($record['response_editor'][2])) {
            $record['response_editor'][2] = 'Match answer 1';
        }
                if (!isset($record['answer_editor'][3])) {
            $record['answer_editor'][3] = array(
                'text' => 'Match value 2',
                'format' => FORMAT_HTML
            );
        }
                if (!isset($record['response_editor'][3])) {
            $record['response_editor'][3] = 'Match answer 2';
        }

                if (!isset($record['jumpto'][0])) {
            $record['jumpto'][0] = LESSON_NEXTPAGE;
        }
        if (!isset($record['jumpto'][1])) {
            $record['jumpto'][1] = LESSON_THISPAGE;
        }

                if (!isset($record['score'][0])) {
            $record['score'][0] = 1;
        }
        $context = context_module::instance($lesson->cmid);
        $page = lesson_page::create((object)$record, new lesson($lesson), $context, $CFG->maxbytes);
        return $DB->get_record('lesson_pages', array('id' => $page->id), '*', MUST_EXIST);
    }

    
    public function create_question_shortanswer($lesson, $record = array()) {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/mod/lesson/locallib.php');
        $now = time();
        $this->pagecount++;
        $record = (array)$record + array(
            'lessonid' => $lesson->id,
            'title' => 'Lesson Shortanswer question '.$this->pagecount,
            'timecreated' => $now,
            'qtype' => 1,              'pageid' => 0,         );
        if (!isset($record['contents_editor'])) {
            $record['contents_editor'] = array(
                'text' => 'Fill in the blank '.$this->pagecount,
                'format' => FORMAT_HTML,
                'itemid' => 0
            );
        }

                if (!isset($record['answer_editor'][0])) {
            $record['answer_editor'][0] = array(
                'text' => 'answer'.$this->pagecount,
                'format' => FORMAT_MOODLE
            );
        }
        if (!isset($record['jumpto'][0])) {
            $record['jumpto'][0] = LESSON_NEXTPAGE;
        }

        $context = context_module::instance($lesson->cmid);
        $page = lesson_page::create((object)$record, new lesson($lesson), $context, $CFG->maxbytes);
        return $DB->get_record('lesson_pages', array('id' => $page->id), '*', MUST_EXIST);
    }

    
    public function create_question_numeric($lesson, $record = array()) {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/mod/lesson/locallib.php');
        $now = time();
        $this->pagecount++;
        $record = (array)$record + array(
            'lessonid' => $lesson->id,
            'title' => 'Lesson numerical question '.$this->pagecount,
            'timecreated' => $now,
            'qtype' => 8,              'pageid' => 0,         );
        if (!isset($record['contents_editor'])) {
            $record['contents_editor'] = array(
                'text' => 'Numerical question '.$this->pagecount,
                'format' => FORMAT_HTML,
                'itemid' => 0
            );
        }

                if (!isset($record['answer_editor'][0])) {
            $record['answer_editor'][0] = array(
                'text' => $this->pagecount,
                'format' => FORMAT_MOODLE
            );
        }
        if (!isset($record['jumpto'][0])) {
            $record['jumpto'][0] = LESSON_NEXTPAGE;
        }

        $context = context_module::instance($lesson->cmid);
        $page = lesson_page::create((object)$record, new lesson($lesson), $context, $CFG->maxbytes);
        return $DB->get_record('lesson_pages', array('id' => $page->id), '*', MUST_EXIST);
    }
}
