<?php



defined('MOODLE_INTERNAL') || die();



class core_weblib_format_text_testcase extends advanced_testcase {

    public function test_format_text_format_html() {
        $this->resetAfterTest();
        filter_set_global_state('emoticon', TEXTFILTER_ON);
        $this->assertRegExp('~^<p><img class="emoticon" alt="smile" ([^>]+)></p>$~',
                format_text('<p>:-)</p>', FORMAT_HTML));
    }

    public function test_format_text_format_html_no_filters() {
        $this->resetAfterTest();
        filter_set_global_state('emoticon', TEXTFILTER_ON);
        $this->assertEquals('<p>:-)</p>',
                format_text('<p>:-)</p>', FORMAT_HTML, array('filter' => false)));
    }

    public function test_format_text_format_plain() {
                $this->resetAfterTest();
        filter_set_global_state('emoticon', TEXTFILTER_ON);
        $this->assertEquals(':-)',
                format_text(':-)', FORMAT_PLAIN));
    }

    public function test_format_text_format_plain_no_filters() {
        $this->resetAfterTest();
        filter_set_global_state('emoticon', TEXTFILTER_ON);
        $this->assertEquals(':-)',
                format_text(':-)', FORMAT_PLAIN, array('filter' => false)));
    }

    public function test_format_text_format_markdown() {
        $this->resetAfterTest();
        filter_set_global_state('emoticon', TEXTFILTER_ON);
        $this->assertRegExp('~^<p><em><img class="emoticon" alt="smile" ([^>]+)></em></p>\n$~',
                format_text('*:-)*', FORMAT_MARKDOWN));
    }

    public function test_format_text_format_markdown_nofilter() {
        $this->resetAfterTest();
        filter_set_global_state('emoticon', TEXTFILTER_ON);
        $this->assertEquals("<p><em>:-)</em></p>\n",
                format_text('*:-)*', FORMAT_MARKDOWN, array('filter' => false)));
    }

    public function test_format_text_format_moodle() {
        $this->resetAfterTest();
        filter_set_global_state('emoticon', TEXTFILTER_ON);
        $this->assertRegExp('~^<div class="text_to_html"><p><img class="emoticon" alt="smile" ([^>]+)></p></div>$~',
                format_text('<p>:-)</p>', FORMAT_MOODLE));
    }

    public function test_format_text_format_moodle_no_filters() {
        $this->resetAfterTest();
        filter_set_global_state('emoticon', TEXTFILTER_ON);
        $this->assertEquals('<div class="text_to_html"><p>:-)</p></div>',
                format_text('<p>:-)</p>', FORMAT_MOODLE, array('filter' => false)));
    }

    public function test_format_text_overflowdiv() {
        $this->assertEquals('<div class="no-overflow"><p>:-)</p></div>',
                format_text('<p>:-)</p>', FORMAT_HTML, array('overflowdiv' => true)));
    }

    
    public function test_format_text_blanktarget($link, $expected) {
        $actual = format_text($link, FORMAT_MOODLE, array('blanktarget' => true, 'filter' => false, 'noclean' => true));
        $this->assertEquals($expected, $actual);
    }

    
    public function format_text_blanktarget_testcases() {
        return [
            'Simple link' => [
                '<a href="https://www.youtube.com/watch?v=JeimE8Wz6e4">Hey, that\'s pretty good!</a>',
                '<div class="text_to_html"><a href="https://www.youtube.com/watch?v=JeimE8Wz6e4" target="_blank"' .
                    ' rel="noreferrer">Hey, that\'s pretty good!</a></div>'
            ],
            'Link with rel' => [
                '<a href="https://www.youtube.com/watch?v=JeimE8Wz6e4" rel="nofollow">Hey, that\'s pretty good!</a>',
                '<div class="text_to_html"><a href="https://www.youtube.com/watch?v=JeimE8Wz6e4" rel="nofollow noreferrer"' .
                    ' target="_blank">Hey, that\'s pretty good!</a></div>'
            ],
            'Link with rel noreferrer' => [
                '<a href="https://www.youtube.com/watch?v=JeimE8Wz6e4" rel="noreferrer">Hey, that\'s pretty good!</a>',
                '<div class="text_to_html"><a href="https://www.youtube.com/watch?v=JeimE8Wz6e4" rel="noreferrer"' .
                 ' target="_blank">Hey, that\'s pretty good!</a></div>'
            ],
            'Link with target' => [
                '<a href="https://www.youtube.com/watch?v=JeimE8Wz6e4" target="_self">Hey, that\'s pretty good!</a>',
                '<div class="text_to_html"><a href="https://www.youtube.com/watch?v=JeimE8Wz6e4" target="_self">' .
                    'Hey, that\'s pretty good!</a></div>'
            ],
            'Link with target blank' => [
                '<a href="https://www.youtube.com/watch?v=JeimE8Wz6e4" target="_blank">Hey, that\'s pretty good!</a>',
                '<div class="text_to_html"><a href="https://www.youtube.com/watch?v=JeimE8Wz6e4" target="_blank"' .
                    ' rel="noreferrer">Hey, that\'s pretty good!</a></div>'
            ],
            'Link with Frank\'s casket inscription' => [
                '<a href="https://en.wikipedia.org/wiki/Franks_Casket">ᚠᛁᛋᚳ᛫ᚠᛚᚩᛞᚢ᛫ᚪᚻᚩᚠᚩᚾᚠᛖᚱᚷ ᛖᚾᛒᛖᚱᛁᚷ ᚹᚪᚱᚦᚷᚪ᛬ᛋᚱᛁᚳᚷᚱᚩᚱᚾᚦᚫᚱᚻᛖᚩᚾᚷᚱᛖᚢᛏᚷᛁᛋᚹᚩᛗ ᚻ' .
                    'ᚱᚩᚾᚫᛋᛒᚪᚾ ᛗᚫᚷᛁᚠᛁᛋᚳ᛫ᚠᛚᚩᛞᚢ᛫ᚪᚻᚩᚠᚩᚾᚠᛖᚱᚷ ᛖᚾᛒᛖᚱᛁᚷ ᚹᚪᚱᚦᚷᚪ᛬ᛋᚱᛁᚳᚷᚱᚩᚱᚾᚦᚫᚱᚻᛖᚩᚾᚷᚱᛖᚢᛏᚷᛁᛋᚹᚩᛗ ᚻᚱᚩᚾᚫᛋᛒᚪᚾ ᛗᚫᚷᛁ</a>',
                '<div class="text_to_html"><a href="https://en.wikipedia.org/wiki/Franks_Casket" target="_blank" ' .
                    'rel="noreferrer">ᚠᛁᛋᚳ᛫ᚠᛚᚩᛞᚢ᛫ᚪᚻᚩᚠᚩᚾᚠᛖᚱᚷ ᛖᚾᛒᛖᚱᛁᚷ ᚹᚪᚱᚦᚷᚪ᛬ᛋᚱᛁᚳᚷᚱᚩᚱᚾᚦᚫᚱᚻᛖᚩᚾᚷᚱᛖᚢᛏᚷᛁᛋᚹᚩᛗ ᚻᚱᚩᚾᚫᛋᛒᚪᚾ ᛗᚫᚷᛁᚠᛁᛋᚳ᛫ᚠᛚᚩᛞᚢ᛫ᚪᚻᚩᚠᚩᚾᚠᛖᚱᚷ ᛖᚾ' .
                    'ᛒᛖᚱᛁᚷ ᚹᚪᚱᚦᚷᚪ᛬ᛋᚱᛁᚳᚷᚱᚩᚱᚾᚦᚫᚱᚻᛖᚩᚾᚷᚱᛖᚢᛏᚷᛁᛋᚹᚩᛗ ᚻᚱᚩᚾᚫᛋᛒᚪᚾ ᛗᚫᚷᛁ</a></div>'
             ],
            'No link' => [
                'Some very boring text written with the Latin script',
                '<div class="text_to_html">Some very boring text written with the Latin script</div>'
            ],
            'No link with Thror\'s map runes' => [
                'ᛋᛏᚫᚾᛞ ᛒᚣ ᚦᛖ ᚷᚱᛖᚣ ᛋᛏᚩᚾᛖ ᚻᚹᛁᛚᛖ ᚦᛖ ᚦᚱᚢᛋᚻ ᚾᚩᚳᛋ ᚫᚾᛞ ᚦᛖ ᛋᛖᛏᛏᛁᚾᚷ ᛋᚢᚾ ᚹᛁᚦ ᚦᛖ ᛚᚫᛋᛏ ᛚᛁᚷᚻᛏ ᚩᚠ ᛞᚢᚱᛁᚾᛋ ᛞᚫᚣ ᚹᛁᛚᛚ ᛋᚻᛁᚾᛖ ᚢᛈᚩᚾ ᚦᛖ ᚳᛖᚣᚻᚩᛚᛖ',
                '<div class="text_to_html">ᛋᛏᚫᚾᛞ ᛒᚣ ᚦᛖ ᚷᚱᛖᚣ ᛋᛏᚩᚾᛖ ᚻᚹᛁᛚᛖ ᚦᛖ ᚦᚱᚢᛋᚻ ᚾᚩᚳᛋ ᚫᚾᛞ ᚦᛖ ᛋᛖᛏᛏᛁᚾᚷ ᛋᚢᚾ ᚹᛁᚦ ᚦᛖ ᛚᚫᛋᛏ ᛚᛁᚷᚻᛏ ᚩᚠ ᛞᚢᚱᛁᚾᛋ ᛞᚫᚣ ᚹ' .
                'ᛁᛚᛚ ᛋᚻᛁᚾᛖ ᚢᛈᚩᚾ ᚦᛖ ᚳᛖᚣᚻᚩᛚᛖ</div>'
            ]
        ];
    }
}
