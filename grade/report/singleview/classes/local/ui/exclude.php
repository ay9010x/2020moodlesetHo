<?php



namespace gradereport_singleview\local\ui;

defined('MOODLE_INTERNAL') || die;

use grade_grade;


class exclude extends grade_attribute_format implements be_checked, be_disabled {

    
    public $name = 'exclude';

    
    public $disabled = false;

    
    public function is_checked() {
        return $this->grade->is_excluded();
    }

    
    public function is_disabled() {
        return $this->disabled;
    }

    
    public function determine_format() {
        return new checkbox_attribute(
            $this->get_name(),
            $this->get_label(),
            $this->is_checked(),
            $this->is_disabled()
        );
    }

    
    public function get_label() {
        if (!isset($this->grade->label)) {
            $this->grade->label = '';
        }
        return $this->grade->label;
    }

    
    public function set($value) {
        if (empty($this->grade->id)) {
            if (empty($value)) {
                return false;
            }

            $gradeitem = $this->grade->grade_item;

                        $gradeitem->update_final_grade(
                $this->grade->userid, null, 'singleview', null, FORMAT_MOODLE
            );

            $gradeparams = array(
                'userid' => $this->grade->userid,
                'itemid' => $this->grade->itemid
            );

            $this->grade = grade_grade::fetch($gradeparams);
            $this->grade->grade_item = $gradeitem;
        }

        $state = $value == 0 ? false : true;

        $this->grade->set_excluded($state);

        $this->grade->grade_item->get_parent_category()->force_regrading();
        return false;
    }
}
