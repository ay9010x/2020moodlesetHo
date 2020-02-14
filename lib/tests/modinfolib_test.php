<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/modinfolib.php');


class core_modinfolib_testcase extends advanced_testcase {
    public function test_section_info_properties() {
        global $DB, $CFG;

        $this->resetAfterTest();
        $oldcfgenableavailability = $CFG->enableavailability;
        $oldcfgenablecompletion = $CFG->enablecompletion;
        set_config('enableavailability', 1);
        set_config('enablecompletion', 1);
        $this->setAdminUser();

                $course = $this->getDataGenerator()->create_course(
                array('format' => 'topics',
                    'numsections' => 3,
                    'enablecompletion' => 1,
                    'groupmode' => SEPARATEGROUPS,
                    'forcegroupmode' => 0),
                array('createsections' => true));
        $coursecontext = context_course::instance($course->id);
        $prereqforum = $this->getDataGenerator()->create_module('forum',
                array('course' => $course->id),
                array('completion' => 1));

                $availability = '{"op":"&","showc":[true,true,true],"c":[' .
                '{"type":"completion","cm":' . $prereqforum->cmid . ',"e":"' .
                    COMPLETION_COMPLETE . '"},' .
                '{"type":"grade","id":666,"min":0.4},' .
                '{"type":"profile","op":"contains","sf":"email","v":"test"}' .
                ']}';
        $DB->set_field('course_sections', 'availability', $availability,
                array('course' => $course->id, 'section' => 2));
        rebuild_course_cache($course->id, true);
        $sectiondb = $DB->get_record('course_sections', array('course' => $course->id, 'section' => 2));

                $studentrole = $DB->get_record('role', array('shortname' => 'student'), '*', MUST_EXIST);
        $student = $this->getDataGenerator()->create_user();
        role_assign($studentrole->id, $student->id, $coursecontext);
        $enrolplugin = enrol_get_plugin('manual');
        $enrolinstance = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'manual'));
        $enrolplugin->enrol_user($enrolinstance, $student->id);
        $this->setUser($student);

                $modinfo = get_fast_modinfo($course->id);
        $si = $modinfo->get_section_info(2);

        $this->assertEquals($sectiondb->id, $si->id);
        $this->assertEquals($sectiondb->course, $si->course);
        $this->assertEquals($sectiondb->section, $si->section);
        $this->assertEquals($sectiondb->name, $si->name);
        $this->assertEquals($sectiondb->visible, $si->visible);
        $this->assertEquals($sectiondb->summary, $si->summary);
        $this->assertEquals($sectiondb->summaryformat, $si->summaryformat);
        $this->assertEquals($sectiondb->sequence, $si->sequence);         $this->assertEquals($availability, $si->availability);

                $this->assertEquals(0, $si->available);
        $this->assertNotEmpty($si->availableinfo);         $this->assertEquals(0, $si->uservisible);

                set_config('enableavailability', $oldcfgenableavailability);
        set_config('enablecompletion', $oldcfgenablecompletion);
    }

    public function test_cm_info_properties() {
        global $DB, $CFG;

        $this->resetAfterTest();
        $oldcfgenableavailability = $CFG->enableavailability;
        $oldcfgenablecompletion = $CFG->enablecompletion;
        set_config('enableavailability', 1);
        set_config('enablecompletion', 1);
        $this->setAdminUser();

                $course = $this->getDataGenerator()->create_course(
                array('format' => 'topics',
                    'numsections' => 3,
                    'enablecompletion' => 1,
                    'groupmode' => SEPARATEGROUPS,
                    'forcegroupmode' => 0),
                array('createsections' => true));
        $coursecontext = context_course::instance($course->id);
        $prereqforum = $this->getDataGenerator()->create_module('forum',
                array('course' => $course->id),
                array('completion' => 1));

                $availability = '{"op":"&","showc":[true,true,true],"c":[' .
                '{"type":"completion","cm":' . $prereqforum->cmid . ',"e":"' .
                    COMPLETION_COMPLETE . '"},' .
                '{"type":"grade","id":666,"min":0.4},' .
                '{"type":"profile","op":"contains","sf":"email","v":"test"}' .
                ']}';
        $assign = $this->getDataGenerator()->create_module('assign',
                array('course' => $course->id),
                array('idnumber' => 123,
                    'groupmode' => VISIBLEGROUPS,
                    'availability' => $availability));
        rebuild_course_cache($course->id, true);

                $assigndb = $DB->get_record('assign', array('id' => $assign->id));
        $moduletypedb = $DB->get_record('modules', array('name' => 'assign'));
        $moduledb = $DB->get_record('course_modules', array('module' => $moduletypedb->id, 'instance' => $assign->id));
        $sectiondb = $DB->get_record('course_sections', array('id' => $moduledb->section));
        $modnamessingular = get_module_types_names(false);
        $modnamesplural = get_module_types_names(true);

                $studentrole = $DB->get_record('role', array('shortname' => 'student'), '*', MUST_EXIST);
        $student = $this->getDataGenerator()->create_user();
        role_assign($studentrole->id, $student->id, $coursecontext);
        $enrolplugin = enrol_get_plugin('manual');
        $enrolinstance = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'manual'));
        $enrolplugin->enrol_user($enrolinstance, $student->id);
        $this->setUser($student);

                $rawmods = get_course_mods($course->id);
        $cachedcminfo = assign_get_coursemodule_info($rawmods[$moduledb->id]);

                $modinfo = get_fast_modinfo($course->id);
        $cm = $modinfo->instances['assign'][$assign->id];

        $this->assertEquals($moduledb->id, $cm->id);
        $this->assertEquals($assigndb->id, $cm->instance);
        $this->assertEquals($moduledb->course, $cm->course);
        $this->assertEquals($moduledb->idnumber, $cm->idnumber);
        $this->assertEquals($moduledb->added, $cm->added);
        $this->assertEquals($moduledb->visible, $cm->visible);
        $this->assertEquals($moduledb->visibleold, $cm->visibleold);
        $this->assertEquals($moduledb->groupmode, $cm->groupmode);
        $this->assertEquals(VISIBLEGROUPS, $cm->groupmode);
        $this->assertEquals($moduledb->groupingid, $cm->groupingid);
        $this->assertEquals($course->groupmodeforce, $cm->coursegroupmodeforce);
        $this->assertEquals($course->groupmode, $cm->coursegroupmode);
        $this->assertEquals(SEPARATEGROUPS, $cm->coursegroupmode);
        $this->assertEquals($course->groupmodeforce ? $course->groupmode : $moduledb->groupmode,
                $cm->effectivegroupmode);         $this->assertEquals(VISIBLEGROUPS, $cm->effectivegroupmode);
        $this->assertEquals($moduledb->indent, $cm->indent);
        $this->assertEquals($moduledb->completion, $cm->completion);
        $this->assertEquals($moduledb->completiongradeitemnumber, $cm->completiongradeitemnumber);
        $this->assertEquals($moduledb->completionview, $cm->completionview);
        $this->assertEquals($moduledb->completionexpected, $cm->completionexpected);
        $this->assertEquals($moduledb->showdescription, $cm->showdescription);
        $this->assertEquals(null, $cm->extra);         $this->assertEquals($cachedcminfo->icon, $cm->icon);
        $this->assertEquals($cachedcminfo->iconcomponent, $cm->iconcomponent);
        $this->assertEquals('assign', $cm->modname);
        $this->assertEquals($moduledb->module, $cm->module);
        $this->assertEquals($cachedcminfo->name, $cm->name);
        $this->assertEquals($sectiondb->section, $cm->sectionnum);
        $this->assertEquals($moduledb->section, $cm->section);
        $this->assertEquals($availability, $cm->availability);
        $this->assertEquals(context_module::instance($moduledb->id), $cm->context);
        $this->assertEquals($modnamessingular['assign'], $cm->modfullname);
        $this->assertEquals($modnamesplural['assign'], $cm->modplural);
        $this->assertEquals(new moodle_url('/mod/assign/view.php', array('id' => $moduledb->id)), $cm->url);
        $this->assertEquals($cachedcminfo->customdata, $cm->customdata);

                $this->assertEquals(0, $cm->groupmembersonly);
        $this->assertDebuggingCalled();

                $this->assertNotEmpty($cm->availableinfo);         $this->assertEquals(0, $cm->uservisible);
        $this->assertEquals('', $cm->extraclasses);
        $this->assertEquals('', $cm->onclick);
        $this->assertEquals(null, $cm->afterlink);
        $this->assertEquals(null, $cm->afterediticons);
        $this->assertEquals('', $cm->content);

                $this->assertTrue(empty($modinfo->somefield));
        $this->assertFalse(isset($modinfo->somefield));
        $cm->somefield;
        $this->assertDebuggingCalled();
        $cm->somefield = 'Some value';
        $this->assertDebuggingCalled();
        $this->assertEmpty($cm->somefield);
        $this->assertDebuggingCalled();

                $prevvalue = $cm->name;
        $this->assertNotEmpty($cm->name);
        $this->assertFalse(empty($cm->name));
        $this->assertTrue(isset($cm->name));
        $cm->name = 'Illegal overwriting';
        $this->assertDebuggingCalled();
        $this->assertEquals($prevvalue, $cm->name);
        $this->assertDebuggingNotCalled();

                set_config('enableavailability', $oldcfgenableavailability);
        set_config('enablecompletion', $oldcfgenablecompletion);
    }

    public function test_matching_cacherev() {
        global $DB, $CFG;

        $this->resetAfterTest();
        $this->setAdminUser();
        $cache = cache::make('core', 'coursemodinfo');

                $course = $this->getDataGenerator()->create_course(
                array('format' => 'topics',
                    'numsections' => 3),
                array('createsections' => true));

                $cacherev = $DB->get_field('course', 'cacherev', array('id' => $course->id));
        $this->assertGreaterThan(0, $cacherev);
        $prevcacherev = $cacherev;

                rebuild_course_cache($course->id, true);
        $cacherev = $DB->get_field('course', 'cacherev', array('id' => $course->id));
        $this->assertGreaterThan($prevcacherev, $cacherev);
        $this->assertEmpty($cache->get($course->id));
        $prevcacherev = $cacherev;

                $modinfo = get_fast_modinfo($course->id);
        $cacherev = $DB->get_field('course', 'cacherev', array('id' => $course->id));
        $this->assertEquals($prevcacherev, $cacherev);
        $cachedvalue = $cache->get($course->id);
        $this->assertNotEmpty($cachedvalue);
        $this->assertEquals($cacherev, $cachedvalue->cacherev);
        $this->assertEquals($cacherev, $modinfo->get_course()->cacherev);
        $prevcacherev = $cacherev;

                $cache->set($course->id, (object)array_merge((array)$cachedvalue, array('secretfield' => 1)));

                course_modinfo::clear_instance_cache();
        $modinfo = get_fast_modinfo($course->id);
        $cacherev = $DB->get_field('course', 'cacherev', array('id' => $course->id));
        $this->assertEquals($prevcacherev, $cacherev);
        $cachedvalue = $cache->get($course->id);
        $this->assertNotEmpty($cachedvalue);
        $this->assertEquals($cacherev, $cachedvalue->cacherev);
        $this->assertNotEmpty($cachedvalue->secretfield);
        $this->assertEquals($cacherev, $modinfo->get_course()->cacherev);
        $prevcacherev = $cacherev;

                rebuild_course_cache($course->id);
        $cacherev = $DB->get_field('course', 'cacherev', array('id' => $course->id));
        $this->assertGreaterThan($prevcacherev, $cacherev);
        $cachedvalue = $cache->get($course->id);
        $this->assertNotEmpty($cachedvalue);
        $this->assertEquals($cacherev, $cachedvalue->cacherev);
        $modinfo = get_fast_modinfo($course->id);
        $this->assertEquals($cacherev, $modinfo->get_course()->cacherev);
        $prevcacherev = $cacherev;

                increment_revision_number('course', 'cacherev', 'id = ?', array($course->id));
                course_modinfo::clear_instance_cache();
        $modinfo = get_fast_modinfo($course->id);
        $cacherev = $DB->get_field('course', 'cacherev', array('id' => $course->id));
        $this->assertGreaterThan($prevcacherev, $cacherev);
        $cachedvalue = $cache->get($course->id);
        $this->assertNotEmpty($cachedvalue);
        $this->assertEquals($cacherev, $cachedvalue->cacherev);
        $this->assertEquals($cacherev, $modinfo->get_course()->cacherev);
        $prevcacherev = $cacherev;

                rebuild_course_cache(0, true);
        $cacherev = $DB->get_field('course', 'cacherev', array('id' => $course->id));
        $this->assertGreaterThan($prevcacherev, $cacherev);
        $this->assertEmpty($cache->get($course->id));
                $modinfo = get_fast_modinfo($course->id);
        $cachedvalue = $cache->get($course->id);
        $this->assertNotEmpty($cachedvalue);
        $this->assertEquals($cacherev, $cachedvalue->cacherev);
        $this->assertEquals($cacherev, $modinfo->get_course()->cacherev);
        $prevcacherev = $cacherev;

                purge_all_caches();
        $cacherev = $DB->get_field('course', 'cacherev', array('id' => $course->id));
        $this->assertGreaterThan($prevcacherev, $cacherev);
        $this->assertEmpty($cache->get($course->id));
    }

    public function test_course_modinfo_properties() {
        global $USER, $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

                $course = $this->getDataGenerator()->create_course(
                array('format' => 'topics',
                    'numsections' => 3),
                array('createsections' => true));
        $DB->execute('UPDATE {course_sections} SET visible = 0 WHERE course = ? and section = ?',
                array($course->id, 3));
        $coursecontext = context_course::instance($course->id);
        $forum0 = $this->getDataGenerator()->create_module('forum',
                array('course' => $course->id), array('section' => 0));
        $assign0 = $this->getDataGenerator()->create_module('assign',
                array('course' => $course->id), array('section' => 0, 'visible' => 0));
        $forum1 = $this->getDataGenerator()->create_module('forum',
                array('course' => $course->id), array('section' => 1));
        $assign1 = $this->getDataGenerator()->create_module('assign',
                array('course' => $course->id), array('section' => 1));
        $page1 = $this->getDataGenerator()->create_module('page',
                array('course' => $course->id), array('section' => 1));
        $page3 = $this->getDataGenerator()->create_module('page',
                array('course' => $course->id), array('section' => 3));

        $modinfo = get_fast_modinfo($course->id);

        $this->assertEquals(array($forum0->cmid, $assign0->cmid, $forum1->cmid, $assign1->cmid, $page1->cmid, $page3->cmid),
                array_keys($modinfo->cms));
        $this->assertEquals($course->id, $modinfo->courseid);
        $this->assertEquals($USER->id, $modinfo->userid);
        $this->assertEquals(array(0 => array($forum0->cmid, $assign0->cmid),
            1 => array($forum1->cmid, $assign1->cmid, $page1->cmid), 3 => array($page3->cmid)), $modinfo->sections);
        $this->assertEquals(array('forum', 'assign', 'page'), array_keys($modinfo->instances));
        $this->assertEquals(array($assign0->id, $assign1->id), array_keys($modinfo->instances['assign']));
        $this->assertEquals(array($forum0->id, $forum1->id), array_keys($modinfo->instances['forum']));
        $this->assertEquals(array($page1->id, $page3->id), array_keys($modinfo->instances['page']));
        $this->assertEquals(groups_get_user_groups($course->id), $modinfo->groups);
        $this->assertEquals(array(0 => array($forum0->cmid, $assign0->cmid),
            1 => array($forum1->cmid, $assign1->cmid, $page1->cmid),
            3 => array($page3->cmid)), $modinfo->get_sections());
        $this->assertEquals(array(0, 1, 2, 3), array_keys($modinfo->get_section_info_all()));
        $this->assertEquals($forum0->cmid . ',' . $assign0->cmid, $modinfo->get_section_info(0)->sequence);
        $this->assertEquals($forum1->cmid . ',' . $assign1->cmid . ',' . $page1->cmid, $modinfo->get_section_info(1)->sequence);
        $this->assertEquals('', $modinfo->get_section_info(2)->sequence);
        $this->assertEquals($page3->cmid, $modinfo->get_section_info(3)->sequence);
        $this->assertEquals($course->id, $modinfo->get_course()->id);
        $this->assertEquals(array('assign', 'forum', 'page'),
                array_keys($modinfo->get_used_module_names()));
        $this->assertEquals(array('assign', 'forum', 'page'),
                array_keys($modinfo->get_used_module_names(true)));
                $this->assertTrue($modinfo->cms[$assign0->cmid]->uservisible);
        $this->assertTrue($modinfo->get_section_info(3)->uservisible);

                $user = $this->getDataGenerator()->create_user();
        $modinfo = get_fast_modinfo($course->id, $user->id);
        $this->assertEquals($user->id, $modinfo->userid);
        $this->assertFalse($modinfo->cms[$assign0->cmid]->uservisible);
        $this->assertFalse($modinfo->get_section_info(3)->uservisible);

                $this->assertTrue(empty($modinfo->somefield));
        $this->assertFalse(isset($modinfo->somefield));
        $modinfo->somefield;
        $this->assertDebuggingCalled();
        $modinfo->somefield = 'Some value';
        $this->assertDebuggingCalled();
        $this->assertEmpty($modinfo->somefield);
        $this->assertDebuggingCalled();

                $this->assertFalse(empty($modinfo->cms));
        $this->assertTrue(isset($modinfo->cms));
        $modinfo->cms = 'Illegal overwriting';
        $this->assertDebuggingCalled();
        $this->assertNotEquals('Illegal overwriting', $modinfo->cms);
    }

    public function test_is_user_access_restricted_by_capability() {
        global $DB;

        $this->resetAfterTest();

                $course = $this->getDataGenerator()->create_course();
        $assign = $this->getDataGenerator()->create_module('assign', array('course'=>$course->id));

                $coursecontext = context_course::instance($course->id);
        $studentrole = $DB->get_record('role', array('shortname' => 'student'), '*', MUST_EXIST);
        $student = $this->getDataGenerator()->create_user();
        role_assign($studentrole->id, $student->id, $coursecontext);
        $enrolplugin = enrol_get_plugin('manual');
        $enrolinstance = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'manual'));
        $enrolplugin->enrol_user($enrolinstance, $student->id);
        $this->setUser($student);

                $cm = get_fast_modinfo($course->id)->instances['assign'][$assign->id];
        $this->assertTrue($cm->uservisible);
        $this->assertFalse($cm->is_user_access_restricted_by_capability());

                role_change_permission($studentrole->id, $coursecontext, 'mod/assign:view', CAP_PROHIBIT);
        get_fast_modinfo($course->id, 0, true);
        $cm = get_fast_modinfo($course->id)->instances['assign'][$assign->id];
        $this->assertFalse($cm->uservisible);
        $this->assertTrue($cm->is_user_access_restricted_by_capability());

                role_change_permission($studentrole->id, $coursecontext, 'mod/assign:view', CAP_INHERIT);
        get_fast_modinfo($course->id, 0, true);
        $cm = get_fast_modinfo($course->id)->instances['assign'][$assign->id];
        $this->assertTrue($cm->uservisible);
        $this->assertFalse($cm->is_user_access_restricted_by_capability());

                role_change_permission($studentrole->id, context_module::instance($cm->id), 'mod/assign:view', CAP_PROHIBIT);
        get_fast_modinfo($course->id, 0, true);
        $cm = get_fast_modinfo($course->id)->instances['assign'][$assign->id];
        $this->assertFalse($cm->uservisible);
        $this->assertTrue($cm->is_user_access_restricted_by_capability());

                $this->setAdminUser();
        $cm = get_fast_modinfo($course->id)->instances['assign'][$assign->id];
        $this->assertTrue($cm->uservisible);
        $this->assertFalse($cm->is_user_access_restricted_by_capability());
        $cm = get_fast_modinfo($course->id, $student->id)->instances['assign'][$assign->id];
        $this->assertFalse($cm->uservisible);
        $this->assertTrue($cm->is_user_access_restricted_by_capability());
    }

    
    public function test_cm_info_property_deprecations() {
        global $DB, $CFG;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course( array('format' => 'topics', 'numsections' => 3),
                array('createsections' => true));
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $cm = get_fast_modinfo($course->id)->instances['forum'][$forum->id];

        $cm->get_url();
        $this->assertDebuggingCalled('cm_info::get_url() is deprecated, please use the property cm_info->url instead.');

        $cm->get_content();
        $this->assertDebuggingCalled('cm_info::get_content() is deprecated, please use the property cm_info->content instead.');

        $cm->get_extra_classes();
        $this->assertDebuggingCalled('cm_info::get_extra_classes() is deprecated, please use the property cm_info->extraclasses instead.');

        $cm->get_on_click();
        $this->assertDebuggingCalled('cm_info::get_on_click() is deprecated, please use the property cm_info->onclick instead.');

        $cm->get_custom_data();
        $this->assertDebuggingCalled('cm_info::get_custom_data() is deprecated, please use the property cm_info->customdata instead.');

        $cm->get_after_link();
        $this->assertDebuggingCalled('cm_info::get_after_link() is deprecated, please use the property cm_info->afterlink instead.');

        $cm->get_after_edit_icons();
        $this->assertDebuggingCalled('cm_info::get_after_edit_icons() is deprecated, please use the property cm_info->afterediticons instead.');

        $cm->obtain_dynamic_data();
        $this->assertDebuggingCalled('cm_info::obtain_dynamic_data() is deprecated and should not be used.');
    }

    
    public function test_cm_info_get_course_module_record() {
        global $DB, $CFG;

        $this->resetAfterTest();

        set_config('enableavailability', 1);
        set_config('enablecompletion', 1);

        $course = $this->getDataGenerator()->create_course(
                array('format' => 'topics', 'numsections' => 3, 'enablecompletion' => 1),
                array('createsections' => true));
        $mods = array();
        $mods[0] = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $mods[1] = $this->getDataGenerator()->create_module('assign',
                array('course' => $course->id,
                    'section' => 3,
                    'idnumber' => '12345',
                    'showdescription' => true
                    ));
                $availabilityvalue = '{"op":"|","show":true,"c":[{"type":"date","d":">=","t":4}]}';
        $mods[2] = $this->getDataGenerator()->create_module('book',
                array('course' => $course->id,
                    'indent' => 5,
                    'availability' => $availabilityvalue,
                    'showdescription' => false,
                    'completion' => true,
                    'completionview' => true,
                    'completionexpected' => time() + 5000,
                    ));
        $mods[3] = $this->getDataGenerator()->create_module('forum',
                array('course' => $course->id,
                    'visible' => 0,
                    'groupmode' => 1,
                    'availability' => null));
        $mods[4] = $this->getDataGenerator()->create_module('forum',
                array('course' => $course->id,
                    'grouping' => 12));

        $modinfo = get_fast_modinfo($course->id);

                $dbfields = array_keys($DB->get_columns('course_modules'));
        sort($dbfields);
        $cmrecord = $modinfo->get_cm($mods[0]->cmid)->get_course_module_record();
        $cmrecordfields = array_keys((array)$cmrecord);
        sort($cmrecordfields);
        $this->assertEquals($dbfields, $cmrecordfields);

                        $cmrecordfull = $modinfo->get_cm($mods[0]->cmid)->get_course_module_record(true);
        $cmrecordfullfields = array_keys((array)$cmrecordfull);
        $cm = get_coursemodule_from_id(null, $mods[0]->cmid, 0, true, MUST_EXIST);
        $cmfields = array_keys((array)$cm);
        $this->assertEquals($cmfields, $cmrecordfullfields);

                        $cm = get_coursemodule_from_instance('forum', $mods[0]->id, null, true, MUST_EXIST);
        $cmfields = array_keys((array)$cm);
        $this->assertEquals($cmfields, $cmrecordfullfields);

                $cm1 = get_coursemodule_from_id(null, $mods[0]->cmid, 0, true, MUST_EXIST);
        $cm2 = get_coursemodule_from_instance('forum', $mods[0]->id, 0, true, MUST_EXIST);
        $cminfo = $modinfo->get_cm($mods[0]->cmid);
        $record = $DB->get_record('course_modules', array('id' => $mods[0]->cmid));
        $this->assertEquals($record, $cminfo->get_course_module_record());
        $this->assertEquals($cm1, $cminfo->get_course_module_record(true));
        $this->assertEquals($cm2, $cminfo->get_course_module_record(true));

        $cm1 = get_coursemodule_from_id(null, $mods[1]->cmid, 0, true, MUST_EXIST);
        $cm2 = get_coursemodule_from_instance('assign', $mods[1]->id, 0, true, MUST_EXIST);
        $cminfo = $modinfo->get_cm($mods[1]->cmid);
        $record = $DB->get_record('course_modules', array('id' => $mods[1]->cmid));
        $this->assertEquals($record, $cminfo->get_course_module_record());
        $this->assertEquals($cm1, $cminfo->get_course_module_record(true));
        $this->assertEquals($cm2, $cminfo->get_course_module_record(true));

        $cm1 = get_coursemodule_from_id(null, $mods[2]->cmid, 0, true, MUST_EXIST);
        $cm2 = get_coursemodule_from_instance('book', $mods[2]->id, 0, true, MUST_EXIST);
        $cminfo = $modinfo->get_cm($mods[2]->cmid);
        $record = $DB->get_record('course_modules', array('id' => $mods[2]->cmid));
        $this->assertEquals($record, $cminfo->get_course_module_record());
        $this->assertEquals($cm1, $cminfo->get_course_module_record(true));
        $this->assertEquals($cm2, $cminfo->get_course_module_record(true));

        $cm1 = get_coursemodule_from_id(null, $mods[3]->cmid, 0, true, MUST_EXIST);
        $cm2 = get_coursemodule_from_instance('forum', $mods[3]->id, 0, true, MUST_EXIST);
        $cminfo = $modinfo->get_cm($mods[3]->cmid);
        $record = $DB->get_record('course_modules', array('id' => $mods[3]->cmid));
        $this->assertEquals($record, $cminfo->get_course_module_record());
        $this->assertEquals($cm1, $cminfo->get_course_module_record(true));
        $this->assertEquals($cm2, $cminfo->get_course_module_record(true));

        $cm1 = get_coursemodule_from_id(null, $mods[4]->cmid, 0, true, MUST_EXIST);
        $cm2 = get_coursemodule_from_instance('forum', $mods[4]->id, 0, true, MUST_EXIST);
        $cminfo = $modinfo->get_cm($mods[4]->cmid);
        $record = $DB->get_record('course_modules', array('id' => $mods[4]->cmid));
        $this->assertEquals($record, $cminfo->get_course_module_record());
        $this->assertEquals($cm1, $cminfo->get_course_module_record(true));
        $this->assertEquals($cm2, $cminfo->get_course_module_record(true));

    }

    
    public function test_availability_property() {
        global $DB, $CFG;

        $this->resetAfterTest();

                $course = $this->getDataGenerator()->create_course(
                array('format' => 'topics', 'numsections' => 3),
                array('createsections' => true));
        $forum = $this->getDataGenerator()->create_module('forum',
                array('course' => $course->id));
        $forum2 = $this->getDataGenerator()->create_module('forum',
                array('course' => $course->id));

                $modinfo = get_fast_modinfo($course->id);
        $cm = $modinfo->get_cm($forum->cmid);
        $this->assertNull($cm->availability);
        $section = $modinfo->get_section_info(1, MUST_EXIST);
        $this->assertNull($section->availability);

                $DB->set_field('course_modules', 'availability', '{}', array('id' => $cm->id));
        $DB->set_field('course_sections', 'availability', '{}', array('id' => $section->id));

                rebuild_course_cache($course->id, true);
        get_fast_modinfo(0, 0, true);
        $modinfo = get_fast_modinfo($course->id);

                $cm = $modinfo->get_cm($forum->cmid);
        $this->assertEquals('{}', $cm->availability);
        $section = $modinfo->get_section_info(1, MUST_EXIST);
        $this->assertEquals('{}', $section->availability);

                $cm = $modinfo->get_cm($forum2->cmid);
        $this->assertNull($cm->availability);
        $section = $modinfo->get_section_info(2, MUST_EXIST);
        $this->assertNull($section->availability);
    }

    
    public function test_get_groups() {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();

                $course1 = $generator->create_course();
        $course2 = $generator->create_course();
        $course3 = $generator->create_course();

                $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $user3 = $generator->create_user();

                $generator->enrol_user($user1->id, $course1->id);
        $generator->enrol_user($user2->id, $course2->id);
        $generator->enrol_user($user3->id, $course2->id);
        $generator->enrol_user($user3->id, $course3->id);

                $group1 = $generator->create_group(array('courseid' => $course1->id));
        $group2 = $generator->create_group(array('courseid' => $course2->id));
        $group3 = $generator->create_group(array('courseid' => $course2->id));

                $this->assertTrue($generator->create_group_member(array('groupid' => $group1->id, 'userid' => $user1->id)));
        $this->assertTrue($generator->create_group_member(array('groupid' => $group2->id, 'userid' => $user2->id)));
        $this->assertTrue($generator->create_group_member(array('groupid' => $group3->id, 'userid' => $user2->id)));
        $this->assertTrue($generator->create_group_member(array('groupid' => $group2->id, 'userid' => $user3->id)));

                $grouping1 = $generator->create_grouping(array('courseid' => $course1->id));
        $grouping2 = $generator->create_grouping(array('courseid' => $course2->id));

                groups_assign_grouping($grouping1->id, $group1->id);
        groups_assign_grouping($grouping2->id, $group2->id);
        groups_assign_grouping($grouping2->id, $group3->id);

                $modinfo = get_fast_modinfo($course1, $user1->id);
        $groups = $modinfo->get_groups($grouping1->id);
        $this->assertCount(1, $groups);
        $this->assertArrayHasKey($group1->id, $groups);

                $modinfo = get_fast_modinfo($course2, $user2->id);
        $groups = $modinfo->get_groups();
        $this->assertCount(2, $groups);
        $this->assertTrue(in_array($group2->id, $groups));
        $this->assertTrue(in_array($group3->id, $groups));

                $modinfo = get_fast_modinfo($course3, $user3->id);
        $groups = $modinfo->get_groups();
        $this->assertCount(0, $groups);
        $this->assertArrayNotHasKey($group1->id, $groups);
    }

    
    public function test_create() {
        global $CFG, $DB;
        $this->resetAfterTest();

                $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $page = $generator->create_module('page', array('course' => $course->id,
                'name' => 'Annie'));

                $this->assertNull(cm_info::create(null));

                $cm = cm_info::create(
                (object)array('id' => $page->cmid, 'course' => $course->id));
        $this->assertInstanceOf('cm_info', $cm);
        $this->assertEquals('Annie', $cm->name);

                $this->assertSame($cm, cm_info::create($cm));

                try {
            cm_info::create((object)array('id' => $page->cmid));
            $this->fail();
        } catch (Exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }

                $hiddenpage = $generator->create_module('page', array('course' => $course->id,
                'name' => 'Annie', 'visible' => 0));

                $user = $generator->create_user();
        $generator->enrol_user($user->id, $course->id);
        $manager = $generator->create_user();
        $generator->enrol_user($manager->id, $course->id,
                $DB->get_field('role', 'id', array('shortname' => 'manager'), MUST_EXIST));

                $cm = cm_info::create((object)array('id' => $page->cmid, 'course' => $course->id),
                $user->id);
        $this->assertTrue($cm->uservisible);
        $cm = cm_info::create((object)array('id' => $hiddenpage->cmid, 'course' => $course->id),
                $user->id);
        $this->assertFalse($cm->uservisible);

                $cm = cm_info::create((object)array('id' => $hiddenpage->cmid, 'course' => $course->id),
                $manager->id);
        $this->assertTrue($cm->uservisible);
    }

    
    public function test_get_course_and_cm_from_cmid() {
        global $CFG, $DB;
        $this->resetAfterTest();

                $generator = $this->getDataGenerator();
        $course = $generator->create_course(array('shortname' => 'Halls'));
        $page = $generator->create_module('page', array('course' => $course->id,
                'name' => 'Annie'));

                list($course, $cm) = get_course_and_cm_from_cmid($page->cmid);
        $this->assertEquals('Halls', $course->shortname);
        $this->assertInstanceOf('cm_info', $cm);
        $this->assertEquals('Annie', $cm->name);

                list($course, $cm) = get_course_and_cm_from_cmid($page->cmid, 'page');
        $this->assertEquals('Annie', $cm->name);

                $fakecm = (object)array('id' => $page->cmid);
        list($course, $cm) = get_course_and_cm_from_cmid($fakecm);
        $this->assertEquals('Halls', $course->shortname);
        $this->assertEquals('Annie', $cm->name);

                $fakecm->course = $course->id;
        list($course, $cm) = get_course_and_cm_from_cmid($fakecm);
        $this->assertEquals('Halls', $course->shortname);
        $this->assertEquals('Annie', $cm->name);

                list($course, $cm) = get_course_and_cm_from_cmid($page->cmid, 'page', $course->id);
        $this->assertEquals('Annie', $cm->name);

                        $course->silly = true;
        list($course, $cm) = get_course_and_cm_from_cmid($page->cmid, 'page', $course);
        $this->assertEquals('Annie', $cm->name);
        $this->assertTrue($course->silly);

                try {
            get_course_and_cm_from_cmid($page->cmid, 'forum');
            $this->fail();
        } catch (moodle_exception $e) {
            $this->assertEquals('invalidcoursemodule', $e->errorcode);
        }

                try {
            get_course_and_cm_from_cmid($page->cmid, 'pigs can fly');
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertContains('Invalid modulename parameter', $e->getMessage());
        }

                try {
            get_course_and_cm_from_cmid($page->cmid + 1);
            $this->fail();
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('dml_exception', $e);
        }

                $hiddenpage = $generator->create_module('page', array('course' => $course->id,
                'name' => 'Annie', 'visible' => 0));

                $user = $generator->create_user();
        $generator->enrol_user($user->id, $course->id);
        $manager = $generator->create_user();
        $generator->enrol_user($manager->id, $course->id,
                $DB->get_field('role', 'id', array('shortname' => 'manager'), MUST_EXIST));

                list($course, $cm) = get_course_and_cm_from_cmid($page->cmid, 'page', 0, $user->id);
        $this->assertTrue($cm->uservisible);
        list($course, $cm) = get_course_and_cm_from_cmid($hiddenpage->cmid, 'page', 0, $user->id);
        $this->assertFalse($cm->uservisible);

                list($course, $cm) = get_course_and_cm_from_cmid($hiddenpage->cmid, 'page', 0, $manager->id);
        $this->assertTrue($cm->uservisible);
    }

    
    public function test_get_course_and_cm_from_instance() {
        global $CFG, $DB;
        $this->resetAfterTest();

                $generator = $this->getDataGenerator();
        $course = $generator->create_course(array('shortname' => 'Halls'));
        $page = $generator->create_module('page', array('course' => $course->id,
                'name' => 'Annie'));

                list($course, $cm) = get_course_and_cm_from_instance($page->id, 'page');
        $this->assertEquals('Halls', $course->shortname);
        $this->assertInstanceOf('cm_info', $cm);
        $this->assertEquals('Annie', $cm->name);

                $fakeinstance = (object)array('id' => $page->id);
        list($course, $cm) = get_course_and_cm_from_instance($fakeinstance, 'page');
        $this->assertEquals('Halls', $course->shortname);
        $this->assertEquals('Annie', $cm->name);

                $fakeinstance->course = $course->id;
        list($course, $cm) = get_course_and_cm_from_instance($fakeinstance, 'page');
        $this->assertEquals('Halls', $course->shortname);
        $this->assertEquals('Annie', $cm->name);

                list($course, $cm) = get_course_and_cm_from_instance($page->id, 'page', $course->id);
        $this->assertEquals('Annie', $cm->name);

                        $course->silly = true;
        list($course, $cm) = get_course_and_cm_from_instance($page->id, 'page', $course);
        $this->assertEquals('Annie', $cm->name);
        $this->assertTrue($course->silly);

                try {
            get_course_and_cm_from_instance($page->id, 'forum');
            $this->fail();
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('dml_exception', $e);
        }

                try {
            get_course_and_cm_from_cmid($page->cmid, '1337 h4x0ring');
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertContains('Invalid modulename parameter', $e->getMessage());
        }

                $hiddenpage = $generator->create_module('page', array('course' => $course->id,
                'name' => 'Annie', 'visible' => 0));

                $user = $generator->create_user();
        $generator->enrol_user($user->id, $course->id);
        $manager = $generator->create_user();
        $generator->enrol_user($manager->id, $course->id,
                $DB->get_field('role', 'id', array('shortname' => 'manager'), MUST_EXIST));

                list($course, $cm) = get_course_and_cm_from_cmid($page->cmid, 'page', 0, $user->id);
        $this->assertTrue($cm->uservisible);
        list($course, $cm) = get_course_and_cm_from_cmid($hiddenpage->cmid, 'page', 0, $user->id);
        $this->assertFalse($cm->uservisible);

                list($course, $cm) = get_course_and_cm_from_cmid($hiddenpage->cmid, 'page', 0, $manager->id);
        $this->assertTrue($cm->uservisible);
    }
}
