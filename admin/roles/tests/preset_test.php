<?php



defined('MOODLE_INTERNAL') || die();


class core_role_preset_testcase extends advanced_testcase {
    public function test_xml() {
        global $DB;

        $roles = $DB->get_records('role');

        foreach ($roles as $role) {
            $xml = core_role_preset::get_export_xml($role->id);
            $this->assertTrue(core_role_preset::is_valid_preset($xml));
            $info = core_role_preset::parse_preset($xml);
            $this->assertSame($role->shortname, $info['shortname']);
            $this->assertSame($role->name, $info['name']);
            $this->assertSame($role->description, $info['description']);
            $this->assertSame($role->archetype, $info['archetype']);

            $contextlevels = get_role_contextlevels($role->id);
            $this->assertEquals(array_values($contextlevels), array_values($info['contextlevels']));

            foreach (array('assign', 'override', 'switch') as $type) {
                $records = $DB->get_records('role_allow_'.$type, array('roleid'=>$role->id), "allow$type ASC");
                $allows = array();
                foreach ($records as $record) {
                    if ($record->{'allow'.$type} == $role->id) {
                        array_unshift($allows, -1);
                    }
                    $allows[] = $record->{'allow'.$type};
                }
                $this->assertEquals($allows, $info['allow'.$type], "$type $role->shortname does not match");
            }

            $capabilities = $DB->get_records_sql(
                "SELECT *
                   FROM {role_capabilities}
                  WHERE contextid = :syscontext AND roleid = :roleid
               ORDER BY capability ASC",
                array('syscontext'=>context_system::instance()->id, 'roleid'=>$role->id));

            foreach ($capabilities as $cap) {
                $this->assertEquals($cap->permission, $info['permissions'][$cap->capability]);
                unset($info['permissions'][$cap->capability]);
            }
                        foreach ($info['permissions'] as $capability => $permission) {
                if ($permission == CAP_INHERIT) {
                    continue;
                }
                $this->fail('only CAP_INHERIT expected');
            }
        }
    }
}
