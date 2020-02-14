<?php


defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');

use core_competency\api;
use tool_lp\external;
use core_competency\invalid_persistent_exception;
use core_competency\plan;
use core_competency\related_competency;
use core_competency\user_competency;
use core_competency\user_competency_plan;
use core_competency\plan_competency;
use core_competency\template;
use core_competency\template_competency;
use core_competency\course_competency_settings;


class tool_lp_external_testcase extends externallib_advanced_testcase {

    
    protected $creator = null;

    
    protected $catcreator = null;

    
    protected $category = null;

    
    protected $othercategory = null;

    
    protected $user = null;

    
    protected $catuser = null;

    
    protected $creatorrole = null;

    
    protected $userrole = null;

    
    protected function setUp() {
        global $DB;

        $this->resetAfterTest(true);

                $creator = $this->getDataGenerator()->create_user();
        $user = $this->getDataGenerator()->create_user();
        $catuser = $this->getDataGenerator()->create_user();
        $catcreator = $this->getDataGenerator()->create_user();
        $category = $this->getDataGenerator()->create_category();
        $othercategory = $this->getDataGenerator()->create_category();
        $syscontext = context_system::instance();
        $catcontext = context_coursecat::instance($category->id);

                $userroles = get_archetype_roles('user');
        $this->assertCount(1, $userroles);
        $authrole = array_pop($userroles);

                unassign_capability('moodle/competency:competencygrade', $authrole->id);
        unassign_capability('moodle/competency:competencymanage', $authrole->id);
        unassign_capability('moodle/competency:competencyview', $authrole->id);
        unassign_capability('moodle/competency:planmanage', $authrole->id);
        unassign_capability('moodle/competency:planmanagedraft', $authrole->id);
        unassign_capability('moodle/competency:planmanageown', $authrole->id);
        unassign_capability('moodle/competency:planview', $authrole->id);
        unassign_capability('moodle/competency:planviewdraft', $authrole->id);
        unassign_capability('moodle/competency:planviewown', $authrole->id);
        unassign_capability('moodle/competency:planviewowndraft', $authrole->id);
        unassign_capability('moodle/competency:templatemanage', $authrole->id);
        unassign_capability('moodle/competency:templateview', $authrole->id);
        unassign_capability('moodle/cohort:manage', $authrole->id);
        unassign_capability('moodle/competency:coursecompetencyconfigure', $authrole->id);

                $this->creatorrole = create_role('Creator role', 'lpcreatorrole', 'learning plan creator role description');
        $this->userrole = create_role('User role', 'lpuserrole', 'learning plan user role description');

        assign_capability('moodle/competency:competencymanage', CAP_ALLOW, $this->creatorrole, $syscontext->id);
        assign_capability('moodle/competency:competencycompetencyconfigure', CAP_ALLOW, $this->creatorrole, $syscontext->id);
        assign_capability('moodle/competency:planmanage', CAP_ALLOW, $this->creatorrole, $syscontext->id);
        assign_capability('moodle/competency:planmanagedraft', CAP_ALLOW, $this->creatorrole, $syscontext->id);
        assign_capability('moodle/competency:planmanageown', CAP_ALLOW, $this->creatorrole, $syscontext->id);
        assign_capability('moodle/competency:planview', CAP_ALLOW, $this->creatorrole, $syscontext->id);
        assign_capability('moodle/competency:planviewdraft', CAP_ALLOW, $this->creatorrole, $syscontext->id);
        assign_capability('moodle/competency:templatemanage', CAP_ALLOW, $this->creatorrole, $syscontext->id);
        assign_capability('moodle/competency:competencygrade', CAP_ALLOW, $this->creatorrole, $syscontext->id);
        assign_capability('moodle/cohort:manage', CAP_ALLOW, $this->creatorrole, $syscontext->id);

        assign_capability('moodle/competency:competencyview', CAP_ALLOW, $this->userrole, $syscontext->id);
        assign_capability('moodle/competency:templateview', CAP_ALLOW, $this->userrole, $syscontext->id);
        assign_capability('moodle/competency:planviewown', CAP_ALLOW, $this->userrole, $syscontext->id);
        assign_capability('moodle/competency:planviewowndraft', CAP_ALLOW, $this->userrole, $syscontext->id);

        role_assign($this->creatorrole, $creator->id, $syscontext->id);
        role_assign($this->creatorrole, $catcreator->id, $catcontext->id);
        role_assign($this->userrole, $user->id, $syscontext->id);
        role_assign($this->userrole, $catuser->id, $catcontext->id);

        $this->creator = $creator;
        $this->catcreator = $catcreator;
        $this->user = $user;
        $this->catuser = $catuser;
        $this->category = $category;
        $this->othercategory = $othercategory;

        accesslib_clear_all_caches_for_unit_testing();
    }

    public function test_search_users_by_capability() {
        global $CFG;
        $this->resetAfterTest(true);

        $dg = $this->getDataGenerator();
        $ux = $dg->create_user();
        $u1 = $dg->create_user(array('idnumber' => 'Cats', 'firstname' => 'Bob', 'lastname' => 'Dyyylan',
            'email' => 'bobbyyy@dyyylan.com', 'phone1' => '123456', 'phone2' => '78910', 'department' => 'Marketing',
            'institution' => 'HQ'));

                $this->setUser($ux);
        $result = external::search_users('yyylan', 'moodle/competency:planmanage');
        $result = external_api::clean_returnvalue(external::search_users_returns(), $result);
        $this->assertCount(0, $result['users']);
        $this->assertEquals(0, $result['count']);

                $usercontext = context_user::instance($u1->id);
        $systemcontext = context_system::instance();
        $customrole = $this->assignUserCapability('moodle/competency:planview', $usercontext->id);

        $result = external::search_users('yyylan', 'moodle/competency:planmanage');
        $result = external_api::clean_returnvalue(external::search_users_returns(), $result);
        $this->assertCount(0, $result['users']);
        $this->assertEquals(0, $result['count']);

                $usercontext = context_user::instance($u1->id);
        $this->assignUserCapability('moodle/competency:planmanage', $usercontext->id, $customrole);

        $result = external::search_users('yyylan', 'moodle/competency:planmanage');
        $result = external_api::clean_returnvalue(external::search_users_returns(), $result);
        $this->assertCount(1, $result['users']);
        $this->assertEquals(1, $result['count']);

                role_assign($this->creatorrole, $ux->id, $usercontext->id);
        $result = external::search_users('yyylan', 'moodle/competency:planmanage');
        $result = external_api::clean_returnvalue(external::search_users_returns(), $result);
        $this->assertCount(1, $result['users']);
        $this->assertEquals(1, $result['count']);

                $ux2 = $dg->create_user();
        role_assign($this->creatorrole, $ux2->id, $systemcontext->id);
        $this->setUser($ux2);
        $result = external::search_users('yyylan', 'moodle/competency:planmanage');
        $result = external_api::clean_returnvalue(external::search_users_returns(), $result);
        $this->assertCount(1, $result['users']);
        $this->assertEquals(1, $result['count']);

                $ux3 = $dg->create_user();
        role_assign($this->creatorrole, $ux3->id, $usercontext->id);
        $this->setUser($ux3);
        $result = external::search_users('yyylan', 'moodle/competency:planmanage');
        $result = external_api::clean_returnvalue(external::search_users_returns(), $result);
        $this->assertCount(1, $result['users']);
        $this->assertEquals(1, $result['count']);

                $this->setUser($ux);

                assign_capability('moodle/competency:planmanage', CAP_PREVENT, $customrole, $usercontext->id);
        $result = external::search_users('yyylan', 'moodle/competency:planmanage');
        $result = external_api::clean_returnvalue(external::search_users_returns(), $result);
        $this->assertCount(1, $result['users']);
        $this->assertEquals(1, $result['count']);

                assign_capability('moodle/competency:planmanage', CAP_PROHIBIT, $customrole, $usercontext->id);
        $result = external::search_users('yyylan', 'moodle/competency:planmanage');
        $result = external_api::clean_returnvalue(external::search_users_returns(), $result);
        $this->assertCount(1, $result['users']);
        $this->assertEquals(1, $result['count']);

    }

    
    public function test_search_users_by_capability_the_comeback() {
        $this->resetAfterTest();
        $dg = $this->getDataGenerator();

        $master = $dg->create_user();
        $manager = $dg->create_user();
        $slave1 = $dg->create_user(array('lastname' => 'MOODLER'));
        $slave2 = $dg->create_user(array('lastname' => 'MOODLER'));
        $slave3 = $dg->create_user(array('lastname' => 'MOODLER'));

        $syscontext = context_system::instance();
        $slave1context = context_user::instance($slave1->id);
        $slave2context = context_user::instance($slave2->id);
        $slave3context = context_user::instance($slave3->id);

                $roleid = $dg->create_role();
        assign_capability('moodle/site:config', CAP_ALLOW, $roleid, $syscontext->id, true);

                assign_capability('moodle/site:config', CAP_PROHIBIT, $roleid, $slave2context->id, true);

                                role_assign($roleid, $master->id, $syscontext);
        role_assign($roleid, $manager->id, $slave1context);

                accesslib_clear_all_caches_for_unit_testing();

                        $this->setUser($master);
        $this->assertTrue(has_capability('moodle/site:config', $syscontext));
        $this->assertTrue(has_capability('moodle/site:config', $slave1context));
        $this->assertFalse(has_capability('moodle/site:config', $slave2context));
        $this->assertTrue(has_capability('moodle/site:config', $slave3context));

                $this->setUser($manager);
        $this->assertFalse(has_capability('moodle/site:config', $syscontext));
        $this->assertTrue(has_capability('moodle/site:config', $slave1context));
        $this->assertFalse(has_capability('moodle/site:config', $slave2context));
        $this->assertFalse(has_capability('moodle/site:config', $slave3context));

                $this->setUser($master);
        $result = external::search_users('MOODLER', 'moodle/site:config');
        $this->assertCount(2, $result['users']);
        $this->assertEquals(2, $result['count']);
        $this->assertArrayHasKey($slave1->id, $result['users']);
        $this->assertArrayHasKey($slave3->id, $result['users']);

        $this->setUser($manager);
        $result = external::search_users('MOODLER', 'moodle/site:config');
        $this->assertCount(1, $result['users']);
        $this->assertEquals(1, $result['count']);
        $this->assertArrayHasKey($slave1->id, $result['users']);
    }

    public function test_search_users() {
        global $CFG;
        $this->resetAfterTest(true);

        $dg = $this->getDataGenerator();
        $ux = $dg->create_user();
        $u1 = $dg->create_user(array('idnumber' => 'Cats', 'firstname' => 'Bob', 'lastname' => 'Dyyylan',
            'email' => 'bobbyyy@dyyylan.com', 'phone1' => '123456', 'phone2' => '78910', 'department' => 'Marketing',
            'institution' => 'HQ'));
        $u2 = $dg->create_user(array('idnumber' => 'Dogs', 'firstname' => 'Alice', 'lastname' => 'Dyyylan',
            'email' => 'alyyyson@dyyylan.com', 'phone1' => '33333', 'phone2' => '77777', 'department' => 'Development',
            'institution' => 'O2'));
        $u3 = $dg->create_user(array('idnumber' => 'Fish', 'firstname' => 'Thomas', 'lastname' => 'Xow',
            'email' => 'fishyyy@moodle.com', 'phone1' => '77777', 'phone2' => '33333', 'department' => 'Research',
            'institution' => 'Bob'));

                $this->setAdminUser();
        $usercontext = context_user::instance($u1->id);
        $dummyrole = $this->assignUserCapability('moodle/competency:planmanage', $usercontext->id);
        $usercontext = context_user::instance($u2->id);
        $this->assignUserCapability('moodle/competency:planmanage', $usercontext->id, $dummyrole);
        $usercontext = context_user::instance($u3->id);
        $this->assignUserCapability('moodle/competency:planmanage', $usercontext->id, $dummyrole);

        $this->setUser($ux);
        $usercontext = context_user::instance($u1->id);
        $this->assignUserCapability('moodle/competency:planmanage', $usercontext->id, $dummyrole);
        $usercontext = context_user::instance($u2->id);
        $this->assignUserCapability('moodle/competency:planmanage', $usercontext->id, $dummyrole);
        $usercontext = context_user::instance($u3->id);
        $this->assignUserCapability('moodle/competency:planmanage', $usercontext->id, $dummyrole);

        $this->setAdminUser();

                $CFG->showuseridentity = '';
        $result = external::search_users('cats', 'moodle/competency:planmanage');
        $result = external_api::clean_returnvalue(external::search_users_returns(), $result);
        $this->assertCount(0, $result['users']);
        $this->assertEquals(0, $result['count']);

                $CFG->showuseridentity = '';
        $result = external::search_users('dyyylan', 'moodle/competency:planmanage');
        $result = external_api::clean_returnvalue(external::search_users_returns(), $result);
        $this->assertCount(2, $result['users']);
        $this->assertEquals(2, $result['count']);
        $this->assertEquals($u2->id, $result['users'][0]['id']);
        $this->assertEquals($u1->id, $result['users'][1]['id']);

                $CFG->showuseridentity = 'institution';
        $result = external::search_users('bob', 'moodle/competency:planmanage');
        $result = external_api::clean_returnvalue(external::search_users_returns(), $result);
        $this->assertCount(2, $result['users']);
        $this->assertEquals(2, $result['count']);
        $this->assertEquals($u1->id, $result['users'][0]['id']);
        $this->assertEquals($u3->id, $result['users'][1]['id']);

                $CFG->showuseridentity = 'idnumber';
        $result = external::search_users('cats', 'moodle/competency:planmanage');
        $result = external_api::clean_returnvalue(external::search_users_returns(), $result);
        $this->assertCount(1, $result['users']);
        $this->assertEquals(1, $result['count']);
        $this->assertEquals($u1->id, $result['users'][0]['id']);
        $this->assertEquals($u1->idnumber, $result['users'][0]['idnumber']);
        $this->assertEmpty($result['users'][0]['email']);
        $this->assertEmpty($result['users'][0]['phone1']);
        $this->assertEmpty($result['users'][0]['phone2']);
        $this->assertEmpty($result['users'][0]['department']);
        $this->assertEmpty($result['users'][0]['institution']);

                $CFG->showuseridentity = 'email';
        $result = external::search_users('yyy', 'moodle/competency:planmanage');
        $result = external_api::clean_returnvalue(external::search_users_returns(), $result);
        $this->assertCount(3, $result['users']);
        $this->assertEquals(3, $result['count']);
        $this->assertEquals($u2->id, $result['users'][0]['id']);
        $this->assertEquals($u2->email, $result['users'][0]['email']);
        $this->assertEquals($u1->id, $result['users'][1]['id']);
        $this->assertEquals($u1->email, $result['users'][1]['email']);
        $this->assertEquals($u3->id, $result['users'][2]['id']);
        $this->assertEquals($u3->email, $result['users'][2]['email']);

                $CFG->showuseridentity = 'idnumber,email,phone1,phone2,department,institution';
        $result = external::search_users('yyy', 'moodle/competency:planmanage');
        $result = external_api::clean_returnvalue(external::search_users_returns(), $result);
        $this->assertCount(3, $result['users']);
        $this->assertEquals(3, $result['count']);
        $this->assertArrayHasKey('idnumber', $result['users'][0]);
        $this->assertArrayHasKey('email', $result['users'][0]);
        $this->assertArrayHasKey('phone1', $result['users'][0]);
        $this->assertArrayHasKey('phone2', $result['users'][0]);
        $this->assertArrayHasKey('department', $result['users'][0]);
        $this->assertArrayHasKey('institution', $result['users'][0]);

                $this->setUser($ux);
        $CFG->showuseridentity = 'idnumber,email,phone1,phone2,department,institution';

                $result = external::search_users('fish');
        $result = external_api::clean_returnvalue(external::search_users_returns(), $result);
        $this->assertCount(0, $result['users']);
        $this->assertEquals(0, $result['count']);

        $result = external::search_users('bob', 'moodle/competency:planmanage');
        $result = external_api::clean_returnvalue(external::search_users_returns(), $result);
        $this->assertCount(1, $result['users']);
        $this->assertEquals(1, $result['count']);
        $this->assertEquals($u1->id, $result['users'][0]['id']);
        $this->assertEmpty($result['users'][0]['idnumber']);
        $this->assertEmpty($result['users'][0]['email']);
        $this->assertEmpty($result['users'][0]['phone1']);
        $this->assertEmpty($result['users'][0]['phone2']);
        $this->assertEmpty($result['users'][0]['department']);
        $this->assertEmpty($result['users'][0]['institution']);
    }

    public function test_data_for_user_competency_summary_in_plan() {
        global $CFG;

        $this->setUser($this->creator);

        $dg = $this->getDataGenerator();
        $lpg = $dg->get_plugin_generator('core_competency');

        $f1 = $lpg->create_framework();

        $c1 = $lpg->create_competency(array('competencyframeworkid' => $f1->get_id()));

        $tpl = $lpg->create_template();
        $lpg->create_template_competency(array('templateid' => $tpl->get_id(), 'competencyid' => $c1->get_id()));

        $plan = $lpg->create_plan(array('userid' => $this->user->id, 'templateid' => $tpl->get_id(), 'name' => 'Evil'));

        $uc = $lpg->create_user_competency(array('userid' => $this->user->id, 'competencyid' => $c1->get_id()));

        $evidence = \core_competency\external::grade_competency_in_plan($plan->get_id(), $c1->get_id(), 1, true);
        $evidence = \core_competency\external::grade_competency_in_plan($plan->get_id(), $c1->get_id(), 2, true);

        $summary = external::data_for_user_competency_summary_in_plan($c1->get_id(), $plan->get_id());
        $this->assertTrue($summary->usercompetencysummary->cangrade);
        $this->assertEquals('Evil', $summary->plan->name);
        $this->assertEquals('B', $summary->usercompetencysummary->usercompetency->gradename);
        $this->assertEquals('B', $summary->usercompetencysummary->evidence[0]->gradename);
        $this->assertEquals('A', $summary->usercompetencysummary->evidence[1]->gradename);
    }

    
    public function test_search_cohorts() {
        $this->resetAfterTest(true);

        $syscontext = array('contextid' => context_system::instance()->id);
        $catcontext = array('contextid' => context_coursecat::instance($this->category->id)->id);
        $othercatcontext = array('contextid' => context_coursecat::instance($this->othercategory->id)->id);

        $cohort1 = $this->getDataGenerator()->create_cohort(array_merge($syscontext, array('name' => 'Cohortsearch 1')));
        $cohort2 = $this->getDataGenerator()->create_cohort(array_merge($catcontext, array('name' => 'Cohortsearch 2')));
        $cohort3 = $this->getDataGenerator()->create_cohort(array_merge($othercatcontext, array('name' => 'Cohortsearch 3')));

        
                $this->setUser($this->user);
        try {
            $result = external::search_cohorts("Cohortsearch", $syscontext, 'parents');
            $this->fail('Invalid permissions in system');
        } catch (required_capability_exception $e) {
                    }

                $this->setUser($this->catuser);
        try {
            $result = external::search_cohorts("Cohortsearch", $catcontext, 'parents');
            $this->fail('Invalid permissions in category');
        } catch (required_capability_exception $e) {
                    }

                $this->setUser($this->creator);
        $result = external::search_cohorts("Cohortsearch", $syscontext, 'parents');
        $this->assertEquals(1, count($result['cohorts']));
        $this->assertEquals('Cohortsearch 1', $result['cohorts'][$cohort1->id]->name);

                $this->setUser($this->catcreator);
        $result = external::search_cohorts("Cohortsearch", $catcontext, 'parents');
        $this->assertEquals(2, count($result['cohorts']));
        $cohorts = array();
        foreach ($result['cohorts'] as $cohort) {
            $cohorts[] = $cohort->name;
        }
        $this->assertTrue(in_array('Cohortsearch 1', $cohorts));
        $this->assertTrue(in_array('Cohortsearch 2', $cohorts));

                $this->setUser($this->creator);
        $result = external::search_cohorts("Cohortsearch", $othercatcontext, 'self');
        $this->assertEquals(1, count($result['cohorts']));
        $this->assertEquals('Cohortsearch 3', $result['cohorts'][$cohort3->id]->name);

                $this->setUser($this->creator);
        $result = external::search_cohorts("Cohortsearch", $syscontext, 'all');
        $this->assertEquals(3, count($result['cohorts']));

                $this->setUser($this->creator);
        try {
            $result = external::search_cohorts("Cohortsearch", $syscontext, 'invalid');
            $this->fail('Invalid parameter includes');
        } catch (coding_exception $e) {
                    }
    }

}
