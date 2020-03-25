<?php




if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    }

global $CFG;
require_once($CFG->libdir.'/form/password.php');


class MoodleQuickForm_passwordunmask extends MoodleQuickForm_password {
    
    public function __construct($elementName=null, $elementLabel=null, $attributes=null) {
        global $CFG;
                if (empty($attributes)) {
            $attributes = array('autocomplete'=>'off');
        } else if (is_array($attributes)) {
            $attributes['autocomplete'] = 'off';
        } else {
            if (strpos($attributes, 'autocomplete') === false) {
                $attributes .= ' autocomplete="off" ';
            }
        }

        parent::__construct($elementName, $elementLabel, $attributes);
    }

    
    public function MoodleQuickForm_passwordunmask($elementName=null, $elementLabel=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $attributes);
    }

    
    function toHtml() {
        global $PAGE;

        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        } else {
            $unmask = get_string('unmaskpassword', 'form');
                        $attributes = array('formid' => $this->getAttribute('id'),
                'checkboxlabel' => $unmask,
                'checkboxname' => $this->getAttribute('name'));
            $PAGE->requires->yui_module('moodle-form-passwordunmask', 'M.form.passwordunmask',
                    array($attributes));
            return $this->_getTabs() . '<input' . $this->_getAttrString($this->_attributes) . ' />';
        }
    }

}
