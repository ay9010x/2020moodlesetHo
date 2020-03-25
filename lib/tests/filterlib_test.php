<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/filterlib.php');


class core_filterlib_testcase extends advanced_testcase {
    private $syscontext;
    private $childcontext;
    private $childcontext2;
    private $catcontext;
    private $coursecontext;
    private $course;
    private $activity1context;
    private $activity2context;

    protected function setUp() {
        global $DB;
        parent::setUp();

        $this->resetAfterTest();
        $DB->delete_records('filter_active', array());
        $DB->delete_records('filter_config', array());
    }

    private function assert_only_one_filter_globally($filter, $state) {
        global $DB;
        $recs = $DB->get_records('filter_active');
        $this->assertCount(1, $recs);
        $rec = reset($recs);
        unset($rec->id);
        $expectedrec = new stdClass();
        $expectedrec->filter = $filter;
        $expectedrec->contextid = context_system::instance()->id;
        $expectedrec->active = $state;
        $expectedrec->sortorder = 1;
        $this->assertEquals($expectedrec, $rec);
    }

    private function assert_global_sort_order($filters) {
        global $DB;

        $sortedfilters = $DB->get_records_menu('filter_active',
            array('contextid' => context_system::instance()->id), 'sortorder', 'sortorder,filter');
        $testarray = array();
        $index = 1;
        foreach ($filters as $filter) {
            $testarray[$index++] = $filter;
        }
        $this->assertEquals($testarray, $sortedfilters);
    }

    public function test_set_filter_globally_on() {
                        filter_set_global_state('name', TEXTFILTER_ON);
                $this->assert_only_one_filter_globally('name', TEXTFILTER_ON);
    }

    public function test_set_filter_globally_off() {
                        filter_set_global_state('name', TEXTFILTER_OFF);
                $this->assert_only_one_filter_globally('name', TEXTFILTER_OFF);
    }

    public function test_set_filter_globally_disabled() {
                        filter_set_global_state('name', TEXTFILTER_DISABLED);
                $this->assert_only_one_filter_globally('name', TEXTFILTER_DISABLED);
    }

    
    public function test_global_config_exception_on_invalid_state() {
        filter_set_global_state('name', 0);
    }

    public function test_auto_sort_order() {
                        filter_set_global_state('one', TEXTFILTER_DISABLED);
        filter_set_global_state('two', TEXTFILTER_DISABLED);
                $this->assert_global_sort_order(array('one', 'two'));
    }

    public function test_auto_sort_order_enabled() {
                        filter_set_global_state('one', TEXTFILTER_ON);
        filter_set_global_state('two', TEXTFILTER_OFF);
                $this->assert_global_sort_order(array('one', 'two'));
    }

    public function test_update_existing_dont_duplicate() {
                        filter_set_global_state('name', TEXTFILTER_ON);
        filter_set_global_state('name', TEXTFILTER_OFF);
                $this->assert_only_one_filter_globally('name', TEXTFILTER_OFF);
    }

    public function test_update_reorder_down() {
                filter_set_global_state('one', TEXTFILTER_ON);
        filter_set_global_state('two', TEXTFILTER_ON);
        filter_set_global_state('three', TEXTFILTER_ON);
                filter_set_global_state('two', TEXTFILTER_ON, -1);
                $this->assert_global_sort_order(array('two', 'one', 'three'));
    }

    public function test_update_reorder_up() {
                filter_set_global_state('one', TEXTFILTER_ON);
        filter_set_global_state('two', TEXTFILTER_ON);
        filter_set_global_state('three', TEXTFILTER_ON);
        filter_set_global_state('four', TEXTFILTER_ON);
                filter_set_global_state('two', TEXTFILTER_ON, 1);
                $this->assert_global_sort_order(array('one', 'three', 'two', 'four'));
    }

    public function test_auto_sort_order_change_to_enabled() {
                filter_set_global_state('one', TEXTFILTER_ON);
        filter_set_global_state('two', TEXTFILTER_DISABLED);
        filter_set_global_state('three', TEXTFILTER_DISABLED);
                filter_set_global_state('three', TEXTFILTER_ON);
                $this->assert_global_sort_order(array('one', 'three', 'two'));
    }

    public function test_auto_sort_order_change_to_disabled() {
                filter_set_global_state('one', TEXTFILTER_ON);
        filter_set_global_state('two', TEXTFILTER_ON);
        filter_set_global_state('three', TEXTFILTER_DISABLED);
                filter_set_global_state('one', TEXTFILTER_DISABLED);
                $this->assert_global_sort_order(array('two', 'one', 'three'));
    }

    public function test_filter_get_global_states() {
                filter_set_global_state('one', TEXTFILTER_ON);
        filter_set_global_state('two', TEXTFILTER_OFF);
        filter_set_global_state('three', TEXTFILTER_DISABLED);
                $filters = filter_get_global_states();
                $this->assertEquals(array(
            'one' => (object) array('filter' => 'one', 'active' => TEXTFILTER_ON, 'sortorder' => 1),
            'two' => (object) array('filter' => 'two', 'active' => TEXTFILTER_OFF, 'sortorder' => 2),
            'three' => (object) array('filter' => 'three', 'active' => TEXTFILTER_DISABLED, 'sortorder' => 3)
        ), $filters);
    }

    private function assert_only_one_local_setting($filter, $contextid, $state) {
        global $DB;
        $recs = $DB->get_records('filter_active');
        $this->assertEquals(1, count($recs), 'More than one record returned %s.');
        $rec = reset($recs);
        unset($rec->id);
        unset($rec->sortorder);
        $expectedrec = new stdClass();
        $expectedrec->filter = $filter;
        $expectedrec->contextid = $contextid;
        $expectedrec->active = $state;
        $this->assertEquals($expectedrec, $rec);
    }

    private function assert_no_local_setting() {
        global $DB;
        $this->assertEquals(0, $DB->count_records('filter_active'));
    }

    public function test_local_on() {
                filter_set_local_state('name', 123, TEXTFILTER_ON);
                $this->assert_only_one_local_setting('name', 123, TEXTFILTER_ON);
    }

    public function test_local_off() {
                filter_set_local_state('name', 123, TEXTFILTER_OFF);
                $this->assert_only_one_local_setting('name', 123, TEXTFILTER_OFF);
    }

    public function test_local_inherit() {
                filter_set_local_state('name', 123, TEXTFILTER_INHERIT);
                $this->assert_no_local_setting();
    }

    
    public function test_local_invalid_state_throws_exception() {
                filter_set_local_state('name', 123, -9999);
    }

    
    public function test_throws_exception_when_setting_global() {
                filter_set_local_state('name', context_system::instance()->id, TEXTFILTER_INHERIT);
    }

    public function test_local_inherit_deletes_existing() {
                filter_set_local_state('name', 123, TEXTFILTER_INHERIT);
                filter_set_local_state('name', 123, TEXTFILTER_INHERIT);
                $this->assert_no_local_setting();
    }

    private function assert_only_one_config($filter, $context, $name, $value) {
        global $DB;
        $recs = $DB->get_records('filter_config');
        $this->assertEquals(1, count($recs), 'More than one record returned %s.');
        $rec = reset($recs);
        unset($rec->id);
        $expectedrec = new stdClass();
        $expectedrec->filter = $filter;
        $expectedrec->contextid = $context;
        $expectedrec->name = $name;
        $expectedrec->value = $value;
        $this->assertEquals($expectedrec, $rec);
    }

    public function test_set_new_config() {
                filter_set_local_config('name', 123, 'settingname', 'An arbitrary value');
                $this->assert_only_one_config('name', 123, 'settingname', 'An arbitrary value');
    }

    public function test_update_existing_config() {
                filter_set_local_config('name', 123, 'settingname', 'An arbitrary value');
                filter_set_local_config('name', 123, 'settingname', 'A changed value');
                $this->assert_only_one_config('name', 123, 'settingname', 'A changed value');
    }

    public function test_filter_get_local_config() {
                filter_set_local_config('name', 123, 'setting1', 'An arbitrary value');
        filter_set_local_config('name', 123, 'setting2', 'Another arbitrary value');
        filter_set_local_config('name', 122, 'settingname', 'Value from another context');
        filter_set_local_config('other', 123, 'settingname', 'Someone else\'s value');
                $config = filter_get_local_config('name', 123);
                $this->assertEquals(array('setting1' => 'An arbitrary value', 'setting2' => 'Another arbitrary value'), $config);
    }

    protected function setup_available_in_context_tests() {
        $course = $this->getDataGenerator()->create_course(array('category'=>1));

        $this->childcontext = context_coursecat::instance(1);
        $this->childcontext2 = context_course::instance($course->id);
        $this->syscontext = context_system::instance();
    }

    private function assert_filter_list($expectedfilters, $filters) {
        $this->setup_available_in_context_tests();
        $this->assertEquals($expectedfilters, array_keys($filters), '', 0, 10, true);
    }

    public function test_globally_on_is_returned() {
        $this->setup_available_in_context_tests();
                filter_set_global_state('name', TEXTFILTER_ON);
                $filters = filter_get_active_in_context($this->syscontext);
                $this->assert_filter_list(array('name'), $filters);
                $this->assertEquals(array(), $filters['name']);
    }

    public function test_globally_off_not_returned() {
        $this->setup_available_in_context_tests();
                filter_set_global_state('name', TEXTFILTER_OFF);
                $filters = filter_get_active_in_context($this->childcontext2);
                $this->assert_filter_list(array(), $filters);
    }

    public function test_globally_off_overridden() {
        $this->setup_available_in_context_tests();
                filter_set_global_state('name', TEXTFILTER_OFF);
        filter_set_local_state('name', $this->childcontext->id, TEXTFILTER_ON);
                $filters = filter_get_active_in_context($this->childcontext2);
                $this->assert_filter_list(array('name'), $filters);
    }

    public function test_globally_on_overridden() {
        $this->setup_available_in_context_tests();
                filter_set_global_state('name', TEXTFILTER_ON);
        filter_set_local_state('name', $this->childcontext->id, TEXTFILTER_OFF);
                $filters = filter_get_active_in_context($this->childcontext2);
                $this->assert_filter_list(array(), $filters);
    }

    public function test_globally_disabled_not_overridden() {
        $this->setup_available_in_context_tests();
                filter_set_global_state('name', TEXTFILTER_DISABLED);
        filter_set_local_state('name', $this->childcontext->id, TEXTFILTER_ON);
                $filters = filter_get_active_in_context($this->syscontext);
                $this->assert_filter_list(array(), $filters);
    }

    public function test_single_config_returned() {
        $this->setup_available_in_context_tests();
                filter_set_global_state('name', TEXTFILTER_ON);
        filter_set_local_config('name', $this->childcontext->id, 'settingname', 'A value');
                $filters = filter_get_active_in_context($this->childcontext);
                $this->assertEquals(array('settingname' => 'A value'), $filters['name']);
    }

    public function test_multi_config_returned() {
        $this->setup_available_in_context_tests();
                filter_set_global_state('name', TEXTFILTER_ON);
        filter_set_local_config('name', $this->childcontext->id, 'settingname', 'A value');
        filter_set_local_config('name', $this->childcontext->id, 'anothersettingname', 'Another value');
                $filters = filter_get_active_in_context($this->childcontext);
                $this->assertEquals(array('settingname' => 'A value', 'anothersettingname' => 'Another value'), $filters['name']);
    }

    public function test_config_from_other_context_not_returned() {
        $this->setup_available_in_context_tests();
                filter_set_global_state('name', TEXTFILTER_ON);
        filter_set_local_config('name', $this->childcontext->id, 'settingname', 'A value');
        filter_set_local_config('name', $this->childcontext2->id, 'anothersettingname', 'Another value');
                $filters = filter_get_active_in_context($this->childcontext2);
                $this->assertEquals(array('anothersettingname' => 'Another value'), $filters['name']);
    }

    public function test_config_from_other_filter_not_returned() {
        $this->setup_available_in_context_tests();
                filter_set_global_state('name', TEXTFILTER_ON);
        filter_set_local_config('name', $this->childcontext->id, 'settingname', 'A value');
        filter_set_local_config('other', $this->childcontext->id, 'anothersettingname', 'Another value');
                $filters = filter_get_active_in_context($this->childcontext);
                $this->assertEquals(array('settingname' => 'A value'), $filters['name']);
    }

    protected function assert_one_available_filter($filter, $localstate, $inheritedstate, $filters) {
        $this->setup_available_in_context_tests();
        $this->assertEquals(1, count($filters), 'More than one record returned %s.');
        $rec = $filters[$filter];
        unset($rec->id);
        $expectedrec = new stdClass();
        $expectedrec->filter = $filter;
        $expectedrec->localstate = $localstate;
        $expectedrec->inheritedstate = $inheritedstate;
        $this->assertEquals($expectedrec, $rec);
    }

    public function test_available_in_context_localoverride() {
        $this->setup_available_in_context_tests();
                filter_set_global_state('name', TEXTFILTER_ON);
        filter_set_local_state('name', $this->childcontext->id, TEXTFILTER_OFF);
                $filters = filter_get_available_in_context($this->childcontext);
                $this->assert_one_available_filter('name', TEXTFILTER_OFF, TEXTFILTER_ON, $filters);
    }

    public function test_available_in_context_nolocaloverride() {
        $this->setup_available_in_context_tests();
                filter_set_global_state('name', TEXTFILTER_ON);
        filter_set_local_state('name', $this->childcontext->id, TEXTFILTER_OFF);
                $filters = filter_get_available_in_context($this->childcontext2);
                $this->assert_one_available_filter('name', TEXTFILTER_INHERIT, TEXTFILTER_OFF, $filters);
    }

    public function test_available_in_context_disabled_not_returned() {
        $this->setup_available_in_context_tests();
                filter_set_global_state('name', TEXTFILTER_DISABLED);
        filter_set_local_state('name', $this->childcontext->id, TEXTFILTER_ON);
                $filters = filter_get_available_in_context($this->childcontext);
                $this->assertEquals(array(), $filters);
    }

    
    public function test_available_in_context_exception_with_syscontext() {
        $this->setup_available_in_context_tests();
                filter_get_available_in_context($this->syscontext);
    }

    protected function setup_preload_activities_test() {
        $this->syscontext = context_system::instance();
        $this->catcontext = context_coursecat::instance(1);
        $this->course = $this->getDataGenerator()->create_course(array('category'=>1));
        $this->coursecontext = context_course::instance($this->course->id);
        $page1 =  $this->getDataGenerator()->create_module('page', array('course'=>$this->course->id));
        $this->activity1context = context_module::instance($page1->cmid);
        $page2 =  $this->getDataGenerator()->create_module('page', array('course'=>$this->course->id));
        $this->activity2context = context_module::instance($page2->cmid);
    }

    private function assert_matches($modinfo) {
        global $FILTERLIB_PRIVATE, $DB;

                $FILTERLIB_PRIVATE = new stdClass();
        filter_preload_activities($modinfo);

                $before = $DB->perf_get_reads();
        $plfilters1 = filter_get_active_in_context($this->activity1context);
        $plfilters2 = filter_get_active_in_context($this->activity2context);
        $after = $DB->perf_get_reads();
        $this->assertEquals($before, $after);

                $FILTERLIB_PRIVATE = new stdClass;
        $before = $DB->perf_get_reads();
        $filters1 = filter_get_active_in_context($this->activity1context);
        $filters2 = filter_get_active_in_context($this->activity2context);
        $after = $DB->perf_get_reads();
        $this->assertTrue($after > $before);

                $this->assertEquals($plfilters1, $filters1);
        $this->assertEquals($plfilters2, $filters2);
    }

    public function test_preload() {
        $this->setup_preload_activities_test();
                $modinfo = new course_modinfo($this->course, 2);

                        
                $this->assert_matches($modinfo);

                filter_set_global_state('name', TEXTFILTER_ON);
        $this->assert_matches($modinfo);

                filter_set_local_state('name', $this->activity2context->id, TEXTFILTER_OFF);
        $this->assert_matches($modinfo);

                filter_set_local_state('name', $this->catcontext->id, TEXTFILTER_OFF);
        $this->assert_matches($modinfo);

                filter_set_local_state('name', $this->activity1context->id, TEXTFILTER_ON);
        $this->assert_matches($modinfo);

                filter_set_global_state('name', TEXTFILTER_DISABLED);
        $this->assert_matches($modinfo);

                filter_set_global_state('frog', TEXTFILTER_ON);
        filter_set_global_state('zombie', TEXTFILTER_ON);
        $this->assert_matches($modinfo);

                filter_set_local_state('zombie', $this->activity1context->id, TEXTFILTER_OFF);
        filter_set_local_state('frog', $this->activity2context->id, TEXTFILTER_OFF);
        $this->assert_matches($modinfo);

                filter_set_local_config('name', $this->activity1context->id, 'a', 'x');
        filter_set_local_config('zombie', $this->activity1context->id, 'a', 'y');
        filter_set_local_config('frog', $this->activity1context->id, 'a', 'z');
                        filter_set_local_config('frog', $this->coursecontext->id, 'q', 'x');
        filter_set_local_config('frog', $this->catcontext->id, 'q', 'z');
        $this->assert_matches($modinfo);
    }

    public function test_filter_delete_all_for_filter() {
        global $DB;

                filter_set_global_state('name', TEXTFILTER_ON);
        filter_set_global_state('other', TEXTFILTER_ON);
        filter_set_local_config('name', context_system::instance()->id, 'settingname', 'A value');
        filter_set_local_config('other', context_system::instance()->id, 'settingname', 'Other value');
        set_config('configname', 'A config value', 'filter_name');
        set_config('configname', 'Other config value', 'filter_other');
                filter_delete_all_for_filter('name');
                $this->assertEquals(1, $DB->count_records('filter_active'));
        $this->assertTrue($DB->record_exists('filter_active', array('filter' => 'other')));
        $this->assertEquals(1, $DB->count_records('filter_config'));
        $this->assertTrue($DB->record_exists('filter_config', array('filter' => 'other')));
        $expectedconfig = new stdClass;
        $expectedconfig->configname = 'Other config value';
        $this->assertEquals($expectedconfig, get_config('filter_other'));
        $this->assertEquals(get_config('filter_name'), new stdClass());
    }

    public function test_filter_delete_all_for_context() {
        global $DB;

                filter_set_global_state('name', TEXTFILTER_ON);
        filter_set_local_state('name', 123, TEXTFILTER_OFF);
        filter_set_local_config('name', 123, 'settingname', 'A value');
        filter_set_local_config('other', 123, 'settingname', 'Other value');
        filter_set_local_config('other', 122, 'settingname', 'Other value');
                filter_delete_all_for_context(123);
                $this->assertEquals(1, $DB->count_records('filter_active'));
        $this->assertTrue($DB->record_exists('filter_active', array('contextid' => context_system::instance()->id)));
        $this->assertEquals(1, $DB->count_records('filter_config'));
        $this->assertTrue($DB->record_exists('filter_config', array('filter' => 'other')));
    }

    public function test_set() {
        global $CFG;

        $this->assertFileExists("$CFG->dirroot/filter/emailprotect");         $this->assertFileExists("$CFG->dirroot/filter/tidy");                 $this->assertFileNotExists("$CFG->dirroot/filter/grgrggr");   
                set_config('filterall', 0);
        set_config('stringfilters', '');
                filter_set_applies_to_strings('tidy', true);
                $this->assertEquals('tidy', $CFG->stringfilters);
        $this->assertEquals(1, $CFG->filterall);

        filter_set_applies_to_strings('grgrggr', true);
        $this->assertEquals('tidy', $CFG->stringfilters);
        $this->assertEquals(1, $CFG->filterall);

        filter_set_applies_to_strings('emailprotect', true);
        $this->assertEquals('tidy,emailprotect', $CFG->stringfilters);
        $this->assertEquals(1, $CFG->filterall);
    }

    public function test_unset_to_empty() {
        global $CFG;

        $this->assertFileExists("$CFG->dirroot/filter/tidy"); 
                set_config('filterall', 1);
        set_config('stringfilters', 'tidy');
                filter_set_applies_to_strings('tidy', false);
                $this->assertEquals('', $CFG->stringfilters);
        $this->assertEquals('', $CFG->filterall);
    }

    public function test_unset_multi() {
        global $CFG;

        $this->assertFileExists("$CFG->dirroot/filter/emailprotect");         $this->assertFileExists("$CFG->dirroot/filter/tidy");                 $this->assertFileExists("$CFG->dirroot/filter/multilang");    
                set_config('filterall', 1);
        set_config('stringfilters', 'emailprotect,tidy,multilang');
                filter_set_applies_to_strings('tidy', false);
                $this->assertEquals('emailprotect,multilang', $CFG->stringfilters);
        $this->assertEquals(1, $CFG->filterall);
    }

    public function test_filter_manager_instance() {

        set_config('perfdebug', 7);
        filter_manager::reset_caches();
        $filterman = filter_manager::instance();
        $this->assertInstanceOf('filter_manager', $filterman);
        $this->assertNotInstanceOf('performance_measuring_filter_manager', $filterman);

        set_config('perfdebug', 15);
        filter_manager::reset_caches();
        $filterman = filter_manager::instance();
        $this->assertInstanceOf('filter_manager', $filterman);
        $this->assertInstanceOf('performance_measuring_filter_manager', $filterman);
    }
}
