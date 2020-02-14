<?php



defined('MOODLE_INTERNAL') || die();


class core_grouplib_testcase extends advanced_testcase {

    public function test_groups_get_group_by_idnumber() {
        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator();

                $cat = $generator->create_category(array('parent' => 0));
        $course = $generator->create_course(array('category' => $cat->id));

        $idnumber1 = 'idnumber1';
        $idnumber2 = 'idnumber2';

        
                $this->assertFalse(groups_get_group_by_idnumber($course->id, ''));
        $this->assertFalse(groups_get_group_by_idnumber($course->id, null));

                $generator->create_group(array('courseid' => $course->id));
        $this->assertFalse(groups_get_group_by_idnumber($course->id, ''));
        $this->assertFalse(groups_get_group_by_idnumber($course->id, null));

        
                $this->assertFalse(groups_get_group_by_idnumber($course->id, $idnumber1));

                $group = $generator->create_group(array('courseid' => $course->id, 'idnumber' => $idnumber1));
        $this->assertEquals($group, groups_get_group_by_idnumber($course->id, $idnumber1));

                $this->assertFalse(groups_get_group_by_idnumber($course->id, ''));
        $this->assertFalse(groups_get_group_by_idnumber($course->id, null));

        
                $this->assertFalse(groups_get_group_by_idnumber($course->id, $idnumber2));

                $group = $generator->create_group(array('courseid' => $course->id, 'idnumber' => $idnumber2));
        $this->assertEquals($group, groups_get_group_by_idnumber($course->id, $idnumber2));

        

                $course = $generator->create_course(array('category' => $cat->id));

                $this->assertFalse(groups_get_group_by_idnumber($course->id, ''));
        $this->assertFalse(groups_get_group_by_idnumber($course->id, null));

                $this->assertFalse(groups_get_group_by_idnumber($course->id, $idnumber1));
        $this->assertFalse(groups_get_group_by_idnumber($course->id, $idnumber2));

                $group = $generator->create_group(array('courseid' => $course->id, 'idnumber' => $idnumber1));
        $this->assertEquals($group, groups_get_group_by_idnumber($course->id, $idnumber1));

        $group = $generator->create_group(array('courseid' => $course->id, 'idnumber' => $idnumber2));
        $this->assertEquals($group, groups_get_group_by_idnumber($course->id, $idnumber2));
    }

    public function test_groups_get_grouping_by_idnumber() {
        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator();

                $cat = $generator->create_category(array('parent' => 0));
        $course = $generator->create_course(array('category' => $cat->id));

        $idnumber1 = 'idnumber1';
        $idnumber2 = 'idnumber2';

        
                $this->assertFalse(groups_get_grouping_by_idnumber($course->id, ''));
        $this->assertFalse(groups_get_grouping_by_idnumber($course->id, null));

                $generator->create_grouping(array('courseid' => $course->id));
        $this->assertFalse(groups_get_grouping_by_idnumber($course->id, ''));
        $this->assertFalse(groups_get_grouping_by_idnumber($course->id, null));

        
                $this->assertFalse(groups_get_grouping_by_idnumber($course->id, $idnumber1));

                $grouping = $generator->create_grouping(array('courseid' => $course->id, 'idnumber' => $idnumber1));
        $this->assertEquals($grouping, groups_get_grouping_by_idnumber($course->id, $idnumber1));

                $this->assertFalse(groups_get_grouping_by_idnumber($course->id, ''));
        $this->assertFalse(groups_get_grouping_by_idnumber($course->id, null));

        
                $this->assertFalse(groups_get_grouping_by_idnumber($course->id, $idnumber2));

                $grouping = $generator->create_grouping(array('courseid' => $course->id, 'idnumber' => $idnumber2));
        $this->assertEquals($grouping, groups_get_grouping_by_idnumber($course->id, $idnumber2));

        

                $course = $generator->create_course(array('category' => $cat->id));

                $this->assertFalse(groups_get_grouping_by_idnumber($course->id, ''));
        $this->assertFalse(groups_get_grouping_by_idnumber($course->id, null));

                $this->assertFalse(groups_get_grouping_by_idnumber($course->id, $idnumber1));
        $this->assertFalse(groups_get_grouping_by_idnumber($course->id, $idnumber2));

                $grouping = $generator->create_grouping(array('courseid' => $course->id, 'idnumber' => $idnumber1));
        $this->assertEquals($grouping, groups_get_grouping_by_idnumber($course->id, $idnumber1));

        $grouping = $generator->create_grouping(array('courseid' => $course->id, 'idnumber' => $idnumber2));
        $this->assertEquals($grouping, groups_get_grouping_by_idnumber($course->id, $idnumber2));
    }

    public function test_groups_get_group_by_name() {
        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator();

                $cat = $generator->create_category(array('parent' => 0));
        $course = $generator->create_course(array('category' => $cat->id));

        $name1 = 'Name 1';
        $name2 = 'Name 2';

                $this->assertFalse(groups_get_group_by_name($course->id, ''));
        $this->assertFalse(groups_get_group_by_name($course->id, null));

                $generator->create_group(array('courseid' => $course->id));
        $this->assertFalse(groups_get_group_by_name($course->id, ''));
        $this->assertFalse(groups_get_group_by_name($course->id, null));

                $this->assertFalse(groups_get_group_by_name($course->id, $name1));
        $this->assertFalse(groups_get_group_by_name($course->id, $name2));

                $group1 = $generator->create_group(array('courseid' => $course->id, 'name' => $name1));
        $this->assertEquals($group1->id, groups_get_group_by_name($course->id, $name1));
        $this->assertFalse(groups_get_group_by_name($course->id, $name2));

                $group2 = $generator->create_group(array('courseid' => $course->id, 'name' => $name2));
        $this->assertEquals($group1->id, groups_get_group_by_name($course->id, $name1));
        $this->assertEquals($group2->id, groups_get_group_by_name($course->id, $name2));

                $this->assertTrue(groups_delete_group($group1));
        $this->assertFalse(groups_get_group_by_name($course->id, $name1));
        $this->assertEquals($group2->id, groups_get_group_by_name($course->id, $name2));

        

                $course = $generator->create_course(array('category' => $cat->id));

                $this->assertFalse(groups_get_group_by_name($course->id, ''));
        $this->assertFalse(groups_get_group_by_name($course->id, null));

                $this->assertFalse(groups_get_group_by_name($course->id, $name1));
        $this->assertFalse(groups_get_group_by_name($course->id, $name2));

                $group1 = $generator->create_group(array('courseid' => $course->id, 'name' => $name1));
        $this->assertEquals($group1->id, groups_get_group_by_name($course->id, $name1));

        $group2 = $generator->create_group(array('courseid' => $course->id, 'name' => $name2));
        $this->assertEquals($group2->id, groups_get_group_by_name($course->id, $name2));
    }

    public function test_groups_get_grouping() {
        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator();

                $cat = $generator->create_category(array('parent' => 0));
        $course = $generator->create_course(array('category' => $cat->id));

        $name1 = 'Grouping 1';
        $name2 = 'Grouping 2';

                $this->assertFalse(groups_get_grouping_by_name($course->id, ''));
        $this->assertFalse(groups_get_grouping_by_name($course->id, null));

                $generator->create_group(array('courseid' => $course->id));
        $this->assertFalse(groups_get_grouping_by_name($course->id, ''));
        $this->assertFalse(groups_get_grouping_by_name($course->id, null));

                $this->assertFalse(groups_get_grouping_by_name($course->id, $name1));
        $this->assertFalse(groups_get_grouping_by_name($course->id, $name2));

                $group1 = $generator->create_grouping(array('courseid' => $course->id, 'name' => $name1));
        $this->assertEquals($group1->id, groups_get_grouping_by_name($course->id, $name1));
        $this->assertFalse(groups_get_grouping_by_name($course->id, $name2));

                $group2 = $generator->create_grouping(array('courseid' => $course->id, 'name' => $name2));
        $this->assertEquals($group1->id, groups_get_grouping_by_name($course->id, $name1));
        $this->assertEquals($group2->id, groups_get_grouping_by_name($course->id, $name2));

                $this->assertTrue(groups_delete_grouping($group1));
        $this->assertFalse(groups_get_grouping_by_name($course->id, $name1));
        $this->assertEquals($group2->id, groups_get_grouping_by_name($course->id, $name2));

        

                $course = $generator->create_course(array('category' => $cat->id));

                $this->assertFalse(groups_get_grouping_by_name($course->id, ''));
        $this->assertFalse(groups_get_grouping_by_name($course->id, null));

                $this->assertFalse(groups_get_grouping_by_name($course->id, $name1));
        $this->assertFalse(groups_get_grouping_by_name($course->id, $name2));

                $group1 = $generator->create_grouping(array('courseid' => $course->id, 'name' => $name1));
        $this->assertEquals($group1->id, groups_get_grouping_by_name($course->id, $name1));

        $group2 = $generator->create_grouping(array('courseid' => $course->id, 'name' => $name2));
        $this->assertEquals($group2->id, groups_get_grouping_by_name($course->id, $name2));
    }

    public function test_groups_get_course_data() {
        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator();

                $cat = $generator->create_category(array('parent' => 0));
        $course = $generator->create_course(array('category' => $cat->id));
        $grouping1 = $generator->create_grouping(array('courseid' => $course->id, 'name' => 'Grouping 1'));
        $grouping2 = $generator->create_grouping(array('courseid' => $course->id, 'name' => 'Grouping 2'));
        $group1 = $generator->create_group(array('courseid' => $course->id, 'name' => 'Group 1'));
        $group2 = $generator->create_group(array('courseid' => $course->id, 'name' => 'Group 2'));
        $group3 = $generator->create_group(array('courseid' => $course->id, 'name' => 'Group 3'));
        $group4 = $generator->create_group(array('courseid' => $course->id, 'name' => 'Group 4'));

                $this->assertTrue(groups_assign_grouping($grouping1->id, $group1->id));
        $this->assertTrue(groups_assign_grouping($grouping1->id, $group2->id));
        $this->assertTrue(groups_assign_grouping($grouping2->id, $group3->id));
        $this->assertTrue(groups_assign_grouping($grouping2->id, $group4->id));

                $data = groups_get_course_data($course->id);
        $this->assertInstanceOf('stdClass', $data);
        $this->assertObjectHasAttribute('groups', $data);
        $this->assertObjectHasAttribute('groupings', $data);
        $this->assertObjectHasAttribute('mappings', $data);

                $this->assertCount(4, $data->groups);
        $this->assertCount(2, $data->groupings);
        $this->assertCount(4, $data->mappings);

                $this->assertArrayHasKey($group1->id, $data->groups);
        $this->assertArrayHasKey($group2->id, $data->groups);
        $this->assertArrayHasKey($group3->id, $data->groups);
        $this->assertArrayHasKey($group4->id, $data->groups);

                $this->assertSame($group3->name, $data->groups[$group3->id]->name);

                $this->assertContains($grouping1->id, array_keys($data->groupings));
        $this->assertContains($grouping2->id, array_keys($data->groupings));

                $this->assertEquals($grouping2->name, $data->groupings[$grouping2->id]->name);

                $grouping1maps = 0;
        $grouping2maps = 0;
        $group1maps = 0;
        $group2maps = 0;
        $group3maps = 0;
        $group4maps = 0;
        foreach ($data->mappings as $mapping) {
            if ($mapping->groupingid === $grouping1->id) {
                $grouping1maps++;
                $this->assertContains($mapping->groupid, array($group1->id, $group2->id));
            } else if ($mapping->groupingid === $grouping2->id) {
                $grouping2maps++;
                $this->assertContains($mapping->groupid, array($group3->id, $group4->id));
            } else {
                $this->fail('Unexpected groupingid');
            }
            switch ($mapping->groupid) {
                case $group1->id : $group1maps++; break;
                case $group2->id : $group2maps++; break;
                case $group3->id : $group3maps++; break;
                case $group4->id : $group4maps++; break;
            }
        }
        $this->assertEquals(2, $grouping1maps);
        $this->assertEquals(2, $grouping2maps);
        $this->assertEquals(1, $group1maps);
        $this->assertEquals(1, $group2maps);
        $this->assertEquals(1, $group3maps);
        $this->assertEquals(1, $group4maps);

                $groups  = groups_get_all_groups($course->id);
        $groupkeys = array_keys($groups);
        $this->assertCount(4, $groups);
        $this->assertContains($group1->id, $groupkeys);
        $this->assertContains($group2->id, $groupkeys);
        $this->assertContains($group3->id, $groupkeys);
        $this->assertContains($group4->id, $groupkeys);

        $groups  = groups_get_all_groups($course->id, null, $grouping1->id);
        $groupkeys = array_keys($groups);
        $this->assertCount(2, $groups);
        $this->assertContains($group1->id, $groupkeys);
        $this->assertContains($group2->id, $groupkeys);
        $this->assertNotContains($group3->id, $groupkeys);
        $this->assertNotContains($group4->id, $groupkeys);

        $groups  = groups_get_all_groups($course->id, null, $grouping2->id);
        $groupkeys = array_keys($groups);
        $this->assertCount(2, $groups);
        $this->assertNotContains($group1->id, $groupkeys);
        $this->assertNotContains($group2->id, $groupkeys);
        $this->assertContains($group3->id, $groupkeys);
        $this->assertContains($group4->id, $groupkeys);

                $groups  = groups_get_all_groups($course->id, null, $grouping2->id, 'g.name, g.id');
        $groupkeys = array_keys($groups);
        $this->assertCount(2, $groups);
        $this->assertNotContains($group3->id, $groupkeys);
        $this->assertContains($group3->name, $groupkeys);
        $this->assertEquals($group3->id, $groups[$group3->name]->id);
    }

    
    public function test_groups_group_visible() {
        global $CFG, $DB;

        $generator = $this->getDataGenerator();
        $this->resetAfterTest();
        $this->setAdminUser();

                $cat = $generator->create_category(array('parent' => 0));
        $course = $generator->create_course(array('category' => $cat->id));
        $coursecontext = context_course::instance($course->id);
        $group1 = $generator->create_group(array('courseid' => $course->id, 'name' => 'Group 1'));
        $group2 = $generator->create_group(array('courseid' => $course->id, 'name' => 'Group 2'));
        $group3 = $generator->create_group(array('courseid' => $course->id, 'name' => 'Group 3'));
        $group4 = $generator->create_group(array('courseid' => $course->id, 'name' => 'Group 4'));

                $assign = $generator->create_module("assign", array('course' => $course->id));
        $cm = get_coursemodule_from_instance("assign", $assign->id);

                $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $user3 = $generator->create_user();

                $generator->enrol_user($user1->id, $course->id);
        $generator->enrol_user($user2->id, $course->id);

                groups_add_member($group1, $user2);

                $role = $DB->get_field("role", "id", array("shortname" => "manager"));
        $generator->enrol_user($user3->id, $course->id, $role);
                assign_capability('moodle/site:accessallgroups', CAP_ALLOW, $role, $coursecontext->id);

                $result = groups_group_visible($group1->id, $course, null, $user1->id);
        $this->assertTrue($result);
        $result = groups_group_visible(0, $course, null, $user1->id);
        $this->assertTrue($result); 
        $result = groups_group_visible($group1->id, $course, $cm, $user1->id);
        $this->assertTrue($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_group_visible($group1->id, $course, $cm, $user1->id);
        $this->assertFalse($result);         $result = groups_group_visible($group1->id, $course, $cm, $user2->id);
        $this->assertTrue($result); 
        $cm->groupmode = VISIBLEGROUPS;
        $result = groups_group_visible($group1->id, $course, $cm, $user1->id);
        $this->assertTrue($result); 
                $course->groupmode = NOGROUPS;
        $course->groupmodeforce = true;
        update_course($course);
        $result = groups_group_visible($group1->id, $course, null, $user1->id);
        $this->assertTrue($result);
        $result = groups_group_visible(0, $course, null, $user1->id);
        $this->assertTrue($result); 
        $result = groups_group_visible($group1->id, $course, $cm, $user1->id);
        $this->assertTrue($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_group_visible($group1->id, $course, $cm, $user1->id);
        $this->assertTrue($result);         $result = groups_group_visible($group1->id, $course, $cm, $user2->id);
        $this->assertTrue($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_group_visible($group1->id, $course, $cm, $user1->id);
        $this->assertTrue($result); 
                $course->groupmode = VISIBLEGROUPS;
        $course->groupmodeforce = true;
        update_course($course);
        $result = groups_group_visible($group1->id, $course, null, $user1->id);
        $this->assertTrue($result);
        $result = groups_group_visible(0, $course, null, $user1->id);
        $this->assertTrue($result); 
        $cm->groupmode = NOGROUPS;
        $result = groups_group_visible($group1->id, $course, $cm, $user1->id);
        $this->assertTrue($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_group_visible($group1->id, $course, $cm, $user1->id);
        $this->assertTrue($result);         $result = groups_group_visible($group1->id, $course, $cm, $user2->id);
        $this->assertTrue($result); 
        $cm->groupmode = VISIBLEGROUPS;
        $result = groups_group_visible($group1->id, $course, $cm, $user1->id);
        $this->assertTrue($result); 
                $course->groupmode = VISIBLEGROUPS;
        $course->groupmodeforce = false;
        update_course($course);
        $result = groups_group_visible($group1->id, $course, null, $user1->id);
        $this->assertTrue($result);
        $result = groups_group_visible(0, $course, null, $user1->id);
        $this->assertTrue($result); 
        $cm->groupmode = NOGROUPS;
        $result = groups_group_visible($group1->id, $course, $cm, $user1->id);
        $this->assertTrue($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_group_visible($group1->id, $course, $cm, $user1->id);
        $this->assertFalse($result);         $result = groups_group_visible($group1->id, $course, $cm, $user2->id);
        $this->assertTrue($result); 
        $cm->groupmode = VISIBLEGROUPS;
        $result = groups_group_visible($group1->id, $course, $cm, $user1->id);
        $this->assertTrue($result); 
                $course->groupmode = SEPARATEGROUPS;
        $course->groupmodeforce = true;
        update_course($course);
        $result = groups_group_visible($group1->id, $course, null, $user1->id);
        $this->assertFalse($result);
        $result = groups_group_visible($group1->id, $course, null, $user2->id);
        $this->assertTrue($result);
        $result = groups_group_visible(0, $course, null, $user2->id);
        $this->assertFalse($result);         $result = groups_group_visible(0, $course, null, $user3->id);
        $this->assertTrue($result);         $result = groups_group_visible($group1->id, $course, null, $user3->id);
        $this->assertTrue($result); 
        $cm->groupmode = NOGROUPS;
        $result = groups_group_visible($group1->id, $course, $cm, $user1->id);
        $this->assertFalse($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_group_visible($group1->id, $course, $cm, $user1->id);
        $this->assertFalse($result);         $result = groups_group_visible($group1->id, $course, $cm, $user2->id);
        $this->assertTrue($result);         $result = groups_group_visible($group1->id, $course, $cm, $user3->id);
        $this->assertTrue($result); 
        $cm->groupmode = VISIBLEGROUPS;
        $result = groups_group_visible($group1->id, $course, $cm, $user1->id);
        $this->assertFalse($result); 
                $course->groupmode = SEPARATEGROUPS;
        $course->groupmodeforce = false;
        update_course($course);
        $result = groups_group_visible($group1->id, $course, null, $user1->id);
        $this->assertFalse($result);
        $result = groups_group_visible($group1->id, $course, null, $user2->id);
        $this->assertTrue($result);
        $result = groups_group_visible(0, $course, null, $user2->id);
        $this->assertFalse($result);         $result = groups_group_visible(0, $course, null, $user3->id);
        $this->assertTrue($result); 
        $cm->groupmode = NOGROUPS;
        $result = groups_group_visible($group1->id, $course, $cm, $user1->id);
        $this->assertTrue($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_group_visible($group1->id, $course, $cm, $user1->id);
        $this->assertFalse($result);         $result = groups_group_visible($group1->id, $course, $cm, $user2->id);
        $this->assertTrue($result); 
        $cm->groupmode = VISIBLEGROUPS;
        $result = groups_group_visible($group1->id, $course, $cm, $user1->id);
        $this->assertTrue($result);     }

    function test_groups_get_groupmode() {
        global $DB;
        $generator = $this->getDataGenerator();
        $this->resetAfterTest();
        $this->setAdminUser();

                $course1 = $generator->create_course();

                $assign1 = $generator->create_module("assign", array('course' => $course1->id));
        $assign2 = $generator->create_module("assign", array('course' => $course1->id),
                array('groupmode' => SEPARATEGROUPS));
        $assign3 = $generator->create_module("assign", array('course' => $course1->id),
                array('groupmode' => VISIBLEGROUPS));

                $cm1 = get_coursemodule_from_instance("assign", $assign1->id);
        $cm2 = get_coursemodule_from_instance("assign", $assign2->id);
        $cm3 = get_coursemodule_from_instance("assign", $assign3->id);
        $modinfo = get_fast_modinfo($course1->id);

                $this->assertEquals(NOGROUPS, groups_get_activity_groupmode($cm1));
        $this->assertEquals(NOGROUPS, groups_get_activity_groupmode($cm1, $course1));
        $this->assertEquals(NOGROUPS, groups_get_activity_groupmode($modinfo->cms[$cm1->id]));
        $this->assertEquals(SEPARATEGROUPS, groups_get_activity_groupmode($cm2));
        $this->assertEquals(SEPARATEGROUPS, groups_get_activity_groupmode($cm2, $course1));
        $this->assertEquals(SEPARATEGROUPS, groups_get_activity_groupmode($modinfo->cms[$cm2->id]));
        $this->assertEquals(VISIBLEGROUPS, groups_get_activity_groupmode($cm3));
        $this->assertEquals(VISIBLEGROUPS, groups_get_activity_groupmode($cm3, $course1));
        $this->assertEquals(VISIBLEGROUPS, groups_get_activity_groupmode($modinfo->cms[$cm3->id]));

                update_course((object)array('id' => $course1->id, 'groupmode' => SEPARATEGROUPS));
                $course1 = $DB->get_record('course', array('id' => $course1->id));
        $modinfo = get_fast_modinfo($course1->id);

                $this->assertEquals(NOGROUPS, groups_get_activity_groupmode($cm1));
        $this->assertEquals(NOGROUPS, groups_get_activity_groupmode($cm1, $course1));
        $this->assertEquals(NOGROUPS, groups_get_activity_groupmode($modinfo->cms[$cm1->id]));
        $this->assertEquals(SEPARATEGROUPS, groups_get_activity_groupmode($cm2));
        $this->assertEquals(SEPARATEGROUPS, groups_get_activity_groupmode($cm2, $course1));
        $this->assertEquals(SEPARATEGROUPS, groups_get_activity_groupmode($modinfo->cms[$cm2->id]));
        $this->assertEquals(VISIBLEGROUPS, groups_get_activity_groupmode($cm3));
        $this->assertEquals(VISIBLEGROUPS, groups_get_activity_groupmode($cm3, $course1));
        $this->assertEquals(VISIBLEGROUPS, groups_get_activity_groupmode($modinfo->cms[$cm3->id]));

                update_course((object)array('id' => $course1->id, 'groupmode' => SEPARATEGROUPS, 'groupmodeforce' => true));
                $course1 = $DB->get_record('course', array('id' => $course1->id));
        $modinfo = get_fast_modinfo($course1->id);

                $this->assertEquals(SEPARATEGROUPS, groups_get_activity_groupmode($cm1));
        $this->assertEquals(SEPARATEGROUPS, groups_get_activity_groupmode($cm1, $course1));
        $this->assertEquals(SEPARATEGROUPS, groups_get_activity_groupmode($modinfo->cms[$cm1->id]));
        $this->assertEquals(SEPARATEGROUPS, groups_get_activity_groupmode($cm2));
        $this->assertEquals(SEPARATEGROUPS, groups_get_activity_groupmode($cm2, $course1));
        $this->assertEquals(SEPARATEGROUPS, groups_get_activity_groupmode($modinfo->cms[$cm2->id]));
        $this->assertEquals(SEPARATEGROUPS, groups_get_activity_groupmode($cm3));
        $this->assertEquals(SEPARATEGROUPS, groups_get_activity_groupmode($cm3, $course1));
        $this->assertEquals(SEPARATEGROUPS, groups_get_activity_groupmode($modinfo->cms[$cm3->id]));
    }

    
    public function test_groups_allgroups_course_menu() {
        global $SESSION;

        $this->resetAfterTest();

                $course = $this->getDataGenerator()->create_course();
        $record = new stdClass();
        $record->courseid = $course->id;
        $group1 = $this->getDataGenerator()->create_group($record);
        $group2 = $this->getDataGenerator()->create_group($record);
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        $this->setUser($user);

        $html = groups_allgroups_course_menu($course, 'someurl.php');
                        $this->assertEmpty($html);

        groups_add_member($group1->id, $user);
                        $html = groups_allgroups_course_menu($course, 'someurl.php');
        $this->assertContains(format_string($group1->name), $html);
        $this->assertNotContains(format_string($group2->name), $html);

        $this->setAdminUser();

                $html = groups_allgroups_course_menu($course, 'someurl.php');
        $this->assertContains(format_string($group1->name), $html);
        $this->assertContains(format_string($group2->name), $html);

                $course->groupmode = SEPARATEGROUPS;
        update_course($course);
        $html = groups_allgroups_course_menu($course, 'someurl.php');
        $this->assertContains(format_string($group1->name), $html);
        $this->assertContains(format_string($group2->name), $html);

                $course->groupmode = VISIBLEGROUPS;
        update_course($course);
        $html = groups_allgroups_course_menu($course, 'someurl.php');
        $this->assertContains(format_string($group1->name), $html);
        $this->assertContains(format_string($group2->name), $html);

                $this->setUser($user);
        $SESSION->activegroup[$course->id][VISIBLEGROUPS][$course->defaultgroupingid] = 5;
        groups_allgroups_course_menu($course, 'someurl.php', false);         $this->assertSame(5, $SESSION->activegroup[$course->id][VISIBLEGROUPS][$course->defaultgroupingid]);
        groups_allgroups_course_menu($course, 'someurl.php', true, $group1->id);         $this->assertSame($group1->id, $SESSION->activegroup[$course->id][VISIBLEGROUPS][$course->defaultgroupingid]);
                groups_allgroups_course_menu($course, 'someurl.php', true, 256);
        $this->assertEquals($group1->id, $SESSION->activegroup[$course->id][VISIBLEGROUPS][$course->defaultgroupingid]);
    }

    
    public function test_groups_ordering() {
        $generator = $this->getDataGenerator();
        $this->resetAfterTest();

                $cat = $generator->create_category(array('parent' => 0));
        $course = $generator->create_course(array('category' => $cat->id));
        $grouping = $generator->create_grouping(array('courseid' => $course->id, 'name' => 'Grouping'));

                $group2 = $generator->create_group(array('courseid' => $course->id, 'name' => 'Group 2'));
        $group1 = $generator->create_group(array('courseid' => $course->id, 'name' => 'Group 1'));

                $this->assertTrue(groups_assign_grouping($grouping->id, $group2->id));
        $this->assertTrue(groups_assign_grouping($grouping->id, $group1->id));

                $groups = array_values(groups_get_all_groups($course->id, 0));
        $this->assertEquals('Group 1', $groups[0]->name);
        $this->assertEquals('Group 2', $groups[1]->name);

                $groups = array_values(groups_get_all_groups($course->id, 0, $grouping->id));
        $this->assertEquals('Group 1', $groups[0]->name);
        $this->assertEquals('Group 2', $groups[1]->name);
    }

    
    public function test_groups_get_user_groups() {
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator();

                $course1 = $generator->create_course();
        $course2 = $generator->create_course();

                $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $user3 = $generator->create_user();

                $generator->enrol_user($user1->id, $course1->id);
        $generator->enrol_user($user1->id, $course2->id);
        $generator->enrol_user($user2->id, $course2->id);
        $generator->enrol_user($user3->id, $course2->id);

                $group1 = $generator->create_group(array('courseid' => $course1->id));
        $group2 = $generator->create_group(array('courseid' => $course2->id));
        $group3 = $generator->create_group(array('courseid' => $course2->id));

                $this->assertTrue($generator->create_group_member(array('groupid' => $group1->id, 'userid' => $user1->id)));
        $this->assertTrue($generator->create_group_member(array('groupid' => $group2->id, 'userid' => $user2->id)));

                $usergroups1 = groups_get_user_groups($course1->id, $user1->id);
        $usergroups2 = groups_get_user_groups($course2->id, $user2->id);;

                $this->assertEquals($group1->id, $usergroups1[0][0]);
        $this->assertEquals($group2->id, $usergroups2[0][0]);

                $grouping1 = $generator->create_grouping(array('courseid' => $course1->id));
        $grouping2 = $generator->create_grouping(array('courseid' => $course2->id));

                groups_assign_grouping($grouping1->id, $group1->id);
        groups_assign_grouping($grouping2->id, $group2->id);
        groups_assign_grouping($grouping2->id, $group3->id);

                $usergroups1 = groups_get_user_groups($course1->id, $user1->id);
        $usergroups2 = groups_get_user_groups($course2->id, $user2->id);
        $this->assertArrayHasKey($grouping1->id, $usergroups1);
        $this->assertArrayHasKey($grouping2->id, $usergroups2);

                $usergroups1 = groups_get_user_groups($course2->id, $user3->id);
        $this->assertCount(0, $usergroups1[0]);

                $usergroups1 = groups_get_user_groups($course1->id, 0);
        $usergroups2 = groups_get_user_groups($course2->id, 0);
        $this->assertCount(0, $usergroups1[0]);
        $this->assertCount(0, $usergroups2[0]);

                $usergroups1 = groups_get_user_groups(0, $user1->id);
        $usergroups2 = groups_get_user_groups(0, $user2->id);
        $this->assertCount(0, $usergroups1[0]);
        $this->assertCount(0, $usergroups2[0]);
    }

    
    protected function make_group_list($number) {
        $testgroups = array();
        for ($a = 0; $a < $number; $a++) {
            $grp = new stdClass();
            $grp->id = 100 + $a;
            $grp->name = 'test group ' . $grp->id;
            $testgroups[$grp->id] = $grp;
        }
        return $testgroups;
    }

    public function test_groups_sort_menu_options_empty() {
        $this->assertEquals(array(), groups_sort_menu_options(array(), array()));
    }

    public function test_groups_sort_menu_options_allowed_goups_only() {
        $this->assertEquals(array(
            100 => 'test group 100',
            101 => 'test group 101',
        ), groups_sort_menu_options($this->make_group_list(2), array()));
    }

    public function test_groups_sort_menu_options_user_goups_only() {
        $this->assertEquals(array(
            100 => 'test group 100',
            101 => 'test group 101',
        ), groups_sort_menu_options(array(), $this->make_group_list(2)));
    }

    public function test_groups_sort_menu_options_user_both() {
        $this->assertEquals(array(
            1 => array(get_string('mygroups', 'group') => array(
                100 => 'test group 100',
                101 => 'test group 101',
            )),
            2 => array(get_string('othergroups', 'group') => array(
                102 => 'test group 102',
                103 => 'test group 103',
            )),
        ), groups_sort_menu_options($this->make_group_list(4), $this->make_group_list(2)));
    }

    public function test_groups_sort_menu_options_user_both_many_groups() {
        $this->assertEquals(array(
            1 => array(get_string('mygroups', 'group') => array(
                100 => 'test group 100',
                101 => 'test group 101',
            )),
            2 => array (get_string('othergroups', 'group') => array(
                102 => 'test group 102',
                103 => 'test group 103',
                104 => 'test group 104',
                105 => 'test group 105',
                106 => 'test group 106',
                107 => 'test group 107',
                108 => 'test group 108',
                109 => 'test group 109',
                110 => 'test group 110',
                111 => 'test group 111',
                112 => 'test group 112',
            )),
        ), groups_sort_menu_options($this->make_group_list(13), $this->make_group_list(2)));
    }

    
    public function test_groups_user_groups_visible() {
        global $CFG, $DB;

        $generator = $this->getDataGenerator();
        $this->resetAfterTest();
        $this->setAdminUser();

                $cat = $generator->create_category(array('parent' => 0));
        $course = $generator->create_course(array('category' => $cat->id));
        $coursecontext = context_course::instance($course->id);
        $group1 = $generator->create_group(array('courseid' => $course->id, 'name' => 'Group 1'));
        $group2 = $generator->create_group(array('courseid' => $course->id, 'name' => 'Group 2'));
        $group3 = $generator->create_group(array('courseid' => $course->id, 'name' => 'Group 3'));
        $group4 = $generator->create_group(array('courseid' => $course->id, 'name' => 'Group 4'));

                $assign = $generator->create_module("assign", array('course' => $course->id));
        $cm = get_coursemodule_from_instance("assign", $assign->id);

                $user1 = $generator->create_user();         $user2 = $generator->create_user();         $user3 = $generator->create_user();         $user4 = $generator->create_user(); 
                $generator->enrol_user($user1->id, $course->id);
        $generator->enrol_user($user2->id, $course->id);
        $generator->enrol_user($user4->id, $course->id);

                        groups_add_member($group1, $user1);
        groups_add_member($group2, $user2);
        groups_add_member($group1, $user4);

                $role = $DB->get_field("role", "id", array("shortname" => "manager"));
        $generator->enrol_user($user3->id, $course->id, $role);
                assign_capability('moodle/site:accessallgroups', CAP_ALLOW, $role, $coursecontext->id);

                $this->setUser($user1);

                $result = groups_user_groups_visible($course, $user2->id);
        $this->assertTrue($result);

        $result = groups_user_groups_visible($course, $user2->id);
        $this->assertTrue($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_user_groups_visible($course, $user2->id, $cm);
        $this->assertFalse($result); 
        $cm->groupmode = VISIBLEGROUPS;
        $result = groups_user_groups_visible($course, $user2->id, $cm);
        $this->assertTrue($result); 
                $course->groupmode = NOGROUPS;
        $course->groupmodeforce = true;
        update_course($course);
        $result = groups_user_groups_visible($course, $user2->id);
        $this->assertTrue($result);

        $result = groups_user_groups_visible($course, $user2->id);
        $this->assertTrue($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_user_groups_visible($course, $user2->id, $cm);
        $this->assertTrue($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_user_groups_visible($course, $user2->id);
        $this->assertTrue($result); 
                $course->groupmode = VISIBLEGROUPS;
        $course->groupmodeforce = true;
        update_course($course);
        $result = groups_user_groups_visible($course, $user2->id);
        $this->assertTrue($result);

        $cm->groupmode = NOGROUPS;
        $result = groups_user_groups_visible($course, $user2->id, $cm);
        $this->assertTrue($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_user_groups_visible($course, $user2->id, $cm);
        $this->assertTrue($result); 
        $cm->groupmode = VISIBLEGROUPS;
        $result = groups_user_groups_visible($course, $user2->id, $cm);
        $this->assertTrue($result); 
                $course->groupmode = VISIBLEGROUPS;
        $course->groupmodeforce = false;
        update_course($course);
        $result = groups_user_groups_visible($course, $user2->id);
        $this->assertTrue($result);

        $cm->groupmode = NOGROUPS;
        $result = groups_user_groups_visible($course, $user2->id);
        $this->assertTrue($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_user_groups_visible($course, $user2->id, $cm);
        $this->assertFalse($result); 
        $cm->groupmode = VISIBLEGROUPS;
        $result = groups_user_groups_visible($course, $user2->id, $cm);
        $this->assertTrue($result); 
                $course->groupmode = SEPARATEGROUPS;
        $course->groupmodeforce = true;
        update_course($course);
        $result = groups_user_groups_visible($course, $user2->id);
        $this->assertFalse($result);

        $result = groups_user_groups_visible($course, $user3->id);
        $this->assertFalse($result); 
        $cm->groupmode = NOGROUPS;
        $result = groups_user_groups_visible($course, $user2->id, $cm);
        $this->assertFalse($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_user_groups_visible($course, $user2->id, $cm);
        $this->assertFalse($result); 
        $result = groups_user_groups_visible($course, $user3->id, $cm);
        $this->assertTrue($result);

        $cm->groupmode = VISIBLEGROUPS;
        $result = groups_user_groups_visible($course, $user2->id, $cm);
        $this->assertFalse($result); 
                $course->groupmode = SEPARATEGROUPS;
        $course->groupmodeforce = false;
        update_course($course);
        $result = groups_user_groups_visible($course, $user2->id);
        $this->assertFalse($result);

        $result = groups_user_groups_visible($course, $user3->id);
        $this->assertFalse($result); 
        $cm->groupmode = NOGROUPS;
        $result = groups_user_groups_visible($course, $user2->id, $cm);
        $this->assertTrue($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_user_groups_visible($course, $user2->id, $cm);
        $this->assertFalse($result); 
        $cm->groupmode = VISIBLEGROUPS;
        $result = groups_user_groups_visible($course, $user2->id, $cm);
        $this->assertTrue($result); 
        
                $course->groupmode = NOGROUPS;
        $course->groupmodeforce = false;
        update_course($course);

        $result = groups_user_groups_visible($course, $user4->id);
        $this->assertTrue($result);

        $result = groups_user_groups_visible($course, $user4->id);
        $this->assertTrue($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_user_groups_visible($course, $user4->id, $cm);
        $this->assertTrue($result); 
        $cm->groupmode = VISIBLEGROUPS;
        $result = groups_user_groups_visible($course, $user4->id, $cm);
        $this->assertTrue($result); 
                $course->groupmode = NOGROUPS;
        $course->groupmodeforce = true;
        update_course($course);
        $result = groups_user_groups_visible($course, $user4->id);
        $this->assertTrue($result);

        $result = groups_user_groups_visible($course, $user4->id);
        $this->assertTrue($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_user_groups_visible($course, $user4->id, $cm);
        $this->assertTrue($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_user_groups_visible($course, $user4->id);
        $this->assertTrue($result); 
                $course->groupmode = VISIBLEGROUPS;
        $course->groupmodeforce = true;
        update_course($course);
        $result = groups_user_groups_visible($course, $user4->id);
        $this->assertTrue($result);

        $cm->groupmode = NOGROUPS;
        $result = groups_user_groups_visible($course, $user4->id, $cm);
        $this->assertTrue($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_user_groups_visible($course, $user4->id, $cm);
        $this->assertTrue($result); 
        $cm->groupmode = VISIBLEGROUPS;
        $result = groups_user_groups_visible($course, $user4->id, $cm);
        $this->assertTrue($result); 
                $course->groupmode = VISIBLEGROUPS;
        $course->groupmodeforce = false;
        update_course($course);
        $result = groups_user_groups_visible($course, $user4->id);
        $this->assertTrue($result);

        $cm->groupmode = NOGROUPS;
        $result = groups_user_groups_visible($course, $user4->id);
        $this->assertTrue($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_user_groups_visible($course, $user4->id, $cm);
        $this->assertTrue($result); 
        $cm->groupmode = VISIBLEGROUPS;
        $result = groups_user_groups_visible($course, $user4->id, $cm);
        $this->assertTrue($result); 
                $course->groupmode = SEPARATEGROUPS;
        $course->groupmodeforce = true;
        update_course($course);
        $result = groups_user_groups_visible($course, $user4->id);
        $this->assertTrue($result);

        $result = groups_user_groups_visible($course, $user3->id);
        $this->assertFalse($result); 
        $cm->groupmode = NOGROUPS;
        $result = groups_user_groups_visible($course, $user4->id, $cm);
        $this->assertTrue($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_user_groups_visible($course, $user4->id, $cm);
        $this->assertTrue($result); 
        $result = groups_user_groups_visible($course, $user3->id, $cm);
        $this->assertTrue($result);

        $cm->groupmode = VISIBLEGROUPS;
        $result = groups_user_groups_visible($course, $user4->id, $cm);
        $this->assertTrue($result); 
                $course->groupmode = SEPARATEGROUPS;
        $course->groupmodeforce = false;
        update_course($course);
        $result = groups_user_groups_visible($course, $user4->id);
        $this->assertTrue($result);

        $result = groups_user_groups_visible($course, $user3->id);
        $this->assertFalse($result); 
        $cm->groupmode = NOGROUPS;
        $result = groups_user_groups_visible($course, $user4->id, $cm);
        $this->assertTrue($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_user_groups_visible($course, $user4->id, $cm);
        $this->assertTrue($result); 
        $cm->groupmode = VISIBLEGROUPS;
        $result = groups_user_groups_visible($course, $user4->id, $cm);
        $this->assertTrue($result); 
        
                $course->groupmode = NOGROUPS;
        $course->groupmodeforce = false;
        update_course($course);

        $this->setUser($user3);

        $result = groups_user_groups_visible($course, $user1->id);
        $this->assertTrue($result);
        $result = groups_user_groups_visible($course, $user1->id);
        $this->assertTrue($result); 
        $result = groups_user_groups_visible($course, $user1->id);
        $this->assertTrue($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_user_groups_visible($course, $user1->id, $cm);
        $this->assertTrue($result);         $result = groups_user_groups_visible($course, $user2->id, $cm);
        $this->assertTrue($result); 
        $cm->groupmode = VISIBLEGROUPS;
        $result = groups_user_groups_visible($course, $user1->id, $cm);
        $this->assertTrue($result); 
                $course->groupmode = NOGROUPS;
        $course->groupmodeforce = true;
        update_course($course);
        $result = groups_user_groups_visible($course, $user1->id);
        $this->assertTrue($result);
        $result = groups_user_groups_visible($course, $user1->id);
        $this->assertTrue($result); 
        $result = groups_user_groups_visible($course, $user1->id);
        $this->assertTrue($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_user_groups_visible($course, $user1->id, $cm);
        $this->assertTrue($result);         $result = groups_user_groups_visible($course, $user2->id, $cm);
        $this->assertTrue($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_user_groups_visible($course, $user1->id);
        $this->assertTrue($result); 
                $course->groupmode = VISIBLEGROUPS;
        $course->groupmodeforce = true;
        update_course($course);
        $result = groups_user_groups_visible($course, $user1->id);
        $this->assertTrue($result);
        $result = groups_user_groups_visible($course, $user1->id);
        $this->assertTrue($result); 
        $cm->groupmode = NOGROUPS;
        $result = groups_user_groups_visible($course, $user1->id, $cm);
        $this->assertTrue($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_user_groups_visible($course, $user1->id, $cm);
        $this->assertTrue($result);         $result = groups_user_groups_visible($course, $user2->id, $cm);
        $this->assertTrue($result); 
        $cm->groupmode = VISIBLEGROUPS;
        $result = groups_user_groups_visible($course, $user1->id, $cm);
        $this->assertTrue($result); 
                $course->groupmode = VISIBLEGROUPS;
        $course->groupmodeforce = false;
        update_course($course);
        $result = groups_user_groups_visible($course, $user1->id);
        $this->assertTrue($result);
        $result = groups_user_groups_visible($course, $user1->id);
        $this->assertTrue($result); 
        $cm->groupmode = NOGROUPS;
        $result = groups_user_groups_visible($course, $user1->id);
        $this->assertTrue($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_user_groups_visible($course, $user1->id, $cm);
        $this->assertTrue($result);         $result = groups_user_groups_visible($course, $user2->id, $cm);
        $this->assertTrue($result); 
        $cm->groupmode = VISIBLEGROUPS;
        $result = groups_user_groups_visible($course, $user1->id, $cm);
        $this->assertTrue($result); 
                $course->groupmode = SEPARATEGROUPS;
        $course->groupmodeforce = true;
        update_course($course);
        $result = groups_user_groups_visible($course, $user1->id);
        $this->assertTrue($result);
        $result = groups_user_groups_visible($course, $user2->id);
        $this->assertTrue($result);
        $result = groups_user_groups_visible($course, $user2->id);
        $this->assertTrue($result);         $result = groups_user_groups_visible($course, $user3->id);
        $this->assertTrue($result);         $result = groups_user_groups_visible($course, $user3->id);
        $this->assertTrue($result);

        $cm->groupmode = NOGROUPS;
        $result = groups_user_groups_visible($course, $user1->id, $cm);
        $this->assertTrue($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_user_groups_visible($course, $user1->id, $cm);
        $this->assertTrue($result);         $result = groups_user_groups_visible($course, $user2->id, $cm);
        $this->assertTrue($result);         $result = groups_user_groups_visible($course, $user3->id, $cm);
        $this->assertTrue($result);

        $cm->groupmode = VISIBLEGROUPS;
        $result = groups_user_groups_visible($course, $user1->id, $cm);
        $this->assertTrue($result); 
                $course->groupmode = SEPARATEGROUPS;
        $course->groupmodeforce = false;
        update_course($course);
        $result = groups_user_groups_visible($course, $user1->id);
        $this->assertTrue($result);
        $result = groups_user_groups_visible($course, $user2->id);
        $this->assertTrue($result);
        $result = groups_user_groups_visible($course, $user2->id);
        $this->assertTrue($result);         $result = groups_user_groups_visible($course, $user3->id);
        $this->assertTrue($result); 
        $cm->groupmode = NOGROUPS;
        $result = groups_user_groups_visible($course, $user1->id, $cm);
        $this->assertTrue($result); 
        $cm->groupmode = SEPARATEGROUPS;
        $result = groups_user_groups_visible($course, $user1->id, $cm);
        $this->assertTrue($result);         $result = groups_user_groups_visible($course, $user2->id, $cm);
        $this->assertTrue($result); 
        $cm->groupmode = VISIBLEGROUPS;
        $result = groups_user_groups_visible($course, $user1->id, $cm);
        $this->assertTrue($result);     }
}
