<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/outputfactories.php');
require_once($CFG->libdir . '/tests/fixtures/test_renderer_factory.php');


class core_outputfactories_testcase extends advanced_testcase {

    public function test_nonautoloaded_classnames() {
        global $PAGE;
        $renderer = $PAGE->get_renderer('mod_assign');
    }

    public function test_autoloaded_classnames() {
        $testfactory = new test_output_factory();
        $component = 'mod_assign';
        $subtype = 'custom';
        $target = RENDERER_TARGET_AJAX;

        $paths = $testfactory->get_standard_renderer_factory_search_paths('');
        $this->assertSame($paths, array (
            '\\core\\output\\renderer_cli',
            'core_renderer_cli',
            '\\core\\output\\renderer',
            'core_renderer',
        ));
        $paths = $testfactory->get_standard_renderer_factory_search_paths($component);
        $this->assertSame($paths, array (
            '\\mod_assign\\output\\renderer_cli',
            'mod_assign_renderer_cli',
            '\\mod_assign\\output\\renderer',
            'mod_assign_renderer',
        ));
        $paths = $testfactory->get_standard_renderer_factory_search_paths($component, $subtype);
        $this->assertSame($paths, array (
            '\\mod_assign\\output\\custom_renderer_cli',
            '\\mod_assign\\output\\custom\\renderer_cli',
            'mod_assign_custom_renderer_cli',
            '\\mod_assign\\output\\custom_renderer',
            '\\mod_assign\\output\\custom\\renderer',
            'mod_assign_custom_renderer',
        ));
        $paths = $testfactory->get_standard_renderer_factory_search_paths($component, $subtype, $target);
        $this->assertSame($paths, array (
            '\\mod_assign\\output\\custom_renderer_ajax',
            '\\mod_assign\\output\\custom\\renderer_ajax',
            'mod_assign_custom_renderer_ajax',
            '\\mod_assign\\output\\custom_renderer',
            '\\mod_assign\\output\\custom\\renderer',
            'mod_assign_custom_renderer',
        ));
        $paths = $testfactory->get_theme_overridden_renderer_factory_search_paths('');
        $this->assertSame($paths, array (
            'theme_child\\output\\core_renderer_cli',
            'theme_child_core_renderer_cli',
            'theme_parent\\output\\core_renderer_cli',
            'theme_parent_core_renderer_cli',
            '\\core\\output\\renderer_cli',
            'core_renderer_cli',
            'theme_child\\output\\core_renderer',
            'theme_child_core_renderer',
            'theme_parent\\output\\core_renderer',
            'theme_parent_core_renderer',
            '\\core\\output\\renderer',
            'core_renderer',
        ));
        $paths = $testfactory->get_theme_overridden_renderer_factory_search_paths($component);
        $this->assertSame($paths, array (
            'theme_child\\output\\mod_assign_renderer_cli',
            'theme_child_mod_assign_renderer_cli',
            'theme_parent\\output\\mod_assign_renderer_cli',
            'theme_parent_mod_assign_renderer_cli',
            '\\mod_assign\\output\\renderer_cli',
            'mod_assign_renderer_cli',
            'theme_child\\output\\mod_assign_renderer',
            'theme_child_mod_assign_renderer',
            'theme_parent\\output\\mod_assign_renderer',
            'theme_parent_mod_assign_renderer',
            '\\mod_assign\\output\\renderer',
            'mod_assign_renderer',
        ));
        $paths = $testfactory->get_theme_overridden_renderer_factory_search_paths($component, $subtype);
        $this->assertSame($paths, array (
            'theme_child\\output\\mod_assign\\custom_renderer_cli',
            'theme_child\\output\\mod_assign\\custom\\renderer_cli',
            'theme_child_mod_assign_custom_renderer_cli',
            'theme_parent\\output\\mod_assign\\custom_renderer_cli',
            'theme_parent\\output\\mod_assign\\custom\\renderer_cli',
            'theme_parent_mod_assign_custom_renderer_cli',
            '\\mod_assign\\output\\custom_renderer_cli',
            '\\mod_assign\\output\\custom\\renderer_cli',
            'mod_assign_custom_renderer_cli',
            'theme_child\\output\\mod_assign\\custom_renderer',
            'theme_child\\output\\mod_assign\\custom\\renderer',
            'theme_child_mod_assign_custom_renderer',
            'theme_parent\\output\\mod_assign\\custom_renderer',
            'theme_parent\\output\\mod_assign\\custom\\renderer',
            'theme_parent_mod_assign_custom_renderer',
            '\\mod_assign\\output\\custom_renderer',
            '\\mod_assign\\output\\custom\\renderer',
            'mod_assign_custom_renderer',
        ));
        $paths = $testfactory->get_theme_overridden_renderer_factory_search_paths($component, $subtype, $target);
        $this->assertSame($paths, array (
            'theme_child\\output\\mod_assign\\custom_renderer_ajax',
            'theme_child\\output\\mod_assign\\custom\\renderer_ajax',
            'theme_child_mod_assign_custom_renderer_ajax',
            'theme_parent\\output\\mod_assign\\custom_renderer_ajax',
            'theme_parent\\output\\mod_assign\\custom\\renderer_ajax',
            'theme_parent_mod_assign_custom_renderer_ajax',
            '\\mod_assign\\output\\custom_renderer_ajax',
            '\\mod_assign\\output\\custom\\renderer_ajax',
            'mod_assign_custom_renderer_ajax',
            'theme_child\\output\\mod_assign\\custom_renderer',
            'theme_child\\output\\mod_assign\\custom\\renderer',
            'theme_child_mod_assign_custom_renderer',
            'theme_parent\\output\\mod_assign\\custom_renderer',
            'theme_parent\\output\\mod_assign\\custom\\renderer',
            'theme_parent_mod_assign_custom_renderer',
            '\\mod_assign\\output\\custom_renderer',
            '\\mod_assign\\output\\custom\\renderer',
            'mod_assign_custom_renderer',
        ));
    }
}
