<?php



defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once($CFG->dirroot . '/mod/lti/locallib.php');
require_once($CFG->dirroot . '/mod/lti/servicelib.php');


class mod_lti_locallib_testcase extends advanced_testcase {

    public function test_split_custom_parameters() {
        $this->resetAfterTest();

        $tool = new stdClass();
        $tool->enabledcapability = '';
        $tool->parameter = '';
        $this->assertEquals(lti_split_custom_parameters(null, $tool, array(), "x=1\ny=2", false),
            array('custom_x' => '1', 'custom_y' => '2'));

        
        $this->assertEquals(lti_split_custom_parameters(null, $tool, array(), 'Review:Chapter=1.2.56', false),
            array('custom_review_chapter' => '1.2.56'));

        $this->assertEquals(lti_split_custom_parameters(null, $tool, array(),
            'Complex!@#$^*(){}[]KEY=Complex!@#$^*;(){}[]½Value', false),
            array('custom_complex____________key' => 'Complex!@#$^*;(){}[]½Value'));

                $user = $this->getDataGenerator()->create_user(array('middlename' => 'SOMETHING'));
        $this->setUser($user);
        $this->assertEquals(array('custom_x' => '1', 'custom_y' => 'SOMETHING'),
            lti_split_custom_parameters(null, $tool, array(), "x=1\ny=\$Person.name.middle", false));
    }

    
    public function disabled_test_sign_parameters() {
        $correct = array ( 'context_id' => '12345', 'context_label' => 'SI124', 'context_title' => 'Social Computing',
            'ext_submit' => 'Click Me', 'lti_message_type' => 'basic-lti-launch-request', 'lti_version' => 'LTI-1p0',
            'oauth_consumer_key' => 'lmsng.school.edu', 'oauth_nonce' => '47458148e33a8f9dafb888c3684cf476',
            'oauth_signature' => 'qWgaBIezihCbeHgcwUy14tZcyDQ=', 'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1307141660', 'oauth_version' => '1.0', 'resource_link_id' => '123',
            'resource_link_title' => 'Weekly Blog', 'roles' => 'Learner', 'tool_consumer_instance_guid' => 'lmsng.school.edu',
            'user_id' => '789');

        $requestparams = array('resource_link_id' => '123', 'resource_link_title' => 'Weekly Blog', 'user_id' => '789',
            'roles' => 'Learner', 'context_id' => '12345', 'context_label' => 'SI124', 'context_title' => 'Social Computing');

        $parms = lti_sign_parameters($requestparams, 'http://www.imsglobal.org/developer/LTI/tool.php', 'POST',
            'lmsng.school.edu', 'secret', 'Click Me', 'lmsng.school.edu' );
        $this->assertTrue(isset($parms['oauth_nonce']));
        $this->assertTrue(isset($parms['oauth_signature']));
        $this->assertTrue(isset($parms['oauth_timestamp']));

                $correct['oauth_nonce'] = $parms['oauth_nonce'];
        $correct['oauth_signature'] = $parms['oauth_signature'];
        $correct['oauth_timestamp'] = $parms['oauth_timestamp'];
        ksort($parms);
        ksort($correct);
        $this->assertEquals($parms, $correct);
    }

    
    public function disabled_test_parse_grade_replace_message() {
        $message = '
            <imsx_POXEnvelopeRequest xmlns = "http://www.imsglobal.org/services/ltiv1p1/xsd/imsoms_v1p0">
              <imsx_POXHeader>
                <imsx_POXRequestHeaderInfo>
                  <imsx_version>V1.0</imsx_version>
                  <imsx_messageIdentifier>999998123</imsx_messageIdentifier>
                </imsx_POXRequestHeaderInfo>
              </imsx_POXHeader>
              <imsx_POXBody>
                <replaceResultRequest>
                  <resultRecord>
                    <sourcedGUID>
                      <sourcedId>' .
            '{&quot;data&quot;:{&quot;instanceid&quot;:&quot;2&quot;,&quot;userid&quot;:&quot;2&quot;},&quot;hash&quot;:' .
            '&quot;0b5078feab59b9938c333ceaae21d8e003a7b295e43cdf55338445254421076b&quot;}' .
                      '</sourcedId>
                    </sourcedGUID>
                    <result>
                      <resultScore>
                        <language>en-us</language>
                        <textString>0.92</textString>
                      </resultScore>
                    </result>
                  </resultRecord>
                </replaceResultRequest>
              </imsx_POXBody>
            </imsx_POXEnvelopeRequest>
';

        $parsed = lti_parse_grade_replace_message(new SimpleXMLElement($message));

        $this->assertEquals($parsed->userid, '2');
        $this->assertEquals($parsed->instanceid, '2');
        $this->assertEquals($parsed->sourcedidhash, '0b5078feab59b9938c333ceaae21d8e003a7b295e43cdf55338445254421076b');

        $ltiinstance = (object)array('servicesalt' => '4e5fcc06de1d58.44963230');

        lti_verify_sourcedid($ltiinstance, $parsed);
    }

    public function test_lti_ensure_url_is_https() {
        $this->assertEquals('https://moodle.org', lti_ensure_url_is_https('http://moodle.org'));
        $this->assertEquals('https://moodle.org', lti_ensure_url_is_https('moodle.org'));
        $this->assertEquals('https://moodle.org', lti_ensure_url_is_https('https://moodle.org'));
    }

    
    public function test_lti_get_url_thumbprint() {
                $this->assertEquals('moodle.org/', lti_get_url_thumbprint('http://MOODLE.ORG'));
        $this->assertEquals('moodle.org/', lti_get_url_thumbprint('http://www.moodle.org'));
        $this->assertEquals('moodle.org/', lti_get_url_thumbprint('https://www.moodle.org'));
        $this->assertEquals('moodle.org/', lti_get_url_thumbprint('moodle.org'));
        $this->assertEquals('moodle.org//this/is/moodle', lti_get_url_thumbprint('http://moodle.org/this/is/moodle'));
        $this->assertEquals('moodle.org//this/is/moodle', lti_get_url_thumbprint('https://moodle.org/this/is/moodle'));
        $this->assertEquals('moodle.org//this/is/moodle', lti_get_url_thumbprint('moodle.org/this/is/moodle'));
        $this->assertEquals('moodle.org//this/is/moodle', lti_get_url_thumbprint('moodle.org/this/is/moodle?'));
        $this->assertEquals('moodle.org//this/is/moodle?foo=bar', lti_get_url_thumbprint('moodle.org/this/is/moodle?foo=bar'));
    }

    
    public function test_lti_buid_request_resource_link_id() {
        $this->resetAfterTest();

        self::setUser($this->getDataGenerator()->create_user());
        $course   = $this->getDataGenerator()->create_course();
        $instance = $this->getDataGenerator()->create_module('lti', array(
            'intro'       => "<p>This</p>\nhas\r\n<p>some</p>\nnew\n\rlines",
            'introformat' => FORMAT_HTML,
            'course'      => $course->id,
        ));

        $typeconfig = array(
            'acceptgrades'     => 1,
            'forcessl'         => 0,
            'sendname'         => 2,
            'sendemailaddr'    => 2,
            'customparameters' => '',
        );

                $params = lti_build_request($instance, $typeconfig, $course, null);
        $this->assertSame($instance->id, $params['resource_link_id']);

                $instance->resource_link_id = $instance->id + 99;
        $params = lti_build_request($instance, $typeconfig, $course, null);
        $this->assertSame($instance->resource_link_id, $params['resource_link_id']);

                unset($instance->id);
        unset($instance->resource_link_id);
        $params = lti_build_request($instance, $typeconfig, $course, null);
        $this->assertArrayNotHasKey('resource_link_id', $params);
    }

    
    public function test_lti_build_request_description() {
        $this->resetAfterTest();

        self::setUser($this->getDataGenerator()->create_user());
        $course   = $this->getDataGenerator()->create_course();
        $instance = $this->getDataGenerator()->create_module('lti', array(
            'intro'       => "<p>This</p>\nhas\r\n<p>some</p>\nnew\n\rlines",
            'introformat' => FORMAT_HTML,
            'course'      => $course->id,
        ));

        $typeconfig = array(
            'acceptgrades'     => 1,
            'forcessl'         => 0,
            'sendname'         => 2,
            'sendemailaddr'    => 2,
            'customparameters' => '',
        );

        $params = lti_build_request($instance, $typeconfig, $course, null);

        $ncount = substr_count($params['resource_link_description'], "\n");
        $this->assertGreaterThan(0, $ncount);

        $rcount = substr_count($params['resource_link_description'], "\r");
        $this->assertGreaterThan(0, $rcount);

        $this->assertEquals($ncount, $rcount, 'The number of \n characters should be the same as the number of \r characters');

        $rncount = substr_count($params['resource_link_description'], "\r\n");
        $this->assertGreaterThan(0, $rncount);

        $this->assertEquals($ncount, $rncount, 'All newline characters should be a combination of \r\n');
    }

    
    public function test_lti_prepare_type_for_save_forcessl() {
        $type = new stdClass();
        $config = new stdClass();

                lti_prepare_type_for_save($type, $config);
        $this->assertObjectHasAttribute('lti_forcessl', $config);
        $this->assertEquals(0, $config->lti_forcessl);
        $this->assertEquals(0, $type->forcessl);

                $config->lti_forcessl = 1;
        lti_prepare_type_for_save($type, $config);
        $this->assertObjectHasAttribute('lti_forcessl', $config);
        $this->assertEquals(1, $config->lti_forcessl);
        $this->assertEquals(1, $type->forcessl);

                $config->lti_forcessl = 0;
        lti_prepare_type_for_save($type, $config);
        $this->assertObjectHasAttribute('lti_forcessl', $config);
        $this->assertEquals(0, $config->lti_forcessl);
        $this->assertEquals(0, $type->forcessl);
    }

    
    public function test_lti_load_type_from_cartridge() {
        $type = new stdClass();
        $type->lti_toolurl = $this->getExternalTestFileUrl('/ims_cartridge_basic_lti_link.xml');

        lti_load_type_if_cartridge($type);

        $this->assertEquals('Example tool', $type->lti_typename);
        $this->assertEquals('Example tool description', $type->lti_description);
        $this->assertEquals('http://www.example.com/lti/provider.php', $type->lti_toolurl);
        $this->assertEquals('http://download.moodle.org/unittest/test.jpg', $type->lti_icon);
        $this->assertEquals('https://download.moodle.org/unittest/test.jpg', $type->lti_secureicon);
    }

    
    public function test_lti_load_tool_from_cartridge() {
        $lti = new stdClass();
        $lti->toolurl = $this->getExternalTestFileUrl('/ims_cartridge_basic_lti_link.xml');

        lti_load_tool_if_cartridge($lti);

        $this->assertEquals('Example tool', $lti->name);
        $this->assertEquals('Example tool description', $lti->intro);
        $this->assertEquals('http://www.example.com/lti/provider.php', $lti->toolurl);
        $this->assertEquals('https://www.example.com/lti/provider.php', $lti->securetoolurl);
        $this->assertEquals('http://download.moodle.org/unittest/test.jpg', $lti->icon);
        $this->assertEquals('https://download.moodle.org/unittest/test.jpg', $lti->secureicon);
    }

    
    public function lti_get_best_tool_by_url_provider() {
        $tools = [
            (object) [
                'name' => 'Here',
                'baseurl' => 'https://example.com/i/am/?where=here',
                'tooldomain' => 'example.com',
                'state' => LTI_TOOL_STATE_CONFIGURED,
                'course' => SITEID
            ],
            (object) [
                'name' => 'There',
                'baseurl' => 'https://example.com/i/am/?where=there',
                'tooldomain' => 'example.com',
                'state' => LTI_TOOL_STATE_CONFIGURED,
                'course' => SITEID
            ],
            (object) [
                'name' => 'Not here',
                'baseurl' => 'https://example.com/i/am/?where=not/here',
                'tooldomain' => 'example.com',
                'state' => LTI_TOOL_STATE_CONFIGURED,
                'course' => SITEID
            ],
            (object) [
                'name' => 'Here',
                'baseurl' => 'https://example.com/i/am/',
                'tooldomain' => 'example.com',
                'state' => LTI_TOOL_STATE_CONFIGURED,
                'course' => SITEID
            ],
            (object) [
                'name' => 'Here',
                'baseurl' => 'https://example.com/i/was',
                'tooldomain' => 'example.com',
                'state' => LTI_TOOL_STATE_CONFIGURED,
                'course' => SITEID
            ],
            (object) [
                'name' => 'Here',
                'baseurl' => 'https://badexample.com/i/am/?where=here',
                'tooldomain' => 'badexample.com',
                'state' => LTI_TOOL_STATE_CONFIGURED,
                'course' => SITEID
            ],
        ];

        $data = [
            [
                'url' => $tools[0]->baseurl,
                'expected' => $tools[0],
            ],
            [
                'url' => $tools[1]->baseurl,
                'expected' => $tools[1],
            ],
            [
                'url' => $tools[2]->baseurl,
                'expected' => $tools[2],
            ],
            [
                'url' => $tools[3]->baseurl,
                'expected' => $tools[3],
            ],
            [
                'url' => $tools[4]->baseurl,
                'expected' => $tools[4],
            ],
            [
                'url' => $tools[5]->baseurl,
                'expected' => $tools[5],
            ],
            [
                'url' => 'https://nomatch.com/i/am/',
                'expected' => null
            ],
            [
                'url' => 'https://example.com',
                'expected' => null
            ],
            [
                'url' => 'https://example.com/i/am/?where=unknown',
                'expected' => $tools[3]
            ]
        ];

                                return array_map(function($data) use ($tools) {
            return [$data['url'], $data['expected'], $tools];
        }, $data);
    }

    
    public function test_lti_get_best_tool_by_url($url, $expected, $tools) {
        $actual = lti_get_best_tool_by_url($url, $tools, null);
        $this->assertSame($expected, $actual);
    }
}
