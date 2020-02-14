<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');


class core_questionlib_testcase extends advanced_testcase {

    
    public function setUp() {
        $this->resetAfterTest();
    }

    
    public function provider_feedback() {
        return array(
            'Feedback test' => array(true),
            'No feedback test' => array(false)
        );
    }

    
    public function setup_quiz_and_questions($type = 'module') {
                $category = $this->getDataGenerator()->create_category();

                $course = $this->getDataGenerator()->create_course(array('numsections' => 5));

        $options = array(
            'course' => $course->id,
            'duedate' => time(),
        );

                $quiz = $this->getDataGenerator()->create_module('quiz', $options);

        $qgen = $this->getDataGenerator()->get_plugin_generator('core_question');

        if ('course' == $type) {
            $context = context_course::instance($course->id);
        } else if ('category' == $type) {
            $context = context_coursecat::instance($category->id);
        } else {
            $context = context_module::instance($quiz->cmid);
        }

        $qcat = $qgen->create_question_category(array('contextid' => $context->id));

        $questions = array(
                $qgen->create_question('shortanswer', null, array('category' => $qcat->id)),
                $qgen->create_question('shortanswer', null, array('category' => $qcat->id)),
        );

        quiz_add_quiz_question($questions[0]->id, $quiz);

        return array($category, $course, $quiz, $qcat, $questions);
    }

    public function test_question_reorder_qtypes() {
        $this->assertEquals(
            array(0 => 't2', 1 => 't1', 2 => 't3'),
            question_reorder_qtypes(array('t1' => '', 't2' => '', 't3' => ''), 't1', +1));
        $this->assertEquals(
            array(0 => 't1', 1 => 't2', 2 => 't3'),
            question_reorder_qtypes(array('t1' => '', 't2' => '', 't3' => ''), 't1', -1));
        $this->assertEquals(
            array(0 => 't2', 1 => 't1', 2 => 't3'),
            question_reorder_qtypes(array('t1' => '', 't2' => '', 't3' => ''), 't2', -1));
        $this->assertEquals(
            array(0 => 't1', 1 => 't2', 2 => 't3'),
            question_reorder_qtypes(array('t1' => '', 't2' => '', 't3' => ''), 't3', +1));
        $this->assertEquals(
            array(0 => 't1', 1 => 't2', 2 => 't3'),
            question_reorder_qtypes(array('t1' => '', 't2' => '', 't3' => ''), 'missing', +1));
    }

    public function test_match_grade_options() {
        $gradeoptions = question_bank::fraction_options_full();

        $this->assertEquals(0.3333333, match_grade_options($gradeoptions, 0.3333333, 'error'));
        $this->assertEquals(0.3333333, match_grade_options($gradeoptions, 0.333333, 'error'));
        $this->assertEquals(0.3333333, match_grade_options($gradeoptions, 0.33333, 'error'));
        $this->assertFalse(match_grade_options($gradeoptions, 0.3333, 'error'));

        $this->assertEquals(0.3333333, match_grade_options($gradeoptions, 0.3333333, 'nearest'));
        $this->assertEquals(0.3333333, match_grade_options($gradeoptions, 0.333333, 'nearest'));
        $this->assertEquals(0.3333333, match_grade_options($gradeoptions, 0.33333, 'nearest'));
        $this->assertEquals(0.3333333, match_grade_options($gradeoptions, 0.33, 'nearest'));

        $this->assertEquals(-0.1428571, match_grade_options($gradeoptions, -0.15, 'nearest'));
    }

    
    public function test_altering_tag_instance_context() {
        global $CFG, $DB;

                $this->setAdminUser();

                        $coursecat1 = $this->getDataGenerator()->create_category();
        $coursecat2 = $this->getDataGenerator()->create_category();

                $context1 = context_coursecat::instance($coursecat1->id);
        $context2 = context_coursecat::instance($coursecat2->id);
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $questioncat1 = $questiongenerator->create_question_category(array('contextid' =>
            $context1->id));
        $questioncat2 = $questiongenerator->create_question_category(array('contextid' =>
            $context2->id));
        $question1 = $questiongenerator->create_question('shortanswer', null, array('category' => $questioncat1->id));
        $question2 = $questiongenerator->create_question('shortanswer', null, array('category' => $questioncat1->id));
        $question3 = $questiongenerator->create_question('shortanswer', null, array('category' => $questioncat2->id));
        $question4 = $questiongenerator->create_question('shortanswer', null, array('category' => $questioncat2->id));

                core_tag_tag::set_item_tags('core_question', 'question', $question1->id, $context1, array('tag 1', 'tag 2'));
        core_tag_tag::set_item_tags('core_question', 'question', $question2->id, $context1, array('tag 3', 'tag 4'));
        core_tag_tag::set_item_tags('core_question', 'question', $question3->id, $context2, array('tag 5', 'tag 6'));
        core_tag_tag::set_item_tags('core_question', 'question', $question4->id, $context2, array('tag 7', 'tag 8'));

                question_move_questions_to_category(array($question1->id, $question2->id), $questioncat2->id);

                $this->assertEquals(8, $DB->count_records('tag_instance', array('component' => 'core_question',
            'contextid' => $questioncat2->contextid)));

                question_move_questions_to_category(array($question1->id, $question2->id), $questioncat1->id);

                $this->assertEquals(4, $DB->count_records('tag_instance', array('component' => 'core_question',
            'contextid' => $questioncat1->contextid)));
        $this->assertEquals(4, $DB->count_records('tag_instance', array('component' => 'core_question',
            'contextid' => $questioncat2->contextid)));

                question_move_category_to_context($questioncat1->id, $questioncat1->contextid, $questioncat2->contextid);

                $this->assertEquals(8, $DB->count_records('tag_instance', array('component' => 'core_question',
            'contextid' => $questioncat2->contextid)));

                question_move_category_to_context($questioncat1->id, $questioncat2->contextid,
            context_coursecat::instance($coursecat1->id)->id);

                $this->assertEquals(4, $DB->count_records('tag_instance', array('component' => 'core_question',
            'contextid' => $questioncat1->contextid)));
        $this->assertEquals(4, $DB->count_records('tag_instance', array('component' => 'core_question',
            'contextid' => $questioncat2->contextid)));

                question_delete_course_category($coursecat1, $coursecat2, false);

                $this->assertEquals(8, $DB->count_records('tag_instance', array('component' => 'core_question',
            'contextid' => $questioncat2->contextid)));

                $course = $this->getDataGenerator()->create_course();

                $coursecontext = context_course::instance($course->id);
        $questioncat = $questiongenerator->create_question_category(array('contextid' =>
            $coursecontext->id));
        $question1 = $questiongenerator->create_question('shortanswer', null, array('category' => $questioncat->id));
        $question2 = $questiongenerator->create_question('shortanswer', null, array('category' => $questioncat->id));

                core_tag_tag::set_item_tags('core_question', 'question', $question1->id, $coursecontext, array('tag 1', 'tag 2'));
        core_tag_tag::set_item_tags('core_question', 'question', $question2->id, $coursecontext, array('tag 1', 'tag 2'));

                $course2 = $this->getDataGenerator()->create_course();

                $bc = new backup_controller(backup::TYPE_1COURSE, $course->id, backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO, backup::MODE_GENERAL, 2);
        $bc->execute_plan();
        $results = $bc->get_results();
        $file = $results['backup_destination'];
        $fp = get_file_packer('application/vnd.moodle.backup');
        $filepath = $CFG->dataroot . '/temp/backup/test-restore-course';
        $file->extract_to_pathname($fp, $filepath);
        $bc->destroy();

                $rc = new restore_controller('test-restore-course', $course2->id, backup::INTERACTIVE_NO,
            backup::MODE_GENERAL, 2, backup::TARGET_NEW_COURSE);
        $rc->execute_precheck();
        $rc->execute_plan();

                $restoredcategory = $DB->get_record('question_categories', array('contextid' => context_course::instance($course2->id)->id),
            '*', MUST_EXIST);

                $this->assertEquals(2, $DB->count_records('question', array('category' => $restoredcategory->id)));

        $rc->destroy();
    }

    
    public function test_question_category_delete_safe() {
        global $DB;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        list($category, $course, $quiz, $qcat, $questions) = $this->setup_quiz_and_questions();

        question_category_delete_safe($qcat);

                $criteria = array('id' => $qcat->id);
        $this->assertEquals(0, $DB->count_records('question_categories', $criteria));

                $criteria = array('category' => $qcat->id);
        $this->assertEquals(0, $DB->count_records('question', $criteria));

                $criteria = array('id' => $questions[0]->id);
        $this->assertEquals(1, $DB->count_records('question', $criteria));
    }

    
    public function test_question_delete_activity($feedback) {
        global $DB;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        list($category, $course, $quiz, $qcat, $questions) = $this->setup_quiz_and_questions();

        $cm = get_coursemodule_from_instance('quiz', $quiz->id);
                if ($feedback) {
            $this->expectOutputRegex('|'.get_string('unusedcategorydeleted', 'question').'|');
        }
        question_delete_activity($cm, $feedback);

                $criteria = array('id' => $qcat->id);
        $this->assertEquals(0, $DB->count_records('question_categories', $criteria));

                $criteria = array('category' => $qcat->id);
        $this->assertEquals(0, $DB->count_records('question', $criteria));
    }

    
    public function test_question_delete_context() {
        global $DB;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        list($category, $course, $quiz, $qcat, $questions) = $this->setup_quiz_and_questions();

                $result = question_delete_context($qcat->contextid);

                $criteria = array('id' => $qcat->id);
        $this->assertEquals(0, $DB->count_records('question_categories', $criteria));

                $criteria = array('category' => $qcat->id);
        $this->assertEquals(0, $DB->count_records('question', $criteria));

                $expected[] = array($qcat->name, get_string('unusedcategorydeleted', 'question'));
        $this->assertEquals($expected, $result);
    }

    
    public function test_question_delete_course($feedback) {
        global $DB;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        list($category, $course, $quiz, $qcat, $questions) = $this->setup_quiz_and_questions('course');

                if ($feedback) {
            $this->expectOutputRegex('|'.get_string('unusedcategorydeleted', 'question').'|');
        }
        question_delete_course($course, $feedback);

                $criteria = array('id' => $qcat->id);
        $this->assertEquals(0, $DB->count_records('question_categories', $criteria));

                $criteria = array('category' => $qcat->id);
        $this->assertEquals(0, $DB->count_records('question', $criteria));
    }

    
    public function test_question_delete_course_category($feedback) {
        global $DB;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        list($category, $course, $quiz, $qcat, $questions) = $this->setup_quiz_and_questions('category');

                if ($feedback) {
            $this->expectOutputRegex('|'.get_string('unusedcategorydeleted', 'question').'|');
        }
        question_delete_course_category($category, 0, $feedback);

                $criteria = array('id' => $qcat->id);
        $this->assertEquals(0, $DB->count_records('question_categories', $criteria));

                $criteria = array('category' => $qcat->id);
        $this->assertEquals(0, $DB->count_records('question', $criteria));
    }

    public function test_question_remove_stale_questions_from_category() {
        global $DB;
        $this->resetAfterTest(true);
        $dg = $this->getDataGenerator();
        $course = $dg->create_course();
        $quiz = $dg->create_module('quiz', ['course' => $course->id]);

        $qgen = $dg->get_plugin_generator('core_question');
        $context = context_system::instance();

        $qcat1 = $qgen->create_question_category(['contextid' => $context->id]);
        $q1a = $qgen->create_question('shortanswer', null, ['category' => $qcat1->id]);             $q1b = $qgen->create_question('random', null, ['category' => $qcat1->id]);                  $DB->set_field('question', 'hidden', 1, ['id' => $q1a->id]);

        $qcat2 = $qgen->create_question_category(['contextid' => $context->id]);
        $q2a = $qgen->create_question('shortanswer', null, ['category' => $qcat2->id]);             $q2b = $qgen->create_question('shortanswer', null, ['category' => $qcat2->id]);             $q2c = $qgen->create_question('random', null, ['category' => $qcat2->id]);                  $q2d = $qgen->create_question('random', null, ['category' => $qcat2->id]);                  $DB->set_field('question', 'hidden', 1, ['id' => $q2a->id]);
        $DB->set_field('question', 'hidden', 1, ['id' => $q2b->id]);
        quiz_add_quiz_question($q2b->id, $quiz);
        quiz_add_quiz_question($q2d->id, $quiz);

        $this->assertEquals(2, $DB->count_records('question', ['category' => $qcat1->id]));
        $this->assertEquals(4, $DB->count_records('question', ['category' => $qcat2->id]));

                question_remove_stale_questions_from_category(0);
        $this->assertEquals(2, $DB->count_records('question', ['category' => $qcat1->id]));
        $this->assertEquals(4, $DB->count_records('question', ['category' => $qcat2->id]));

                question_remove_stale_questions_from_category($qcat1->id);
        $this->assertEquals(0, $DB->count_records('question', ['category' => $qcat1->id]));
        $this->assertEquals(4, $DB->count_records('question', ['category' => $qcat2->id]));
        $this->assertFalse($DB->record_exists('question', ['id' => $q1a->id]));
        $this->assertFalse($DB->record_exists('question', ['id' => $q1b->id]));

                question_remove_stale_questions_from_category($qcat2->id);
        $this->assertEquals(0, $DB->count_records('question', ['category' => $qcat1->id]));
        $this->assertEquals(2, $DB->count_records('question', ['category' => $qcat2->id]));
        $this->assertFalse($DB->record_exists('question', ['id' => $q2a->id]));
        $this->assertTrue($DB->record_exists('question', ['id' => $q2b->id]));
        $this->assertFalse($DB->record_exists('question', ['id' => $q2c->id]));
        $this->assertTrue($DB->record_exists('question', ['id' => $q2d->id]));
    }
}
