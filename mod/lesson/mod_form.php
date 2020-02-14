<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/lesson/locallib.php');

class mod_lesson_mod_form extends moodleform_mod {

    protected $course = null;

    public function __construct($current, $section, $cm, $course) {
        $this->course = $course;
        parent::__construct($current, $section, $cm, $course);
    }

    
    public function mod_lesson_mod_form($current, $section, $cm, $course) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($current, $section, $cm, $course);
    }

    function definition() {
        global $CFG, $COURSE, $DB;

        $mform    = $this->_form;

        $lessonconfig = get_config('mod_lesson');

        $mform->addElement('header', 'general', get_string('general', 'form'));

        
        $mform->addElement('hidden', 'width');
        $mform->setType('width', PARAM_INT);
        $mform->setDefault('width', $lessonconfig->slideshowwidth);

        
        $mform->addElement('hidden', 'height');
        $mform->setType('height', PARAM_INT);
        $mform->setDefault('height', $lessonconfig->slideshowheight);

        
        $mform->addElement('hidden', 'bgcolor');
        $mform->setType('bgcolor', PARAM_TEXT);
        $mform->setDefault('bgcolor', $lessonconfig->slideshowbgcolor);

        
        $mform->addElement('hidden', 'mediawidth');
        $mform->setType('mediawidth', PARAM_INT);
        $mform->setDefault('mediawidth', $lessonconfig->mediawidth);

        
        $mform->addElement('hidden', 'mediaheight');
        $mform->setType('mediaheight', PARAM_INT);
        $mform->setDefault('mediaheight', $lessonconfig->mediaheight);

        
        $mform->addElement('hidden', 'mediaclose');
        $mform->setType('mediaclose', PARAM_BOOL);
        $mform->setDefault('mediaclose', $lessonconfig->mediaclose);

        $mform->addElement('text', 'name', get_string('name'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $this->standard_intro_elements();

                $mform->addElement('header', 'appearancehdr', get_string('appearance'));

        $filemanageroptions = array();
        $filemanageroptions['filetypes'] = '*';
        $filemanageroptions['maxbytes'] = $this->course->maxbytes;
        $filemanageroptions['subdirs'] = 0;
        $filemanageroptions['maxfiles'] = 1;

        $mform->addElement('filemanager', 'mediafile', get_string('mediafile', 'lesson'), null, $filemanageroptions);
        $mform->addHelpButton('mediafile', 'mediafile', 'lesson');
        $mform->setAdvanced('mediafile', $lessonconfig->mediafile_adv);

        $mform->addElement('selectyesno', 'progressbar', get_string('progressbar', 'lesson'));
        $mform->addHelpButton('progressbar', 'progressbar', 'lesson');
        $mform->setDefault('progressbar', $lessonconfig->progressbar);
        $mform->setAdvanced('progressbar', $lessonconfig->progressbar_adv);

        $mform->addElement('selectyesno', 'ongoing', get_string('ongoing', 'lesson'));
        $mform->addHelpButton('ongoing', 'ongoing', 'lesson');
        $mform->setDefault('ongoing', $lessonconfig->ongoing);
        $mform->setAdvanced('ongoing', $lessonconfig->ongoing_adv);

        $mform->addElement('selectyesno', 'displayleft', get_string('displayleftmenu', 'lesson'));
        $mform->addHelpButton('displayleft', 'displayleftmenu', 'lesson');
        $mform->setDefault('displayleft', $lessonconfig->displayleftmenu);
        $mform->setAdvanced('displayleft', $lessonconfig->displayleftmenu_adv);

        $options = array();
        for($i = 100; $i >= 0; $i--) {
            $options[$i] = $i.'%';
        }
        $mform->addElement('select', 'displayleftif', get_string('displayleftif', 'lesson'), $options);
        $mform->addHelpButton('displayleftif', 'displayleftif', 'lesson');
        $mform->setDefault('displayleftif', $lessonconfig->displayleftif);
        $mform->setAdvanced('displayleftif', $lessonconfig->displayleftif_adv);

        $mform->addElement('selectyesno', 'slideshow', get_string('slideshow', 'lesson'));
        $mform->addHelpButton('slideshow', 'slideshow', 'lesson');
        $mform->setDefault('slideshow', $lessonconfig->slideshow);
        $mform->setAdvanced('slideshow', $lessonconfig->slideshow_adv);

        $numbers = array();
        for ($i = 20; $i > 1; $i--) {
            $numbers[$i] = $i;
        }

        $mform->addElement('select', 'maxanswers', get_string('maximumnumberofanswersbranches', 'lesson'), $numbers);
        $mform->setDefault('maxanswers', $lessonconfig->maxanswers);
        $mform->setAdvanced('maxanswers', $lessonconfig->maxanswers_adv);
        $mform->setType('maxanswers', PARAM_INT);
        $mform->addHelpButton('maxanswers', 'maximumnumberofanswersbranches', 'lesson');

        $mform->addElement('selectyesno', 'feedback', get_string('displaydefaultfeedback', 'lesson'));
        $mform->addHelpButton('feedback', 'displaydefaultfeedback', 'lesson');
        $mform->setDefault('feedback', $lessonconfig->defaultfeedback);
        $mform->setAdvanced('feedback', $lessonconfig->defaultfeedback_adv);

                if ($mods = get_course_mods($COURSE->id)) {
            $modinstances = array();
            foreach ($mods as $mod) {
                                if ($module = get_coursemodule_from_instance($mod->modname, $mod->instance, $COURSE->id)) {
                                        if (!isset($this->_cm->id) || $this->_cm->id != $mod->id) {
                        $modinstances[$mod->id] = $mod->modname.' - '.$module->name;
                    }
                }
            }
            asort($modinstances);             $modinstances=array(0=>get_string('none'))+$modinstances;

            $mform->addElement('select', 'activitylink', get_string('activitylink', 'lesson'), $modinstances);
            $mform->addHelpButton('activitylink', 'activitylink', 'lesson');
            $mform->setDefault('activitylink', 0);
            $mform->setAdvanced('activitylink', $lessonconfig->activitylink_adv);
        }

                $mform->addElement('header', 'availabilityhdr', get_string('availability'));

        $mform->addElement('date_time_selector', 'available', get_string('available', 'lesson'), array('optional'=>true));
        $mform->setDefault('available', 0);

        $mform->addElement('date_time_selector', 'deadline', get_string('deadline', 'lesson'), array('optional'=>true));
        $mform->setDefault('deadline', 0);

                $mform->addElement('duration', 'timelimit', get_string('timelimit', 'lesson'),
                array('optional' => true));
        $mform->addHelpButton('timelimit', 'timelimit', 'lesson');
        $mform->setAdvanced('timelimit', $lessonconfig->timelimit_adv);
        $mform->setDefault('timelimit', $lessonconfig->timelimit);

        $mform->addElement('selectyesno', 'usepassword', get_string('usepassword', 'lesson'));
        $mform->addHelpButton('usepassword', 'usepassword', 'lesson');
        $mform->setDefault('usepassword', $lessonconfig->password);
        $mform->setAdvanced('usepassword', $lessonconfig->password_adv);

        $mform->addElement('passwordunmask', 'password', get_string('password', 'lesson'));
        $mform->setDefault('password', '');
        $mform->setAdvanced('password', $lessonconfig->password_adv);
        $mform->setType('password', PARAM_RAW);
        $mform->disabledIf('password', 'usepassword', 'eq', 0);
        $mform->disabledIf('passwordunmask', 'usepassword', 'eq', 0);

                if ($this->current && isset($this->current->dependency) && $this->current->dependency) {
            $mform->addElement('header', 'dependencyon', get_string('prerequisitelesson', 'lesson'));
            $mform->addElement('static', 'warningobsolete',
                get_string('warning', 'lesson'),
                get_string('prerequisiteisobsolete', 'lesson'));
            $options = array(0 => get_string('none'));
            if ($lessons = get_all_instances_in_course('lesson', $COURSE)) {
                foreach ($lessons as $lesson) {
                    if ($lesson->id != $this->_instance) {
                        $options[$lesson->id] = format_string($lesson->name, true);
                    }

                }
            }
            $mform->addElement('select', 'dependency', get_string('dependencyon', 'lesson'), $options);
            $mform->addHelpButton('dependency', 'dependencyon', 'lesson');
            $mform->setDefault('dependency', 0);

            $mform->addElement('text', 'timespent', get_string('timespentminutes', 'lesson'));
            $mform->setDefault('timespent', 0);
            $mform->setType('timespent', PARAM_INT);
            $mform->disabledIf('timespent', 'dependency', 'eq', 0);

            $mform->addElement('checkbox', 'completed', get_string('completed', 'lesson'));
            $mform->setDefault('completed', 0);
            $mform->disabledIf('completed', 'dependency', 'eq', 0);

            $mform->addElement('text', 'gradebetterthan', get_string('gradebetterthan', 'lesson'));
            $mform->setDefault('gradebetterthan', 0);
            $mform->setType('gradebetterthan', PARAM_INT);
            $mform->disabledIf('gradebetterthan', 'dependency', 'eq', 0);
        } else {
            $mform->addElement('hidden', 'dependency', 0);
            $mform->setType('dependency', PARAM_INT);
            $mform->addElement('hidden', 'timespent', 0);
            $mform->setType('timespent', PARAM_INT);
            $mform->addElement('hidden', 'completed', 0);
            $mform->setType('completed', PARAM_INT);
            $mform->addElement('hidden', 'gradebetterthan', 0);
            $mform->setType('gradebetterthan', PARAM_INT);
            $mform->setConstants(array('dependency' => 0, 'timespent' => 0,
                    'completed' => 0, 'gradebetterthan' => 0));
        }

                $mform->addElement('header', 'flowcontrol', get_string('flowcontrol', 'lesson'));

        $mform->addElement('selectyesno', 'modattempts', get_string('modattempts', 'lesson'));
        $mform->addHelpButton('modattempts', 'modattempts', 'lesson');
        $mform->setDefault('modattempts', $lessonconfig->modattempts);
        $mform->setAdvanced('modattempts', $lessonconfig->modattempts_adv);

        $mform->addElement('selectyesno', 'review', get_string('displayreview', 'lesson'));
        $mform->addHelpButton('review', 'displayreview', 'lesson');
        $mform->setDefault('review', $lessonconfig->displayreview);
        $mform->setAdvanced('review', $lessonconfig->displayreview_adv);

        $numbers = array();
        for ($i = 10; $i > 0; $i--) {
            $numbers[$i] = $i;
        }
        $mform->addElement('select', 'maxattempts', get_string('maximumnumberofattempts', 'lesson'), $numbers);
        $mform->addHelpButton('maxattempts', 'maximumnumberofattempts', 'lesson');
        $mform->setDefault('maxattempts', $lessonconfig->maximumnumberofattempts);
        $mform->setAdvanced('maxattempts', $lessonconfig->maximumnumberofattempts_adv);

        $defaultnextpages = array();
        $defaultnextpages[0] = get_string('normal', 'lesson');
        $defaultnextpages[LESSON_UNSEENPAGE] = get_string('showanunseenpage', 'lesson');
        $defaultnextpages[LESSON_UNANSWEREDPAGE] = get_string('showanunansweredpage', 'lesson');
        $mform->addElement('select', 'nextpagedefault', get_string('actionaftercorrectanswer', 'lesson'), $defaultnextpages);
        $mform->addHelpButton('nextpagedefault', 'actionaftercorrectanswer', 'lesson');
        $mform->setDefault('nextpagedefault', $lessonconfig->defaultnextpage);
        $mform->setAdvanced('nextpagedefault', $lessonconfig->defaultnextpage_adv);

        $numbers = array();
        for ($i = 100; $i >= 0; $i--) {
            $numbers[$i] = $i;
        }
        $mform->addElement('select', 'maxpages', get_string('numberofpagestoshow', 'lesson'), $numbers);
        $mform->addHelpButton('maxpages', 'numberofpagestoshow', 'lesson');
        $mform->setDefault('maxpages', $lessonconfig->numberofpagestoshow);
        $mform->setAdvanced('maxpages', $lessonconfig->numberofpagestoshow_adv);

                $this->standard_grading_coursemodule_elements();

        
        $mform->addElement('selectyesno', 'practice', get_string('practice', 'lesson'));
        $mform->addHelpButton('practice', 'practice', 'lesson');
        $mform->setDefault('practice', $lessonconfig->practice);
        $mform->setAdvanced('practice', $lessonconfig->practice_adv);

        $mform->addElement('selectyesno', 'custom', get_string('customscoring', 'lesson'));
        $mform->addHelpButton('custom', 'customscoring', 'lesson');
        $mform->setDefault('custom', $lessonconfig->customscoring);
        $mform->setAdvanced('custom', $lessonconfig->customscoring_adv);

        $mform->addElement('selectyesno', 'retake', get_string('retakesallowed', 'lesson'));
        $mform->addHelpButton('retake', 'retakesallowed', 'lesson');
        $mform->setDefault('retake', $lessonconfig->retakesallowed);
        $mform->setAdvanced('retake', $lessonconfig->retakesallowed_adv);

        $options = array();
        $options[0] = get_string('usemean', 'lesson');
        $options[1] = get_string('usemaximum', 'lesson');
        $mform->addElement('select', 'usemaxgrade', get_string('handlingofretakes', 'lesson'), $options);
        $mform->addHelpButton('usemaxgrade', 'handlingofretakes', 'lesson');
        $mform->setDefault('usemaxgrade', $lessonconfig->handlingofretakes);
        $mform->setAdvanced('usemaxgrade', $lessonconfig->handlingofretakes_adv);
        $mform->disabledIf('usemaxgrade', 'retake', 'eq', '0');

        $numbers = array();
        for ($i = 100; $i >= 0; $i--) {
            $numbers[$i] = $i;
        }
        $mform->addElement('select', 'minquestions', get_string('minimumnumberofquestions', 'lesson'), $numbers);
        $mform->addHelpButton('minquestions', 'minimumnumberofquestions', 'lesson');
        $mform->setDefault('minquestions', $lessonconfig->minimumnumberofquestions);
        $mform->setAdvanced('minquestions', $lessonconfig->minimumnumberofquestions_adv);

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    
    public function data_preprocessing(&$defaultvalues) {
        if (isset($defaultvalues['conditions'])) {
            $conditions = unserialize($defaultvalues['conditions']);
            $defaultvalues['timespent'] = $conditions->timespent;
            $defaultvalues['completed'] = $conditions->completed;
            $defaultvalues['gradebetterthan'] = $conditions->gradebetterthan;
        }

                $defaultvalues['completiontimespentenabled'] =
            !empty($defaultvalues['completiontimespent']) ? 1 : 0;

        if ($this->current->instance) {
                        $draftitemid = file_get_submitted_draft_itemid('mediafile');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_lesson', 'mediafile', 0, array('subdirs'=>0, 'maxbytes' => $this->course->maxbytes, 'maxfiles' => 1));
            $defaultvalues['mediafile'] = $draftitemid;
        }
    }

    
    function validation($data, $files) {
        $errors = parent::validation($data, $files);

                if ($data['available'] != 0 && $data['deadline'] != 0 &&
                $data['deadline'] < $data['available']) {
            $errors['deadline'] = get_string('closebeforeopen', 'lesson');
        }

        if (!empty($data['usepassword']) && empty($data['password'])) {
            $errors['password'] = get_string('emptypassword', 'lesson');
        }

        return $errors;
    }

    
    public function add_completion_rules() {
        $mform = $this->_form;

        $mform->addElement('checkbox', 'completionendreached', get_string('completionendreached', 'lesson'),
                get_string('completionendreached_desc', 'lesson'));

        $group = array();
        $group[] =& $mform->createElement('checkbox', 'completiontimespentenabled', '',
                get_string('completiontimespent', 'lesson'));
        $group[] =& $mform->createElement('duration', 'completiontimespent', array('optional' => true));
        $mform->addGroup($group, 'completiontimespentgroup', get_string('completiontimespentgroup', 'lesson'), array(' '), false);
        $mform->disabledIf('completiontimespent[number]', 'completiontimespentenabled', 'notchecked');
        $mform->disabledIf('completiontimespent[timeunit]', 'completiontimespentenabled', 'notchecked');

        return array('completionendreached', 'completiontimespentgroup');
    }

    
    public function completion_rule_enabled($data) {
        return !empty($data['completionendreached']) || $data['completiontimespent'] > 0;
    }

    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }
                if (!empty($data->completionunlocked)) {
            $autocompletion = !empty($data->completion) && $data->completion == COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->completiontimespentenabled) || !$autocompletion) {
                $data->completiontimespent = 0;
            }
        }
        if (!empty($data->completionunlocked)) {
            $autocompletion = !empty($data->completion) && $data->completion == COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->completionendreached) || !$autocompletion) {
                $data->completionendreached = 0;
            }
        }
        return $data;
    }
}
