<?php




defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/lightboxgallery/lib.php');
require_once($CFG->dirroot . '/mod/lightboxgallery/locallib.php');


class mod_lightboxgallery_lib_testcase extends advanced_testcase {
    public function test_lightboxgallery_resize_text() {
        $this->assertEquals('test123', lightboxgallery_resize_text('test123', 10));
        $this->assertEquals('test123456...', lightboxgallery_resize_text('test1234567', 10));
    }

    public function test_lightboxgallery_edit_types() {
        $this->resetAfterTest();

        $disabledplugins = explode(',', get_config('lightboxgallery', 'disabledplugins'));

        $types = ['caption', 'crop', 'delete', 'flip', 'resize', 'rotate', 'tag', 'thumbnail'];

                $actual = array_keys(lightboxgallery_edit_types(true));
        $this->assertEquals($types, $actual);

                $types = ['caption', 'delete', 'flip', 'resize', 'rotate', 'tag', 'thumbnail'];
        $actual = array_keys(lightboxgallery_edit_types());
        $this->assertEquals($types, $actual);

                $types = ['caption', 'delete', 'resize', 'rotate', 'tag', 'thumbnail'];
        set_config('disabledplugins', 'flip', 'lightboxgallery');
        $actual = array_keys(lightboxgallery_edit_types());
        $this->assertEquals($types, $actual);

        $types = ['caption', 'resize', 'rotate', 'tag', 'thumbnail'];
        set_config('disabledplugins', 'delete,flip', 'lightboxgallery');
        $actual = array_keys(lightboxgallery_edit_types());
        $this->assertEquals($types, $actual);
    }
}
