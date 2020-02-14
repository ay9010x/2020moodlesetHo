<?php



namespace gradereport_singleview\local\screen;

use html_table;
use html_writer;
use stdClass;
use grade_item;
use grade_grade;
use gradereport_singleview\local\ui\bulk_insert;

defined('MOODLE_INTERNAL') || die;


abstract class tablelike extends screen {

    
    protected $headers = array();

    
    protected $initerrors = array();

    
    protected $definition = array();

    
    public abstract function format_line($item);

    
    public abstract function summary();

    
    public function headers() {
        return $this->headers;
    }

    
    public function set_headers($overwrite) {
        $this->headers = $overwrite;
        return $this;
    }

    
    public function init_errors() {
        return $this->initerrors;
    }

    
    public function set_init_error($mesg) {
        $this->initerrors[] = $mesg;
    }

    
    public function definition() {
        return $this->definition;
    }

    
    public function set_definition($overwrite) {
        $this->definition = $overwrite;
        return $this;
    }

    
    public function format_definition($line, $grade) {
        foreach ($this->definition() as $i => $field) {
                        $tab = ($i * $this->total) + $this->index;
            $classname = '\\gradereport_singleview\\local\\ui\\' . $field;
            $html = new $classname($grade, $tab);

            if ($field == 'finalgrade' and !empty($this->structure)) {
                $html .= $this->structure->get_grade_analysis_icon($grade);
            }

                                    if ($field == 'exclude' && !has_capability('moodle/grade:manage', $this->context)){
                $html->disabled = true;
            }

            $line[] = $html;
        }
        return $line;
    }

    
    public function html() {
        global $OUTPUT;

        if (!empty($this->initerrors)) {
            $warnings = '';
            foreach ($this->initerrors as $mesg) {
                $warnings .= $OUTPUT->notification($mesg);
            }
            return $warnings;
        }
        $table = new html_table();

        $table->head = $this->headers();

        $summary = $this->summary();
        if (!empty($summary)) {
            $table->summary = $summary;
        }

                $this->index = 0;
        $this->total = count($this->items);

        foreach ($this->items as $item) {
            if ($this->index >= ($this->perpage * $this->page) &&
                $this->index < ($this->perpage * ($this->page + 1))) {
                $table->data[] = $this->format_line($item);
            }
            $this->index++;
        }

        $underlying = get_class($this);

        $data = new stdClass();
        $data->table = $table;
        $data->instance = $this;

        $buttonattr = array('class' => 'singleview_buttons submit');
        $buttonhtml = implode(' ', $this->buttons());

        $buttons = html_writer::tag('div', $buttonhtml, $buttonattr);
        $selectview = new select($this->courseid, $this->itemid, $this->groupid);

        $sessionvalidation = html_writer::empty_tag('input',
            array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));

        $html = $selectview->html();
        $html .= html_writer::tag('form',
            $buttons . html_writer::table($table) . $this->bulk_insert() . $buttons . $sessionvalidation,
            array('method' => 'POST')
        );
        $html .= $selectview->html();
        return $html;
    }

    
    public function bulk_insert() {
        return html_writer::tag(
            'div',
            (new bulk_insert($this->item))->html(),
            array('class' => 'singleview_bulk')
        );
    }

    
    public function buttons() {
        $save = html_writer::empty_tag('input', array(
            'type' => 'submit',
            'value' => get_string('save', 'gradereport_singleview'),
        ));

        return array($save);
    }
}
