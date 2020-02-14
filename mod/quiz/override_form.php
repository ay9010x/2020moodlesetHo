<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/mod/quiz/mod_form.php');



class quiz_override_form extends moodleform {

    
    protected $cm;

    
    protected $quiz;

    
    protected $context;

    
    protected $groupmode;

    
    protected $groupid;

    
    protected $userid;

    
    public function __construct($submiturl, $cm, $quiz, $context, $groupmode, $override) {

        $this->cm = $cm;
        $this->quiz = $quiz;
        $this->context = $context;
        $this->groupmode = $groupmode;
        $this->groupid = empty($override->groupid) ? 0 : $override->groupid;
        $this->userid = empty($override->userid) ? 0 : $override->userid;

        parent::__construct($submiturl, null, 'post');

    }

    protected function definition() {
        global $CFG, $DB;

        $cm = $this->cm;
        $mform = $this->_form;

        $mform->addElement('header', 'override', get_string('override', 'quiz'));

        if ($this->groupmode) {
                        if ($this->groupid) {
                                $groupchoices = array();
                $groupchoices[$this->groupid] = groups_get_group_name($this->groupid);
                $mform->addElement('select', 'groupid',
                        get_string('overridegroup', 'quiz'), $groupchoices);
                $mform->freeze('groupid');
            } else {
                                $groups = groups_get_all_groups($cm->course);
                if (empty($groups)) {
                                        $link = new moodle_url('/mod/quiz/overrides.php', array('cmid'=>$cm->id));
                    print_error('groupsnone', 'quiz', $link);
                }

                $groupchoices = array();
                foreach ($groups as $group) {
                    $groupchoices[$group->id] = $group->name;
                }
                unset($groups);

                if (count($groupchoices) == 0) {
                    $groupchoices[0] = get_string('none');
                }

                $mform->addElement('select', 'groupid',
                        get_string('overridegroup', 'quiz'), $groupchoices);
                $mform->addRule('groupid', get_string('required'), 'required', null, 'client');
            }
        } else {
                        if ($this->userid) {
                                $user = $DB->get_record('user', array('id'=>$this->userid));
                $userchoices = array();
                $userchoices[$this->userid] = fullname($user);
                $mform->addElement('select', 'userid',
                        get_string('overrideuser', 'quiz'), $userchoices);
                $mform->freeze('userid');
            } else {
                                $users = array();
                list($sort, $sortparams) = users_order_by_sql('u');
                if (!empty($sortparams)) {
                    throw new coding_exception('users_order_by_sql returned some query parameters. ' .
                            'This is unexpected, and a problem because there is no way to pass these ' .
                            'parameters to get_users_by_capability. See MDL-34657.');
                }
                $users = get_users_by_capability($this->context, 'mod/quiz:attempt',
                        'u.id, u.email, ' . get_all_user_name_fields(true, 'u'),
                        $sort, '', '', '', '', false, true);

                                $info = new \core_availability\info_module($cm);
                $users = $info->filter_user_list($users);

                if (empty($users)) {
                                        $link = new moodle_url('/mod/quiz/overrides.php', array('cmid'=>$cm->id));
                    print_error('usersnone', 'quiz', $link);
                }

                $userchoices = array();
                $canviewemail = in_array('email', get_extra_user_fields($this->context));
                foreach ($users as $id => $user) {
                    if (empty($invalidusers[$id]) || (!empty($override) &&
                            $id == $override->userid)) {
                        if ($canviewemail) {
                            $userchoices[$id] = fullname($user) . ', ' . $user->email;
                        } else {
                            $userchoices[$id] = fullname($user);
                        }
                    }
                }
                unset($users);

                if (count($userchoices) == 0) {
                    $userchoices[0] = get_string('none');
                }
                $mform->addElement('searchableselector', 'userid',
                        get_string('overrideuser', 'quiz'), $userchoices);
                $mform->addRule('userid', get_string('required'), 'required', null, 'client');
            }
        }

                                $mform->addElement('passwordunmask', 'password', get_string('requirepassword', 'quiz'));
        $mform->setType('password', PARAM_TEXT);
        $mform->addHelpButton('password', 'requirepassword', 'quiz');
        $mform->setDefault('password', $this->quiz->password);

                $mform->addElement('date_time_selector', 'timeopen',
                get_string('quizopen', 'quiz'), mod_quiz_mod_form::$datefieldoptions);
        $mform->setDefault('timeopen', $this->quiz->timeopen);

        $mform->addElement('date_time_selector', 'timeclose',
                get_string('quizclose', 'quiz'), mod_quiz_mod_form::$datefieldoptions);
        $mform->setDefault('timeclose', $this->quiz->timeclose);

                $mform->addElement('duration', 'timelimit',
                get_string('timelimit', 'quiz'), array('optional' => true));
        $mform->addHelpButton('timelimit', 'timelimit', 'quiz');
        $mform->setDefault('timelimit', $this->quiz->timelimit);

                $attemptoptions = array('0' => get_string('unlimited'));
        for ($i = 1; $i <= QUIZ_MAX_ATTEMPT_OPTION; $i++) {
            $attemptoptions[$i] = $i;
        }
        $mform->addElement('select', 'attempts',
                get_string('attemptsallowed', 'quiz'), $attemptoptions);
        $mform->setDefault('attempts', $this->quiz->attempts);

                $mform->addElement('submit', 'resetbutton',
                get_string('reverttodefaults', 'quiz'));

        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton',
                get_string('save', 'quiz'));
        $buttonarray[] = $mform->createElement('submit', 'againbutton',
                get_string('saveoverrideandstay', 'quiz'));
        $buttonarray[] = $mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonbar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonbar');

    }

    public function validation($data, $files) {
        global $COURSE, $DB;
        $errors = parent::validation($data, $files);

        $mform =& $this->_form;
        $quiz = $this->quiz;

        if ($mform->elementExists('userid')) {
            if (empty($data['userid'])) {
                $errors['userid'] = get_string('required');
            }
        }

        if ($mform->elementExists('groupid')) {
            if (empty($data['groupid'])) {
                $errors['groupid'] = get_string('required');
            }
        }

                if (!empty($data['timeopen']) && !empty($data['timeclose'])) {
            if ($data['timeclose'] < $data['timeopen'] ) {
                $errors['timeclose'] = get_string('closebeforeopen', 'quiz');
            }
        }

                $changed = false;
        $keys = array('timeopen', 'timeclose', 'timelimit', 'attempts', 'password');
        foreach ($keys as $key) {
            if ($data[$key] != $quiz->{$key}) {
                $changed = true;
                break;
            }
        }
        if (!$changed) {
            $errors['timeopen'] = get_string('nooverridedata', 'quiz');
        }

        return $errors;
    }
}
