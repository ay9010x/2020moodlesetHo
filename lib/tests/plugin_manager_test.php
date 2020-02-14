<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/lib/tests/fixtures/testable_plugin_manager.php');
require_once($CFG->dirroot.'/lib/tests/fixtures/testable_plugininfo_base.php');


class core_plugin_manager_testcase extends advanced_testcase {

    public function tearDown() {
                        testable_core_plugin_manager::reset_caches();
    }

    public function test_instance() {
        $pluginman1 = core_plugin_manager::instance();
        $this->assertInstanceOf('core_plugin_manager', $pluginman1);
        $pluginman2 = core_plugin_manager::instance();
        $this->assertSame($pluginman1, $pluginman2);
        $pluginman3 = testable_core_plugin_manager::instance();
        $this->assertInstanceOf('core_plugin_manager', $pluginman3);
        $this->assertInstanceOf('testable_core_plugin_manager', $pluginman3);
        $pluginman4 = testable_core_plugin_manager::instance();
        $this->assertSame($pluginman3, $pluginman4);
        $this->assertNotSame($pluginman1, $pluginman3);
    }

    public function test_reset_caches() {
                core_plugin_manager::reset_caches();
        testable_core_plugin_manager::reset_caches();
    }

    
    public function test_teardown_works_precheck() {
        $pluginman = testable_core_plugin_manager::instance();
        $pluginfo = testable_plugininfo_base::fake_plugin_instance('fake', '/dev/null', 'one', '/dev/null/fake',
            'testable_plugininfo_base', $pluginman);
        $pluginman->inject_testable_plugininfo('fake', 'one', $pluginfo);

        $this->assertInstanceOf('\core\plugininfo\base', $pluginman->get_plugin_info('fake_one'));
        $this->assertNull($pluginman->get_plugin_info('fake_two'));
    }

    public function test_teardown_works_postcheck() {
        $pluginman = testable_core_plugin_manager::instance();
        $this->assertNull($pluginman->get_plugin_info('fake_one'));
        $this->assertNull($pluginman->get_plugin_info('fake_two'));
    }

    public function test_get_plugin_types() {
                $types = core_plugin_manager::instance()->get_plugin_types();
        $this->assertInternalType('array', $types);
        foreach ($types as $type => $fulldir) {
            $this->assertFileExists($fulldir);
        }
    }

    public function test_get_installed_plugins() {
        $types = core_plugin_manager::instance()->get_plugin_types();
        foreach ($types as $type => $fulldir) {
            $installed = core_plugin_manager::instance()->get_installed_plugins($type);
            foreach ($installed as $plugin => $version) {
                $this->assertRegExp('/^[a-z]+[a-z0-9_]*$/', $plugin);
                $this->assertTrue(is_numeric($version), 'All plugins should have a version, plugin '.$type.'_'.$plugin.' does not have version info.');
            }
        }
    }

    public function test_get_enabled_plugins() {
        $types = core_plugin_manager::instance()->get_plugin_types();
        foreach ($types as $type => $fulldir) {
            $enabled = core_plugin_manager::instance()->get_enabled_plugins($type);
            if (is_array($enabled)) {
                foreach ($enabled as $key => $val) {
                    $this->assertRegExp('/^[a-z]+[a-z0-9_]*$/', $key);
                    $this->assertSame($key, $val);
                }
            } else {
                $this->assertNull($enabled);
            }
        }
    }

    public function test_get_present_plugins() {
        $types = core_plugin_manager::instance()->get_plugin_types();
        foreach ($types as $type => $fulldir) {
            $present = core_plugin_manager::instance()->get_present_plugins($type);
            if (is_array($present)) {
                foreach ($present as $plugin => $version) {
                    $this->assertRegExp('/^[a-z]+[a-z0-9_]*$/', $plugin, 'All plugins are supposed to have version.php file.');
                    $this->assertInternalType('object', $version);
                    $this->assertTrue(is_numeric($version->version), 'All plugins should have a version, plugin '.$type.'_'.$plugin.' does not have version info.');
                }
            } else {
                                $this->assertNull($present);
            }
        }
    }

    public function test_get_plugins() {
        $plugininfos1 = core_plugin_manager::instance()->get_plugins();
        foreach ($plugininfos1 as $type => $infos) {
            foreach ($infos as $name => $info) {
                $this->assertInstanceOf('\core\plugininfo\base', $info);
            }
        }

                        $plugininfos2 = testable_core_plugin_manager::instance()->get_plugins();
        $this->assertNotSame($plugininfos1['mod']['forum'], $plugininfos2['mod']['forum']);

                $plugininfos3 = core_plugin_manager::instance()->get_plugins();
        $this->assertSame($plugininfos1['mod']['forum'], $plugininfos3['mod']['forum']);
        $plugininfos4 = testable_core_plugin_manager::instance()->get_plugins();
        $this->assertSame($plugininfos2['mod']['forum'], $plugininfos4['mod']['forum']);
    }

    public function test_plugininfo_back_reference_to_the_plugin_manager() {
        $plugman1 = core_plugin_manager::instance();
        $plugman2 = testable_core_plugin_manager::instance();

        foreach ($plugman1->get_plugins() as $type => $infos) {
            foreach ($infos as $info) {
                $this->assertSame($info->pluginman, $plugman1);
            }
        }

        foreach ($plugman2->get_plugins() as $type => $infos) {
            foreach ($infos as $info) {
                $this->assertSame($info->pluginman, $plugman2);
            }
        }
    }

    public function test_get_plugins_of_type() {
        $plugininfos = core_plugin_manager::instance()->get_plugins();
        foreach ($plugininfos as $type => $infos) {
            $this->assertSame($infos, core_plugin_manager::instance()->get_plugins_of_type($type));
        }
    }

    public function test_get_subplugins_of_plugin() {
        global $CFG;

                $this->assertFileExists("$CFG->dirroot/lib/editor/tinymce", 'TinyMCE is not present.');

        $subplugins = core_plugin_manager::instance()->get_subplugins_of_plugin('editor_tinymce');
        foreach ($subplugins as $component => $info) {
            $this->assertInstanceOf('\core\plugininfo\base', $info);
        }
    }

    public function test_get_subplugins() {
                $subplugins = core_plugin_manager::instance()->get_subplugins();
        $this->assertInternalType('array', $subplugins);
    }

    public function test_get_parent_of_subplugin() {
        global $CFG;

                $this->assertFileExists("$CFG->dirroot/lib/editor/tinymce", 'TinyMCE is not present.');

        $parent = core_plugin_manager::instance()->get_parent_of_subplugin('tinymce');
        $this->assertSame('editor_tinymce', $parent);
    }

    public function test_plugin_name() {
        global $CFG;

                $this->assertFileExists("$CFG->dirroot/lib/editor/tinymce", 'TinyMCE is not present.');

        $name = core_plugin_manager::instance()->plugin_name('editor_tinymce');
        $this->assertSame(get_string('pluginname', 'editor_tinymce'), $name);
    }

    public function test_plugintype_name() {
        $name = core_plugin_manager::instance()->plugintype_name('editor');
        $this->assertSame(get_string('type_editor', 'core_plugin'), $name);
    }

    public function test_plugintype_name_plural() {
        $name = core_plugin_manager::instance()->plugintype_name_plural('editor');
        $this->assertSame(get_string('type_editor_plural', 'core_plugin'), $name);
    }

    public function test_get_plugin_info() {
        global $CFG;

                $this->assertFileExists("$CFG->dirroot/lib/editor/tinymce", 'TinyMCE is not present.');

        $info = core_plugin_manager::instance()->get_plugin_info('editor_tinymce');
        $this->assertInstanceOf('\core\plugininfo\editor', $info);
    }

    public function test_can_uninstall_plugin() {
        global $CFG;

                $this->assertFileExists("$CFG->dirroot/$CFG->admin/tool/assignmentupgrade", 'assign upgrade tool is not present');
        $this->assertFileExists("$CFG->dirroot/mod/assign", 'assign module is not present');

        $this->assertFalse(core_plugin_manager::instance()->can_uninstall_plugin('mod_assign'));
        $this->assertTrue(core_plugin_manager::instance()->can_uninstall_plugin('tool_assignmentupgrade'));
    }

    public function test_plugin_states() {
        global $CFG;
        $this->resetAfterTest();

                $this->assertFileExists("$CFG->dirroot/mod/assign", 'assign module is not present');
        $this->assertFileExists("$CFG->dirroot/mod/forum", 'forum module is not present');
        $this->assertFileExists("$CFG->dirroot/$CFG->admin/tool/phpunit", 'phpunit tool is not present');
        $this->assertFileNotExists("$CFG->dirroot/mod/xxxxxxx");
        $this->assertFileNotExists("$CFG->dirroot/enrol/autorize");

                $assignversion = get_config('mod_assign', 'version');
        set_config('version', $assignversion - 1, 'mod_assign');
                $forumversion = get_config('mod_forum', 'version');
        set_config('version', $forumversion + 1, 'mod_forum');
                unset_config('version', 'tool_phpunit');
                set_config('version', 2013091300, 'mod_xxxxxxx');
                set_config('version', 2013091300, 'enrol_authorize');

        core_plugin_manager::reset_caches();

        $plugininfos = core_plugin_manager::instance()->get_plugins();
        foreach ($plugininfos as $type => $infos) {
            foreach ($infos as $name => $info) {
                
                if ($info->component === 'mod_assign') {
                    $this->assertSame(core_plugin_manager::PLUGIN_STATUS_UPGRADE, $info->get_status(), 'Invalid '.$info->component.' state');
                } else if ($info->component === 'mod_forum') {
                    $this->assertSame(core_plugin_manager::PLUGIN_STATUS_DOWNGRADE, $info->get_status(), 'Invalid '.$info->component.' state');
                } else if ($info->component === 'tool_phpunit') {
                    $this->assertSame(core_plugin_manager::PLUGIN_STATUS_NEW, $info->get_status(), 'Invalid '.$info->component.' state');
                } else if ($info->component === 'mod_xxxxxxx') {
                    $this->assertSame(core_plugin_manager::PLUGIN_STATUS_MISSING, $info->get_status(), 'Invalid '.$info->component.' state');
                } else if ($info->component === 'enrol_authorize') {
                    $this->assertSame(core_plugin_manager::PLUGIN_STATUS_DELETE, $info->get_status(), 'Invalid '.$info->component.' state');
                } else {
                    $this->assertSame(core_plugin_manager::PLUGIN_STATUS_UPTODATE, $info->get_status(), 'Invalid '.$info->component.' state');
                }
            }
        }
    }

    public function test_plugin_available_updates() {
        $pluginman = testable_core_plugin_manager::instance();

        $foobar = testable_plugininfo_base::fake_plugin_instance('foo', '/dev/null', 'bar', '/dev/null/fake',
            'testable_plugininfo_base', $pluginman);
        $foobar->versiondb = 2015092900;
        $foobar->versiondisk = 2015092900;
        $pluginman->inject_testable_plugininfo('foo', 'bar', $foobar);

        $washere = false;
        foreach ($pluginman->get_plugins() as $type => $infos) {
            foreach ($infos as $name => $plugin) {
                $updates = $plugin->available_updates();
                if ($plugin->component != 'foo_bar') {
                    $this->assertNull($updates);
                } else {
                    $this->assertTrue(is_array($updates));
                    $this->assertEquals(3, count($updates));
                    foreach ($updates as $update) {
                        $washere = true;
                        $this->assertInstanceOf('\core\update\info', $update);
                        $this->assertEquals($update->component, $plugin->component);
                        $this->assertTrue($update->version > $plugin->versiondb);
                    }
                }
            }
        }
        $this->assertTrue($washere);
    }

    public function test_some_plugins_updatable_none() {
        $pluginman = testable_core_plugin_manager::instance();
        $this->assertFalse($pluginman->some_plugins_updatable());
    }

    public function test_some_plugins_updatable_some() {
        $pluginman = testable_core_plugin_manager::instance();

        $foobar = testable_plugininfo_base::fake_plugin_instance('foo', '/dev/null', 'bar', '/dev/null/fake',
            'testable_plugininfo_base', $pluginman);
        $foobar->versiondb = 2015092900;
        $foobar->versiondisk = 2015092900;
        $pluginman->inject_testable_plugininfo('foo', 'bar', $foobar);

        $this->assertTrue($pluginman->some_plugins_updatable());
    }

    public function test_available_updates() {
        $pluginman = testable_core_plugin_manager::instance();

        $foobar = testable_plugininfo_base::fake_plugin_instance('foo', '/dev/null', 'bar', '/dev/null/fake',
            'testable_plugininfo_base', $pluginman);
        $foobar->versiondb = 2015092900;
        $foobar->versiondisk = 2015092900;
        $pluginman->inject_testable_plugininfo('foo', 'bar', $foobar);

        $updates = $pluginman->available_updates();

        $this->assertTrue(is_array($updates));
        $this->assertEquals(1, count($updates));
        $update = $updates['foo_bar'];
        $this->assertInstanceOf('\core\update\remote_info', $update);
        $this->assertEquals('foo_bar', $update->component);
        $this->assertEquals(2015100400, $update->version->version);
    }

    public function test_get_remote_plugin_info() {
        $pluginman = testable_core_plugin_manager::instance();

        $this->assertFalse($pluginman->get_remote_plugin_info('not_exists', ANY_VERSION, false));

        $info = $pluginman->get_remote_plugin_info('foo_bar', 2015093000, true);
        $this->assertEquals(2015093000, $info->version->version);

        $info = $pluginman->get_remote_plugin_info('foo_bar', 2015093000, false);
        $this->assertEquals(2015100400, $info->version->version);
    }

    
    public function test_get_remote_plugin_info_exception() {
        $pluginman = testable_core_plugin_manager::instance();
        $pluginman->get_remote_plugin_info('any_thing', ANY_VERSION, true);
    }

    public function test_is_remote_plugin_available() {
        $pluginman = testable_core_plugin_manager::instance();

        $this->assertFalse($pluginman->is_remote_plugin_available('not_exists', ANY_VERSION, false));
        $this->assertTrue($pluginman->is_remote_plugin_available('foo_bar', 2013131313, false));
        $this->assertFalse($pluginman->is_remote_plugin_available('foo_bar', 2013131313, true));
    }

    public function test_resolve_requirements() {
        $pluginman = testable_core_plugin_manager::instance();

                $pluginfo = testable_plugininfo_base::fake_plugin_instance('fake', '/dev/null', 'one', '/dev/null/fake',
            'testable_plugininfo_base', $pluginman);
        $pluginfo->versiondisk = 2015060600;

                $pluginfo->versionrequires = null;
        $this->assertTrue($pluginfo->is_core_dependency_satisfied(2015100100));
        $reqs = $pluginman->resolve_requirements($pluginfo, 2015100100, 29);
        $this->assertEquals(2015100100, $reqs['core']->hasver);
        $this->assertEquals(ANY_VERSION, $reqs['core']->reqver);
        $this->assertEquals($pluginman::REQUIREMENT_STATUS_OK, $reqs['core']->status);

                $pluginfo->versionrequires = 2015110900;
        $this->assertFalse($pluginfo->is_core_dependency_satisfied(2015100100));
        $reqs = $pluginman->resolve_requirements($pluginfo, 2015100100, 29);
        $this->assertEquals(2015100100, $reqs['core']->hasver);
        $this->assertEquals(2015110900, $reqs['core']->reqver);
        $this->assertEquals($pluginman::REQUIREMENT_STATUS_OUTDATED, $reqs['core']->status);

                $pluginfo->versionrequires = 2015110900;
        $this->assertTrue($pluginfo->is_core_dependency_satisfied(2015110900));
        $reqs = $pluginman->resolve_requirements($pluginfo, 2015110900, 30);
        $this->assertEquals(2015110900, $reqs['core']->hasver);
        $this->assertEquals(2015110900, $reqs['core']->reqver);
        $this->assertEquals($pluginman::REQUIREMENT_STATUS_OK, $reqs['core']->status);

                $pluginfo->versionrequires = 2014122400;
        $this->assertTrue($pluginfo->is_core_dependency_satisfied(2015100100));
        $reqs = $pluginman->resolve_requirements($pluginfo, 2015100100, 29);
        $this->assertEquals(2015100100, $reqs['core']->hasver);
        $this->assertEquals(2014122400, $reqs['core']->reqver);
        $this->assertEquals($pluginman::REQUIREMENT_STATUS_OK, $reqs['core']->status);

                
        $pluginfo->dependencies = array('foo_bar' => ANY_VERSION, 'not_exists' => ANY_VERSION);
        $reqs = $pluginman->resolve_requirements($pluginfo, 2015110900, 30);
        $this->assertNull($reqs['foo_bar']->hasver);
        $this->assertEquals(ANY_VERSION, $reqs['foo_bar']->reqver);
        $this->assertEquals($pluginman::REQUIREMENT_STATUS_MISSING, $reqs['foo_bar']->status);
        $this->assertEquals($pluginman::REQUIREMENT_AVAILABLE, $reqs['foo_bar']->availability);
        $this->assertEquals($pluginman::REQUIREMENT_UNAVAILABLE, $reqs['not_exists']->availability);

        $pluginfo->dependencies = array('foo_bar' => 2013122400);
        $reqs = $pluginman->resolve_requirements($pluginfo, 2015110900, 30);
        $this->assertEquals($pluginman::REQUIREMENT_AVAILABLE, $reqs['foo_bar']->availability);

        $pluginfo->dependencies = array('foo_bar' => 2015093000);
        $reqs = $pluginman->resolve_requirements($pluginfo, 2015110900, 30);
        $this->assertEquals($pluginman::REQUIREMENT_AVAILABLE, $reqs['foo_bar']->availability);

        $pluginfo->dependencies = array('foo_bar' => 2015100500);
        $reqs = $pluginman->resolve_requirements($pluginfo, 2015110900, 30);
        $this->assertEquals($pluginman::REQUIREMENT_AVAILABLE, $reqs['foo_bar']->availability);

        $pluginfo->dependencies = array('foo_bar' => 2025010100);
        $reqs = $pluginman->resolve_requirements($pluginfo, 2015110900, 30);
        $this->assertEquals($pluginman::REQUIREMENT_UNAVAILABLE, $reqs['foo_bar']->availability);

                $pluginfo = testable_plugininfo_base::fake_plugin_instance('fake', '/dev/null', 'missing', '/dev/null/fake',
            'testable_plugininfo_base', $pluginman);
        $pluginfo->versiondisk = null;
        $this->assertEmpty($pluginman->resolve_requirements($pluginfo, 2015110900, 30));
    }

    public function test_missing_dependencies() {
        $pluginman = testable_core_plugin_manager::instance();

        $one = testable_plugininfo_base::fake_plugin_instance('fake', '/dev/null', 'one', '/dev/null/fake',
            'testable_plugininfo_base', $pluginman);
        $one->versiondisk = 2015070800;

        $two = testable_plugininfo_base::fake_plugin_instance('fake', '/dev/null', 'two', '/dev/null/fake',
            'testable_plugininfo_base', $pluginman);
        $two->versiondisk = 2015070900;

        $pluginman->inject_testable_plugininfo('fake', 'one', $one);
        $pluginman->inject_testable_plugininfo('fake', 'two', $two);

        $this->assertEmpty($pluginman->missing_dependencies());

        $one->dependencies = array('foo_bar' => ANY_VERSION);
        $misdeps = $pluginman->missing_dependencies();
        $this->assertInstanceOf('\core\update\remote_info', $misdeps['foo_bar']);
        $this->assertEquals(2015100400, $misdeps['foo_bar']->version->version);

        $two->dependencies = array('foo_bar' => 2015100500);
        $misdeps = $pluginman->missing_dependencies();
        $this->assertInstanceOf('\core\update\remote_info', $misdeps['foo_bar']);
        $this->assertEquals(2015100500, $misdeps['foo_bar']->version->version);
    }
}
