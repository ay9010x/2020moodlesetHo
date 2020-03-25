<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/xmlrpc/locallib.php');


class webservice_xmlrpc_locallib_testcase extends advanced_testcase {

    
    public function setUp() {
        if (!function_exists('xmlrpc_decode')) {
            $this->markTestSkipped('XMLRPC is not installed.');
        }
    }

    
    public function test_prepare_response($returnsdesc, $returns, $expected) {
        $server = $this->getMockBuilder('webservice_xmlrpc_server')
                       ->disableOriginalConstructor()
                       ->setMethods(null)
                       ->getMock();

        $rc = new \ReflectionClass('webservice_xmlrpc_server');
        $rcm = $rc->getMethod('prepare_response');
        $rcm->setAccessible(true);

        $func = $rc->getProperty('function');
        $func->setAccessible(true);
        $func->setValue($server, (object) ['returns_desc' => new external_value(PARAM_RAW, $returnsdesc, VALUE_OPTIONAL)]);

        $ret = $rc->getProperty('returns');
        $ret->setAccessible(true);
        $ret->setValue($server, $returns);

        $rcm->invokeArgs($server, []);
        $response = $rc->getProperty('response');
        $response->setAccessible(true);

        $this->assertEquals($expected, $response->getValue($server));
    }

    
    public function test_generate_error($exception, $code, $expected) {
        $server = $this->getMockBuilder('webservice_xmlrpc_server')
                ->disableOriginalConstructor()
                ->setMethods(null)
                ->getMock();

        $rc = new \ReflectionClass('webservice_xmlrpc_server');
        $rcm = $rc->getMethod('generate_error');
        $rcm->setAccessible(true);

        if ($code === null) {
            $result = $rcm->invokeArgs($server, [$exception]);
        } else {
            $result = $rcm->invokeArgs($server, [$exception, $code]);
        }
        $this->assertEquals($expected, $result);
    }

    
    public function prepare_response_provider() {
        return [
            'Description written with Latin script' => [
                'Ennyn Durin, Aran Moria: pedo mellon a minno',
                'Mellon!',
                '<?xml version="1.0" encoding="UTF-8"?><methodResponse><params><param><value><string>Mellon!</string></value>'
                . '</param></params></methodResponse>'
            ],
            'Description with non-Latin glyphs' => [
                'What biscuits do you have?',
                                '😂🤵😂 𝒪𝓃𝓁𝓎 𝓉𝒽𝑒 𝒻𝒾𝓃𝑒𝓈𝓉 𝐼𝓉𝒶𝓁𝒾𝒶𝓃 𝒷𝒾𝓈𝒸𝓊𝒾𝓉𝓈 😂🤵😂',
                '<?xml version="1.0" encoding="UTF-8"?><methodResponse><params><param><value><string>'
                . '😂🤵😂 𝒪𝓃𝓁𝓎 𝓉𝒽𝑒 𝒻𝒾𝓃𝑒𝓈𝓉 𝐼𝓉𝒶𝓁𝒾𝒶𝓃 𝒷𝒾𝓈𝒸𝓊𝒾𝓉𝓈 😂🤵😂</string></value></param></params></methodResponse>'
            ]
        ];
    }

    
    public function generate_error_provider() {
        return [
            'Standard exception with default faultcode' => [
                new \Exception(),
                null,
                '<?xml version="1.0" encoding="UTF-8"?><methodResponse><fault><value><struct><member><name>faultCode</name><value><int>404</int></value></member><member><name>faultString</name><value><string/></value></member></struct></value></fault></methodResponse>'
            ],
            'Standard exception with default faultcode and exception content' => [
                new \Exception('PC LOAD LETTER'),
                null,
                '<?xml version="1.0" encoding="UTF-8"?><methodResponse><fault><value><struct><member><name>faultCode</name><value><int>404</int></value></member><member><name>faultString</name><value><string>PC LOAD LETTER</string></value></member></struct></value></fault></methodResponse>'
            ],
            'Standard exception with really messed up non-Latin glyphs' => [
                new \Exception('P̫̬̳̫̓͊̇r̨͎̜ͧa͚̬̙̺͎̙ͬẏ͎̲̦̲e̶̞͎͙̻͐̉r͙̙ͮ̓̈ͧ̔̃ ̠ͨ́ͭ̎̎̇̿n̗̥̞͗o̼̖͛̂̒̿ͮ͘t̷̞͎̘̘̝̥̲͂̌ͭ ͕̹͚̪͖̖̊̆́̒ͫ̓̀fͤͦͭͥ͊ͩo̼̱̻̹͒̿͒u̡͕̞͕̜̠͕ͥͭ̈̄̈́͐ń̘̼̇͜d̸̰̻͎͉̱̰̥̿͒'),
                null,
                '<?xml version="1.0" encoding="UTF-8"?><methodResponse><fault><value><struct><member><name>faultCode</name><value><int>404</int></value></member><member><name>faultString</name><value><string>P̫̬̳̫̓͊̇r̨͎̜ͧa͚̬̙̺͎̙ͬẏ͎̲̦̲e̶̞͎͙̻͐̉r͙̙ͮ̓̈ͧ̔̃ ̠ͨ́ͭ̎̎̇̿n̗̥̞͗o̼̖͛̂̒̿ͮ͘t̷̞͎̘̘̝̥̲͂̌ͭ ͕̹͚̪͖̖̊̆́̒ͫ̓̀fͤͦͭͥ͊ͩo̼̱̻̹͒̿͒u̡͕̞͕̜̠͕ͥͭ̈̄̈́͐ń̘̼̇͜d̸̰̻͎͉̱̰̥̿͒</string></value></member></struct></value></fault></methodResponse>'
            ]
        ];
    }
}
