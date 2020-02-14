<?php

namespace core\progress;

defined('MOODLE_INTERNAL') || die();


class display_if_slow extends display {
    
    const DEFAULT_DISPLAY_DELAY = 5;

    
    private static $nextid = 1;

    
    protected $id;

    
    protected $heading;

    
    protected $starttime;

    
    public function __construct($heading = '', $delay = self::DEFAULT_DISPLAY_DELAY) {
                $this->starttime = time() + $delay;
        $this->heading = $heading;
        parent::__construct(false);
    }

    
    public function start_html() {
        global $OUTPUT;
        $this->id = 'core_progress_display_if_slow' . self::$nextid;
        self::$nextid++;

                        echo \html_writer::start_div('core_progress_display_if_slow',
                array('id' => $this->id));

                if ($this->heading !== '') {
            echo $OUTPUT->heading($this->heading, 3);
        }

                parent::start_html();
    }

    
    public function update_progress() {
                if ($this->starttime) {
            if (time() > $this->starttime) {
                $this->starttime = 0;
            } else {
                                return;
            }
        }

                parent::update_progress();
    }

    
    public function end_html() {
        parent::end_html();
        echo \html_writer::end_div();
        echo \html_writer::script('document.getElementById("' . $this->id .
                '").style.display = "none"');
    }
}
