<?php



defined('MOODLE_INTERNAL') || die();

use tool_cohortroles\api;


class tool_cohortroles_api_testcase extends advanced_testcase {
    
    protected $cohort = null;

    
    protected $userassignto = null;

    
    protected $userassignover = null;

    
    protected $role = null;

    
    protected function setUp() {
        global $DB;

        $this->resetAfterTest(true);

                $this->cohort = $this->getDataGenerator()->create_cohort();
        $this->userassignto = $this->getDataGenerator()->create_user();
        $this->userassignover = $this->getDataGenerator()->create_user();
        $this->roleid = create_role('Sausage Roll', 'sausageroll', 'mmmm');
        cohort_add_member($this->cohort->id, $this->userassignover->id);
    }


    public function test_create_cohort_role_assignment_without_permission() {
        $this->setExpectedException('required_capability_exception');
        $this->setUser($this->userassignto);
        $params = (object) array(
            'userid' => $this->userassignto->id,
            'roleid' => $this->roleid,
            'cohortid' => $this->cohort->id
        );
        api::create_cohort_role_assignment($params);
    }

    public function test_create_cohort_role_assignment_with_invalid_data() {
        $this->setExpectedException('core_competency\invalid_persistent_exception');
        $this->setAdminUser();
        $params = (object) array(
            'userid' => $this->userassignto->id,
            'roleid' => -8,
            'cohortid' => $this->cohort->id
        );
        api::create_cohort_role_assignment($params);
    }

    public function test_create_cohort_role_assignment() {
        $this->setAdminUser();
        $params = (object) array(
            'userid' => $this->userassignto->id,
            'roleid' => $this->roleid,
            'cohortid' => $this->cohort->id
        );
        $result = api::create_cohort_role_assignment($params);
        $this->assertNotEmpty($result->get_id());
        $this->assertEquals($result->get_userid(), $this->userassignto->id);
        $this->assertEquals($result->get_roleid(), $this->roleid);
        $this->assertEquals($result->get_cohortid(), $this->cohort->id);
    }

    public function test_delete_cohort_role_assignment_without_permission() {
        $this->setAdminUser();
        $params = (object) array(
            'userid' => $this->userassignto->id,
            'roleid' => $this->roleid,
            'cohortid' => $this->cohort->id
        );
        $result = api::create_cohort_role_assignment($params);
        $this->setExpectedException('required_capability_exception');
        $this->setUser($this->userassignto);
        api::delete_cohort_role_assignment($result->get_id());
    }

    public function test_delete_cohort_role_assignment_with_invalid_data() {
        $this->setAdminUser();
        $params = (object) array(
            'userid' => $this->userassignto->id,
            'roleid' => $this->roleid,
            'cohortid' => $this->cohort->id
        );
        $result = api::create_cohort_role_assignment($params);
        $this->setExpectedException('dml_missing_record_exception');
        api::delete_cohort_role_assignment($result->get_id() + 1);
    }

    public function test_delete_cohort_role_assignment() {
        $this->setAdminUser();
        $params = (object) array(
            'userid' => $this->userassignto->id,
            'roleid' => $this->roleid,
            'cohortid' => $this->cohort->id
        );
        $result = api::create_cohort_role_assignment($params);
        $worked = api::delete_cohort_role_assignment($result->get_id());
        $this->assertTrue($worked);
    }

    public function test_list_cohort_role_assignments() {
        $this->setAdminUser();
        $params = (object) array(
            'userid' => $this->userassignto->id,
            'roleid' => $this->roleid,
            'cohortid' => $this->cohort->id
        );
        $result = api::create_cohort_role_assignment($params);

        $list = api::list_cohort_role_assignments();
        $list[0]->is_valid();
        $this->assertEquals($list[0], $result);
    }

    public function test_count_cohort_role_assignments() {
        $this->setAdminUser();
        $params = (object) array(
            'userid' => $this->userassignto->id,
            'roleid' => $this->roleid,
            'cohortid' => $this->cohort->id
        );
        $result = api::create_cohort_role_assignment($params);

        $count = api::count_cohort_role_assignments();
        $this->assertEquals($count, 1);
    }

    public function test_sync_all_cohort_roles() {
        $this->setAdminUser();
        $params = (object) array(
            'userid' => $this->userassignto->id,
            'roleid' => $this->roleid,
            'cohortid' => $this->cohort->id
        );
        $result = api::create_cohort_role_assignment($params);

                $sync = api::sync_all_cohort_roles();

        $rolesadded = array(array(
            'useridassignedto' => $this->userassignto->id,
            'useridassignedover' => $this->userassignover->id,
            'roleid' => $this->roleid
        ));
        $rolesremoved = array();
        $expected = array('rolesadded' => $rolesadded,
                          'rolesremoved' => $rolesremoved);
        $this->assertEquals($sync, $expected);

                cohort_remove_member($this->cohort->id, $this->userassignover->id);
        $sync = api::sync_all_cohort_roles();

        $rolesadded = array();
        $rolesremoved = array(array(
            'useridassignedto' => $this->userassignto->id,
            'useridassignedover' => $this->userassignover->id,
            'roleid' => $this->roleid
        ));
        $expected = array('rolesadded' => $rolesadded,
                          'rolesremoved' => $rolesremoved);
        $this->assertEquals($sync, $expected);

                $usercontext = context_user::instance($this->userassignover->id);
        role_assign($this->roleid, $this->userassignto->id, $usercontext->id);
        $sync = api::sync_all_cohort_roles();

        $rolesadded = array();
        $rolesremoved = array();
        $expected = array('rolesadded' => $rolesadded,
                          'rolesremoved' => $rolesremoved);
        $this->assertEquals($sync, $expected);

                role_unassign($this->roleid, $this->userassignto->id, $usercontext->id);
                cohort_add_member($this->cohort->id, $this->userassignover->id);
        $sync = api::sync_all_cohort_roles();
        $rolesadded = array(array(
            'useridassignedto' => $this->userassignto->id,
            'useridassignedover' => $this->userassignover->id,
            'roleid' => $this->roleid
        ));
        $rolesremoved = array();
        $expected = array('rolesadded' => $rolesadded,
                          'rolesremoved' => $rolesremoved);
        $this->assertEquals($sync, $expected);

                cohort_delete_cohort($this->cohort);
        $sync = api::sync_all_cohort_roles();

        $rolesadded = array();
        $rolesremoved = array(array(
            'useridassignedto' => $this->userassignto->id,
            'useridassignedover' => $this->userassignover->id,
            'roleid' => $this->roleid
        ));
        $expected = array('rolesadded' => $rolesadded,
                          'rolesremoved' => $rolesremoved);
        $this->assertEquals($sync, $expected);
    }

}
