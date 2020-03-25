<?php




defined('MOODLE_INTERNAL') || die();


class moodle1_mod_lesson_handler extends moodle1_mod_handler {
        protected $answers;

        protected $page;
        protected $pages;
        protected $prevpageid = 0;

    
    protected $fileman = null;

    
    protected $moduleid = null;

    
    public function get_paths() {
        return array(
            new convert_path(
                'lesson', '/MOODLE_BACKUP/COURSE/MODULES/MOD/LESSON',
                array(
                    'renamefields' => array(
                        'usegrademax' => 'usemaxgrade',
                    ),
                )
            ),
            new convert_path(
                'lesson_page', '/MOODLE_BACKUP/COURSE/MODULES/MOD/LESSON/PAGES/PAGE',
                array(
                    'newfields' => array(
                        'contentsformat' => FORMAT_MOODLE,
                        'nextpageid' => 0,                         'prevpageid' => 0
                    ),
                )
            ),
            new convert_path(
                'lesson_pages', '/MOODLE_BACKUP/COURSE/MODULES/MOD/LESSON/PAGES'
            ),
            new convert_path(
                'lesson_answer', '/MOODLE_BACKUP/COURSE/MODULES/MOD/LESSON/PAGES/PAGE/ANSWERS/ANSWER',
                array(
                    'newfields' => array(
                        'answerformat' => 0,
                        'responseformat' => 0,
                    ),
                    'renamefields' => array(
                        'answertext' => 'answer_text',
                    ),
                )
            )
        );
    }

    
    public function process_lesson($data) {

                $instanceid     = $data['id'];
        $cminfo         = $this->get_cminfo($instanceid);
        $this->moduleid = $cminfo['id'];
        $contextid      = $this->converter->get_contextid(CONTEXT_MODULE, $this->moduleid);

                $this->fileman = $this->converter->get_file_manager($contextid, 'mod_lesson');

                if (!empty($data['mediafile']) and strpos($data['mediafile'], '://') === false) {
            $this->fileman->filearea = 'mediafile';
            $this->fileman->itemid   = 0;
            try {
                $this->fileman->migrate_file('course_files/'.$data['mediafile']);
            } catch (moodle1_convert_exception $e) {
                                $this->log('error migrating lesson mediafile', backup::LOG_WARNING, 'course_files/'.$data['mediafile']);
            }
        }

                $this->open_xml_writer("activities/lesson_{$this->moduleid}/lesson.xml");
        $this->xmlwriter->begin_tag('activity', array('id' => $instanceid, 'moduleid' => $this->moduleid,
            'modulename' => 'lesson', 'contextid' => $contextid));
        $this->xmlwriter->begin_tag('lesson', array('id' => $instanceid));

        foreach ($data as $field => $value) {
            if ($field <> 'id') {
                $this->xmlwriter->full_tag($field, $value);
            }
        }

        return $data;
    }

    public function on_lesson_pages_start() {
        $this->xmlwriter->begin_tag('pages');
    }

    
    public function process_lesson_page($data) {
        global $CFG;

                if ($CFG->texteditors !== 'textarea') {
            $data['contents'] = text_to_html($data['contents'], false, false, true);
            $data['contentsformat'] = FORMAT_HTML;
        }

                $this->page = new stdClass();
        $this->page->id = $data['pageid'];
        unset($data['pageid']);
        $this->page->data = $data;
    }

    
    public function process_lesson_answer($data) {

                $flags = intval($data['flags']);
        if ($flags & 1) {
            $data['answer_text']  = text_to_html($data['answer_text'], false, false, true);
            $data['answerformat'] = FORMAT_HTML;
        }
        if ($flags & 2) {
            $data['response']       = text_to_html($data['response'], false, false, true);
            $data['responseformat'] = FORMAT_HTML;
        }

                        $this->answers[] = $data;
    }

    public function on_lesson_page_end() {
        $this->page->answers = $this->answers;
        $this->pages[] = $this->page;

        $firstbatch = count($this->pages) > 2;
        $nextbatch = count($this->pages) > 1 && $this->prevpageid != 0;

        if ( $firstbatch || $nextbatch ) {             if ($this->prevpageid == 0) {
                                $pg1 = $this->pages[1];
                $pg0 = $this->pages[0];
                $this->write_single_page_xml($pg0, 0, $pg1->id);
                $this->prevpageid = $pg0->id;
                array_shift($this->pages);             }

            $pg1 = $this->pages[0];
                        $pg2 = $this->pages[1];
            $this->write_single_page_xml($pg1, $this->prevpageid, $pg2->id);
            $this->prevpageid = $pg1->id;
            array_shift($this->pages);         }
        $this->answers = array();         $this->page = null;
    }

    public function on_lesson_pages_end() {
        if ($this->pages) {
            if (isset($this->pages[1])) {                 $this->write_single_page_xml($this->pages[0], $this->prevpageid, $this->pages[1]->id);
                $this->prevpageid = $this->pages[0]->id;
                array_shift($this->pages);
            }
                        $this->write_single_page_xml($this->pages[0], $this->prevpageid, 0);
        }
        $this->xmlwriter->end_tag('pages');
                unset($this->pages);
        $this->prevpageid = 0;

    }

    
    public function on_lesson_end() {
                $this->write_xml('overrides', array());

                $this->xmlwriter->end_tag('lesson');
        $this->xmlwriter->end_tag('activity');
        $this->close_xml_writer();

                $this->open_xml_writer("activities/lesson_{$this->moduleid}/inforef.xml");
        $this->xmlwriter->begin_tag('inforef');
        $this->xmlwriter->begin_tag('fileref');
        foreach ($this->fileman->get_fileids() as $fileid) {
            $this->write_xml('file', array('id' => $fileid));
        }
        $this->xmlwriter->end_tag('fileref');
        $this->xmlwriter->end_tag('inforef');
        $this->close_xml_writer();
    }

    
    protected function write_single_page_xml($page, $prevpageid=0, $nextpageid=0) {
                $page->data['nextpageid'] = $nextpageid;
        $page->data['prevpageid'] = $prevpageid;

                $this->xmlwriter->begin_tag('page', array('id' => $page->id));

        foreach ($page->data as $field => $value) {
            $this->xmlwriter->full_tag($field, $value);
        }

                $answers = $page->answers;

        $this->xmlwriter->begin_tag('answers');

        $numanswers = count($answers);
        if ($numanswers) {             if ($numanswers > 3 && $page->data['qtype'] == 5) {                 if ($answers[0]['jumpto'] !== '0' || $answers[1]['jumpto'] !== '0') {
                    if ($answers[2]['jumpto'] !== '0') {
                        $answers[0]['jumpto'] = $answers[2]['jumpto'];
                        $answers[2]['jumpto'] = '0';
                    }
                    if ($answers[3]['jumpto'] !== '0') {
                        $answers[1]['jumpto'] = $answers[3]['jumpto'];
                        $answers[3]['jumpto'] = '0';
                    }
                }
            }
            foreach ($answers as $data) {
                $this->write_xml('answer', $data, array('/answer/id'));
            }
        }

        $this->xmlwriter->end_tag('answers');

                $this->xmlwriter->end_tag('page');
    }
}
