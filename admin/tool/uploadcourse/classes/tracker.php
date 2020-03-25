<?php



defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/weblib.php');


class tool_uploadcourse_tracker {

    
    const NO_OUTPUT = 0;

    
    const OUTPUT_HTML = 1;

    
    const OUTPUT_PLAIN = 2;

    
    protected $columns = array('line', 'result', 'id', 'shortname', 'fullname', 'idnumber', 'status');

    
    protected $rownb = 0;

    
    protected $outputmode;

    
    protected $buffer;

    
    public function __construct($outputmode = self::NO_OUTPUT) {
        $this->outputmode = $outputmode;
        if ($this->outputmode == self::OUTPUT_PLAIN) {
            $this->buffer = new progress_trace_buffer(new text_progress_trace());
        }
    }

    
    public function finish() {
        if ($this->outputmode == self::NO_OUTPUT) {
            return;
        }

        if ($this->outputmode == self::OUTPUT_HTML) {
            echo html_writer::end_tag('table');
        }
    }

    
    public function results($total, $created, $updated, $deleted, $errors) {
        if ($this->outputmode == self::NO_OUTPUT) {
            return;
        }

        $message = array(
            get_string('coursestotal', 'tool_uploadcourse', $total),
            get_string('coursescreated', 'tool_uploadcourse', $created),
            get_string('coursesupdated', 'tool_uploadcourse', $updated),
            get_string('coursesdeleted', 'tool_uploadcourse', $deleted),
            get_string('courseserrors', 'tool_uploadcourse', $errors)
        );

        if ($this->outputmode == self::OUTPUT_PLAIN) {
            foreach ($message as $msg) {
                $this->buffer->output($msg);
            }
        } else if ($this->outputmode == self::OUTPUT_HTML) {
            $buffer = new progress_trace_buffer(new html_list_progress_trace());
            foreach ($message as $msg) {
                $buffer->output($msg);
            }
            $buffer->finished();
        }
    }

    
    public function output($line, $outcome, $status, $data) {
        global $OUTPUT;
        if ($this->outputmode == self::NO_OUTPUT) {
            return;
        }

        if ($this->outputmode == self::OUTPUT_PLAIN) {
            $message = array(
                $line,
                $outcome ? 'OK' : 'NOK',
                isset($data['id']) ? $data['id'] : '',
                isset($data['shortname']) ? $data['shortname'] : '',
                isset($data['fullname']) ? $data['fullname'] : '',
                isset($data['idnumber']) ? $data['idnumber'] : ''
            );
            $this->buffer->output(implode("\t", $message));
            if (!empty($status)) {
                foreach ($status as $st) {
                    $this->buffer->output($st, 1);
                }
            }
        } else if ($this->outputmode == self::OUTPUT_HTML) {
            $ci = 0;
            $this->rownb++;
            if (is_array($status)) {
                $status = implode(html_writer::empty_tag('br'), $status);
            }
            if ($outcome) {
                $outcome = $OUTPUT->pix_icon('i/valid', '');
            } else {
                $outcome = $OUTPUT->pix_icon('i/invalid', '');
            }
            echo html_writer::start_tag('tr', array('class' => 'r' . $this->rownb % 2));
            echo html_writer::tag('td', $line, array('class' => 'c' . $ci++));
            echo html_writer::tag('td', $outcome, array('class' => 'c' . $ci++));
            echo html_writer::tag('td', isset($data['id']) ? $data['id'] : '', array('class' => 'c' . $ci++));
            echo html_writer::tag('td', isset($data['shortname']) ? $data['shortname'] : '', array('class' => 'c' . $ci++));
            echo html_writer::tag('td', isset($data['fullname']) ? $data['fullname'] : '', array('class' => 'c' . $ci++));
            echo html_writer::tag('td', isset($data['idnumber']) ? $data['idnumber'] : '', array('class' => 'c' . $ci++));
            echo html_writer::tag('td', $status, array('class' => 'c' . $ci++));
            echo html_writer::end_tag('tr');
        }
    }

    
    public function start() {
        if ($this->outputmode == self::NO_OUTPUT) {
            return;
        }

        if ($this->outputmode == self::OUTPUT_PLAIN) {
            $columns = array_flip($this->columns);
            unset($columns['status']);
            $columns = array_flip($columns);
            $this->buffer->output(implode("\t", $columns));
        } else if ($this->outputmode == self::OUTPUT_HTML) {
            $ci = 0;
            echo html_writer::start_tag('table', array('class' => 'generaltable boxaligncenter flexible-wrap',
                'summary' => get_string('uploadcoursesresult', 'tool_uploadcourse')));
            echo html_writer::start_tag('tr', array('class' => 'heading r' . $this->rownb));
            echo html_writer::tag('th', get_string('csvline', 'tool_uploadcourse'),
                array('class' => 'c' . $ci++, 'scope' => 'col'));
            echo html_writer::tag('th', get_string('result', 'tool_uploadcourse'), array('class' => 'c' . $ci++, 'scope' => 'col'));
            echo html_writer::tag('th', get_string('id', 'tool_uploadcourse'), array('class' => 'c' . $ci++, 'scope' => 'col'));
            echo html_writer::tag('th', get_string('shortname'), array('class' => 'c' . $ci++, 'scope' => 'col'));
            echo html_writer::tag('th', get_string('fullname'), array('class' => 'c' . $ci++, 'scope' => 'col'));
            echo html_writer::tag('th', get_string('idnumber'), array('class' => 'c' . $ci++, 'scope' => 'col'));
            echo html_writer::tag('th', get_string('status'), array('class' => 'c' . $ci++, 'scope' => 'col'));
            echo html_writer::end_tag('tr');
        }
    }

}
