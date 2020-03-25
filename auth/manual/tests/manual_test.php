<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/auth/manual/auth.php');


class auth_manual_testcase extends advanced_testcase {

    
    protected $authplugin;

    
    protected $config;

    
    protected function setUp() {
        $this->resetAfterTest(true);
        $this->authplugin = new auth_plugin_manual();
        $this->config = new stdClass();
        $this->config->expiration = '1';
        $this->config->expiration_warning = '2';
        $this->config->expirationtime = '30';
        $this->authplugin->process_config($this->config);
        $this->authplugin->config = get_config(auth_plugin_manual::COMPONENT_NAME);
    }

    
    public function test_user_update_password() {
        $user = $this->getDataGenerator()->create_user();
        $expectedtime = time();
        $passwordisupdated = $this->authplugin->user_update_password($user, 'MyNewPassword*');

                $this->assertGreaterThanOrEqual($expectedtime, get_user_preferences('auth_manual_passwordupdatetime', 0, $user->id));

                $this->assertTrue($passwordisupdated);
    }

    
    public function test_password_expire() {
        $userrecord = array();
        $expirationtime = 31 * DAYSECS;
        $userrecord['timecreated'] = time() - $expirationtime;
        $user1 = $this->getDataGenerator()->create_user($userrecord);
        $user2 = $this->getDataGenerator()->create_user();

                $this->assertLessThanOrEqual(-1, $this->authplugin->password_expire($user1->username));

                $this->assertEquals(30, $this->authplugin->password_expire($user2->username));

        $this->authplugin->user_update_password($user1, 'MyNewPassword*');

                $this->assertEquals(30, $this->authplugin->password_expire($user1->username));
    }

    
    public function test_process_config() {
        $this->assertTrue($this->authplugin->process_config($this->config));
        $config = get_config(auth_plugin_manual::COMPONENT_NAME);
        $this->assertEquals($this->config->expiration, $config->expiration);
        $this->assertEquals($this->config->expiration_warning, $config->expiration_warning);
        $this->assertEquals($this->config->expirationtime, $config->expirationtime);
    }
}
