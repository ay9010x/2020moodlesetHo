<?php



namespace gradereport_singleview\local\ui;

defined('MOODLE_INTERNAL') || die;


class override extends grade_attribute_format implements be_checked, be_disabled {

    
    public $name = 'override';

    
    public function is_checked() {
        return $this->grade->is_overridden();
    }

    
    public function is_disabled() {
        $lockedgrade = $lockedgradeitem = 0;
        if (!empty($this->grade->locked)) {
            $lockedgrade = 1;
        }
        if (!empty($this->grade->grade_item->locked)) {
            $lockedgradeitem = 1;
        }
        return ($lockedgrade || $lockedgradeitem);
    }

    
    public function get_label() {
        if (!isset($this->grade->label)) {
            $this->grade->label = '';
        }
        return $this->grade->label;
    }

    
    public function determine_format() {
        if (!$this->grade->grade_item->is_overridable_item()) {
            return new empty_element();
        }
        return new checkbox_attribute(
            $this->get_name(),
            $this->get_label(),
            $this->is_checked(),
            $this->is_disabled()
        );
    }

    
    public function set($value) {
        if (empty($this->grade->id)) {
            return false;
        }

        $state = $value == 0 ? false : true;

        $this->grade->set_overridden($state);
        $this->grade->grade_item->force_regrading();
        return false;
    }
}
