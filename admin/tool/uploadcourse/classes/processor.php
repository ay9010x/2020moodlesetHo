<?php



defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/csvlib.class.php');


class tool_uploadcourse_processor {

    
    const MODE_CREATE_NEW = 1;

    
    const MODE_CREATE_ALL = 2;

    
    const MODE_CREATE_OR_UPDATE = 3;

    
    const MODE_UPDATE_ONLY = 4;

    
    const UPDATE_NOTHING = 0;

    
    const UPDATE_ALL_WITH_DATA_ONLY = 1;

    
    const UPDATE_ALL_WITH_DATA_OR_DEFAUTLS = 2;

    
    const UPDATE_MISSING_WITH_DATA_OR_DEFAUTLS = 3;

    
    protected $mode;

    
    protected $updatemode;

    
    protected $allowrenames = false;

    
    protected $allowdeletes = false;

    
    protected $allowresets = false;

    
    protected $restorefile;

    
    protected $templatecourse;

    
    protected $reset;

    
    protected $shortnametemplate;

    
    protected $cir;

    
    protected $defaults = array();

    
    protected $columns = array();

    
    protected $errors = array();

    
    protected $linenb = 0;

    
    protected $processstarted = false;

    
    public function __construct(csv_import_reader $cir, array $options, array $defaults = array()) {

        if (!isset($options['mode']) || !in_array($options['mode'], array(self::MODE_CREATE_NEW, self::MODE_CREATE_ALL,
                self::MODE_CREATE_OR_UPDATE, self::MODE_UPDATE_ONLY))) {
            throw new coding_exception('Unknown process mode');
        }

                $this->mode = (int) $options['mode'];

        $this->updatemode = self::UPDATE_NOTHING;
        if (isset($options['updatemode'])) {
                        $this->updatemode = (int) $options['updatemode'];
        }
        if (isset($options['allowrenames'])) {
            $this->allowrenames = $options['allowrenames'];
        }
        if (isset($options['allowdeletes'])) {
            $this->allowdeletes = $options['allowdeletes'];
        }
        if (isset($options['allowresets'])) {
            $this->allowresets = $options['allowresets'];
        }

        if (isset($options['restorefile'])) {
            $this->restorefile = $options['restorefile'];
        }
        if (isset($options['templatecourse'])) {
            $this->templatecourse = $options['templatecourse'];
        }
        if (isset($options['reset'])) {
            $this->reset = $options['reset'];
        }
        if (isset($options['shortnametemplate'])) {
            $this->shortnametemplate = $options['shortnametemplate'];
        }

        $this->cir = $cir;
        $this->columns = $cir->get_columns();
        $this->defaults = $defaults;
        $this->validate();
        $this->reset();
    }

    
    public function execute($tracker = null) {
        if ($this->processstarted) {
            throw new coding_exception('Process has already been started');
        }
        $this->processstarted = true;

        if (empty($tracker)) {
            $tracker = new tool_uploadcourse_tracker(tool_uploadcourse_tracker::NO_OUTPUT);
        }
        $tracker->start();

        $total = 0;
        $created = 0;
        $updated = 0;
        $deleted = 0;
        $errors = 0;

                core_php_time_limit::raise();
        raise_memory_limit(MEMORY_EXTRA);

                while ($line = $this->cir->next()) {
            $this->linenb++;
            $total++;

            $data = $this->parse_line($line);
            $course = $this->get_course($data);
            if ($course->prepare()) {
                $course->proceed();

                $status = $course->get_statuses();
                if (array_key_exists('coursecreated', $status)) {
                    $created++;
                } else if (array_key_exists('courseupdated', $status)) {
                    $updated++;
                } else if (array_key_exists('coursedeleted', $status)) {
                    $deleted++;
                }

                $data = array_merge($data, $course->get_data(), array('id' => $course->get_id()));
                $tracker->output($this->linenb, true, $status, $data);
            } else {
                $errors++;
                $tracker->output($this->linenb, false, $course->get_errors(), $data);
            }
        }

        $tracker->finish();
        $tracker->results($total, $created, $updated, $deleted, $errors);
    }

    
    protected function get_course($data) {
        $importoptions = array(
            'candelete' => $this->allowdeletes,
            'canrename' => $this->allowrenames,
            'canreset' => $this->allowresets,
            'reset' => $this->reset,
            'restoredir' => $this->get_restore_content_dir(),
            'shortnametemplate' => $this->shortnametemplate
        );
        return new tool_uploadcourse_course($this->mode, $this->updatemode, $data, $this->defaults, $importoptions);
    }

    
    public function get_errors() {
        return $this->errors;
    }

    
    protected function get_restore_content_dir() {
        $backupfile = null;
        $shortname = null;

        if (!empty($this->restorefile)) {
            $backupfile = $this->restorefile;
        } else if (!empty($this->templatecourse) || is_numeric($this->templatecourse)) {
            $shortname = $this->templatecourse;
        }

        $dir = tool_uploadcourse_helper::get_restore_content_dir($backupfile, $shortname);
        return $dir;
    }

    
    protected function log_error($errors) {
        if (empty($errors)) {
            return;
        }

        foreach ($errors as $code => $langstring) {
            if (!isset($this->errors[$this->linenb])) {
                $this->errors[$this->linenb] = array();
            }
            $this->errors[$this->linenb][$code] = $langstring;
        }
    }

    
    protected function parse_line($line) {
        $data = array();
        foreach ($line as $keynum => $value) {
            if (!isset($this->columns[$keynum])) {
                                continue;
            }

            $key = $this->columns[$keynum];
            $data[$key] = $value;
        }
        return $data;
    }

    
    public function preview($rows = 10, $tracker = null) {
        if ($this->processstarted) {
            throw new coding_exception('Process has already been started');
        }
        $this->processstarted = true;

        if (empty($tracker)) {
            $tracker = new tool_uploadcourse_tracker(tool_uploadcourse_tracker::NO_OUTPUT);
        }
        $tracker->start();

                core_php_time_limit::raise();
        raise_memory_limit(MEMORY_EXTRA);

                $preview = array();
        while (($line = $this->cir->next()) && $rows > $this->linenb) {
            $this->linenb++;
            $data = $this->parse_line($line);
            $course = $this->get_course($data);
            $result = $course->prepare();
            if (!$result) {
                $tracker->output($this->linenb, $result, $course->get_errors(), $data);
            } else {
                $tracker->output($this->linenb, $result, $course->get_statuses(), $data);
            }
            $row = $data;
            $preview[$this->linenb] = $row;
        }

        $tracker->finish();

        return $preview;
    }

    
    public function reset() {
        $this->processstarted = false;
        $this->linenb = 0;
        $this->cir->init();
        $this->errors = array();
    }

    
    protected function validate() {
        if (empty($this->columns)) {
            throw new moodle_exception('cannotreadtmpfile', 'error');
        } else if (count($this->columns) < 2) {
            throw new moodle_exception('csvfewcolumns', 'error');
        }
    }
}
