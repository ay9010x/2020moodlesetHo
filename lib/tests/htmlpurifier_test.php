<?php



defined('MOODLE_INTERNAL') || die();



class core_htmlpurifier_testcase extends basic_testcase {

    
    public function test_allow_blank_target() {
                                $text = '<a href="http://moodle.org" target="_blank">Some link</a>';
        $expected = '<a href="http://moodle.org" target="_blank" rel="noreferrer">Some link</a>';
        $result = format_text($text, FORMAT_HTML);
        $this->assertSame($expected, $result);

        $result = format_text('<a href="http://moodle.org" target="some">Some link</a>', FORMAT_HTML);
        $this->assertSame('<a href="http://moodle.org">Some link</a>', $result);
    }

    
    public function test_nolink() {
                $text = '<nolink><div>no filters</div></nolink>';
        $result = purify_html($text, array());
        $this->assertSame($text, $result);

        $text = '<nolink>xxx<em>xx</em><div>xxx</div></nolink>';
        $result = purify_html($text, array());
        $this->assertSame($text, $result);
    }

    
    public function test_tex() {
        $text = '<tex>a+b=c</tex>';
        $result = purify_html($text, array());
        $this->assertSame($text, $result);
    }

    
    public function test_algebra() {
        $text = '<algebra>a+b=c</algebra>';
        $result = purify_html($text, array());
        $this->assertSame($text, $result);
    }

    
    public function test_multilang() {
        $text = '<lang lang="en">hmmm</lang><lang lang="anything">hm</lang>';
        $result = purify_html($text, array());
        $this->assertSame($text, $result);

        $text = '<span lang="en" class="multilang">hmmm</span><span lang="anything" class="multilang">hm</span>';
        $result = purify_html($text, array());
        $this->assertSame($text, $result);

        $text = '<span lang="en">hmmm</span>';
        $result = purify_html($text, array());
        $this->assertNotSame($text, $result);

        
        $text = '<span lang="de_DU" class="multilang">asas</span>';
        $result = purify_html($text, array());
        $this->assertSame($text, $result);

        $text = '<lang lang="de_DU">xxxxxx</lang>';
        $result = purify_html($text, array());
        $this->assertSame($text, $result);
    }

    
    public function test_format_text_allowid() {
                $options = array(
            'nocache' => true
        );
        $result = format_text('<div id="example">Frog</div>', FORMAT_HTML, $options);
        $this->assertSame('<div>Frog</div>', $result);

                $options['allowid'] = true;
        $result = format_text('<div id="example">Frog</div>', FORMAT_HTML, $options);
        $this->assertSame('<div id="example">Frog</div>', $result);
    }

    public function test_allowobjectembed() {
        global $CFG;

        $this->assertSame('0', $CFG->allowobjectembed);

        $text = '<object width="425" height="350">
<param name="movie" value="http://www.youtube.com/v/AyPzM5WK8ys" />
<param name="wmode" value="transparent" />
<embed src="http://www.youtube.com/v/AyPzM5WK8ys" type="application/x-shockwave-flash" wmode="transparent" width="425" height="350" />
</object>hmmm';
        $result = purify_html($text, array());
        $this->assertSame('hmmm', trim($result));

        $CFG->allowobjectembed = '1';

        $expected = '<object width="425" height="350" data="http://www.youtube.com/v/AyPzM5WK8ys" type="application/x-shockwave-flash">
<param name="allowScriptAccess" value="never" />
<param name="allowNetworking" value="internal" />
<param name="movie" value="http://www.youtube.com/v/AyPzM5WK8ys" />
<param name="wmode" value="transparent" />
<embed src="http://www.youtube.com/v/AyPzM5WK8ys" type="application/x-shockwave-flash" wmode="transparent" width="425" height="350" allowscriptaccess="never" allownetworking="internal" />
</object>hmmm';
        $result = purify_html($text, array());
        $this->assertSame(str_replace("\n", '', $expected), str_replace("\n", '', $result));

        $CFG->allowobjectembed = '0';

        $result = purify_html($text, array());
        $this->assertSame('hmmm', trim($result));
    }

    
    public function test_line_breaking() {
        $text = "\n\raa\rsss\nsss\r";
        $this->assertSame($text, purify_html($text));
    }

    
    public function test_tidy() {
        $text = "<p>xx";
        $this->assertSame('<p>xx</p>', purify_html($text));

        $text = "<P>xx</P>";
        $this->assertSame('<p>xx</p>', purify_html($text));

        $text = "xx<br>";
        $this->assertSame('xx<br />', purify_html($text));
    }

    
    public function test_nested_lists() {
        $text = "<ul><li>One<ul><li>Two</li></ul></li><li>Three</li></ul>";
        $this->assertSame($text, purify_html($text));
    }

    
    public function test_cleaning_nastiness() {
        $text = "x<SCRIPT>alert('XSS')</SCRIPT>x";
        $this->assertSame('xx', purify_html($text));

        $text = '<DIV STYLE="background-image:url(javascript:alert(\'XSS\'))">xx</DIV>';
        $this->assertSame('<div>xx</div>', purify_html($text));

        $text = '<DIV STYLE="width:expression(alert(\'XSS\'));">xx</DIV>';
        $this->assertSame('<div>xx</div>', purify_html($text));

        $text = 'x<IFRAME SRC="javascript:alert(\'XSS\');"></IFRAME>x';
        $this->assertSame('xx', purify_html($text));

        $text = 'x<OBJECT TYPE="text/x-scriptlet" DATA="http://ha.ckers.org/scriptlet.html"></OBJECT>x';
        $this->assertSame('xx', purify_html($text));

        $text = 'x<EMBED SRC="http://ha.ckers.org/xss.swf" AllowScriptAccess="always"></EMBED>x';
        $this->assertSame('xx', purify_html($text));

        $text = 'x<form></form>x';
        $this->assertSame('xx', purify_html($text));
    }

    
    public function test_is_purify_html_necessary() {
                $text = "";
        $this->assertFalse(is_purify_html_necessary($text));
        $this->assertSame($text, purify_html($text));

        $text = "666";
        $this->assertFalse(is_purify_html_necessary($text));
        $this->assertSame($text, purify_html($text));

        $text = "abc\ndef \" ' ";
        $this->assertFalse(is_purify_html_necessary($text));
        $this->assertSame($text, purify_html($text));

        $text = "abc\n<p>def</p>efg<p>hij</p>";
        $this->assertFalse(is_purify_html_necessary($text));
        $this->assertSame($text, purify_html($text));

        $text = "<br />abc\n<p>def<em>efg</em><strong>hi<br />j</strong></p>";
        $this->assertFalse(is_purify_html_necessary($text));
        $this->assertSame($text, purify_html($text));

                $text = "&nbsp;";
        $this->assertTrue(is_purify_html_necessary($text));

        $text = "Gin & Tonic";
        $this->assertTrue(is_purify_html_necessary($text));

        $text = "Gin > Tonic";
        $this->assertTrue(is_purify_html_necessary($text));

        $text = "Gin < Tonic";
        $this->assertTrue(is_purify_html_necessary($text));

        $text = "<div>abc</div>";
        $this->assertTrue(is_purify_html_necessary($text));

        $text = "<span>abc</span>";
        $this->assertTrue(is_purify_html_necessary($text));

        $text = "<br>abc";
        $this->assertTrue(is_purify_html_necessary($text));

        $text = "<p class='xxx'>abc</p>";
        $this->assertTrue(is_purify_html_necessary($text));

        $text = "<p>abc<em></p></em>";
        $this->assertTrue(is_purify_html_necessary($text));

        $text = "<p>abc";
        $this->assertTrue(is_purify_html_necessary($text));
    }

    public function test_allowed_schemes() {
                $text = '<a href="http://www.example.com/course/view.php?id=5">link</a>';
        $this->assertSame($text, purify_html($text));

        $text = '<a href="https://www.example.com/course/view.php?id=5">link</a>';
        $this->assertSame($text, purify_html($text));

        $text = '<a href="ftp://user@ftp.example.com/some/file.txt">link</a>';
        $this->assertSame($text, purify_html($text));

        $text = '<a href="nntp://example.com/group/123">link</a>';
        $this->assertSame($text, purify_html($text));

        $text = '<a href="news:groupname">link</a>';
        $this->assertSame($text, purify_html($text));

        $text = '<a href="mailto:user@example.com">link</a>';
        $this->assertSame($text, purify_html($text));

                $text = '<a href="irc://irc.example.com/3213?pass">link</a>';
        $this->assertSame($text, purify_html($text));

        $text = '<a href="rtsp://www.example.com/movie.mov">link</a>';
        $this->assertSame($text, purify_html($text));

        $text = '<a href="rtmp://www.example.com/video.f4v">link</a>';
        $this->assertSame($text, purify_html($text));

        $text = '<a href="teamspeak://speak.example.com/?par=val?par2=val2">link</a>';
        $this->assertSame($text, purify_html($text));

        $text = '<a href="gopher://gopher.example.com/resource">link</a>';
        $this->assertSame($text, purify_html($text));

        $text = '<a href="mms://www.example.com/movie.mms">link</a>';
        $this->assertSame($text, purify_html($text));

                $text = '<a href="javascript://www.example.com">link</a>';
        $this->assertSame('<a>link</a>', purify_html($text));

        $text = '<a href="hmmm://www.example.com">link</a>';
        $this->assertSame('<a>link</a>', purify_html($text));
    }

    
    public function test_media_tags($mediatag, $expected) {
        $actual = format_text($mediatag, FORMAT_MOODLE, ['filter' => false]);
        $this->assertEquals($expected, $actual);
    }

    
    public function media_tags_provider() {
                $generatetestcases = function($prefix, array $attrs, array $templates) {
            return array_reduce($attrs, function($carry, $attr) use ($prefix, $templates) {
                $testcase = [$prefix . '/' . $attr => [
                    sprintf($templates[0], $attr),
                    sprintf($templates[1], $attr)
                ]];
                return empty(array_values($carry)[0]) ? $testcase : $carry + $testcase;
            }, [[]]);
        };

        $audioattrs = [
            'preload="auto"', 'autoplay=""', 'loop=""', 'muted=""', 'controls=""',
            'crossorigin="anonymous"', 'crossorigin="use-credentials"'
        ];
        $videoattrs = [
            'crossorigin="anonymous"', 'crossorigin="use-credentials"',
            'poster="https://upload.wikimedia.org/wikipedia/en/1/14/Space_jam.jpg"',
            'preload="auto"', 'autoplay=""', 'playsinline=""', 'loop=""', 'muted=""',
            'controls=""', 'width="420"', 'height="69"'
        ];
        return $generatetestcases('Plain audio', $audioattrs + ['src="http://example.com/jam.wav"'], [
                '<audio %1$s>Looks like you can\'t slam the jams.</audio>',
                '<div class="text_to_html"><audio %1$s>Looks like you can\'t slam the jams.</audio></div>'
            ]) + $generatetestcases('Audio with one source', $audioattrs, [
                '<audio %1$s><source src="http://example.com/getup.wav">No tasty jams for you.</audio>',
                '<div class="text_to_html">' .
                    '<audio %1$s>' .
                        '<source src="http://example.com/getup.wav" />' .
                        'No tasty jams for you.' .
                    '</audio>' .
                '</div>'
            ]) + $generatetestcases('Audio with multiple sources', $audioattrs, [
                '<audio %1$s>' .
                    '<source src="http://example.com/getup.wav" type="audio/wav">' .
                    '<source src="http://example.com/getup.mp3" type="audio/mpeg">' .
                    '<source src="http://example.com/getup.ogg" type="audio/ogg">' .
                    'No tasty jams for you.' .
                '</audio>',
                '<div class="text_to_html">' .
                    '<audio %1$s>' .
                        '<source src="http://example.com/getup.wav" type="audio/wav" />' .
                        '<source src="http://example.com/getup.mp3" type="audio/mpeg" />' .
                        '<source src="http://example.com/getup.ogg" type="audio/ogg" />' .
                        'No tasty jams for you.' .
                    '</audio>' .
                '</div>'
            ]) + $generatetestcases('Audio with sources and tracks', $audioattrs, [
                '<audio %1$s>' .
                    '<source src="http://example.com/getup.wav" type="audio/wav">' .
                    '<track kind="subtitles" src="http://example.com/subtitles_en.vtt" label="English" srclang="en">' .
                    '<track kind="subtitles" src="http://example.com/subtitles_es.vtt" label="Espanol" srclang="es">' .
                    'No tasty jams for you.' .
                '</audio>',
                '<div class="text_to_html">' .
                    '<audio %1$s>' .
                        '<source src="http://example.com/getup.wav" type="audio/wav" />' .
                        '<track kind="subtitles" src="http://example.com/subtitles_en.vtt" label="English" srclang="en" />' .
                        '<track kind="subtitles" src="http://example.com/subtitles_es.vtt" label="Espanol" srclang="es" />' .
                        'No tasty jams for you.' .
                    '</audio>' .
                '</div>'
            ]) + $generatetestcases('Plain video', $videoattrs + ['src="http://example.com/prettygood.mp4'], [
                '<video %1$s>Oh, that\'s pretty bad 😦</video>',
                '<div class="text_to_html"><video %1$s>Oh, that\'s pretty bad 😦</video></div>'
            ]) + $generatetestcases('Video with illegal subtag', $videoattrs + ['src="http://example.com/prettygood.mp4'], [
                '<video %1$s><subtag></subtag>Oh, that\'s pretty bad 😦</video>',
                '<div class="text_to_html"><video %1$s>Oh, that\'s pretty bad 😦</video></div>'
            ]) + $generatetestcases('Video with legal subtag', $videoattrs + ['src="http://example.com/prettygood.mp4'], [
                '<video %1$s>Did not work <a href="http://example.com/prettygood.mp4">click here to download</a></video>',
                '<div class="text_to_html"><video %1$s>Did not work <a href="http://example.com/prettygood.mp4">' .
                'click here to download</a></video></div>'
            ]) + $generatetestcases('Source tag without video or audio', $videoattrs, [
                'some text <source src="http://example.com/getup.wav" type="audio/wav"> the end',
                '<div class="text_to_html">some text  the end</div>'
            ]) + $generatetestcases('Video with one source', $videoattrs, [
                '<video %1$s><source src="http://example.com/prettygood.mp4">Oh, that\'s pretty bad 😦</video>',
                '<div class="text_to_html">' .
                    '<video %1$s>' .
                        '<source src="http://example.com/prettygood.mp4" />' .
                        'Oh, that\'s pretty bad 😦' .
                    '</video>' .
                '</div>'
            ]) + $generatetestcases('Video with multiple sources', $videoattrs, [
                '<video %1$s>' .
                    '<source src="http://example.com/prettygood.mp4" type="video/mp4">' .
                    '<source src="http://example.com/eljefe.mp4" type="video/mp4">' .
                    '<source src="http://example.com/turnitup.mov" type="video/mov">' .
                    'Oh, that\'s pretty bad 😦' .
                '</video>',
                '<div class="text_to_html">' .
                    '<video %1$s>' .
                        '<source src="http://example.com/prettygood.mp4" type="video/mp4" />' .
                        '<source src="http://example.com/eljefe.mp4" type="video/mp4" />' .
                        '<source src="http://example.com/turnitup.mov" type="video/mov" />' .
                        'Oh, that\'s pretty bad 😦' .
                    '</video>' .
                '</div>'
            ]) + $generatetestcases('Video with sources and tracks', $audioattrs, [
                '<video %1$s>' .
                    '<source src="http://example.com/getup.wav" type="audio/wav">' .
                    '<track kind="subtitles" src="http://example.com/subtitles_en.vtt" label="English" srclang="en">' .
                    '<track kind="subtitles" src="http://example.com/subtitles_es.vtt" label="Espanol" srclang="es">' .
                    'No tasty jams for you.' .
                '</video>',
                '<div class="text_to_html">' .
                    '<video %1$s>' .
                        '<source src="http://example.com/getup.wav" type="audio/wav" />' .
                        '<track kind="subtitles" src="http://example.com/subtitles_en.vtt" label="English" srclang="en" />' .
                        '<track kind="subtitles" src="http://example.com/subtitles_es.vtt" label="Espanol" srclang="es" />' .
                    'No tasty jams for you.' .
                    '</video>' .
                '</div>'
            ]) + ['Video with invalid crossorigin' => [
                    '<video src="http://example.com/turnitup.mov" crossorigin="can i pls hab?">' .
                        'Oh, that\'s pretty bad 😦' .
                    '</video>',
                    '<div class="text_to_html">' .
                        '<video src="http://example.com/turnitup.mov">' .
                           'Oh, that\'s pretty bad 😦' .
                        '</video>' .
                    '</div>'
            ]] + ['Audio with invalid crossorigin' => [
                    '<audio src="http://example.com/getup.wav" crossorigin="give me. the jams.">' .
                        'nyemnyemnyem' .
                    '</audio>',
                    '<div class="text_to_html">' .
                        '<audio src="http://example.com/getup.wav">' .
                            'nyemnyemnyem' .
                        '</audio>' .
                    '</div>'
            ]] + ['Other attributes' => [
                '<video src="http://example.com/turnitdown.mov" class="nofilter" data-something="data attribute" someattribute="somevalue" onclick="boom">' .
                    '<source src="http://example.com/getup.wav" type="audio/wav" class="shouldberemoved" data-sourcedata="source data" onmouseover="kill session" />' .
                    '<track src="http://example.com/subtitles_en.vtt" class="shouldberemoved" data-trackdata="track data" onmouseover="removeme" />' .
                    'Do not remove attribute class but remove other attributes' .
                '</video>',
                '<div class="text_to_html">' .
                    '<video src="http://example.com/turnitdown.mov" class="nofilter">' .
                        '<source src="http://example.com/getup.wav" type="audio/wav" />' .
                        '<track src="http://example.com/subtitles_en.vtt" />' .
                        'Do not remove attribute class but remove other attributes' .
                    '</video>' .
                '</div>'
            ]];
    }
}
