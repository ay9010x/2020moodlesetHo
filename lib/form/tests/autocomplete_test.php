<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/form/autocomplete.php');


class core_form_autocomplete_testcase extends basic_testcase {
    
    public function test_validation() {
                $options = array('1' => 'One', 2 => 'Two');
        $element = new MoodleQuickForm_autocomplete('testel', null, $options);
        $submission = array('testel' => 2);
        $this->assertEquals($element->exportValue($submission), 2);
        $submission = array('testel' => 3);
        $this->assertNull($element->exportValue($submission));

                $options = array('1' => 'One', 2 => 'Two');
        $element = new MoodleQuickForm_autocomplete('testel', null, $options, array('multiple'=>'multiple'));
        $submission = array('testel' => array(2, 3));
        $this->assertEquals($element->exportValue($submission), array(2));

                $element = new MoodleQuickForm_autocomplete('testel', null, array(), array('multiple'=>'multiple', 'ajax'=>'anything'));
        $submission = array('testel' => array(2, 3));
        $this->assertEquals($element->exportValue($submission), array(2, 3));
    }

}
