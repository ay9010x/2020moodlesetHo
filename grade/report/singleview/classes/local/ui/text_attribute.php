<?php



namespace gradereport_singleview\local\ui;

use html_writer;
defined('MOODLE_INTERNAL') || die;


class text_attribute extends element {

    
    private $isdisabled;

    
    public function __construct($name, $value, $label, $isdisabled = false) {
        $this->isdisabled = $isdisabled;
        parent::__construct($name, $value, $label);
    }

    
    public function is_textbox() {
        return true;
    }

    
    public function html() {
        $attributes = array(
            'type' => 'text',
            'name' => $this->name,
            'value' => $this->value,
            'id' => $this->name
        );

        if ($this->isdisabled) {
            $attributes['disabled'] = 'DISABLED';
        }

        $hidden = array(
            'type' => 'hidden',
            'name' => 'old' . $this->name,
            'value' => $this->value
        );

        $label = '';
        if (preg_match("/^feedback/", $this->name)) {
            $labeltitle = get_string('feedbackfor', 'gradereport_singleview', $this->label);
            $attributes['tabindex'] = '2';
            $label = html_writer::tag('label', $labeltitle,  array('for' => $this->name, 'class' => 'accesshide'));
        } else if (preg_match("/^finalgrade/", $this->name)) {
            $labeltitle = get_string('gradefor', 'gradereport_singleview', $this->label);
            $attributes['tabindex'] = '1';
            $label = html_writer::tag('label', $labeltitle,  array('for' => $this->name, 'class' => 'accesshide'));
        }

        return (
            $label .
            html_writer::empty_tag('input', $attributes) .
            html_writer::empty_tag('input', $hidden)
        );
    }
}
