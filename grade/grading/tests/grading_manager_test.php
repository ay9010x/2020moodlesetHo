<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/grade/grading/lib.php'); 


class testable_grading_manager extends grading_manager {
}



class core_grade_grading_manager_testcase extends advanced_testcase {
    public function test_basic_instantiation() {
        $manager1 = get_grading_manager();

        $fakecontext = (object)array(
            'id'            => 42,
            'contextlevel'  => CONTEXT_MODULE,
            'instanceid'    => 22,
            'path'          => '/1/3/15/42',
            'depth'         => 4);

        $manager2 = get_grading_manager($fakecontext);
        $manager3 = get_grading_manager($fakecontext, 'assignment_upload');
        $manager4 = get_grading_manager($fakecontext, 'assignment_upload', 'submission');
    }

    
    public function test_set_and_get_grading_area() {
        global $DB;

        $this->resetAfterTest(true);

                $areaname1 = 'area1-' . (string)microtime(true);
        $areaname2 = 'area2-' . (string)microtime(true);
        $fakecontext = (object)array(
            'id'            => 42,
            'contextlevel'  => CONTEXT_MODULE,
            'instanceid'    => 22,
            'path'          => '/1/3/15/42',
            'depth'         => 4);

                $gradingman = get_grading_manager($fakecontext, 'mod_foobar', $areaname1);
        $this->assertNull($gradingman->get_active_method());

                $this->assertTrue($gradingman->set_active_method('rubric'));
        $this->assertEquals('rubric', $gradingman->get_active_method());

                $this->assertFalse($gradingman->set_active_method('rubric'));

                $gradingman->set_area($areaname2);
        $this->assertNull($gradingman->get_active_method());

                $gradingman->set_area($areaname1);
        $this->assertEquals('rubric', $gradingman->get_active_method());

                $this->setExpectedException('moodle_exception');
        $gradingman->set_active_method('no_one_should_ever_try_to_implement_a_method_with_this_silly_name');
    }

    
    public function test_tokenize() {

        $UTFfailuremessage = 'A test using UTF-8 characters has failed. Consider updating PHP and PHP\'s PCRE or INTL extensions (MDL-30494)';

        $needle = "    šašek, \n\n   \r    a král;  \t";
        $tokens = testable_grading_manager::tokenize($needle);
        $this->assertEquals(2, count($tokens), $UTFfailuremessage);
        $this->assertTrue(in_array('šašek', $tokens), $UTFfailuremessage);
        $this->assertTrue(in_array('král', $tokens), $UTFfailuremessage);

        $needle = ' "   šašek a král "    ';
        $tokens = testable_grading_manager::tokenize($needle);
        $this->assertEquals(1, count($tokens));
        $this->assertTrue(in_array('šašek a král', $tokens));

        $needle = '""';
        $tokens = testable_grading_manager::tokenize($needle);
        $this->assertTrue(empty($tokens));

        $needle = '"0"';
        $tokens = testable_grading_manager::tokenize($needle);
        $this->assertEquals(1, count($tokens));
        $this->assertTrue(in_array('0', $tokens));

        $needle = '<span>Aha</span>, then who\'s a bad guy here he?';
        $tokens = testable_grading_manager::tokenize($needle);
        $this->assertEquals(8, count($tokens));
        $this->assertTrue(in_array('span', $tokens));         $this->assertTrue(in_array('Aha', $tokens));
        $this->assertTrue(in_array('who', $tokens));         $this->assertTrue(!in_array('a', $tokens));         $this->assertTrue(in_array('he', $tokens)); 
        $needle = 'grammar, "english language"';
        $tokens = testable_grading_manager::tokenize($needle);
        $this->assertTrue(in_array('grammar', $tokens));
        $this->assertTrue(in_array('english', $tokens));
        $this->assertTrue(in_array('language', $tokens));
        $this->assertTrue(!in_array('english language', $tokens));     }
}
