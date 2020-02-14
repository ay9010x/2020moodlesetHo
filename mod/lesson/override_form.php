<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/mod/lesson/mod_form.php');



class lesson_override_form extends moodleform {

    
    protected $cm;

    
    protected $lesson;

    
    protected $context;

    
    protected $groupmode;

    
    protected $groupid;

    
    protected $userid;

    
    public function __construct($submiturl, $cm, $lesson, $context, $groupmode, $override) {

        $this->cm = $cm;
        $this->lesson = $lesson;
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

        $mform->addElement('header', 'override', get_string('override', 'lesson'));

        if ($this->groupmode) {
                        if ($this->groupid) {
                                $groupchoices = array();
                $groupchoices[$this->groupid] = groups_get_group_name($this->groupid);
                $mform->addElement('select', 'groupid',
                        get_string('overridegroup', 'lesson'), $groupchoices);
                $mform->freeze('groupid');
            } else {
                                $groups = groups_get_all_groups($cm->course);
                if (empty($groups)) {
                                        $link = new moodle_url('/mod/lesson/overrides.php', array('cmid' => $cm->id));
                    print_error('groupsnone', 'lesson', $link);
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
                        get_string('overridegroup', 'lesson'), $groupchoices);
                $mform->addRule('groupid', get_string('required'), 'required', null, 'client');
            }
        } else {
                        if ($this->userid) {
                                $user = $DB->get_record('user', array('id' => $this->userid));
                $userchoices = array();
                $userchoices[$this->userid] = fullname($user);
                $mform->addElement('select', 'userid',
                        get_string('overrideuser', 'lesson'), $userchoices);
                $mform->freeze('userid');
            } else {
                                $users = get_enrolled_users($this->context, '', 0,
                        'u.id, u.email, ' . get_all_user_name_fields(true, 'u'));

                                $info = new \core_availability\info_module($cm);
                $users = $info->filter_user_list($users);

                if (empty($users)) {
                                        $link = new moodle_url('/mod/lesson/overrides.php', array('cmid' => $cm->id));
                    print_error('usersnone', 'lesson', $link);
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
                        get_string('overrideuser', 'lesson'), $userchoices);
                $mform->addRule('userid', get_string('required'), 'required', null, 'client');
            }
        }

                                $mform->addElement('passwordunmask', 'password', get_string('usepassword', 'lesson'));
        $mform->setType('password', PARAM_TEXT);
        $mform->addHelpButton('password', 'usepassword', 'lesson');
        $mform->setDefault('password', $this->lesson->password);;

                $mform->addElement('date_time_selector', 'available', get_string('available', 'lesson'), array('optional' => true));
        $mform->setDefault('available', $this->lesson->available);

        $mform->addElement('date_time_selector', 'deadline', get_string('deadline', 'lesson'), array('optional' => true));
        $mform->setDefault('deadline', $this->lesson->deadline);

                $mform->addElement('duration', 'timelimit',
                get_string('timelimit', 'lesson'), array('optional' => true));
        if ($this->lesson->timelimit != 0) {
            $mform->setDefault('timelimit', 0);
        } else {
            $mform->setDefault('timelimit', $this->lesson->timelimit);
        }

                $mform->addElement('selectyesno', 'review', get_string('displayreview', 'lesson'));
        $mform->addHelpButton('review', 'displayreview', 'lesson');
        $mform->setDefault('review', $this->lesson->review);

                $numbers = array();
        for ($i = 10; $i > 0; $i--) {
            $numbers[$i] = $i;
        }
        $mform->addElement('select', 'maxattempts', get_string('maximumnumberofattempts', 'lesson'), $numbers);
        $mform->addHelpButton('maxattempts', 'maximumnumberofattempts', 'lesson');
        $mform->setDefault('maxattempts', $this->lesson->maxattempts);

                $mform->addElement('selectyesno', 'retake', get_string('retakesallowed', 'lesson'));
        $mform->addHelpButton('retake', 'retakesallowed', 'lesson');
        $mform->setDefault('retake', $this->lesson->retake);

                $mform->addElement('submit', 'resetbutton',
                get_string('reverttodefaults', 'lesson'));

        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton',
                get_string('save', 'lesson'));
        $buttonarray[] = $mform->createElement('submit', 'againbutton',
                get_string('saveoverrideandstay', 'lesson'));
        $buttonarray[] = $mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonbar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonbar');

    }

    
    public function validation($data, $files) {
        global $COURSE, $DB;
        $errors = parent::validation($data, $files);

        $mform =& $this->_form;
        $lesson = $this->lesson;

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

                if (!empty($data['available']) && !empty($data['deadline'])) {
            if ($data['deadline'] < $data['available'] ) {
                $errors['deadline'] = get_string('closebeforeopen', 'lesson');
            }
        }

                $changed = false;
        $keys = array('available', 'deadline', 'review', 'timelimit', 'maxattempts', 'retake', 'password');
        foreach ($keys as $key) {
            if ($data[$key] != $lesson->{$key}) {
                $changed = true;
                break;
            }
        }

        if (!$changed) {
            $errors['available'] = get_string('nooverridedata', 'lesson');
        }

        return $errors;
    }
}
