<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/mod/choice/lib.php');


class mod_choice_externallib_testcase extends externallib_advanced_testcase {

    
    public function test_get_choice_results() {
        global $DB;

        $this->resetAfterTest(true);

        $course = self::getDataGenerator()->create_course();
        $params = new stdClass();
        $params->course = $course->id;
        $params->option = array('fried rice', 'spring rolls', 'sweet and sour pork', 'satay beef', 'gyouza');
        $params->name = 'First Choice Activity';
        $params->showresults = CHOICE_SHOWRESULTS_AFTER_ANSWER;
        $params->publish = 1;
        $params->allowmultiple = 1;
        $params->showunanswered = 1;
        $choice = self::getDataGenerator()->create_module('choice', $params);

        $cm = get_coursemodule_from_id('choice', $choice->cmid);
        $choiceinstance = choice_get_choice($cm->instance);
        $options = array_keys($choiceinstance->option);
        $student1 = $this->getDataGenerator()->create_user();
        $student2 = $this->getDataGenerator()->create_user();
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));

                self::getDataGenerator()->enrol_user($student1->id,  $course->id, $studentrole->id);
        self::getDataGenerator()->enrol_user($student2->id,  $course->id, $studentrole->id);

        $this->setUser($student1);
        $myanswer = $options[2];
        choice_user_submit_response($myanswer, $choice, $student1->id, $course, $cm);
        $results = mod_choice_external::get_choice_results($choice->id);
                $results = external_api::clean_returnvalue(mod_choice_external::get_choice_results_returns(), $results);

                $resultsarr = array();
        foreach ($results['options'] as $option) {
            $resultsarr[$option['id']] = $option['userresponses'];
        }
                $this->assertEquals($resultsarr[$myanswer][0]['userid'], $student1->id);
                $this->assertEquals($resultsarr[0][0]['userid'], $student2->id);

                $this->setUser($student2);
        $results = mod_choice_external::get_choice_results($choice->id);
                $results = external_api::clean_returnvalue(mod_choice_external::get_choice_results_returns(), $results);
                foreach ($results['options'] as $option) {
            $this->assertCount(0, $option['userresponses']);
        }

        $timenow = time();
                $choice->showresults = CHOICE_SHOWRESULTS_AFTER_CLOSE;
                $choice->timeopen = $timenow - (60 * 60 * 24 * 3);
        $choice->timeclose = $timenow + (60 * 60 * 24 * 2);
        $DB->update_record('choice', $choice);

        $results = mod_choice_external::get_choice_results($choice->id);
                $results = external_api::clean_returnvalue(mod_choice_external::get_choice_results_returns(), $results);
                foreach ($results['options'] as $option) {
            $this->assertCount(0, $option['userresponses']);
        }

                $choice->timeclose = $timenow - (60 * 60 * 24 * 2);
        $DB->update_record('choice', $choice);
                $results = mod_choice_external::get_choice_results($choice->id);
                $results = external_api::clean_returnvalue(mod_choice_external::get_choice_results_returns(), $results);
                $resultsarr = array();
        foreach ($results['options'] as $option) {
            $resultsarr[$option['id']] = $option['userresponses'];
        }
                $this->assertEquals($resultsarr[$myanswer][0]['userid'], $student1->id);
                $this->assertEquals($resultsarr[0][0]['userid'], $student2->id);

                $choice->publish = 0;
        $DB->update_record('choice', $choice);
        $results = mod_choice_external::get_choice_results($choice->id);
                $results = external_api::clean_returnvalue(mod_choice_external::get_choice_results_returns(), $results);
                $resultsarr = array();
                foreach ($results['options'] as $option) {
            $this->assertCount(0, $option['userresponses']);
            $resultsarr[$option['id']] = $option;
        }
                $this->assertEquals(1, $resultsarr[$myanswer]['numberofuser']);
    }

    
    public function test_get_choice_options() {
        global $DB;

                $notopenyet = 1;
        $previewonly = 2;
        $expired = 3;

        $this->resetAfterTest(true);
        $timenow = time();
        $timeopen = $timenow + (60 * 60 * 24 * 2);
        $timeclose = $timenow + (60 * 60 * 24 * 7);
        $course = self::getDataGenerator()->create_course();
        $possibleoptions = array('fried rice', 'spring rolls', 'sweet and sour pork', 'satay beef', 'gyouza');
        $params = array();
        $params['course'] = $course->id;
        $params['option'] = $possibleoptions;
        $params['name'] = 'First Choice Activity';
        $params['showpreview'] = 0;

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_choice');
        $choice = $generator->create_instance($params);

        $student1 = $this->getDataGenerator()->create_user();
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
                self::getDataGenerator()->enrol_user($student1->id,  $course->id, $studentrole->id);
        $this->setUser($student1);

        $results = mod_choice_external::get_choice_options($choice->id);
                $results = external_api::clean_returnvalue(mod_choice_external::get_choice_options_returns(), $results);
                $this->assertCount(count($possibleoptions), $results['options']);

                $choice->timeopen = $timeopen;
        $choice->timeclose = $timeclose;
        $DB->update_record('choice', $choice);

        $results = mod_choice_external::get_choice_options($choice->id);
                $results = external_api::clean_returnvalue(mod_choice_external::get_choice_options_returns(), $results);
                $this->assertCount(0, $results['options']);
        $this->assertEquals($notopenyet, $results['warnings'][0]['warningcode']);

                $choice->showpreview = 1;
        $DB->update_record('choice', $choice);
        $results = mod_choice_external::get_choice_options($choice->id);
                $results = external_api::clean_returnvalue(mod_choice_external::get_choice_options_returns(), $results);
                $this->assertCount(count($possibleoptions), $results['options']);

        foreach ($results['options'] as $option) {
                        $this->assertEquals(1, $option['disabled']);
        }
        $warnings = array();
        foreach ($results['warnings'] as $warning) {
            $warnings[$warning['warningcode']] = $warning['message'];
        }
        $this->assertTrue(isset($warnings[$previewonly]));
        $this->assertTrue(isset($warnings[$notopenyet]));

                $choice->timeopen = $timenow - (60 * 60 * 24 * 3);
        $choice->timeclose = $timenow + (60 * 60 * 24 * 2);
        $DB->update_record('choice', $choice);
        $cm = get_coursemodule_from_id('choice', $choice->cmid);
        $choiceinstance = choice_get_choice($cm->instance);
        $optionsids = array_keys($choiceinstance->option);
        $myanswerid = $optionsids[2];
        choice_user_submit_response($myanswerid, $choice, $student1->id, $course, $cm);

        $results = mod_choice_external::get_choice_options($choice->id);
                $results = external_api::clean_returnvalue(mod_choice_external::get_choice_options_returns(), $results);
                $this->assertCount(count($possibleoptions), $results['options']);
        foreach ($results['options'] as $option) {
                        if ($option['id'] == $myanswerid and !$choice->allowupdate) {
                $this->assertEquals(1, $option['disabled']);
                $this->assertEquals(1, $option['checked']);
            } else {
                $this->assertEquals(0, $option['disabled']);
            }
        }

                        $choice->timeopen = $timenow - (60 * 60 * 24 * 3);
        $choice->timeclose = $timenow - (60 * 60 * 24 * 2);
        $DB->update_record('choice', $choice);
        $results = mod_choice_external::get_choice_options($choice->id);
                $results = external_api::clean_returnvalue(mod_choice_external::get_choice_options_returns(), $results);
                $this->assertCount(0, $results['options']);
        $this->assertEquals($expired, $results['warnings'][0]['warningcode']);

    }

    
    public function test_submit_choice_response() {
        global $DB;

        $this->resetAfterTest(true);

        $course = self::getDataGenerator()->create_course();
        $params = new stdClass();
        $params->course = $course->id;
        $params->option = array('fried rice', 'spring rolls', 'sweet and sour pork', 'satay beef', 'gyouza');
        $params->name = 'First Choice Activity';
        $params->showresults = CHOICE_SHOWRESULTS_ALWAYS;
        $params->allowmultiple = 1;
        $params->showunanswered = 1;
        $choice = self::getDataGenerator()->create_module('choice', $params);
        $cm = get_coursemodule_from_id('choice', $choice->cmid);
        $choiceinstance = choice_get_choice($cm->instance);
        $options = array_keys($choiceinstance->option);
        $student1 = $this->getDataGenerator()->create_user();
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));

                self::getDataGenerator()->enrol_user($student1->id,  $course->id, $studentrole->id);

        $this->setUser($student1);
        $myresponse = $options[2];
        $results = mod_choice_external::submit_choice_response($choice->id, array($myresponse));
                $results = external_api::clean_returnvalue(mod_choice_external::submit_choice_response_returns(), $results);
        $myanswers = $DB->get_records('choice_answers', array('choiceid' => $choice->id, 'userid' => $student1->id));
        $myanswer = reset($myanswers);
        $this->assertEquals($results['answers'][0]['id'], $myanswer->id);
        $this->assertEquals($results['answers'][0]['choiceid'], $myanswer->choiceid);
        $this->assertEquals($results['answers'][0]['userid'], $myanswer->userid);
        $this->assertEquals($results['answers'][0]['timemodified'], $myanswer->timemodified);
    }

    
    public function test_view_choice() {
        global $DB;

        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();
        $choice = $this->getDataGenerator()->create_module('choice', array('course' => $course->id));
        $context = context_module::instance($choice->cmid);
        $cm = get_coursemodule_from_instance('choice', $choice->id);

                try {
            mod_choice_external::view_choice(0);
            $this->fail('Exception expected due to invalid mod_choice instance id.');
        } catch (moodle_exception $e) {
            $this->assertEquals('invalidcoursemodule', $e->errorcode);
        }

                $user = self::getDataGenerator()->create_user();
        $this->setUser($user);
        try {
            mod_choice_external::view_choice($choice->id);
            $this->fail('Exception expected due to not enrolled user.');
        } catch (moodle_exception $e) {
            $this->assertEquals('requireloginerror', $e->errorcode);
        }

                $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $studentrole->id);

                $sink = $this->redirectEvents();

        $result = mod_choice_external::view_choice($choice->id);
        $result = external_api::clean_returnvalue(mod_choice_external::view_choice_returns(), $result);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_shift($events);

                $this->assertInstanceOf('\mod_choice\event\course_module_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $moodlechoice = new \moodle_url('/mod/choice/view.php', array('id' => $cm->id));
        $this->assertEquals($moodlechoice, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

    }

    
    public function test_get_choices_by_courses() {
        global $DB;
        $this->resetAfterTest(true);
                $this->setAdminUser();
        $course1 = self::getDataGenerator()->create_course();
        $choiceoptions1 = array(
          'course' => $course1->id,
          'name' => 'First IMSCP'
        );
        $choice1 = self::getDataGenerator()->create_module('choice', $choiceoptions1);
        $course2 = self::getDataGenerator()->create_course();

        $choiceoptions2 = array(
          'course' => $course2->id,
          'name' => 'Second IMSCP'
        );
        $choice2 = self::getDataGenerator()->create_module('choice', $choiceoptions2);
        $student1 = $this->getDataGenerator()->create_user();

        $studentrole = $DB->get_record('role', array('shortname' => 'student'));

                self::getDataGenerator()->enrol_user($student1->id,  $course1->id, $studentrole->id);

        $this->setUser($student1);
        $choices = mod_choice_external::get_choices_by_courses(array());
        $choices = external_api::clean_returnvalue(mod_choice_external::get_choices_by_courses_returns(), $choices);
        $this->assertCount(1, $choices['choices']);
        $this->assertEquals('First IMSCP', $choices['choices'][0]['name']);
                $this->assertFalse(isset($choices['choices'][0]['section']));

                        $choices = mod_choice_external::get_choices_by_courses(array($course2->id));
        $choices = external_api::clean_returnvalue(mod_choice_external::get_choices_by_courses_returns(), $choices);
        $this->assertCount(0, $choices['choices']);
        $this->assertEquals(1, $choices['warnings'][0]['warningcode']);

                $this->setAdminUser();
                $choices = mod_choice_external::get_choices_by_courses(array($course2->id));
        $choices = external_api::clean_returnvalue(mod_choice_external::get_choices_by_courses_returns(), $choices);
        $this->assertCount(1, $choices['choices']);
        $this->assertEquals('Second IMSCP', $choices['choices'][0]['name']);
                $this->assertEquals(0, $choices['choices'][0]['section']);

                $this->setUser($student1);
        $contextcourse1 = context_course::instance($course1->id);
                assign_capability('mod/choice:choose', CAP_PROHIBIT, $studentrole->id, $contextcourse1->id);
        accesslib_clear_all_caches_for_unit_testing();

        $choices = mod_choice_external::get_choices_by_courses(array($course1->id));
        $choices = external_api::clean_returnvalue(mod_choice_external::get_choices_by_courses_returns(), $choices);
        $this->assertFalse(isset($choices['choices'][0]['timeopen']));
    }

    
    public function test_delete_choice_responses() {
        global $DB;

        $this->resetAfterTest(true);

        $course = self::getDataGenerator()->create_course();
        $params = new stdClass();
        $params->course = $course->id;
        $params->option = array('fried rice', 'spring rolls', 'sweet and sour pork', 'satay beef', 'gyouza');
        $params->name = 'First Choice Activity';
        $params->showresults = CHOICE_SHOWRESULTS_ALWAYS;
        $params->allowmultiple = 1;
        $params->showunanswered = 1;
        $choice = self::getDataGenerator()->create_module('choice', $params);
        $cm = get_coursemodule_from_id('choice', $choice->cmid);

        $choiceinstance = choice_get_choice($cm->instance);
        $options = array_keys($choiceinstance->option);

        $student = $this->getDataGenerator()->create_user();
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));

                self::getDataGenerator()->enrol_user($student->id,  $course->id, $studentrole->id);

        $this->setUser($student);
        $results = mod_choice_external::submit_choice_response($choice->id, array($options[1], $options[2]));
        $results = external_api::clean_returnvalue(mod_choice_external::submit_choice_response_returns(), $results);

        $myresponses = array_keys(choice_get_my_response($choice));

                try {
            mod_choice_external::delete_choice_responses($choice->id, array($myresponses[0], $myresponses[0]));
            $this->fail('Exception expected due to missing permissions.');
        } catch (required_capability_exception $e) {
            $this->assertEquals('nopermissions', $e->errorcode);
        }

                $DB->set_field('choice', 'allowupdate', 1, array('id' => $choice->id));
        $DB->set_field('choice', 'timeclose', time() - DAYSECS, array('id' => $choice->id));
        try {
            mod_choice_external::delete_choice_responses($choice->id, array($myresponses[0], $myresponses[1]));
            $this->fail('Exception expected due to expired choice.');
        } catch (moodle_exception $e) {
            $this->assertEquals('expired', $e->errorcode);
        }

                $DB->set_field('choice', 'timeclose', 0, array('id' => $choice->id));
        $results = mod_choice_external::delete_choice_responses($choice->id, array($myresponses[0], $myresponses[1]));
        $results = external_api::clean_returnvalue(mod_choice_external::delete_choice_responses_returns(), $results);

        $this->assertTrue($results['status']);
        $this->assertCount(0, $results['warnings']);
                $this->assertCount(0, choice_get_my_response($choice));

                $results = mod_choice_external::submit_choice_response($choice->id, array($options[1], $options[2]));
        $results = external_api::clean_returnvalue(mod_choice_external::submit_choice_response_returns(), $results);

        $myresponses = array_keys(choice_get_my_response($choice));
                $results = mod_choice_external::delete_choice_responses($choice->id, array($myresponses[0]));
        $results = external_api::clean_returnvalue(mod_choice_external::delete_choice_responses_returns(), $results);
        $this->assertTrue($results['status']);
        $this->assertCount(0, $results['warnings']);
                $this->assertCount(1, choice_get_my_response($choice));

                $results = mod_choice_external::delete_choice_responses($choice->id, array($myresponses[1], $myresponses[0] + 2,
                                                                $myresponses[0] + 3));
        $results = external_api::clean_returnvalue(mod_choice_external::delete_choice_responses_returns(), $results);
        $this->assertTrue($results['status']);
                $this->assertCount(2, $results['warnings']);
                $this->assertCount(0, choice_get_my_response($choice));

                        $results = mod_choice_external::submit_choice_response($choice->id, array($options[1], $options[2]));
        $results = external_api::clean_returnvalue(mod_choice_external::submit_choice_response_returns(), $results);
        $studentresponses = array_keys(choice_get_my_response($choice));

        $this->setAdminUser();
        $DB->set_field('choice', 'allowupdate', 0, array('id' => $choice->id));
        $DB->set_field('choice', 'timeclose', time() - DAYSECS, array('id' => $choice->id));

        $results = mod_choice_external::delete_choice_responses($choice->id, array($studentresponses[0], $studentresponses[1]));
        $results = external_api::clean_returnvalue(mod_choice_external::delete_choice_responses_returns(), $results);

        $this->assertTrue($results['status']);
        $this->assertCount(0, $results['warnings']);

                $DB->set_field('choice', 'timeclose', 0, array('id' => $choice->id));
        $results = mod_choice_external::submit_choice_response($choice->id, array($options[1], $options[2]));
        $results = external_api::clean_returnvalue(mod_choice_external::submit_choice_response_returns(), $results);
                choice_user_submit_response( array($options[1], $options[2]), $choice, $student->id, $course, $cm);

                $results = mod_choice_external::delete_choice_responses($choice->id);
        $results = external_api::clean_returnvalue(mod_choice_external::delete_choice_responses_returns(), $results);

        $this->assertTrue($results['status']);
        $this->assertCount(0, $results['warnings']);
        $this->assertCount(0, choice_get_all_responses($choice));

                $this->setUser($student);

                $DB->set_field('choice', 'allowupdate', 1, array('id' => $choice->id));
        $DB->set_field('choice', 'timeclose', 0, array('id' => $choice->id));
        $results = mod_choice_external::submit_choice_response($choice->id, array($options[1], $options[2]));
        $results = external_api::clean_returnvalue(mod_choice_external::submit_choice_response_returns(), $results);

                $results = mod_choice_external::delete_choice_responses($choice->id);
        $results = external_api::clean_returnvalue(mod_choice_external::delete_choice_responses_returns(), $results);

        $this->assertTrue($results['status']);
        $this->assertCount(0, $results['warnings']);
        $this->assertCount(0, choice_get_my_response($choice));

    }
}
