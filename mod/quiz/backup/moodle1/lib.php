<?php



defined('MOODLE_INTERNAL') || die();


class moodle1_mod_quiz_handler extends moodle1_mod_handler {

    
    protected $fileman = null;

    
    protected $moduleid = null;

    
    public function get_paths() {
        return array(
            new convert_path(
                'quiz', '/MOODLE_BACKUP/COURSE/MODULES/MOD/QUIZ',
                array(
                    'newfields' => array(
                        'showuserpicture'       => 0,
                        'questiondecimalpoints' => -1,
                        'introformat'           => 0,
                        'showblocks'            => 0,
                    ),
                )
            ),
            new convert_path('quiz_question_instances',
                    '/MOODLE_BACKUP/COURSE/MODULES/MOD/QUIZ/QUESTION_INSTANCES'),
            new convert_path('quiz_question_instance',
                    '/MOODLE_BACKUP/COURSE/MODULES/MOD/QUIZ/QUESTION_INSTANCES/QUESTION_INSTANCE',
                array(
                    'renamefields' => array(
                        'question' => 'questionid',
                        'grade'    => 'maxmark',
                    ),
                )
            ),
            new convert_path('quiz_feedbacks',
                    '/MOODLE_BACKUP/COURSE/MODULES/MOD/QUIZ/FEEDBACKS'),
            new convert_path('quiz_feedback',
                    '/MOODLE_BACKUP/COURSE/MODULES/MOD/QUIZ/FEEDBACKS/FEEDBACK',
                array(
                    'newfields' => array(
                        'feedbacktextformat' => FORMAT_HTML,
                    )
                )
            )
        );
    }

    
    public function process_quiz($data) {
        global $CFG;

                if (is_null($data['sumgrades'])) {
            $data['sumgrades'] = 0;
                                }

                if ($CFG->texteditors !== 'textarea') {
            $data['intro']       = text_to_html($data['intro'], false, false, true);
            $data['introformat'] = FORMAT_HTML;
        }

                $data['timelimit'] *= 60;

                $instanceid     = $data['id'];
        $cminfo         = $this->get_cminfo($instanceid);
        $this->moduleid = $cminfo['id'];
        $contextid      = $this->converter->get_contextid(CONTEXT_MODULE, $this->moduleid);

                $this->fileman = $this->converter->get_file_manager($contextid, 'mod_quiz');

                $this->fileman->filearea = 'intro';
        $this->fileman->itemid   = 0;
        $data['intro'] = moodle1_converter::migrate_referenced_files(
                $data['intro'], $this->fileman);

                $this->open_xml_writer("activities/quiz_{$this->moduleid}/quiz.xml");
        $this->xmlwriter->begin_tag('activity', array('id' => $instanceid,
                'moduleid' => $this->moduleid, 'modulename' => 'quiz',
                'contextid' => $contextid));
        $this->xmlwriter->begin_tag('quiz', array('id' => $instanceid));

        foreach ($data as $field => $value) {
            if ($field <> 'id') {
                $this->xmlwriter->full_tag($field, $value);
            }
        }

        return $data;
    }

    public function on_quiz_question_instances_start() {
        $this->xmlwriter->begin_tag('question_instances');
    }

    public function on_quiz_question_instances_end() {
        $this->xmlwriter->end_tag('question_instances');
    }

    public function process_quiz_question_instance($data) {
        $this->write_xml('question_instance', $data, array('/question_instance/id'));
    }

    public function on_quiz_feedbacks_start() {
        $this->xmlwriter->begin_tag('feedbacks');
    }

    public function on_quiz_feedbacks_end() {
        $this->xmlwriter->end_tag('feedbacks');
    }

    public function process_quiz_feedback($data) {
                if (is_null($data['mingrade'])) {
            $data['mingrade'] = 0;
        }
        if (is_null($data['maxgrade'])) {
            $data['maxgrade'] = 0;
        }

        $this->write_xml('feedback', $data, array('/feedback/id'));
    }

    
    public function on_quiz_end() {

                $this->write_xml('overrides', array());

                $this->xmlwriter->end_tag('quiz');
        $this->xmlwriter->end_tag('activity');
        $this->close_xml_writer();

                $this->open_xml_writer("activities/quiz_{$this->moduleid}/inforef.xml");
        $this->xmlwriter->begin_tag('inforef');
        $this->xmlwriter->begin_tag('fileref');
        foreach ($this->fileman->get_fileids() as $fileid) {
            $this->write_xml('file', array('id' => $fileid));
        }
        $this->xmlwriter->end_tag('fileref');
        $this->xmlwriter->end_tag('inforef');
        $this->close_xml_writer();
    }
}
