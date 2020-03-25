<?php



defined('MOODLE_INTERNAL') || die();


abstract class tool_generator_backend {
    
    const MIN_SIZE = 0;
    
    const MAX_SIZE = 5;
    
    const DEFAULT_SIZE = 3;

    
    protected $fixeddataset;

    
    protected $filesizelimit;

    
    protected $progress;

    
    protected $lastdot;

    
    protected $lastpercentage;

    
    protected $starttime;

    
    protected $size;

    
    public function __construct($size, $fixeddataset = false, $filesizelimit = false, $progress = true) {

                if ($size < self::MIN_SIZE || $size > self::MAX_SIZE) {
            throw new coding_exception('Invalid size');
        }

                $this->size = $size;
        $this->fixeddataset = $fixeddataset;
        $this->filesizelimit = $filesizelimit;
        $this->progress = $progress;
    }

    
    public static function size_for_name($sizename) {
        for ($size = self::MIN_SIZE; $size <= self::MAX_SIZE; $size++) {
            if ($sizename == get_string('shortsize_' . $size, 'tool_generator')) {
                return $size;
            }
        }
        throw new coding_exception("Unknown size name '$sizename'");
    }

    
    protected function log($langstring, $a = null, $leaveopen = false) {
        if (!$this->progress) {
            return;
        }
        if (CLI_SCRIPT) {
            echo '* ';
        } else {
            echo html_writer::start_tag('li');
        }
        echo get_string('progress_' . $langstring, 'tool_generator', $a);
        if (!$leaveopen) {
            if (CLI_SCRIPT) {
                echo "\n";
            } else {
                echo html_writer::end_tag('li');
            }
        } else {
            echo ': ';
            $this->lastdot = time();
            $this->lastpercentage = $this->lastdot;
            $this->starttime = microtime(true);
        }
    }

    
    protected function dot($number, $total) {
        if (!$this->progress) {
            return;
        }
        $now = time();
        if ($now == $this->lastdot) {
            return;
        }
        $this->lastdot = $now;
        if (CLI_SCRIPT) {
            echo '.';
        } else {
            echo ' . ';
        }
        if ($now - $this->lastpercentage >= 30) {
            echo round(100.0 * $number / $total, 1) . '%';
            $this->lastpercentage = $now;
        }

                if (!CLI_SCRIPT) {
            core_php_time_limit::raise(120);
        }
    }

    
    protected function end_log() {
        if (!$this->progress) {
            return;
        }
        echo get_string('done', 'tool_generator', round(microtime(true) - $this->starttime, 1));
        if (CLI_SCRIPT) {
            echo "\n";
        } else {
            echo html_writer::end_tag('li');
        }
    }

}
