<?php



namespace gradereport_singleview\local\ui;

defined('MOODLE_INTERNAL') || die;


class feedback extends grade_attribute_format implements unique_value, be_disabled {

    
    public $name = 'feedback';

    
    public function get_value() {
        return $this->grade->feedback ? $this->grade->feedback : '';
    }

    
    public function get_label() {
        if (!isset($this->grade->label)) {
            $this->grade->label = '';
        }
        return $this->grade->label;
    }

    
    public function is_disabled() {
        $locked = 0;
        $gradeitemlocked = 0;
        $overridden = 0;
        
        if (!empty($this->grade->locked)) {
            $locked = 1;
        }
        if (!empty($this->grade->grade_item->locked)) {
            $gradeitemlocked = 1;
        }
        if ($this->grade->grade_item->is_overridable_item() and !$this->grade->is_overridden()) {
            $overridden = 1;
        }
        return ($locked || $gradeitemlocked || $overridden);
    }

    
    public function determine_format() {
        return new text_attribute(
            $this->get_name(),
            $this->get_value(),
            $this->get_label(),
            $this->is_disabled()
        );
    }

    
    public function set($value) {
        $finalgrade = false;
        $trimmed = trim($value);
        if (empty($trimmed)) {
            $feedback = null;
        } else {
            $feedback = $value;
        }

        $this->grade->grade_item->update_final_grade(
            $this->grade->userid, $finalgrade, 'singleview',
            $feedback, FORMAT_MOODLE
        );
    }
}
