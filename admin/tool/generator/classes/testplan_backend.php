<?php



defined('MOODLE_INTERNAL') || die();


class tool_generator_testplan_backend extends tool_generator_backend {

    
    protected static $repourl = 'https://github.com/moodlehq/moodle-performance-comparison';

    
    protected static $users = array(1, 30, 100, 1000, 5000, 10000);

    
    protected static $loops = array(5, 5, 5, 6, 6, 7);

    
    protected static $rampups = array(1, 6, 40, 100, 500, 800);

    
    public static function get_size_choices() {

        $options = array();
        for ($size = self::MIN_SIZE; $size <= self::MAX_SIZE; $size++) {
            $a = new stdClass();
            $a->users = self::$users[$size];
            $a->loops = self::$loops[$size];
            $a->rampup = self::$rampups[$size];
            $options[$size] = get_string('testplansize_' . $size, 'tool_generator', $a);
        }
        return $options;
    }

    
    public static function get_course_options() {
        $courses = get_courses('all', 'c.sortorder ASC', 'c.id, c.shortname, c.fullname');
        if (!$courses) {
            print_error('error_nocourses', 'tool_generator');
        }

        $options = array();
        unset($courses[1]);
        foreach ($courses as $course) {
            $options[$course->id] = $course->fullname . '(' . $course->shortname . ')';
        }
        return $options;
    }

    
    public static function get_repourl() {
        return self::$repourl;
    }

    
    public static function create_testplan_file($courseid, $size) {
        $jmxcontents = self::generate_test_plan($courseid, $size);

        $fs = get_file_storage();
        $filerecord = self::get_file_record('testplan', 'jmx');
        return $fs->create_file_from_string($filerecord, $jmxcontents);
    }

    
    public static function create_users_file($courseid, $updateuserspassword) {
        $csvcontents = self::generate_users_file($courseid, $updateuserspassword);

        $fs = get_file_storage();
        $filerecord = self::get_file_record('users', 'csv');
        return $fs->create_file_from_string($filerecord, $csvcontents);
    }

    
    protected static function generate_test_plan($targetcourseid, $size) {
        global $CFG;

                $template = file_get_contents(__DIR__ . '/../testplan.template.jmx');

                $coursedata = self::get_course_test_data($targetcourseid);

                $urlcomponents = parse_url($CFG->wwwroot);
        if (empty($urlcomponents['path'])) {
            $urlcomponents['path'] = '';
        }

        $replacements = array(
            $CFG->version,
            self::$users[$size],
            self::$loops[$size],
            self::$rampups[$size],
            $urlcomponents['host'],
            $urlcomponents['path'],
            get_string('shortsize_' . $size, 'tool_generator'),
            $targetcourseid,
            $coursedata->pageid,
            $coursedata->forumid,
            $coursedata->forumdiscussionid,
            $coursedata->forumreplyid
        );

        $placeholders = array(
            '{{MOODLEVERSION_PLACEHOLDER}}',
            '{{USERS_PLACEHOLDER}}',
            '{{LOOPS_PLACEHOLDER}}',
            '{{RAMPUP_PLACEHOLDER}}',
            '{{HOST_PLACEHOLDER}}',
            '{{SITEPATH_PLACEHOLDER}}',
            '{{SIZE_PLACEHOLDER}}',
            '{{COURSEID_PLACEHOLDER}}',
            '{{PAGEACTIVITYID_PLACEHOLDER}}',
            '{{FORUMACTIVITYID_PLACEHOLDER}}',
            '{{FORUMDISCUSSIONID_PLACEHOLDER}}',
            '{{FORUMREPLYID_PLACEHOLDER}}'
        );

                return str_replace($placeholders, $replacements, $template);
    }

    
    protected static function generate_users_file($targetcourseid, $updateuserspassword) {
        global $CFG;

        $coursecontext = context_course::instance($targetcourseid);

        $users = get_enrolled_users($coursecontext, '', 0, 'u.id, u.username, u.auth', 'u.username ASC');
        if (!$users) {
            print_error('coursewithoutusers', 'tool_generator');
        }

        $lines = array();
        foreach ($users as $user) {

                        if ($updateuserspassword) {
                $userauth = get_auth_plugin($user->auth);
                if (!$userauth->user_update_password($user, $CFG->tool_generator_users_password)) {
                    print_error('errorpasswordupdate', 'auth');
                }
            }

                        $lines[] = $user->username . ',' . $CFG->tool_generator_users_password;
        }

        return implode(PHP_EOL, $lines);
    }

    
    protected static function get_file_record($filearea, $filetype) {

        $systemcontext = context_system::instance();

        $filerecord = new stdClass();
        $filerecord->contextid = $systemcontext->id;
        $filerecord->component = 'tool_generator';
        $filerecord->filearea = $filearea;
        $filerecord->itemid = 0;
        $filerecord->filepath = '/';

                $filerecord->filename = $filearea . '_' . date('YmdHi', time()) . '_' . rand(1000, 9999) . '.' . $filetype;

        return $filerecord;
    }

    
    protected static function get_course_test_data($targetcourseid) {
        global $DB, $USER;

        $data = new stdClass();

                $course = new stdClass();
        $course->id = $targetcourseid;
        $courseinfo = new course_modinfo($course, $USER->id);

                if (!$pages = $courseinfo->get_instances_of('page')) {
            print_error('error_nopageinstances', 'tool_generator');
        }
        $data->pageid = reset($pages)->id;

                if (!$forums = $courseinfo->get_instances_of('forum')) {
            print_error('error_noforuminstances', 'tool_generator');
        }
        $forum = reset($forums);

                if (!$discussions = forum_get_discussions($forum, 'd.timemodified ASC', false, -1, 1)) {
            print_error('error_noforumdiscussions', 'tool_generator');
        }
        $discussion = reset($discussions);

        $data->forumid = $forum->id;
        $data->forumdiscussionid = $discussion->discussion;
        $data->forumreplyid = $discussion->id;

                return $data;
    }

    
    public static function has_selected_course_any_problem($course, $size) {
        global $DB;

        $errors = array();

        if (!is_numeric($course)) {
            if (!$course = $DB->get_field('course', 'id', array('shortname' => $course))) {
                $errors['courseid'] = get_string('error_nonexistingcourse', 'tool_generator');
                return $errors;
            }
        }

        $coursecontext = context_course::instance($course, IGNORE_MISSING);
        if (!$coursecontext) {
            $errors['courseid'] = get_string('error_nonexistingcourse', 'tool_generator');
            return $errors;
        }

        if (!$users = get_enrolled_users($coursecontext, '', 0, 'u.id')) {
            $errors['courseid'] = get_string('coursewithoutusers', 'tool_generator');
        }

                $coursesizes = tool_generator_course_backend::get_users_per_size();
        if (count($users) < self::$users[$size]) {
            $errors['size'] = get_string('notenoughusers', 'tool_generator');
        }

        if (empty($errors)) {
            return false;
        }

        return $errors;
    }
}
