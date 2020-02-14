<?php


namespace gradereport_singleview\local\ui;

use html_writer;

defined('MOODLE_INTERNAL') || die;


class checkbox_attribute extends element {

    
    private $ischecked;

    
    public function __construct($name, $label, $ischecked = false, $locked=0) {
        $this->ischecked = $ischecked;
        $this->locked = $locked;
        parent::__construct($name, 1, $label);
    }

    
    public function is_checkbox() {
        return true;
    }

    
    public function html() {

        $attributes = array(
            'type' => 'checkbox',
            'name' => $this->name,
            'value' => 1,
            'id' => $this->name
        );

                if ( $this->locked) {
            $attributes['disabled'] = 'DISABLED';
        }

        $hidden = array(
            'type' => 'hidden',
            'name' => 'old' . $this->name
        );

        if ($this->ischecked) {
            $attributes['checked'] = 'CHECKED';
            $hidden['value'] = 1;
        }

        $type = "override";
        if (preg_match("/^exclude/", $this->name)) {
            $type = "exclude";
        }

        return (
            html_writer::tag('label',
                             get_string($type . 'for', 'gradereport_singleview', $this->label),
                             array('for' => $this->name, 'class' => 'accesshide')) .
            html_writer::empty_tag('input', $attributes) .
            html_writer::empty_tag('input', $hidden)
        );
    }
}
