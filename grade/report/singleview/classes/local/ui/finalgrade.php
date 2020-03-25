<?php



namespace gradereport_singleview\local\ui;

defined('MOODLE_INTERNAL') || die;

use stdClass;

class finalgrade extends grade_attribute_format implements unique_value, be_disabled {

    
    public $name = 'finalgrade';

    
    public function get_value() {
        $this->label = $this->grade->grade_item->itemname;

        $val = $this->grade->finalgrade;
        if ($this->grade->grade_item->scaleid) {
            return $val ? (int)$val : -1;
        } else {
            return $val ? format_float($val, $this->grade->grade_item->get_decimals()) : '';
        }
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
        if ($this->grade->grade_item->load_scale()) {
            $scale = $this->grade->grade_item->load_scale();

            $options = array(-1 => get_string('nograde'));

            foreach ($scale->scale_items as $i => $name) {
                $options[$i + 1] = $name;
            }

            return new dropdown_attribute(
                $this->get_name(),
                $options,
                $this->get_label(),
                $this->get_value(),
                $this->is_disabled()
            );
        } else {
            return new text_attribute(
                $this->get_name(),
                $this->get_value(),
                $this->get_label(),
                $this->is_disabled()
            );
        }
    }

    
    public function set($value) {
        global $DB;

        $userid = $this->grade->userid;
        $gradeitem = $this->grade->grade_item;

        $feedback = false;
        $feedbackformat = false;
        if ($gradeitem->gradetype == GRADE_TYPE_SCALE) {
            if ($value == -1) {
                $finalgrade = null;
            } else {
                $finalgrade = $value;
            }
        } else {
            $finalgrade = unformat_float($value);
        }

        $errorstr = '';
        if ($finalgrade) {
            $bounded = $gradeitem->bounded_grade($finalgrade);
            if ($bounded > $finalgrade) {
                $errorstr = 'lessthanmin';
            } else if ($bounded < $finalgrade) {
                $errorstr = 'morethanmax';
            }
        }

        if ($errorstr) {
            $user = $DB->get_record('user', array('id' => $userid), 'id, firstname, alternatename, lastname');
            $gradestr = new stdClass;
            if (!empty($user->alternatename)) {
                $gradestr->username = $user->alternatename . ' (' . $user->firstname . ') ' . $user->lastname;
            } else {
                $gradestr->username = $user->firstname . ' ' . $user->lastname;
            }
            $gradestr->itemname = $this->grade->grade_item->get_name();
            $errorstr = get_string($errorstr, 'grades', $gradestr);
        }

        $gradeitem->update_final_grade($userid, $finalgrade, 'singleview', $feedback, FORMAT_MOODLE);
        return $errorstr;
    }
}
