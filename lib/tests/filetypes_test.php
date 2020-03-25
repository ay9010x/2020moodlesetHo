<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/filelib.php');


class core_filetypes_testcase extends advanced_testcase {

    public function test_add_type() {
        $this->resetAfterTest();

                        $types = get_mimetypes_array();
        $this->assertArrayNotHasKey('frog', $types);
        $this->assertArrayNotHasKey('zombie', $types);

                core_filetypes::add_type('frog', 'application/x-frog', 'document');
        core_filetypes::add_type('zombie', 'application/x-zombie', 'document',
            array('document', 'image'), 'image', 'A zombie', true);

                $types = get_mimetypes_array();
        $this->assertEquals('application/x-frog', $types['frog']['type']);
        $this->assertEquals('document', $types['frog']['icon']);
        $this->assertEquals(array('document', 'image'), $types['zombie']['groups']);
        $this->assertEquals('image', $types['zombie']['string']);
        $this->assertEquals(true, $types['zombie']['defaulticon']);
        $this->assertEquals('A zombie', $types['zombie']['customdescription']);

                try {
            core_filetypes::add_type('frog', 'application/x-frog', 'document');
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertContains('already exists', $e->getMessage());
            $this->assertContains('frog', $e->getMessage());
        }

                try {
            core_filetypes::add_type('.frog', 'application/x-frog', 'document');
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertContains('Invalid extension', $e->getMessage());
            $this->assertContains('..frog', $e->getMessage());
        }
        try {
            core_filetypes::add_type('', 'application/x-frog', 'document');
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertContains('Invalid extension', $e->getMessage());
        }

                        try {
            core_filetypes::add_type('gecko', 'text/plain', 'document',
                    array(), '', '', true);
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertContains('default icon set', $e->getMessage());
            $this->assertContains('text/plain', $e->getMessage());
        }
    }

    public function test_update_type() {
        $this->resetAfterTest();

                $types = get_mimetypes_array();
        $this->assertEquals('application/msword', $types['doc']['type']);

                core_filetypes::update_type('doc', 'doc', 'application/x-frog', 'document');

                        $types = get_mimetypes_array();
        $this->assertEquals('application/x-frog', $types['doc']['type']);
        $this->assertArrayNotHasKey('groups', $types['doc']);

                core_filetypes::update_type('doc', 'docccc', 'application/x-frog', 'document');
        $types = get_mimetypes_array();
        $this->assertEquals('application/x-frog', $types['docccc']['type']);
        $this->assertArrayNotHasKey('doc', $types);

                try {
            core_filetypes::update_type('doc', 'doc', 'application/x-frog', 'document');
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertContains('not found', $e->getMessage());
            $this->assertContains('doc', $e->getMessage());
        }

                try {
            core_filetypes::update_type('docccc', '.frog', 'application/x-frog', 'document');
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertContains('Invalid extension', $e->getMessage());
            $this->assertContains('.frog', $e->getMessage());
        }
        try {
            core_filetypes::update_type('docccc', '', 'application/x-frog', 'document');
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertContains('Invalid extension', $e->getMessage());
        }

                try {
            core_filetypes::update_type('docccc', 'docccc', 'text/plain', 'document',
                    array(), '', '', true);
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertContains('default icon set', $e->getMessage());
            $this->assertContains('text/plain', $e->getMessage());
        }
    }

    public function test_delete_type() {
        $this->resetAfterTest();

                $types = get_mimetypes_array();
        $this->assertArrayHasKey('doc', $types);

                core_filetypes::delete_type('doc');
        $types = get_mimetypes_array();
        $this->assertArrayNotHasKey('doc', $types);

                try {
            core_filetypes::delete_type('doc');
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertContains('not found', $e->getMessage());
            $this->assertContains('doc', $e->getMessage());
        }

                core_filetypes::add_type('frog', 'application/x-frog', 'document');
        $types = get_mimetypes_array();
        $this->assertArrayHasKey('frog', $types);
        core_filetypes::delete_type('frog');
        $types = get_mimetypes_array();
        $this->assertArrayNotHasKey('frog', $types);
    }

    public function test_revert_type_to_default() {
        $this->resetAfterTest();

                core_filetypes::delete_type('doc');
        $this->assertArrayNotHasKey('doc', get_mimetypes_array());
        core_filetypes::revert_type_to_default('doc');
        $this->assertArrayHasKey('doc', get_mimetypes_array());

                core_filetypes::update_type('asm', 'asm', 'text/plain', 'sourcecode', array(), '', 'An asm file');
        $types = get_mimetypes_array();
        $this->assertEquals('An asm file', $types['asm']['customdescription']);
        core_filetypes::revert_type_to_default('asm');
        $types = get_mimetypes_array();
        $this->assertArrayNotHasKey('customdescription', $types['asm']);

                try {
            core_filetypes::revert_type_to_default('frog');
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertContains('not a default type', $e->getMessage());
            $this->assertContains('frog', $e->getMessage());
        }
    }

    
    public function test_cleanup() {
        global $CFG;
        $this->resetAfterTest();

                $this->assertObjectNotHasAttribute('customfiletypes', $CFG);

                core_filetypes::add_type('frog', 'application/x-frog', 'document');
        $this->assertObjectHasAttribute('customfiletypes', $CFG);
        core_filetypes::delete_type('frog');
        $this->assertObjectNotHasAttribute('customfiletypes', $CFG);

                core_filetypes::update_type('asm', 'asm', 'text/plain', 'document');
        $this->assertObjectHasAttribute('customfiletypes', $CFG);
        core_filetypes::update_type('asm', 'asm', 'text/plain', 'sourcecode');
        $this->assertObjectNotHasAttribute('customfiletypes', $CFG);

                core_filetypes::delete_type('asm');
        $this->assertObjectHasAttribute('customfiletypes', $CFG);
        core_filetypes::add_type('asm', 'text/plain', 'sourcecode');
        $this->assertObjectNotHasAttribute('customfiletypes', $CFG);

                core_filetypes::update_type('asm', 'asm', 'text/plain', 'document');
        $this->assertObjectHasAttribute('customfiletypes', $CFG);
        core_filetypes::revert_type_to_default('asm');
        $this->assertObjectNotHasAttribute('customfiletypes', $CFG);

                core_filetypes::delete_type('asm');
        $this->assertObjectHasAttribute('customfiletypes', $CFG);
        core_filetypes::revert_type_to_default('asm');
        $this->assertObjectNotHasAttribute('customfiletypes', $CFG);
    }
}
