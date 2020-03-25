<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/pagelib.php');
require_once($CFG->libdir . '/blocklib.php');


class core_moodle_page_testcase extends advanced_testcase {

    
    protected $testpage;

    public function setUp() {
        parent::setUp();
        $this->resetAfterTest();
        $this->testpage = new testable_moodle_page();
    }

    public function test_course_returns_site_before_set() {
        global $SITE;
                $this->assertSame($SITE, $this->testpage->course);
    }

    public function test_setting_course_works() {
                $course = $this->getDataGenerator()->create_course();
        $this->testpage->set_context(context_system::instance());                 $this->testpage->set_course($course);
                $this->assertEquals($course, $this->testpage->course);
    }

    public function test_global_course_and_page_course_are_same_with_global_page() {
        global $COURSE, $PAGE;
                $course = $this->getDataGenerator()->create_course();
        $this->testpage->set_context(context_system::instance());         $PAGE = $this->testpage;
                $this->testpage->set_course($course);
                $this->assertSame($COURSE, $this->testpage->course);
    }

    public function test_global_course_not_changed_with_non_global_page() {
        global $COURSE;
        $originalcourse = $COURSE;
                $course = $this->getDataGenerator()->create_course();
        $this->testpage->set_context(context_system::instance());                 $this->testpage->set_course($course);
                $this->assertSame($originalcourse, $COURSE);
    }

    public function test_cannot_set_course_once_theme_set() {
                $this->testpage->force_theme(theme_config::DEFAULT_THEME);
        $course = $this->getDataGenerator()->create_course();
                $this->setExpectedException('coding_exception');
                $this->testpage->set_course($course);
    }

    public function test_cannot_set_category_once_theme_set() {
                $this->testpage->force_theme(theme_config::DEFAULT_THEME);
                $this->setExpectedException('coding_exception');
                $this->testpage->set_category_by_id(123);
    }

    public function test_cannot_set_category_once_course_set() {
                $course = $this->getDataGenerator()->create_course();
        $this->testpage->set_context(context_system::instance());         $this->testpage->set_course($course);
                $this->setExpectedException('coding_exception');
                $this->testpage->set_category_by_id(123);
    }

    public function test_categories_array_empty_for_front_page() {
        global $SITE;
                $this->testpage->set_context(context_system::instance());         $this->testpage->set_course($SITE);
                $this->assertEquals(array(), $this->testpage->categories);
    }

    public function test_set_state_normal_path() {
        $course = $this->getDataGenerator()->create_course();
        $this->testpage->set_context(context_system::instance());
        $this->testpage->set_course($course);

        $this->assertEquals(moodle_page::STATE_BEFORE_HEADER, $this->testpage->state);

        $this->testpage->set_state(moodle_page::STATE_PRINTING_HEADER);
        $this->assertEquals(moodle_page::STATE_PRINTING_HEADER, $this->testpage->state);

        $this->testpage->set_state(moodle_page::STATE_IN_BODY);
        $this->assertEquals(moodle_page::STATE_IN_BODY, $this->testpage->state);

        $this->testpage->set_state(moodle_page::STATE_DONE);
        $this->assertEquals(moodle_page::STATE_DONE, $this->testpage->state);
    }

    public function test_set_state_cannot_skip_one() {
                $this->setExpectedException('coding_exception');
                $this->testpage->set_state(moodle_page::STATE_IN_BODY);
    }

    public function test_header_printed_false_initially() {
                $this->assertFalse($this->testpage->headerprinted);
    }

    public function test_header_printed_becomes_true() {
        $course = $this->getDataGenerator()->create_course();
        $this->testpage->set_context(context_system::instance());
        $this->testpage->set_course($course);

                $this->testpage->set_state(moodle_page::STATE_PRINTING_HEADER);
        $this->testpage->set_state(moodle_page::STATE_IN_BODY);
                $this->assertTrue($this->testpage->headerprinted);
    }

    public function test_set_context() {
                $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);
                $this->testpage->set_context($context);
                $this->assertSame($context, $this->testpage->context);
    }

    public function test_pagetype_defaults_to_script() {
        global $SCRIPT;
                $SCRIPT = '/index.php';
        $this->testpage->initialise_default_pagetype();
        $this->assertSame('site-index', $this->testpage->pagetype);
    }

    public function test_set_pagetype() {
                $this->testpage->set_pagetype('a-page-type');
                $this->assertSame('a-page-type', $this->testpage->pagetype);
    }

    public function test_initialise_default_pagetype() {
                $this->testpage->initialise_default_pagetype('admin/tool/unittest/index.php');
                $this->assertSame('admin-tool-unittest-index', $this->testpage->pagetype);
    }

    public function test_initialise_default_pagetype_fp() {
                $this->testpage->initialise_default_pagetype('index.php');
                $this->assertSame('site-index', $this->testpage->pagetype);
    }

    public function test_get_body_classes_empty() {
                $this->assertSame('', $this->testpage->bodyclasses);
    }

    public function test_get_body_classes_single() {
                $this->testpage->add_body_class('aclassname');
                $this->assertSame('aclassname', $this->testpage->bodyclasses);
    }

    public function test_get_body_classes() {
                $this->testpage->add_body_classes(array('aclassname', 'anotherclassname'));
                $this->assertSame('aclassname anotherclassname', $this->testpage->bodyclasses);
    }

    public function test_url_to_class_name() {
        $this->assertSame('example-com', $this->testpage->url_to_class_name('http://example.com'));
        $this->assertSame('example-com--80', $this->testpage->url_to_class_name('http://example.com:80'));
        $this->assertSame('example-com--moodle', $this->testpage->url_to_class_name('https://example.com/moodle'));
        $this->assertSame('example-com--8080--nested-moodle', $this->testpage->url_to_class_name('https://example.com:8080/nested/moodle'));
    }

    public function test_set_docs_path() {
                $this->testpage->set_docs_path('a/file/path');
                $this->assertSame('a/file/path', $this->testpage->docspath);
    }

    public function test_docs_path_defaults_from_pagetype() {
                $this->testpage->set_pagetype('a-page-type');
                $this->assertSame('a/page/type', $this->testpage->docspath);
    }

    public function test_set_url_root() {
        global $CFG;
                $this->testpage->set_url('/');
                $this->assertSame($CFG->wwwroot . '/', $this->testpage->url->out());
    }

    public function test_set_url_one_param() {
        global $CFG;
                $this->testpage->set_url('/mod/quiz/attempt.php', array('attempt' => 123));
                $this->assertSame($CFG->wwwroot . '/mod/quiz/attempt.php?attempt=123', $this->testpage->url->out());
    }

    public function test_set_url_two_params() {
        global $CFG;
                $this->testpage->set_url('/mod/quiz/attempt.php', array('attempt' => 123, 'page' => 7));
                $this->assertSame($CFG->wwwroot . '/mod/quiz/attempt.php?attempt=123&amp;page=7', $this->testpage->url->out());
    }

    public function test_set_url_using_moodle_url() {
        global $CFG;
                $url = new moodle_url('/mod/workshop/allocation.php', array('cmid' => 29, 'method' => 'manual'));
                $this->testpage->set_url($url);
                $this->assertSame($CFG->wwwroot . '/mod/workshop/allocation.php?cmid=29&amp;method=manual', $this->testpage->url->out());
    }

    public function test_set_url_sets_page_type() {
                $this->testpage->set_url('/mod/quiz/attempt.php', array('attempt' => 123, 'page' => 7));
                $this->assertSame('mod-quiz-attempt', $this->testpage->pagetype);
    }

    public function test_set_url_does_not_change_explicit_page_type() {
                $this->testpage->set_pagetype('a-page-type');
                $this->testpage->set_url('/mod/quiz/attempt.php', array('attempt' => 123, 'page' => 7));
                $this->assertSame('a-page-type', $this->testpage->pagetype);
    }

    public function test_set_subpage() {
                $this->testpage->set_subpage('somestring');
                $this->assertSame('somestring', $this->testpage->subpage);
    }

    public function test_set_heading() {
                $this->testpage->set_heading('a heading');
                $this->assertSame('a heading', $this->testpage->heading);
    }

    public function test_set_title() {
                $this->testpage->set_title('a title');
                $this->assertSame('a title', $this->testpage->title);
    }

    public function test_default_pagelayout() {
                $this->assertSame('base', $this->testpage->pagelayout);
    }

    public function test_set_pagelayout() {
                $this->testpage->set_pagelayout('type');
                $this->assertSame('type', $this->testpage->pagelayout);
    }

    public function test_setting_course_sets_context() {
                $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);

                $this->testpage->set_course($course);

                $this->assertSame($context, $this->testpage->context);
    }

    public function test_set_category_top_level() {
        global $DB;
                $cat = $this->getDataGenerator()->create_category();
        $catdbrecord = $DB->get_record('course_categories', array('id' => $cat->id));
                $this->testpage->set_category_by_id($cat->id);
                $this->assertEquals($catdbrecord, $this->testpage->category);
        $this->assertSame(context_coursecat::instance($cat->id), $this->testpage->context);
    }

    public function test_set_nested_categories() {
        global $DB;
                $topcat = $this->getDataGenerator()->create_category();
        $topcatdbrecord = $DB->get_record('course_categories', array('id' => $topcat->id));
        $subcat = $this->getDataGenerator()->create_category(array('parent'=>$topcat->id));
        $subcatdbrecord = $DB->get_record('course_categories', array('id' => $subcat->id));
                $this->testpage->set_category_by_id($subcat->id);
                $categories = $this->testpage->categories;
        $this->assertCount(2, $categories);
        $this->assertEquals($topcatdbrecord, array_pop($categories));
        $this->assertEquals($subcatdbrecord, array_pop($categories));
    }

    public function test_cm_null_initially() {
                $this->assertNull($this->testpage->cm);
    }

    public function test_set_cm() {
                $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course'=>$course->id));
        $cm = get_coursemodule_from_id('forum', $forum->cmid);
                $this->testpage->set_cm($cm);
                $this->assertEquals($cm->id, $this->testpage->cm->id);
    }

    public function test_cannot_set_activity_record_before_cm() {
                $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course'=>$course->id));
        $cm = get_coursemodule_from_id('forum', $forum->cmid);
                $this->setExpectedException('coding_exception');
                $this->testpage->set_activity_record($forum);
    }

    public function test_setting_cm_sets_context() {
                $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course'=>$course->id));
        $cm = get_coursemodule_from_id('forum', $forum->cmid);
                $this->testpage->set_cm($cm);
                $this->assertSame(context_module::instance($cm->id), $this->testpage->context);
    }

    public function test_activity_record_loaded_if_not_set() {
                $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course'=>$course->id));
        $cm = get_coursemodule_from_id('forum', $forum->cmid);
                $this->testpage->set_cm($cm);
                unset($forum->cmid);
        $this->assertEquals($forum, $this->testpage->activityrecord);
    }

    public function test_set_activity_record() {
                $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course'=>$course->id));
        $cm = get_coursemodule_from_id('forum', $forum->cmid);
        $this->testpage->set_cm($cm);
                $this->testpage->set_activity_record($forum);
                unset($forum->cmid);
        $this->assertEquals($forum, $this->testpage->activityrecord);
    }

    public function test_cannot_set_inconsistent_activity_record_course() {
                $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course'=>$course->id));
        $cm = get_coursemodule_from_id('forum', $forum->cmid);
        $this->testpage->set_cm($cm);
                $this->setExpectedException('coding_exception');
                $forum->course = 13;
        $this->testpage->set_activity_record($forum);
    }

    public function test_cannot_set_inconsistent_activity_record_instance() {
                $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course'=>$course->id));
        $cm = get_coursemodule_from_id('forum', $forum->cmid);
        $this->testpage->set_cm($cm);
                $this->setExpectedException('coding_exception');
                $forum->id = 13;
        $this->testpage->set_activity_record($forum);
    }

    public function test_setting_cm_sets_course() {
                $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course'=>$course->id));
        $cm = get_coursemodule_from_id('forum', $forum->cmid);
                $this->testpage->set_cm($cm);
                $this->assertEquals($course->id, $this->testpage->course->id);
    }

    public function test_set_cm_with_course_and_activity_no_db() {
                $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course'=>$course->id));
        $cm = get_coursemodule_from_id('forum', $forum->cmid);
                        $this->testpage->set_cm($cm, $course, $forum);
                $this->assertEquals($cm->id, $this->testpage->cm->id);
        $this->assertEquals($course->id, $this->testpage->course->id);
        unset($forum->cmid);
        $this->assertEquals($forum, $this->testpage->activityrecord);
    }

    public function test_cannot_set_cm_with_inconsistent_course() {
                $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course'=>$course->id));
        $cm = get_coursemodule_from_id('forum', $forum->cmid);
                $this->setExpectedException('coding_exception');
                $cm->course = 13;
        $this->testpage->set_cm($cm, $course);
    }

    public function test_get_activity_name() {
                $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course'=>$course->id));
        $cm = get_coursemodule_from_id('forum', $forum->cmid);
                $this->testpage->set_cm($cm, $course, $forum);
                $this->assertSame('forum', $this->testpage->activityname);
    }

    public function test_user_is_editing_on() {
                
                global $USER;

        $this->testpage->set_context(context_system::instance());
        $this->setAdminUser();

        $USER->editing = true;
                $this->assertTrue($this->testpage->user_is_editing());
    }

    public function test_user_is_editing_off() {
                
                global $USER;

        $this->testpage->set_context(context_system::instance());
        $this->setAdminUser();

        $USER->editing = false;
                $this->assertFalse($this->testpage->user_is_editing());
    }

    public function test_default_editing_capabilities() {
        $this->testpage->set_context(context_system::instance());
        $this->setAdminUser();

                $this->assertEquals(array('moodle/site:manageblocks'), $this->testpage->all_editing_caps());
    }

    public function test_other_block_editing_cap() {
        $this->testpage->set_context(context_system::instance());
        $this->setAdminUser();

                $this->testpage->set_blocks_editing_capability('moodle/my:manageblocks');
                $this->assertEquals(array('moodle/my:manageblocks'), $this->testpage->all_editing_caps());
    }

    public function test_other_editing_cap() {
        $this->testpage->set_context(context_system::instance());
        $this->setAdminUser();

                $this->testpage->set_other_editing_capability('moodle/course:manageactivities');
                $actualcaps = $this->testpage->all_editing_caps();
        $expectedcaps = array('moodle/course:manageactivities', 'moodle/site:manageblocks');
        $this->assertEquals(array_values($expectedcaps), array_values($actualcaps));
    }

    public function test_other_editing_caps() {
        $this->testpage->set_context(context_system::instance());
        $this->setAdminUser();

                $this->testpage->set_other_editing_capability(array('moodle/course:manageactivities', 'moodle/site:other'));
                $actualcaps = $this->testpage->all_editing_caps();
        $expectedcaps = array('moodle/course:manageactivities', 'moodle/site:other', 'moodle/site:manageblocks');
        $this->assertEquals(array_values($expectedcaps), array_values($actualcaps));
    }

    
    public function test_get_renderer() {
        global $OUTPUT, $PAGE;
        $oldoutput = $OUTPUT;
        $oldpage = $PAGE;
        $PAGE = $this->testpage;

        $this->testpage->set_pagelayout('standard');
        $this->assertEquals('standard', $this->testpage->pagelayout);
                $this->testpage->initialise_theme_and_output();
                $this->assertInstanceOf('core_renderer', $OUTPUT);
                $this->assertInstanceOf('core_renderer', $this->testpage->get_renderer('core'));
                $this->assertInstanceOf('core_renderer_cli',
            $this->testpage->get_renderer('core', null, RENDERER_TARGET_MAINTENANCE));

                $this->assertInstanceOf('core_course_renderer', $this->testpage->get_renderer('core', 'course'));

                try {
            $this->testpage->get_renderer('core', 'monkeys');
            $this->fail('Request for renderer with invalid component didn\'t throw expected exception.');
        } catch (coding_exception $exception) {
            $this->assertEquals('monkeys', $exception->debuginfo);
        }

        $PAGE = $oldpage;
        $OUTPUT = $oldoutput;
    }

    
    public function test_get_renderer_maintenance() {
        global $OUTPUT, $PAGE;
        $oldoutput = $OUTPUT;
        $oldpage = $PAGE;
        $PAGE = $this->testpage;

        $this->testpage->set_pagelayout('maintenance');
        $this->assertEquals('maintenance', $this->testpage->pagelayout);
                $this->testpage->initialise_theme_and_output();
                        $this->assertInstanceOf('core_renderer_cli', $OUTPUT);
                $this->assertInstanceOf('core_renderer', $this->testpage->get_renderer('core'));
                $this->assertInstanceOf('core_renderer_cli',
            $this->testpage->get_renderer('core', null, RENDERER_TARGET_MAINTENANCE));
                $this->assertInstanceOf('core_course_renderer', $this->testpage->get_renderer('core', 'course'));

        try {
            $this->testpage->get_renderer('core', 'monkeys');
            $this->fail('Request for renderer with invalid component didn\'t throw expected exception.');
        } catch (coding_exception $exception) {
            $this->assertEquals('monkeys', $exception->debuginfo);
        }

        $PAGE = $oldpage;
        $OUTPUT = $oldoutput;
    }

    public function test_render_to_cli() {
        global $OUTPUT;

        $footer = $OUTPUT->footer();
        $this->assertEmpty($footer, 'cli output does not have a footer.');
    }
}


class testable_moodle_page extends moodle_page {
    public function initialise_default_pagetype($script = null) {
        parent::initialise_default_pagetype($script);
    }
    public function url_to_class_name($url) {
        return parent::url_to_class_name($url);
    }
    public function all_editing_caps() {
        return parent::all_editing_caps();
    }
}
