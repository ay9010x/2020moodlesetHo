<?php



namespace gradereport_singleview\local\ui;

use html_writer;

defined('MOODLE_INTERNAL') || die;


class dropdown_attribute extends element {

    
    private $selected;

    
    private $options;

    
    private $isdisabled;

    
    public function __construct($name, $options, $label, $selected = '', $isdisabled = false) {
        $this->selected = $selected;
        $this->options = $options;
        $this->isdisabled = $isdisabled;
        parent::__construct($name, $selected, $label);
    }

    
    public function is_dropdown() {
        return true;
    }

    
    public function html() {
        $old = array(
            'type' => 'hidden',
            'name' => 'old' . $this->name,
            'value' => $this->selected
        );

        $attributes = array('tabindex' => '1');

        if (!empty($this->isdisabled)) {
            $attributes['disabled'] = 'DISABLED';
        }

        $select = html_writer::select(
            $this->options, $this->name, $this->selected, false, $attributes
        );

        return ($select . html_writer::empty_tag('input', $old));
    }
}
