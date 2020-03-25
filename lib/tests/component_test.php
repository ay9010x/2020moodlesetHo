<?php



defined('MOODLE_INTERNAL') || die();



class core_component_testcase extends advanced_testcase {

                const SUBSYSTEMCOUNT = 65;

    public function test_get_core_subsystems() {
        global $CFG;

        $subsystems = core_component::get_core_subsystems();

        $this->assertCount(self::SUBSYSTEMCOUNT, $subsystems, 'Oh, somebody added or removed a core subsystem, think twice before doing that!');

                foreach ($subsystems as $subsystem => $fulldir) {
            $this->assertFalse(strpos($subsystem, '_'), 'Core subsystems must be one work without underscores');
            if ($fulldir === null) {
                if ($subsystem === 'filepicker' or $subsystem === 'help') {
                                    } else {
                                        $this->assertFileExists("$CFG->dirroot/lang/en/$subsystem.php", 'Core subsystems without fulldir are usually used for lang strings.');
                }
                continue;
            }
            $this->assertFileExists($fulldir);
                        $this->assertStringStartsWith($CFG->dirroot.'/', $fulldir);
            $reldir = substr($fulldir, strlen($CFG->dirroot)+1);
            $this->assertFalse(strpos($reldir, '\\'));
        }

                $items = new DirectoryIterator("$CFG->dirroot/lang/en");
        foreach ($items as $item) {
            if ($item->isDot() or $item->isDir()) {
                continue;
            }
            $file = $item->getFilename();
            if ($file === 'moodle.php') {
                                continue;
            }

            if (substr($file, -4) !== '.php') {
                continue;
            }
            $file = substr($file, 0, strlen($file)-4);
            $this->assertArrayHasKey($file, $subsystems, 'All core lang files should be subsystems, think twice before adding anything!');
        }
        unset($item);
        unset($items);

    }

    public function test_deprecated_get_core_subsystems() {
        global $CFG;

        $subsystems = core_component::get_core_subsystems();

        $this->assertSame($subsystems, get_core_subsystems(true));

        $realsubsystems = get_core_subsystems();
        $this->assertDebuggingCalled();
        $this->assertSame($realsubsystems, get_core_subsystems(false));
        $this->assertDebuggingCalled();

        $this->assertEquals(count($subsystems), count($realsubsystems));

        foreach ($subsystems as $subsystem => $fulldir) {
            $this->assertArrayHasKey($subsystem, $realsubsystems);
            if ($fulldir === null) {
                $this->assertNull($realsubsystems[$subsystem]);
                continue;
            }
            $this->assertSame($fulldir, $CFG->dirroot.'/'.$realsubsystems[$subsystem]);
        }
    }

    public function test_get_plugin_types() {
        global $CFG;

        $this->assertTrue(empty($CFG->themedir), 'Non-empty $CFG->themedir is not covered by any tests yet, you need to disable it.');

        $plugintypes = core_component::get_plugin_types();

        foreach ($plugintypes as $plugintype => $fulldir) {
            $this->assertStringStartsWith("$CFG->dirroot/", $fulldir);
        }
    }

    public function test_deprecated_get_plugin_types() {
        global $CFG;

        $plugintypes = core_component::get_plugin_types();

        $this->assertSame($plugintypes, get_plugin_types());
        $this->assertSame($plugintypes, get_plugin_types(true));

        $realplugintypes = get_plugin_types(false);
        $this->assertDebuggingCalled();

        foreach ($plugintypes as $plugintype => $fulldir) {
            $this->assertSame($fulldir, $CFG->dirroot.'/'.$realplugintypes[$plugintype]);
        }
    }

    public function test_get_plugin_list() {
        global $CFG;

        $plugintypes = core_component::get_plugin_types();

        foreach ($plugintypes as $plugintype => $fulldir) {
            $plugins = core_component::get_plugin_list($plugintype);
            foreach ($plugins as $pluginname => $plugindir) {
                $this->assertStringStartsWith("$CFG->dirroot/", $plugindir);
            }
            if ($plugintype !== 'auth') {
                                $reldir = substr($fulldir, strlen($CFG->dirroot)+1);
                $dirs = get_list_of_plugins($reldir);
                $dirs = array_values($dirs);
                $this->assertDebuggingCalled();
                $this->assertSame($dirs, array_keys($plugins));
            }
        }
    }

    public function test_deprecated_get_plugin_list() {
        $plugintypes = core_component::get_plugin_types();

        foreach ($plugintypes as $plugintype => $fulldir) {
            $plugins = core_component::get_plugin_list($plugintype);
            $this->assertSame($plugins, get_plugin_list($plugintype));
        }
    }

    public function test_get_plugin_directory() {
        $plugintypes = core_component::get_plugin_types();

        foreach ($plugintypes as $plugintype => $fulldir) {
            $plugins = core_component::get_plugin_list($plugintype);
            foreach ($plugins as $pluginname => $plugindir) {
                $this->assertSame($plugindir, core_component::get_plugin_directory($plugintype, $pluginname));
            }
        }
    }

    public function test_deprecated_get_plugin_directory() {
        $plugintypes = core_component::get_plugin_types();

        foreach ($plugintypes as $plugintype => $fulldir) {
            $plugins = core_component::get_plugin_list($plugintype);
            foreach ($plugins as $pluginname => $plugindir) {
                $this->assertSame(core_component::get_plugin_directory($plugintype, $pluginname), get_plugin_directory($plugintype, $pluginname));
            }
        }
    }

    public function test_get_subsystem_directory() {
        $subsystems = core_component::get_core_subsystems();
        foreach ($subsystems as $subsystem => $fulldir) {
            $this->assertSame($fulldir, core_component::get_subsystem_directory($subsystem));
        }
    }

    public function test_is_valid_plugin_name() {
        $this->assertTrue(core_component::is_valid_plugin_name('mod', 'example1'));
        $this->assertTrue(core_component::is_valid_plugin_name('mod', 'feedback360'));
        $this->assertFalse(core_component::is_valid_plugin_name('mod', 'feedback_360'));
        $this->assertFalse(core_component::is_valid_plugin_name('mod', '2feedback'));
        $this->assertFalse(core_component::is_valid_plugin_name('mod', '1example'));
        $this->assertFalse(core_component::is_valid_plugin_name('mod', 'example.xx'));
        $this->assertFalse(core_component::is_valid_plugin_name('mod', '.example'));
        $this->assertFalse(core_component::is_valid_plugin_name('mod', '_example'));
        $this->assertFalse(core_component::is_valid_plugin_name('mod', 'example_'));
        $this->assertFalse(core_component::is_valid_plugin_name('mod', 'example_x1'));
        $this->assertFalse(core_component::is_valid_plugin_name('mod', 'example-x1'));
        $this->assertFalse(core_component::is_valid_plugin_name('mod', 'role'));

        $this->assertTrue(core_component::is_valid_plugin_name('tool', 'example1'));
        $this->assertTrue(core_component::is_valid_plugin_name('tool', 'example_x1'));
        $this->assertTrue(core_component::is_valid_plugin_name('tool', 'example_x1_xxx'));
        $this->assertTrue(core_component::is_valid_plugin_name('tool', 'feedback360'));
        $this->assertTrue(core_component::is_valid_plugin_name('tool', 'feed_back360'));
        $this->assertTrue(core_component::is_valid_plugin_name('tool', 'role'));
        $this->assertFalse(core_component::is_valid_plugin_name('tool', '1example'));
        $this->assertFalse(core_component::is_valid_plugin_name('tool', 'example.xx'));
        $this->assertFalse(core_component::is_valid_plugin_name('tool', 'example-xx'));
        $this->assertFalse(core_component::is_valid_plugin_name('tool', '.example'));
        $this->assertFalse(core_component::is_valid_plugin_name('tool', '_example'));
        $this->assertFalse(core_component::is_valid_plugin_name('tool', 'example_'));
        $this->assertFalse(core_component::is_valid_plugin_name('tool', 'example__x1'));
    }

    public function test_normalize_componentname() {
                $this->assertSame('core', core_component::normalize_componentname('core'));
        $this->assertSame('core', core_component::normalize_componentname('moodle'));
        $this->assertSame('core', core_component::normalize_componentname(''));

                $this->assertSame('core_admin', core_component::normalize_componentname('admin'));
        $this->assertSame('core_admin', core_component::normalize_componentname('core_admin'));
        $this->assertSame('core_admin', core_component::normalize_componentname('moodle_admin'));

                $this->assertSame('mod_workshop', core_component::normalize_componentname('workshop'));
        $this->assertSame('mod_workshop', core_component::normalize_componentname('mod_workshop'));
        $this->assertSame('workshopform_accumulative', core_component::normalize_componentname('workshopform_accumulative'));
        $this->assertSame('mod_quiz', core_component::normalize_componentname('quiz'));
        $this->assertSame('quiz_grading', core_component::normalize_componentname('quiz_grading'));
        $this->assertSame('mod_data', core_component::normalize_componentname('data'));
        $this->assertSame('datafield_checkbox', core_component::normalize_componentname('datafield_checkbox'));

                $this->assertSame('auth_mnet', core_component::normalize_componentname('auth_mnet'));
        $this->assertSame('enrol_self', core_component::normalize_componentname('enrol_self'));
        $this->assertSame('block_html', core_component::normalize_componentname('block_html'));
        $this->assertSame('block_mnet_hosts', core_component::normalize_componentname('block_mnet_hosts'));
        $this->assertSame('local_amos', core_component::normalize_componentname('local_amos'));
        $this->assertSame('local_admin', core_component::normalize_componentname('local_admin'));

                $this->assertSame('mod_whoonearthwouldcomewithsuchastupidnameofcomponent',
            core_component::normalize_componentname('whoonearthwouldcomewithsuchastupidnameofcomponent'));
                $this->assertSame('whoonearth_wouldcomewithsuchastupidnameofcomponent',
            core_component::normalize_componentname('whoonearth_wouldcomewithsuchastupidnameofcomponent'));
        $this->assertSame('whoonearth_would_come_withsuchastupidnameofcomponent',
            core_component::normalize_componentname('whoonearth_would_come_withsuchastupidnameofcomponent'));
    }

    public function test_normalize_component() {
                $this->assertSame(array('core', null), core_component::normalize_component('core'));
        $this->assertSame(array('core', null), core_component::normalize_component('moodle'));
        $this->assertSame(array('core', null), core_component::normalize_component(''));

                $this->assertSame(array('core', 'admin'), core_component::normalize_component('admin'));
        $this->assertSame(array('core', 'admin'), core_component::normalize_component('core_admin'));
        $this->assertSame(array('core', 'admin'), core_component::normalize_component('moodle_admin'));

                $this->assertSame(array('mod', 'workshop'), core_component::normalize_component('workshop'));
        $this->assertSame(array('mod', 'workshop'), core_component::normalize_component('mod_workshop'));
        $this->assertSame(array('workshopform', 'accumulative'), core_component::normalize_component('workshopform_accumulative'));
        $this->assertSame(array('mod', 'quiz'), core_component::normalize_component('quiz'));
        $this->assertSame(array('quiz', 'grading'), core_component::normalize_component('quiz_grading'));
        $this->assertSame(array('mod', 'data'), core_component::normalize_component('data'));
        $this->assertSame(array('datafield', 'checkbox'), core_component::normalize_component('datafield_checkbox'));

                $this->assertSame(array('auth', 'mnet'), core_component::normalize_component('auth_mnet'));
        $this->assertSame(array('enrol', 'self'), core_component::normalize_component('enrol_self'));
        $this->assertSame(array('block', 'html'), core_component::normalize_component('block_html'));
        $this->assertSame(array('block', 'mnet_hosts'), core_component::normalize_component('block_mnet_hosts'));
        $this->assertSame(array('local', 'amos'), core_component::normalize_component('local_amos'));
        $this->assertSame(array('local', 'admin'), core_component::normalize_component('local_admin'));

                $this->assertSame(array('mod', 'whoonearthwouldcomewithsuchastupidnameofcomponent'),
            core_component::normalize_component('whoonearthwouldcomewithsuchastupidnameofcomponent'));
                $this->assertSame(array('whoonearth', 'wouldcomewithsuchastupidnameofcomponent'),
            core_component::normalize_component('whoonearth_wouldcomewithsuchastupidnameofcomponent'));
        $this->assertSame(array('whoonearth', 'would_come_withsuchastupidnameofcomponent'),
            core_component::normalize_component('whoonearth_would_come_withsuchastupidnameofcomponent'));
    }

    public function test_deprecated_normalize_component() {
                $this->assertSame(array('core', null), normalize_component('core'));
        $this->assertSame(array('core', null), normalize_component(''));
        $this->assertSame(array('core', null), normalize_component('moodle'));

                $this->assertSame(array('core', 'admin'), normalize_component('admin'));
        $this->assertSame(array('core', 'admin'), normalize_component('core_admin'));
        $this->assertSame(array('core', 'admin'), normalize_component('moodle_admin'));

                $this->assertSame(array('mod', 'workshop'), normalize_component('workshop'));
        $this->assertSame(array('mod', 'workshop'), normalize_component('mod_workshop'));
        $this->assertSame(array('workshopform', 'accumulative'), normalize_component('workshopform_accumulative'));
        $this->assertSame(array('mod', 'quiz'), normalize_component('quiz'));
        $this->assertSame(array('quiz', 'grading'), normalize_component('quiz_grading'));
        $this->assertSame(array('mod', 'data'), normalize_component('data'));
        $this->assertSame(array('datafield', 'checkbox'), normalize_component('datafield_checkbox'));

                $this->assertSame(array('auth', 'mnet'), normalize_component('auth_mnet'));
        $this->assertSame(array('enrol', 'self'), normalize_component('enrol_self'));
        $this->assertSame(array('block', 'html'), normalize_component('block_html'));
        $this->assertSame(array('block', 'mnet_hosts'), normalize_component('block_mnet_hosts'));
        $this->assertSame(array('local', 'amos'), normalize_component('local_amos'));
        $this->assertSame(array('local', 'admin'), normalize_component('local_admin'));

                $this->assertSame(array('mod', 'whoonearthwouldcomewithsuchastupidnameofcomponent'),
            normalize_component('whoonearthwouldcomewithsuchastupidnameofcomponent'));
                $this->assertSame(array('whoonearth', 'wouldcomewithsuchastupidnameofcomponent'),
            normalize_component('whoonearth_wouldcomewithsuchastupidnameofcomponent'));
        $this->assertSame(array('whoonearth', 'would_come_withsuchastupidnameofcomponent'),
            normalize_component('whoonearth_would_come_withsuchastupidnameofcomponent'));
    }

    public function test_get_component_directory() {
        $plugintypes = core_component::get_plugin_types();
        foreach ($plugintypes as $plugintype => $fulldir) {
            $plugins = core_component::get_plugin_list($plugintype);
            foreach ($plugins as $pluginname => $plugindir) {
                $this->assertSame($plugindir, core_component::get_component_directory(($plugintype.'_'.$pluginname)));
            }
        }

        $subsystems = core_component::get_core_subsystems();
        foreach ($subsystems as $subsystem => $fulldir) {
            $this->assertSame($fulldir, core_component::get_component_directory(('core_'.$subsystem)));
        }
    }

    public function test_deprecated_get_component_directory() {
        $plugintypes = core_component::get_plugin_types();
        foreach ($plugintypes as $plugintype => $fulldir) {
            $plugins = core_component::get_plugin_list($plugintype);
            foreach ($plugins as $pluginname => $plugindir) {
                $this->assertSame($plugindir, get_component_directory(($plugintype.'_'.$pluginname)));
            }
        }

        $subsystems = core_component::get_core_subsystems();
        foreach ($subsystems as $subsystem => $fulldir) {
            $this->assertSame($fulldir, get_component_directory(('core_'.$subsystem)));
        }
    }

    public function test_get_subtype_parent() {
        global $CFG;

        $this->assertNull(core_component::get_subtype_parent('mod'));

                $this->assertFileExists("$CFG->dirroot/mod/assign/db/subplugins.php");
        $this->assertSame('mod_assign', core_component::get_subtype_parent('assignsubmission'));
        $this->assertSame('mod_assign', core_component::get_subtype_parent('assignfeedback'));
        $this->assertNull(core_component::get_subtype_parent('assignxxxxx'));
    }

    public function test_get_subplugins() {
        global $CFG;

                $this->assertFileExists("$CFG->dirroot/mod/assign/db/subplugins.php");

        $subplugins = core_component::get_subplugins('mod_assign');
        $this->assertSame(array('assignsubmission', 'assignfeedback'), array_keys($subplugins));

        $subs = core_component::get_plugin_list('assignsubmission');
        $feeds = core_component::get_plugin_list('assignfeedback');

        $this->assertSame(array_keys($subs), $subplugins['assignsubmission']);
        $this->assertSame(array_keys($feeds), $subplugins['assignfeedback']);

                $this->assertFileExists("$CFG->dirroot/mod/choice");
        $this->assertFileNotExists("$CFG->dirroot/mod/choice/db/subplugins.php");

        $this->assertNull(core_component::get_subplugins('mod_choice'));

        $this->assertNull(core_component::get_subplugins('xxxx_yyyy'));
    }

    public function test_get_plugin_types_with_subplugins() {
        global $CFG;

        $types = core_component::get_plugin_types_with_subplugins();

                $expected = array(
            'mod' => "$CFG->dirroot/mod",
            'editor' => "$CFG->dirroot/lib/editor",
            'tool' => "$CFG->dirroot/$CFG->admin/tool",
            'local' => "$CFG->dirroot/local",
        );

        $this->assertSame($expected, $types);

    }

    public function test_get_plugin_list_with_file() {
        $this->resetAfterTest(true);

        
        $expected = array();
        $reports = core_component::get_plugin_list('report');
        foreach ($reports as $name => $fulldir) {
            if (file_exists("$fulldir/lib.php")) {
                $expected[] = $name;
            }
        }

                $list = core_component::get_plugin_list_with_file('report', 'lib.php', false);
        $this->assertEquals($expected, array_keys($list));

                $list = core_component::get_plugin_list_with_file('report', 'lib.php', false);
        $this->assertEquals($expected, array_keys($list));

                $list = core_component::get_plugin_list_with_file('report', 'lib.php', true);
        $this->assertEquals($expected, array_keys($list));

                $list = core_component::get_plugin_list_with_file('report', 'idontexist.php', true);
        $this->assertEquals(array(), array_keys($list));
    }

    public function test_get_component_classes_int_namespace() {

                $this->assertCount(0, core_component::get_component_classes_in_namespace('core_unexistingcomponent', 'something'));
        $this->assertCount(0, core_component::get_component_classes_in_namespace('auth_cas', 'something'));

                $this->assertCount(0, core_component::get_component_classes_in_namespace('auth_cas', 'tas'));
        $this->assertCount(0, core_component::get_component_classes_in_namespace('core_user', 'course'));
        $this->assertCount(0, core_component::get_component_classes_in_namespace('mod_forum', 'output\\emaildigest'));
        $this->assertCount(0, core_component::get_component_classes_in_namespace('mod_forum', '\\output\\emaildigest'));
        $this->assertCount(2, core_component::get_component_classes_in_namespace('mod_forum', 'output\\email'));
        $this->assertCount(2, core_component::get_component_classes_in_namespace('mod_forum', '\\output\\email'));
        $this->assertCount(2, core_component::get_component_classes_in_namespace('mod_forum', 'output\\email\\'));
        $this->assertCount(2, core_component::get_component_classes_in_namespace('mod_forum', '\\output\\email\\'));

                $this->assertCount(1, core_component::get_component_classes_in_namespace('auth_cas', 'task'));
        $this->assertCount(1, core_component::get_component_classes_in_namespace('auth_cas', '\\task'));

                $this->assertCount(7, core_component::get_component_classes_in_namespace('core', 'update'));
        $this->assertCount(7, core_component::get_component_classes_in_namespace('', 'update'));
        $this->assertCount(7, core_component::get_component_classes_in_namespace('moodle', 'update'));

                $this->assertCount(5, core_component::get_component_classes_in_namespace('core_user', '\\output\\myprofile\\'));
        $this->assertCount(5, core_component::get_component_classes_in_namespace('core_user', 'output\\myprofile\\'));
        $this->assertCount(5, core_component::get_component_classes_in_namespace('core_user', '\\output\\myprofile'));
        $this->assertCount(5, core_component::get_component_classes_in_namespace('core_user', 'output\\myprofile'));

                $this->assertCount(2, core_component::get_component_classes_in_namespace('tool_mobile', ''));
        $this->assertCount(1, core_component::get_component_classes_in_namespace('tool_filetypes'));
    }
}
