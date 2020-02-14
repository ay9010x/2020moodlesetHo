<?php



defined('MOODLE_INTERNAL') || die();
global $CFG;

use core_competency\course_competency;
use core_competency\course_module_competency;
use tool_lpmigrate\framework_mapper;
use tool_lpmigrate\framework_processor;


class tool_lpmigrate_framework_processor_testcase extends advanced_testcase {

    
    public function setUp() {
        $this->resetAfterTest(true);
        $dg = $this->getDataGenerator();
        $lpg = $dg->get_plugin_generator('core_competency');

        $f1 = $lpg->create_framework(array('idnumber' => 'BIO2015'));
        $f2 = $lpg->create_framework(array('idnumber' => 'BIO2016'));

        $f1comps = array();
        $f1comps['A1'] = $lpg->create_competency(array('competencyframeworkid' => $f1->get_id(), 'idnumber' => 'A1'));
        $f1comps['A2'] = $lpg->create_competency(array('competencyframeworkid' => $f1->get_id(), 'idnumber' => 'A2'));
        $f1comps['A3'] = $lpg->create_competency(array('competencyframeworkid' => $f1->get_id(), 'idnumber' => 'A3'));
        $f1comps['X1'] = $lpg->create_competency(array('competencyframeworkid' => $f1->get_id(), 'idnumber' => 'X1'));

        $f2comps = array();
        $f2comps['A1'] = $lpg->create_competency(array('competencyframeworkid' => $f2->get_id(), 'idnumber' => 'A1'));
        $f2comps['A2'] = $lpg->create_competency(array('competencyframeworkid' => $f2->get_id(), 'idnumber' => 'A2'));
        $f2comps['A3'] = $lpg->create_competency(array('competencyframeworkid' => $f2->get_id(), 'idnumber' => 'A3'));
        $f2comps['Y1'] = $lpg->create_competency(array('competencyframeworkid' => $f2->get_id(), 'idnumber' => 'Y1'));

        $c1 = $dg->create_course(array('startdate' => time() - 72000));
        $c2 = $dg->create_course(array('startdate' => time() + 72000));
        $cms = array(
            $c1->id => array(
                'F1' => $dg->create_module('forum', (object) array('course' => $c1->id)),
                'P1' => $dg->create_module('page', (object) array('course' => $c1->id)),
                'EmptyA' => $dg->create_module('page', (object) array('course' => $c1->id)),
            ),
            $c2->id => array(
                'F1' => $dg->create_module('forum', (object) array('course' => $c2->id)),
                'EmptyB' => $dg->create_module('page', (object) array('course' => $c2->id)),
            ),
        );

                $ccs = array(
            $c1->id => array(
                $f1comps['A1']->get_id() => $lpg->create_course_competency(array('courseid' => $c1->id,
                    'competencyid' => $f1comps['A1']->get_id())),
                $f1comps['A3']->get_id() => $lpg->create_course_competency(array('courseid' => $c1->id,
                    'competencyid' => $f1comps['A3']->get_id())),
                $f1comps['X1']->get_id() => $lpg->create_course_competency(array('courseid' => $c1->id,
                    'competencyid' => $f1comps['X1']->get_id())),
            ),
            $c2->id => array(
                $f1comps['A2']->get_id() => $lpg->create_course_competency(array('courseid' => $c2->id,
                    'competencyid' => $f1comps['A2']->get_id())),
                $f1comps['A3']->get_id() => $lpg->create_course_competency(array('courseid' => $c2->id,
                    'competencyid' => $f1comps['A3']->get_id())),
            )
        );

                $cmcs = array(
            $cms[$c1->id]['F1']->cmid => array(
                $f1comps['A1']->get_id() => $lpg->create_course_module_competency(array(
                    'cmid' => $cms[$c1->id]['F1']->cmid,
                    'competencyid' => $f1comps['A1']->get_id()
                )),
                $f1comps['X1']->get_id() => $lpg->create_course_module_competency(array(
                    'cmid' => $cms[$c1->id]['F1']->cmid,
                    'competencyid' => $f1comps['X1']->get_id()
                )),
            ),
            $cms[$c1->id]['P1']->cmid => array(
                $f1comps['A3']->get_id() => $lpg->create_course_module_competency(array(
                    'cmid' => $cms[$c1->id]['P1']->cmid,
                    'competencyid' => $f1comps['A3']->get_id()
                )),
            ),
            $cms[$c2->id]['F1']->cmid => array(
                $f1comps['A2']->get_id() => $lpg->create_course_module_competency(array(
                    'cmid' => $cms[$c2->id]['F1']->cmid,
                    'competencyid' => $f1comps['A2']->get_id()
                )),
                $f1comps['A3']->get_id() => $lpg->create_course_module_competency(array(
                    'cmid' => $cms[$c2->id]['F1']->cmid,
                    'competencyid' => $f1comps['A3']->get_id()
                )),
            ),
        );

        $this->assertCourseCompetencyExists($c1, $f1comps['A1']);
        $this->assertCourseCompetencyExists($c1, $f1comps['A3']);
        $this->assertCourseCompetencyExists($c1, $f1comps['X1']);
        $this->assertCourseCompetencyExists($c2, $f1comps['A2']);
        $this->assertCourseCompetencyExists($c2, $f1comps['A3']);
        $this->assertModuleCompetencyExists($cms[$c1->id]['F1'], $f1comps['A1']);
        $this->assertModuleCompetencyExists($cms[$c1->id]['P1'], $f1comps['A3']);
        $this->assertModuleCompetencyExists($cms[$c1->id]['F1'], $f1comps['X1']);
        $this->assertModuleCompetencyExists($cms[$c2->id]['F1'], $f1comps['A2']);
        $this->assertModuleCompetencyExists($cms[$c2->id]['F1'], $f1comps['A3']);

        $this->f1 = $f1;
        $this->f1comps = $f1comps;
        $this->f2 = $f2;
        $this->f2comps = $f2comps;
        $this->c1 = $c1;
        $this->c2 = $c2;
        $this->cms = $cms;
        $this->ccs = $ccs;
        $this->cmcs = $cmcs;
    }

    public function test_simple_migration() {
        $this->setAdminUser();

        $mapper = new framework_mapper($this->f1->get_id(), $this->f2->get_id());
        $mapper->automap();
        $processor = new framework_processor($mapper);
        $processor->proceed();

        $this->assertEquals(2, $processor->get_courses_found_count());
        $this->assertEquals(5, $processor->get_expected_course_competency_migrations());
        $this->assertEquals(4, $processor->get_course_competency_migrations());
        $this->assertEquals(4, $processor->get_course_competency_removals());

        $this->assertEquals(3, $processor->get_cms_found_count());
        $this->assertEquals(5, $processor->get_expected_module_competency_migrations());
        $this->assertEquals(4, $processor->get_module_competency_migrations());
        $this->assertEquals(4, $processor->get_module_competency_removals());

        $this->assertEquals(array(), $processor->get_warnings());
        $this->assertEquals(array(), $processor->get_errors());
        $this->assertEquals(array($this->f1comps['X1']->get_id() => true), $processor->get_missing_mappings());

        $this->assertCourseCompetencyMigrated($this->c1, $this->f1comps['A1'], $this->f2comps['A1']);
        $this->assertCourseCompetencyMigrated($this->c1, $this->f1comps['A3'], $this->f2comps['A3']);
        $this->assertCourseCompetencyMigrated($this->c2, $this->f1comps['A2'], $this->f2comps['A2']);
        $this->assertCourseCompetencyMigrated($this->c2, $this->f1comps['A3'], $this->f2comps['A3']);

        $this->assertModuleCompetencyMigrated($this->cms[$this->c1->id]['F1'], $this->f1comps['A1'], $this->f2comps['A1']);
        $this->assertModuleCompetencyMigrated($this->cms[$this->c1->id]['P1'], $this->f1comps['A3'], $this->f2comps['A3']);
        $this->assertModuleCompetencyMigrated($this->cms[$this->c2->id]['F1'], $this->f1comps['A2'], $this->f2comps['A2']);
        $this->assertModuleCompetencyMigrated($this->cms[$this->c2->id]['F1'], $this->f1comps['A3'], $this->f2comps['A3']);

        $this->assertCourseCompetencyExists($this->c1, $this->f1comps['X1']);
        $this->assertModuleCompetencyExists($this->cms[$this->c1->id]['F1'], $this->f1comps['X1']);
    }

    public function test_remove_when_missing() {
        $this->setAdminUser();

        $mapper = new framework_mapper($this->f1->get_id(), $this->f2->get_id());
        $mapper->automap();
        $processor = new framework_processor($mapper);
        $processor->set_remove_when_mapping_is_missing(true);
        $processor->proceed();

        $this->assertEquals(2, $processor->get_courses_found_count());
        $this->assertEquals(5, $processor->get_expected_course_competency_migrations());
        $this->assertEquals(4, $processor->get_course_competency_migrations());
        $this->assertEquals(5, $processor->get_course_competency_removals());

        $this->assertEquals(3, $processor->get_cms_found_count());
        $this->assertEquals(5, $processor->get_expected_module_competency_migrations());
        $this->assertEquals(4, $processor->get_module_competency_migrations());
        $this->assertEquals(5, $processor->get_module_competency_removals());

        $this->assertCount(0, $processor->get_errors());
        $this->assertCount(0, $processor->get_warnings());

        $this->assertCourseCompetencyNotExists($this->c1, $this->f1comps['X1']);
        $this->assertModuleCompetencyNotExists($this->cms[$this->c1->id]['F1'], $this->f1comps['X1']);
    }

    public function test_allowed_courses() {
        $this->setAdminUser();

        $mapper = new framework_mapper($this->f1->get_id(), $this->f2->get_id());
        $mapper->automap();
        $processor = new framework_processor($mapper);
        $processor->set_allowedcourses(array($this->c1->id));
        $processor->proceed();

        $this->assertEquals(1, $processor->get_courses_found_count());
        $this->assertEquals(3, $processor->get_expected_course_competency_migrations());
        $this->assertEquals(2, $processor->get_course_competency_migrations());
        $this->assertEquals(2, $processor->get_course_competency_removals());

        $this->assertEquals(2, $processor->get_cms_found_count());
        $this->assertEquals(3, $processor->get_expected_module_competency_migrations());
        $this->assertEquals(2, $processor->get_module_competency_migrations());
        $this->assertEquals(2, $processor->get_module_competency_removals());

        $this->assertCount(0, $processor->get_errors());
        $this->assertCount(0, $processor->get_warnings());

        $this->assertCourseCompetencyMigrated($this->c1, $this->f1comps['A1'], $this->f2comps['A1']);
        $this->assertCourseCompetencyMigrated($this->c1, $this->f1comps['A3'], $this->f2comps['A3']);
        $this->assertModuleCompetencyMigrated($this->cms[$this->c1->id]['F1'], $this->f1comps['A1'], $this->f2comps['A1']);
        $this->assertModuleCompetencyMigrated($this->cms[$this->c1->id]['P1'], $this->f1comps['A3'], $this->f2comps['A3']);

        $this->assertCourseCompetencyNotMigrated($this->c2, $this->f1comps['A2'], $this->f2comps['A2']);
        $this->assertCourseCompetencyNotMigrated($this->c2, $this->f1comps['A3'], $this->f2comps['A3']);
        $this->assertModuleCompetencyNotMigrated($this->cms[$this->c2->id]['F1'], $this->f1comps['A2'], $this->f2comps['A2']);
        $this->assertModuleCompetencyNotMigrated($this->cms[$this->c2->id]['F1'], $this->f1comps['A3'], $this->f2comps['A3']);
    }

    public function test_disallowed_courses() {
        $this->setAdminUser();

        $mapper = new framework_mapper($this->f1->get_id(), $this->f2->get_id());
        $mapper->automap();
        $processor = new framework_processor($mapper);
        $processor->set_disallowedcourses(array($this->c2->id));
        $processor->proceed();

        $this->assertEquals(1, $processor->get_courses_found_count());
        $this->assertEquals(3, $processor->get_expected_course_competency_migrations());
        $this->assertEquals(2, $processor->get_course_competency_migrations());
        $this->assertEquals(2, $processor->get_course_competency_removals());

        $this->assertEquals(2, $processor->get_cms_found_count());
        $this->assertEquals(3, $processor->get_expected_module_competency_migrations());
        $this->assertEquals(2, $processor->get_module_competency_migrations());
        $this->assertEquals(2, $processor->get_module_competency_removals());

        $this->assertCount(0, $processor->get_errors());
        $this->assertCount(0, $processor->get_warnings());

        $this->assertCourseCompetencyMigrated($this->c1, $this->f1comps['A1'], $this->f2comps['A1']);
        $this->assertCourseCompetencyMigrated($this->c1, $this->f1comps['A3'], $this->f2comps['A3']);
        $this->assertModuleCompetencyMigrated($this->cms[$this->c1->id]['F1'], $this->f1comps['A1'], $this->f2comps['A1']);
        $this->assertModuleCompetencyMigrated($this->cms[$this->c1->id]['P1'], $this->f1comps['A3'], $this->f2comps['A3']);

        $this->assertCourseCompetencyNotMigrated($this->c2, $this->f1comps['A2'], $this->f2comps['A2']);
        $this->assertCourseCompetencyNotMigrated($this->c2, $this->f1comps['A3'], $this->f2comps['A3']);
        $this->assertModuleCompetencyNotMigrated($this->cms[$this->c2->id]['F1'], $this->f1comps['A2'], $this->f2comps['A2']);
        $this->assertModuleCompetencyNotMigrated($this->cms[$this->c2->id]['F1'], $this->f1comps['A3'], $this->f2comps['A3']);
    }

    public function test_course_start_date_from() {
        $this->setAdminUser();

        $mapper = new framework_mapper($this->f1->get_id(), $this->f2->get_id());
        $mapper->automap();
        $processor = new framework_processor($mapper);
        $processor->set_course_start_date_from(time());
        $processor->proceed();

        $this->assertEquals(1, $processor->get_courses_found_count());
        $this->assertEquals(2, $processor->get_expected_course_competency_migrations());
        $this->assertEquals(2, $processor->get_course_competency_migrations());
        $this->assertEquals(2, $processor->get_course_competency_removals());

        $this->assertEquals(1, $processor->get_cms_found_count());
        $this->assertEquals(2, $processor->get_expected_module_competency_migrations());
        $this->assertEquals(2, $processor->get_module_competency_migrations());
        $this->assertEquals(2, $processor->get_module_competency_removals());

        $this->assertCount(0, $processor->get_errors());
        $this->assertCount(0, $processor->get_warnings());

        $this->assertCourseCompetencyNotMigrated($this->c1, $this->f1comps['A1'], $this->f2comps['A1']);
        $this->assertCourseCompetencyNotMigrated($this->c1, $this->f1comps['A3'], $this->f2comps['A3']);
        $this->assertModuleCompetencyNotMigrated($this->cms[$this->c1->id]['F1'], $this->f1comps['A1'], $this->f2comps['A1']);
        $this->assertModuleCompetencyNotMigrated($this->cms[$this->c1->id]['P1'], $this->f1comps['A3'], $this->f2comps['A3']);

        $this->assertCourseCompetencyMigrated($this->c2, $this->f1comps['A2'], $this->f2comps['A2']);
        $this->assertCourseCompetencyMigrated($this->c2, $this->f1comps['A3'], $this->f2comps['A3']);
        $this->assertModuleCompetencyMigrated($this->cms[$this->c2->id]['F1'], $this->f1comps['A2'], $this->f2comps['A2']);
        $this->assertModuleCompetencyMigrated($this->cms[$this->c2->id]['F1'], $this->f1comps['A3'], $this->f2comps['A3']);
    }

    public function test_destination_competency_exists() {
        $this->setAdminUser();
        $lpg = $this->getDataGenerator()->get_plugin_generator('core_competency');

                $lpg->create_course_competency(array('courseid' => $this->c1->id, 'competencyid' => $this->f2comps['A1']->get_id()));

                $lpg->create_course_module_competency(array(
            'cmid' => $this->cms[$this->c2->id]['F1']->cmid,
            'competencyid' => $this->f2comps['A2']->get_id()
        ));

        $mapper = new framework_mapper($this->f1->get_id(), $this->f2->get_id());
        $mapper->automap();
        $processor = new framework_processor($mapper);
        $processor->proceed();

        $this->assertEquals(2, $processor->get_courses_found_count());
        $this->assertEquals(5, $processor->get_expected_course_competency_migrations());
        $this->assertEquals(3, $processor->get_course_competency_migrations());
        $this->assertEquals(2, $processor->get_course_competency_removals());

        $this->assertEquals(3, $processor->get_cms_found_count());
        $this->assertEquals(5, $processor->get_expected_module_competency_migrations());
        $this->assertEquals(3, $processor->get_module_competency_migrations());
        $this->assertEquals(3, $processor->get_module_competency_removals());

        $this->assertEquals(array(), $processor->get_errors());
        $warnings = $processor->get_warnings();
        $this->assertCount(2, $warnings);

        $warning = array_shift($warnings);
        $this->assertEquals($this->c1->id, $warning['courseid']);
        $this->assertEquals($this->f1comps['A1']->get_id(), $warning['competencyid']);
        $this->assertEquals(null, $warning['cmid']);
        $this->assertRegexp('/competency already exists/', $warning['message']);

        $warning = array_shift($warnings);
        $this->assertEquals($this->c2->id, $warning['courseid']);
        $this->assertEquals($this->f1comps['A2']->get_id(), $warning['competencyid']);
        $this->assertEquals($this->cms[$this->c2->id]['F1']->cmid, $warning['cmid']);
        $this->assertRegexp('/competency already exists/', $warning['message']);

        $this->assertCourseCompetencyExists($this->c1, $this->f1comps['A1']);
        $this->assertModuleCompetencyExists($this->cms[$this->c2->id]['F1'], $this->f1comps['A2']);
    }

    public function test_destination_competency_exists_remove_original() {
        $this->setAdminUser();
        $lpg = $this->getDataGenerator()->get_plugin_generator('core_competency');

                $lpg->create_course_competency(array('courseid' => $this->c1->id, 'competencyid' => $this->f2comps['A1']->get_id()));

                $lpg->create_course_module_competency(array(
            'cmid' => $this->cms[$this->c2->id]['F1']->cmid,
            'competencyid' => $this->f2comps['A2']->get_id()
        ));

        $mapper = new framework_mapper($this->f1->get_id(), $this->f2->get_id());
        $mapper->automap();
        $processor = new framework_processor($mapper);
        $processor->set_remove_original_when_destination_already_present(true);
        $processor->proceed();

        $this->assertEquals(2, $processor->get_courses_found_count());
        $this->assertEquals(5, $processor->get_expected_course_competency_migrations());
        $this->assertEquals(3, $processor->get_course_competency_migrations());
        $this->assertEquals(4, $processor->get_course_competency_removals());

        $this->assertEquals(3, $processor->get_cms_found_count());
        $this->assertEquals(5, $processor->get_expected_module_competency_migrations());
        $this->assertEquals(3, $processor->get_module_competency_migrations());
        $this->assertEquals(4, $processor->get_module_competency_removals());

        $this->assertEquals(array(), $processor->get_errors());
        $this->assertEquals(array(), $processor->get_warnings());

        $this->assertCourseCompetencyNotExists($this->c1, $this->f1comps['A1']);
        $this->assertModuleCompetencyNotExists($this->cms[$this->c2->id]['F1'], $this->f1comps['A2']);
    }

    public function test_permission_exception() {

        $this->preventResetByRollback(); 
        $dg = $this->getDataGenerator();
        $u = $dg->create_user();
        $role = $dg->create_role();
        $sysctx = context_system::instance();

        $dg->enrol_user($u->id, $this->c1->id, 'editingteacher');
        $dg->enrol_user($u->id, $this->c2->id, 'editingteacher');
        assign_capability('moodle/competency:coursecompetencymanage', CAP_PROHIBIT, $role, $sysctx->id);
        role_assign($role, $u->id, context_course::instance($this->c1->id)->id);
        role_assign($role, $u->id, context_module::instance($this->cms[$this->c2->id]['F1']->cmid)->id);

        accesslib_clear_all_caches_for_unit_testing();
        $this->setUser($u);

                $mapper = new framework_mapper($this->f1->get_id(), $this->f2->get_id());
        $mapper->automap();
        $processor = new framework_processor($mapper);
        $processor->set_allowedcourses(array($this->c1->id));
        $processor->proceed();

        $this->assertEquals(1, $processor->get_courses_found_count());
        $this->assertEquals(3, $processor->get_expected_course_competency_migrations());
        $this->assertEquals(0, $processor->get_course_competency_migrations());
        $this->assertEquals(0, $processor->get_course_competency_removals());

        $this->assertEquals(2, $processor->get_cms_found_count());
        $this->assertEquals(3, $processor->get_expected_module_competency_migrations());
        $this->assertEquals(0, $processor->get_module_competency_migrations());
        $this->assertEquals(0, $processor->get_module_competency_removals());

        $this->assertEquals(array(), $processor->get_warnings());
        $errors = $processor->get_errors();
        $this->assertCount(2, $errors);
        $this->assertEquals($this->c1->id, $errors[0]['courseid']);
        $this->assertEquals($this->f1comps['A1']->get_id(), $errors[0]['competencyid']);
        $this->assertEquals(null, $errors[0]['cmid']);
        $this->assertRegexp('/Sorry, but you do not currently have permissions to do that/', $errors[0]['message']);
        $this->assertEquals($this->f1comps['A3']->get_id(), $errors[1]['competencyid']);

        $this->assertCourseCompetencyNotMigrated($this->c1, $this->f1comps['A1'], $this->f2comps['A1']);
        $this->assertCourseCompetencyNotMigrated($this->c1, $this->f1comps['A3'], $this->f2comps['A3']);
        $this->assertModuleCompetencyNotMigrated($this->cms[$this->c1->id]['F1'], $this->f1comps['A1'], $this->f2comps['A1']);
        $this->assertModuleCompetencyNotMigrated($this->cms[$this->c1->id]['P1'], $this->f1comps['A3'], $this->f2comps['A3']);

                $processor = new framework_processor($mapper);
        $processor->set_allowedcourses(array($this->c2->id));
        $processor->proceed();

        $this->assertEquals(1, $processor->get_courses_found_count());
        $this->assertEquals(2, $processor->get_expected_course_competency_migrations());
        $this->assertEquals(2, $processor->get_course_competency_migrations());
        $this->assertEquals(0, $processor->get_course_competency_removals());

        $this->assertEquals(1, $processor->get_cms_found_count());
        $this->assertEquals(2, $processor->get_expected_module_competency_migrations());
        $this->assertEquals(0, $processor->get_module_competency_migrations());
        $this->assertEquals(0, $processor->get_module_competency_removals());

        $this->assertEquals(array(), $processor->get_warnings());
        $errors = $processor->get_errors();
        $this->assertCount(2, $errors);
        $this->assertEquals($this->c2->id, $errors[0]['courseid']);
        $this->assertEquals($this->f1comps['A2']->get_id(), $errors[0]['competencyid']);
        $this->assertEquals($this->cms[$this->c2->id]['F1']->cmid, $errors[0]['cmid']);
        $this->assertRegexp('/Sorry, but you do not currently have permissions to do that/', $errors[0]['message']);
        $this->assertEquals($this->f1comps['A3']->get_id(), $errors[1]['competencyid']);

                $this->assertCourseCompetencyExists($this->c2, $this->f1comps['A2']);
        $this->assertCourseCompetencyExists($this->c2, $this->f1comps['A3']);
        $this->assertCourseCompetencyExists($this->c2, $this->f2comps['A2']);
        $this->assertCourseCompetencyExists($this->c2, $this->f2comps['A3']);

                $this->assertModuleCompetencyNotMigrated($this->cms[$this->c2->id]['F1'], $this->f1comps['A2'], $this->f2comps['A2']);
        $this->assertModuleCompetencyNotMigrated($this->cms[$this->c2->id]['F1'], $this->f1comps['A3'], $this->f2comps['A2']);
    }

    
    protected function assertCourseCompetencyExists($course, $competency) {
        $this->assertTrue(course_competency::record_exists_select("courseid = :courseid AND competencyid = :competencyid",
            array('courseid' => $course->id, 'competencyid' => $competency->get_id())));
    }

    
    protected function assertCourseCompetencyNotExists($course, $competency) {
        $this->assertFalse(course_competency::record_exists_select("courseid = :courseid AND competencyid = :competencyid",
            array('courseid' => $course->id, 'competencyid' => $competency->get_id())));
    }

    
    protected function assertCourseCompetencyMigrated($course, $compfrom, $compto) {
        $ccs = $this->ccs[$course->id];

        $this->assertCourseCompetencyNotExists($course, $compfrom);
        $this->assertCourseCompetencyExists($course, $compto);

        $before = $ccs[$compfrom->get_id()];
        $after = course_competency::get_record(array(
            'courseid' => $course->id,
            'competencyid' => $compto->get_id()
        ));

        $this->assertNotEquals($before->get_id(), $after->get_id());
        $this->assertEquals($before->get_courseid(), $after->get_courseid());
        $this->assertEquals($before->get_sortorder(), $after->get_sortorder());
        $this->assertEquals($before->get_ruleoutcome(), $after->get_ruleoutcome());
    }

    
    protected function assertCourseCompetencyNotMigrated($course, $compfrom, $compto) {
        $ccs = $this->ccs[$course->id];

        $this->assertCourseCompetencyExists($course, $compfrom);
        $this->assertCourseCompetencyNotExists($course, $compto);

        $before = $ccs[$compfrom->get_id()];
        $after = $ccs[$compfrom->get_id()];

        $this->assertEquals($before->get_id(), $after->get_id());
        $this->assertEquals($before->get_courseid(), $after->get_courseid());
        $this->assertEquals($before->get_sortorder(), $after->get_sortorder());
        $this->assertEquals($before->get_ruleoutcome(), $after->get_ruleoutcome());
    }

    
    protected function assertModuleCompetencyExists($cm, $competency) {
        $this->assertTrue(course_module_competency::record_exists_select("cmid = :cmid AND competencyid = :competencyid",
            array('cmid' => $cm->cmid, 'competencyid' => $competency->get_id())));
    }

    
    protected function assertModuleCompetencyNotExists($cm, $competency) {
        $this->assertFalse(course_module_competency::record_exists_select("cmid = :cmid AND competencyid = :competencyid",
            array('cmid' => $cm->cmid, 'competencyid' => $competency->get_id())));
    }

    
    protected function assertModuleCompetencyMigrated($cm, $compfrom, $compto) {
        $cmcs = $this->cmcs[$cm->cmid];

        $this->assertModuleCompetencyNotExists($cm, $compfrom);
        $this->assertModuleCompetencyExists($cm, $compto);

        $before = $cmcs[$compfrom->get_id()];
        $after = course_module_competency::get_record(array(
            'cmid' => $cm->cmid,
            'competencyid' => $compto->get_id()
        ));

        $this->assertNotEquals($before->get_id(), $after->get_id());
        $this->assertEquals($before->get_cmid(), $after->get_cmid());
        $this->assertEquals($before->get_sortorder(), $after->get_sortorder());
        $this->assertEquals($before->get_ruleoutcome(), $after->get_ruleoutcome());
    }

    
    protected function assertModuleCompetencyNotMigrated($cm, $compfrom, $compto) {
        $cmcs = $this->cmcs[$cm->cmid];

        $this->assertModuleCompetencyExists($cm, $compfrom);
        $this->assertModuleCompetencyNotExists($cm, $compto);

        $before = $cmcs[$compfrom->get_id()];
        $after = $cmcs[$compfrom->get_id()];

        $this->assertEquals($before->get_id(), $after->get_id());
        $this->assertEquals($before->get_cmid(), $after->get_cmid());
        $this->assertEquals($before->get_sortorder(), $after->get_sortorder());
        $this->assertEquals($before->get_ruleoutcome(), $after->get_ruleoutcome());
    }

}