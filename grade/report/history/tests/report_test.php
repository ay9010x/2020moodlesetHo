<?php



defined('MOODLE_INTERNAL') || die();


class gradereport_history_report_testcase extends advanced_testcase {

    
    public function test_query_db() {
        $this->resetAfterTest();

                $c1 = $this->getDataGenerator()->create_course();
        $c2 = $this->getDataGenerator()->create_course();
        $c1ctx = context_course::instance($c1->id);
        $c2ctx = context_course::instance($c2->id);

                $u1 = $this->getDataGenerator()->create_user();
        $u2 = $this->getDataGenerator()->create_user();
        $u3 = $this->getDataGenerator()->create_user();
        $u4 = $this->getDataGenerator()->create_user();
        $u5 = $this->getDataGenerator()->create_user();
        $grader1 = $this->getDataGenerator()->create_user();
        $grader2 = $this->getDataGenerator()->create_user();

                $c1m1 = $this->getDataGenerator()->create_module('assign', array('course' => $c1));
        $c1m2 = $this->getDataGenerator()->create_module('assign', array('course' => $c1));
        $c1m3 = $this->getDataGenerator()->create_module('assign', array('course' => $c1));
        $c2m1 = $this->getDataGenerator()->create_module('assign', array('course' => $c2));
        $c2m2 = $this->getDataGenerator()->create_module('assign', array('course' => $c2));

                $giparams = array('itemtype' => 'mod', 'itemmodule' => 'assign');
        $grades = array();

        $gi = grade_item::fetch($giparams + array('iteminstance' => $c1m1->id));
        $grades['c1m1u1'] = $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u1->id,
                'timemodified' => time() - 3600));
        $grades['c1m1u2'] = $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u2->id,
                'timemodified' => time() + 3600));
        $grades['c1m1u3'] = $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u3->id));
        $grades['c1m1u4'] = $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u4->id));
        $grades['c1m1u5'] = $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u5->id));

        $gi = grade_item::fetch($giparams + array('iteminstance' => $c1m2->id));
        $grades['c1m2u1'] = $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u1->id));
        $grades['c1m2u2'] = $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u2->id));

        $gi = grade_item::fetch($giparams + array('iteminstance' => $c1m3->id));
        $grades['c1m3u1'] = $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u1->id));

        $gi = grade_item::fetch($giparams + array('iteminstance' => $c2m1->id));
        $grades['c2m1u1'] = $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u1->id,
            'usermodified' => $grader1->id));
        $grades['c2m1u2'] = $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u2->id,
            'usermodified' => $grader1->id));
        $grades['c2m1u3'] = $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u3->id,
            'usermodified' => $grader1->id));
        $grades['c2m1u4'] = $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u4->id,
            'usermodified' => $grader2->id));

                $grades['c2m1u5a'] = $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u5->id,
            'timemodified' => time() - 60));
        $grades['c2m1u5b'] = $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u5->id,
            'timemodified' => time()));
        $grades['c2m1u5c'] = $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u5->id,
            'timemodified' => time() + 60));

                $now = time();
        $gi = grade_item::fetch($giparams + array('iteminstance' => $c2m2->id));
        $grades['c2m2u1a'] = $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u1->id,
            'timemodified' => $now - 60, 'finalgrade' => 50));
        $grades['c2m2u1b'] = $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u1->id,
            'timemodified' => $now - 50, 'finalgrade' => 50));              $grades['c2m2u1c'] = $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u1->id,
            'timemodified' => $now, 'finalgrade' => 75));
        $grades['c2m2u1d'] = $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u1->id,
            'timemodified' => $now + 10, 'finalgrade' => 75));              $grades['c2m2u1e'] = $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u1->id,
            'timemodified' => $now + 60, 'finalgrade' => 25));
        $grades['c2m2u1f'] = $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u1->id,
            'timemodified' => $now + 70, 'finalgrade' => 25));      
                                
                $this->assertEquals(8, $this->get_tablelog_results($c1ctx, array(), true));
        $this->assertEquals(13, $this->get_tablelog_results($c2ctx, array(), true));

                $this->assertEquals(3, $this->get_tablelog_results($c1ctx, array('userids' => $u1->id), true));

                $this->assertEquals(4, $this->get_tablelog_results($c1ctx, array('userids' => "$u1->id,$u3->id"), true));

                $gi = grade_item::fetch($giparams + array('iteminstance' => $c1m1->id));
        $this->assertEquals(5, $this->get_tablelog_results($c1ctx, array('itemid' => $gi->id), true));
        $gi = grade_item::fetch($giparams + array('iteminstance' => $c1m3->id));
        $this->assertEquals(1, $this->get_tablelog_results($c1ctx, array('itemid' => $gi->id), true));

                $this->assertEquals(3, $this->get_tablelog_results($c2ctx, array('grader' => $grader1->id), true));
        $this->assertEquals(1, $this->get_tablelog_results($c2ctx, array('grader' => $grader2->id), true));

                $results = $this->get_tablelog_results($c1ctx, array('datefrom' => time() + 1800));
        $this->assertGradeHistoryIds(array($grades['c1m1u2']->id), $results);
        $results = $this->get_tablelog_results($c1ctx, array('datetill' => time() - 1800));
        $this->assertGradeHistoryIds(array($grades['c1m1u1']->id), $results);
        $results = $this->get_tablelog_results($c1ctx, array('datefrom' => time() - 1800, 'datetill' => time() + 1800));
        $this->assertGradeHistoryIds(array($grades['c1m1u3']->id, $grades['c1m1u4']->id, $grades['c1m1u5']->id,
            $grades['c1m2u1']->id, $grades['c1m2u2']->id, $grades['c1m3u1']->id), $results);

                $this->assertEquals(3, $this->get_tablelog_results($c2ctx, array('userids' => $u5->id), true));
        $this->assertEquals(1, $this->get_tablelog_results($c2ctx, array('userids' => $u5->id, 'revisedonly' => true), true));

                $gi = grade_item::fetch($giparams + array('iteminstance' => $c2m2->id));
        $this->assertEquals(6, $this->get_tablelog_results($c2ctx, array('userids' => $u1->id, 'itemid' => $gi->id), true));
        $results = $this->get_tablelog_results($c2ctx, array('userids' => $u1->id, 'itemid' => $gi->id, 'revisedonly' => true));
        $this->assertGradeHistoryIds(array($grades['c2m2u1a']->id, $grades['c2m2u1c']->id, $grades['c2m2u1e']->id), $results);

                $this->assertEquals(null, $results[$grades['c2m2u1a']->id]->prevgrade);
        $this->assertEquals($grades['c2m2u1a']->finalgrade, $results[$grades['c2m2u1c']->id]->prevgrade);
        $this->assertEquals($grades['c2m2u1c']->finalgrade, $results[$grades['c2m2u1e']->id]->prevgrade);
    }

    
    public function test_get_users() {
        $this->resetAfterTest();

                $c1 = $this->getDataGenerator()->create_course();
        $c2 = $this->getDataGenerator()->create_course();
        $c1ctx = context_course::instance($c1->id);
        $c2ctx = context_course::instance($c2->id);

        $c1m1 = $this->getDataGenerator()->create_module('assign', array('course' => $c1));
        $c2m1 = $this->getDataGenerator()->create_module('assign', array('course' => $c2));

                $u1 = $this->getDataGenerator()->create_user(array('firstname' => 'Eric', 'lastname' => 'Cartman'));
        $u2 = $this->getDataGenerator()->create_user(array('firstname' => 'Stan', 'lastname' => 'Marsh'));
        $u3 = $this->getDataGenerator()->create_user(array('firstname' => 'Kyle', 'lastname' => 'Broflovski'));
        $u4 = $this->getDataGenerator()->create_user(array('firstname' => 'Kenny', 'lastname' => 'McCormick'));

                $gi = grade_item::fetch(array('iteminstance' => $c1m1->id, 'itemtype' => 'mod', 'itemmodule' => 'assign'));
        $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u1->id));
        $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u2->id));
        $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u3->id));

        $gi = grade_item::fetch(array('iteminstance' => $c2m1->id, 'itemtype' => 'mod', 'itemmodule' => 'assign'));
        $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u4->id));

                $users = \gradereport_history\helper::get_users($c1ctx);
        $this->assertCount(3, $users);
        $this->assertArrayHasKey($u3->id, $users);
        $users = \gradereport_history\helper::get_users($c2ctx);
        $this->assertCount(1, $users);
        $this->assertArrayHasKey($u4->id, $users);
        $users = \gradereport_history\helper::get_users($c1ctx, 'c');
        $this->assertCount(1, $users);
        $this->assertArrayHasKey($u1->id, $users);
        $users = \gradereport_history\helper::get_users($c1ctx, '', 0, 2);
        $this->assertCount(2, $users);
        $this->assertArrayHasKey($u3->id, $users);
        $this->assertArrayHasKey($u1->id, $users);
        $users = \gradereport_history\helper::get_users($c1ctx, '', 1, 2);
        $this->assertCount(1, $users);
        $this->assertArrayHasKey($u2->id, $users);

                $this->assertEquals(3, \gradereport_history\helper::get_users_count($c1ctx));
        $this->assertEquals(1, \gradereport_history\helper::get_users_count($c2ctx));
        $this->assertEquals(1, \gradereport_history\helper::get_users_count($c1ctx, 'c'));
    }

    
    public function test_graders() {
        $this->resetAfterTest();

                $c1 = $this->getDataGenerator()->create_course();
        $c2 = $this->getDataGenerator()->create_course();

        $c1m1 = $this->getDataGenerator()->create_module('assign', array('course' => $c1));
        $c2m1 = $this->getDataGenerator()->create_module('assign', array('course' => $c2));

                $u1 = $this->getDataGenerator()->create_user(array('firstname' => 'Eric', 'lastname' => 'Cartman'));
        $u2 = $this->getDataGenerator()->create_user(array('firstname' => 'Stan', 'lastname' => 'Marsh'));
        $u3 = $this->getDataGenerator()->create_user(array('firstname' => 'Kyle', 'lastname' => 'Broflovski'));
        $u4 = $this->getDataGenerator()->create_user(array('firstname' => 'Kenny', 'lastname' => 'McCormick'));

                $gi = grade_item::fetch(array('iteminstance' => $c1m1->id, 'itemtype' => 'mod', 'itemmodule' => 'assign'));
        $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u1->id, 'usermodified' => $u1->id));
        $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u1->id, 'usermodified' => $u2->id));
        $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u1->id, 'usermodified' => $u3->id));

        $gi = grade_item::fetch(array('iteminstance' => $c2m1->id, 'itemtype' => 'mod', 'itemmodule' => 'assign'));
        $this->create_grade_history(array('itemid' => $gi->id, 'userid' => $u1->id, 'usermodified' => $u4->id));

                $graders = \gradereport_history\helper::get_graders($c1->id);
        $this->assertCount(4, $graders);         $this->assertArrayHasKey($u1->id, $graders);
        $this->assertArrayHasKey($u2->id, $graders);
        $this->assertArrayHasKey($u3->id, $graders);
        $graders = \gradereport_history\helper::get_graders($c2->id);
        $this->assertCount(2, $graders);         $this->assertArrayHasKey($u4->id, $graders);
    }

    
    protected function assertGradeHistoryIds(array $expectedids, array $objects) {
        $this->assertCount(count($expectedids), $objects);
        $expectedids = array_flip($expectedids);
        foreach ($objects as $object) {
            $this->assertArrayHasKey($object->id, $expectedids);
            unset($expectedids[$object->id]);
        }
        $this->assertCount(0, $expectedids);
    }

    
    protected function create_grade_history($params) {
        global $DB;
        $params = (array) $params;

        if (!isset($params['itemid'])) {
            throw new coding_exception('Missing itemid key.');
        }
        if (!isset($params['userid'])) {
            throw new coding_exception('Missing userid key.');
        }

                $grade = new stdClass();
        $grade->itemid = 0;
        $grade->userid = 0;
        $grade->oldid = 123;
        $grade->rawgrade = 50;
        $grade->finalgrade = 50;
        $grade->timecreated = time();
        $grade->timemodified = time();
        $grade->information = '';
        $grade->informationformat = FORMAT_PLAIN;
        $grade->feedback = '';
        $grade->feedbackformat = FORMAT_PLAIN;
        $grade->usermodified = 2;

                $grade = (object) array_merge((array) $grade, $params);

                $grade->id = $DB->insert_record('grade_grades_history', $grade);

        return $grade;
    }

    
    protected function get_tablelog_results($coursecontext, $filters = array(), $count = false) {
        $table = new gradereport_history_tests_tablelog('something', $coursecontext, new moodle_url(''), $filters);
        return $table->get_test_results($count);
    }

}


class gradereport_history_tests_tablelog extends \gradereport_history\output\tablelog {

    
    public function get_test_results($count = false) {
        global $DB;
        if ($count) {
            list($sql, $params) = $this->get_sql_and_params(true);
            return $DB->count_records_sql($sql, $params);
        } else {
            $this->setup();
            list($sql, $params) = $this->get_sql_and_params();
            return $DB->get_records_sql($sql, $params);
        }
    }

}
