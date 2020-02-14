<?php

namespace core\progress;

defined('MOODLE_INTERNAL') || die();


class display extends base {
    
    const WIBBLE_STATES = 13;

    
    private $bar;

    protected $lastwibble, $currentstate = 0, $direction = 1;

    
    protected $displaynames = false;

    
    public function __construct($startnow = true) {
        if ($startnow) {
            $this->start_html();
        }
    }

    
    public function set_display_names($displaynames = true) {
        $this->displaynames = $displaynames;
    }

    
    public function start_html() {
        if ($this->bar) {
            throw new \coding_exception('Already started');
        }
        $this->bar = new \progress_bar();
        $this->bar->create();
        echo \html_writer::start_div('wibbler');
    }

    
    public function end_html() {
                $this->bar->update_full(100, '');
        $this->bar = null;

                echo \html_writer::end_div();
    }

    
    public function update_progress() {
                if (!$this->is_in_progress_section()) {
            if ($this->bar) {
                $this->end_html();
            }
        } else {
            if (!$this->bar) {
                $this->start_html();
            }
                                    if (time() != $this->lastwibble) {
                $this->lastwibble = time();
                echo \html_writer::div('', 'wibble state' . $this->currentstate);

                                $this->currentstate += $this->direction;
                if ($this->currentstate < 0 || $this->currentstate >= self::WIBBLE_STATES) {
                    $this->direction = -$this->direction;
                    $this->currentstate += 2 * $this->direction;
                }
                $buffersize = ini_get('output_buffering');
                if (!is_numeric($buffersize)) {
                                                                                $buffersize = 0;
                }
                if ($buffersize) {
                                        echo str_pad('', $buffersize);
                }
            }

                        list ($min, $max) = $this->get_progress_proportion_range();

                        $message = '';
            if ($this->displaynames) {
                $message = $this->get_current_description();
            }
            $this->bar->update_full($min * 100, $message);

                        flush();
        }
    }
}
