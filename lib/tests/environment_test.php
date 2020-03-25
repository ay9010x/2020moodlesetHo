<?php



defined('MOODLE_INTERNAL') || die();



class core_environment_testcase extends advanced_testcase {

    
    public function test_environment() {
        global $CFG;

        require_once($CFG->libdir.'/environmentlib.php');
        list($envstatus, $environment_results) = check_moodle_environment(normalize_version($CFG->release), ENV_SELECT_RELEASE);

        $this->assertNotEmpty($envstatus);
        foreach ($environment_results as $environment_result) {
            if ($environment_result->part === 'php_setting'
                and $environment_result->info === 'opcache.enable'
                and $environment_result->getLevel() === 'optional'
                and $environment_result->getStatus() === false
            ) {
                $this->markTestSkipped('OPCache extension is not necessary for unit testing.');
                continue;
            }
            $this->assertTrue($environment_result->getStatus(), "Problem detected in environment ($environment_result->part:$environment_result->info), fix all warnings and errors!");
        }
    }

    
    public function test_get_list_of_environment_versions() {
        global $CFG;
        require_once($CFG->libdir.'/environmentlib.php');
                $xml = <<<END
<COMPATIBILITY_MATRIX>
    <MOODLE version="1.9">
        <PHP_EXTENSIONS>
            <PHP_EXTENSION name="xsl" level="required" />
        </PHP_EXTENSIONS>
    </MOODLE>
    <MOODLE version="2.5">
        <PHP_EXTENSIONS>
            <PHP_EXTENSION name="xsl" level="required" />
        </PHP_EXTENSIONS>
    </MOODLE>
    <MOODLE version="2.6">
        <PHP_EXTENSIONS>
            <PHP_EXTENSION name="xsl" level="required" />
        </PHP_EXTENSIONS>
    </MOODLE>
    <MOODLE version="2.7">
        <PHP_EXTENSIONS>
            <PHP_EXTENSION name="xsl" level="required" />
        </PHP_EXTENSIONS>
    </MOODLE>
    <PLUGIN name="block_test">
        <PHP_EXTENSIONS>
            <PHP_EXTENSION name="xsl" level="required" />
        </PHP_EXTENSIONS>
    </PLUGIN>
</COMPATIBILITY_MATRIX>
END;
        $environemt = xmlize($xml);
        $versions = get_list_of_environment_versions($environemt);
        $this->assertCount(5, $versions);
        $this->assertContains('1.9', $versions);
        $this->assertContains('2.5', $versions);
        $this->assertContains('2.6', $versions);
        $this->assertContains('2.7', $versions);
        $this->assertContains('all', $versions);
    }

    
    public function test_verify_plugin() {
        global $CFG;
        require_once($CFG->libdir.'/environmentlib.php');
                $plugin1xml = <<<END
<PLUGIN name="block_testcase">
    <PHP_EXTENSIONS>
        <PHP_EXTENSION name="xsl" level="required" />
    </PHP_EXTENSIONS>
</PLUGIN>
END;
        $plugin1 = xmlize($plugin1xml);
        $plugin2xml = <<<END
<PLUGIN>
    <PHP_EXTENSIONS>
        <PHP_EXTENSION name="xsl" level="required" />
    </PHP_EXTENSIONS>
</PLUGIN>
END;
        $plugin2 = xmlize($plugin2xml);
        $this->assertTrue(environment_verify_plugin('block_testcase', $plugin1['PLUGIN']));
        $this->assertFalse(environment_verify_plugin('block_testcase', $plugin2['PLUGIN']));
        $this->assertFalse(environment_verify_plugin('mod_someother', $plugin1['PLUGIN']));
        $this->assertFalse(environment_verify_plugin('mod_someother', $plugin2['PLUGIN']));
    }

    
    public function test_restrict_php_version_greater_than_restricted_version() {
        global $CFG;
        require_once($CFG->libdir.'/environmentlib.php');

        $result = new environment_results('php');
        $delimiter = '.';
                $currentversion = explode($delimiter, normalize_version(phpversion()));
                $currentversion[0]--;
        $restrictedversion = implode($delimiter, $currentversion);

                $result->setStatus(true);

        $this->assertTrue(restrict_php_version($result, $restrictedversion),
            'restrict_php_version returns true if the current version exceeds the restricted version');
    }

    
    public function test_restrict_php_version_equal_to_restricted_version() {
        global $CFG;
        require_once($CFG->libdir.'/environmentlib.php');

        $result = new environment_results('php');
                $currentversion = normalize_version(phpversion());

                $result->setStatus(true);

        $this->assertTrue(restrict_php_version($result, $currentversion),
            'restrict_php_version returns true if the current version is equal to the restricted version');
    }

    
    public function test_restrict_php_version_less_than_restricted_version() {
        global $CFG;
        require_once($CFG->libdir.'/environmentlib.php');

        $result = new environment_results('php');
        $delimiter = '.';
                $currentversion = explode($delimiter, normalize_version(phpversion()));
                $currentversion[0]++;
        $restrictedversion = implode($delimiter, $currentversion);

                $result->setStatus(true);

        $this->assertFalse(restrict_php_version($result, $restrictedversion),
            'restrict_php_version returns false if the current version is less than the restricted version');
    }
}
