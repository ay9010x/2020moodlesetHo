<?php



defined('MOODLE_INTERNAL') || die();


class tool_generator_course_backend extends tool_generator_backend {
    
    private static $paramsections = array(1, 10, 100, 500, 1000, 2000);
    
    private static $paramassignments = array(1, 10, 100, 500, 1000, 2000);
    
    private static $parampages = array(1, 50, 200, 1000, 5000, 10000);
    
    private static $paramusers = array(1, 100, 1000, 10000, 50000, 100000);
    
    private static $paramsmallfilecount = array(1, 64, 128, 1024, 16384, 32768);
    
    private static $paramsmallfilesize = array(1024, 16384, 81920, 102400, 65536, 65536);
    
    private static $parambigfilecount = array(1, 2, 5, 10, 10, 10);
    
    private static $parambigfilesize = array(8192, 4194304, 16777216, 83886080,
            858993459, 1717986918);
    
    private static $paramforumdiscussions = array(1, 10, 100, 500, 1000, 2000);
    
    private static $paramforumposts = array(2, 2, 5, 10, 10, 10);

    
    private $shortname;

    
    private $fullname = "";

    
    private $summary = "";

    
    private $summaryformat = FORMAT_HTML;

    
    protected $generator;

    
    private $course;

    
    private $userids;

    
    public function __construct(
        $shortname,
        $size,
        $fixeddataset = false,
        $filesizelimit = false,
        $progress = true,
        $fullname = null,
        $summary = null,
        $summaryformat = FORMAT_HTML) {

                $this->shortname = $shortname;

                if (empty($fullname)) {
            $this->fullname = get_string(
                'fullname',
                'tool_generator',
                array(
                    'size' => get_string('shortsize_' . $size, 'tool_generator')
                )
            );
        } else {
            $this->fullname = $fullname;
        }

                if (!is_null($summary)) {
            $this->summary = $summary;
            $this->summaryformat = $summaryformat;
        }

        parent::__construct($size, $fixeddataset, $filesizelimit, $progress);
    }

    
    public static function get_users_per_size() {
        return self::$paramusers;
    }

    
    public static function get_size_choices() {
        $options = array();
        for ($size = self::MIN_SIZE; $size <= self::MAX_SIZE; $size++) {
            $options[$size] = get_string('coursesize_' . $size, 'tool_generator');
        }
        return $options;
    }

    
    public static function check_shortname_available($shortname) {
        global $DB;
        $fullname = $DB->get_field('course', 'fullname',
                array('shortname' => $shortname), IGNORE_MISSING);
        if ($fullname !== false) {
                                                            return get_string('shortnametaken', 'moodle', $fullname);
        }
        return '';
    }

    
    public function make() {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/lib/phpunit/classes/util.php');

        raise_memory_limit(MEMORY_EXTRA);

        if ($this->progress && !CLI_SCRIPT) {
            echo html_writer::start_tag('ul');
        }

        $entirestart = microtime(true);

                $transaction = $DB->start_delegated_transaction();

                $this->generator = phpunit_util::get_data_generator();

                $this->course = $this->create_course();
        $this->create_users();
        $this->create_assignments();
        $this->create_pages();
        $this->create_small_files();
        $this->create_big_files();
        $this->create_forum();

                $this->log('coursecompleted', round(microtime(true) - $entirestart, 1));

        if ($this->progress && !CLI_SCRIPT) {
            echo html_writer::end_tag('ul');
        }

                $transaction->allow_commit();
        return $this->course->id;
    }

    
    private function create_course() {
        $this->log('createcourse', $this->shortname);
        $courserecord = array(
            'shortname' => $this->shortname,
            'fullname' => $this->fullname,
            'numsections' => self::$paramsections[$this->size],
            'startdate' => usergetmidnight(time())
        );
        if (strlen($this->summary) > 0) {
            $courserecord['summary'] = $this->summary;
            $courserecord['summary_format'] = $this->summaryformat;
        }

        return $this->generator->create_course($courserecord, array('createsections' => true));
    }

    
    private function create_users() {
        global $DB;

                $count = self::$paramusers[$this->size];

                        $this->log('checkaccounts', $count);
        $nextnumber = 1;
        $rs = $DB->get_recordset_select('user', $DB->sql_like('username', '?'),
                array('tool_generator_%'), 'username', 'id, username');
        foreach ($rs as $rec) {
                        $matches = array();
            if (!preg_match('~^tool_generator_([0-9]{6})$~', $rec->username, $matches)) {
                continue;
            }
            $number = (int)$matches[1];

                        if ($number != $nextnumber) {
                $this->create_user_accounts($nextnumber, min($number - 1, $count));
            } else {
                $this->userids[$number] = (int)$rec->id;
            }

                        $nextnumber = $number + 1;
            if ($number >= $count) {
                break;
            }
        }
        $rs->close();

                if ($nextnumber <= $count) {
            $this->create_user_accounts($nextnumber, $count);
        }

                $this->log('enrol', $count, true);

        $enrolplugin = enrol_get_plugin('manual');
        $instances = enrol_get_instances($this->course->id, true);
        foreach ($instances as $instance) {
            if ($instance->enrol === 'manual') {
                break;
            }
        }
        if ($instance->enrol !== 'manual') {
            throw new coding_exception('No manual enrol plugin in course');
        }
        $role = $DB->get_record('role', array('shortname' => 'student'), '*', MUST_EXIST);

        for ($number = 1; $number <= $count; $number++) {
                        $enrolplugin->enrol_user($instance, $this->userids[$number], $role->id);
            $this->dot($number, $count);
        }

                reset($this->userids);

        $this->end_log();
    }

    
    private function create_user_accounts($first, $last) {
        global $CFG;

        $this->log('createaccounts', (object)array('from' => $first, 'to' => $last), true);
        $count = $last - $first + 1;
        $done = 0;
        for ($number = $first; $number <= $last; $number++, $done++) {
                        $textnumber = (string)$number;
            while (strlen($textnumber) < 6) {
                $textnumber = '0' . $textnumber;
            }
            $username = 'tool_generator_' . $textnumber;

                        $record = array('username' => $username, 'idnumber' => $number);

                        if (!empty($CFG->tool_generator_users_password)) {
                $record['password'] = $CFG->tool_generator_users_password;
            }

            $user = $this->generator->create_user($record);
            $this->userids[$number] = (int)$user->id;
            $this->dot($done, $count);
        }
        $this->end_log();
    }

    
    private function create_assignments() {
                $assigngenerator = $this->generator->get_plugin_generator('mod_assign');

                $number = self::$paramassignments[$this->size];
        $this->log('createassignments', $number, true);
        for ($i = 0; $i < $number; $i++) {
            $record = array('course' => $this->course);
            $options = array('section' => $this->get_target_section());
            $assigngenerator->create_instance($record, $options);
            $this->dot($i, $number);
        }

        $this->end_log();
    }

    
    private function create_pages() {
                $pagegenerator = $this->generator->get_plugin_generator('mod_page');

                $number = self::$parampages[$this->size];
        $this->log('createpages', $number, true);
        for ($i = 0; $i < $number; $i++) {
            $record = array('course' => $this->course);
            $options = array('section' => $this->get_target_section());
            $pagegenerator->create_instance($record, $options);
            $this->dot($i, $number);
        }

        $this->end_log();
    }

    
    private function create_small_files() {
        $count = self::$paramsmallfilecount[$this->size];
        $this->log('createsmallfiles', $count, true);

                $resourcegenerator = $this->generator->get_plugin_generator('mod_resource');
        $record = array('course' => $this->course,
                'name' => get_string('smallfiles', 'tool_generator'));
        $options = array('section' => 0);
        $resource = $resourcegenerator->create_instance($record, $options);

                $fs = get_file_storage();
        $context = context_module::instance($resource->cmid);
        $filerecord = array('component' => 'mod_resource', 'filearea' => 'content',
                'contextid' => $context->id, 'itemid' => 0, 'filepath' => '/');
        for ($i = 0; $i < $count; $i++) {
            $filerecord['filename'] = 'smallfile' . $i . '.dat';

                                    $data = self::get_random_binary($this->limit_filesize(self::$paramsmallfilesize[$this->size]));

            $fs->create_file_from_string($filerecord, $data);
            $this->dot($i, $count);
        }

        $this->end_log();
    }

    
    private static function get_random_binary($length) {

        $data = microtime(true);
        if (strlen($data) > $length) {
                        return substr($data, -$length);
        }
        $length -= strlen($data);
        for ($j = 0; $j < $length; $j++) {
            $data .= chr(rand(1, 255));
        }
        return $data;
    }

    
    private function create_big_files() {
        global $CFG;

                $count = self::$parambigfilecount[$this->size];
        $filesize = $this->limit_filesize(self::$parambigfilesize[$this->size]);
        $blocks = ceil($filesize / 65536);
        $blocksize = floor($filesize / $blocks);

        $this->log('createbigfiles', $count, true);

                $tempfolder = make_temp_directory('tool_generator');
        $tempfile = $tempfolder . '/' . rand();

                $fs = get_file_storage();
        $resourcegenerator = $this->generator->get_plugin_generator('mod_resource');
        for ($i = 0; $i < $count; $i++) {
                        $record = array('course' => $this->course,
                    'name' => get_string('bigfile', 'tool_generator', $i));
            $options = array('section' => $this->get_target_section());
            $resource = $resourcegenerator->create_instance($record, $options);

                        $handle = fopen($tempfile, 'w');
            if (!$handle) {
                throw new coding_exception('Failed to open temporary file');
            }
            for ($j = 0; $j < $blocks; $j++) {
                $data = self::get_random_binary($blocksize);
                fwrite($handle, $data);
                $this->dot($i * $blocks + $j, $count * $blocks);
            }
            fclose($handle);

                        $context = context_module::instance($resource->cmid);
            $filerecord = array('component' => 'mod_resource', 'filearea' => 'content',
                    'contextid' => $context->id, 'itemid' => 0, 'filepath' => '/',
                    'filename' => 'bigfile' . $i . '.dat');
            $fs->create_file_from_pathname($filerecord, $tempfile);
        }

        unlink($tempfile);
        $this->end_log();
    }

    
    private function create_forum() {
        global $DB;

        $discussions = self::$paramforumdiscussions[$this->size];
        $posts = self::$paramforumposts[$this->size];
        $totalposts = $discussions * $posts;

        $this->log('createforum', $totalposts, true);

                $forumgenerator = $this->generator->get_plugin_generator('mod_forum');
        $record = array('course' => $this->course,
                'name' => get_string('pluginname', 'forum'));
        $options = array('section' => 0);
        $forum = $forumgenerator->create_instance($record, $options);

                $sofar = 0;
        for ($i = 0; $i < $discussions; $i++) {
            $record = array('forum' => $forum->id, 'course' => $this->course->id,
                    'userid' => $this->get_target_user());
            $discussion = $forumgenerator->create_discussion($record);
            $parentid = $DB->get_field('forum_posts', 'id', array('discussion' => $discussion->id), MUST_EXIST);
            $sofar++;
            for ($j = 0; $j < $posts - 1; $j++, $sofar++) {
                $record = array('discussion' => $discussion->id,
                        'userid' => $this->get_target_user(), 'parent' => $parentid);
                $forumgenerator->create_post($record);
                $this->dot($sofar, $totalposts);
            }
        }

        $this->end_log();
    }

    
    private function get_target_section() {

        if (!$this->fixeddataset) {
            $key = rand(1, self::$paramsections[$this->size]);
        } else {
                        $key = 1;
        }

        return $key;
    }

    
    private function get_target_user() {

        if (!$this->fixeddataset) {
            $userid = $this->userids[rand(1, self::$paramusers[$this->size])];
        } else if ($userid = current($this->userids)) {
                        next($this->userids);
        } else {
                        $userid = reset($this->userids);
        }

        return $userid;
    }

    
    private function limit_filesize($length) {

                if (is_numeric($this->filesizelimit) && $length > $this->filesizelimit) {
            $length = floor($this->filesizelimit);
        }

        return $length;
    }

}
