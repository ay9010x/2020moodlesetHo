<?php





defined('MOODLE_INTERNAL') || die();


require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/lesson/lib.php');
require_once($CFG->libdir . '/filelib.php');


define('LESSON_THISPAGE', 0);

define("LESSON_UNSEENPAGE", 1);

define("LESSON_UNANSWEREDPAGE", 2);

define("LESSON_NEXTPAGE", -1);

define("LESSON_EOL", -9);

define("LESSON_UNSEENBRANCHPAGE", -50);

define("LESSON_PREVIOUSPAGE", -40);

define("LESSON_RANDOMPAGE", -60);

define("LESSON_RANDOMBRANCH", -70);

define("LESSON_CLUSTERJUMP", -80);

define("LESSON_UNDEFINED", -99);


define("LESSON_MAX_EVENT_LENGTH", "432000");


define("LESSON_ANSWER_HTML", "HTML");



function lesson_display_teacher_warning($lesson) {
    global $DB;

        $params = array ("lessonid" => $lesson->id);
    if (!$lessonanswers = $DB->get_records_select("lesson_answers", "lessonid = :lessonid", $params)) {
                return false;
    }
        foreach ($lessonanswers as $lessonanswer) {
        if ($lessonanswer->jumpto == LESSON_CLUSTERJUMP || $lessonanswer->jumpto == LESSON_UNSEENBRANCHPAGE) {
            return true;
        }
    }

        return false;
}


function lesson_unseen_question_jump($lesson, $user, $pageid) {
    global $DB;

        if (!$retakes = $DB->count_records("lesson_grades", array("lessonid"=>$lesson->id, "userid"=>$user))) {
        $retakes = 0;
    }

        if ($viewedpages = $DB->get_records("lesson_attempts", array("lessonid"=>$lesson->id, "userid"=>$user, "retry"=>$retakes), "timeseen DESC")) {
        foreach($viewedpages as $viewed) {
            $seenpages[] = $viewed->pageid;
        }
    } else {
        $seenpages = array();
    }

        $lessonpages = $lesson->load_all_pages();

    if ($pageid == LESSON_UNSEENBRANCHPAGE) {          $pageid = $seenpages[0];      }

        while ($pageid != 0) {         if ($lessonpages[$pageid]->qtype == LESSON_PAGE_BRANCHTABLE) {
            break;
        }
        $pageid = $lessonpages[$pageid]->prevpageid;
    }

    $pagesinbranch = $lesson->get_sub_pages_of($pageid, array(LESSON_PAGE_BRANCHTABLE, LESSON_PAGE_ENDOFBRANCH));

        $unseen = array();
    foreach($pagesinbranch as $page) {
        if (!in_array($page->id, $seenpages)) {
            $unseen[] = $page->id;
        }
    }

    if(count($unseen) == 0) {
        if(isset($pagesinbranch)) {
            $temp = end($pagesinbranch);
            $nextpage = $temp->nextpageid;         } else {
                        $nextpage = $lessonpages[$pageid]->nextpageid;
        }
        if ($nextpage == 0) {
            return LESSON_EOL;
        } else {
            return $nextpage;
        }
    } else {
        return $unseen[rand(0, count($unseen)-1)];      }
}


function lesson_unseen_branch_jump($lesson, $userid) {
    global $DB;

    if (!$retakes = $DB->count_records("lesson_grades", array("lessonid"=>$lesson->id, "userid"=>$userid))) {
        $retakes = 0;
    }

    $params = array ("lessonid" => $lesson->id, "userid" => $userid, "retry" => $retakes);
    if (!$seenbranches = $DB->get_records_select("lesson_branch", "lessonid = :lessonid AND userid = :userid AND retry = :retry", $params,
                "timeseen DESC")) {
        print_error('cannotfindrecords', 'lesson');
    }

        $lessonpages = $lesson->load_all_pages();

            $seen = array();
    foreach ($seenbranches as $seenbranch) {
        if (!$seenbranch->flag) {
            $seen[$seenbranch->pageid] = $seenbranch->pageid;
        } else {
            $start = $seenbranch->pageid;
            break;
        }
    }
            $pageid = $lessonpages[$start]->nextpageid;     $branchtables = array();
    while ($pageid != 0) {          if ($lessonpages[$pageid]->qtype == LESSON_PAGE_BRANCHTABLE) {
            $branchtables[] = $lessonpages[$pageid]->id;
        }
        $pageid = $lessonpages[$pageid]->nextpageid;
    }
    $unseen = array();
    foreach ($branchtables as $branchtable) {
                if (!array_key_exists($branchtable, $seen)) {
            $unseen[] = $branchtable;
        }
    }
    if (count($unseen) > 0) {
        return $unseen[rand(0, count($unseen)-1)];      } else {
        return LESSON_EOL;      }
}


function lesson_random_question_jump($lesson, $pageid) {
    global $DB;

        $params = array ("lessonid" => $lesson->id);
    if (!$lessonpages = $DB->get_records_select("lesson_pages", "lessonid = :lessonid", $params)) {
        print_error('cannotfindpages', 'lesson');
    }

        while ($pageid != 0) { 
        if ($lessonpages[$pageid]->qtype == LESSON_PAGE_BRANCHTABLE) {
            break;
        }
        $pageid = $lessonpages[$pageid]->prevpageid;
    }

        $pagesinbranch = $lesson->get_sub_pages_of($pageid, array(LESSON_PAGE_BRANCHTABLE, LESSON_PAGE_ENDOFBRANCH));

    if(count($pagesinbranch) == 0) {
                return $lessonpages[$pageid]->nextpageid;
    } else {
        return $pagesinbranch[rand(0, count($pagesinbranch)-1)]->id;      }
}


function lesson_grade($lesson, $ntries, $userid = 0) {
    global $USER, $DB;

    if (empty($userid)) {
        $userid = $USER->id;
    }

        $ncorrect     = 0;
    $nviewed      = 0;
    $score        = 0;
    $nmanual      = 0;
    $manualpoints = 0;
    $thegrade     = 0;
    $nquestions   = 0;
    $total        = 0;
    $earned       = 0;

    $params = array ("lessonid" => $lesson->id, "userid" => $userid, "retry" => $ntries);
    if ($useranswers = $DB->get_records_select("lesson_attempts",  "lessonid = :lessonid AND
            userid = :userid AND retry = :retry", $params, "timeseen")) {
                $attemptset = array();
        foreach ($useranswers as $useranswer) {
            $attemptset[$useranswer->pageid][] = $useranswer;
        }

                foreach ($attemptset as $key => $set) {
            $attemptset[$key] = array_slice($set, 0, $lesson->maxattempts);
        }

                list($usql, $parameters) = $DB->get_in_or_equal(array_keys($attemptset));
        array_unshift($parameters, $lesson->id);
        $pages = $DB->get_records_select("lesson_pages", "lessonid = ? AND id $usql", $parameters);
        $answers = $DB->get_records_select("lesson_answers", "lessonid = ? AND pageid $usql", $parameters);

                $nquestions = count($pages);

        foreach ($attemptset as $attempts) {
            $page = lesson_page::load($pages[end($attempts)->pageid], $lesson);
            if ($lesson->custom) {
                $attempt = end($attempts);
                                if ($page->requires_manual_grading()) {
                    $useranswerobj = unserialize($attempt->useranswer);
                    if (isset($useranswerobj->score)) {
                        $earned += $useranswerobj->score;
                    }
                    $nmanual++;
                    $manualpoints += $answers[$attempt->answerid]->score;
                } else if (!empty($attempt->answerid)) {
                    $earned += $page->earned_score($answers, $attempt);
                }
            } else {
                foreach ($attempts as $attempt) {
                    $earned += $attempt->correct;
                }
                $attempt = end($attempts);                                 if ($page->requires_manual_grading()) {
                    $nmanual++;
                    $manualpoints++;
                }
            }
                        $nviewed += count($attempts);
        }

        if ($lesson->custom) {
            $bestscores = array();
                        foreach ($answers as $answer) {
                if(!isset($bestscores[$answer->pageid])) {
                    $bestscores[$answer->pageid] = $answer->score;
                } else if ($bestscores[$answer->pageid] < $answer->score) {
                    $bestscores[$answer->pageid] = $answer->score;
                }
            }
            $total = array_sum($bestscores);
        } else {
                        if ($lesson->minquestions and $nquestions < $lesson->minquestions) {
                                $total =  $nviewed + ($lesson->minquestions - $nquestions);
            } else {
                $total = $nviewed;
            }
        }
    }

    if ($total) {         $thegrade = round(100 * $earned / $total, 5);
    }

        $gradeinfo               = new stdClass;
    $gradeinfo->nquestions   = $nquestions;
    $gradeinfo->attempts     = $nviewed;
    $gradeinfo->total        = $total;
    $gradeinfo->earned       = $earned;
    $gradeinfo->grade        = $thegrade;
    $gradeinfo->nmanual      = $nmanual;
    $gradeinfo->manualpoints = $manualpoints;

    return $gradeinfo;
}


function lesson_displayleftif($lesson) {
    global $CFG, $USER, $DB;

    if (!empty($lesson->displayleftif)) {
                $params = array ("userid" => $USER->id, "lessonid" => $lesson->id);
        if ($maxgrade = $DB->get_record_sql('SELECT userid, MAX(grade) AS maxgrade FROM {lesson_grades} WHERE userid = :userid AND lessonid = :lessonid GROUP BY userid', $params)) {
            if ($maxgrade->maxgrade < $lesson->displayleftif) {
                return 0;              }
        } else {
            return 0;         }
    }

        return $lesson->displayleft;
}


function lesson_add_fake_blocks($page, $cm, $lesson, $timer = null) {
    $bc = lesson_menu_block_contents($cm->id, $lesson);
    if (!empty($bc)) {
        $regions = $page->blocks->get_regions();
        $firstregion = reset($regions);
        $page->blocks->add_fake_block($bc, $firstregion);
    }

    $bc = lesson_mediafile_block_contents($cm->id, $lesson);
    if (!empty($bc)) {
        $page->blocks->add_fake_block($bc, $page->blocks->get_default_region());
    }

    if (!empty($timer)) {
        $bc = lesson_clock_block_contents($cm->id, $lesson, $timer, $page);
        if (!empty($bc)) {
            $page->blocks->add_fake_block($bc, $page->blocks->get_default_region());
        }
    }
}


function lesson_mediafile_block_contents($cmid, $lesson) {
    global $OUTPUT;
    if (empty($lesson->mediafile)) {
        return null;
    }

    $options = array();
    $options['menubar'] = 0;
    $options['location'] = 0;
    $options['left'] = 5;
    $options['top'] = 5;
    $options['scrollbars'] = 1;
    $options['resizable'] = 1;
    $options['width'] = $lesson->mediawidth;
    $options['height'] = $lesson->mediaheight;

    $link = new moodle_url('/mod/lesson/mediafile.php?id='.$cmid);
    $action = new popup_action('click', $link, 'lessonmediafile', $options);
    $content = $OUTPUT->action_link($link, get_string('mediafilepopup', 'lesson'), $action, array('title'=>get_string('mediafilepopup', 'lesson')));

    $bc = new block_contents();
    $bc->title = get_string('linkedmedia', 'lesson');
    $bc->attributes['class'] = 'mediafile block';
    $bc->content = $content;

    return $bc;
}


function lesson_clock_block_contents($cmid, $lesson, $timer, $page) {
        $context = context_module::instance($cmid);
    if ($lesson->timelimit == 0 || has_capability('mod/lesson:manage', $context)) {
        return null;
    }

    $content = '<div id="lesson-timer">';
    $content .=  $lesson->time_remaining($timer->starttime);
    $content .= '</div>';

    $clocksettings = array('starttime' => $timer->starttime, 'servertime' => time(), 'testlength' => $lesson->timelimit);
    $page->requires->data_for_js('clocksettings', $clocksettings, true);
    $page->requires->strings_for_js(array('timeisup'), 'lesson');
    $page->requires->js('/mod/lesson/timer.js');
    $page->requires->js_init_call('show_clock');

    $bc = new block_contents();
    $bc->title = get_string('timeremaining', 'lesson');
    $bc->attributes['class'] = 'clock block';
    $bc->content = $content;

    return $bc;
}


function lesson_menu_block_contents($cmid, $lesson) {
    global $CFG, $DB;

    if (!$lesson->displayleft) {
        return null;
    }

    $pages = $lesson->load_all_pages();
    foreach ($pages as $page) {
        if ((int)$page->prevpageid === 0) {
            $pageid = $page->id;
            break;
        }
    }
    $currentpageid = optional_param('pageid', $pageid, PARAM_INT);

    if (!$pageid || !$pages) {
        return null;
    }

    $content = '<a href="#maincontent" class="skip">'.get_string('skip', 'lesson')."</a>\n<div class=\"menuwrapper\">\n<ul>\n";

    while ($pageid != 0) {
        $page = $pages[$pageid];

                if ($page->displayinmenublock && $page->display) {
            if ($page->id == $currentpageid) {
                $content .= '<li class="selected">'.format_string($page->title,true)."</li>\n";
            } else {
                $content .= "<li class=\"notselected\"><a href=\"$CFG->wwwroot/mod/lesson/view.php?id=$cmid&amp;pageid=$page->id\">".format_string($page->title,true)."</a></li>\n";
            }

        }
        $pageid = $page->nextpageid;
    }
    $content .= "</ul>\n</div>\n";

    $bc = new block_contents();
    $bc->title = get_string('lessonmenu', 'lesson');
    $bc->attributes['class'] = 'menu block';
    $bc->content = $content;

    return $bc;
}


function lesson_add_header_buttons($cm, $context, $extraeditbuttons=false, $lessonpageid=null) {
    global $CFG, $PAGE, $OUTPUT;
    if (has_capability('mod/lesson:edit', $context) && $extraeditbuttons) {
        if ($lessonpageid === null) {
            print_error('invalidpageid', 'lesson');
        }
        if (!empty($lessonpageid) && $lessonpageid != LESSON_EOL) {
            $url = new moodle_url('/mod/lesson/editpage.php', array(
                'id'       => $cm->id,
                'pageid'   => $lessonpageid,
                'edit'     => 1,
                'returnto' => $PAGE->url->out(false)
            ));
            $PAGE->set_button($OUTPUT->single_button($url, get_string('editpagecontent', 'lesson')));
        }
    }
}


function lesson_get_media_html($lesson, $context) {
    global $CFG, $PAGE, $OUTPUT;
    require_once("$CFG->libdir/resourcelib.php");

        if (strpos($lesson->mediafile, '://') !== false) {
        $url = new moodle_url($lesson->mediafile);
    } else {
                $url = moodle_url::make_pluginfile_url($context->id, 'mod_lesson', 'mediafile', $lesson->timemodified, '/', ltrim($lesson->mediafile, '/'));
    }
    $title = $lesson->mediafile;

    $clicktoopen = html_writer::link($url, get_string('download'));

    $mimetype = resourcelib_guess_url_mimetype($url);

    $extension = resourcelib_get_extension($url->out(false));

    $mediarenderer = $PAGE->get_renderer('core', 'media');
    $embedoptions = array(
        core_media::OPTION_TRUSTED => true,
        core_media::OPTION_BLOCK => true
    );

        if (in_array($mimetype, array('image/gif','image/jpeg','image/png'))) {          $code = resourcelib_embed_image($url, $title);

    } else if ($mediarenderer->can_embed_url($url, $embedoptions)) {
                $code = $mediarenderer->embed_url($url, $title, 0, 0, $embedoptions);

    } else {
                $code = resourcelib_embed_general($url, $title, $clicktoopen, $mimetype);
    }

    return $code;
}


function lesson_process_group_deleted_in_course($courseid, $groupid = null) {
    global $DB;

    $params = array('courseid' => $courseid);
    if ($groupid) {
        $params['groupid'] = $groupid;
                $sql = "SELECT o.id, o.lessonid
                  FROM {lesson_overrides} o
                  JOIN {lesson} lesson ON lesson.id = o.lessonid
                 WHERE lesson.course = :courseid
                   AND o.groupid = :groupid";
    } else {
                $sql = "SELECT o.id, o.lessonid
                  FROM {lesson_overrides} o
                  JOIN {lesson} lesson ON lesson.id = o.lessonid
             LEFT JOIN {groups} grp ON grp.id = o.groupid
                 WHERE lesson.course = :courseid
                   AND o.groupid IS NOT NULL
                   AND grp.id IS NULL";
    }
    $records = $DB->get_records_sql_menu($sql, $params);
    if (!$records) {
        return;     }
    $DB->delete_records_list('lesson_overrides', 'id', array_keys($records));
}


abstract class lesson_add_page_form_base extends moodleform {

    
    public $qtype;

    
    public $qtypestring;

    
    protected $editoroptions = array();

    
    protected $standard = true;

    
    protected $answerformat = '';

    
    protected $responseformat = '';

    
    public function custom_definition() {}

    
    public function get_answer_format() {
        return $this->answerformat;
    }

    
    public function get_response_format() {
        return $this->responseformat;
    }

    
    public final function is_standard() {
        return (bool)$this->standard;
    }

    
    public final function definition() {
        $mform = $this->_form;
        $editoroptions = $this->_customdata['editoroptions'];

        $mform->addElement('header', 'qtypeheading', get_string('createaquestionpage', 'lesson', get_string($this->qtypestring, 'lesson')));

        if (!empty($this->_customdata['returnto'])) {
            $mform->addElement('hidden', 'returnto', $this->_customdata['returnto']);
            $mform->setType('returnto', PARAM_URL);
        }

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'pageid');
        $mform->setType('pageid', PARAM_INT);

        if ($this->standard === true) {
            $mform->addElement('hidden', 'qtype');
            $mform->setType('qtype', PARAM_INT);

            $mform->addElement('text', 'title', get_string('pagetitle', 'lesson'), array('size'=>70));
            $mform->setType('title', PARAM_TEXT);
            $mform->addRule('title', get_string('required'), 'required', null, 'client');

            $this->editoroptions = array('noclean'=>true, 'maxfiles'=>EDITOR_UNLIMITED_FILES, 'maxbytes'=>$this->_customdata['maxbytes']);
            $mform->addElement('editor', 'contents_editor', get_string('pagecontents', 'lesson'), null, $this->editoroptions);
            $mform->setType('contents_editor', PARAM_RAW);
            $mform->addRule('contents_editor', get_string('required'), 'required', null, 'client');
        }

        $this->custom_definition();

        if ($this->_customdata['edit'] === true) {
            $mform->addElement('hidden', 'edit', 1);
            $mform->setType('edit', PARAM_BOOL);
            $this->add_action_buttons(get_string('cancel'), get_string('savepage', 'lesson'));
        } else if ($this->qtype === 'questiontype') {
            $this->add_action_buttons(get_string('cancel'), get_string('addaquestionpage', 'lesson'));
        } else {
            $this->add_action_buttons(get_string('cancel'), get_string('savepage', 'lesson'));
        }
    }

    
    protected final function add_jumpto($name, $label=null, $selected=LESSON_NEXTPAGE) {
        $title = get_string("jump", "lesson");
        if ($label === null) {
            $label = $title;
        }
        if (is_int($name)) {
            $name = "jumpto[$name]";
        }
        $this->_form->addElement('select', $name, $label, $this->_customdata['jumpto']);
        $this->_form->setDefault($name, $selected);
        $this->_form->addHelpButton($name, 'jumps', 'lesson');
    }

    
    protected final function add_score($name, $label=null, $value=null) {
        if ($label === null) {
            $label = get_string("score", "lesson");
        }

        if (is_int($name)) {
            $name = "score[$name]";
        }
        $this->_form->addElement('text', $name, $label, array('size'=>5));
        $this->_form->setType($name, PARAM_INT);
        if ($value !== null) {
            $this->_form->setDefault($name, $value);
        }
        $this->_form->addHelpButton($name, 'score', 'lesson');

                if (!$this->_customdata['lesson']->custom) {
            $this->_form->freeze($name);
        }
    }

    
    protected final function add_answer($count, $label = null, $required = false, $format= '') {
        if ($label === null) {
            $label = get_string('answer', 'lesson');
        }

        if ($format == LESSON_ANSWER_HTML) {
            $this->_form->addElement('editor', 'answer_editor['.$count.']', $label,
                    array('rows' => '4', 'columns' => '80'),
                    array('noclean' => true, 'maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $this->_customdata['maxbytes']));
            $this->_form->setType('answer_editor['.$count.']', PARAM_RAW);
            $this->_form->setDefault('answer_editor['.$count.']', array('text' => '', 'format' => FORMAT_HTML));
        } else {
            $this->_form->addElement('text', 'answer_editor['.$count.']', $label,
                    array('size' => '50', 'maxlength' => '200'));
            $this->_form->setType('answer_editor['.$count.']', PARAM_TEXT);
        }

        if ($required) {
            $this->_form->addRule('answer_editor['.$count.']', get_string('required'), 'required', null, 'client');
        }
    }
    
    protected final function add_response($count, $label = null, $required = false) {
        if ($label === null) {
            $label = get_string('response', 'lesson');
        }
        $this->_form->addElement('editor', 'response_editor['.$count.']', $label,
                 array('rows' => '4', 'columns' => '80'),
                 array('noclean' => true, 'maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $this->_customdata['maxbytes']));
        $this->_form->setType('response_editor['.$count.']', PARAM_RAW);
        $this->_form->setDefault('response_editor['.$count.']', array('text' => '', 'format' => FORMAT_HTML));

        if ($required) {
            $this->_form->addRule('response_editor['.$count.']', get_string('required'), 'required', null, 'client');
        }
    }

    
    public function construction_override($pageid, lesson $lesson) {
        return true;
    }
}




class lesson extends lesson_base {

    
    protected $firstpageid = null;
    
    protected $lastpageid = null;
    
    protected $pages = array();
    
    protected $loadedallpages = false;

    
    public static function create($properties) {
        return new lesson($properties);
    }

    
    public static function load($lessonid) {
        global $DB;

        if (!$lesson = $DB->get_record('lesson', array('id' => $lessonid))) {
            print_error('invalidcoursemodule');
        }
        return new lesson($lesson);
    }

    
    public function delete() {
        global $CFG, $DB;
        require_once($CFG->libdir.'/gradelib.php');
        require_once($CFG->dirroot.'/calendar/lib.php');

        $cm = get_coursemodule_from_instance('lesson', $this->properties->id, $this->properties->course);
        $context = context_module::instance($cm->id);

        $this->delete_all_overrides();

        $DB->delete_records("lesson", array("id"=>$this->properties->id));
        $DB->delete_records("lesson_pages", array("lessonid"=>$this->properties->id));
        $DB->delete_records("lesson_answers", array("lessonid"=>$this->properties->id));
        $DB->delete_records("lesson_attempts", array("lessonid"=>$this->properties->id));
        $DB->delete_records("lesson_grades", array("lessonid"=>$this->properties->id));
        $DB->delete_records("lesson_timer", array("lessonid"=>$this->properties->id));
        $DB->delete_records("lesson_branch", array("lessonid"=>$this->properties->id));
        if ($events = $DB->get_records('event', array("modulename"=>'lesson', "instance"=>$this->properties->id))) {
            foreach($events as $event) {
                $event = calendar_event::load($event);
                $event->delete();
            }
        }

                $fs = get_file_storage();
        $fs->delete_area_files($context->id);

        grade_update('mod/lesson', $this->properties->course, 'mod', 'lesson', $this->properties->id, 0, null, array('deleted'=>1));
        return true;
    }

    
    public function delete_override($overrideid) {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/calendar/lib.php');

        $cm = get_coursemodule_from_instance('lesson', $this->properties->id, $this->properties->course);

        $override = $DB->get_record('lesson_overrides', array('id' => $overrideid), '*', MUST_EXIST);

                $conds = array('modulename' => 'lesson',
                'instance' => $this->properties->id);
        if (isset($override->userid)) {
            $conds['userid'] = $override->userid;
        } else {
            $conds['groupid'] = $override->groupid;
        }
        $events = $DB->get_records('event', $conds);
        foreach ($events as $event) {
            $eventold = calendar_event::load($event);
            $eventold->delete();
        }

        $DB->delete_records('lesson_overrides', array('id' => $overrideid));

                $params = array(
            'objectid' => $override->id,
            'context' => context_module::instance($cm->id),
            'other' => array(
                'lessonid' => $override->lessonid
            )
        );
                if (!empty($override->userid)) {
            $params['relateduserid'] = $override->userid;
            $event = \mod_lesson\event\user_override_deleted::create($params);
        } else {
            $params['other']['groupid'] = $override->groupid;
            $event = \mod_lesson\event\group_override_deleted::create($params);
        }

                $event->add_record_snapshot('lesson_overrides', $override);
        $event->trigger();

        return true;
    }

    
    public function delete_all_overrides() {
        global $DB;

        $overrides = $DB->get_records('lesson_overrides', array('lessonid' => $this->properties->id), 'id');
        foreach ($overrides as $override) {
            $this->delete_override($override->id);
        }
    }

    
    public function update_effective_access($userid) {
        global $DB;

                $override = $DB->get_record('lesson_overrides', array('lessonid' => $this->properties->id, 'userid' => $userid));

        if (!$override) {
            $override = new stdClass();
            $override->available = null;
            $override->deadline = null;
            $override->timelimit = null;
            $override->review = null;
            $override->maxattempts = null;
            $override->retake = null;
            $override->password = null;
        }

                $groupings = groups_get_user_groups($this->properties->course, $userid);

        if (!empty($groupings[0])) {
                        list($extra, $params) = $DB->get_in_or_equal(array_values($groupings[0]));
            $sql = "SELECT * FROM {lesson_overrides}
                    WHERE groupid $extra AND lessonid = ?";
            $params[] = $this->properties->id;
            $records = $DB->get_records_sql($sql, $params);

                        $availables = array();
            $deadlines = array();
            $timelimits = array();
            $reviews = array();
            $attempts = array();
            $retakes = array();
            $passwords = array();

            foreach ($records as $gpoverride) {
                if (isset($gpoverride->available)) {
                    $availables[] = $gpoverride->available;
                }
                if (isset($gpoverride->deadline)) {
                    $deadlines[] = $gpoverride->deadline;
                }
                if (isset($gpoverride->timelimit)) {
                    $timelimits[] = $gpoverride->timelimit;
                }
                if (isset($gpoverride->review)) {
                    $reviews[] = $gpoverride->review;
                }
                if (isset($gpoverride->maxattempts)) {
                    $attempts[] = $gpoverride->maxattempts;
                }
                if (isset($gpoverride->retake)) {
                    $retakes[] = $gpoverride->retake;
                }
                if (isset($gpoverride->password)) {
                    $passwords[] = $gpoverride->password;
                }
            }
                        if (is_null($override->available) && count($availables)) {
                $override->available = min($availables);
            }
            if (is_null($override->deadline) && count($deadlines)) {
                if (in_array(0, $deadlines)) {
                    $override->deadline = 0;
                } else {
                    $override->deadline = max($deadlines);
                }
            }
            if (is_null($override->timelimit) && count($timelimits)) {
                if (in_array(0, $timelimits)) {
                    $override->timelimit = 0;
                } else {
                    $override->timelimit = max($timelimits);
                }
            }
            if (is_null($override->review) && count($reviews)) {
                $override->review = max($reviews);
            }
            if (is_null($override->maxattempts) && count($attempts)) {
                $override->maxattempts = max($attempts);
            }
            if (is_null($override->retake) && count($retakes)) {
                $override->retake = max($retakes);
            }
            if (is_null($override->password) && count($passwords)) {
                $override->password = array_shift($passwords);
                if (count($passwords)) {
                    $override->extrapasswords = $passwords;
                }
            }

        }

                $keys = array('available', 'deadline', 'timelimit', 'maxattempts', 'review', 'retake');
        foreach ($keys as $key) {
            if (isset($override->{$key})) {
                $this->properties->{$key} = $override->{$key};
            }
        }

                if (isset($override->password)) {
            if ($override->password == '') {
                $this->properties->usepassword = 0;
            } else {
                $this->properties->usepassword = 1;
                $this->properties->password = $override->password;
                if (isset($override->extrapasswords)) {
                    $this->properties->extrapasswords = $override->extrapasswords;
                }
            }
        }
    }

    
    protected function get_messages() {
        global $SESSION;

        $messages = array();
        if (!empty($SESSION->lesson_messages) && is_array($SESSION->lesson_messages) && array_key_exists($this->properties->id, $SESSION->lesson_messages)) {
            $messages = $SESSION->lesson_messages[$this->properties->id];
            unset($SESSION->lesson_messages[$this->properties->id]);
        }

        return $messages;
    }

    
    public function get_attempts($retries, $correct=false, $pageid=null, $userid=null) {
        global $USER, $DB;
        $params = array("lessonid"=>$this->properties->id, "userid"=>$userid, "retry"=>$retries);
        if ($correct) {
            $params['correct'] = 1;
        }
        if ($pageid !== null) {
            $params['pageid'] = $pageid;
        }
        if ($userid === null) {
            $params['userid'] = $USER->id;
        }
        return $DB->get_records('lesson_attempts', $params, 'timeseen ASC');
    }

    
    protected function get_firstpage() {
        $pages = $this->load_all_pages();
        if (count($pages) > 0) {
            foreach ($pages as $page) {
                if ((int)$page->prevpageid === 0) {
                    return $page;
                }
            }
        }
        return false;
    }

    
    protected function get_lastpage() {
        $pages = $this->load_all_pages();
        if (count($pages) > 0) {
            foreach ($pages as $page) {
                if ((int)$page->nextpageid === 0) {
                    return $page;
                }
            }
        }
        return false;
    }

    
    protected function get_firstpageid() {
        global $DB;
        if ($this->firstpageid == null) {
            if (!$this->loadedallpages) {
                $firstpageid = $DB->get_field('lesson_pages', 'id', array('lessonid'=>$this->properties->id, 'prevpageid'=>0));
                if (!$firstpageid) {
                    print_error('cannotfindfirstpage', 'lesson');
                }
                $this->firstpageid = $firstpageid;
            } else {
                $firstpage = $this->get_firstpage();
                $this->firstpageid = $firstpage->id;
            }
        }
        return $this->firstpageid;
    }

    
    public function get_lastpageid() {
        global $DB;
        if ($this->lastpageid == null) {
            if (!$this->loadedallpages) {
                $lastpageid = $DB->get_field('lesson_pages', 'id', array('lessonid'=>$this->properties->id, 'nextpageid'=>0));
                if (!$lastpageid) {
                    print_error('cannotfindlastpage', 'lesson');
                }
                $this->lastpageid = $lastpageid;
            } else {
                $lastpageid = $this->get_lastpage();
                $this->lastpageid = $lastpageid->id;
            }
        }

        return $this->lastpageid;
    }

     
    public function get_next_page($nextpageid) {
        global $USER, $DB;
        $allpages = $this->load_all_pages();
        if ($this->properties->nextpagedefault) {
                        $nretakes = $DB->count_records("lesson_grades", array("lessonid" => $this->properties->id, "userid" => $USER->id));
            shuffle($allpages);
            $found = false;
            if ($this->properties->nextpagedefault == LESSON_UNSEENPAGE) {
                foreach ($allpages as $nextpage) {
                    if (!$DB->count_records("lesson_attempts", array("pageid" => $nextpage->id, "userid" => $USER->id, "retry" => $nretakes))) {
                        $found = true;
                        break;
                    }
                }
            } elseif ($this->properties->nextpagedefault == LESSON_UNANSWEREDPAGE) {
                foreach ($allpages as $nextpage) {
                    if (!$DB->count_records("lesson_attempts", array('pageid' => $nextpage->id, 'userid' => $USER->id, 'correct' => 1, 'retry' => $nretakes))) {
                        $found = true;
                        break;
                    }
                }
            }
            if ($found) {
                if ($this->properties->maxpages) {
                                        if ($DB->count_records("lesson_attempts", array("lessonid" => $this->properties->id, "userid" => $USER->id, "retry" => $nretakes)) >= $this->properties->maxpages) {
                        return LESSON_EOL;
                    }
                }
                return $nextpage->id;
            }
        }
                foreach ($allpages as $nextpage) {
            if ((int)$nextpage->id === (int)$nextpageid) {
                return $nextpage->id;
            }
        }
        return LESSON_EOL;
    }

    
    public function add_message($message, $class="notifyproblem", $align='center') {
        global $SESSION;

        if (empty($SESSION->lesson_messages) || !is_array($SESSION->lesson_messages)) {
            $SESSION->lesson_messages = array();
            $SESSION->lesson_messages[$this->properties->id] = array();
        } else if (!array_key_exists($this->properties->id, $SESSION->lesson_messages)) {
            $SESSION->lesson_messages[$this->properties->id] = array();
        }

        $SESSION->lesson_messages[$this->properties->id][] = array($message, $class, $align);

        return true;
    }

    
    public function is_accessible() {
        $available = $this->properties->available;
        $deadline = $this->properties->deadline;
        return (($available == 0 || time() >= $available) && ($deadline == 0 || time() < $deadline));
    }

    
    public function start_timer() {
        global $USER, $DB;

        $cm = get_coursemodule_from_instance('lesson', $this->properties()->id, $this->properties()->course,
            false, MUST_EXIST);

                $event = \mod_lesson\event\lesson_started::create(array(
            'objectid' => $this->properties()->id,
            'context' => context_module::instance($cm->id),
            'courseid' => $this->properties()->course
        ));
        $event->trigger();

        $USER->startlesson[$this->properties->id] = true;
        $startlesson = new stdClass;
        $startlesson->lessonid = $this->properties->id;
        $startlesson->userid = $USER->id;
        $startlesson->starttime = time();
        $startlesson->lessontime = time();
        $DB->insert_record('lesson_timer', $startlesson);
        if ($this->properties->timelimit) {
            $this->add_message(get_string('timelimitwarning', 'lesson', format_time($this->properties->timelimit)), 'center');
        }
        return true;
    }

    
    public function update_timer($restart=false, $continue=false, $endreached =false) {
        global $USER, $DB;

        $cm = get_coursemodule_from_instance('lesson', $this->properties->id, $this->properties->course);

                        $params = array("lessonid" => $this->properties->id, "userid" => $USER->id);
        if (!$timer = $DB->get_records('lesson_timer', $params, 'starttime DESC', '*', 0, 1)) {
            $this->start_timer();
            $timer = $DB->get_records('lesson_timer', $params, 'starttime DESC', '*', 0, 1);
        }
        $timer = current($timer); 
        if ($restart) {
            if ($continue) {
                                $timer->starttime = time() - ($timer->lessontime - $timer->starttime);

                                $event = \mod_lesson\event\lesson_resumed::create(array(
                    'objectid' => $this->properties->id,
                    'context' => context_module::instance($cm->id),
                    'courseid' => $this->properties->course
                ));
                $event->trigger();

            } else {
                                $timer->starttime = time();

                                $event = \mod_lesson\event\lesson_restarted::create(array(
                    'objectid' => $this->properties->id,
                    'context' => context_module::instance($cm->id),
                    'courseid' => $this->properties->course
                ));
                $event->trigger();

            }
        }

        $timer->lessontime = time();
        $timer->completed = $endreached;
        $DB->update_record('lesson_timer', $timer);

                $cm = get_coursemodule_from_instance('lesson', $this->properties()->id, $this->properties()->course,
            false, MUST_EXIST);
        $course = get_course($cm->course);
        $completion = new completion_info($course);
        if ($completion->is_enabled($cm) && $this->properties()->completiontimespent > 0) {
            $completion->update_state($cm, COMPLETION_COMPLETE);
        }
        return $timer;
    }

    
    public function stop_timer() {
        global $USER, $DB;
        unset($USER->startlesson[$this->properties->id]);

        $cm = get_coursemodule_from_instance('lesson', $this->properties()->id, $this->properties()->course,
            false, MUST_EXIST);

                $event = \mod_lesson\event\lesson_ended::create(array(
            'objectid' => $this->properties()->id,
            'context' => context_module::instance($cm->id),
            'courseid' => $this->properties()->course
        ));
        $event->trigger();

        return $this->update_timer(false, false, true);
    }

    
    public function has_pages() {
        global $DB;
        $pagecount = $DB->count_records('lesson_pages', array('lessonid'=>$this->properties->id));
        return ($pagecount>0);
    }

    
    public function link_for_activitylink() {
        global $DB;
        $module = $DB->get_record('course_modules', array('id' => $this->properties->activitylink));
        if ($module) {
            $modname = $DB->get_field('modules', 'name', array('id' => $module->module));
            if ($modname) {
                $instancename = $DB->get_field($modname, 'name', array('id' => $module->instance));
                if ($instancename) {
                    return html_writer::link(new moodle_url('/mod/'.$modname.'/view.php', array('id'=>$this->properties->activitylink)),
                        get_string('activitylinkname', 'lesson', $instancename),
                        array('class'=>'centerpadded lessonbutton standardbutton'));
                }
            }
        }
        return '';
    }

    
    public function load_page($pageid) {
        if (!array_key_exists($pageid, $this->pages)) {
            $manager = lesson_page_type_manager::get($this);
            $this->pages[$pageid] = $manager->load_page($pageid, $this);
        }
        return $this->pages[$pageid];
    }

    
    public function load_all_pages() {
        if (!$this->loadedallpages) {
            $manager = lesson_page_type_manager::get($this);
            $this->pages = $manager->load_all_pages($this);
            $this->loadedallpages = true;
        }
        return $this->pages;
    }

    
    public function jumpto_is_correct($pageid, $jumpto) {
        global $DB;

                if (!$jumpto) {
                        return false;
        } elseif ($jumpto == LESSON_NEXTPAGE) {
            return true;
        } elseif ($jumpto == LESSON_UNSEENBRANCHPAGE) {
            return true;
        } elseif ($jumpto == LESSON_RANDOMPAGE) {
            return true;
        } elseif ($jumpto == LESSON_CLUSTERJUMP) {
            return true;
        } elseif ($jumpto == LESSON_EOL) {
            return true;
        }

        $pages = $this->load_all_pages();
        $apageid = $pages[$pageid]->nextpageid;
        while ($apageid != 0) {
            if ($jumpto == $apageid) {
                return true;
            }
            $apageid = $pages[$apageid]->nextpageid;
        }
        return false;
    }

    
    public function time_remaining($starttime) {
        $timeleft = $starttime + $this->properties->timelimit - time();
        $hours = floor($timeleft/3600);
        $timeleft = $timeleft - ($hours * 3600);
        $minutes = floor($timeleft/60);
        $secs = $timeleft - ($minutes * 60);

        if ($minutes < 10) {
            $minutes = "0$minutes";
        }
        if ($secs < 10) {
            $secs = "0$secs";
        }
        $output   = array();
        $output[] = $hours;
        $output[] = $minutes;
        $output[] = $secs;
        $output = implode(':', $output);
        return $output;
    }

    
    public function cluster_jump($pageid, $userid=null) {
        global $DB, $USER;

        if ($userid===null) {
            $userid = $USER->id;
        }
                if (!$retakes = $DB->count_records("lesson_grades", array("lessonid"=>$this->properties->id, "userid"=>$userid))) {
            $retakes = 0;
        }
                $seenpages = array();
        if ($attempts = $this->get_attempts($retakes)) {
            foreach ($attempts as $attempt) {
                $seenpages[$attempt->pageid] = $attempt->pageid;
            }

        }

                $lessonpages = $this->load_all_pages();
                while ($pageid != 0) {             if ($lessonpages[$pageid]->qtype == LESSON_PAGE_CLUSTER) {
                break;
            }
            $pageid = $lessonpages[$pageid]->prevpageid;
        }

        $clusterpages = array();
        $clusterpages = $this->get_sub_pages_of($pageid, array(LESSON_PAGE_ENDOFCLUSTER));
        $unseen = array();
        foreach ($clusterpages as $key=>$cluster) {
                        if ($this->is_sub_page_of_type($cluster->id,
                    array(LESSON_PAGE_BRANCHTABLE), array(LESSON_PAGE_ENDOFBRANCH, LESSON_PAGE_CLUSTER))
                    || $cluster->qtype == LESSON_PAGE_ENDOFBRANCH) {
                unset($clusterpages[$key]);
            } else if ($cluster->qtype == LESSON_PAGE_BRANCHTABLE) {
                                $branchpages = $this->get_sub_pages_of($cluster->id, array(LESSON_PAGE_BRANCHTABLE, LESSON_PAGE_ENDOFBRANCH));
                $flag = true;
                foreach ($branchpages as $branchpage) {
                    if (array_key_exists($branchpage->id, $seenpages)) {                          $flag = false;
                    }
                }
                if ($flag && count($branchpages) > 0) {
                                        $unseen[] = $cluster;
                }
            } elseif ($cluster->is_unseen($seenpages)) {
                $unseen[] = $cluster;
            }
        }

        if (count($unseen) > 0) {
                        $nextpage = $unseen[rand(0, count($unseen)-1)];
            if ($nextpage->qtype == LESSON_PAGE_BRANCHTABLE) {
                                $branchpages = $this->get_sub_pages_of($nextpage->id, array(LESSON_PAGE_BRANCHTABLE, LESSON_PAGE_ENDOFBRANCH));
                return $branchpages[rand(0, count($branchpages)-1)]->id;
            } else {                 return $nextpage->id;
            }
        } else {
                        if (end($clusterpages)->nextpageid == 0) {
                return LESSON_EOL;
            } else {
                $clusterendid = $pageid;
                while ($clusterendid != 0) {                     if ($lessonpages[$clusterendid]->qtype == LESSON_PAGE_ENDOFCLUSTER) {
                        break;
                    }
                    $clusterendid = $lessonpages[$clusterendid]->nextpageid;
                }
                $exitjump = $DB->get_field("lesson_answers", "jumpto", array("pageid" => $clusterendid, "lessonid" => $this->properties->id));
                if ($exitjump == LESSON_NEXTPAGE) {
                    $exitjump = $lessonpages[$clusterendid]->nextpageid;
                }
                if ($exitjump == 0) {
                    return LESSON_EOL;
                } else if (in_array($exitjump, array(LESSON_EOL, LESSON_PREVIOUSPAGE))) {
                    return $exitjump;
                } else {
                    if (!array_key_exists($exitjump, $lessonpages)) {
                        $found = false;
                        foreach ($lessonpages as $page) {
                            if ($page->id === $clusterendid) {
                                $found = true;
                            } else if ($page->qtype == LESSON_PAGE_ENDOFCLUSTER) {
                                $exitjump = $DB->get_field("lesson_answers", "jumpto", array("pageid" => $page->id, "lessonid" => $this->properties->id));
                                if ($exitjump == LESSON_NEXTPAGE) {
                                    $exitjump = $lessonpages[$page->id]->nextpageid;
                                }
                                break;
                            }
                        }
                    }
                    if (!array_key_exists($exitjump, $lessonpages)) {
                        return LESSON_EOL;
                    }
                    return $exitjump;
                }
            }
        }
    }

    
    public function get_sub_pages_of($pageid, array $ends) {
        $lessonpages = $this->load_all_pages();
        $pageid = $lessonpages[$pageid]->nextpageid;          $pages = array();

        while (true) {
            if ($pageid == 0 || in_array($lessonpages[$pageid]->qtype, $ends)) {
                break;
            }
            $pages[] = $lessonpages[$pageid];
            $pageid = $lessonpages[$pageid]->nextpageid;
        }

        return $pages;
    }

    
    public function is_sub_page_of_type($pageid, array $types, array $ends) {
        $pages = $this->load_all_pages();
        $pageid = $pages[$pageid]->prevpageid; 
        array_unshift($ends, 0);
                while (true) {
            if ($pageid==0 || in_array($pages[$pageid]->qtype, $ends)) {
                return false;
            } else if (in_array($pages[$pageid]->qtype, $types)) {
                return true;
            }
            $pageid = $pages[$pageid]->prevpageid;
        }
    }

    
    public function resort_pages($pageid, $after) {
        global $CFG;

        $cm = get_coursemodule_from_instance('lesson', $this->properties->id, $this->properties->course);
        $context = context_module::instance($cm->id);

        $pages = $this->load_all_pages();

        if (!array_key_exists($pageid, $pages) || ($after!=0 && !array_key_exists($after, $pages))) {
            print_error('cannotfindpages', 'lesson', "$CFG->wwwroot/mod/lesson/edit.php?id=$cm->id");
        }

        $pagetomove = clone($pages[$pageid]);
        unset($pages[$pageid]);

        $pageids = array();
        if ($after === 0) {
            $pageids['p0'] = $pageid;
        }
        foreach ($pages as $page) {
            $pageids[] = $page->id;
            if ($page->id == $after) {
                $pageids[] = $pageid;
            }
        }

        $pageidsref = $pageids;
        reset($pageidsref);
        $prev = 0;
        $next = next($pageidsref);
        foreach ($pageids as $pid) {
            if ($pid === $pageid) {
                $page = $pagetomove;
            } else {
                $page = $pages[$pid];
            }
            if ($page->prevpageid != $prev || $page->nextpageid != $next) {
                $page->move($next, $prev);

                if ($pid === $pageid) {
                                        $pageupdated = array('next' => $next, 'prev' => $prev);
                }
            }

            $prev = $page->id;
            $next = next($pageidsref);
            if (!$next) {
                $next = 0;
            }
        }

                if (!empty($pageupdated)) {
            $eventparams = array(
                'context' => $context,
                'objectid' => $pageid,
                'other' => array(
                    'pagetype' => $page->get_typestring(),
                    'prevpageid' => $pageupdated['prev'],
                    'nextpageid' => $pageupdated['next']
                )
            );
            $event = \mod_lesson\event\page_moved::create($eventparams);
            $event->trigger();
        }

    }
}



abstract class lesson_base {

    
    protected $properties;

    
    public function __construct($properties) {
        $this->properties = (object)$properties;
    }

    
    public function __set($key, $value) {
        if (method_exists($this, 'set_'.$key)) {
            $this->{'set_'.$key}($value);
        }
        $this->properties->{$key} = $value;
    }

    
    public function __get($key) {
        if (method_exists($this, 'get_'.$key)) {
            return $this->{'get_'.$key}();
        }
        return $this->properties->{$key};
    }

    
    public function __isset($key) {
        if (method_exists($this, 'get_'.$key)) {
            $val = $this->{'get_'.$key}();
            return !empty($val);
        }
        return !empty($this->properties->{$key});
    }

    
    
        
        
    public function properties() {
        return $this->properties;
    }
}



abstract class lesson_page extends lesson_base {

    
    protected $lesson = null;
    
    protected $answers = null;
    
    protected $type = 0;

    
    const TYPE_QUESTION = 0;
    const TYPE_STRUCTURE = 1;

    
    abstract protected function get_typeid();
    
    abstract protected function get_typestring();

    
    abstract public function display($renderer, $attempt);

    
    final public static function create($properties, lesson $lesson, $context, $maxbytes) {
        global $DB;
        $newpage = new stdClass;
        $newpage->title = $properties->title;
        $newpage->contents = $properties->contents_editor['text'];
        $newpage->contentsformat = $properties->contents_editor['format'];
        $newpage->lessonid = $lesson->id;
        $newpage->timecreated = time();
        $newpage->qtype = $properties->qtype;
        $newpage->qoption = (isset($properties->qoption))?1:0;
        $newpage->layout = (isset($properties->layout))?1:0;
        $newpage->display = (isset($properties->display))?1:0;
        $newpage->prevpageid = 0;         $newpage->nextpageid = 0; 
        if ($properties->pageid) {
            $prevpage = $DB->get_record("lesson_pages", array("id" => $properties->pageid), 'id, nextpageid');
            if (!$prevpage) {
                print_error('cannotfindpages', 'lesson');
            }
            $newpage->prevpageid = $prevpage->id;
            $newpage->nextpageid = $prevpage->nextpageid;
        } else {
            $nextpage = $DB->get_record('lesson_pages', array('lessonid'=>$lesson->id, 'prevpageid'=>0), 'id');
            if ($nextpage) {
                                $newpage->nextpageid = $nextpage->id;
            }
        }

        $newpage->id = $DB->insert_record("lesson_pages", $newpage);

        $editor = new stdClass;
        $editor->id = $newpage->id;
        $editor->contents_editor = $properties->contents_editor;
        $editor = file_postupdate_standard_editor($editor, 'contents', array('noclean'=>true, 'maxfiles'=>EDITOR_UNLIMITED_FILES, 'maxbytes'=>$maxbytes), $context, 'mod_lesson', 'page_contents', $editor->id);
        $DB->update_record("lesson_pages", $editor);

        if ($newpage->prevpageid > 0) {
            $DB->set_field("lesson_pages", "nextpageid", $newpage->id, array("id" => $newpage->prevpageid));
        }
        if ($newpage->nextpageid > 0) {
            $DB->set_field("lesson_pages", "prevpageid", $newpage->id, array("id" => $newpage->nextpageid));
        }

        $page = lesson_page::load($newpage, $lesson);
        $page->create_answers($properties);

                $eventparams = array(
            'context' => $context,
            'objectid' => $newpage->id,
            'other' => array(
                'pagetype' => $page->get_typestring()
                )
            );
        $event = \mod_lesson\event\page_created::create($eventparams);
        $snapshot = clone($newpage);
        $snapshot->timemodified = 0;
        $event->add_record_snapshot('lesson_pages', $snapshot);
        $event->trigger();

        $lesson->add_message(get_string('insertedpage', 'lesson').': '.format_string($newpage->title, true), 'notifysuccess');

        return $page;
    }

    
    final public static function load($id, lesson $lesson) {
        global $DB;

        if (is_object($id) && !empty($id->qtype)) {
            $page = $id;
        } else {
            $page = $DB->get_record("lesson_pages", array("id" => $id));
            if (!$page) {
                print_error('cannotfindpages', 'lesson');
            }
        }
        $manager = lesson_page_type_manager::get($lesson);

        $class = 'lesson_page_type_'.$manager->get_page_type_idstring($page->qtype);
        if (!class_exists($class)) {
            $class = 'lesson_page';
        }

        return new $class($page, $lesson);
    }

    
    final public function delete() {
        global $DB;

        $cm = get_coursemodule_from_instance('lesson', $this->lesson->id, $this->lesson->course);
        $context = context_module::instance($cm->id);

                $fs = get_file_storage();
        if ($attempts = $DB->get_records('lesson_attempts', array("pageid" => $this->properties->id))) {
            foreach ($attempts as $attempt) {
                $fs->delete_area_files($context->id, 'mod_lesson', 'essay_responses', $attempt->id);
            }
        }

                $DB->delete_records("lesson_attempts", array("pageid" => $this->properties->id));

        $DB->delete_records("lesson_branch", array("pageid" => $this->properties->id));
                $DB->delete_records("lesson_answers", array("pageid" => $this->properties->id));
                $DB->delete_records("lesson_pages", array("id" => $this->properties->id));

                $eventparams = array(
            'context' => $context,
            'objectid' => $this->properties->id,
            'other' => array(
                'pagetype' => $this->get_typestring()
                )
            );
        $event = \mod_lesson\event\page_deleted::create($eventparams);
        $event->add_record_snapshot('lesson_pages', $this->properties);
        $event->trigger();

                $fs->delete_area_files($context->id, 'mod_lesson', 'page_contents', $this->properties->id);
        $fs->delete_area_files($context->id, 'mod_lesson', 'page_answers', $this->properties->id);
        $fs->delete_area_files($context->id, 'mod_lesson', 'page_responses', $this->properties->id);

                if (!$this->properties->prevpageid && !$this->properties->nextpageid) {
                    } elseif (!$this->properties->prevpageid) {
                        $page = $this->lesson->load_page($this->properties->nextpageid);
            $page->move(null, 0);
        } elseif (!$this->properties->nextpageid) {
                        $page = $this->lesson->load_page($this->properties->prevpageid);
            $page->move(0);
        } else {
                        $prevpage = $this->lesson->load_page($this->properties->prevpageid);
            $nextpage = $this->lesson->load_page($this->properties->nextpageid);

            $prevpage->move($nextpage->id);
            $nextpage->move(null, $prevpage->id);
        }
        return true;
    }

    
    final public function move($nextpageid=null, $prevpageid=null) {
        global $DB;
        if ($nextpageid === null) {
            $nextpageid = $this->properties->nextpageid;
        }
        if ($prevpageid === null) {
            $prevpageid = $this->properties->prevpageid;
        }
        $obj = new stdClass;
        $obj->id = $this->properties->id;
        $obj->prevpageid = $prevpageid;
        $obj->nextpageid = $nextpageid;
        $DB->update_record('lesson_pages', $obj);
    }

    
    final public function get_answers() {
        global $DB;
        if ($this->answers === null) {
            $this->answers = array();
            $answers = $DB->get_records('lesson_answers', array('pageid'=>$this->properties->id, 'lessonid'=>$this->lesson->id), 'id');
            if (!$answers) {
                                                                return array();
            }
            foreach ($answers as $answer) {
                $this->answers[count($this->answers)] = new lesson_page_answer($answer);
            }
        }
        return $this->answers;
    }

    
    final protected function get_lesson() {
        return $this->lesson;
    }

    
    final protected function get_type() {
        return $this->type;
    }

    
    final public function record_attempt($context) {
        global $DB, $USER, $OUTPUT, $PAGE;

        
        $result = $this->check_answer();

        $result->attemptsremaining  = 0;
        $result->maxattemptsreached = false;

        if ($result->noanswer) {
            $result->newpageid = $this->properties->id;             $result->feedback  = get_string('noanswer', 'lesson');
        } else {
            if (!has_capability('mod/lesson:manage', $context)) {
                $nretakes = $DB->count_records("lesson_grades", array("lessonid"=>$this->lesson->id, "userid"=>$USER->id));

                                $nattempts = $DB->count_records('lesson_attempts', array('lessonid' => $this->lesson->id,
                    'userid' => $USER->id, 'pageid' => $this->properties->id, 'retry' => $nretakes));

                                if ($nattempts >= $this->lesson->maxattempts) {
                    $result->maxattemptsreached = true;
                    $result->feedback = get_string('maximumnumberofattemptsreached', 'lesson');
                    $result->newpageid = $this->lesson->get_next_page($this->properties->nextpageid);
                    return $result;
                }

                                $attempt = new stdClass;
                $attempt->lessonid = $this->lesson->id;
                $attempt->pageid = $this->properties->id;
                $attempt->userid = $USER->id;
                $attempt->answerid = $result->answerid;
                $attempt->retry = $nretakes;
                $attempt->correct = $result->correctanswer;
                if($result->userresponse !== null) {
                    $attempt->useranswer = $result->userresponse;
                }

                $attempt->timeseen = time();
                                $userisreviewing = false;
                if (isset($USER->modattempts[$this->lesson->id])) {
                    $attempt->retry = $nretakes - 1;                     $userisreviewing = true;
                }

                                if (!$userisreviewing) {
                    if ($this->lesson->retake || (!$this->lesson->retake && $nretakes == 0)) {
                        $attempt->id = $DB->insert_record("lesson_attempts", $attempt);
                                                $eventparams = array(
                            'context' => context_module::instance($PAGE->cm->id),
                            'objectid' => $this->properties->id,
                            'other' => array(
                                'pagetype' => $this->get_typestring()
                                )
                            );
                        $event = \mod_lesson\event\question_answered::create($eventparams);
                        $event->add_record_snapshot('lesson_attempts', $attempt);
                        $event->trigger();

                                                $nattempts++;
                    }
                }
                                                if (!$result->correctanswer && ($result->newpageid == 0)) {
                                        if ($nattempts >= $this->lesson->maxattempts) {
                        if ($this->lesson->maxattempts > 1) {                             $result->maxattemptsreached = true;
                        }
                        $result->newpageid = LESSON_NEXTPAGE;
                    } else if ($this->lesson->maxattempts > 1) {                         $result->attemptsremaining = $this->lesson->maxattempts - $nattempts;
                    }
                }
            }
                        if ($result->newpageid == 0) {
                $result->newpageid = $this->properties->id;
            } elseif ($result->newpageid == LESSON_NEXTPAGE) {
                $result->newpageid = $this->lesson->get_next_page($this->properties->nextpageid);
            }

                        if (empty($result->response)) {
                if (!$this->lesson->feedback && !$result->noanswer && !($this->lesson->review & !$result->correctanswer && !$result->isessayquestion)) {
                                                                                                    
                    $result->nodefaultresponse = true;                  } else if ($result->isessayquestion) {
                    $result->response = get_string('defaultessayresponse', 'lesson');
                } else if ($result->correctanswer) {
                    $result->response = get_string('thatsthecorrectanswer', 'lesson');
                } else {
                    $result->response = get_string('thatsthewronganswer', 'lesson');
                }
            }

            if ($result->response) {
                if ($this->lesson->review && !$result->correctanswer && !$result->isessayquestion) {
                    $nretakes = $DB->count_records("lesson_grades", array("lessonid"=>$this->lesson->id, "userid"=>$USER->id));
                    $qattempts = $DB->count_records("lesson_attempts", array("userid"=>$USER->id, "retry"=>$nretakes, "pageid"=>$this->properties->id));
                    if ($qattempts == 1) {
                        $result->feedback = $OUTPUT->box(get_string("firstwrong", "lesson"), 'feedback');
                    } else {
                        $result->feedback = $OUTPUT->box(get_string("secondpluswrong", "lesson"), 'feedback');
                    }
                } else {
                    $result->feedback = '';
                }
                $class = 'response';
                if ($result->correctanswer) {
                    $class .= ' correct';                 } else if (!$result->isessayquestion) {
                    $class .= ' incorrect';                 }
                $options = new stdClass;
                $options->noclean = true;
                $options->para = true;
                $options->overflowdiv = true;
                $options->context = $context;

                $result->feedback .= $OUTPUT->box(format_text($this->get_contents(), $this->properties->contentsformat, $options),
                        'generalbox boxaligncenter');
                if (isset($result->studentanswerformat)) {
                                        $studentanswer = format_text($result->studentanswer, $result->studentanswerformat,
                            array('context' => $context, 'para' => true));
                } else {
                    $studentanswer = format_string($result->studentanswer);
                }
                $result->feedback .= '<div class="correctanswer generalbox"><em>'
                        . get_string("youranswer", "lesson").'</em> : ' . $studentanswer;
                if (isset($result->responseformat)) {
                    $result->response = file_rewrite_pluginfile_urls($result->response, 'pluginfile.php', $context->id,
                            'mod_lesson', 'page_responses', $result->answerid);
                    $result->feedback .= $OUTPUT->box(format_text($result->response, $result->responseformat, $options)
                            , $class);
                } else {
                    $result->feedback .= $OUTPUT->box($result->response, $class);
                }
                $result->feedback .= '</div>';
            }
        }

        return $result;
    }

    
    final protected function get_jump_name($jumpto) {
        global $DB;
        static $jumpnames = array();

        if (!array_key_exists($jumpto, $jumpnames)) {
            if ($jumpto == LESSON_THISPAGE) {
                $jumptitle = get_string('thispage', 'lesson');
            } elseif ($jumpto == LESSON_NEXTPAGE) {
                $jumptitle = get_string('nextpage', 'lesson');
            } elseif ($jumpto == LESSON_EOL) {
                $jumptitle = get_string('endoflesson', 'lesson');
            } elseif ($jumpto == LESSON_UNSEENBRANCHPAGE) {
                $jumptitle = get_string('unseenpageinbranch', 'lesson');
            } elseif ($jumpto == LESSON_PREVIOUSPAGE) {
                $jumptitle = get_string('previouspage', 'lesson');
            } elseif ($jumpto == LESSON_RANDOMPAGE) {
                $jumptitle = get_string('randompageinbranch', 'lesson');
            } elseif ($jumpto == LESSON_RANDOMBRANCH) {
                $jumptitle = get_string('randombranch', 'lesson');
            } elseif ($jumpto == LESSON_CLUSTERJUMP) {
                $jumptitle = get_string('clusterjump', 'lesson');
            } else {
                if (!$jumptitle = $DB->get_field('lesson_pages', 'title', array('id' => $jumpto))) {
                    $jumptitle = '<strong>'.get_string('notdefined', 'lesson').'</strong>';
                }
            }
            $jumpnames[$jumpto] = format_string($jumptitle,true);
        }

        return $jumpnames[$jumpto];
    }

    
    public function __construct($properties, lesson $lesson) {
        parent::__construct($properties);
        $this->lesson = $lesson;
    }

    
    public function earned_score($answers, $attempt) {
        return $answers[$attempt->answerid]->score;
    }

    
    public function callback_on_view($canmanage) {
        return true;
    }

    
    public function save_answers_files($context, $maxbytes, &$answer, $answereditor = '', $responseeditor = '') {
        global $DB;
        if (isset($answereditor['itemid'])) {
            $answer->answer = file_save_draft_area_files($answereditor['itemid'],
                    $context->id, 'mod_lesson', 'page_answers', $answer->id,
                    array('noclean' => true, 'maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $maxbytes),
                    $answer->answer, null);
            $DB->set_field('lesson_answers', 'answer', $answer->answer, array('id' => $answer->id));
        }
        if (isset($responseeditor['itemid'])) {
            $answer->response = file_save_draft_area_files($responseeditor['itemid'],
                    $context->id, 'mod_lesson', 'page_responses', $answer->id,
                    array('noclean' => true, 'maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $maxbytes),
                    $answer->response, null);
            $DB->set_field('lesson_answers', 'response', $answer->response, array('id' => $answer->id));
        }
    }

    
    public static function rewrite_answers_urls($answer, $rewriteanswer = true) {
        global $PAGE;

        $context = context_module::instance($PAGE->cm->id);
        if ($rewriteanswer) {
            $answer->answer = file_rewrite_pluginfile_urls($answer->answer, 'pluginfile.php', $context->id,
                    'mod_lesson', 'page_answers', $answer->id);
        }
        $answer->response = file_rewrite_pluginfile_urls($answer->response, 'pluginfile.php', $context->id,
                'mod_lesson', 'page_responses', $answer->id);

        return $answer;
    }

    
    public function update($properties, $context = null, $maxbytes = null) {
        global $DB, $PAGE;
        $answers  = $this->get_answers();
        $properties->id = $this->properties->id;
        $properties->lessonid = $this->lesson->id;
        if (empty($properties->qoption)) {
            $properties->qoption = '0';
        }
        if (empty($context)) {
            $context = $PAGE->context;
        }
        if ($maxbytes === null) {
            $maxbytes = get_user_max_upload_file_size($context);
        }
        $properties->timemodified = time();
        $properties = file_postupdate_standard_editor($properties, 'contents', array('noclean'=>true, 'maxfiles'=>EDITOR_UNLIMITED_FILES, 'maxbytes'=>$maxbytes), $context, 'mod_lesson', 'page_contents', $properties->id);
        $DB->update_record("lesson_pages", $properties);

                \mod_lesson\event\page_updated::create_from_lesson_page($this, $context)->trigger();

        if ($this->type == self::TYPE_STRUCTURE && $this->get_typeid() != LESSON_PAGE_BRANCHTABLE) {
                        if (count($answers) > 1) {
                $answer = array_shift($answers);
                foreach ($answers as $a) {
                    $DB->delete_record('lesson_answers', array('id' => $a->id));
                }
            } else if (count($answers) == 1) {
                $answer = array_shift($answers);
            } else {
                $answer = new stdClass;
                $answer->lessonid = $properties->lessonid;
                $answer->pageid = $properties->id;
                $answer->timecreated = time();
            }

            $answer->timemodified = time();
            if (isset($properties->jumpto[0])) {
                $answer->jumpto = $properties->jumpto[0];
            }
            if (isset($properties->score[0])) {
                $answer->score = $properties->score[0];
            }
            if (!empty($answer->id)) {
                $DB->update_record("lesson_answers", $answer->properties());
            } else {
                $DB->insert_record("lesson_answers", $answer);
            }
        } else {
            for ($i = 0; $i < $this->lesson->maxanswers; $i++) {
                if (!array_key_exists($i, $this->answers)) {
                    $this->answers[$i] = new stdClass;
                    $this->answers[$i]->lessonid = $this->lesson->id;
                    $this->answers[$i]->pageid = $this->id;
                    $this->answers[$i]->timecreated = $this->timecreated;
                }

                if (isset($properties->answer_editor[$i])) {
                    if (is_array($properties->answer_editor[$i])) {
                                                $this->answers[$i]->answer = $properties->answer_editor[$i]['text'];
                        $this->answers[$i]->answerformat = $properties->answer_editor[$i]['format'];
                    } else {
                                                $this->answers[$i]->answer = $properties->answer_editor[$i];
                        $this->answers[$i]->answerformat = FORMAT_MOODLE;
                    }
                }

                if (!empty($properties->response_editor[$i]) && is_array($properties->response_editor[$i])) {
                    $this->answers[$i]->response = $properties->response_editor[$i]['text'];
                    $this->answers[$i]->responseformat = $properties->response_editor[$i]['format'];
                }

                if (isset($this->answers[$i]->answer) && $this->answers[$i]->answer != '') {
                    if (isset($properties->jumpto[$i])) {
                        $this->answers[$i]->jumpto = $properties->jumpto[$i];
                    }
                    if ($this->lesson->custom && isset($properties->score[$i])) {
                        $this->answers[$i]->score = $properties->score[$i];
                    }
                    if (!isset($this->answers[$i]->id)) {
                        $this->answers[$i]->id = $DB->insert_record("lesson_answers", $this->answers[$i]);
                    } else {
                        $DB->update_record("lesson_answers", $this->answers[$i]->properties());
                    }

                                        if (isset($properties->response_editor[$i])) {
                        $this->save_answers_files($context, $maxbytes, $this->answers[$i],
                                $properties->answer_editor[$i], $properties->response_editor[$i]);
                    } else {
                        $this->save_answers_files($context, $maxbytes, $this->answers[$i],
                                $properties->answer_editor[$i]);
                    }

                } else if (isset($this->answers[$i]->id)) {
                    $DB->delete_records('lesson_answers', array('id' => $this->answers[$i]->id));
                    unset($this->answers[$i]);
                }
            }
        }
        return true;
    }

    
    public function add_page_link($previd) {
        return false;
    }

    
    public function is_unseen($param) {
        global $USER, $DB;
        if (is_array($param)) {
            $seenpages = $param;
            return (!array_key_exists($this->properties->id, $seenpages));
        } else {
            $nretakes = $param;
            if (!$DB->count_records("lesson_attempts", array("pageid"=>$this->properties->id, "userid"=>$USER->id, "retry"=>$nretakes))) {
                return true;
            }
        }
        return false;
    }

    
    public function is_unanswered($nretakes) {
        global $DB, $USER;
        if (!$DB->count_records("lesson_attempts", array('pageid'=>$this->properties->id, 'userid'=>$USER->id, 'correct'=>1, 'retry'=>$nretakes))) {
            return true;
        }
        return false;
    }

    
    public function create_answers($properties) {
        global $DB, $PAGE;
                $newanswer = new stdClass;
        $newanswer->lessonid = $this->lesson->id;
        $newanswer->pageid = $this->properties->id;
        $newanswer->timecreated = $this->properties->timecreated;

        $cm = get_coursemodule_from_instance('lesson', $this->lesson->id, $this->lesson->course);
        $context = context_module::instance($cm->id);

        $answers = array();

        for ($i = 0; $i < $this->lesson->maxanswers; $i++) {
            $answer = clone($newanswer);

            if (isset($properties->answer_editor[$i])) {
                if (is_array($properties->answer_editor[$i])) {
                                        $answer->answer = $properties->answer_editor[$i]['text'];
                    $answer->answerformat = $properties->answer_editor[$i]['format'];
                } else {
                                        $answer->answer = $properties->answer_editor[$i];
                    $answer->answerformat = FORMAT_MOODLE;
                }
            }
            if (!empty($properties->response_editor[$i]) && is_array($properties->response_editor[$i])) {
                $answer->response = $properties->response_editor[$i]['text'];
                $answer->responseformat = $properties->response_editor[$i]['format'];
            }

            if (isset($answer->answer) && $answer->answer != '') {
                if (isset($properties->jumpto[$i])) {
                    $answer->jumpto = $properties->jumpto[$i];
                }
                if ($this->lesson->custom && isset($properties->score[$i])) {
                    $answer->score = $properties->score[$i];
                }
                $answer->id = $DB->insert_record("lesson_answers", $answer);
                if (isset($properties->response_editor[$i])) {
                    $this->save_answers_files($context, $PAGE->course->maxbytes, $answer,
                            $properties->answer_editor[$i], $properties->response_editor[$i]);
                } else {
                    $this->save_answers_files($context, $PAGE->course->maxbytes, $answer,
                            $properties->answer_editor[$i]);
                }
                $answers[$answer->id] = new lesson_page_answer($answer);
            } else {
                break;
            }
        }

        $this->answers = $answers;
        return $answers;
    }

    
    public function check_answer() {
        $result = new stdClass;
        $result->answerid        = 0;
        $result->noanswer        = false;
        $result->correctanswer   = false;
        $result->isessayquestion = false;           $result->response        = '';
        $result->newpageid       = 0;               $result->studentanswer   = '';              $result->userresponse    = null;
        $result->feedback        = '';
        $result->nodefaultresponse  = false;         return $result;
    }

    
    public function has_option() {
        return false;
    }

    
    public function max_answers($default) {
        return $default;
    }

    
    public function properties() {
        $properties = clone($this->properties);
        if ($this->answers === null) {
            $this->get_answers();
        }
        if (count($this->answers)>0) {
            $count = 0;
            $qtype = $properties->qtype;
            foreach ($this->answers as $answer) {
                $properties->{'answer_editor['.$count.']'} = array('text' => $answer->answer, 'format' => $answer->answerformat);
                if ($qtype != LESSON_PAGE_MATCHING) {
                    $properties->{'response_editor['.$count.']'} = array('text' => $answer->response, 'format' => $answer->responseformat);
                } else {
                    $properties->{'response_editor['.$count.']'} = $answer->response;
                }
                $properties->{'jumpto['.$count.']'} = $answer->jumpto;
                $properties->{'score['.$count.']'} = $answer->score;
                $count++;
            }
        }
        return $properties;
    }

    
    public static function get_jumptooptions($pageid, lesson $lesson) {
        global $DB;
        $jump = array();
        $jump[0] = get_string("thispage", "lesson");
        $jump[LESSON_NEXTPAGE] = get_string("nextpage", "lesson");
        $jump[LESSON_PREVIOUSPAGE] = get_string("previouspage", "lesson");
        $jump[LESSON_EOL] = get_string("endoflesson", "lesson");

        if ($pageid == 0) {
            return $jump;
        }

        $pages = $lesson->load_all_pages();
        if ($pages[$pageid]->qtype == LESSON_PAGE_BRANCHTABLE || $lesson->is_sub_page_of_type($pageid, array(LESSON_PAGE_BRANCHTABLE), array(LESSON_PAGE_ENDOFBRANCH, LESSON_PAGE_CLUSTER))) {
            $jump[LESSON_UNSEENBRANCHPAGE] = get_string("unseenpageinbranch", "lesson");
            $jump[LESSON_RANDOMPAGE] = get_string("randompageinbranch", "lesson");
        }
        if($pages[$pageid]->qtype == LESSON_PAGE_CLUSTER || $lesson->is_sub_page_of_type($pageid, array(LESSON_PAGE_CLUSTER), array(LESSON_PAGE_ENDOFCLUSTER))) {
            $jump[LESSON_CLUSTERJUMP] = get_string("clusterjump", "lesson");
        }
        if (!optional_param('firstpage', 0, PARAM_INT)) {
            $apageid = $DB->get_field("lesson_pages", "id", array("lessonid" => $lesson->id, "prevpageid" => 0));
            while (true) {
                if ($apageid) {
                    $title = $DB->get_field("lesson_pages", "title", array("id" => $apageid));
                    $jump[$apageid] = strip_tags(format_string($title,true));
                    $apageid = $DB->get_field("lesson_pages", "nextpageid", array("id" => $apageid));
                } else {
                                        break;
                }
            }
        }
        return $jump;
    }
    
    public function get_contents() {
        global $PAGE;
        if (!empty($this->properties->contents)) {
            if (!isset($this->properties->contentsformat)) {
                $this->properties->contentsformat = FORMAT_HTML;
            }
            $context = context_module::instance($PAGE->cm->id);
            $contents = file_rewrite_pluginfile_urls($this->properties->contents, 'pluginfile.php', $context->id, 'mod_lesson',
                                                     'page_contents', $this->properties->id);              return format_text($contents, $this->properties->contentsformat,
                               array('context' => $context, 'noclean' => true,
                                     'overflowdiv' => true));          } else {
            return '';
        }
    }

    
    protected function get_displayinmenublock() {
        return false;
    }

    
    public function option_description_string() {
        return '';
    }

    
    public function display_answers(html_table $table) {
        $answers = $this->get_answers();
        $i = 1;
        foreach ($answers as $answer) {
            $cells = array();
            $cells[] = "<span class=\"label\">".get_string("jump", "lesson")." $i<span>: ";
            $cells[] = $this->get_jump_name($answer->jumpto);
            $table->data[] = new html_table_row($cells);
            if ($i === 1){
                $table->data[count($table->data)-1]->cells[0]->style = 'width:20%;';
            }
            $i++;
        }
        return $table;
    }

    
    protected function get_grayout() {
        return 0;
    }

    
    public function stats(array &$pagestats, $tries) {
        return true;
    }

    
    public function report_answers($answerpage, $answerdata, $useranswer, $pagestats, &$i, &$n) {
        $answers = $this->get_answers();
        $formattextdefoptions = new stdClass;
        $formattextdefoptions->para = false;          foreach ($answers as $answer) {
            $data = get_string('jumpsto', 'lesson', $this->get_jump_name($answer->jumpto));
            $answerdata->answers[] = array($data, "");
            $answerpage->answerdata = $answerdata;
        }
        return $answerpage;
    }

    
    public function get_jumps() {
        global $DB;
        $jumps = array();
        $params = array ("lessonid" => $this->lesson->id, "pageid" => $this->properties->id);
        if ($answers = $this->get_answers()) {
            foreach ($answers as $answer) {
                $jumps[] = $this->get_jump_name($answer->jumpto);
            }
        } else {
            $jumps[] = $this->get_jump_name($this->properties->nextpageid);
        }
        return $jumps;
    }
    
    public function requires_manual_grading() {
        return false;
    }

    
    public function override_next_page() {
        return false;
    }

    
    public function valid_page_and_view(&$validpages, &$pageviews) {
        $validpages[$this->properties->id] = 1;
        return $this->properties->nextpageid;
    }
}




class lesson_page_answer extends lesson_base {

    
    public static function load($id) {
        global $DB;
        $answer = $DB->get_record("lesson_answers", array("id" => $id));
        return new lesson_page_answer($answer);
    }

    
    public static function create($properties, lesson_page $page) {
        return $page->create_answers($properties);
    }

}


class lesson_page_type_manager {

    
    protected $types = array();

    
    public static function get(lesson $lesson) {
        static $pagetypemanager;
        if (!($pagetypemanager instanceof lesson_page_type_manager)) {
            $pagetypemanager = new lesson_page_type_manager();
            $pagetypemanager->load_lesson_types($lesson);
        }
        return $pagetypemanager;
    }

    
    public function load_lesson_types(lesson $lesson) {
        global $CFG;
        $basedir = $CFG->dirroot.'/mod/lesson/pagetypes/';
        $dir = dir($basedir);
        while (false !== ($entry = $dir->read())) {
            if (strpos($entry, '.')===0 || !preg_match('#^[a-zA-Z]+\.php#i', $entry)) {
                continue;
            }
            require_once($basedir.$entry);
            $class = 'lesson_page_type_'.strtok($entry,'.');
            if (class_exists($class)) {
                $pagetype = new $class(new stdClass, $lesson);
                $this->types[$pagetype->typeid] = $pagetype;
            }
        }

    }

    
    public function get_page_type_strings($type=null, $special=true) {
        $types = array();
        foreach ($this->types as $pagetype) {
            if (($type===null || $pagetype->type===$type) && ($special===true || $pagetype->is_standard())) {
                $types[$pagetype->typeid] = $pagetype->typestring;
            }
        }
        return $types;
    }

    
    public function get_page_type_idstring($id) {
        foreach ($this->types as $pagetype) {
            if ((int)$pagetype->typeid === (int)$id) {
                return $pagetype->idstring;
            }
        }
        return 'unknown';
    }

    
    public function load_page($pageid, lesson $lesson) {
        global $DB;
        if (!($page =$DB->get_record('lesson_pages', array('id'=>$pageid, 'lessonid'=>$lesson->id)))) {
            print_error('cannotfindpages', 'lesson');
        }
        $pagetype = get_class($this->types[$page->qtype]);
        $page = new $pagetype($page, $lesson);
        return $page;
    }

    
    protected function check_page_order($page1, $page2) {
        global $DB;
        if (empty($page1)) {
            if ($page2->prevpageid != 0) {
                debugging("***prevpageid of page " . $page2->id . " set to 0***");
                $page2->prevpageid = 0;
                $DB->set_field("lesson_pages", "prevpageid", 0, array("id" => $page2->id));
            }
        } else if (empty($page2)) {
            if ($page1->nextpageid != 0) {
                debugging("***nextpageid of page " . $page1->id . " set to 0***");
                $page1->nextpageid = 0;
                $DB->set_field("lesson_pages", "nextpageid", 0, array("id" => $page1->id));
            }
        } else {
            if ($page1->nextpageid != $page2->id) {
                debugging("***nextpageid of page " . $page1->id . " set to " . $page2->id . "***");
                $page1->nextpageid = $page2->id;
                $DB->set_field("lesson_pages", "nextpageid", $page2->id, array("id" => $page1->id));
            }
            if ($page2->prevpageid != $page1->id) {
                debugging("***prevpageid of page " . $page2->id . " set to " . $page1->id . "***");
                $page2->prevpageid = $page1->id;
                $DB->set_field("lesson_pages", "prevpageid", $page1->id, array("id" => $page2->id));
            }
        }
    }

    
    public function load_all_pages(lesson $lesson) {
        global $DB;
        if (!($pages =$DB->get_records('lesson_pages', array('lessonid'=>$lesson->id)))) {
            return array();         }
        foreach ($pages as $key=>$page) {
            $pagetype = get_class($this->types[$page->qtype]);
            $pages[$key] = new $pagetype($page, $lesson);
        }

        $orderedpages = array();
        $lastpageid = 0;
        $morepages = true;
        while ($morepages) {
            $morepages = false;
            foreach ($pages as $page) {
                if ((int)$page->prevpageid === (int)$lastpageid) {
                                        $prevpage = null;
                    if ($lastpageid !== 0) {
                        $prevpage = $orderedpages[$lastpageid];
                    }
                    $this->check_page_order($prevpage, $page);
                    $morepages = true;
                    $orderedpages[$page->id] = $page;
                    unset($pages[$page->id]);
                    $lastpageid = $page->id;
                    if ((int)$page->nextpageid===0) {
                        break 2;
                    } else {
                        break 1;
                    }
                }
            }
        }

                foreach ($pages as $page) {
                        $prevpage = null;
            if ($lastpageid !== 0) {
                $prevpage = $orderedpages[$lastpageid];
            }
            $this->check_page_order($prevpage, $page);
            $orderedpages[$page->id] = $page;
            unset($pages[$page->id]);
            $lastpageid = $page->id;
        }

        if ($lastpageid !== 0) {
            $this->check_page_order($orderedpages[$lastpageid], null);
        }

        return $orderedpages;
    }

    
    public function get_page_form($type, $arguments) {
        $class = 'lesson_add_page_form_'.$this->get_page_type_idstring($type);
        if (!class_exists($class) || get_parent_class($class)!=='lesson_add_page_form_base') {
            debugging('Lesson page type unknown class requested '.$class, DEBUG_DEVELOPER);
            $class = 'lesson_add_page_form_selection';
        } else if ($class === 'lesson_add_page_form_unknown') {
            $class = 'lesson_add_page_form_selection';
        }
        return new $class(null, $arguments);
    }

    
    public function get_add_page_type_links($previd) {
        global $OUTPUT;

        $links = array();

        foreach ($this->types as $key=>$type) {
            if ($link = $type->add_page_link($previd)) {
                $links[$key] = $link;
            }
        }

        return $links;
    }
}
