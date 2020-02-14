<?php


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/lib.php');


class webservice_test extends advanced_testcase {

    
    public function setUp() {
                parent::setUp();

                set_config('enablewebservices', '1');
    }

    
    public function test_init_service_class() {
        global $DB, $USER;

        $this->resetAfterTest(true);

                $this->setAdminUser();

                $webservice = new stdClass();
        $webservice->name = 'Test web service';
        $webservice->enabled = true;
        $webservice->restrictedusers = false;
        $webservice->component = 'moodle';
        $webservice->timecreated = time();
        $webservice->downloadfiles = true;
        $webservice->uploadfiles = true;
        $externalserviceid = $DB->insert_record('external_services', $webservice);

                $externaltoken = new stdClass();
        $externaltoken->token = 'testtoken';
        $externaltoken->tokentype = 0;
        $externaltoken->userid = $USER->id;
        $externaltoken->externalserviceid = $externalserviceid;
        $externaltoken->contextid = 1;
        $externaltoken->creatorid = $USER->id;
        $externaltoken->timecreated = time();
        $DB->insert_record('external_tokens', $externaltoken);

                $wsmethod = new stdClass();
        $wsmethod->externalserviceid = $externalserviceid;
        $wsmethod->functionname = 'core_course_get_contents';
        $DB->insert_record('external_services_functions', $wsmethod);

                $dummy = new webservice_dummy(WEBSERVICE_AUTHMETHOD_PERMANENT_TOKEN);
                $dummy->set_token($externaltoken->token);
                $dummy->run();
                $servicemethods = $dummy->get_service_methods();
        $servicestructs = $dummy->get_service_structs();
        $this->assertNotEmpty($servicemethods);
                $this->assertEquals(1, count($servicemethods));
                $this->assertEmpty($servicestructs);

                        $wsmethod->functionname = 'core_comment_get_comments';
        $DB->insert_record('external_services_functions', $wsmethod);
                $wsmethod->functionname = 'core_grades_update_grades';
        $DB->insert_record('external_services_functions', $wsmethod);

                $dummy->run();
                $servicemethods = $dummy->get_service_methods();
        $servicestructs = $dummy->get_service_structs();
        $this->assertEquals(3, count($servicemethods));
        $this->assertEquals(2, count($servicestructs));

                foreach ($servicemethods as $method) {
                        $function = external_api::external_function_info($method->name);

                        foreach ($function->parameters_desc->keys as $name => $keydesc) {
                $this->check_params($method->inputparams[$name]['type'], $keydesc, $servicestructs);
            }

                        $this->check_params($method->outputparams['return']['type'], $function->returns_desc, $servicestructs);

                        $this->assertEquals($function->description, $method->description);
        }
    }

    
    private function check_params($type, $methoddesc, $servicestructs) {
        if ($methoddesc instanceof external_value) {
                        if (in_array($methoddesc->type, [PARAM_INT, PARAM_FLOAT, PARAM_BOOL])) {
                $this->assertEquals($methoddesc->type, $type);
            } else {
                $this->assertEquals('string', $type);
            }
        } else if ($methoddesc instanceof external_single_structure) {
                        $structinfo = $this->get_struct_info($servicestructs, $type);
            $this->assertNotNull($structinfo);
                        foreach ($structinfo->properties as $propname => $proptype) {
                $this->assertTrue($this->in_keydesc($methoddesc, $propname));
            }
        } else if ($methoddesc instanceof external_multiple_structure) {
                        $this->assertEquals('array', $type);
        }
    }

    
    private function get_struct_info($structarray, $structclass) {
        foreach ($structarray as $struct) {
            if ($struct->classname === $structclass) {
                return $struct;
            }
        }
        return null;
    }

    
    private function in_keydesc(external_single_structure $keydesc, $propertyname) {
        foreach ($keydesc->keys as $key => $desc) {
            if ($key === $propertyname) {
                return true;
            }
        }
        return false;
    }
}


class webservice_dummy extends webservice_base_server {

    
    public function __construct($authmethod) {
        parent::__construct($authmethod);

                $this->wsname = 'rest';
    }

    
    public function set_token($token) {
        $this->token = $token;
    }

    
    protected function parse_request() {
            }

    
    protected function send_response() {
            }

    
    protected function send_error($ex = null) {
            }

    
    public function run() {
        $this->authenticate_user();
        $this->init_service_class();
    }

    
    public function get_service_methods() {
        return $this->servicemethods;
    }

    
    public function get_service_structs() {
        return $this->servicestructs;
    }
}
