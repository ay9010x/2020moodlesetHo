<?php



defined('MOODLE_INTERNAL') || die();



class core_text_testcase extends advanced_testcase {

    
    public function test_parse_charset() {
        $this->assertSame('windows-1250', core_text::parse_charset('Cp1250'));
                $this->assertSame('windows-1252', core_text::parse_charset('ms-ansi'));
    }

    
    public function test_convert() {
        $utf8 = "Žluťoučký koníček";
        $iso2 = pack("H*", "ae6c75bb6f75e86bfd206b6f6eede8656b");
        $win  = pack("H*", "8e6c759d6f75e86bfd206b6f6eede8656b");
        $this->assertSame($iso2, core_text::convert($utf8, 'utf-8', 'iso-8859-2'));
        $this->assertSame($utf8, core_text::convert($iso2, 'iso-8859-2', 'utf-8'));
        $this->assertSame($win, core_text::convert($utf8, 'utf-8', 'win-1250'));
        $this->assertSame($utf8, core_text::convert($win, 'win-1250', 'utf-8'));
        $this->assertSame($iso2, core_text::convert($win, 'win-1250', 'iso-8859-2'));
        $this->assertSame($win, core_text::convert($iso2, 'iso-8859-2', 'win-1250'));
        $this->assertSame($iso2, core_text::convert($iso2, 'iso-8859-2', 'iso-8859-2'));
        $this->assertSame($win, core_text::convert($win, 'win-1250', 'cp1250'));
        $this->assertSame($utf8, core_text::convert($utf8, 'utf-8', 'utf-8'));

        $utf8 = '言語設定';
        $str = pack("H*", "b8c0b8ecc0dfc4ea");         $this->assertSame($str, core_text::convert($utf8, 'utf-8', 'EUC-JP'));
        $this->assertSame($utf8, core_text::convert($str, 'EUC-JP', 'utf-8'));
        $this->assertSame($utf8, core_text::convert($utf8, 'utf-8', 'utf-8'));

        $str = pack("H*", "1b24423840386c405f446a1b2842");         $this->assertSame($str, core_text::convert($utf8, 'utf-8', 'ISO-2022-JP'));
        $this->assertSame($utf8, core_text::convert($str, 'ISO-2022-JP', 'utf-8'));
        $this->assertSame($utf8, core_text::convert($utf8, 'utf-8', 'utf-8'));

        $str = pack("H*", "8cbe8cea90dd92e8");         $this->assertSame($str, core_text::convert($utf8, 'utf-8', 'SHIFT-JIS'));
        $this->assertSame($utf8, core_text::convert($str, 'SHIFT-JIS', 'utf-8'));
        $this->assertSame($utf8, core_text::convert($utf8, 'utf-8', 'utf-8'));

        $utf8 = '简体中文';
        $str = pack("H*", "bcf2cce5d6d0cec4");         $this->assertSame($str, core_text::convert($utf8, 'utf-8', 'GB2312'));
        $this->assertSame($utf8, core_text::convert($str, 'GB2312', 'utf-8'));
        $this->assertSame($utf8, core_text::convert($utf8, 'utf-8', 'utf-8'));

        $str = pack("H*", "bcf2cce5d6d0cec4");         $this->assertSame($str, core_text::convert($utf8, 'utf-8', 'GB18030'));
        $this->assertSame($utf8, core_text::convert($str, 'GB18030', 'utf-8'));
        $this->assertSame($utf8, core_text::convert($utf8, 'utf-8', 'utf-8'));

        $utf8 = "Žluťoučký koníček";
        $this->assertSame('Zlutoucky konicek', core_text::convert($utf8, 'utf-8', 'ascii'));
        $this->assertSame($utf8, core_text::convert($utf8.chr(130), 'utf-8', 'utf-8'));
        $utf8 = "Der eine stößt den Speer zum Mann";
        $this->assertSame('Der eine stoesst den Speer zum Mann', core_text::convert($utf8, 'utf-8', 'ascii'));
        $iso1 = core_text::convert($utf8, 'utf-8', 'iso-8859-1');
        $this->assertSame('Der eine stoesst den Speer zum Mann', core_text::convert($iso1, 'iso-8859-1', 'ascii'));
    }

    
    public function test_substr() {
        $str = "Žluťoučký koníček";
        $this->assertSame($str, core_text::substr($str, 0));
        $this->assertSame('luťoučký koníček', core_text::substr($str, 1));
        $this->assertSame('luť', core_text::substr($str, 1, 3));
        $this->assertSame($str, core_text::substr($str, 0, 100));
        $this->assertSame('če', core_text::substr($str, -3, 2));

        $iso2 = pack("H*", "ae6c75bb6f75e86bfd206b6f6eede8656b");
        $this->assertSame(core_text::convert('luť', 'utf-8', 'iso-8859-2'), core_text::substr($iso2, 1, 3, 'iso-8859-2'));
        $this->assertSame(core_text::convert($str, 'utf-8', 'iso-8859-2'), core_text::substr($iso2, 0, 100, 'iso-8859-2'));
        $this->assertSame(core_text::convert('če', 'utf-8', 'iso-8859-2'), core_text::substr($iso2, -3, 2, 'iso-8859-2'));

        $win  = pack("H*", "8e6c759d6f75e86bfd206b6f6eede8656b");
        $this->assertSame(core_text::convert('luť', 'utf-8', 'cp1250'), core_text::substr($win, 1, 3, 'cp1250'));
        $this->assertSame(core_text::convert($str, 'utf-8', 'cp1250'), core_text::substr($win, 0, 100, 'cp1250'));
        $this->assertSame(core_text::convert('če', 'utf-8', 'cp1250'), core_text::substr($win, -3, 2, 'cp1250'));

        $str = pack("H*", "b8c0b8ecc0dfc4ea");         $s = pack("H*", "b8ec");         $this->assertSame($s, core_text::substr($str, 1, 1, 'EUC-JP'));

        $str = pack("H*", "1b24423840386c405f446a1b2842");         $s = pack("H*", "1b2442386c1b2842");         $this->assertSame($s, core_text::substr($str, 1, 1, 'ISO-2022-JP'));

        $str = pack("H*", "8cbe8cea90dd92e8");         $s = pack("H*", "8cea");         $this->assertSame($s, core_text::substr($str, 1, 1, 'SHIFT-JIS'));

        $str = pack("H*", "bcf2cce5d6d0cec4");         $s = pack("H*", "cce5");         $this->assertSame($s, core_text::substr($str, 1, 1, 'GB2312'));

        $str = pack("H*", "bcf2cce5d6d0cec4");         $s = pack("H*", "cce5");         $this->assertSame($s, core_text::substr($str, 1, 1, 'GB18030'));
    }

    
    public function test_strlen() {
        $str = "Žluťoučký koníček";
        $this->assertSame(17, core_text::strlen($str));

        $iso2 = pack("H*", "ae6c75bb6f75e86bfd206b6f6eede8656b");
        $this->assertSame(17, core_text::strlen($iso2, 'iso-8859-2'));

        $win  = pack("H*", "8e6c759d6f75e86bfd206b6f6eede8656b");
        $this->assertSame(17, core_text::strlen($win, 'cp1250'));

        $str = pack("H*", "b8ec");         $this->assertSame(1, core_text::strlen($str, 'EUC-JP'));
        $str = pack("H*", "b8c0b8ecc0dfc4ea");         $this->assertSame(4, core_text::strlen($str, 'EUC-JP'));

        $str = pack("H*", "1b2442386c1b2842");         $this->assertSame(1, core_text::strlen($str, 'ISO-2022-JP'));
        $str = pack("H*", "1b24423840386c405f446a1b2842");         $this->assertSame(4, core_text::strlen($str, 'ISO-2022-JP'));

        $str = pack("H*", "8cea");         $this->assertSame(1, core_text::strlen($str, 'SHIFT-JIS'));
        $str = pack("H*", "8cbe8cea90dd92e8");         $this->assertSame(4, core_text::strlen($str, 'SHIFT-JIS'));

        $str = pack("H*", "cce5");         $this->assertSame(1, core_text::strlen($str, 'GB2312'));
        $str = pack("H*", "bcf2cce5d6d0cec4");         $this->assertSame(4, core_text::strlen($str, 'GB2312'));

        $str = pack("H*", "cce5");         $this->assertSame(1, core_text::strlen($str, 'GB18030'));
        $str = pack("H*", "bcf2cce5d6d0cec4");         $this->assertSame(4, core_text::strlen($str, 'GB18030'));
    }

    
    public function test_str_max_bytes() {
                $str = '言語設定';

        $this->assertEquals(12, strlen($str));

                $conv = core_text::str_max_bytes($str, 12);
        $this->assertEquals(12, strlen($conv));
        $this->assertSame('言語設定', $conv);
        $conv = core_text::str_max_bytes($str, 11);
        $this->assertEquals(9, strlen($conv));
        $this->assertSame('言語設', $conv);
        $conv = core_text::str_max_bytes($str, 10);
        $this->assertEquals(9, strlen($conv));
        $this->assertSame('言語設', $conv);
        $conv = core_text::str_max_bytes($str, 9);
        $this->assertEquals(9, strlen($conv));
        $this->assertSame('言語設', $conv);
        $conv = core_text::str_max_bytes($str, 8);
        $this->assertEquals(6, strlen($conv));
        $this->assertSame('言語', $conv);

                $str = '言語設a定';

        $this->assertEquals(13, strlen($str));

        $conv = core_text::str_max_bytes($str, 11);
        $this->assertEquals(10, strlen($conv));
        $this->assertSame('言語設a', $conv);
        $conv = core_text::str_max_bytes($str, 10);
        $this->assertEquals(10, strlen($conv));
        $this->assertSame('言語設a', $conv);
        $conv = core_text::str_max_bytes($str, 9);
        $this->assertEquals(9, strlen($conv));
        $this->assertSame('言語設', $conv);
        $conv = core_text::str_max_bytes($str, 8);
        $this->assertEquals(6, strlen($conv));
        $this->assertSame('言語', $conv);

                $conv = core_text::str_max_bytes($str, 0);
        $this->assertEquals(0, strlen($conv));
        $this->assertSame('', $conv);
    }

    
    public function test_strtolower() {
        $str = "Žluťoučký koníček";
        $low = 'žluťoučký koníček';
        $this->assertSame($low, core_text::strtolower($str));

        $iso2 = pack("H*", "ae6c75bb6f75e86bfd206b6f6eede8656b");
        $this->assertSame(core_text::convert($low, 'utf-8', 'iso-8859-2'), core_text::strtolower($iso2, 'iso-8859-2'));

        $win  = pack("H*", "8e6c759d6f75e86bfd206b6f6eede8656b");
        $this->assertSame(core_text::convert($low, 'utf-8', 'cp1250'), core_text::strtolower($win, 'cp1250'));

        $str = '言語設定';
        $this->assertSame($str, core_text::strtolower($str));

        $str = '简体中文';
        $this->assertSame($str, core_text::strtolower($str));

        $str = pack("H*", "1b24423840386c405f446a1b2842");         $this->assertSame($str, core_text::strtolower($str, 'ISO-2022-JP'));

        $str = pack("H*", "8cbe8cea90dd92e8");         $this->assertSame($str, core_text::strtolower($str, 'SHIFT-JIS'));

        $str = pack("H*", "bcf2cce5d6d0cec4");         $this->assertSame($str, core_text::strtolower($str, 'GB2312'));

        $str = pack("H*", "bcf2cce5d6d0cec4");         $this->assertSame($str, core_text::strtolower($str, 'GB18030'));

                $str = 1309528800;
        $this->assertSame((string)$str, core_text::strtolower($str));
    }

    
    public function test_strtoupper() {
        $str = "Žluťoučký koníček";
        $up  = 'ŽLUŤOUČKÝ KONÍČEK';
        $this->assertSame($up, core_text::strtoupper($str));

        $iso2 = pack("H*", "ae6c75bb6f75e86bfd206b6f6eede8656b");
        $this->assertSame(core_text::convert($up, 'utf-8', 'iso-8859-2'), core_text::strtoupper($iso2, 'iso-8859-2'));

        $win  = pack("H*", "8e6c759d6f75e86bfd206b6f6eede8656b");
        $this->assertSame(core_text::convert($up, 'utf-8', 'cp1250'), core_text::strtoupper($win, 'cp1250'));

        $str = '言語設定';
        $this->assertSame($str, core_text::strtoupper($str));

        $str = '简体中文';
        $this->assertSame($str, core_text::strtoupper($str));

        $str = pack("H*", "1b24423840386c405f446a1b2842");         $this->assertSame($str, core_text::strtoupper($str, 'ISO-2022-JP'));

        $str = pack("H*", "8cbe8cea90dd92e8");         $this->assertSame($str, core_text::strtoupper($str, 'SHIFT-JIS'));

        $str = pack("H*", "bcf2cce5d6d0cec4");         $this->assertSame($str, core_text::strtoupper($str, 'GB2312'));

        $str = pack("H*", "bcf2cce5d6d0cec4");         $this->assertSame($str, core_text::strtoupper($str, 'GB18030'));
    }

    
    public function test_strrev() {
        $strings = array(
            "Žluťoučký koníček" => "kečínok ýkčuoťulŽ",
            'ŽLUŤOUČKÝ KONÍČEK' => "KEČÍNOK ÝKČUOŤULŽ",
            '言語設定' => '定設語言',
            '简体中文' => '文中体简',
            "Der eine stößt den Speer zum Mann" => "nnaM muz reepS ned tßöts enie reD"
        );
        foreach ($strings as $before => $after) {
                        $this->assertSame($after, core_text::strrev($before));
            $this->assertSame($before, core_text::strrev($after));
                        $this->assertSame($after, core_text::strrev(core_text::strrev($after)));
        }
    }

    
    public function test_strpos() {
        $str = "Žluťoučký koníček";
        $this->assertSame(10, core_text::strpos($str, 'koníč'));
    }

    
    public function test_strrpos() {
        $str = "Žluťoučký koníček";
        $this->assertSame(11, core_text::strrpos($str, 'o'));
    }

    
    public function test_specialtoascii() {
        $str = "Žluťoučký koníček";
        $this->assertSame('Zlutoucky konicek', core_text::specialtoascii($str));
    }

    
    public function test_encode_mimeheader() {
        global $CFG;
        require_once($CFG->libdir.'/phpmailer/moodle_phpmailer.php');
        $mailer = new moodle_phpmailer();

                $str = "Žluťoučký koníček";
        $encodedstr = '=?utf-8?B?xb1sdcWlb3XEjWvDvSBrb27DrcSNZWs=?=';
        $this->assertSame($encodedstr, core_text::encode_mimeheader($str));
        $this->assertSame($encodedstr, $mailer->encodeHeader($str));
        $this->assertSame('"' . $encodedstr . '"', $mailer->encodeHeader($str, 'phrase'));

                $latinstr = 'text"with quotes';
        $this->assertSame($latinstr, core_text::encode_mimeheader($latinstr));
        $this->assertSame($latinstr, $mailer->encodeHeader($latinstr));
        $this->assertSame('"text\\"with quotes"', $mailer->encodeHeader($latinstr, 'phrase'));

                $longlatinstr = 'This is a very long text that still should not be split into several lines in the email headers because '.
            'it does not have any non-latin characters. The "quotes" and \\backslashes should be escaped only if it\'s a part of email address';
        $this->assertSame($longlatinstr, core_text::encode_mimeheader($longlatinstr));
        $this->assertSame($longlatinstr, $mailer->encodeHeader($longlatinstr));
        $longlatinstrwithslash = preg_replace(['/\\\\/', "/\"/"], ['\\\\\\', '\\"'], $longlatinstr);
        $this->assertSame('"' . $longlatinstrwithslash . '"', $mailer->encodeHeader($longlatinstr, 'phrase'));

                $longstr = "Неопознанная ошибка в файле C:\\tmp\\: \"Не пользуйтесь виндоуз\"";
        $encodedlongstr = "=?utf-8?B?0J3QtdC+0L/QvtC30L3QsNC90L3QsNGPINC+0YjQuNCx0LrQsCDQsiDRhNCw?=
 =?utf-8?B?0LnQu9C1IEM6XHRtcFw6ICLQndC1INC/0L7Qu9GM0LfRg9C50YLQtdGB?=
 =?utf-8?B?0Ywg0LLQuNC90LTQvtGD0Lci?=";
        $this->assertSame($encodedlongstr, $mailer->encodeHeader($longstr));
        $this->assertSame('"' . $encodedlongstr . '"', $mailer->encodeHeader($longstr, 'phrase'));
    }

    
    public function test_entities_to_utf8() {
        $str = "&#x17d;lu&#x165;ou&#x10d;k&#xfd; kon&iacute;&#269;ek&copy;&quot;&amp;&lt;&gt;&sect;&laquo;";
        $this->assertSame("Žluťoučký koníček©\"&<>§«", core_text::entities_to_utf8($str));
    }

    
    public function test_utf8_to_entities() {
        $str = "&#x17d;luťoučký kon&iacute;ček&copy;&quot;&amp;&lt;&gt;&sect;&laquo;";
        $this->assertSame("&#x17d;lu&#x165;ou&#x10d;k&#xfd; kon&iacute;&#x10d;ek&copy;&quot;&amp;&lt;&gt;&sect;&laquo;", core_text::utf8_to_entities($str));
        $this->assertSame("&#381;lu&#357;ou&#269;k&#253; kon&iacute;&#269;ek&copy;&quot;&amp;&lt;&gt;&sect;&laquo;", core_text::utf8_to_entities($str, true));

        $str = "&#381;luťoučký kon&iacute;ček&copy;&quot;&amp;&lt;&gt;&sect;&laquo;";
        $this->assertSame("&#x17d;lu&#x165;ou&#x10d;k&#xfd; kon&#xed;&#x10d;ek&#xa9;\"&<>&#xa7;&#xab;", core_text::utf8_to_entities($str, false, true));
        $this->assertSame("&#381;lu&#357;ou&#269;k&#253; kon&#237;&#269;ek&#169;\"&<>&#167;&#171;", core_text::utf8_to_entities($str, true, true));
    }

    
    public function test_trim_utf8_bom() {
        $bom = "\xef\xbb\xbf";
        $str = "Žluťoučký koníček";
        $this->assertSame($str.$bom, core_text::trim_utf8_bom($bom.$str.$bom));
    }

    
    public function test_get_encodings() {
        $encodings = core_text::get_encodings();
        $this->assertTrue(is_array($encodings));
        $this->assertTrue(count($encodings) > 1);
        $this->assertTrue(isset($encodings['UTF-8']));
    }

    
    public function test_code2utf8() {
        $this->assertSame('Ž', core_text::code2utf8(381));
    }

    
    public function test_utf8ord() {
        $this->assertSame(ord(''), core_text::utf8ord(''));
        $this->assertSame(ord('f'), core_text::utf8ord('f'));
        $this->assertSame(0x03B1, core_text::utf8ord('α'));
        $this->assertSame(0x0439, core_text::utf8ord('й'));
        $this->assertSame(0x2FA1F, core_text::utf8ord('𯨟'));
        $this->assertSame(381, core_text::utf8ord('Ž'));
    }

    
    public function test_strtotitle() {
        $str = "žluťoučký koníček";
        $this->assertSame("Žluťoučký Koníček", core_text::strtotitle($str));
    }

    
    public function test_strrchr() {
        $str = "Žluťoučký koníček";
        $this->assertSame('koníček', core_text::strrchr($str, 'koní'));
        $this->assertSame('Žluťoučký ', core_text::strrchr($str, 'koní', true));
        $this->assertFalse(core_text::strrchr($str, 'A'));
        $this->assertFalse(core_text::strrchr($str, 'ç', true));
    }
}

