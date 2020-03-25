<?php



defined('MOODLE_INTERNAL') || die();



class core_gdlib_testcase extends basic_testcase {

    private $fixturepath = null;

    public function setUp() {
        $this->fixturepath = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;
    }

    public function test_generate_image_thumbnail() {
        global $CFG;
        require_once($CFG->libdir . '/gdlib.php');

        
                $pngpath = $this->fixturepath . 'gd-logo.png';
        $pngthumb = generate_image_thumbnail($pngpath, 24, 24);
        $this->assertTrue(is_string($pngthumb));

                $imageinfo = getimagesizefromstring($pngthumb);
        $this->assertEquals(24, $imageinfo[0]);
        $this->assertEquals(24, $imageinfo[1]);
        $this->assertEquals('image/png', $imageinfo['mime']);
    }

    public function test_generate_image_thumbnail_from_string() {
        global $CFG;
        require_once($CFG->libdir . '/gdlib.php');

        
                $this->assertFalse(generate_image_thumbnail_from_string('', 24, 24));
        $this->assertFalse(generate_image_thumbnail_from_string('invalid', 0, 24));
        $this->assertFalse(generate_image_thumbnail_from_string('invalid', 24, 0));

                $this->assertFalse(generate_image_thumbnail_from_string('invalid', 24, 24));

                $pngpath = $this->fixturepath . 'gd-logo.png';
        $pngdata = file_get_contents($pngpath);
        $pngthumb = generate_image_thumbnail_from_string($pngdata, 24, 24);
        $this->assertTrue(is_string($pngthumb));

                $imageinfo = getimagesizefromstring($pngthumb);
        $this->assertEquals(24, $imageinfo[0]);
        $this->assertEquals(24, $imageinfo[1]);
        $this->assertEquals('image/png', $imageinfo['mime']);
    }
}
