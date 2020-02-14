<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/xmlrpc/lib.php');


class webservice_xmlrpc_test extends advanced_testcase {

    
    public function setUp() {
        $this->resetAfterTest();

                if (!function_exists('xmlrpc_decode')) {
            $this->markTestSkipped('XMLRPC is not installed.');
        }
    }

    
    public function test_client_with_array_response() {
        global $CFG;

        $client = new webservice_xmlrpc_client_mock('/webservice/xmlrpc/server.php', 'anytoken');
        $mockresponse = file_get_contents($CFG->dirroot . '/webservice/xmlrpc/tests/fixtures/array_response.xml');
        $client->set_mock_response($mockresponse);
        $result = $client->call('testfunction');
        $this->assertEquals(xmlrpc_decode($mockresponse), $result);
    }

    
    public function test_client_with_value_response() {
        global $CFG;

        $client = new webservice_xmlrpc_client_mock('/webservice/xmlrpc/server.php', 'anytoken');
        $mockresponse = file_get_contents($CFG->dirroot . '/webservice/xmlrpc/tests/fixtures/value_response.xml');
        $client->set_mock_response($mockresponse);
        $result = $client->call('testfunction');
        $this->assertEquals(xmlrpc_decode($mockresponse), $result);
    }

    
    public function test_client_with_fault_response() {
        global $CFG;

        $client = new webservice_xmlrpc_client_mock('/webservice/xmlrpc/server.php', 'anytoken');
        $mockresponse = file_get_contents($CFG->dirroot . '/webservice/xmlrpc/tests/fixtures/fault_response.xml');
        $client->set_mock_response($mockresponse);
        $this->setExpectedException('moodle_exception');
        $client->call('testfunction');
    }
}


class webservice_xmlrpc_client_mock extends webservice_xmlrpc_client {

    
    private $mockresponse;

    
    public function set_mock_response($mockresponse) {
        $this->mockresponse = $mockresponse;
    }

    
    public function call($functionname, $params = array()) {
                $response = $this->mockresponse;

                        $result = xmlrpc_decode($response);
        if (is_array($result) && xmlrpc_is_fault($result)) {
            throw new moodle_exception($result['faultString']);
        }

        return $result;
    }
}
