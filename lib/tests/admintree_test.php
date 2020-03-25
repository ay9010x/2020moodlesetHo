<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir.'/adminlib.php');


class core_admintree_testcase extends advanced_testcase {

    
    public function test_add_nodes() {

        $tree = new admin_root(true);
        $tree->add('root', $one = new admin_category('one', 'One'));
        $tree->add('root', new admin_category('three', 'Three'));
        $tree->add('one', new admin_category('one-one', 'One-one'));
        $tree->add('one', new admin_category('one-three', 'One-three'));

                $map = array();
        foreach ($tree->children as $child) {
            $map[] = $child->name;
        }
        $this->assertEquals(array('one', 'three'), $map);

                $tree->add('root', new admin_category('two', 'Two'), 'three');
        $map = array();
        foreach ($tree->children as $child) {
            $map[] = $child->name;
        }
        $this->assertEquals(array('one', 'two', 'three'), $map);

                $tree->add('root', new admin_category('four', 'Four'), 'five');
        $this->assertDebuggingCalled('Sibling five not found', DEBUG_DEVELOPER);

        $tree->add('root', new admin_category('five', 'Five'));
        $map = array();
        foreach ($tree->children as $child) {
            $map[] = $child->name;
        }
        $this->assertEquals(array('one', 'two', 'three', 'four', 'five'), $map);

                $tree->add('one', new admin_category('one-two', 'One-two'), 'one-three');
        $map = array();
        foreach ($one->children as $child) {
            $map[] = $child->name;
        }
        $this->assertEquals(array('one-one', 'one-two', 'one-three'), $map);

                $tree->add('one', new admin_category('one-four', 'One-four'), 'one');
        $this->assertDebuggingCalled('Sibling one not found', DEBUG_DEVELOPER);

        $tree->add('root', new admin_category('six', 'Six'), 'one-two');
        $this->assertDebuggingCalled('Sibling one-two not found', DEBUG_DEVELOPER);

                $tree->add('root', new admin_externalpage('zero', 'Zero', 'http://foo.bar'), 'one');
        $map = array();
        foreach ($tree->children as $child) {
            $map[] = $child->name;
        }
        $this->assertEquals(array('zero', 'one', 'two', 'three', 'four', 'five', 'six'), $map);
    }

    
    public function test_add_nodes_before_invalid1() {
        $tree = new admin_root(true);
        $tree->add('root', new admin_externalpage('foo', 'Foo', 'http://foo.bar'), array('moodle:site/config'));
    }

    
    public function test_add_nodes_before_invalid2() {
        $tree = new admin_root(true);
        $tree->add('root', new admin_category('bar', 'Bar'), '');
    }

    
    public function test_admin_setting_configexecutable() {
        global $CFG;
        $this->resetAfterTest();

        $executable = new admin_setting_configexecutable('test1', 'Text 1', 'Help Path', '');

                $result = $executable->output_html($CFG->dirroot . '/lib/tests/other/file_does_not_exist');
        $this->assertRegexp('/class="patherror"/', $result);

                $result = $executable->output_html($CFG->dirroot);
        $this->assertRegexp('/class="patherror"/', $result);

                $result = $executable->output_html($CFG->dirroot . '/filter/tex/readme_moodle.txt');
        $this->assertRegexp('/class="patherror"/', $result);

                if ($CFG->ostype == 'WINDOWS') {
            $filetocheck = 'mimetex.exe';
        } else {
            $filetocheck = 'mimetex.darwin';
        }
        $result = $executable->output_html($CFG->dirroot . '/filter/tex/' . $filetocheck);
        $this->assertRegexp('/class="pathok"/', $result);

                $result = $executable->output_html('');
        $this->assertRegexp('/name="s__test1" value=""/', $result);
    }

    
    public function test_config_logging() {
        global $DB;
        $this->resetAfterTest();

        $DB->delete_records('config_log', array());

        $adminroot = new admin_root(true);
        $adminroot->add('root', $one = new admin_category('one', 'One'));
        $page = new admin_settingpage('page', 'Page');
        $page->add(new admin_setting_configtext('text1', 'Text 1', '', ''));
        $page->add(new admin_setting_configpasswordunmask('pass1', 'Password 1', '', ''));
        $adminroot->add('one', $page);

        $this->assertEmpty($DB->get_records('config_log'));
        $data = array('s__text1'=>'sometext', 's__pass1'=>'');
        $count = $this->save_config_data($adminroot, $data);

        $this->assertEquals(2, $count);
        $records = $DB->get_records('config_log', array(), 'id asc');
        $this->assertCount(2, $records);
        reset($records);
        $record = array_shift($records);
        $this->assertNull($record->plugin);
        $this->assertSame('text1', $record->name);
        $this->assertNull($record->oldvalue);
        $this->assertSame('sometext', $record->value);
        $record = array_shift($records);
        $this->assertNull($record->plugin);
        $this->assertSame('pass1', $record->name);
        $this->assertNull($record->oldvalue);
        $this->assertSame('', $record->value);

        $DB->delete_records('config_log', array());
        $data = array('s__text1'=>'other', 's__pass1'=>'nice password');
        $count = $this->save_config_data($adminroot, $data);

        $this->assertEquals(2, $count);
        $records = $DB->get_records('config_log', array(), 'id asc');
        $this->assertCount(2, $records);
        reset($records);
        $record = array_shift($records);
        $this->assertNull($record->plugin);
        $this->assertSame('text1', $record->name);
        $this->assertSame('sometext', $record->oldvalue);
        $this->assertSame('other', $record->value);
        $record = array_shift($records);
        $this->assertNull($record->plugin);
        $this->assertSame('pass1', $record->name);
        $this->assertSame('', $record->oldvalue);
        $this->assertSame('********', $record->value);

        $DB->delete_records('config_log', array());
        $data = array('s__text1'=>'', 's__pass1'=>'');
        $count = $this->save_config_data($adminroot, $data);

        $this->assertEquals(2, $count);
        $records = $DB->get_records('config_log', array(), 'id asc');
        $this->assertCount(2, $records);
        reset($records);
        $record = array_shift($records);
        $this->assertNull($record->plugin);
        $this->assertSame('text1', $record->name);
        $this->assertSame('other', $record->oldvalue);
        $this->assertSame('', $record->value);
        $record = array_shift($records);
        $this->assertNull($record->plugin);
        $this->assertSame('pass1', $record->name);
        $this->assertSame('********', $record->oldvalue);
        $this->assertSame('', $record->value);
    }

    protected function save_config_data(admin_root $adminroot, array $data) {
        $adminroot->errors = array();

        $settings = admin_find_write_settings($adminroot, $data);

        $count = 0;
        foreach ($settings as $fullname=>$setting) {
            
            $original = $setting->get_setting();
            $error = $setting->write_setting($data[$fullname]);
            if ($error !== '') {
                $adminroot->errors[$fullname] = new stdClass();
                $adminroot->errors[$fullname]->data  = $data[$fullname];
                $adminroot->errors[$fullname]->id    = $setting->get_id();
                $adminroot->errors[$fullname]->error = $error;
            } else {
                $setting->write_setting_flags($data);
            }
            if ($setting->post_write_settings($original)) {
                $count++;
            }
        }

        return $count;
    }

    public function test_preventexecpath() {
        $this->resetAfterTest();

        set_config('preventexecpath', 0);
        set_config('execpath', null, 'abc_cde');
        $this->assertFalse(get_config('abc_cde', 'execpath'));
        $setting = new admin_setting_configexecutable('abc_cde/execpath', 'some desc', '', '/xx/yy');
        $setting->write_setting('/oo/pp');
        $this->assertSame('/oo/pp', get_config('abc_cde', 'execpath'));

                set_config('preventexecpath', 1);
        $setting->write_setting('/mm/nn');
        $this->assertSame('/oo/pp', get_config('abc_cde', 'execpath'));

                set_config('execpath', null, 'abc_cde');
        $setting->write_setting('/mm/nn');
        $this->assertSame('/xx/yy', get_config('abc_cde', 'execpath'));

                $setting = new admin_setting_configexecutable('abc_cde/execpath', 'some desc', '', null);
        set_config('execpath', null, 'abc_cde');
        $setting->write_setting('/mm/nn');
        $this->assertSame('', get_config('abc_cde', 'execpath'));

        
        set_config('preventexecpath', 0);
        set_config('execpath', null, 'abc_cde');
        $this->assertFalse(get_config('abc_cde', 'execpath'));
        $setting = new admin_setting_configfile('abc_cde/execpath', 'some desc', '', '/xx/yy');
        $setting->write_setting('/oo/pp');
        $this->assertSame('/oo/pp', get_config('abc_cde', 'execpath'));

                set_config('preventexecpath', 1);
        $setting->write_setting('/mm/nn');
        $this->assertSame('/oo/pp', get_config('abc_cde', 'execpath'));

                set_config('execpath', null, 'abc_cde');
        $setting->write_setting('/mm/nn');
        $this->assertSame('/xx/yy', get_config('abc_cde', 'execpath'));

                $setting = new admin_setting_configfile('abc_cde/execpath', 'some desc', '', null);
        set_config('execpath', null, 'abc_cde');
        $setting->write_setting('/mm/nn');
        $this->assertSame('', get_config('abc_cde', 'execpath'));

        set_config('preventexecpath', 0);
        set_config('execpath', null, 'abc_cde');
        $this->assertFalse(get_config('abc_cde', 'execpath'));
        $setting = new admin_setting_configdirectory('abc_cde/execpath', 'some desc', '', '/xx/yy');
        $setting->write_setting('/oo/pp');
        $this->assertSame('/oo/pp', get_config('abc_cde', 'execpath'));

                set_config('preventexecpath', 1);
        $setting->write_setting('/mm/nn');
        $this->assertSame('/oo/pp', get_config('abc_cde', 'execpath'));

                set_config('execpath', null, 'abc_cde');
        $setting->write_setting('/mm/nn');
        $this->assertSame('/xx/yy', get_config('abc_cde', 'execpath'));

                $setting = new admin_setting_configdirectory('abc_cde/execpath', 'some desc', '', null);
        set_config('execpath', null, 'abc_cde');
        $setting->write_setting('/mm/nn');
        $this->assertSame('', get_config('abc_cde', 'execpath'));
    }
}
