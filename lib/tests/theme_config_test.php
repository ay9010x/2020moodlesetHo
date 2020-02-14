<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/outputlib.php');


class core_theme_config_testcase extends advanced_testcase {
    
    public function test_svg_image_use() {
        global $CFG;

        $this->resetAfterTest();

                $this->assertTrue(file_exists($CFG->dirroot.'/pix/i/test.svg'));
        $this->assertTrue(file_exists($CFG->dirroot.'/pix/i/test.png'));

        $theme = theme_config::load(theme_config::DEFAULT_THEME);

                $imagefile = $theme->resolve_image_location('i/test', 'moodle', true);
        $this->assertSame('test.svg', basename($imagefile));
        $imagefile = $theme->resolve_image_location('i/test', 'moodle', false);
        $this->assertSame('test.png', basename($imagefile));

                        $testtheme = clone $theme;
        $CFG->svgicons = true;
        $imagefile = $testtheme->resolve_image_location('i/test', 'moodle', null);
        $this->assertSame('test.svg', basename($imagefile));
        $CFG->svgicons = false;
                $testtheme = clone $theme;
        $imagefile = $testtheme->resolve_image_location('i/test', 'moodle', null);
        $this->assertSame('test.png', basename($imagefile));
        unset($CFG->svgicons);

                $useragents = array(
                        'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)' => false,
                        'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)' => false,
                        'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; Trident/4.0)' => false,
                        'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0)' => false,
                        'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)' => true,
                        'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; Trident/5.0)' => false,
                        'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Trident/6.0; Touch)' => true,
                        'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.2; Trident/6.0; Touch; .NET4.0E; .NET4.0C; Tablet PC 2.0)' => true,
                        'Mozilla/5.0 (Windows NT 6.3; WOW64; Trident/7.0; rv:11.0)' => true,
                        'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.3; Trident/7.0; .NET4.0E; .NET4.0C)' => true,
                        'Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/534.17 (KHTML, like Gecko) Chrome/11.0.652.0 Safari/534.17' => true,
                        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/22.0.1207.1 Safari/537.1' => true,
                        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.89 Safari/537.1' => true,
                        'Mozilla/5.0 (Windows NT 6.1; rv:1.9) Gecko/20100101 Firefox/4.0' => true,
                        'Mozilla/5.0 (Windows NT 6.1; rv:15.0) Gecko/20120716 Firefox/15.0.1' => true,
                        'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:15.0) Gecko/20100101 Firefox/15.0.1' => true,
                        'Opera/9.80 (X11; Linux x86_64; U; en) Presto/2.10.289 Version/12.02' => false,
                        'Mozilla/5.0 (Linux; U; Android 0.5; en-us) AppleWebKit/522+ (KHTML, like Gecko) Safari/419.3' => false,
                        'Mozilla/5.0 (Linux; U; Android 2.3.5; en-us; HTC Vision Build/GRI40) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1' => false,
                        'Mozilla/5.0 (Linux; U; Android 3.0; en-us; Xoom Build/HRI39) AppleWebKit/534.13 (KHTML, like Gecko) Version/4.0 Safari/534.13' => true,
                        'Mozilla/5.0 (Linux; Android 4.3; it-it; SAMSUNG GT-I9505/I9505XXUEMJ7 Build/JSS15J) AppleWebKit/537.36 (KHTML, like Gecko) Version/1.5 Chrome/28.0.1500.94 Mobile Safari/537.36' => true
        );
        foreach ($useragents as $agent => $expected) {
            core_useragent::instance(true, $agent);
                        $testtheme = clone $theme;
            $imagefile = $testtheme->resolve_image_location('i/test', 'moodle', null);
            if ($expected) {
                $this->assertSame('test.svg', basename($imagefile), 'Incorrect image returned for user agent `'.$agent.'`');
            } else {
                $this->assertSame('test.png', basename($imagefile), 'Incorrect image returned for user agent `'.$agent.'`');
            }
        }
    }

    
    public function test_devicedetectregex() {
        global $CFG;

        $this->resetAfterTest();

                $this->assertEmpty(json_decode($CFG->devicedetectregex));
        $this->assertTrue(core_useragent::set_user_device_type('tablet'));
        $exceptionoccured = false;
        try {
            core_useragent::set_user_device_type('featurephone');
        } catch (moodle_exception $e) {
            $exceptionoccured = true;
        }
        $this->assertTrue($exceptionoccured);

                $config = array('featurephone' => '(Symbian|MIDP-1.0|Maemo|Windows CE)');
        $CFG->devicedetectregex = json_encode($config);
        core_useragent::instance(true);         $this->assertTrue(core_useragent::set_user_device_type('tablet'));
        $this->assertTrue(core_useragent::set_user_device_type('featurephone'));
    }
}
