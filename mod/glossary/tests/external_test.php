<?php



defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');


class mod_glossary_external_testcase extends externallib_advanced_testcase {

    
    public function test_get_glossaries_by_courses() {
        $this->resetAfterTest(true);

                $this->setAdminUser();
        $c1 = self::getDataGenerator()->create_course();
        $c2 = self::getDataGenerator()->create_course();
        $g1 = self::getDataGenerator()->create_module('glossary', array('course' => $c1->id, 'name' => 'First Glossary'));
        $g2 = self::getDataGenerator()->create_module('glossary', array('course' => $c1->id, 'name' => 'Second Glossary'));
        $g3 = self::getDataGenerator()->create_module('glossary', array('course' => $c2->id, 'name' => 'Third Glossary'));

        $s1 = $this->getDataGenerator()->create_user();
        self::getDataGenerator()->enrol_user($s1->id,  $c1->id);

                $this->setUser($s1);
        $glossaries = mod_glossary_external::get_glossaries_by_courses(array());
        $glossaries = external_api::clean_returnvalue(mod_glossary_external::get_glossaries_by_courses_returns(), $glossaries);

        $this->assertCount(2, $glossaries['glossaries']);
        $this->assertEquals('First Glossary', $glossaries['glossaries'][0]['name']);
        $this->assertEquals('Second Glossary', $glossaries['glossaries'][1]['name']);

                $glossaries = mod_glossary_external::get_glossaries_by_courses(array($c1->id, $c2->id));
        $glossaries = external_api::clean_returnvalue(mod_glossary_external::get_glossaries_by_courses_returns(), $glossaries);

        $this->assertCount(2, $glossaries['glossaries']);
        $this->assertEquals('First Glossary', $glossaries['glossaries'][0]['name']);
        $this->assertEquals('Second Glossary', $glossaries['glossaries'][1]['name']);

        $this->assertEquals('course', $glossaries['warnings'][0]['item']);
        $this->assertEquals($c2->id, $glossaries['warnings'][0]['itemid']);
        $this->assertEquals('1', $glossaries['warnings'][0]['warningcode']);

                $this->setAdminUser();

        $glossaries = mod_glossary_external::get_glossaries_by_courses(array($c2->id));
        $glossaries = external_api::clean_returnvalue(mod_glossary_external::get_glossaries_by_courses_returns(), $glossaries);

        $this->assertCount(1, $glossaries['glossaries']);
        $this->assertEquals('Third Glossary', $glossaries['glossaries'][0]['name']);
    }

    public function test_view_glossary() {
        $this->resetAfterTest(true);

                $c1 = $this->getDataGenerator()->create_course();
        $g1 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id));
        $u1 = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($u1->id, $c1->id);

        $sink = $this->redirectEvents();
        $this->setUser($u1);
        $return = mod_glossary_external::view_glossary($g1->id, 'letter');
        $return = external_api::clean_returnvalue(mod_glossary_external::view_glossary_returns(), $return);
        $events = $sink->get_events();

                $this->assertTrue($return['status']);
        $this->assertEmpty($return['warnings']);
        $this->assertCount(1, $events);
        $this->assertEquals('\mod_glossary\event\course_module_viewed', $events[0]->eventname);
        $sink->close();
    }

    public function test_view_glossary_without_permission() {
        $this->resetAfterTest(true);

                $c1 = $this->getDataGenerator()->create_course();
        $g1 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id));
        $u1 = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($u1->id, $c1->id);
        $ctx = context_module::instance($g1->cmid);

                $roles = get_archetype_roles('user');
        $role = array_shift($roles);
        assign_capability('mod/glossary:view', CAP_PROHIBIT, $role->id, $ctx, true);
        accesslib_clear_all_caches_for_unit_testing();

                $this->setUser($u1);
        $this->setExpectedException('require_login_exception', 'Activity is hidden');
        mod_glossary_external::view_glossary($g1->id, 'letter');
    }

    public function test_view_entry() {
        $this->resetAfterTest(true);

                $gg = $this->getDataGenerator()->get_plugin_generator('mod_glossary');
        $c1 = $this->getDataGenerator()->create_course();
        $g1 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id));
        $g2 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id, 'visible' => false));
        $u1 = $this->getDataGenerator()->create_user();
        $e1 = $gg->create_content($g1, array('approved' => 1));
        $e2 = $gg->create_content($g1, array('approved' => 0, 'userid' => $u1->id));
        $e3 = $gg->create_content($g1, array('approved' => 0, 'userid' => -1));
        $e4 = $gg->create_content($g2, array('approved' => 1));
        $ctx = context_module::instance($g1->cmid);
        $this->getDataGenerator()->enrol_user($u1->id, $c1->id);
        $this->setUser($u1);

                $sink = $this->redirectEvents();
        $return = mod_glossary_external::view_entry($e1->id);
        $return = external_api::clean_returnvalue(mod_glossary_external::view_entry_returns(), $return);
        $events = $sink->get_events();
        $this->assertTrue($return['status']);
        $this->assertEmpty($return['warnings']);
        $this->assertCount(1, $events);
        $this->assertEquals('\mod_glossary\event\entry_viewed', $events[0]->eventname);
        $sink->close();

                $return = mod_glossary_external::view_entry($e2->id);
        $return = external_api::clean_returnvalue(mod_glossary_external::view_entry_returns(), $return);
        $events = $sink->get_events();
        $this->assertTrue($return['status']);
        $this->assertEmpty($return['warnings']);
        $this->assertCount(1, $events);
        $this->assertEquals('\mod_glossary\event\entry_viewed', $events[0]->eventname);
        $sink->close();

                try {
            mod_glossary_external::view_entry($e3->id);
            $this->fail('Cannot view non-approved entries of others.');
        } catch (invalid_parameter_exception $e) {
                    }

                $this->setExpectedException('require_login_exception', 'Activity is hidden');
        mod_glossary_external::view_entry($e4->id);
    }

    public function test_get_entries_by_letter() {
        $this->resetAfterTest(true);

                $gg = $this->getDataGenerator()->get_plugin_generator('mod_glossary');
        $c1 = $this->getDataGenerator()->create_course();
        $g1 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id));
        $g2 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id));
        $u1 = $this->getDataGenerator()->create_user();
        $ctx = context_module::instance($g1->cmid);
        $this->getDataGenerator()->enrol_user($u1->id, $c1->id);

        $e1a = $gg->create_content($g1, array('approved' => 0, 'concept' => 'Bob', 'userid' => 2));
        $e1b = $gg->create_content($g1, array('approved' => 1, 'concept' => 'Jane', 'userid' => 2));
        $e1c = $gg->create_content($g1, array('approved' => 1, 'concept' => 'Alice', 'userid' => $u1->id));
        $e1d = $gg->create_content($g1, array('approved' => 0, 'concept' => '0-day', 'userid' => $u1->id));
        $e2a = $gg->create_content($g2);

        $this->setAdminUser();

                $return = mod_glossary_external::get_entries_by_letter($g1->id, 'ALL', 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_letter_returns(), $return);
        $this->assertCount(3, $return['entries']);
        $this->assertEquals(3, $return['count']);
        $this->assertEquals($e1c->id, $return['entries'][0]['id']);
        $this->assertEquals($e1a->id, $return['entries'][1]['id']);
        $this->assertEquals($e1b->id, $return['entries'][2]['id']);

                $return = mod_glossary_external::get_entries_by_letter($g1->id, 'ALL', 0, 20, array('includenotapproved' => 1));
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_letter_returns(), $return);
        $this->assertCount(4, $return['entries']);
        $this->assertEquals(4, $return['count']);
        $this->assertEquals($e1d->id, $return['entries'][0]['id']);
        $this->assertEquals($e1c->id, $return['entries'][1]['id']);
        $this->assertEquals($e1a->id, $return['entries'][2]['id']);
        $this->assertEquals($e1b->id, $return['entries'][3]['id']);

                $this->setUser($u1);
        $return = mod_glossary_external::get_entries_by_letter($g1->id, 'ALL', 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_letter_returns(), $return);
        $this->assertCount(3, $return['entries']);
        $this->assertEquals(3, $return['count']);
        $this->assertEquals($e1d->id, $return['entries'][0]['id']);
        $this->assertEquals($e1c->id, $return['entries'][1]['id']);
        $this->assertEquals($e1b->id, $return['entries'][2]['id']);

                $return = mod_glossary_external::get_entries_by_letter($g1->id, 'ALL', 0, 20, array('includenotapproved' => 1));
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_letter_returns(), $return);
        $this->assertCount(3, $return['entries']);
        $this->assertEquals(3, $return['count']);
        $this->assertEquals($e1d->id, $return['entries'][0]['id']);
        $this->assertEquals($e1c->id, $return['entries'][1]['id']);
        $this->assertEquals($e1b->id, $return['entries'][2]['id']);
    }

    public function test_get_entries_by_letter_with_parameters() {
        $this->resetAfterTest(true);

                $gg = $this->getDataGenerator()->get_plugin_generator('mod_glossary');
        $c1 = $this->getDataGenerator()->create_course();
        $g1 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id));
        $u1 = $this->getDataGenerator()->create_user();
        $ctx = context_module::instance($g1->cmid);
        $this->getDataGenerator()->enrol_user($u1->id, $c1->id);

        $e1a = $gg->create_content($g1, array('approved' => 1, 'concept' => '0-day', 'userid' => $u1->id));
        $e1b = $gg->create_content($g1, array('approved' => 1, 'concept' => 'Bob', 'userid' => 2));
        $e1c = $gg->create_content($g1, array('approved' => 1, 'concept' => '1-dayb', 'userid' => $u1->id));

        $this->setUser($u1);

                $return = mod_glossary_external::get_entries_by_letter($g1->id, 'b', 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_letter_returns(), $return);
        $this->assertCount(1, $return['entries']);
        $this->assertEquals(1, $return['count']);
        $this->assertEquals($e1b->id, $return['entries'][0]['id']);

                $return = mod_glossary_external::get_entries_by_letter($g1->id, 'SPECIAL', 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_letter_returns(), $return);
        $this->assertCount(2, $return['entries']);
        $this->assertEquals(2, $return['count']);
        $this->assertEquals($e1a->id, $return['entries'][0]['id']);
        $this->assertEquals($e1c->id, $return['entries'][1]['id']);

                $return = mod_glossary_external::get_entries_by_letter($g1->id, 'ALL', 0, 1, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_letter_returns(), $return);
        $this->assertCount(1, $return['entries']);
        $this->assertEquals(3, $return['count']);
        $this->assertEquals($e1a->id, $return['entries'][0]['id']);
        $return = mod_glossary_external::get_entries_by_letter($g1->id, 'ALL', 1, 2, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_letter_returns(), $return);
        $this->assertCount(2, $return['entries']);
        $this->assertEquals(3, $return['count']);
        $this->assertEquals($e1c->id, $return['entries'][0]['id']);
        $this->assertEquals($e1b->id, $return['entries'][1]['id']);
    }

    public function test_get_entries_by_date() {
        global $DB;
        $this->resetAfterTest(true);

                $gg = $this->getDataGenerator()->get_plugin_generator('mod_glossary');
        $c1 = $this->getDataGenerator()->create_course();
        $g1 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id, 'displayformat' => 'entrylist'));
        $g2 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id));
        $u1 = $this->getDataGenerator()->create_user();
        $ctx = context_module::instance($g1->cmid);
        $this->getDataGenerator()->enrol_user($u1->id, $c1->id);

        $now = time();
        $e1a = $gg->create_content($g1, array('approved' => 1, 'concept' => 'Bob', 'userid' => $u1->id,
            'timecreated' => 1, 'timemodified' => $now + 3600));
        $e1b = $gg->create_content($g1, array('approved' => 1, 'concept' => 'Jane', 'userid' => $u1->id,
            'timecreated' => $now + 3600, 'timemodified' => 1));
        $e1c = $gg->create_content($g1, array('approved' => 1, 'concept' => 'Alice', 'userid' => $u1->id,
            'timecreated' => $now + 1, 'timemodified' => $now + 1));
        $e1d = $gg->create_content($g1, array('approved' => 0, 'concept' => '0-day', 'userid' => $u1->id,
            'timecreated' => $now + 2, 'timemodified' => $now + 2));
        $e2a = $gg->create_content($g2);

        $this->setAdminUser($u1);

                $return = mod_glossary_external::get_entries_by_date($g1->id, 'UPDATE', 'DESC', 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_date_returns(), $return);
        $this->assertCount(3, $return['entries']);
        $this->assertEquals(3, $return['count']);
        $this->assertEquals($e1a->id, $return['entries'][0]['id']);
        $this->assertEquals($e1c->id, $return['entries'][1]['id']);
        $this->assertEquals($e1b->id, $return['entries'][2]['id']);

                $return = mod_glossary_external::get_entries_by_date($g1->id, 'UPDATE', 'ASC', 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_date_returns(), $return);
        $this->assertCount(3, $return['entries']);
        $this->assertEquals(3, $return['count']);
        $this->assertEquals($e1b->id, $return['entries'][0]['id']);
        $this->assertEquals($e1c->id, $return['entries'][1]['id']);
        $this->assertEquals($e1a->id, $return['entries'][2]['id']);

                $return = mod_glossary_external::get_entries_by_date($g1->id, 'CREATION', 'ASC', 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_date_returns(), $return);
        $this->assertCount(3, $return['entries']);
        $this->assertEquals(3, $return['count']);
        $this->assertEquals($e1a->id, $return['entries'][0]['id']);
        $this->assertEquals($e1c->id, $return['entries'][1]['id']);
        $this->assertEquals($e1b->id, $return['entries'][2]['id']);

                $return = mod_glossary_external::get_entries_by_date($g1->id, 'CREATION', 'DESC', 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_date_returns(), $return);
        $this->assertCount(3, $return['entries']);
        $this->assertEquals(3, $return['count']);
        $this->assertEquals($e1b->id, $return['entries'][0]['id']);
        $this->assertEquals($e1c->id, $return['entries'][1]['id']);
        $this->assertEquals($e1a->id, $return['entries'][2]['id']);

                $return = mod_glossary_external::get_entries_by_date($g1->id, 'CREATION', 'ASC', 0, 20,
            array('includenotapproved' => true));
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_date_returns(), $return);
        $this->assertCount(4, $return['entries']);
        $this->assertEquals(4, $return['count']);
        $this->assertEquals($e1a->id, $return['entries'][0]['id']);
        $this->assertEquals($e1c->id, $return['entries'][1]['id']);
        $this->assertEquals($e1d->id, $return['entries'][2]['id']);
        $this->assertEquals($e1b->id, $return['entries'][3]['id']);

                $return = mod_glossary_external::get_entries_by_date($g1->id, 'CREATION', 'ASC', 0, 2,
            array('includenotapproved' => true));
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_date_returns(), $return);
        $this->assertCount(2, $return['entries']);
        $this->assertEquals(4, $return['count']);
        $this->assertEquals($e1a->id, $return['entries'][0]['id']);
        $this->assertEquals($e1c->id, $return['entries'][1]['id']);
        $return = mod_glossary_external::get_entries_by_date($g1->id, 'CREATION', 'ASC', 2, 2,
            array('includenotapproved' => true));
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_date_returns(), $return);
        $this->assertCount(2, $return['entries']);
        $this->assertEquals(4, $return['count']);
        $this->assertEquals($e1d->id, $return['entries'][0]['id']);
        $this->assertEquals($e1b->id, $return['entries'][1]['id']);
    }

    public function test_get_categories() {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $gg = $this->getDataGenerator()->get_plugin_generator('mod_glossary');
        $c1 = $this->getDataGenerator()->create_course();
        $g1 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id));
        $g2 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id));
        $cat1a = $gg->create_category($g1);
        $cat1b = $gg->create_category($g1);
        $cat1c = $gg->create_category($g1);
        $cat2a = $gg->create_category($g2);

        $return = mod_glossary_external::get_categories($g1->id, 0, 20);
        $return = external_api::clean_returnvalue(mod_glossary_external::get_categories_returns(), $return);
        $this->assertCount(3, $return['categories']);
        $this->assertEquals(3, $return['count']);
        $this->assertEquals($cat1a->id, $return['categories'][0]['id']);
        $this->assertEquals($cat1b->id, $return['categories'][1]['id']);
        $this->assertEquals($cat1c->id, $return['categories'][2]['id']);

        $return = mod_glossary_external::get_categories($g1->id, 1, 2);
        $return = external_api::clean_returnvalue(mod_glossary_external::get_categories_returns(), $return);
        $this->assertCount(2, $return['categories']);
        $this->assertEquals(3, $return['count']);
        $this->assertEquals($cat1b->id, $return['categories'][0]['id']);
        $this->assertEquals($cat1c->id, $return['categories'][1]['id']);
    }

    public function test_get_entries_by_category() {
        $this->resetAfterTest(true);

        $gg = $this->getDataGenerator()->get_plugin_generator('mod_glossary');
        $c1 = $this->getDataGenerator()->create_course();
        $g1 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id, 'displayformat' => 'entrylist'));
        $g2 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id, 'displayformat' => 'entrylist'));
        $u1 = $this->getDataGenerator()->create_user();
        $ctx = context_module::instance($g1->cmid);

        $e1a1 = $gg->create_content($g1, array('approved' => 1, 'userid' => $u1->id));
        $e1a2 = $gg->create_content($g1, array('approved' => 1, 'userid' => $u1->id));
        $e1a3 = $gg->create_content($g1, array('approved' => 1, 'userid' => $u1->id));
        $e1b1 = $gg->create_content($g1, array('approved' => 1, 'userid' => $u1->id));
        $e1b2 = $gg->create_content($g1, array('approved' => 0, 'userid' => $u1->id));
        $e1x1 = $gg->create_content($g1, array('approved' => 1, 'userid' => $u1->id));
        $e1x2 = $gg->create_content($g1, array('approved' => 0, 'userid' => $u1->id));
        $e2a1 = $gg->create_content($g2, array('approved' => 1, 'userid' => $u1->id));
        $e2a2 = $gg->create_content($g2, array('approved' => 1, 'userid' => $u1->id));

        $cat1a = $gg->create_category($g1, array('name' => 'Fish'), array($e1a1, $e1a2, $e1a3));
        $cat1b = $gg->create_category($g1, array('name' => 'Cat'), array($e1b1, $e1b2));
        $cat1c = $gg->create_category($g1, array('name' => 'Zebra'), array($e1b1));           $cat2a = $gg->create_category($g2, array(), array($e2a1, $e2a2));

        $this->setAdminUser();

                $return = mod_glossary_external::get_entries_by_category($g1->id, $cat1a->id, 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_category_returns(), $return);
        $this->assertCount(3, $return['entries']);
        $this->assertEquals(3, $return['count']);
        $this->assertEquals($e1a1->id, $return['entries'][0]['id']);
        $this->assertEquals($e1a2->id, $return['entries'][1]['id']);
        $this->assertEquals($e1a3->id, $return['entries'][2]['id']);

                $return = mod_glossary_external::get_entries_by_category($g1->id, GLOSSARY_SHOW_ALL_CATEGORIES, 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_category_returns(), $return);
        $this->assertCount(5, $return['entries']);
        $this->assertEquals(5, $return['count']);
        $this->assertEquals($e1b1->id, $return['entries'][0]['id']);
        $this->assertEquals($e1a1->id, $return['entries'][1]['id']);
        $this->assertEquals($e1a2->id, $return['entries'][2]['id']);
        $this->assertEquals($e1a3->id, $return['entries'][3]['id']);
        $this->assertEquals($e1b1->id, $return['entries'][4]['id']);

                $return = mod_glossary_external::get_entries_by_category($g1->id, GLOSSARY_SHOW_NOT_CATEGORISED, 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_category_returns(), $return);
        $this->assertCount(1, $return['entries']);
        $this->assertEquals(1, $return['count']);
        $this->assertEquals($e1x1->id, $return['entries'][0]['id']);

                $return = mod_glossary_external::get_entries_by_category($g1->id, $cat1b->id, 0, 20,
            array('includenotapproved' => true));
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_category_returns(), $return);
        $this->assertCount(2, $return['entries']);
        $this->assertEquals(2, $return['count']);
        $this->assertEquals($e1b1->id, $return['entries'][0]['id']);
        $this->assertEquals($e1b2->id, $return['entries'][1]['id']);

                $return = mod_glossary_external::get_entries_by_category($g1->id, GLOSSARY_SHOW_ALL_CATEGORIES, 0, 3,
            array('includenotapproved' => true));
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_category_returns(), $return);
        $this->assertCount(3, $return['entries']);
        $this->assertEquals(6, $return['count']);
        $this->assertEquals($e1b1->id, $return['entries'][0]['id']);
        $this->assertEquals($e1b2->id, $return['entries'][1]['id']);
        $this->assertEquals($e1a1->id, $return['entries'][2]['id']);
        $return = mod_glossary_external::get_entries_by_category($g1->id, GLOSSARY_SHOW_ALL_CATEGORIES, 3, 2,
            array('includenotapproved' => true));
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_category_returns(), $return);
        $this->assertCount(2, $return['entries']);
        $this->assertEquals(6, $return['count']);
        $this->assertEquals($e1a2->id, $return['entries'][0]['id']);
        $this->assertEquals($e1a3->id, $return['entries'][1]['id']);
    }

    public function test_get_authors() {
        $this->resetAfterTest(true);

        $gg = $this->getDataGenerator()->get_plugin_generator('mod_glossary');
        $c1 = $this->getDataGenerator()->create_course();
        $g1 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id));
        $g2 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id));

        $u1 = $this->getDataGenerator()->create_user(array('lastname' => 'Upsilon'));
        $u2 = $this->getDataGenerator()->create_user(array('lastname' => 'Alpha'));
        $u3 = $this->getDataGenerator()->create_user(array('lastname' => 'Omega'));

        $ctx = context_module::instance($g1->cmid);

        $e1a = $gg->create_content($g1, array('userid' => $u1->id, 'approved' => 1));
        $e1b = $gg->create_content($g1, array('userid' => $u1->id, 'approved' => 1));
        $e1c = $gg->create_content($g1, array('userid' => $u1->id, 'approved' => 1));
        $e2a = $gg->create_content($g1, array('userid' => $u2->id, 'approved' => 1));
        $e3a = $gg->create_content($g1, array('userid' => $u3->id, 'approved' => 0));

        $this->setAdminUser();

                $return = mod_glossary_external::get_authors($g1->id, 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_authors_returns(), $return);
        $this->assertCount(2, $return['authors']);
        $this->assertEquals(2, $return['count']);
        $this->assertEquals($u2->id, $return['authors'][0]['id']);
        $this->assertEquals($u1->id, $return['authors'][1]['id']);

                $return = mod_glossary_external::get_authors($g1->id, 0, 20, array('includenotapproved' => true));
        $return = external_api::clean_returnvalue(mod_glossary_external::get_authors_returns(), $return);
        $this->assertCount(3, $return['authors']);
        $this->assertEquals(3, $return['count']);
        $this->assertEquals($u2->id, $return['authors'][0]['id']);
        $this->assertEquals($u3->id, $return['authors'][1]['id']);
        $this->assertEquals($u1->id, $return['authors'][2]['id']);

                $return = mod_glossary_external::get_authors($g1->id, 1, 1, array('includenotapproved' => true));
        $return = external_api::clean_returnvalue(mod_glossary_external::get_authors_returns(), $return);
        $this->assertCount(1, $return['authors']);
        $this->assertEquals(3, $return['count']);
        $this->assertEquals($u3->id, $return['authors'][0]['id']);
    }

    public function test_get_entries_by_author() {
        $this->resetAfterTest(true);

                $gg = $this->getDataGenerator()->get_plugin_generator('mod_glossary');
        $c1 = $this->getDataGenerator()->create_course();
        $g1 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id, 'displayformat' => 'entrylist'));
        $g2 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id, 'displayformat' => 'entrylist'));
        $u1 = $this->getDataGenerator()->create_user(array('lastname' => 'Upsilon', 'firstname' => 'Zac'));
        $u2 = $this->getDataGenerator()->create_user(array('lastname' => 'Ultra', 'firstname' => '1337'));
        $u3 = $this->getDataGenerator()->create_user(array('lastname' => 'Alpha', 'firstname' => 'Omega'));
        $u4 = $this->getDataGenerator()->create_user(array('lastname' => '0-day', 'firstname' => 'Zoe'));
        $ctx = context_module::instance($g1->cmid);
        $this->getDataGenerator()->enrol_user($u1->id, $c1->id);

        $e1a1 = $gg->create_content($g1, array('approved' => 1, 'userid' => $u1->id));
        $e1a2 = $gg->create_content($g1, array('approved' => 1, 'userid' => $u1->id));
        $e1a3 = $gg->create_content($g1, array('approved' => 1, 'userid' => $u1->id));
        $e1b1 = $gg->create_content($g1, array('approved' => 0, 'userid' => $u2->id));
        $e1b2 = $gg->create_content($g1, array('approved' => 1, 'userid' => $u2->id));
        $e1c1 = $gg->create_content($g1, array('approved' => 1, 'userid' => $u3->id));
        $e1d1 = $gg->create_content($g1, array('approved' => 1, 'userid' => $u4->id));
        $e2a = $gg->create_content($g2, array('approved' => 1, 'userid' => $u1->id));

        $this->setUser($u1);

                $return = mod_glossary_external::get_entries_by_author($g1->id, 'u', 'LASTNAME', 'ASC', 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_author_returns(), $return);
        $this->assertCount(4, $return['entries']);
        $this->assertEquals(4, $return['count']);
        $this->assertEquals($e1b2->id, $return['entries'][0]['id']);
        $this->assertEquals($e1a1->id, $return['entries'][1]['id']);
        $this->assertEquals($e1a2->id, $return['entries'][2]['id']);
        $this->assertEquals($e1a3->id, $return['entries'][3]['id']);

                $return = mod_glossary_external::get_entries_by_author($g1->id, 'SPECIAL', 'LASTNAME', 'ASC', 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_author_returns(), $return);
        $this->assertCount(1, $return['entries']);
        $this->assertEquals(1, $return['count']);
        $this->assertEquals($e1d1->id, $return['entries'][0]['id']);

                $return = mod_glossary_external::get_entries_by_author($g1->id, 'ALL', 'LASTNAME', 'ASC', 0, 1, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_author_returns(), $return);
        $this->assertCount(1, $return['entries']);
        $this->assertEquals(6, $return['count']);
        $this->assertEquals($e1d1->id, $return['entries'][0]['id']);
        $return = mod_glossary_external::get_entries_by_author($g1->id, 'ALL', 'LASTNAME', 'ASC', 1, 2, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_author_returns(), $return);
        $this->assertCount(2, $return['entries']);
        $this->assertEquals(6, $return['count']);
        $this->assertEquals($e1c1->id, $return['entries'][0]['id']);
        $this->assertEquals($e1b2->id, $return['entries'][1]['id']);

                $this->setAdminUser();
        $return = mod_glossary_external::get_entries_by_author($g1->id, 'ALL', 'LASTNAME', 'ASC', 0, 20,
            array('includenotapproved' => true));
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_author_returns(), $return);
        $this->assertCount(7, $return['entries']);
        $this->assertEquals(7, $return['count']);
        $this->assertEquals($e1d1->id, $return['entries'][0]['id']);
        $this->assertEquals($e1c1->id, $return['entries'][1]['id']);
        $this->assertEquals($e1b1->id, $return['entries'][2]['id']);
        $this->assertEquals($e1b2->id, $return['entries'][3]['id']);
        $this->assertEquals($e1a1->id, $return['entries'][4]['id']);
        $this->assertEquals($e1a2->id, $return['entries'][5]['id']);
        $this->assertEquals($e1a3->id, $return['entries'][6]['id']);

                $return = mod_glossary_external::get_entries_by_author($g1->id, 'ALL', 'LASTNAME', 'DESC', 0, 1, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_author_returns(), $return);
        $this->assertCount(1, $return['entries']);
        $this->assertEquals(6, $return['count']);
        $this->assertEquals($e1a1->id, $return['entries'][0]['id']);

                $return = mod_glossary_external::get_entries_by_author($g1->id, 'ALL', 'FIRSTNAME', 'ASC', 0, 1, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_author_returns(), $return);
        $this->assertCount(1, $return['entries']);
        $this->assertEquals(6, $return['count']);
        $this->assertEquals($e1b2->id, $return['entries'][0]['id']);

                $return = mod_glossary_external::get_entries_by_author($g1->id, 'ALL', 'FIRSTNAME', 'DESC', 0, 1, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_author_returns(), $return);
        $this->assertCount(1, $return['entries']);
        $this->assertEquals(6, $return['count']);
        $this->assertEquals($e1d1->id, $return['entries'][0]['id']);

                $return = mod_glossary_external::get_entries_by_author($g1->id, 'z', 'FIRSTNAME', 'DESC', 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_author_returns(), $return);
        $this->assertCount(4, $return['entries']);
        $this->assertEquals(4, $return['count']);
        $this->assertEquals($e1d1->id, $return['entries'][0]['id']);
        $this->assertEquals($e1a1->id, $return['entries'][1]['id']);
        $this->assertEquals($e1a2->id, $return['entries'][2]['id']);
        $this->assertEquals($e1a3->id, $return['entries'][3]['id']);

                delete_user($u2);
        $return = mod_glossary_external::get_entries_by_author($g1->id, 'u', 'LASTNAME', 'ASC', 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_author_returns(), $return);
        $this->assertCount(4, $return['entries']);
        $this->assertEquals(4, $return['count']);
        $this->assertEquals($e1b2->id, $return['entries'][0]['id']);
        $this->assertEquals($e1a1->id, $return['entries'][1]['id']);
        $this->assertEquals($e1a2->id, $return['entries'][2]['id']);
        $this->assertEquals($e1a3->id, $return['entries'][3]['id']);
    }

    public function test_get_entries_by_author_id() {
        $this->resetAfterTest(true);

                $gg = $this->getDataGenerator()->get_plugin_generator('mod_glossary');
        $c1 = $this->getDataGenerator()->create_course();
        $g1 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id, 'displayformat' => 'entrylist'));
        $g2 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id, 'displayformat' => 'entrylist'));
        $u1 = $this->getDataGenerator()->create_user(array('lastname' => 'Upsilon', 'firstname' => 'Zac'));
        $u2 = $this->getDataGenerator()->create_user(array('lastname' => 'Ultra', 'firstname' => '1337'));
        $u3 = $this->getDataGenerator()->create_user(array('lastname' => 'Alpha', 'firstname' => 'Omega'));
        $u4 = $this->getDataGenerator()->create_user(array('lastname' => '0-day', 'firstname' => 'Zoe'));
        $ctx = context_module::instance($g1->cmid);
        $this->getDataGenerator()->enrol_user($u1->id, $c1->id);

        $e1a1 = $gg->create_content($g1, array('approved' => 1, 'userid' => $u1->id, 'concept' => 'Zoom',
            'timecreated' => 3600, 'timemodified' => time() - 3600));
        $e1a2 = $gg->create_content($g1, array('approved' => 1, 'userid' => $u1->id, 'concept' => 'Alpha'));
        $e1a3 = $gg->create_content($g1, array('approved' => 1, 'userid' => $u1->id, 'concept' => 'Dog',
            'timecreated' => 1, 'timemodified' => time() - 1800));
        $e1a4 = $gg->create_content($g1, array('approved' => 0, 'userid' => $u1->id, 'concept' => 'Bird'));
        $e1b1 = $gg->create_content($g1, array('approved' => 0, 'userid' => $u2->id));
        $e2a = $gg->create_content($g2, array('approved' => 1, 'userid' => $u1->id));

        $this->setAdminUser();

                $return = mod_glossary_external::get_entries_by_author_id($g1->id, $u1->id, 'CONCEPT', 'ASC', 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_author_id_returns(), $return);
        $this->assertCount(3, $return['entries']);
        $this->assertEquals(3, $return['count']);
        $this->assertEquals($e1a2->id, $return['entries'][0]['id']);
        $this->assertEquals($e1a3->id, $return['entries'][1]['id']);
        $this->assertEquals($e1a1->id, $return['entries'][2]['id']);

                $return = mod_glossary_external::get_entries_by_author_id($g1->id, $u1->id, 'CONCEPT', 'DESC', 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_author_id_returns(), $return);
        $this->assertCount(3, $return['entries']);
        $this->assertEquals(3, $return['count']);
        $this->assertEquals($e1a1->id, $return['entries'][0]['id']);
        $this->assertEquals($e1a3->id, $return['entries'][1]['id']);
        $this->assertEquals($e1a2->id, $return['entries'][2]['id']);

                $return = mod_glossary_external::get_entries_by_author_id($g1->id, $u1->id, 'CREATION', 'ASC', 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_author_id_returns(), $return);
        $this->assertCount(3, $return['entries']);
        $this->assertEquals(3, $return['count']);
        $this->assertEquals($e1a3->id, $return['entries'][0]['id']);
        $this->assertEquals($e1a1->id, $return['entries'][1]['id']);
        $this->assertEquals($e1a2->id, $return['entries'][2]['id']);

                $return = mod_glossary_external::get_entries_by_author_id($g1->id, $u1->id, 'CREATION', 'DESC', 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_author_id_returns(), $return);
        $this->assertCount(3, $return['entries']);
        $this->assertEquals(3, $return['count']);
        $this->assertEquals($e1a2->id, $return['entries'][0]['id']);
        $this->assertEquals($e1a1->id, $return['entries'][1]['id']);
        $this->assertEquals($e1a3->id, $return['entries'][2]['id']);

                $return = mod_glossary_external::get_entries_by_author_id($g1->id, $u1->id, 'UPDATE', 'ASC', 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_author_id_returns(), $return);
        $this->assertCount(3, $return['entries']);
        $this->assertEquals(3, $return['count']);
        $this->assertEquals($e1a1->id, $return['entries'][0]['id']);
        $this->assertEquals($e1a3->id, $return['entries'][1]['id']);
        $this->assertEquals($e1a2->id, $return['entries'][2]['id']);

                $return = mod_glossary_external::get_entries_by_author_id($g1->id, $u1->id, 'UPDATE', 'DESC', 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_author_id_returns(), $return);
        $this->assertCount(3, $return['entries']);
        $this->assertEquals(3, $return['count']);
        $this->assertEquals($e1a2->id, $return['entries'][0]['id']);
        $this->assertEquals($e1a3->id, $return['entries'][1]['id']);
        $this->assertEquals($e1a1->id, $return['entries'][2]['id']);

                $return = mod_glossary_external::get_entries_by_author_id($g1->id, $u1->id, 'CONCEPT', 'ASC', 0, 20,
            array('includenotapproved' => true));
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_author_id_returns(), $return);
        $this->assertCount(4, $return['entries']);
        $this->assertEquals(4, $return['count']);
        $this->assertEquals($e1a2->id, $return['entries'][0]['id']);
        $this->assertEquals($e1a4->id, $return['entries'][1]['id']);
        $this->assertEquals($e1a3->id, $return['entries'][2]['id']);
        $this->assertEquals($e1a1->id, $return['entries'][3]['id']);

                $return = mod_glossary_external::get_entries_by_author_id($g1->id, $u1->id, 'CONCEPT', 'ASC', 0, 2,
            array('includenotapproved' => true));
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_author_id_returns(), $return);
        $this->assertCount(2, $return['entries']);
        $this->assertEquals(4, $return['count']);
        $this->assertEquals($e1a2->id, $return['entries'][0]['id']);
        $this->assertEquals($e1a4->id, $return['entries'][1]['id']);
        $return = mod_glossary_external::get_entries_by_author_id($g1->id, $u1->id, 'CONCEPT', 'ASC', 1, 2,
            array('includenotapproved' => true));
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_author_id_returns(), $return);
        $this->assertCount(2, $return['entries']);
        $this->assertEquals(4, $return['count']);
        $this->assertEquals($e1a4->id, $return['entries'][0]['id']);
        $this->assertEquals($e1a3->id, $return['entries'][1]['id']);
    }

    public function test_get_entries_by_search() {
        $this->resetAfterTest(true);

                $gg = $this->getDataGenerator()->get_plugin_generator('mod_glossary');
        $c1 = $this->getDataGenerator()->create_course();
        $g1 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id));
        $g2 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id));
        $u1 = $this->getDataGenerator()->create_user();
        $ctx = context_module::instance($g1->cmid);
        $this->getDataGenerator()->enrol_user($u1->id, $c1->id);
        $this->setUser($u1);

        $e1 = $gg->create_content($g1, array('approved' => 1, 'concept' => 'House', 'timecreated' => time() + 3600));
        $e2 = $gg->create_content($g1, array('approved' => 1, 'concept' => 'Mouse', 'timemodified' => 1));
        $e3 = $gg->create_content($g1, array('approved' => 1, 'concept' => 'Hero'));
        $e4 = $gg->create_content($g1, array('approved' => 0, 'concept' => 'Toulouse'));
        $e5 = $gg->create_content($g1, array('approved' => 1, 'definition' => 'Heroes', 'concept' => 'Abcd'));
        $e6 = $gg->create_content($g1, array('approved' => 0, 'definition' => 'When used for Heroes'));
        $e7 = $gg->create_content($g1, array('approved' => 1, 'timecreated' => 1, 'timemodified' => time() + 3600,
            'concept' => 'Z'), array('Couscous'));
        $e8 = $gg->create_content($g1, array('approved' => 0), array('Heroes'));
        $e9 = $gg->create_content($g2, array('approved' => 0));

        $this->setAdminUser();

                $query = 'hero';
        $return = mod_glossary_external::get_entries_by_search($g1->id, $query, false, 'CONCEPT', 'ASC', 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_search_returns(), $return);
        $this->assertCount(1, $return['entries']);
        $this->assertEquals(1, $return['count']);
        $this->assertEquals($e3->id, $return['entries'][0]['id']);

                $query = 'hero';
        $return = mod_glossary_external::get_entries_by_search($g1->id, $query, true, 'CONCEPT', 'ASC', 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_search_returns(), $return);
        $this->assertCount(2, $return['entries']);
        $this->assertEquals(2, $return['count']);
        $this->assertEquals($e5->id, $return['entries'][0]['id']);
        $this->assertEquals($e3->id, $return['entries'][1]['id']);

                $query = 'hero';
        $return = mod_glossary_external::get_entries_by_search($g1->id, $query, true, 'CONCEPT', 'DESC', 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_search_returns(), $return);
        $this->assertCount(2, $return['entries']);
        $this->assertEquals(2, $return['count']);
        $this->assertEquals($e3->id, $return['entries'][0]['id']);
        $this->assertEquals($e5->id, $return['entries'][1]['id']);

                $query = 'couscous';
        $return = mod_glossary_external::get_entries_by_search($g1->id, $query, false, 'CONCEPT', 'ASC', 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_search_returns(), $return);
        $this->assertCount(1, $return['entries']);
        $this->assertEquals(1, $return['count']);
        $this->assertEquals($e7->id, $return['entries'][0]['id']);
        $return = mod_glossary_external::get_entries_by_search($g1->id, $query, true, 'CONCEPT', 'ASC', 0, 20, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_search_returns(), $return);
        $this->assertCount(1, $return['entries']);
        $this->assertEquals(1, $return['count']);
        $this->assertEquals($e7->id, $return['entries'][0]['id']);

                $query = 'ou';
        $return = mod_glossary_external::get_entries_by_search($g1->id, $query, false, 'CREATION', 'ASC', 0, 1, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_search_returns(), $return);
        $this->assertCount(1, $return['entries']);
        $this->assertEquals(3, $return['count']);
        $this->assertEquals($e7->id, $return['entries'][0]['id']);
        $return = mod_glossary_external::get_entries_by_search($g1->id, $query, false, 'CREATION', 'DESC', 0, 1, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_search_returns(), $return);
        $this->assertCount(1, $return['entries']);
        $this->assertEquals(3, $return['count']);
        $this->assertEquals($e1->id, $return['entries'][0]['id']);

                $query = 'ou';
        $return = mod_glossary_external::get_entries_by_search($g1->id, $query, false, 'UPDATE', 'ASC', 0, 1, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_search_returns(), $return);
        $this->assertCount(1, $return['entries']);
        $this->assertEquals(3, $return['count']);
        $this->assertEquals($e2->id, $return['entries'][0]['id']);
        $return = mod_glossary_external::get_entries_by_search($g1->id, $query, false, 'UPDATE', 'DESC', 0, 1, array());
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_search_returns(), $return);
        $this->assertCount(1, $return['entries']);
        $this->assertEquals(3, $return['count']);
        $this->assertEquals($e7->id, $return['entries'][0]['id']);

                $query = 'ou';
        $return = mod_glossary_external::get_entries_by_search($g1->id, $query, false, 'CONCEPT', 'ASC', 0, 20,
            array('includenotapproved' => true));
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_search_returns(), $return);
        $this->assertCount(4, $return['entries']);
        $this->assertEquals(4, $return['count']);
        $this->assertEquals($e1->id, $return['entries'][0]['id']);
        $this->assertEquals($e2->id, $return['entries'][1]['id']);
        $this->assertEquals($e4->id, $return['entries'][2]['id']);
        $this->assertEquals($e7->id, $return['entries'][3]['id']);

                $query = '+Heroes -Abcd';
        $return = mod_glossary_external::get_entries_by_search($g1->id, $query, true, 'CONCEPT', 'ASC', 0, 20,
            array('includenotapproved' => true));
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_search_returns(), $return);
        $this->assertCount(2, $return['entries']);
        $this->assertEquals(2, $return['count']);
        $this->assertEquals($e6->id, $return['entries'][0]['id']);
        $this->assertEquals($e8->id, $return['entries'][1]['id']);
    }

    public function test_get_entries_by_term() {
        $this->resetAfterTest(true);

                $gg = $this->getDataGenerator()->get_plugin_generator('mod_glossary');
        $c1 = $this->getDataGenerator()->create_course();
        $g1 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id));
        $g2 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id));
        $u1 = $this->getDataGenerator()->create_user();
        $ctx = context_module::instance($g1->cmid);
        $this->getDataGenerator()->enrol_user($u1->id, $c1->id);

        $this->setAdminUser();

        $e1 = $gg->create_content($g1, array('userid' => $u1->id, 'approved' => 1, 'concept' => 'cat'));
        $e2 = $gg->create_content($g1, array('userid' => $u1->id, 'approved' => 1), array('cat', 'dog'));
        $e3 = $gg->create_content($g1, array('userid' => $u1->id, 'approved' => 1), array('dog'));
        $e4 = $gg->create_content($g1, array('userid' => $u1->id, 'approved' => 0, 'concept' => 'dog'));
        $e5 = $gg->create_content($g2, array('userid' => $u1->id, 'approved' => 1, 'concept' => 'dog'), array('cat'));

                $return = mod_glossary_external::get_entries_by_term($g1->id, 'cat', 0, 20, array('includenotapproved' => false));
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_term_returns(), $return);
        $this->assertCount(2, $return['entries']);
        $this->assertEquals(2, $return['count']);
                $expected = array($e1->id, $e2->id);
        $actual = array($return['entries'][0]['id'], $return['entries'][1]['id']);
        $this->assertEquals($expected, $actual, '', 0.0, 10, true);

                $return = mod_glossary_external::get_entries_by_term($g1->id, 'dog', 0, 20, array('includenotapproved' => false));
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_term_returns(), $return);

        $this->assertCount(2, $return['entries']);
        $this->assertEquals(2, $return['count']);
                $expected = array($e2->id, $e3->id);
        $actual = array($return['entries'][0]['id'], $return['entries'][1]['id']);
        $this->assertEquals($expected, $actual, '', 0.0, 10, true);

                $return = mod_glossary_external::get_entries_by_term($g1->id, 'dog', 0, 20, array('includenotapproved' => true));
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_term_returns(), $return);
        $this->assertCount(3, $return['entries']);
        $this->assertEquals(3, $return['count']);
                $expected = array($e4->id, $e2->id, $e3->id);
        $actual = array($return['entries'][0]['id'], $return['entries'][1]['id'], $return['entries'][2]['id']);
        $this->assertEquals($expected, $actual, '', 0.0, 10, true);

                $return = mod_glossary_external::get_entries_by_term($g1->id, 'dog', 0, 1, array('includenotapproved' => true));
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_term_returns(), $return);
        $this->assertCount(1, $return['entries']);
                        $this->assertEquals(3, $return['count']);
        $return = mod_glossary_external::get_entries_by_term($g1->id, 'dog', 1, 1, array('includenotapproved' => true));
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_by_term_returns(), $return);
        $this->assertCount(1, $return['entries']);
        $this->assertEquals(3, $return['count']);
    }

    public function test_get_entries_to_approve() {
        $this->resetAfterTest(true);

                $gg = $this->getDataGenerator()->get_plugin_generator('mod_glossary');
        $c1 = $this->getDataGenerator()->create_course();
        $g1 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id));
        $g2 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id));
        $u1 = $this->getDataGenerator()->create_user();
        $ctx = context_module::instance($g1->cmid);
        $this->getDataGenerator()->enrol_user($u1->id, $c1->id);

        $e1a = $gg->create_content($g1, array('approved' => 0, 'concept' => 'Bob', 'userid' => $u1->id,
            'timecreated' => time() + 3600));
        $e1b = $gg->create_content($g1, array('approved' => 0, 'concept' => 'Jane', 'userid' => $u1->id, 'timecreated' => 1));
        $e1c = $gg->create_content($g1, array('approved' => 0, 'concept' => 'Alice', 'userid' => $u1->id, 'timemodified' => 1));
        $e1d = $gg->create_content($g1, array('approved' => 0, 'concept' => '0-day', 'userid' => $u1->id,
            'timemodified' => time() + 3600));
        $e1e = $gg->create_content($g1, array('approved' => 1, 'concept' => '1-day', 'userid' => $u1->id));
        $e2a = $gg->create_content($g2);

        $this->setAdminUser(true);

                $return = mod_glossary_external::get_entries_to_approve($g1->id, 'ALL', 'CONCEPT', 'ASC', 0, 20);
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_to_approve_returns(), $return);
        $this->assertCount(4, $return['entries']);
        $this->assertEquals(4, $return['count']);
        $this->assertEquals($e1d->id, $return['entries'][0]['id']);
        $this->assertEquals($e1c->id, $return['entries'][1]['id']);
        $this->assertEquals($e1a->id, $return['entries'][2]['id']);
        $this->assertEquals($e1b->id, $return['entries'][3]['id']);

                $return = mod_glossary_external::get_entries_to_approve($g1->id, 'ALL', 'CONCEPT', 'DESC', 0, 20);
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_to_approve_returns(), $return);
        $this->assertCount(4, $return['entries']);
        $this->assertEquals(4, $return['count']);
        $this->assertEquals($e1b->id, $return['entries'][0]['id']);
        $this->assertEquals($e1a->id, $return['entries'][1]['id']);
        $this->assertEquals($e1c->id, $return['entries'][2]['id']);
        $this->assertEquals($e1d->id, $return['entries'][3]['id']);

                $return = mod_glossary_external::get_entries_to_approve($g1->id, 'a', 'CONCEPT', 'ASC', 0, 20);
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_to_approve_returns(), $return);
        $this->assertCount(1, $return['entries']);
        $this->assertEquals(1, $return['count']);
        $this->assertEquals($e1c->id, $return['entries'][0]['id']);

                $return = mod_glossary_external::get_entries_to_approve($g1->id, 'SPECIAL', 'CONCEPT', 'ASC', 0, 20);
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_to_approve_returns(), $return);
        $this->assertCount(1, $return['entries']);
        $this->assertEquals(1, $return['count']);
        $this->assertEquals($e1d->id, $return['entries'][0]['id']);

                $return = mod_glossary_external::get_entries_to_approve($g1->id, 'ALL', 'CONCEPT', 'ASC', 0, 2);
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_to_approve_returns(), $return);
        $this->assertCount(2, $return['entries']);
        $this->assertEquals(4, $return['count']);
        $this->assertEquals($e1d->id, $return['entries'][0]['id']);
        $this->assertEquals($e1c->id, $return['entries'][1]['id']);
        $return = mod_glossary_external::get_entries_to_approve($g1->id, 'ALL', 'CONCEPT', 'ASC', 1, 2);
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_to_approve_returns(), $return);
        $this->assertCount(2, $return['entries']);
        $this->assertEquals(4, $return['count']);
        $this->assertEquals($e1c->id, $return['entries'][0]['id']);
        $this->assertEquals($e1a->id, $return['entries'][1]['id']);

                $return = mod_glossary_external::get_entries_to_approve($g1->id, 'ALL', 'CREATION', 'ASC', 0, 1);
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_to_approve_returns(), $return);
        $this->assertCount(1, $return['entries']);
        $this->assertEquals(4, $return['count']);
        $this->assertEquals($e1b->id, $return['entries'][0]['id']);

                $return = mod_glossary_external::get_entries_to_approve($g1->id, 'ALL', 'CREATION', 'DESC', 0, 1);
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_to_approve_returns(), $return);
        $this->assertCount(1, $return['entries']);
        $this->assertEquals(4, $return['count']);
        $this->assertEquals($e1a->id, $return['entries'][0]['id']);

                $return = mod_glossary_external::get_entries_to_approve($g1->id, 'ALL', 'UPDATE', 'ASC', 0, 1);
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_to_approve_returns(), $return);
        $this->assertCount(1, $return['entries']);
        $this->assertEquals(4, $return['count']);
        $this->assertEquals($e1c->id, $return['entries'][0]['id']);

                $return = mod_glossary_external::get_entries_to_approve($g1->id, 'ALL', 'UPDATE', 'DESC', 0, 1);
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entries_to_approve_returns(), $return);
        $this->assertCount(1, $return['entries']);
        $this->assertEquals(4, $return['count']);
        $this->assertEquals($e1d->id, $return['entries'][0]['id']);

                $this->setUser($u1);
        $this->setExpectedException('required_capability_exception');
        mod_glossary_external::get_entries_to_approve($g1->id, 'ALL', 'CONCEPT', 'ASC', 0, 1);
        $this->fail('Do not test anything else after this.');
    }

    public function test_get_entry_by_id() {
        $this->resetAfterTest(true);

                $gg = $this->getDataGenerator()->get_plugin_generator('mod_glossary');
        $c1 = $this->getDataGenerator()->create_course();
        $c2 = $this->getDataGenerator()->create_course();
        $g1 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id));
        $g2 = $this->getDataGenerator()->create_module('glossary', array('course' => $c1->id, 'visible' => 0));
        $u1 = $this->getDataGenerator()->create_user();
        $u2 = $this->getDataGenerator()->create_user();
        $ctx = context_module::instance($g1->cmid);
        $this->getDataGenerator()->enrol_user($u1->id, $c1->id);

        $e1 = $gg->create_content($g1, array('approved' => 1, 'userid' => $u1->id));
        $e2 = $gg->create_content($g1, array('approved' => 0, 'userid' => $u1->id));
        $e3 = $gg->create_content($g1, array('approved' => 0, 'userid' => $u2->id));
        $e4 = $gg->create_content($g2, array('approved' => 1));

        $this->setUser($u1);
        $return = mod_glossary_external::get_entry_by_id($e1->id);
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entry_by_id_returns(), $return);
        $this->assertEquals($e1->id, $return['entry']['id']);

        $return = mod_glossary_external::get_entry_by_id($e2->id);
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entry_by_id_returns(), $return);
        $this->assertEquals($e2->id, $return['entry']['id']);

        try {
            $return = mod_glossary_external::get_entry_by_id($e3->id);
            $this->fail('Cannot view unapproved entries of others.');
        } catch (invalid_parameter_exception $e) {
                    }

        try {
            $return = mod_glossary_external::get_entry_by_id($e4->id);
            $this->fail('Cannot view entries from another course.');
        } catch (require_login_exception $e) {
                    }

                $this->setAdminUser();
        $return = mod_glossary_external::get_entry_by_id($e3->id);
        $return = external_api::clean_returnvalue(mod_glossary_external::get_entry_by_id_returns(), $return);
        $this->assertEquals($e3->id, $return['entry']['id']);
    }

}
