<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/xmlrpc/locallib.php');


class xmlrpc_server_test extends advanced_testcase {

    
    public function setUp() {
        if (!function_exists('xmlrpc_decode')) {
            $this->markTestSkipped('XMLRPC is not installed.');
        }
    }

    
    public function test_parse_request($input, $expectfunction, $expectparams) {
        $server = $this->getMockBuilder('\webservice_xmlrpc_server')
                       ->setMethods(['fetch_input_content'])
                       ->disableOriginalConstructor()
                       ->getMock();

        $server->method('fetch_input_content')
               ->willReturn($input);

        $rc = new \ReflectionClass('\webservice_xmlrpc_server');
        $rcm = $rc->getMethod('parse_request');
        $rcm->setAccessible(true);
        $rcm->invoke($server);

        $rcp = $rc->getProperty('functionname');
        $rcp->setAccessible(true);
        $this->assertEquals($expectfunction, $rcp->getValue($server));

        $rcp = $rc->getProperty('parameters');
        $rcp->setAccessible(true);
        $this->assertEquals($expectparams, $rcp->getValue($server));
    }

    
    public function parse_request_provider() {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';

                $validmethod = '<methodName>core_get_component_strings</methodName>';
        $requiredparams = '<params><param><value><string>moodle</string></value></param></params>';
        $allparams = '<params><param><value><string>moodle</string></value></param><param><value><string>en</string></value>'
                . '</param></params>';
        $requiredparamsnonlatin = '<params><param><value><string>ᛞᛁᛞᛃᛟᚢᚲᚾᛟᚹᛈᚺᛈᛋᚢᛈᛈᛟᚱᛏᛋᚢᛏᚠ8ᚡᚨᚱᛁᚨᛒᛚᛖᚾᚨᛗᛖᛋ</string></value></param></params>';

        return [
                'Valid method, required params only' => [
                    "{$xml}<methodCall>{$validmethod}{$requiredparams}</methodCall>",
                    'core_get_component_strings',
                    ['component' => 'moodle'],
                ],
                'Valid method, all params' => [
                    "{$xml}<methodCall>{$validmethod}{$allparams}</methodCall>",
                    'core_get_component_strings',
                    ['component' => 'moodle', 'lang' => 'en'],
                ],
                'Valid method required params only (non Latin)' => [
                    "{$xml}<methodCall>{$validmethod}{$requiredparamsnonlatin}</methodCall>",
                    'core_get_component_strings',
                    ['component' => 'ᛞᛁᛞᛃᛟᚢᚲᚾᛟᚹᛈᚺᛈᛋᚢᛈᛈᛟᚱᛏᛋᚢᛏᚠ8ᚡᚨᚱᛁᚨᛒᛚᛖᚾᚨᛗᛖᛋ'],
                ],
            ];
    }
}
