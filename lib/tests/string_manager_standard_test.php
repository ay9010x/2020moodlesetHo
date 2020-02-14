<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir.'/moodlelib.php');


class core_string_manager_standard_testcase extends advanced_testcase {

    public function test_string_manager_instance() {
        $this->resetAfterTest();

        $otherroot = dirname(__FILE__).'/fixtures/langtest';
        $stringman = testable_core_string_manager::instance($otherroot);
        $this->assertInstanceOf('core_string_manager', $stringman);
    }

    public function test_get_language_dependencies() {
        $this->resetAfterTest();

        $otherroot = dirname(__FILE__).'/fixtures/langtest';
        $stringman = testable_core_string_manager::instance($otherroot);

                $this->assertSame(array(), $stringman->get_language_dependencies('en'));
                $this->assertSame(array('aa'), $stringman->get_language_dependencies('aa'));
                $this->assertSame(array('de'), $stringman->get_language_dependencies('de'));
                $this->assertSame(array('de', 'de_du', 'de_kids'), $stringman->get_language_dependencies('de_kids'));
                $this->assertSame(array('sd'), $stringman->get_language_dependencies('sd'));
                $this->assertSame(array('cda', 'cdb', 'cdc'), $stringman->get_language_dependencies('cdc'));
                $this->assertSame(array('bb'), $stringman->get_language_dependencies('bb'));
                $this->assertSame(array('bb', 'bc'), $stringman->get_language_dependencies('bc'));
    }

    public function test_deprecated_strings() {
        $stringman = get_string_manager();

                $this->assertFalse($stringman->string_deprecated('hidden', 'grades'));

                $this->assertTrue($stringman->string_deprecated('timelimitmin', 'mod_quiz'));
        $this->assertTrue($stringman->string_exists('timelimitmin', 'mod_quiz'));
        $this->assertDebuggingNotCalled();
        $this->assertEquals('Time limit (minutes)', get_string('timelimitmin', 'mod_quiz'));
        $this->assertDebuggingCalled('String [timelimitmin,mod_quiz] is deprecated. '.
            'Either you should no longer be using that string, or the string has been incorrectly deprecated, in which case you should report this as a bug. '.
            'Please refer to https://docs.moodle.org/dev/String_deprecation');
    }

    
    public function get_deprecated_strings_provider() {
        global $CFG;

        $teststringman = testable_core_string_manager::instance($CFG->langotherroot, $CFG->langlocalroot, array());
        $allstrings = $teststringman->get_all_deprecated_strings();
        return array_map(function($string) {
            return [$string];
        }, $allstrings);
    }

    
    public function test_validate_deprecated_strings_files($string) {
        $stringman = get_string_manager();

        $result = preg_match('/^(.*),(.*)$/', $string, $matches);
        $this->assertEquals(1, $result);
        $this->assertCount(3, $matches);
        $this->assertEquals($matches[2], clean_param($matches[2], PARAM_COMPONENT),
            "Component name {$string} appearing in one of the lang/en/deprecated.txt files does not have correct syntax");

        list($pluginttype, $pluginname) = core_component::normalize_component($matches[2]);
        $normcomponent = $pluginname ? ($pluginttype . '_' . $pluginname) : $pluginttype;
        $this->assertEquals($normcomponent, $matches[2],
            'String "'.$string.'" appearing in one of the lang/en/deprecated.txt files does not have normalised component name');

        $this->assertTrue($stringman->string_exists($matches[1], $matches[2]),
            "String {$string} appearing in one of the lang/en/deprecated.txt files does not exist");
    }
}


class testable_core_string_manager extends core_string_manager_standard {

    
    public static function instance($otherroot, $localroot = null, $usecache = false, array $translist = array(), $menucache = null) {
        global $CFG;

        if (is_null($localroot)) {
            $localroot = $otherroot;
        }

        if (is_null($menucache)) {
            $menucache = $CFG->cachedir.'/languages';
        }

        return new testable_core_string_manager($otherroot, $localroot, $usecache, $translist, $menucache);
    }

    public function get_all_deprecated_strings() {
        return array_flip($this->load_deprecated_strings());
    }
}
