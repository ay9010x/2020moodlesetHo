<?php




defined('MOODLE_INTERNAL') || die();

 
define("LESSON_PAGE_SHORTANSWER",   "1");

class lesson_page_type_shortanswer extends lesson_page {

    protected $type = lesson_page::TYPE_QUESTION;
    protected $typeidstring = 'shortanswer';
    protected $typeid = LESSON_PAGE_SHORTANSWER;
    protected $string = null;

    public function get_typeid() {
        return $this->typeid;
    }
    public function get_typestring() {
        if ($this->string===null) {
            $this->string = get_string($this->typeidstring, 'lesson');
        }
        return $this->string;
    }
    public function get_idstring() {
        return $this->typeidstring;
    }
    public function display($renderer, $attempt) {
        global $USER, $CFG, $PAGE;
        $mform = new lesson_display_answer_form_shortanswer($CFG->wwwroot.'/mod/lesson/continue.php', array('contents'=>$this->get_contents(), 'lessonid'=>$this->lesson->id));
        $data = new stdClass;
        $data->id = $PAGE->cm->id;
        $data->pageid = $this->properties->id;
        if (isset($USER->modattempts[$this->lesson->id])) {
            $data->answer = s($attempt->useranswer);
        }
        $mform->set_data($data);

                $eventparams = array(
            'context' => context_module::instance($PAGE->cm->id),
            'objectid' => $this->properties->id,
            'other' => array(
                    'pagetype' => $this->get_typestring()
                )
            );

        $event = \mod_lesson\event\question_viewed::create($eventparams);
        $event->trigger();
        return $mform->display();
    }
    public function check_answer() {
        global $CFG;
        $result = parent::check_answer();

        $mform = new lesson_display_answer_form_shortanswer($CFG->wwwroot.'/mod/lesson/continue.php', array('contents'=>$this->get_contents()));
        $data = $mform->get_data();
        require_sesskey();

        $studentanswer = trim($data->answer);
        if ($studentanswer === '') {
            $result->noanswer = true;
            return $result;
        }

        $i=0;
        $answers = $this->get_answers();
        foreach ($answers as $answer) {
            $answer = parent::rewrite_answers_urls($answer, false);
            $i++;
                        $expectedanswer  = clean_param($answer->answer, PARAM_TEXT);
            $ismatch         = false;
            $markit          = false;
            $useregexp       = ($this->qoption);

            if ($useregexp) {                 $ignorecase = '';
                if (substr($expectedanswer, -2) == '/i') {
                    $expectedanswer = substr($expectedanswer, 0, -2);
                    $ignorecase = 'i';
                }
            } else {
                $expectedanswer = str_replace('*', '#####', $expectedanswer);
                $expectedanswer = preg_quote($expectedanswer, '/');
                $expectedanswer = str_replace('#####', '.*', $expectedanswer);
            }
                        if ((!$this->lesson->custom && $this->lesson->jumpto_is_correct($this->properties->id, $answer->jumpto)) or ($this->lesson->custom && $answer->score > 0) ) {
                if (!$useregexp) {                     if (preg_match('/^'.$expectedanswer.'$/i',$studentanswer)) {
                        $ismatch = true;
                    }
                } else {
                    if (preg_match('/^'.$expectedanswer.'$/'.$ignorecase,$studentanswer)) {
                        $ismatch = true;
                    }
                }
                if ($ismatch == true) {
                    $result->correctanswer = true;
                }
            } else {
               if (!$useregexp) {                                         if (preg_match('/^'.$expectedanswer.'$/i',$studentanswer)) {
                        $ismatch = true;
                    }
                } else {                     $startcode = substr($expectedanswer,0,2);
                    switch ($startcode){
                                                case "--":
                            $expectedanswer = substr($expectedanswer,2);
                            if (!preg_match('/^'.$expectedanswer.'$/'.$ignorecase,$studentanswer)) {
                                $ismatch = true;
                            }
                            break;
                                                case "++":
                            $expectedanswer=substr($expectedanswer,2);
                            $markit = true;
                                                        if (preg_match_all('/'.$expectedanswer.'/'.$ignorecase,$studentanswer, $matches)) {
                                $ismatch   = true;
                                $nb        = count($matches[0]);
                                $original  = array();
                                $marked    = array();
                                $fontStart = '<span class="incorrect matches">';
                                $fontEnd   = '</span>';
                                for ($i = 0; $i < $nb; $i++) {
                                    array_push($original,$matches[0][$i]);
                                    array_push($marked,$fontStart.$matches[0][$i].$fontEnd);
                                }
                                $studentanswer = str_replace($original, $marked, $studentanswer);
                            }
                            break;
                                                default:
                            if (preg_match('/^'.$expectedanswer.'$/'.$ignorecase,$studentanswer, $matches)) {
                                $ismatch = true;
                            }
                            break;
                    }
                    $result->correctanswer = false;
                }
            }
            if ($ismatch) {
                $result->newpageid = $answer->jumpto;
                $options = new stdClass();
                $options->para = false;
                $result->response = format_text($answer->response, $answer->responseformat, $options);
                $result->answerid = $answer->id;
                break;             }
        }
        $result->userresponse = $studentanswer;
                $result->studentanswer = s($studentanswer);
        return $result;
    }

    public function option_description_string() {
        if ($this->properties->qoption) {
            return " - ".get_string("casesensitive", "lesson");
        }
        return parent::option_description_string();
    }

    public function display_answers(html_table $table) {
        $answers = $this->get_answers();
        $options = new stdClass;
        $options->noclean = true;
        $options->para = false;
        $i = 1;
        foreach ($answers as $answer) {
            $answer = parent::rewrite_answers_urls($answer, false);
            $cells = array();
            if ($this->lesson->custom && $answer->score > 0) {
                                $cells[] = '<span class="labelcorrect">'.get_string("answer", "lesson")." $i</span>: \n";
            } else if ($this->lesson->custom) {
                $cells[] = '<span class="label">'.get_string("answer", "lesson")." $i</span>: \n";
            } else if ($this->lesson->jumpto_is_correct($this->properties->id, $answer->jumpto)) {
                                $cells[] = '<span class="correct">'.get_string("answer", "lesson")." $i</span>: \n";
            } else {
                $cells[] = '<span class="labelcorrect">'.get_string("answer", "lesson")." $i</span>: \n";
            }
            $cells[] = format_text($answer->answer, $answer->answerformat, $options);
            $table->data[] = new html_table_row($cells);

            $cells = array();
            $cells[] = "<span class=\"label\">".get_string("response", "lesson")." $i</span>";
            $cells[] = format_text($answer->response, $answer->responseformat, $options);
            $table->data[] = new html_table_row($cells);

            $cells = array();
            $cells[] = "<span class=\"label\">".get_string("score", "lesson").'</span>';
            $cells[] = $answer->score;
            $table->data[] = new html_table_row($cells);

            $cells = array();
            $cells[] = "<span class=\"label\">".get_string("jump", "lesson").'</span>';
            $cells[] = $this->get_jump_name($answer->jumpto);
            $table->data[] = new html_table_row($cells);
            if ($i === 1){
                $table->data[count($table->data)-1]->cells[0]->style = 'width:20%;';
            }
            $i++;
        }
        return $table;
    }
    public function stats(array &$pagestats, $tries) {
        if(count($tries) > $this->lesson->maxattempts) {             $temp = $tries[$this->lesson->maxattempts - 1];
        } else {
                        $temp = end($tries);
        }
        if (isset($pagestats[$temp->pageid][$temp->useranswer])) {
            $pagestats[$temp->pageid][$temp->useranswer]++;
        } else {
            $pagestats[$temp->pageid][$temp->useranswer] = 1;
        }
        if (isset($pagestats[$temp->pageid]["total"])) {
            $pagestats[$temp->pageid]["total"]++;
        } else {
            $pagestats[$temp->pageid]["total"] = 1;
        }
        return true;
    }

    public function report_answers($answerpage, $answerdata, $useranswer, $pagestats, &$i, &$n) {
        global $PAGE;

        $answers = $this->get_answers();
        $formattextdefoptions = new stdClass;
        $formattextdefoptions->para = false;          foreach ($answers as $answer) {
            $answer = parent::rewrite_answers_urls($answer, false);
            if ($useranswer == null && $i == 0) {
                                if (isset($pagestats[$this->properties->id])) {
                    $stats = $pagestats[$this->properties->id];
                    $total = $stats["total"];
                    unset($stats["total"]);
                    foreach ($stats as $valentered => $ntimes) {
                        $data = '<input type="text" size="50" disabled="disabled" readonly="readonly" value="'.s($valentered).'" />';
                        $percent = $ntimes / $total * 100;
                        $percent = round($percent, 2);
                        $percent .= "% ".get_string("enteredthis", "lesson");
                        $answerdata->answers[] = array($data, $percent);
                    }
                } else {
                    $answerdata->answers[] = array(get_string("nooneansweredthisquestion", "lesson"), " ");
                }
                $i++;
            } else if ($useranswer != null && ($answer->id == $useranswer->answerid || $answer == end($answers))) {
                                 $data = '<input type="text" size="50" disabled="disabled" readonly="readonly" value="'.s($useranswer->useranswer).'">';
                if (isset($pagestats[$this->properties->id][$useranswer->useranswer])) {
                    $percent = $pagestats[$this->properties->id][$useranswer->useranswer] / $pagestats[$this->properties->id]["total"] * 100;
                    $percent = round($percent, 2);
                    $percent .= "% ".get_string("enteredthis", "lesson");
                } else {
                    $percent = get_string("nooneenteredthis", "lesson");
                }
                $answerdata->answers[] = array($data, $percent);

                if ($answer->id == $useranswer->answerid) {
                    if ($answer->response == null) {
                        if ($useranswer->correct) {
                            $answerdata->response = get_string("thatsthecorrectanswer", "lesson");
                        } else {
                            $answerdata->response = get_string("thatsthewronganswer", "lesson");
                        }
                    } else {
                        $answerdata->response = $answer->response;
                    }
                    if ($this->lesson->custom) {
                        $answerdata->score = get_string("pointsearned", "lesson").": ".$answer->score;
                    } elseif ($useranswer->correct) {
                        $answerdata->score = get_string("receivedcredit", "lesson");
                    } else {
                        $answerdata->score = get_string("didnotreceivecredit", "lesson");
                    }
                                        $answerpage->answerdata = $answerdata;
                    break;
                } else {
                    $answerdata->response = get_string("thatsthewronganswer", "lesson");
                    if ($this->lesson->custom) {
                        $answerdata->score = get_string("pointsearned", "lesson").": 0";
                    } else {
                        $answerdata->score = get_string("didnotreceivecredit", "lesson");
                    }
                }
            }
            $answerpage->answerdata = $answerdata;
        }
        return $answerpage;
    }
}


class lesson_add_page_form_shortanswer extends lesson_add_page_form_base {
    public $qtype = 'shortanswer';
    public $qtypestring = 'shortanswer';
    protected $answerformat = '';
    protected $responseformat = LESSON_ANSWER_HTML;

    public function custom_definition() {

        $this->_form->addElement('checkbox', 'qoption', get_string('options', 'lesson'), get_string('casesensitive', 'lesson'));         $this->_form->setDefault('qoption', 0);
        $this->_form->addHelpButton('qoption', 'casesensitive', 'lesson');

        for ($i = 0; $i < $this->_customdata['lesson']->maxanswers; $i++) {
            $this->_form->addElement('header', 'answertitle'.$i, get_string('answer').' '.($i+1));
                        $this->add_answer($i, null, ($i < 1));
            $this->add_response($i);
            $this->add_jumpto($i, null, ($i == 0 ? LESSON_NEXTPAGE : LESSON_THISPAGE));
            $this->add_score($i, null, ($i===0)?1:0);
        }
    }
}

class lesson_display_answer_form_shortanswer extends moodleform {

    public function definition() {
        global $OUTPUT, $USER;
        $mform = $this->_form;
        $contents = $this->_customdata['contents'];

        $hasattempt = false;
        $attrs = array('size'=>'50', 'maxlength'=>'200');
        if (isset($this->_customdata['lessonid'])) {
            $lessonid = $this->_customdata['lessonid'];
            if (isset($USER->modattempts[$lessonid]->useranswer)) {
                $attrs['readonly'] = 'readonly';
                $hasattempt = true;
            }
        }

        $placeholder = false;
        if (preg_match('/_____+/', $contents, $matches)) {
            $placeholder = $matches[0];
            $contentsparts = explode( $placeholder, $contents, 2);
            $attrs['size'] = round(strlen($placeholder) * 1.1);
        }

                $mform->setDisableShortforms();

        $mform->addElement('header', 'pageheader');
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'pageid');
        $mform->setType('pageid', PARAM_INT);

        if ($placeholder) {
            $contentsgroup = array();
            $contentsgroup[] = $mform->createElement('static', '', '', $contentsparts[0]);
            $contentsgroup[] = $mform->createElement('text', 'answer', '', $attrs);
            $contentsgroup[] = $mform->createElement('static', '', '', $contentsparts[1]);
            $mform->addGroup($contentsgroup, '', '', '', false);
        } else {
            $mform->addElement('html', $OUTPUT->container($contents, 'contents'));
            $mform->addElement('text', 'answer', get_string('youranswer', 'lesson'), $attrs);

        }
        $mform->setType('answer', PARAM_TEXT);

        if ($hasattempt) {
            $this->add_action_buttons(null, get_string("nextpage", "lesson"));
        } else {
            $this->add_action_buttons(null, get_string("submit", "lesson"));
        }
    }

}
