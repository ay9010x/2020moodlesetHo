<?php



defined('MOODLE_INTERNAL') || die();


class core_requirejs_testcase extends advanced_testcase {

    
    public function test_requirejs() {
        global $CFG;

                $result = core_requirejs::find_one_amd_module('core', 'templates', false);
        $expected = ['core/templates' => $CFG->dirroot . '/lib/amd/build/templates.min.js'];
        $this->assertEquals($expected, $result);

        $result = core_requirejs::find_one_amd_module('core', 'templates', true);
        $expected = ['core/templates' => $CFG->dirroot . '/lib/amd/src/templates.js'];
        $this->assertEquals($expected, $result);

                $result = core_requirejs::find_one_amd_module('core_group', 'doesnotexist', false);
        $expected = [];
        $this->assertEquals($expected, $result);

                $result = core_requirejs::find_one_amd_module('tool_templatelibrary', 'search', true);
        $expected = ['tool_templatelibrary/search' => $CFG->dirroot . '/admin/tool/templatelibrary/amd/src/search.js'];
        $this->assertEquals($expected, $result);

                $result = core_requirejs::find_all_amd_modules(true);
        foreach ($result as $key => $path) {
                        list($component, $template) = explode('/', $key, 2);
                        $dir = core_component::get_component_directory($component);
            $this->assertNotEmpty($dir);

                        if (strpos($component, '_') === false) {
                $this->assertEquals('core', $component);
            }
            $this->assertNotContains('.min', $path);
        }

                $result = core_requirejs::find_all_amd_modules(false);
        foreach ($result as $key => $path) {
                        list($component, $template) = explode('/', $key, 2);
            $dir = core_component::get_component_directory($component);
            $this->assertNotEmpty($dir);
                        if (strpos($component, '_') === false) {
                $this->assertEquals('core', $component);
            }

            $this->assertContains('.min', $path);
        }

    }
}
