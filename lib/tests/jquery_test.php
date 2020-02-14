<?php



defined('MOODLE_INTERNAL') || die();



class core_jquery_testcase extends basic_testcase {

    public function test_plugins_file() {
        global $CFG;

        $plugins = null;
        require($CFG->libdir . '/jquery/plugins.php');
        $this->assertInternalType('array', $plugins);
        $this->assertEquals(array('jquery', 'migrate', 'ui', 'ui-css'), array_keys($plugins));

        foreach ($plugins as $type => $files) {
            foreach ($files['files'] as $file) {
                $this->assertFileExists($CFG->libdir . '/jquery/' . $file);
            }
        }
    }
}
