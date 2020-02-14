<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/medialib.php');


class core_medialib_testcase extends advanced_testcase {

    
    public static $includecoverage = array('lib/medialib.php', 'lib/outputrenderers.php');

    
    public function setUp() {
        global $CFG;
        parent::setUp();

                $this->resetAfterTest();

                $CFG->core_media_enable_html5video = false;
        $CFG->core_media_enable_html5audio = false;
        $CFG->core_media_enable_mp3 = false;
        $CFG->core_media_enable_flv = false;
        $CFG->core_media_enable_wmp = false;
        $CFG->core_media_enable_qt = false;
        $CFG->core_media_enable_rm = false;
        $CFG->core_media_enable_youtube = false;
        $CFG->core_media_enable_vimeo = false;
        $CFG->core_media_enable_swf = false;

        $_SERVER = array('HTTP_USER_AGENT' => '');
        $this->pretend_to_be_safari();
    }

    
    private function pretend_to_be_safari() {
                core_useragent::instance(true, 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_8; de-at) ' .
                'AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1');
    }

    
    private function pretend_to_be_firefox() {
                core_useragent::instance(true, 'Mozilla/5.0 (X11; Linux x86_64; rv:46.0) Gecko/20100101 Firefox/46.0 ');
    }

    
    public function test_is_enabled() {
        global $CFG;

                $test = new core_media_player_test;
        $this->assertFalse($test->is_enabled());
        $CFG->core_media_enable_test = 0;
        $this->assertFalse($test->is_enabled());
        $CFG->core_media_enable_test = 1;
        $this->assertTrue($test->is_enabled());
    }

    
    public function test_get_filename() {
        $this->assertSame('frog.mp4', core_media::get_filename(new moodle_url(
                '/pluginfile.php/312/mod_page/content/7/frog.mp4')));
                        $this->assertSame('frog.mp4', core_media::get_filename(new moodle_url(
                '/pluginfile.php?file=/312/mod_page/content/7/frog.mp4')));
    }

    
    public function test_get_extension() {
        $this->assertSame('mp4', core_media::get_extension(new moodle_url(
                '/pluginfile.php/312/mod_page/content/7/frog.mp4')));
        $this->assertSame('', core_media::get_extension(new moodle_url(
                '/pluginfile.php/312/mod_page/content/7/frog')));
        $this->assertSame('mp4', core_media::get_extension(new moodle_url(
                '/pluginfile.php?file=/312/mod_page/content/7/frog.mp4')));
        $this->assertSame('', core_media::get_extension(new moodle_url(
                '/pluginfile.php?file=/312/mod_page/content/7/frog')));
    }

    
    public function test_list_supported_urls() {
        global $CFG;
        $test = new core_media_player_test;

                $supported1 = new moodle_url('http://example.org/1.test');
        $supported2 = new moodle_url('http://example.org/2.TST');
        $unsupported = new moodle_url('http://example.org/2.jpg');

                $result = $test->list_supported_urls(array());
        $this->assertEquals(array(), $result);

                $result = $test->list_supported_urls(array($supported1));
        $this->assertEquals(array($supported1), $result);

                $result = $test->list_supported_urls(array($supported1, $supported2));
        $this->assertEquals(array($supported1, $supported2), $result);

                $result = $test->list_supported_urls(array($unsupported));
        $this->assertEquals(array(), $result);

                $result = $test->list_supported_urls(array($supported2, $unsupported, $supported1));
        $this->assertEquals(array($supported2, $supported1), $result);
    }

    
    public function test_get_players() {
        global $CFG, $PAGE;

                $renderer = new core_media_renderer_test($PAGE, '');
        $this->assertSame('link', $renderer->get_players_test());

                $CFG->core_media_enable_html5audio = true;
        $CFG->core_media_enable_mp3 = true;
        $renderer = new core_media_renderer_test($PAGE, '');
        $this->assertSame('mp3, html5audio, link', $renderer->get_players_test());

                $CFG->core_media_enable_mp3 = false;
        $CFG->core_media_enable_html5video = true;
        $CFG->core_media_enable_qt = true;
        $renderer = new core_media_renderer_test($PAGE, '');
        $this->assertSame('html5video, html5audio, qt, link', $renderer->get_players_test());
    }

    
    public function test_can_embed_url() {
        global $CFG, $PAGE;

                $url = new moodle_url('http://example.org/test.mp4');
        $renderer = new core_media_renderer_test($PAGE, '');
        $this->assertFalse($renderer->can_embed_url($url));

                $CFG->core_media_enable_qt = true;
        $renderer = new core_media_renderer_test($PAGE, '');
        $this->assertTrue($renderer->can_embed_url($url));

                $CFG->core_media_enable_html5video = true;
        $renderer = new core_media_renderer_test($PAGE, '');
        $this->assertTrue($renderer->can_embed_url($url));

                $CFG->core_media_enable_qt = false;
        $renderer = new core_media_renderer_test($PAGE, '');
        $this->assertTrue($renderer->can_embed_url($url));

                $CFG->core_media_enable_html5video = false;
        $CFG->core_media_enable_wmp = true;
        $renderer = new core_media_renderer_test($PAGE, '');
        $this->assertFalse($renderer->can_embed_url($url));
    }

    
    public function test_embed_url_fallbacks() {
        global $CFG, $PAGE;

                $qt = 'qtplugin.cab';
        $html5video = '</video>';
        $html5audio = '</audio>';
        $link = 'mediafallbacklink';
        $mp3 = 'mediaplugin_mp3';

        $url = new moodle_url('http://example.org/test.mp4');

                $renderer = new core_media_renderer_test($PAGE, '');
        $t = $renderer->embed_url($url, 0, 0, '',
                array(core_media::OPTION_NO_LINK => true));
                $this->assertSame('', $t);

                $renderer = new core_media_renderer_test($PAGE, '');
        $t = $renderer->embed_url($url);
        $this->assertContains($link, $t);

                $CFG->core_media_enable_html5video = true;
        $CFG->core_media_enable_html5audio = true;
        $CFG->core_media_enable_mp3 = true;
        $CFG->core_media_enable_qt = true;

                $mediaformats = array('mp3', 'm4a', 'mp4', 'm4v');

        foreach ($mediaformats as $format) {
            $url = new moodle_url('http://example.org/test.' . $format);
            $renderer = new core_media_renderer_test($PAGE, '');
            $textwithlink = $renderer->embed_url($url);
            $textwithoutlink = $renderer->embed_url($url, 0, 0, '', array(core_media::OPTION_NO_LINK => true));

            switch ($format) {
                case 'mp3':
                    $this->assertContains($mp3, $textwithlink);
                    $this->assertContains($html5audio, $textwithlink);
                    $this->assertContains($link, $textwithlink);

                    $this->assertContains($mp3, $textwithoutlink);
                    $this->assertContains($html5audio, $textwithoutlink);
                    $this->assertNotContains($link, $textwithoutlink);
                    break;

                case 'm4a':
                    $this->assertContains($qt, $textwithlink);
                    $this->assertContains($html5audio, $textwithlink);
                    $this->assertContains($link, $textwithlink);

                    $this->assertContains($qt, $textwithoutlink);
                    $this->assertContains($html5audio, $textwithoutlink);
                    $this->assertNotContains($link, $textwithoutlink);
                    break;

                case 'mp4':
                case 'm4v':
                    $this->assertContains($qt, $textwithlink);
                    $this->assertContains($html5video, $textwithlink);
                    $this->assertContains($link, $textwithlink);

                    $this->assertContains($qt, $textwithoutlink);
                    $this->assertContains($html5video, $textwithoutlink);
                    $this->assertNotContains($link, $textwithoutlink);
                    break;

                default:
                    break;
            }
        }
    }

    
    public function test_embed_url_swf() {
        global $CFG, $PAGE;
        $CFG->core_media_enable_swf = true;
        $renderer = new core_media_renderer_test($PAGE, '');

                $url = new moodle_url('http://example.org/test.swf');
        $t = $renderer->embed_url($url);
        $this->assertNotContains('</object>', $t);

                $url = new moodle_url('http://example.org/test.swf');
        $t = $renderer->embed_url($url, '', 0, 0, array(core_media::OPTION_TRUSTED => true));
        $this->assertContains('</object>', $t);
    }

    
    public function test_embed_url_other_formats() {
        global $CFG, $PAGE;

                $CFG->core_media_enable_html5audio = true;
        $CFG->core_media_enable_mp3 = true;
        $CFG->core_media_enable_flv = true;
        $CFG->core_media_enable_wmp = true;
        $CFG->core_media_enable_rm = true;
        $CFG->core_media_enable_youtube = true;
        $CFG->core_media_enable_vimeo = true;
        $renderer = new core_media_renderer_test($PAGE, '');

                        
                $url = new moodle_url('http://example.org/test.mp3');
        $t = $renderer->embed_url($url);
        $this->assertContains('core_media_mp3_', $t);

                $url = new moodle_url('http://example.org/test.flv');
        $t = $renderer->embed_url($url);
        $this->assertContains('core_media_flv_', $t);

                $url = new moodle_url('http://example.org/test.avi');
        $t = $renderer->embed_url($url);
        $this->assertContains('6BF52A52-394A-11d3-B153-00C04F79FAA6', $t);

                $url = new moodle_url('http://example.org/test.rm');
        $t = $renderer->embed_url($url);
        $this->assertContains('CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA', $t);

                $url = new moodle_url('http://www.youtube.com/watch?v=vyrwMmsufJc');
        $t = $renderer->embed_url($url);
        $this->assertContains('</iframe>', $t);
        $url = new moodle_url('http://www.youtube.com/v/vyrwMmsufJc');
        $t = $renderer->embed_url($url);
        $this->assertContains('</iframe>', $t);

                $url = new moodle_url('https://www.youtube.com/watch?v=dv2f_xfmbD8&index=4&list=PLxcO_MFWQBDcyn9xpbmx601YSDlDcTcr0');
        $t = $renderer->embed_url($url);
        $this->assertContains('</iframe>', $t);
        $this->assertContains('list=PLxcO_MFWQBDcyn9xpbmx601YSDlDcTcr0', $t);

                $url = new moodle_url('https://www.youtube.com/watch?v=JNJMF1l3udM&t=1h11s');
        $t = $renderer->embed_url($url);
        $this->assertContains('</iframe>', $t);
        $this->assertContains('start=3611', $t);

                $url = new moodle_url('https://www.youtube.com/watch?v=dv2f_xfmbD8&index=4&list=PLxcO_MFWQBDcyn9xpbmx601YSDlDcTcr0&t=1m5s');
        $t = $renderer->embed_url($url);
        $this->assertContains('</iframe>', $t);
        $this->assertContains('list=PLxcO_MFWQBDcyn9xpbmx601YSDlDcTcr0', $t);
        $this->assertContains('start=65', $t);

                $url = new moodle_url('https://www.youtube.com/watch?v=dv2f_xfmbD8&index=4&list=PLxcO_">');
        $t = $renderer->embed_url($url);
        $this->assertContains('</iframe>', $t);
        $this->assertNotContains('list=PLxcO_', $t);         $url = new moodle_url('https://www.youtube.com/watch?v=JNJMF1l3udM&t=">');
        $t = $renderer->embed_url($url);
        $this->assertContains('</iframe>', $t);
        $this->assertNotContains('start=', $t); 
                $url = new moodle_url('http://www.youtube.com/view_play_list?p=PL6E18E2927047B662');
        $t = $renderer->embed_url($url);
        $this->assertContains('</iframe>', $t);
        $url = new moodle_url('http://www.youtube.com/playlist?list=PL6E18E2927047B662');
        $t = $renderer->embed_url($url);
        $this->assertContains('</iframe>', $t);
        $url = new moodle_url('http://www.youtube.com/p/PL6E18E2927047B662');
        $t = $renderer->embed_url($url);
        $this->assertContains('</iframe>', $t);

                $url = new moodle_url('http://vimeo.com/1176321');
        $t = $renderer->embed_url($url);
        $this->assertContains('</iframe>', $t);

                $this->pretend_to_be_firefox();
        $url = new moodle_url('http://example.org/test.ogg');
        $t = $renderer->embed_url($url);
        $this->assertContains('</audio>', $t);
    }

    
    public function test_slash_arguments() {
        global $CFG, $PAGE;

                        
                $CFG->core_media_enable_mp3 = true;
        $renderer = new core_media_renderer_test($PAGE, '');

                $url = new moodle_url('http://example.org/pluginfile.php?file=x/y/z/test.mp3');
        $t = $renderer->embed_url($url);
        $this->assertContains('core_media_mp3_', $t);
    }

    
    public function test_embed_or_blank() {
        global $CFG, $PAGE;
        $CFG->core_media_enable_html5audio = true;
        $this->pretend_to_be_firefox();

        $renderer = new core_media_renderer_test($PAGE, '');

        $options = array(core_media::OPTION_FALLBACK_TO_BLANK => true);

                $url = new moodle_url('http://example.org/test.ogg');
        $t = $renderer->embed_url($url, '', 0, 0, $options);
        $this->assertContains('</audio>', $t);
        $this->assertContains('mediafallbacklink', $t);

                $url = new moodle_url('http://example.org/test.mp4');
        $t = $renderer->embed_url($url, '', 0, 0, $options);
        $this->assertSame('', $t);
    }

    
    public function test_embed_url_size() {
        global $CFG, $PAGE;

                                        $CFG->core_media_enable_html5video = true;
        $renderer = new core_media_renderer_test($PAGE, '');
        $url = new moodle_url('http://example.org/test.mp4');

                $t = $renderer->embed_url($url);
        $this->assertContains('width="' . CORE_MEDIA_VIDEO_WIDTH . '"', $t);
        $this->assertNotContains('height', $t);

                $t = $renderer->embed_url($url, '', '666', '101');
        $this->assertContains('width="666"', $t);
        $this->assertContains('height="101"', $t);

                $url = new moodle_url('http://example.org/test.mp4?d=123x456');
        $t = $renderer->embed_url($url, '', '666', '101');
        $this->assertContains('width="123"', $t);
        $this->assertContains('height="456"', $t);
    }

    
    public function test_embed_url_name() {
        global $CFG, $PAGE;

                        $CFG->core_media_enable_html5video = true;
        $renderer = new core_media_renderer_test($PAGE, '');
        $url = new moodle_url('http://example.org/test.mp4');

                $t = $renderer->embed_url($url);
        $this->assertContains('title="test.mp4"', $t);

                $t = $renderer->embed_url($url, 'frog & toad');
        $this->assertContains('title="frog &amp; toad"', $t);
    }

    
    public function test_split_alternatives() {
                $mp4 = 'http://example.org/test.mp4';
        $result = core_media::split_alternatives($mp4, $w, $h);
        $this->assertEquals($mp4, $result[0]->out(false));

                $this->assertEquals(0, $w);
        $this->assertEquals(0, $h);

                $webm = 'http://example.org/test.webm';
        $result = core_media::split_alternatives("$mp4#$webm", $w, $h);
        $this->assertEquals($mp4, $result[0]->out(false));
        $this->assertEquals($webm, $result[1]->out(false));

                $size = 'd=400x280';
        $result = core_media::split_alternatives("$mp4#$webm#$size", $w, $h);
        $this->assertEquals($mp4, $result[0]->out(false));
        $this->assertEquals($webm, $result[1]->out(false));
        $this->assertEquals(400, $w);
        $this->assertEquals(280, $h);

                $result = core_media::split_alternatives("$mp4?d=1x1#$webm?$size", $w, $h);
        $this->assertEquals($mp4, $result[0]->out(false));
        $this->assertEquals($webm, $result[1]->out(false));
        $this->assertEquals(400, $w);
        $this->assertEquals(280, $h);
    }

    
    public function test_embed_alternatives() {
        global $PAGE, $CFG;

                
                $urls = array(
            new moodle_url('http://example.org/test.mp4'),
            new moodle_url('http://example.org/test.ogv'),
            new moodle_url('http://example.org/test.webm'),
            new moodle_url('http://example.org/test.flv'),
        );

                $CFG->core_media_enable_html5video = true;
        $CFG->core_media_enable_flv = true;
        $renderer = new core_media_renderer_test($PAGE, '');

                $t = $renderer->embed_alternatives($urls);

                $this->assertContains('<source src="http://example.org/test.mp4"', $t);
        $this->assertNotContains('<source src="http://example.org/test.ogv"', $t);
        $this->assertNotContains('<source src="http://example.org/test.webm"', $t);
        $this->assertNotContains('<source src="http://example.org/test.flv"', $t);

                        $this->assertTrue((bool)preg_match('~core_media_flv_.*<video~s', $t));

                $this->pretend_to_be_firefox();
        $t = $renderer->embed_alternatives($urls);

                $this->assertContains('<source src="http://example.org/test.mp4"', $t);
        $this->assertContains('<source src="http://example.org/test.ogv"', $t);
        $this->assertContains('<source src="http://example.org/test.webm"', $t);
        $this->assertNotContains('<source src="http://example.org/test.flv"', $t);
    }

    
    public static function string_urls($urls) {
        $out = array();
        foreach ($urls as $url) {
            $out[] = $url->out(false);
        }
        return implode(',', $out);
    }

    
    public static function string_options($options) {
        $out = '';
        foreach ($options as $key => $value) {
            if ($out) {
                $out .= ';';
            }
            $out .= "$key=$value";
        }
        return $out;
    }
}


class core_media_player_test extends core_media_player {
    
    public $ext;
    
    public $rank;
    
    public $num;

    
    public function __construct($num = 1, $rank = 13, $ext = array('tst', 'test')) {
        $this->ext = $ext;
        $this->rank = $rank;
        $this->num = $num;
    }

    public function embed($urls, $name, $width, $height, $options) {
        return $this->num . ':' . medialib_test::string_urls($urls) .
                ",$name,$width,$height,<!--FALLBACK-->," . medialib_test::string_options($options);
    }

    public function get_supported_extensions() {
        return $this->ext;
    }

    public function get_rank() {
        return $this->rank;
    }
}


class core_media_renderer_test extends core_media_renderer {
    
    public function get_players_test() {
        $players = $this->get_players();
        $out = '';
        foreach ($players as $player) {
            if ($out) {
                $out .= ', ';
            }
            $out .= str_replace('core_media_player_', '', get_class($player));
        }
        return $out;
    }
}
