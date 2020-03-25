<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/lib/external/externallib.php');
require_once($CFG->dirroot . '/webservice/tests/helpers.php');


class core_external_testcase extends externallib_advanced_testcase {

    
    public function test_get_string() {
        $this->resetAfterTest(true);

        $service = new stdClass();
        $service->name = 'Dummy Service';
        $service->id = 12;

                $returnedstring = core_external::get_string('addservice', 'webservice', null,
                array(array('name' => 'name', 'value' => $service->name),
                      array('name' => 'id', 'value' => $service->id)));

                $returnedstring = external_api::clean_returnvalue(core_external::get_string_returns(), $returnedstring);

        $corestring = get_string('addservice', 'webservice', $service);
        $this->assertSame($corestring, $returnedstring);

                $acapname = 'A capability name';
        $returnedstring = core_external::get_string('missingrequiredcapability', 'webservice', null,
                array(array('value' => $acapname)));

                $returnedstring = external_api::clean_returnvalue(core_external::get_string_returns(), $returnedstring);

        $corestring = get_string('missingrequiredcapability', 'webservice', $acapname);
        $this->assertSame($corestring, $returnedstring);

                $returnedstring = core_external::get_string('missingpassword', 'webservice');

                $returnedstring = external_api::clean_returnvalue(core_external::get_string_returns(), $returnedstring);

        $corestring = get_string('missingpassword', 'webservice');
        $this->assertSame($corestring, $returnedstring);

                $this->setExpectedException('moodle_exception');
        $returnedstring = core_external::get_string('addservice', 'webservice', null,
                array(array('value' => $service->name),
                      array('name' => 'id', 'value' => $service->id)));
    }

    
    public function test_get_string_containing_html() {
        $result = core_external::get_string('registrationinfo');
        $actual = external_api::clean_returnvalue(core_external::get_string_returns(), $result);
        $expected = get_string('registrationinfo', 'moodle');
        $this->assertSame($expected, $actual);
    }

    
    public function test_get_string_with_args_containing_html() {
        $result = core_external::get_string('added', 'moodle', null, [['value' => '<strong>Test</strong>']]);
        $actual = external_api::clean_returnvalue(core_external::get_string_returns(), $result);
        $expected = get_string('added', 'moodle', '<strong>Test</strong>');
        $this->assertSame($expected, $actual);
    }

    
    public function test_get_strings() {
        $this->resetAfterTest(true);

        $stringmanager = get_string_manager();

        $service = new stdClass();
        $service->name = 'Dummy Service';
        $service->id = 12;

        $returnedstrings = core_external::get_strings(
                array(
                    array(
                        'stringid' => 'addservice', 'component' => 'webservice',
                        'stringparams' => array(array('name' => 'name', 'value' => $service->name),
                              array('name' => 'id', 'value' => $service->id)
                        ),
                        'lang' => 'en'
                    ),
                    array('stringid' =>  'addaservice', 'component' => 'webservice', 'lang' => 'en')
                ));

                $returnedstrings = external_api::clean_returnvalue(core_external::get_strings_returns(), $returnedstrings);

        foreach($returnedstrings as $returnedstring) {
            $corestring = $stringmanager->get_string($returnedstring['stringid'],
                                                     $returnedstring['component'],
                                                     $service,
                                                     'en');
            $this->assertSame($corestring, $returnedstring['string']);
        }
    }

    
    public function test_get_strings_containing_html() {
        $result = core_external::get_strings([['stringid' => 'registrationinfo'], ['stringid' => 'loginaspasswordexplain']]);
        $actual = external_api::clean_returnvalue(core_external::get_strings_returns(), $result);
        $this->assertSame(get_string('registrationinfo', 'moodle'), $actual[0]['string']);
        $this->assertSame(get_string('loginaspasswordexplain', 'moodle'), $actual[1]['string']);
    }

    
    public function test_get_strings_with_args_containing_html() {
        $result = core_external::get_strings([
            ['stringid' => 'added', 'stringparams' => [['value' => '<strong>Test</strong>']]],
            ['stringid' => 'loggedinas', 'stringparams' => [['value' => '<strong>Test</strong>']]]]
        );
        $actual = external_api::clean_returnvalue(core_external::get_strings_returns(), $result);
        $this->assertSame(get_string('added', 'moodle', '<strong>Test</strong>'), $actual[0]['string']);
        $this->assertSame(get_string('loggedinas', 'moodle', '<strong>Test</strong>'), $actual[1]['string']);
    }

    
    public function test_get_component_strings() {
        global $USER;
        $this->resetAfterTest(true);

        $stringmanager = get_string_manager();

        $wsstrings = $stringmanager->load_component_strings('webservice', current_language());

        $componentstrings = core_external::get_component_strings('webservice');

                $componentstrings = external_api::clean_returnvalue(core_external::get_component_strings_returns(), $componentstrings);

        $this->assertEquals(count($componentstrings), count($wsstrings));
        foreach($componentstrings as $string) {
            $this->assertSame($string['string'], $wsstrings[$string['stringid']]);
        }
    }

    
    public function test_update_inplace_editable() {
        $this->resetAfterTest(true);

                try {
            core_external::update_inplace_editable('tool_log', 'itemtype', 1, 'newvalue');
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertEquals('Error calling update processor', $e->getMessage());
        }

                        $this->setAdminUser();
        $tag = $this->getDataGenerator()->create_tag();
        $res = core_external::update_inplace_editable('core_tag', 'tagname', $tag->id, 'new tag name');
        $res = external_api::clean_returnvalue(core_external::update_inplace_editable_returns(), $res);
        $this->assertEquals('new tag name', $res['value']);
    }
}
