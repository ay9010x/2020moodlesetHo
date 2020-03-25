<?php




class mod_lesson_generator_testcase extends advanced_testcase {

    public function test_create_instance() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $this->assertFalse($DB->record_exists('lesson', array('course' => $course->id)));
        $lesson = $this->getDataGenerator()->create_module('lesson', array('course' => $course));
        $records = $DB->get_records('lesson', array('course' => $course->id), 'id');
        $this->assertEquals(1, count($records));
        $this->assertTrue(array_key_exists($lesson->id, $records));

        $params = array('course' => $course->id, 'name' => 'Another lesson');
        $lesson = $this->getDataGenerator()->create_module('lesson', $params);
        $records = $DB->get_records('lesson', array('course' => $course->id), 'id');
        $this->assertEquals(2, count($records));
        $this->assertEquals('Another lesson', $records[$lesson->id]->name);
    }

    public function test_create_content() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $lesson = $this->getDataGenerator()->create_module('lesson', array('course' => $course));
        $lessongenerator = $this->getDataGenerator()->get_plugin_generator('mod_lesson');

        $page1 = $lessongenerator->create_content($lesson);
        $page2 = $lessongenerator->create_content($lesson, array('title' => 'Custom title'));
        $records = $DB->get_records('lesson_pages', array('lessonid' => $lesson->id), 'id');
        $this->assertEquals(2, count($records));
        $this->assertEquals($page1->id, $records[$page1->id]->id);
        $this->assertEquals($page2->id, $records[$page2->id]->id);
        $this->assertEquals('Custom title', $records[$page2->id]->title);
    }

    
    public function test_create_question_truefalse() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $lesson = $this->getDataGenerator()->create_module('lesson', array('course' => $course));
        $lessongenerator = $this->getDataGenerator()->get_plugin_generator('mod_lesson');

        $page1 = $lessongenerator->create_question_truefalse($lesson);
        $page2 = $lessongenerator->create_question_truefalse($lesson, array('title' => 'Custom title'));
        $records = $DB->get_records('lesson_pages', array('lessonid' => $lesson->id), 'id');
        $p1answers = $DB->get_records('lesson_answers', array('lessonid' => $lesson->id, 'pageid' => $page1->id), 'id');
        $p2answers = $DB->get_records('lesson_answers', array('lessonid' => $lesson->id, 'pageid' => $page2->id), 'id');
        $this->assertCount(2, $records);
        $this->assertCount(2, $p1answers);         $this->assertCount(2, $p2answers);
        $this->assertEquals($page1->id, $records[$page1->id]->id);
        $this->assertEquals($page2->id, $records[$page2->id]->id);
        $this->assertEquals($page2->title, $records[$page2->id]->title);
    }

    
    public function test_create_question_multichoice() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $lesson = $this->getDataGenerator()->create_module('lesson', array('course' => $course));
        $lessongenerator = $this->getDataGenerator()->get_plugin_generator('mod_lesson');

        $page1 = $lessongenerator->create_question_multichoice($lesson);
        $page2 = $lessongenerator->create_question_multichoice($lesson, array('title' => 'Custom title'));
        $records = $DB->get_records('lesson_pages', array('lessonid' => $lesson->id), 'id');
        $p1answers = $DB->get_records('lesson_answers', array('lessonid' => $lesson->id, 'pageid' => $page1->id), 'id');
        $p2answers = $DB->get_records('lesson_answers', array('lessonid' => $lesson->id, 'pageid' => $page2->id), 'id');
        $this->assertCount(2, $records);
        $this->assertCount(2, $p1answers);         $this->assertCount(2, $p2answers);
        $this->assertEquals($page1->id, $records[$page1->id]->id);
        $this->assertEquals($page2->id, $records[$page2->id]->id);
        $this->assertEquals($page2->title, $records[$page2->id]->title);
    }

    
    public function test_create_question_essay() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $lesson = $this->getDataGenerator()->create_module('lesson', array('course' => $course));
        $lessongenerator = $this->getDataGenerator()->get_plugin_generator('mod_lesson');

        $page1 = $lessongenerator->create_question_essay($lesson);
        $page2 = $lessongenerator->create_question_essay($lesson, array('title' => 'Custom title'));
        $records = $DB->get_records('lesson_pages', array('lessonid' => $lesson->id), 'id');
        $p1answers = $DB->get_records('lesson_answers', array('lessonid' => $lesson->id, 'pageid' => $page1->id), 'id');
        $p2answers = $DB->get_records('lesson_answers', array('lessonid' => $lesson->id, 'pageid' => $page2->id), 'id');
        $this->assertCount(2, $records);
        $this->assertCount(1, $p1answers);         $this->assertCount(1, $p2answers);
        $this->assertEquals($page1->id, $records[$page1->id]->id);
        $this->assertEquals($page2->id, $records[$page2->id]->id);
        $this->assertEquals($page2->title, $records[$page2->id]->title);
    }

    
    public function test_create_question_matching() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $lesson = $this->getDataGenerator()->create_module('lesson', array('course' => $course));
        $lessongenerator = $this->getDataGenerator()->get_plugin_generator('mod_lesson');

        $page1 = $lessongenerator->create_question_matching($lesson);
        $page2 = $lessongenerator->create_question_matching($lesson, array('title' => 'Custom title'));
        $records = $DB->get_records('lesson_pages', array('lessonid' => $lesson->id), 'id');
        $p1answers = $DB->get_records('lesson_answers', array('lessonid' => $lesson->id, 'pageid' => $page1->id), 'id');
        $p2answers = $DB->get_records('lesson_answers', array('lessonid' => $lesson->id, 'pageid' => $page2->id), 'id');
        $this->assertCount(2, $records);
        $this->assertCount(4, $p1answers);         $this->assertCount(4, $p2answers);
        $this->assertEquals($page1->id, $records[$page1->id]->id);
        $this->assertEquals($page2->id, $records[$page2->id]->id);
        $this->assertEquals($page2->title, $records[$page2->id]->title);
    }

    
    public function test_create_question_numeric() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $lesson = $this->getDataGenerator()->create_module('lesson', array('course' => $course));
        $lessongenerator = $this->getDataGenerator()->get_plugin_generator('mod_lesson');

        $page1 = $lessongenerator->create_question_numeric($lesson);
        $page2 = $lessongenerator->create_question_numeric($lesson, array('title' => 'Custom title'));
        $records = $DB->get_records('lesson_pages', array('lessonid' => $lesson->id), 'id');
        $p1answers = $DB->get_records('lesson_answers', array('lessonid' => $lesson->id, 'pageid' => $page1->id), 'id');
        $p2answers = $DB->get_records('lesson_answers', array('lessonid' => $lesson->id, 'pageid' => $page2->id), 'id');
        $this->assertCount(2, $records);
        $this->assertCount(1, $p1answers);         $this->assertCount(1, $p2answers);
        $this->assertEquals($page1->id, $records[$page1->id]->id);
        $this->assertEquals($page2->id, $records[$page2->id]->id);
        $this->assertEquals($page2->title, $records[$page2->id]->title);
    }

    
    public function test_create_question_shortanswer() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $lesson = $this->getDataGenerator()->create_module('lesson', array('course' => $course));
        $lessongenerator = $this->getDataGenerator()->get_plugin_generator('mod_lesson');

        $page1 = $lessongenerator->create_question_shortanswer($lesson);
        $page2 = $lessongenerator->create_question_shortanswer($lesson, array('title' => 'Custom title'));
        $records = $DB->get_records('lesson_pages', array('lessonid' => $lesson->id), 'id');
        $p1answers = $DB->get_records('lesson_answers', array('lessonid' => $lesson->id, 'pageid' => $page1->id), 'id');
        $p2answers = $DB->get_records('lesson_answers', array('lessonid' => $lesson->id, 'pageid' => $page2->id), 'id');
        $this->assertCount(2, $records);
        $this->assertCount(1, $p1answers);         $this->assertCount(1, $p2answers);
        $this->assertEquals($page1->id, $records[$page1->id]->id);
        $this->assertEquals($page2->id, $records[$page2->id]->id);
        $this->assertEquals($page2->title, $records[$page2->id]->title);
    }
}
