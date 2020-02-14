<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/externallib.php');
require_once($CFG->dirroot . '/webservice/tests/helpers.php');


class core_webservice_externallib_testcase extends externallib_advanced_testcase {

    public function setUp() {
                parent::setUp();

                set_config('enablewebservices', '1');
    }

    public function test_get_site_info() {
        global $DB, $USER, $CFG;

        $this->resetAfterTest(true);

                set_config('release', '2.4dev (Build: 20120823)');
        set_config('version', '2012083100.00');

        $maxbytes = 10485760;
        $userquota = 5242880;
        set_config('maxbytes', $maxbytes);
        set_config('userquota', $userquota);

                $user = array();
        $user['username'] = 'johnd';
        $user['firstname'] = 'John';
        $user['lastname'] = 'Doe';
        self::setUser(self::getDataGenerator()->create_user($user));

                $webservice = new stdClass();
        $webservice->name = 'Test web service';
        $webservice->enabled = true;
        $webservice->restrictedusers = false;
        $webservice->component = 'moodle';
        $webservice->timecreated = time();
        $webservice->downloadfiles = true;
        $webservice->uploadfiles = true;
        $externalserviceid = $DB->insert_record('external_services', $webservice);

                $DB->insert_record('external_services_functions', array('externalserviceid' => $externalserviceid,
            'functionname' => 'core_course_get_contents'));

        $_POST['wstoken'] = 'testtoken';
        $externaltoken = new stdClass();
        $externaltoken->token = 'testtoken';
        $externaltoken->tokentype = 0;
        $externaltoken->userid = $USER->id;
        $externaltoken->externalserviceid = $externalserviceid;
        $externaltoken->contextid = 1;
        $externaltoken->creatorid = $USER->id;
        $externaltoken->timecreated = time();
        $DB->insert_record('external_tokens', $externaltoken);

        $siteinfo = core_webservice_external::get_site_info();

                $siteinfo = external_api::clean_returnvalue(core_webservice_external::get_site_info_returns(), $siteinfo);

        $this->assertEquals('johnd', $siteinfo['username']);
        $this->assertEquals('John', $siteinfo['firstname']);
        $this->assertEquals('Doe', $siteinfo['lastname']);
        $this->assertEquals(current_language(), $siteinfo['lang']);
        $this->assertEquals($USER->id, $siteinfo['userid']);
        $this->assertEquals(true, $siteinfo['downloadfiles']);
        $this->assertEquals($CFG->release, $siteinfo['release']);
        $this->assertEquals($CFG->version, $siteinfo['version']);
        $this->assertEquals($CFG->mobilecssurl, $siteinfo['mobilecssurl']);
        $this->assertEquals(count($siteinfo['functions']), 1);
        $function = array_pop($siteinfo['functions']);
        $this->assertEquals($function['name'], 'core_course_get_contents');
        $this->assertEquals($function['version'], $siteinfo['version']);
        $this->assertEquals(1, $siteinfo['downloadfiles']);
        $this->assertEquals(1, $siteinfo['uploadfiles']);

        foreach ($siteinfo['advancedfeatures'] as $feature) {
            if ($feature['name'] == 'mnet_dispatcher_mode') {
                if ($CFG->mnet_dispatcher_mode == 'off') {
                    $this->assertEquals(0, $feature['value']);
                } else {
                    $this->assertEquals(1, $feature['value']);
                }
            } else {
                $this->assertEquals($CFG->{$feature['name']}, $feature['value']);
            }
        }

        $this->assertEquals($userquota, $siteinfo['userquota']);
                        $this->assertEquals(get_max_upload_file_size($maxbytes), $siteinfo['usermaxuploadfilesize']);
        $this->assertEquals(true, $siteinfo['usercanmanageownfiles']);

        $this->assertEquals(HOMEPAGE_MY, $siteinfo['userhomepage']);

                $this->setAdminUser();

                $_POST['wstoken'] = 'testtoken';
        $externaltoken = new stdClass();
        $externaltoken->token = 'testtoken';
        $externaltoken->tokentype = 0;
        $externaltoken->userid = $USER->id;
        $externaltoken->externalserviceid = $externalserviceid;
        $externaltoken->contextid = 1;
        $externaltoken->creatorid = $USER->id;
        $externaltoken->timecreated = time();
        $DB->insert_record('external_tokens', $externaltoken);

                $CFG->defaulthomepage = HOMEPAGE_USER;
        set_user_preference('user_home_page_preference', HOMEPAGE_SITE);

        $siteinfo = core_webservice_external::get_site_info();

                $siteinfo = external_api::clean_returnvalue(core_webservice_external::get_site_info_returns(), $siteinfo);

        $this->assertEquals(0, $siteinfo['userquota']);
        $this->assertEquals(USER_CAN_IGNORE_FILE_SIZE_LIMITS, $siteinfo['usermaxuploadfilesize']);
        $this->assertEquals(true, $siteinfo['usercanmanageownfiles']);

        $this->assertEquals(HOMEPAGE_SITE, $siteinfo['userhomepage']);

    }

}
