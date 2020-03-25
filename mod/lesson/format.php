<?php




defined('MOODLE_INTERNAL') || die();


function lesson_import_question_files($field, $data, $answer, $contextid) {
    global $DB;
    if (!isset($data['itemid'])) {
        return;
    }
    $text = file_save_draft_area_files($data['itemid'],
            $contextid, 'mod_lesson', 'page_' . $field . 's', $answer->id,
            array('subdirs' => false, 'maxfiles' => -1, 'maxbytes' => 0),
            $answer->$field);

    $DB->set_field("lesson_answers", $field, $text, array("id" => $answer->id));
}


function lesson_save_question_options($question, $lesson, $contextid) {
    global $DB;

            if (!($lesson instanceof lesson)) {
        $lesson = new lesson($lesson);
    }
    $manager = lesson_page_type_manager::get($lesson);

    $timenow = time();
    $result = new stdClass();

        $defaultanswer = new stdClass();
    $defaultanswer->lessonid   = $question->lessonid;
    $defaultanswer->pageid = $question->id;
    $defaultanswer->timecreated   = $timenow;
    $defaultanswer->answerformat = FORMAT_HTML;
    $defaultanswer->jumpto = LESSON_THISPAGE;
    $defaultanswer->grade = 0;
    $defaultanswer->score = 0;

    switch ($question->qtype) {
        case LESSON_PAGE_SHORTANSWER:

            $answers = array();
            $maxfraction = -1;

                        foreach ($question->answer as $key => $dataanswer) {
                if ($dataanswer != "") {
                    $answer = clone($defaultanswer);
                    if ($question->fraction[$key] >=0.5) {
                        $answer->jumpto = LESSON_NEXTPAGE;
                        $answer->score = 1;
                    }
                    $answer->grade = round($question->fraction[$key] * 100);
                    $answer->answer   = $dataanswer;
                    $answer->response = $question->feedback[$key]['text'];
                    $answer->responseformat = $question->feedback[$key]['format'];
                    $answer->id = $DB->insert_record("lesson_answers", $answer);
                    lesson_import_question_files('response', $question->feedback[$key], $answer, $contextid);
                    $answers[] = $answer->id;
                    if ($question->fraction[$key] > $maxfraction) {
                        $maxfraction = $question->fraction[$key];
                    }
                }
            }


                        if ($maxfraction != 1) {
                $maxfraction = $maxfraction * 100;
                $result->notice = get_string("fractionsnomax", "lesson", $maxfraction);
                return $result;
            }
            break;

        case LESSON_PAGE_NUMERICAL:   
            $answers = array();
            $maxfraction = -1;


                        foreach ($question->answer as $key => $dataanswer) {
                if ($dataanswer != "") {
                    $answer = clone($defaultanswer);
                    if ($question->fraction[$key] >= 0.5) {
                        $answer->jumpto = LESSON_NEXTPAGE;
                        $answer->score = 1;
                    }
                    $answer->grade = round($question->fraction[$key] * 100);
                    $min = $question->answer[$key] - $question->tolerance[$key];
                    $max = $question->answer[$key] + $question->tolerance[$key];
                    $answer->answer   = $min.":".$max;
                    $answer->response = $question->feedback[$key]['text'];
                    $answer->responseformat = $question->feedback[$key]['format'];
                    $answer->id = $DB->insert_record("lesson_answers", $answer);
                    lesson_import_question_files('response', $question->feedback[$key], $answer, $contextid);

                    $answers[] = $answer->id;
                    if ($question->fraction[$key] > $maxfraction) {
                        $maxfraction = $question->fraction[$key];
                    }
                }
            }

                        if ($maxfraction != 1) {
                $maxfraction = $maxfraction * 100;
                $result->notice = get_string("fractionsnomax", "lesson", $maxfraction);
                return $result;
            }
        break;


        case LESSON_PAGE_TRUEFALSE:

                                    $answer = clone($defaultanswer);
            $answer->grade = 100;
            $answer->jumpto = LESSON_NEXTPAGE;
            $answer->score = 1;
            if ($question->correctanswer) {
                $answer->answer = get_string("true", "lesson");
                if (isset($question->feedbacktrue)) {
                    $answer->response = $question->feedbacktrue['text'];
                    $answer->responseformat = $question->feedbacktrue['format'];
                    $answer->id = $DB->insert_record("lesson_answers", $answer);
                    lesson_import_question_files('response', $question->feedbacktrue, $answer, $contextid);
                }
            } else {
                $answer->answer = get_string("false", "lesson");
                if (isset($question->feedbackfalse)) {
                    $answer->response = $question->feedbackfalse['text'];
                    $answer->responseformat = $question->feedbackfalse['format'];
                    $answer->id = $DB->insert_record("lesson_answers", $answer);
                    lesson_import_question_files('response', $question->feedbackfalse, $answer, $contextid);
                }
            }

                        $answer = clone($defaultanswer);
            if ($question->correctanswer) {
                $answer->answer = get_string("false", "lesson");
                if (isset($question->feedbackfalse)) {
                    $answer->response = $question->feedbackfalse['text'];
                    $answer->responseformat = $question->feedbackfalse['format'];
                    $answer->id = $DB->insert_record("lesson_answers", $answer);
                    lesson_import_question_files('response', $question->feedbackfalse, $answer, $contextid);
                }
            } else {
                $answer->answer = get_string("true", "lesson");
                if (isset($question->feedbacktrue)) {
                    $answer->response = $question->feedbacktrue['text'];
                    $answer->responseformat = $question->feedbacktrue['format'];
                    $answer->id = $DB->insert_record("lesson_answers", $answer);
                    lesson_import_question_files('response', $question->feedbacktrue, $answer, $contextid);
                }
            }

          break;

        case LESSON_PAGE_MULTICHOICE:

            $totalfraction = 0;
            $maxfraction = -1;

            $answers = array();

                        foreach ($question->answer as $key => $dataanswer) {
                if ($dataanswer != "") {
                    $answer = clone($defaultanswer);
                    $answer->grade = round($question->fraction[$key] * 100);

                    if ($question->single) {
                        if ($answer->grade > 50) {
                            $answer->jumpto = LESSON_NEXTPAGE;
                            $answer->score = 1;
                        }
                    } else {
                                                if ($question->fraction[$key] > 0) {
                            $answer->jumpto = LESSON_NEXTPAGE;
                            $answer->score = 1;
                        }
                    }
                    $answer->answer   = $dataanswer['text'];
                    $answer->answerformat   = $dataanswer['format'];
                    $answer->response = $question->feedback[$key]['text'];
                    $answer->responseformat = $question->feedback[$key]['format'];
                    $answer->id = $DB->insert_record("lesson_answers", $answer);
                    lesson_import_question_files('answer', $dataanswer, $answer, $contextid);
                    lesson_import_question_files('response', $question->feedback[$key], $answer, $contextid);

                                        if ($question->fraction[$key] > 0) {
                        $totalfraction += $question->fraction[$key];
                    }
                    if ($question->fraction[$key] > $maxfraction) {
                        $maxfraction = $question->fraction[$key];
                    }
                }
            }

                        if ($question->single) {
                if ($maxfraction != 1) {
                    $maxfraction = $maxfraction * 100;
                    $result->notice = get_string("fractionsnomax", "lesson", $maxfraction);
                    return $result;
                }
            } else {
                $totalfraction = round($totalfraction,2);
                if ($totalfraction != 1) {
                    $totalfraction = $totalfraction * 100;
                    $result->notice = get_string("fractionsaddwrong", "lesson", $totalfraction);
                    return $result;
                }
            }
        break;

        case LESSON_PAGE_MATCHING:

            $subquestions = array();

                        $correctanswer = clone($defaultanswer);
            $correctanswer->answer = get_string('thatsthecorrectanswer', 'lesson');
            $correctanswer->jumpto = LESSON_NEXTPAGE;
            $correctanswer->score = 1;
            $DB->insert_record("lesson_answers", $correctanswer);

                        $wronganswer = clone($defaultanswer);
            $wronganswer->answer = get_string('thatsthewronganswer', 'lesson');
            $DB->insert_record("lesson_answers", $wronganswer);

            $i = 0;
                        foreach ($question->subquestions as $key => $questiontext) {
                $answertext = $question->subanswers[$key];
                if (!empty($questiontext) and !empty($answertext)) {
                    $answer = clone($defaultanswer);
                    $answer->answer = $questiontext['text'];
                    $answer->answerformat   = $questiontext['format'];
                    $answer->response   = $answertext;
                    if ($i == 0) {
                                                $answer->jumpto = LESSON_NEXTPAGE;
                    }
                    $answer->id = $DB->insert_record("lesson_answers", $answer);
                    lesson_import_question_files('answer', $questiontext, $answer, $contextid);
                    $subquestions[] = $answer->id;
                    $i++;
                }
            }

            if (count($subquestions) < 3) {
                $result->notice = get_string("notenoughsubquestions", "lesson");
                return $result;
            }
            break;

        case LESSON_PAGE_ESSAY:
            $answer = new stdClass();
            $answer->lessonid = $question->lessonid;
            $answer->pageid = $question->id;
            $answer->timecreated = $timenow;
            $answer->answer = null;
            $answer->answerformat = FORMAT_MOODLE;
            $answer->grade = 0;
            $answer->score = 1;
            $answer->jumpto = LESSON_NEXTPAGE;
            $answer->response = null;
            $answer->responseformat = FORMAT_MOODLE;
            $answer->id = $DB->insert_record("lesson_answers", $answer);
        break;
        default:
            $result->error = "Unsupported question type ($question->qtype)!";
            return $result;
    }
    return true;
}


class qformat_default {

    var $displayerrors = true;
    var $category = null;
    var $questionids = array();
    protected $importcontext = null;
    var $qtypeconvert = array('numerical'   => LESSON_PAGE_NUMERICAL,
                               'multichoice' => LESSON_PAGE_MULTICHOICE,
                               'truefalse'   => LESSON_PAGE_TRUEFALSE,
                               'shortanswer' => LESSON_PAGE_SHORTANSWER,
                               'match'       => LESSON_PAGE_MATCHING,
                               'essay'       => LESSON_PAGE_ESSAY
                              );

        function provide_import() {
        return false;
    }

    function set_importcontext($context) {
        $this->importcontext = $context;
    }

    
    protected function error($message, $text='', $questionname='') {
        $importerrorquestion = get_string('importerrorquestion', 'question');

        echo "<div class=\"importerror\">\n";
        echo "<strong>$importerrorquestion $questionname</strong>";
        if (!empty($text)) {
            $text = s($text);
            echo "<blockquote>$text</blockquote>\n";
        }
        echo "<strong>$message</strong>\n";
        echo "</div>";
    }

    
    public function try_importing_using_qtypes($data, $question = null, $extra = null,
            $qtypehint = '') {

        return false;
    }

    function importpreprocess() {
                return true;
    }

    function importprocess($filename, $lesson, $pageid) {
        global $DB, $OUTPUT;

            $timenow = time();

        if (! $lines = $this->readdata($filename)) {
            echo $OUTPUT->notification("File could not be read, or was empty");
            return false;
        }

        if (! $questions = $this->readquestions($lines)) {               echo $OUTPUT->notification("There are no questions in this file!");
            return false;
        }

                echo $OUTPUT->notification(get_string('importcount', 'lesson',
                $this->count_questions($questions)), 'notifysuccess');

        $count = 0;
        $addquestionontop = false;
        if ($pageid == 0) {
            $addquestionontop = true;
            $updatelessonpage = $DB->get_record('lesson_pages', array('lessonid' => $lesson->id, 'prevpageid' => 0));
        } else {
            $updatelessonpage = $DB->get_record('lesson_pages', array('lessonid' => $lesson->id, 'id' => $pageid));
        }

        $unsupportedquestions = 0;

        foreach ($questions as $question) {               switch ($question->qtype) {
                                case 'category':
                    break;
                                case 'shortanswer' :
                case 'numerical' :
                case 'truefalse' :
                case 'multichoice' :
                case 'match' :
                case 'essay' :
                    $count++;

                                        echo "<hr><p><b>$count</b>. ".$this->format_question_text($question)."</p>";

                    $newpage = new stdClass;
                    $newpage->lessonid = $lesson->id;
                    $newpage->qtype = $this->qtypeconvert[$question->qtype];
                    switch ($question->qtype) {
                        case 'shortanswer' :
                            if (isset($question->usecase)) {
                                $newpage->qoption = $question->usecase;
                            }
                            break;
                        case 'multichoice' :
                            if (isset($question->single)) {
                                $newpage->qoption = !$question->single;
                            }
                            break;
                    }
                    $newpage->timecreated = $timenow;
                    if ($question->name != $question->questiontext) {
                        $newpage->title = $question->name;
                    } else {
                        $newpage->title = "Page $count";
                    }
                    $newpage->contents = $question->questiontext;
                    $newpage->contentsformat = isset($question->questionformat) ? $question->questionformat : FORMAT_HTML;

                                        if ($pageid) {
                                                if (!$page = $DB->get_record("lesson_pages", array("id" => $pageid))) {
                            print_error('invalidpageid', 'lesson');
                        }
                        $newpage->prevpageid = $pageid;
                        $newpage->nextpageid = $page->nextpageid;
                                                $newpageid = $DB->insert_record("lesson_pages", $newpage);
                                                $DB->set_field("lesson_pages", "nextpageid", $newpageid, array("id" => $pageid));
                    } else {
                                                                        $params = array ("lessonid" => $lesson->id, "prevpageid" => 0);
                        if (!$page = $DB->get_record_select("lesson_pages", "lessonid = :lessonid AND prevpageid = :prevpageid", $params)) {
                                                        $newpage->prevpageid = 0;                             $newpage->nextpageid = 0;                             $newpageid = $DB->insert_record("lesson_pages", $newpage);
                        } else {
                                                        $newpage->prevpageid = 0;                             $newpage->nextpageid = $page->id;
                            $newpageid = $DB->insert_record("lesson_pages", $newpage);
                                                        $DB->set_field("lesson_pages", "prevpageid", $newpageid, array("id" => $page->id));
                        }
                    }

                                        $pageid = $newpageid;
                    $question->id = $newpageid;

                    $this->questionids[] = $question->id;

                                        if (isset($question->questiontextitemid)) {
                        $questiontext = file_save_draft_area_files($question->questiontextitemid,
                                $this->importcontext->id, 'mod_lesson', 'page_contents', $newpageid,
                                null , $question->questiontext);
                                                $DB->set_field("lesson_pages", "contents", $questiontext, array("id" => $newpageid));
                    }

                    
                    $question->lessonid = $lesson->id;                     $question->qtype = $this->qtypeconvert[$question->qtype];
                    $result = lesson_save_question_options($question, $lesson, $this->importcontext->id);

                    if (!empty($result->error)) {
                        echo $OUTPUT->notification($result->error);
                        return false;
                    }

                    if (!empty($result->notice)) {
                        echo $OUTPUT->notification($result->notice);
                        return true;
                    }
                    break;
                            default :
                    $unsupportedquestions++;
                    break;
            }
        }
                if (!empty($updatelessonpage)) {
            if ($addquestionontop) {
                $DB->set_field("lesson_pages", "prevpageid", $pageid, array("id" => $updatelessonpage->id));
            } else {
                $DB->set_field("lesson_pages", "prevpageid", $pageid, array("id" => $updatelessonpage->nextpageid));
            }
        }
        if ($unsupportedquestions) {
            echo $OUTPUT->notification(get_string('unknownqtypesnotimported', 'lesson', $unsupportedquestions));
        }
        return true;
    }

    
    protected function count_questions($questions) {
        $count = 0;
        if (!is_array($questions)) {
            return $count;
        }
        foreach ($questions as $question) {
            if (!is_object($question) || !isset($question->qtype) ||
                    ($question->qtype == 'category')) {
                continue;
            }
            $count++;
        }
        return $count;
    }

    function readdata($filename) {
    
        if (is_readable($filename)) {
            $filearray = file($filename);

                        if (preg_match("/\r/", $filearray[0]) AND !preg_match("/\n/", $filearray[0])) {
                return explode("\r", $filearray[0]);
            } else {
                return $filearray;
            }
        }
        return false;
    }

    protected function readquestions($lines) {
                
        $questions = array();
        $currentquestion = array();

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                if (!empty($currentquestion)) {
                    if ($question = $this->readquestion($currentquestion)) {
                        $questions[] = $question;
                    }
                    $currentquestion = array();
                }
            } else {
                $currentquestion[] = $line;
            }
        }

        if (!empty($currentquestion)) {              if ($question = $this->readquestion($currentquestion)) {
                $questions[] = $question;
            }
        }

        return $questions;
    }


    protected function readquestion($lines) {
            
                throw new coding_exception('Question format plugin is missing important code: readquestion.');

        return null;
    }

    
    public function create_default_question_name($questiontext, $default) {
        $name = $this->clean_question_name(shorten_text($questiontext, 80));
        if ($name) {
            return $name;
        } else {
            return $default;
        }
    }

    
    public function clean_question_name($name) {
        $name = clean_param($name, PARAM_TEXT);         $name = trim($name);
        $trimlength = 251;
        while (core_text::strlen($name) > 255 && $trimlength > 0) {
            $name = shorten_text($name, $trimlength);
            $trimlength -= 10;
        }
        return $name;
    }

    
    protected function defaultquestion() {
        global $CFG;
        static $defaultshuffleanswers = null;
        if (is_null($defaultshuffleanswers)) {
            $defaultshuffleanswers = get_config('quiz', 'shuffleanswers');
        }

        $question = new stdClass();
        $question->shuffleanswers = $defaultshuffleanswers;
        $question->defaultmark = 1;
        $question->image = "";
        $question->usecase = 0;
        $question->multiplier = array();
        $question->questiontextformat = FORMAT_MOODLE;
        $question->generalfeedback = '';
        $question->generalfeedbackformat = FORMAT_MOODLE;
        $question->correctfeedback = '';
        $question->partiallycorrectfeedback = '';
        $question->incorrectfeedback = '';
        $question->answernumbering = 'abc';
        $question->penalty = 0.3333333;
        $question->length = 1;
        $question->qoption = 0;
        $question->layout = 1;

                        $question->export_process = true;
        $question->import_process = true;

        return $question;
    }

    function importpostprocess() {
                                return true;
    }

    
    protected function format_question_text($question) {
        $formatoptions = new stdClass();
        $formatoptions->noclean = true;
                                        $text = str_replace('@@PLUGINFILE@@/', 'http://example.com/', $question->questiontext);
        return html_to_text(format_text($text,
                $question->questiontextformat, $formatoptions), 0, false);
    }

    
    protected function add_blank_combined_feedback($question) {
        return $question;
    }
}



class qformat_based_on_xml extends qformat_default {
    
    public function cleaninput($str) {

        $html_code_list = array(
            "&#039;" => "'",
            "&#8217;" => "'",
            "&#8220;" => "\"",
            "&#8221;" => "\"",
            "&#8211;" => "-",
            "&#8212;" => "-",
        );
        $str = strtr($str, $html_code_list);
                $str = core_text::entities_to_utf8($str, false);
        return $str;
    }

    
    public function text_field($text) {
        return array(
            'text' => trim($text),
            'format' => FORMAT_HTML,
            'files' => array(),
        );
    }

    
    public function getpath($xml, $path, $default, $istext=false, $error='') {
        foreach ($path as $index) {
            if (!isset($xml[$index])) {
                if (!empty($error)) {
                    $this->error($error);
                    return false;
                } else {
                    return $default;
                }
            }

            $xml = $xml[$index];
        }

        if ($istext) {
            if (!is_string($xml)) {
                $this->error(get_string('invalidxml', 'qformat_xml'));
            }
            $xml = trim($xml);
        }

        return $xml;
    }
}
