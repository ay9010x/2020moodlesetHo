<?php



defined('MOODLE_INTERNAL') || die();


class tool_generator_site_backend extends tool_generator_backend {

    
    const SHORTNAMEPREFIX = 'testcourse_';

    
    protected $bypasscheck;

    
    protected static $sitecourses = array(
        array(2, 8, 64, 256, 1024, 4096),
        array(1, 4, 8, 16, 32, 64),
        array(0, 0, 1, 4, 8, 16),
        array(0, 0, 0, 1, 0, 0),
        array(0, 0, 0, 0, 1, 0),
        array(0, 0, 0, 0, 0, 1)
    );

    
    public function __construct($size, $bypasscheck, $fixeddataset = false, $filesizelimit = false, $progress = true) {

                $this->bypasscheck = $bypasscheck;

        parent::__construct($size, $fixeddataset, $filesizelimit, $progress);
    }

    
    public static function get_size_choices() {
        $options = array();
        for ($size = self::MIN_SIZE; $size <= self::MAX_SIZE; $size++) {
            $options[$size] = get_string('sitesize_' . $size, 'tool_generator');
        }
        return $options;
    }

    
    public function make() {
        global $DB, $CFG;

        raise_memory_limit(MEMORY_EXTRA);

        if ($this->progress && !CLI_SCRIPT) {
            echo html_writer::start_tag('ul');
        }

        $entirestart = microtime(true);

                $prevchdir = getcwd();
        chdir($CFG->dirroot);
        $ncourse = self::get_last_testcourse_id();
        foreach (self::$sitecourses as $coursesize => $ncourses) {
            for ($i = 1; $i <= $ncourses[$this->size]; $i++) {
                                $ncourse++;
                $this->run_create_course(self::SHORTNAMEPREFIX . $ncourse, $coursesize);
            }
        }
        chdir($prevchdir);

                $lastcourseid = $DB->get_field('course', 'id', array('shortname' => self::SHORTNAMEPREFIX . $ncourse));

                $this->log('sitecompleted', round(microtime(true) - $entirestart, 1));

        if ($this->progress && !CLI_SCRIPT) {
            echo html_writer::end_tag('ul');
        }

        return $lastcourseid;
    }

    
    protected function run_create_course($shortname, $coursesize) {

                $command = 'php admin/tool/generator/cli/maketestcourse.php';

        $options = array(
            '--shortname="' . $shortname . '"',
            '--size="' . get_string('shortsize_' . $coursesize, 'tool_generator') . '"'
        );

        if (!$this->progress) {
            $options[] = '--quiet';
        }

        if ($this->filesizelimit) {
            $options[] = '--filesizelimit="' . $this->filesizelimit . '"';
        }

                $optionstoextend = array(
            'fixeddataset' => 'fixeddataset',
            'bypasscheck' => 'bypasscheck',
        );

                foreach ($optionstoextend as $attribute => $option) {
            if (!empty($this->{$attribute})) {
                $options[] = '--' . $option;
            }
        }
        $options = implode(' ', $options);
        if ($this->progress) {
            system($command . ' ' . $options, $exitcode);
        } else {
            passthru($command . ' ' . $options, $exitcode);
        }

        if ($exitcode != 0) {
            exit($exitcode);
        }
    }

    
    protected static function get_last_testcourse_id() {
        global $DB;

        $params = array();
        $params['shortnameprefix'] = $DB->sql_like_escape(self::SHORTNAMEPREFIX) . '%';
        $like = $DB->sql_like('shortname', ':shortnameprefix');

        if (!$testcourses = $DB->get_records_select('course', $like, $params, '', 'shortname')) {
            return 0;
        }
                $shortnames = array_keys($testcourses);
        core_collator::asort($shortnames, core_collator::SORT_NATURAL);
        $shortnames = array_reverse($shortnames);

                $prefixnchars = strlen(self::SHORTNAMEPREFIX);
        foreach ($shortnames as $shortname) {
            $sufix = substr($shortname, $prefixnchars);
            if (preg_match('/^[\d]+$/', $sufix)) {
                return $sufix;
            }
        }
                return 0;
    }

}
