<?php




global $CFG;
require_once("$CFG->libdir/form/selectgroups.php");
require_once("$CFG->libdir/questionlib.php");


class MoodleQuickForm_questioncategory extends MoodleQuickForm_selectgroups {
    
    var $_options = array('top'=>false, 'currentcat'=>0, 'nochildrenof' => -1);

    
    public function __construct($elementName = null, $elementLabel = null, $options = null, $attributes = null) {
        parent::__construct($elementName, $elementLabel, array(), $attributes);
        $this->_type = 'questioncategory';
        if (is_array($options)) {
            $this->_options = $options + $this->_options;
            $this->loadArrayOptGroups(
                        question_category_options($this->_options['contexts'], $this->_options['top'], $this->_options['currentcat'],
                                                false, $this->_options['nochildrenof']));
        }
    }

    
    public function MoodleQuickForm_questioncategory($elementName = null, $elementLabel = null, $options = null, $attributes = null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $options, $attributes);
    }
}
